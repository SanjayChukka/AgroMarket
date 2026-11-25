
<?php

// Respond to the registration form's email-code requests.
require __DIR__ . '/includes/email-code.php';

header('Content-Type: application/json');


// Validate the request method and CSRF token.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !codeCsrf()) {

    http_response_code(403);

    exit(json_encode([
        'message' => 'Reload the form and try again.'
    ]));
}


$sent = false;

try {

    // Establish the database connection.
    require __DIR__ . '/../config/db.php';

    $email = codeInput('email');


    // Handle the email verification code request.
    if (codeInput('action') === 'send') {

        $status = requestEmailCode(
            $link,
            $email,
            'registration'
        );

        $sent = $status === 'sent';


        // Handle successful OTP delivery.
        if ($sent) {

            $message = 'Verification code sent.';

        } elseif ($status === 'limited') {

            // Handle OTP request rate limiting.
            http_response_code(429);

            $message = 'Please wait before requesting another code. You can still enter your most recent code.';

        } else {

            // Handle OTP delivery failure.
            http_response_code(503);

            $message = 'We could not send your verification email. Please try again later.';
        }

    } else {

        // Verify the submitted OTP.
        if (
            !checkEmailCode(
                $link,
                'registration',
                $email,
                codeInput('otp')
            )
        ) {
            throw new DomainException(
                'Incorrect or expired code. Request a new code if needed.'
            );
        }

        $message = 'Email verified.';
    }

} catch (DomainException $e) {

    // Handle validation and verification errors.
    http_response_code(422);

    $message = $e->getMessage();

} catch (Throwable $e) {

    // Handle unexpected server errors.
    http_response_code(503);

    $message = 'Email verification is unavailable.';

    error_log('Registration OTP error ' . $e->getCode());
}


// Return the JSON response to JavaScript.
echo json_encode([
    'sent'    => $sent,
    'message' => $message
]);
