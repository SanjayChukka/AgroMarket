
<?php

require __DIR__ . '/includes/bootstrap.php';

$section = 'commissions';
$pageTitle = 'Commission dashboard';

$tables = [
    'farmer' => 'order_commissions',
    'buyer'  => 'buyer_commissions'
];

// Mark one payer's fee as received after the admin confirms the offline payment.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    checkPost();

    $payer = $_POST['payer'] ?? null;

    $orderId = filter_input(
        INPUT_POST,
        'order_id',
        FILTER_VALIDATE_INT
    );

    $returnPage = ($_GET['state'] ?? '') === 'due'
        ? 'commissions.php?state=due'
        : 'commissions.php';

    if (
        ($_POST['action'] ?? '') !== 'receive' ||
        !is_string($payer) ||
        !isset($tables[$payer]) ||
        !$orderId ||
        $orderId < 1
    ) {
        go(
            $returnPage,
            'Invalid commission. Refresh the ledger and try again.'
        );
    }

    // A single guarded update also covers repeat clicks and simultaneous requests.
    $table = $tables[$payer];

    $receipt = 'AMC-REC-' . strtoupper($payer[0]) . '-' . $orderId;

    try {

        $stmt = $link->prepare(
            "UPDATE $table
             SET
                collected_at = NOW(),
                collected_by = ?,
                receipt_reference = ?
             WHERE order_id = ?
                AND amount > 0
                AND collected_at IS NULL
                AND EXISTS (
                    SELECT 1
                    FROM orders
                    WHERE order_id = ?
                    AND order_status = 'completed'
                )"
        );

        $stmt->execute([
            $adminId,
            $receipt,
            $orderId,
            $orderId
        ]);

        $received = $stmt->affected_rows === 1;

        $stmt->close();

    } catch (mysqli_sql_exception $error) {

        error_log(
            'Commission receipt failed: ' . $error->getCode()
        );

        go(
            $returnPage,
            'The payment could not be recorded. Refresh the ledger and try again.'
        );
    }

    go(
        $returnPage,
        $received
            ? 'Payment recorded. Receipt ' . $receipt
            : 'This fee is already collected or is not due.'
    );
}


// One row per payer keeps the two fee balances independent.
$ledger = "(
    SELECT
        order_id,
        'farmer' AS payer,
        rate_percent,
        amount,
        collected_at,
        collected_by,
        receipt_reference
    FROM order_commissions

    UNION ALL

    SELECT
        order_id,
        'buyer',
        rate_percent,
        amount,
        collected_at,
        collected_by,
        receipt_reference
    FROM buyer_commissions
)";

$fees = rows(
    "SELECT
        c.*,
        o.order_number,
        o.order_status,
        o.subtotal,
        u.user_id,
        u.full_name,
        admin.full_name AS collector
    FROM $ledger c

    JOIN orders o
        ON o.order_id = c.order_id

    JOIN users u
        ON u.user_id = IF(
            c.payer = 'buyer',
            o.buyer_id,
            o.farmer_id
        )

    LEFT JOIN users admin
        ON admin.user_id = c.collected_by

    WHERE c.amount > 0
        AND (
            c.collected_at IS NOT NULL
            OR o.order_status NOT IN ('cancelled', 'rejected')
        )

    ORDER BY o.order_id DESC, c.payer"
);


$totals = array_fill_keys(
    ['buyer', 'farmer', 'collected', 'due', 'upcoming'],
    0
);

$balances = [];

foreach ($fees as &$fee) {

    $state = $fee['collected_at'] !== null
        ? 'collected'
        : ($fee['order_status'] === 'completed'
            ? 'due'
            : 'upcoming');

    $fee['state'] = $state;

    $cents = (int) round((float) $fee['amount'] * 100);

    $totals[$state] += $cents;

    if ($state === 'collected') {
        $totals[$fee['payer']] += $cents;
    }

    $key = $fee['payer'] . ':' . $fee['user_id'];

    $balances[$key] ??= [
        'name'      => $fee['full_name'],
        'role'      => $fee['payer'],
        'id'        => $fee['user_id'],
        'due'       => 0,
        'upcoming'  => 0,
        'collected' => 0
    ];

    $balances[$key][$state] += $cents;
}

unset($fee);

$totals['earned'] = $totals['due'] + $totals['collected'];

$filter = $_GET['state'] ?? 'all';

$states = [
    'all'       => 'All',
    'due'       => 'Due',
    'collected' => 'Collected',
    'upcoming'  => 'Upcoming'
];

if (!is_string($filter) || !isset($states[$filter])) {
    $filter = 'all';
}

$visible = array_filter(
    $fees,
    static fn($row) =>
        $filter === 'all' || $row['state'] === $filter
);

