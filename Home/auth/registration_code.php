<?php

// Respond to the registration form’s email-code requests.
require __DIR__ . '/includes/email-code.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !codeCsrf()) {
    http_response_code(403);
    exit(json_encode(['message' => 'Reload the form and try again.']));
}
$sent = false;
try {
    require __DIR__ . '/../config/db.php';
    $email = codeInput('email');
    if (codeInput('action') === 'send') {
        $status = requestEmailCode($link, $email, 'registration');
        $sent = $status === 'sent';
        if ($sent) {
            $message = 'Verification code sent.';
        } elseif ($status === 'limited') {
            http_response_code(429);
            $message = 'Please wait before requesting another code. You can still enter your most recent code.';
        } else {
            http_response_code(503);
            $message = 'We could not send your verification email. Please try again later.';
        }
    } else {
        if (!checkEmailCode($link, 'registration', $email, codeInput('otp'))) {
            throw new DomainException('Incorrect or expired code. Request a new code if needed.');
        }
        $message = 'Email verified.';
    }
} catch (DomainException $e) {
    http_response_code(422);
    $message = $e->getMessage();
} catch (Throwable $e) {
    http_response_code(503);
    $message = 'Email verification is unavailable.';
    error_log('Registration OTP error ' . $e->getCode());
}
echo json_encode(['sent' => $sent, 'message' => $message]);
