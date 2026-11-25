<?php
// Called only from a role-protected orders page.
function showOrderMoney(mysqli $link, string $role, int $userId): void
{
    $farmer = $role === 'farmer';
    $fees = $farmer ? 'order_commissions' : 'buyer_commissions';
    $owner = $farmer ? 'farmer_id' : 'buyer_id';
    $operator = $farmer ? '-' : '+';
    $amounts = $link->execute_query("SELECT
        COALESCE(SUM(o.subtotal $operator COALESCE(c.amount, 0)), 0) AS total,
        COALESCE(SUM(CASE WHEN r.order_id IS NOT NULL THEN o.subtotal ELSE 0 END), 0) AS crop_paid,
        COALESCE(SUM(CASE WHEN c.collected_at IS NOT NULL THEN c.amount ELSE 0 END), 0) AS fee_paid,
        COALESCE(SUM(CASE WHEN r.order_id IS NOT NULL THEN o.subtotal ELSE 0 END)
            $operator SUM(CASE WHEN c.collected_at IS NOT NULL THEN c.amount ELSE 0 END), 0) AS confirmed
        FROM orders o LEFT JOIN $fees c ON c.order_id = o.order_id
        LEFT JOIN crop_payment_receipts r ON r.order_id = o.order_id
        WHERE o.$owner = ? AND o.order_status = 'completed'", [$userId])->fetch_assoc();

    $labels = [
        'total' => $farmer ? 'Net earnings after fees' : 'Total purchase cost',
        'crop_paid' => 'Crop payments confirmed by farmer',
        'fee_paid' => 'Commission paid',
        'confirmed' => $farmer ? 'Net amount received' : 'Total spent and confirmed',
    ];
    ?>
    <section aria-label="Completed order payments">
        <h2><?= $farmer ? 'Earnings' : 'Spending' ?> on completed orders</h2>
        <div class="money-grid">
            <?php foreach ($labels as $key => $label): ?>
                <div class="money-card"><span><?= e($label) ?></span>
                    <strong>₹<?= number_format((float) $amounts[$key], 2) ?></strong></div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}
