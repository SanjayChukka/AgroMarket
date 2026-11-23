<?php
require __DIR__ . '/includes/bootstrap.php';
$section = 'commissions';
$pageTitle = 'Platform commissions';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkPost();
    $id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $reference = is_string($_POST['reference'] ?? null) ? trim($_POST['reference']) : '';
    $payer = $_POST['payer'] ?? '';
    $tables = ['farmer' => 'order_commissions', 'buyer' => 'buyer_commissions'];
    if (!is_string($payer) || !isset($tables[$payer]) || !$id || $reference === '' || strlen($reference) > 100) {
        go('commissions.php', 'Enter the receipt reference (up to 100 bytes).');
    }
    $table = $tables[$payer];
    $stmt = $link->prepare("UPDATE $table c JOIN orders o ON o.order_id=c.order_id
        SET c.collected_at=NOW(), c.receipt_reference=?, c.collected_by=?
        WHERE c.order_id=? AND o.order_status='completed' AND c.amount>0 AND c.collected_at IS NULL");
    $stmt->execute([$reference, $adminId, $id]);
    go('commissions.php', $stmt->affected_rows ? 'Receipt recorded.' : 'This fee is already settled or is not due.');
}
$ledger = "(SELECT order_id,'Farmer' AS payer,amount,collected_at,receipt_reference FROM order_commissions
    UNION ALL SELECT order_id,'Buyer',amount,collected_at,receipt_reference FROM buyer_commissions)";
$fees = rows("SELECT o.order_id,o.order_number,u.full_name,c.payer,c.amount,c.collected_at,c.receipt_reference
    FROM $ledger c JOIN orders o ON o.order_id=c.order_id
    JOIN users u ON u.user_id=IF(c.payer='Buyer',o.buyer_id,o.farmer_id)
    WHERE o.order_status='completed' AND c.amount>0 ORDER BY c.collected_at IS NULL DESC,o.order_id DESC,c.payer");
$totals = rows("SELECT SUM(IF(c.collected_at IS NULL,c.amount,0)) due,
    SUM(IF(c.collected_at IS NULL,0,c.amount)) collected FROM $ledger c
    JOIN orders o ON o.order_id=c.order_id WHERE o.order_status='completed'")[0];
require __DIR__ . '/includes/header.php';
?>
<p>Total fees due: ₹<?= number_format($totals['due'] ?? 0,2) ?> · Collected: ₹<?= number_format($totals['collected'] ?? 0,2) ?></p>
<p>Record a receipt after the named payer pays the platform fee offline.</p>
<?php if (!$fees): ?><p>No commissionable orders have been completed.</p><?php else: ?>
<div class="table-wrap"><table><thead><tr><th>Order</th><th>Payer</th><th>Fee</th><th>Receipt</th></tr></thead><tbody>
<?php foreach ($fees as $fee): ?><tr><td><?= h($fee['order_number']) ?></td><td><?= h($fee['full_name']) ?> (<?= h($fee['payer']) ?>)</td>
<td>₹<?= h($fee['amount']) ?></td><td>
<?php if ($fee['collected_at']): ?><?= h($fee['receipt_reference']) ?> · <?= h($fee['collected_at']) ?>
<?php else: ?><form method="post"><?php csrfInput(); ?>
<input type="hidden" name="order_id" value="<?= (int)$fee['order_id'] ?>">
<input type="hidden" name="payer" value="<?= h(strtolower($fee['payer'])) ?>">
<label>Receipt reference <input name="reference" maxlength="100" required></label>
<button>Record collection</button></form><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
