<?php
require_once __DIR__ . '/email-code.php';
require_once __DIR__ . '/../../config/db.php';
$message = '';
$reset   = $_SESSION['email_codes']['password_reset'] ?? [];
if ($stage === 'reset' && (! hasEmailProof('password_reset', $reset['email'] ?? '') || empty($reset['user']))) {
    header('Location: forget_password.php');exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (! codeCsrf()) {
            throw new DomainException('Reload the form and try again.');
        }

        if ($stage === 'request') {
            requestEmailCode($link, codeInput('email'), 'password_reset');
            header('Location: verify_reset_opt.php', true, 303);exit;
        }
        if ($stage === 'verify') {
            if (! checkEmailCode($link, 'password_reset', $reset['email'] ?? '', codeInput('otp')) || empty($reset['user'])) {
                throw new DomainException('The code is incorrect or expired. You can request another.');
            }

            session_regenerate_id(true);
            header('Location: reset_password.php', true, 303);exit;
        }
        $password = codeInput('password');
        if (strlen($password) < 8 || strlen($password) > 72 || str_contains($password, chr(0)) ||
            ! preg_match('/[A-Z]/', $password) || ! preg_match('/[a-z]/', $password) ||
            ! preg_match('/[0-9]/', $password) || ! preg_match('/[^a-zA-Z0-9]/', $password)) {
            throw new DomainException('Use 8–72 bytes with uppercase, lowercase, a number and a special character.');
        }

        if ($password !== codeInput('confirm_password')) {
            throw new DomainException('Passwords do not match.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $link->begin_transaction();
        try {
            consumeEmailProof($link, 'password_reset', $reset['email']);
            $link->execute_query("UPDATE users SET password_hash=? WHERE user_id=? AND email=? AND BINARY password_hash=? AND account_status='active'",
                [$hash, $reset['user'], $reset['email'], $reset['old_hash']]);
            if ($link->affected_rows !== 1) {
                throw new DomainException('Request a new code to reset your password.');
            }

            $link->commit();
        } catch (Throwable $e) {$link->rollback();throw $e;}
        unset($_SESSION['email_codes']['password_reset']);
        $_SESSION['reset_done'] = true;
        header('Location: login.php', true, 303);exit;
    } catch (DomainException $e) {$message = $e->getMessage();} catch (Throwable $e) {error_log('Password recovery error ' . $e->getCode());
        $message = 'Recovery is temporarily unavailable. Please try again.';}
}
