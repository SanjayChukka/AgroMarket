
<?php

// Provision a new administrator locally without publishing a shared password.
if (PHP_SAPI !== 'cli') {

    http_response_code(404);

    exit;
}


// Validate command-line arguments.
if (
    $argc !== 3 ||
    !filter_var($argv[2], FILTER_VALIDATE_EMAIL) ||
    !preg_match('/^[a-zA-Z0-9_]{3,40}$/D', $argv[1])
) {
    fwrite(
        STDERR,
        "Usage: php create_admin.php USERNAME EMAIL\n"
    );

    exit(1);
}


// Establish the database connection.
require __DIR__ . '/../config/db.php';


// Generate a secure initial password.
$password = bin2hex(random_bytes(16)) . 'Aa!';


// Begin database transaction.
$link->begin_transaction();

try {

    // Create the administrator account.
    $link->execute_query(
        "INSERT INTO users (
            role,
            full_name,
            username,
            email,
            password_hash,
            account_status
        ) VALUES (
            'admin',
            'Administrator',
            ?,
            ?,
            ?,
            'active'
        )",
        [
            $argv[1],
            $argv[2],
            password_hash($password, PASSWORD_DEFAULT)
        ]
    );


    // Create the administrator profile.
    $link->execute_query(
        "INSERT INTO admin_profiles (
            admin_id
        ) VALUES (?)",
        [$link->insert_id]
    );


    // Commit the database transaction.
    $link->commit();

} catch (Throwable $e) {

    // Roll back changes if account creation fails.
    $link->rollback();

    fwrite(
        STDERR,
        "Could not create the account. Check for an existing username or email.\n"
    );

    exit(1);
}


// The new administrator changes this generated password after first sign-in.
echo "Initial password: ", $password, PHP_EOL;
