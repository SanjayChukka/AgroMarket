<?php
require __DIR__ . '/includes/bootstrap.php';

$section = 'commissions';
$pageTitle = 'Commission dashboard';
$tables = ['farmer' => 'order_commissions', 'buyer' => 'buyer_commissions'];

// Import posted INR credits from the platform's own bank statement.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkPost();
    $file = $_FILES['statement'] ?? [];
    if (($file['error'] ?? -1) !== UPLOAD_ERR_OK || $file['size'] > 2000000) {
        go('commissions.php', 'Choose a CSV under 2 MB.');
    }
    $stream = fopen($file['tmp_name'], 'rb');
    $columns = fgetcsv($stream);
    if ($columns) {
        $columns[0] = preg_replace('/^\xEF\xBB\xBF/', '', $columns[0]);
    }
    if ($columns !== ['fee_reference', 'bank_reference', 'credit_inr']) {
        go('commissions.php', 'Use the three column names shown on the page.');
    }

    $matched = $skipped = 0;
    $link->begin_transaction();
    try {
        while (($row = fgetcsv($stream)) !== false) {
            if (count($row) !== 3) { $skipped++; continue; }
            [$ref, $bank, $amount] = array_map('trim', $row);
            if (!preg_match('/^AMC-(B|F)-([1-9][0-9]{0,17})$/D', $ref, $m)
                || !preg_match('/^[A-Za-z0-9._\/-]{1,100}$/D', $bank)
                || !preg_match('/^\d{1,12}(\.\d{1,2})?$/D', $amount)) {
                $skipped++;
                continue;
            }

            $payer = $m[1] === 'B' ? 'buyer' : 'farmer';
            $table = $tables[$payer];
            $id = $m[2];
            $fee = $link->execute_query("SELECT c.order_id FROM $table c JOIN orders o ON o.order_id=c.order_id
                WHERE c.order_id=? AND c.collected_at IS NULL AND o.order_status='completed'
                  AND c.amount=CAST(? AS DECIMAL(14,2)) AND c.amount>0 FOR UPDATE", [$id, $amount])->fetch_row();
            if (!$fee) { $skipped++; continue; }
            try {
                $link->execute_query('INSERT INTO commission_bank_credits
                    (bank_reference, order_id, payer, credited_amount, recorded_by) VALUES (?,?,?,?,?)',
                    [$bank, $id, $payer, $amount, $adminId]);
            } catch (mysqli_sql_exception $error) {
                if ($error->getCode() !== 1062) throw $error;
                $skipped++;
                continue;
            }
            $link->execute_query("UPDATE $table SET collected_at=NOW(), collected_by=?, receipt_reference=?
                WHERE order_id=?", [$adminId, $bank, $id]);
            $matched++;
        }
        $link->commit();
    } catch (Throwable $error) {
        $link->rollback();
        error_log('Statement reconciliation failed: ' . $error->getCode());
        go('commissions.php', 'Import failed. No new credits were recorded. Retry the statement.');
    }
    go('commissions.php', "$matched fee(s) collected. $skipped row(s) already recorded or unmatched.");
}

// One row per payer keeps the two fee balances independent.
$ledger = "(SELECT order_id, 'farmer' AS payer, rate_percent, amount, collected_at, collected_by, receipt_reference
            FROM order_commissions UNION ALL
            SELECT order_id, 'buyer', rate_percent, amount, collected_at, collected_by, receipt_reference
            FROM buyer_commissions)";
