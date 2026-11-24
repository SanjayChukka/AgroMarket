<?php
// Loaded only from the authenticated admin records controller.
function manageMarketRecord(mysqli $link, string $section, int $id, int $adminId): void
{
    checkPost();
    $action = $_POST['action'] ?? '';
    $reason = is_string($_POST['reason'] ?? null) ? trim($_POST['reason']) : '';
    $path = $section . '.php?id=' . $id;
    if ($reason === '' || strlen($reason) > 400) {
        go($path, 'Enter a reason of up to 400 bytes.');
    }
    $link->begin_transaction();
    try {
        if ($section === 'orders') {
            $order = rows('SELECT * FROM orders WHERE order_id=? FOR UPDATE', [$id])[0] ?? [];
            if (($order['order_status'] ?? '') !== 'pending') {
                throw new DomainException('Only pending orders can be changed here.');
            }
            $old = $new = 'pending';
            if ($action === 'cancel') {
                $new = 'cancelled';
                $stmt = $link->prepare('UPDATE orders SET order_status=? WHERE order_id=?');
                $stmt->execute([$new, $id]);
                $message = 'Administrator cancelled the pending order.';
            } elseif ($action === 'address' && $order['fulfilment_method'] === 'delivery') {
                $address = is_string($_POST['address'] ?? null) ? trim($_POST['address']) : '';
                if ($address === '' || strlen($address) > 255 || $order['delivery_address'] !== ($_POST['previous_address'] ?? null)) {
                    throw new DomainException('Review the current street address and enter a value of up to 255 bytes.');
                }
                $stmt = $link->prepare('UPDATE orders SET delivery_address=? WHERE order_id=?');
                $stmt->execute([$address, $id]);
                $message = 'Administrator corrected the delivery street address.';
            } else {
                throw new DomainException('Action unavailable.');
            }
            $remarks = $message . ' ' . $reason;
            $stmt = $link->prepare('INSERT INTO order_status_history(order_id,changed_by,old_status,new_status,remarks) VALUES(?,?,?,?,?)');
            $stmt->execute([$id,$adminId,$old,$new,$remarks]);
            $stmt = $link->prepare("INSERT INTO notifications(user_id,title,message,notification_type,related_order_id) VALUES(?,?,?,'order_update',?)");
            foreach ([$order['buyer_id'], $order['farmer_id']] as $recipient) {
                $stmt->execute([$recipient, 'Order update', $order['order_number'] . ' — ' . $remarks, $id]);
            }
        } elseif ($section === 'listings' && in_array($action,['pause','resume'],true)) {
            $resume = $action === 'resume';
            $stmt = $link->prepare("UPDATE crop_listings SET listing_status=? WHERE listing_id=? AND listing_status=? AND (?=0 OR quantity_available>0)");
            $stmt->execute([$resume ? 'available':'paused', $id, $resume ? 'paused':'available', (int)$resume]);
            if (!$stmt->affected_rows) throw new DomainException('Listing status or stock changed. Review the listing.');
            $stmt = $link->prepare("INSERT INTO notifications(user_id,title,message,notification_type)
                SELECT farmer_id,'Listing updated',CONCAT(crop_name, ': ', ?),'listing_update' FROM crop_listings WHERE listing_id=?");
            $stmt->execute([($resume ? 'Resumed. ' : 'Paused. ') . $reason, $id]);
        } else {
            throw new DomainException('Action unavailable.');
        }
        $link->commit();
    } catch (Throwable $error) {
        $link->rollback();
        if (!$error instanceof DomainException) error_log($error->getMessage());
        go($path, $error instanceof DomainException ? $error->getMessage() : 'Update failed. No changes saved.');
    }
    go($path,'Changes saved.');
}
