<?php
    require __DIR__ . '/includes/bootstrap.php';
    $section   = 'commissions';
    $pageTitle = 'Platform commissions';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkPost();
    $id        = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $reference = is_string($_POST['reference'] ?? null) ? trim($_POST['reference']) : '';
    $payer     = $_POST['payer'] ?? '';
    $tables    = ['farmer' => 'order_commissions', 'buyer' => 'buyer_commissions'];
    if (! is_string($payer) || ! isset($tables[$payer]) || ! $id || $reference === '' || strlen($reference) > 100) {
        go('commissions.php', 'Enter the receipt reference (up to 100 bytes).');
    }
    $table = $tables[$payer];
    $stmt  = $link->prepare("UPDATE $table c JOIN orders o ON o.order_id=c.order_id
        SET c.collected_at=NOW(), c.receipt_reference=?, c.collected_by=?
        WHERE c.order_id=? AND o.order_status='completed' AND c.amount>0 AND c.collected_at IS NULL");
    $stmt->execute([$reference, $adminId, $id]);
    go('commissions.php', $stmt->affected_rows ? 'Receipt recorded.' : 'This fee is already settled or is not due.');
    }
    $ledger = "(SELECT order_id,'Farmer' AS payer,amount,collected_at,receipt_reference FROM order_commissions
    UNION ALL SELECT order_id,'Buyer',amount,collected_at,receipt_reference FROM buyer_commissions)";
    $collected = rows("SELECT
        COALESCE(SUM(CASE WHEN payer='Farmer' THEN amount ELSE 0 END), 0) AS farmer,
        COALESCE(SUM(CASE WHEN payer='Buyer' THEN amount ELSE 0 END), 0) AS buyer,
        COALESCE(SUM(amount), 0) AS total
        FROM $ledger c WHERE collected_at IS NOT NULL AND amount > 0")[0];
    $fees = rows("SELECT o.order_id,o.order_number,o.order_status,u.user_id,u.full_name,c.payer,c.amount,c.collected_at,c.receipt_reference
    FROM $ledger c JOIN orders o ON o.order_id=c.order_id
    JOIN users u ON u.user_id=IF(c.payer='Buyer',o.buyer_id,o.farmer_id)
    WHERE (o.order_status NOT IN ('cancelled','rejected') OR c.collected_at IS NOT NULL) AND c.amount>0 ORDER BY c.collected_at IS NULL DESC,o.order_id DESC,c.payer");
    $balances = [];
    foreach ($fees as $fee) {
    $key  = $fee['user_id'] . '-' . $fee['payer'];
    $balances[$key] ??= ['name' => $fee['full_name'], 'role' => $fee['payer'], 'id' => $fee['user_id'], 'upcoming' => 0, 'due' => 0, 'paid' => 0];
    $bucket                   = $fee['collected_at'] !== null ? 'paid' : ($fee['order_status'] === 'completed' ? 'due' : 'upcoming');
    $balances[$key][$bucket] += (int) round((float) $fee['amount'] * 100);
    }
    require __DIR__ . '/includes/header.php';
?>
<section aria-labelledby="collection-heading">
    <h2 id="collection-heading">Commission collected</h2>
    <div class="kpis">
        <?php foreach (['total' => 'Total collected', 'farmer' => 'From farmers', 'buyer' => 'From buyers'] as $key => $caption): ?>
            <div class="metric">
                <span><?= h($caption) ?></span>
                <strong>₹<?= number_format((float) $collected[$key], 2) ?></strong>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<p>Upcoming fees become due when the buyer confirms receipt. Record paid fees using the offline receipt reference.</p>
<?php if (! $fees): ?><p>No eligible commission records yet.</p><?php else: ?>
<h2>Balances by user</h2><div class="table-wrap"><table><thead><tr><th>User</th><th>Role</th><th>Upcoming</th><th>Due</th><th>Paid</th></tr></thead><tbody>
<?php foreach ($balances as $balance): ?><tr><td><?php echo h($balance['name']) ?> #<?php echo (int) $balance['id'] ?></td><td><?php echo h($balance['role']) ?></td>
<?php foreach (['upcoming', 'due', 'paid'] as $bucket): ?><td>₹<?php echo number_format($balance[$bucket] / 100, 2) ?></td><?php endforeach; ?></tr><?php endforeach; ?>
</tbody></table></div><h2>Order receipts</h2>
<div class="table-wrap"><table><thead><tr><th>Order</th><th>Payer</th><th>Fee</th><th>Receipt</th></tr></thead><tbody>
<?php foreach ($fees as $fee): ?><tr><td><?php echo h($fee['order_number']) ?></td><td><?php echo h($fee['full_name']) ?> (<?php echo h($fee['payer']) ?>)</td>
<td>₹<?php echo h($fee['amount']) ?></td><td>
<?php if ($fee['collected_at']): ?><?php echo h($fee['receipt_reference']) ?> · <?php echo h($fee['collected_at']) ?>
<?php elseif ($fee['order_status'] !== 'completed'): ?>Upcoming · <?php echo h(label($fee['order_status'])) ?>
<?php else: ?><form method="post"><?php csrfInput(); ?>
<input type="hidden" name="order_id" value="<?php echo (int) $fee['order_id'] ?>">
<input type="hidden" name="payer" value="<?php echo h(strtolower($fee['payer'])) ?>">
<label>Receipt reference <input name="reference" maxlength="100" required></label>
<button>Record collection</button></form><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