$fees = rows("SELECT c.*, o.order_number, o.order_status, o.subtotal, u.user_id, u.full_name,
                     admin.full_name AS collector
              FROM $ledger c JOIN orders o ON o.order_id = c.order_id
              JOIN users u ON u.user_id = IF(c.payer = 'buyer', o.buyer_id, o.farmer_id)
              LEFT JOIN users admin ON admin.user_id = c.collected_by
              WHERE c.amount > 0 AND (c.collected_at IS NOT NULL OR o.order_status NOT IN ('cancelled','rejected'))
              ORDER BY o.order_id DESC, c.payer");

$totals = array_fill_keys(['buyer', 'farmer', 'collected', 'due', 'upcoming'], 0);
$balances = [];
foreach ($fees as &$fee) {
    $state = $fee['collected_at'] !== null ? 'collected' : ($fee['order_status'] === 'completed' ? 'due' : 'upcoming');
    $fee['state'] = $state;
    $cents = (int) round((float) $fee['amount'] * 100);
    $totals[$state] += $cents;
    if ($state === 'collected') {
        $totals[$fee['payer']] += $cents;
    }
    $key = $fee['payer'] . ':' . $fee['user_id'];
    $balances[$key] ??= ['name' => $fee['full_name'], 'role' => $fee['payer'], 'id' => $fee['user_id'],
                         'due' => 0, 'upcoming' => 0, 'collected' => 0];
    $balances[$key][$state] += $cents;
}
unset($fee);
$totals['earned'] = $totals['due'] + $totals['collected'];
$filter = $_GET['state'] ?? 'all';
$states = ['all' => 'All', 'due' => 'Due', 'collected' => 'Collected', 'upcoming' => 'Upcoming'];
if (!is_string($filter) || !isset($states[$filter])) {
    $filter = 'all';
}
$visible = array_filter($fees, static fn($row) => $filter === 'all' || $row['state'] === $filter);
require __DIR__ . '/includes/header.php';
?>
<section aria-label="Commission summary" class="kpis commission-grid">
    <?php foreach (['earned' => 'Commission earned', 'collected' => 'Total collected', 'farmer' => 'Collected from farmers',
                    'buyer' => 'Collected from buyers', 'due' => 'Pending collection', 'upcoming' => 'Upcoming fees'] as $key => $title): ?>
        <div class="metric"><span><?= h($title) ?></span><strong>₹<?= number_format($totals[$key] / 100, 2) ?></strong></div>
    <?php endforeach; ?>
</section>

<h2>Order commissions</h2>
<p>Completion automatically makes both fees due. Match incoming INR credits against their fee references to record collection.</p>
<form method="post" enctype="multipart/form-data">
    <?php csrfInput(); ?>
    <label>Bank credits CSV <input type="file" name="statement" accept=".csv,text/csv" required></label>
    <p>Columns in order: <code>fee_reference,bank_reference,credit_inr</code>. Include posted credits from the platform bank account. Each row must cover one full fee.</p>
    <button>Match bank credits</button>
</form>
<p>Filter the order ledger by payment status.</p>
<form method="get">
    <label>Payment status <select name="state">
        <?php foreach ($states as $value => $caption): ?>
            <option value="<?= h($value) ?>" <?= $filter === $value ? 'selected' : '' ?>><?= h($caption) ?></option>
        <?php endforeach; ?>
    </select></label>
    <button>Filter</button>
</form>

<?php if (!$visible): ?>
    <p>No fees match this status.</p>
<?php else: ?>
<div class="table-wrap"><table>
        <thead><tr><th>Fee reference</th><th>Order</th><th>Payer</th><th>Crop subtotal</th><th>Rate</th>
            <th>Commission</th><th>Payment status</th><th>Receipt</th><th>Collected on</th><th>Recorded by</th></tr></thead>
        <tbody>
        <?php foreach ($visible as $fee): ?>
            <tr>
                <td><?= $fee['state'] === 'upcoming' ? '—' : h('AMC-' . strtoupper($fee['payer'][0]) . '-' . $fee['order_id']) ?></td>
                <td><a href="orders.php?id=<?= (int) $fee['order_id'] ?>"><?= h($fee['order_number']) ?></a></td>
                <td><?= h($fee['full_name']) ?> (<?= h(ucfirst($fee['payer'])) ?>)</td>
                <td>₹<?= h($fee['subtotal']) ?></td><td><?= h($fee['rate_percent']) ?>%</td><td>₹<?= h($fee['amount']) ?></td>
                <td><?= h($states[$fee['state']]) ?></td><td><?= h($fee['receipt_reference'] ?? '—') ?></td>
                <td><?= h($fee['collected_at'] ?? '—') ?></td><td><?= h($fee['collector'] ?? '—') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>

<?php endif; ?>

<?php if ($balances): ?>
<h2>Balances by user</h2>
<div class="table-wrap"><table>
    <thead><tr><th>User</th><th>Role</th><th>Upcoming</th><th>Due</th><th>Collected</th></tr></thead>
    <tbody><?php foreach ($balances as $balance): ?>
        <tr><td><?= h($balance['name']) ?> #<?= (int) $balance['id'] ?></td><td><?= h(ucfirst($balance['role'])) ?></td>
            <?php foreach (['upcoming', 'due', 'collected'] as $key): ?>
                <td>₹<?= number_format($balance[$key] / 100, 2) ?></td>
            <?php endforeach; ?>
        </tr>
    <?php endforeach; ?></tbody>
</table></div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