require __DIR__ . '/includes/header.php';

?>

<!-- Commission Summary -->

<section aria-label="Commission summary" class="kpis commission-grid">

    <?php foreach ([
        'earned'    => 'Commission earned',
        'collected' => 'Total collected',
        'farmer'    => 'Collected from farmers',
        'buyer'     => 'Collected from buyers',
        'due'       => 'Pending collection',
        'upcoming'  => 'Upcoming fees'
    ] as $key => $title): ?>

        <div class="metric">

            <span>
                <?php echo h($title); ?>
            </span>

            <strong>
                ₹<?php echo number_format($totals[$key] / 100, 2); ?>
            </strong>

        </div>

    <?php endforeach; ?>

</section>


<!-- Order Commissions -->

<h2>Order commissions</h2>

<p>
    When a completed order’s commission reaches the platform,
    click Received beside the payer’s fee.
</p>


<!-- Payment Status Filter -->

<form method="get">

    <label>

        Payment status

        <select name="state">

            <?php foreach ($states as $value => $caption): ?>

                <option
                    value="<?php echo h($value); ?>"
                    <?php echo $filter === $value ? 'selected' : ''; ?>
                >
                    <?php echo h($caption); ?>
                </option>

            <?php endforeach; ?>

        </select>

    </label>

    <button>Filter</button>

</form>


<!-- Commission Records -->

<?php if (!$visible): ?>

    <p>No fees match this status.</p>

<?php else: ?>

    <div class="table-wrap">

        <table>

            <thead>

                <tr>
                    <th>Fee reference</th>
                    <th>Order</th>
                    <th>Payer</th>
                    <th>Crop subtotal</th>
                    <th>Rate</th>
                    <th>Commission</th>
                    <th>Payment status</th>
                    <th>Receipt</th>
                    <th>Collected on</th>
                    <th>Recorded by</th>
                    <th>Action</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($visible as $fee): ?>

                    <tr>

                        <td>
                            <?php
                            echo $fee['state'] === 'upcoming'
                                ? '—'
                                : h(
                                    'AMC-' .
                                    strtoupper($fee['payer'][0]) .
                                    '-' .
                                    $fee['order_id']
                                );
                            ?>
                        </td>

                        <td>

                            <a href="orders.php?id=<?php echo (int) $fee['order_id']; ?>">
                                <?php echo h($fee['order_number']); ?>
                            </a>

                        </td>

                        <td>
                            <?php echo h($fee['full_name']); ?>
                            (<?php echo h(ucfirst($fee['payer'])); ?>)
                        </td>

                        <td>
                            ₹<?php echo h($fee['subtotal']); ?>
                        </td>

                        <td>
                            <?php echo h($fee['rate_percent']); ?>%
                        </td>

                        <td>
                            ₹<?php echo h($fee['amount']); ?>
                        </td>

                        <td>
                            <?php echo h($states[$fee['state']]); ?>
                        </td>

                        <td>
                            <?php echo h($fee['receipt_reference'] ?? '—'); ?>
                        </td>

                        <td>
                            <?php echo h($fee['collected_at'] ?? '—'); ?>
                        </td>

                        <td>
                            <?php echo h($fee['collector'] ?? '—'); ?>
                        </td>

                        <td>

                            <?php if ($fee['state'] === 'due'): ?>

                                <form method="post">

                                    <?php csrfInput(); ?>

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="receive"
                                    >

                                    <input
                                        type="hidden"
                                        name="payer"
                                        value="<?php echo h($fee['payer']); ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="order_id"
                                        value="<?php echo (int) $fee['order_id']; ?>"
                                    >

                                    <button
                                        type="submit"
                                        aria-label="<?php echo h(
                                            'Received ' .
                                            $fee['payer'] .
                                            ' fee for ' .
                                            $fee['order_number']
                                        ); ?>"
                                    >
                                        Received
                                    </button>

                                </form>

                            <?php else: ?>

                                —

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

<?php endif; ?>


<!-- Balances by User -->

<?php if ($balances): ?>

    <h2>Balances by user</h2>

    <div class="table-wrap">

        <table>

            <thead>

                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Upcoming</th>
                    <th>Due</th>
                    <th>Collected</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($balances as $balance): ?>

                    <tr>

                        <td>
                            <?php echo h($balance['name']); ?>
                            #<?php echo (int) $balance['id']; ?>
                        </td>

                        <td>
                            <?php echo h(ucfirst($balance['role'])); ?>
                        </td>

                        <?php foreach (
                            ['upcoming', 'due', 'collected'] as $key
                        ): ?>

                            <td>
                                ₹<?php echo number_format(
                                    $balance[$key] / 100,
                                    2
                                ); ?>
                            </td>

                        <?php endforeach; ?>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
