<?php
require __DIR__.'/includes/email-code.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !codeCsrf()) {
    http_response_code(403); exit(json_encode(['message'=>'Reload the form and try again.']));
}
try {
    require __DIR__.'/../config/db.php';
    $email = codeInput('email');
    if (codeInput('action') === 'send') {
        requestEmailCode($link,$email,'registration');
        $message = 'Check your inbox. Allow 60 seconds between requests.';
    } else {
        if (!checkEmailCode($link,'registration',$email,codeInput('otp'))) throw new DomainException('Incorrect or expired code. Request a new code if needed.');
        $message = 'Email verified.';
    }
} catch (DomainException $e) { http_response_code(422); $message=$e->getMessage(); }
catch (Throwable $e) { http_response_code(503); $message='Email verification is unavailable.'; error_log('Registration OTP error '.$e->getCode()); }
echo json_encode(['message'=>$message]);
