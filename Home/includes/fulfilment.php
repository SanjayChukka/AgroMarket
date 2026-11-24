<?php
require_once __DIR__ . "/../includes/email-service.php";
$_SESSION['fulfilment_token'] ??= bin2hex(random_bytes(32));

function nextFulfilmentAction(array $order, string $role): ?array
{
    if (!isset($order['fulfilment_method'])) return null;
    if ($role === 'farmer' && $order['order_status'] === 'accepted') {
        return $order['fulfilment_method'] === 'pickup'
            ? ['ready_for_pickup', 'Mark ready for pickup', 'The farmer marked the order ready for pickup.']
            : ['dispatched', 'Mark as dispatched', 'The farmer dispatched the order.'];
    }
    if ($role === 'buyer' && in_array($order['order_status'], ['dispatched','ready_for_pickup'], true)) {
        return ['completed', 'Confirm receipt', 'The buyer confirmed receipt of the crop.'];
    }
    return null;
}

function processFulfilment(mysqli $link, string $role): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    if (!$id || !is_string($_POST['token'] ?? null) || !hash_equals($_SESSION['fulfilment_token'], $_POST['token'])) {
        http_response_code(403);
        exit('Invalid submission. Reopen the order and try again.');
    }
    $userId = (int) $_SESSION['user_id'];
    $ownerColumn = $role === 'farmer' ? 'farmer_id' : 'buyer_id';
    $path = $role === 'farmer' ? 'order_details.php' : 'order_tracking.php';
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $link->begin_transaction();
    try {
        $stmt = $link->prepare("SELECT buyer_id,farmer_id,order_number,order_status,fulfilment_method FROM orders WHERE order_id=? AND $ownerColumn=? FOR UPDATE");
        $stmt->execute([$id, $userId]);
        $order = $stmt->get_result()->fetch_assoc();
        $step = $order ? nextFulfilmentAction($order, $role) : null;
        if (!$step || $step[0] !== ($_POST['next_status'] ?? null)) {
            throw new DomainException('The action is no longer available. Review the current order status.');
        }
        [$next, , $remarks] = $step;
        if ($next === 'ready_for_pickup') {
            $instructions = is_string($_POST['pickup_details'] ?? null) ? trim($_POST['pickup_details']) : '';
            if ($instructions === '' || strlen($instructions) > 240) throw new DomainException('Enter a pickup location of up to 240 bytes.');
            $remarks .= ' Pickup location: ' . $instructions;
        }
        $stmt = $link->prepare('UPDATE orders SET order_status=? WHERE order_id=?');
        $stmt->execute([$next, $id]);
        $stmt = $link->prepare('INSERT INTO order_status_history(order_id,changed_by,old_status,new_status,remarks) VALUES(?,?,?,?,?)');
        $stmt->execute([$id, $userId, $order['order_status'], $next, $remarks]);
        $stmt = $link->prepare("INSERT INTO notifications(user_id,title,message,notification_type,related_order_id) VALUES(?,?,?,'order_update',?)");
        $recipient = $role === 'farmer' ? $order['buyer_id'] : $order['farmer_id'];
        $stmt->execute([$recipient, 'Order ' . str_replace('_',' ',$next), $order['order_number'] . ' — ' . $remarks, $id]);
        $emailKeys = $next === 'completed' ? queueOrderEmails($link,$id,'completed') : [];
        $link->commit();
        foreach ($emailKeys as $key) dispatchAgroMail($link,$key);
        $message = 'Order status updated.';
    } catch (DomainException $error) {
        $link->rollback();
        $message = $error->getMessage();
    } catch (Throwable $error) {
        $link->rollback();
        error_log('Fulfilment update failed: ' . $error->getCode());
        $message = 'The update could not be saved. Please try again.';
    }
    $_SESSION['fulfilment_notice'][$id] = $message;
    header('Location: ' . $path . '?id=' . $id, true, 303);
    exit;
}

function showFulfilment(array $order, string $role): void
{
    $id = (int) $order['order_id'];
    $step = nextFulfilmentAction($order, $role);
    if (isset($_SESSION['fulfilment_notice'][$id])) {
        echo '<p role="status">' . e($_SESSION['fulfilment_notice'][$id]) . '</p>';
        unset($_SESSION['fulfilment_notice'][$id]);
    }
    if (!$step) return;
    ?>
    <form method="post">
        <input type="hidden" name="token" value="<?= e($_SESSION['fulfilment_token']) ?>">
        <input type="hidden" name="order_id" value="<?= $id ?>">
        <input type="hidden" name="next_status" value="<?= e($step[0]) ?>">
        <?php if ($step[0] === 'ready_for_pickup'): ?>
            <label>Pickup location <input name="pickup_details" required maxlength="240" placeholder="Farm address or collection point"></label>
        <?php endif; ?>
        <button type="submit"><?= e($step[1]) ?></button>
    </form>
    <?php
}
