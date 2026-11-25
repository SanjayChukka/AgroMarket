
<?php

require_once __DIR__ . '/../config/auth_check.php';

require_login('farmer', '../auth/login.php');

require_once __DIR__ . '/../config/db.php';


// Retrieve the payment confirmation token.
$token = $_POST['token'] ?? null;


// Validate the request method and CSRF token.
if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_SESSION['crop_payment_token']) ||
    !is_string($token) ||
    !hash_equals($_SESSION['crop_payment_token'], $token)
) {
    http_response_code(403);

    exit('Reopen the orders page and try again.');
}


// Retrieve and validate the order ID.
$id = filter_input(
    INPUT_POST,
    'order_id',
    FILTER_VALIDATE_INT
);


// Record the payment receipt for a completed order.
// Preserve the original confirmation time for duplicate requests.
$link->execute_query(
    "INSERT INTO crop_payment_receipts (
        order_id,
        farmer_id
    )
    SELECT
        order_id,
        farmer_id
    FROM orders
    WHERE order_id = ?
        AND farmer_id = ?
        AND order_status = 'completed'

    ON DUPLICATE KEY UPDATE
        confirmed_at = crop_payment_receipts.confirmed_at",
    [
        $id,
        (int) $_SESSION['user_id']
    ]
);


// Redirect to the farmer orders page.
header('Location: orders.php', true, 303);
