
<?php

// Allow execution only through the command-line interface.
if (PHP_SAPI !== 'cli') {

    http_response_code(404);

    exit;
}


// Load database connection and email service.
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/email-service.php';


// Fetch pending emails that are eligible for retry.
$rows = $link->query(
    'SELECT message_key
     FROM transactional_emails
     WHERE sent_at IS NULL
        AND retry_at <= NOW()
     ORDER BY retry_at
     LIMIT 100'
);


// Attempt to dispatch each pending email.
foreach ($rows as $row) {

    dispatchAgroMail($link, $row['message_key']);
}
