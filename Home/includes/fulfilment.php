
<?php

require_once __DIR__ . "/../includes/email-service.php";

$_SESSION['fulfilment_token'] ??= bin2hex(random_bytes(32));


// Determine the next available fulfilment action.
function nextFulfilmentAction(array $order, string $role): ?array
{
    if (!isset($order['fulfilment_method'])) {
        return null;
    }

    // Farmer actions for accepted orders.
    if (
        $role === 'farmer' &&
        $order['order_status'] === 'accepted'
    ) {
        return $order['fulfilment_method'] === 'pickup'
            ? [
                'ready_for_pickup',
                'Mark ready for pickup',
                'The farmer marked the order ready for pickup.'
            ]
            : [
                'dispatched',
                'Mark as dispatched',
                'The farmer dispatched the order.'
            ];
    }

    // Buyer actions for dispatched or pickup-ready orders.
    if (
        $role === 'buyer' &&
        in_array(
            $order['order_status'],
            ['dispatched', 'ready_for_pickup'],
            true
        )
    ) {
        return [
            'completed',
            'Confirm receipt',
            'The buyer confirmed receipt of the crop.'
        ];
    }

    return null;
}


// Process order fulfilment actions.
function processFulfilment(mysqli $link, string $role): void
{
    // Process only POST requests.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $id = filter_input(
        INPUT_POST,
        'order_id',
        FILTER_VALIDATE_INT
    );

    // Validate the order ID and CSRF token.
    if (
        !$id ||
        !is_string($_POST['token'] ?? null) ||
        !hash_equals(
            $_SESSION['fulfilment_token'],
            $_POST['token']
        )
    ) {
        http_response_code(403);

        exit('Invalid submission. Reopen the order and try again.');
    }


    // Identify the current user and role-specific destination.
    $userId = (int) $_SESSION['user_id'];

    $ownerColumn = $role === 'farmer'
        ? 'farmer_id'
        : 'buyer_id';

    $path = $role === 'farmer'
        ? 'order_details.php'
        : 'order_tracking.php';

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


    // Begin database transaction.
    $link->begin_transaction();

    try {

        // Fetch and lock the order belonging to the current user.
        $stmt = $link->prepare(
            "SELECT
                buyer_id,
                farmer_id,
                order_number,
                order_status,
                fulfilment_method
             FROM orders
             WHERE order_id = ?
                AND $ownerColumn = ?
             FOR UPDATE"
        );

        $stmt->execute([$id, $userId]);

        $order = $stmt->get_result()->fetch_assoc();

        $step = $order
            ? nextFulfilmentAction($order, $role)
            : null;


        // Verify that the requested action is still available.
        if (
            !$step ||
            $step[0] !== ($_POST['next_status'] ?? null)
        ) {
            throw new DomainException(
                'The action is no longer available. Review the current order status.'
            );
        }

        [$next, , $remarks] = $step;


        // Collect pickup instructions when required.
        if ($next === 'ready_for_pickup') {

            $instructions = is_string($_POST['pickup_details'] ?? null)
                ? trim($_POST['pickup_details'])
                : '';

            if (
                $instructions === '' ||
                strlen($instructions) > 240
            ) {
                throw new DomainException(
                    'Enter a pickup location of up to 240 bytes.'
                );
            }

            $remarks .= ' Pickup location: ' . $instructions;
        }


        // Update the current order status.
        $stmt = $link->prepare(
            'UPDATE orders
             SET order_status = ?
             WHERE order_id = ?'
        );

        $stmt->execute([
            $next,
            $id
        ]);


        // Record the status change in order history.
        $stmt = $link->prepare(
            'INSERT INTO order_status_history (
                order_id,
                changed_by,
                old_status,
                new_status,
                remarks
            ) VALUES (?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $id,
            $userId,
            $order['order_status'],
            $next,
            $remarks
        ]);


        // Prepare a notification for the other user.
        $stmt = $link->prepare(
            "INSERT INTO notifications (
                user_id,
                title,
                message,
                notification_type,
                related_order_id
            ) VALUES (?, ?, ?, 'order_update', ?)"
        );

        $recipient = $role === 'farmer'
            ? $order['buyer_id']
            : $order['farmer_id'];

        $stmt->execute([
            $recipient,
            'Order ' . str_replace('_', ' ', $next),
            $order['order_number'] . ' — ' . $remarks,
            $id
        ]);


        // Queue completion emails for the buyer and farmer.
        $emailKeys = $next === 'completed'
            ? queueOrderEmails($link, $id, 'completed')
            : [];


        // Commit all database changes.
        $link->commit();


        // Dispatch queued emails after a successful commit.
        foreach ($emailKeys as $key) {

            dispatchAgroMail($link, $key);
        }

        $message = 'Order status updated.';

    } catch (DomainException $error) {

        // Roll back changes and display the validation message.
        $link->rollback();

        $message = $error->getMessage();

    } catch (Throwable $error) {

        // Roll back changes and log unexpected errors.
        $link->rollback();

        error_log(
            'Fulfilment update failed: ' . $error->getCode()
        );

        $message = 'The update could not be saved. Please try again.';
    }


    // Store the result message in the session.
    $_SESSION['fulfilment_notice'][$id] = $message;


    // Redirect to the corresponding order page.
    header(
        'Location: ' . $path . '?id=' . $id,
        true,
        303
    );

    exit;
}


// Display available order fulfilment actions.
function showFulfilment(array $order, string $role): void
{
    $id = (int) $order['order_id'];

    $step = nextFulfilmentAction($order, $role);


    // Display the status update result.
    if (isset($_SESSION['fulfilment_notice'][$id])) {

        echo '<p role="status">'
            . e($_SESSION['fulfilment_notice'][$id])
            . '</p>';

        unset($_SESSION['fulfilment_notice'][$id]);
    }


    // Stop if no further fulfilment action is available.
    if (!$step) {
        return;
    }

    ?>

    <!-- Order Fulfilment Form -->
    <form method="post">

        <!-- CSRF Protection -->
        <input
            type="hidden"
            name="token"
            value="<?php echo e($_SESSION['fulfilment_token']); ?>"
        >

        <!-- Order ID -->
        <input
            type="hidden"
            name="order_id"
            value="<?php echo $id; ?>"
        >

        <!-- Next Order Status -->
        <input
            type="hidden"
            name="next_status"
            value="<?php echo e($step[0]); ?>"
        >


        <!-- Pickup Location -->
        <?php if ($step[0] === 'ready_for_pickup'): ?>

            <label>

                Pickup location

                <input
                    name="pickup_details"
                    required
                    maxlength="240"
                    placeholder="Farm address or collection point"
                >

            </label>

        <?php endif; ?>


        <!-- Fulfilment Action Button -->
        <button type="submit">
            <?php echo e($step[1]); ?>
        </button>

    </form>

    <?php
}
