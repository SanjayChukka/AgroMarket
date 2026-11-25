
<?php

    // Issue and verify short-lived email challenges for registration and password recovery.
    require_once __DIR__ . '/../../includes/email-service.php';

    if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
    }

    header('Cache-Control: no-store');

    $_SESSION['code_csrf'] ??= bin2hex(random_bytes(32));

    // Retrieve POST input safely.
    function codeInput(string $key): string
    {
    return is_string($_POST[$key] ?? null)
        ? $_POST[$key]
        : '';
    }

    // Check that this form submission came from the current
    // recovery or registration session.
    function codeCsrf(): bool
    {
    return hash_equals(
        $_SESSION['code_csrf'],
        codeInput('code_csrf')
    );
    }

    // Serialize requests for this address and limit how often
    // new codes can be issued.
    function requestEmailCode(
    mysqli $db,
    string $email,
    string $purpose
    ): string {

    $email = strtolower(trim($email));

    // Validate email address.
    if (
        ! filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 150
    ) {
        throw new DomainException(
            'Enter a valid registered email address.'
        );
    }

    // Generate and acquire a database lock.
    $lock = hash('sha256', "$purpose:$email");

    if (
        ! (int) $db->execute_query(
            'SELECT GET_LOCK(?, 2)',
            [$lock]
        )->fetch_row()[0]
    ) {
        return 'limited';
    }

    try {

        // Check recent OTP requests.
        $count = $db->execute_query(
            'SELECT
                COUNT(*) n,
                MAX(created_at) > NOW() - INTERVAL 60 SECOND recent
             FROM verification_codes
             WHERE identifier = ?
                AND purpose = ?
                AND created_at > NOW() - INTERVAL 15 MINUTE',
            [$email, $purpose]
        )->fetch_assoc();

        if ($count['recent'] || $count['n'] >= 4) {
            return 'limited';
        }

        // Find the registered user for password recovery.
        $user = null;

        if ($purpose === 'password_reset') {

            $user = $db->execute_query(
                "SELECT
                    user_id,
                    password_hash,
                    email
                 FROM users
                 WHERE email = ?
                    AND account_status = 'active'",
                [$email]
            )->fetch_assoc();
        }

        // Generate a six-digit OTP.
        $code = str_pad(
            (string) random_int(0, 999999),
            6,
            '0',
            STR_PAD_LEFT
        );

        $secret = bin2hex(random_bytes(32));

        // Begin database transaction.
        $db->begin_transaction();

        try {

            // Expire previous verification codes.
            $db->execute_query(
                'UPDATE verification_codes
                 SET expires_at = NOW()
                 WHERE identifier = ?
                    AND purpose = ?
                    AND expires_at > NOW()',
                [$email, $purpose]
            );

            // Store the hashed verification code.
            $db->execute_query(
                "INSERT INTO verification_codes (
                    user_id,
                    identifier,
                    channel,
                    purpose,
                    code_hash,
                    expires_at
                ) VALUES (
                    ?,
                    ?,
                    'email',
                    ?,
                    ?,
                    NOW() + INTERVAL 10 MINUTE
                )",
                [
                    $user['user_id'] ?? null,
                    $email,
                    $purpose,
                    hash_hmac('sha256', $code, $secret),
                ]
            );

            $id = $db->insert_id;

            $db->commit();

        } catch (Throwable $e) {

            $db->rollback();

            throw $e;
        }

        // Keep this challenge bound to the session that requested it.
        $_SESSION['email_codes'][$purpose] = [
            'id'       => $id,
            'email'    => $email,
            'secret'   => $secret,
            'expires'  => time() + 600,
            'user'     => $user['user_id'] ?? null,
            'old_hash' => $user['password_hash'] ?? null,
            'verified' => false,
        ];

        // Send OTP for registration or password recovery.
        if ($purpose === 'registration' || $user) {

            $task = $purpose === 'registration'
                ? 'verify your registration email'
                : 'reset your password';

            if (
                ! agroSendMail(
                    $user['email'] ?? $email,
                    'AgroMarket email verification',
                    "Use $code to $task. It expires in 10 minutes. Do not share this code."
                )
            ) {
                error_log('AgroMarket OTP delivery failed.');

                return 'failed';
            }

            return 'sent';
        }

        return 'skipped';

    } finally {

        // Release the database lock.
        $db->execute_query(
            'SELECT RELEASE_LOCK(?)',
            [$lock]
        );
    }
    }

    // Count this attempt and verify the session-bound challenge
    // before granting proof.
    function checkEmailCode(
    mysqli $db,
    string $purpose,
    string $email,
    string $code
    ): bool {

    $c = $_SESSION['email_codes'][$purpose] ?? [];

    // Validate email and OTP format.
    if (
        ($c['email'] ?? null) !== strtolower(trim($email)) ||
        ! preg_match('/^[0-9]{6}$/D', $code)
    ) {
        return false;
    }

    // Increment attempts and verify the OTP.
    $db->execute_query(
        'UPDATE verification_codes
         SET
            attempts = attempts + 1,
            verified_at = IF(code_hash = ?, NOW(), NULL)
         WHERE verification_id = ?
            AND verified_at IS NULL
            AND attempts < 5
            AND expires_at > NOW()',
        [
            hash_hmac('sha256', $code, $c['secret']),
            $c['id'],
        ]
    );

    if ($db->affected_rows !== 1) {
        return false;
    }

    // Check whether the verification was successful.
    $ok = (bool) $db->execute_query(
        'SELECT verified_at
         FROM verification_codes
         WHERE verification_id = ?',
        [$c['id']]
    )->fetch_row()[0];

    $_SESSION['email_codes'][$purpose]['verified'] = $ok;

    return $ok;
    }

    // Check that the verified session still matches
    // the requested purpose and email address.
    function hasEmailProof(
    string $purpose,
    string $email
    ): bool {

    $c = $_SESSION['email_codes'][$purpose] ?? [];

    return ! empty($c['verified'])
    && $c['expires'] > time()
    && $c['email'] === strtolower(trim($email));
    }

    // Called inside the same transaction as the account write.
    function consumeEmailProof(
    mysqli $db,
    string $purpose,
    string $email
    ): void {

    // Ensure that the email has been verified.
    if (! hasEmailProof($purpose, $email)) {

        throw new DomainException(
            'Verify your email again.'
        );
    }

    // Expire the verified code to prevent reuse.
    $db->execute_query(
        'UPDATE verification_codes
         SET
            expires_at = NOW(),
            code_hash = \'\'
         WHERE verification_id = ?
            AND purpose = ?
            AND identifier = ?
            AND verified_at IS NOT NULL
            AND expires_at > NOW()',
        [
            $_SESSION['email_codes'][$purpose]['id'],
            $purpose,
            strtolower(trim($email)),
        ]
    );

    if ($db->affected_rows !== 1) {

        throw new DomainException(
            'Verify your email again.'
        );
    }
}
