
<?php

    require __DIR__ . '/includes/bootstrap.php';
    require __DIR__ . '/includes/catalog.php';
    require __DIR__ . '/includes/charts.php';

    /* Page Configuration */
    $section   = 'reports';
    $pageTitle = 'Reports';

    $types = [
    'orders'       => 'Orders',
    'transactions' => 'Order values',
    'farmers'      => 'Farmer registrations',
    'buyers'       => 'Buyer registrations',
    'listings'     => 'Crop listings',
    ];

    $type = $_GET['type'] ?? 'orders';

    if (! isset($types[$type])) {
    $type = 'orders';
    }

    /* Date Filters */
    $today = rows('SELECT CURRENT_DATE today')[0]['today'];

    $from = $_GET['from'] ?? substr($today, 0, 8) . '01';
    $to   = $_GET['to'] ?? $today;

    /* Validate Date Format */
    function validDate($value): bool
    {
    if (! is_string($value)) {
        return false;
    }

    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

    return $date && $date->format('Y-m-d') === $value;
    }

    $valid = validDate($from) && validDate($to) && $from <= $to;

    /* Report Configuration */
    $config = catalog($type);

    $cols = $type === 'transactions'
    ? [
    'reference' => 'Order',
    'buyer'     => 'Buyer',
    'amount'    => 'Order value (INR)',
    'status'    => 'Order status',
    'recorded'  => 'Placed',
    ]
    : $config['columns'];

    /* Generate Report */
    if ($valid) {

    $params = [
        $from,
        (new DateTimeImmutable($to))
            ->modify('+1 day')
            ->format('Y-m-d'),
    ];

    $sql = "
        SELECT *
        FROM ({$config['sql']}) records
        WHERE recorded >= ?
          AND recorded < ?
        ORDER BY recorded DESC, id DESC
    ";

    /* Download CSV Report */
    if (isset($_GET['download'])) {

        $stmt = $link->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->get_result();

        header('Content-Type: text/csv; charset=utf-8');

        header(
            'Content-Disposition: attachment; filename="agromarket-' .
            $type . '-' . $from . '-' . $to . '.csv"'
        );

        $out = fopen('php://output', 'w');

        fwrite($out, "\xEF\xBB\xBF");

        fputcsv(
            $out,
            array_values($cols),
            ',',
            '"',
            ''
        );

        while ($r = $result->fetch_assoc()) {

            $line = [];

            foreach ($cols as $key => $heading) {

                $v = (string) ($r[$key] ?? '');

                $line[] = preg_match(
                    '/^[\s]*[=+@-]|^[\t\r\n]/u',
                    $v
                ) ? "'" . $v : $v;
            }

            fputcsv($out, $line, ',', '"', '');
        }

        fclose($out);
        exit;
    }

    /* Report Preview */
    $preview = rows(
        $sql . ' LIMIT 25',
        $params
    );

    /* Order Summary */
    $summary = rows(
        "
        SELECT
            COUNT(*) orders,
            COALESCE(SUM(order_status = 'completed'), 0) completed,
            COALESCE(SUM(order_status = 'pending'), 0) pending,
            COALESCE(SUM(subtotal), 0) amount
        FROM orders
        WHERE placed_at >= ?
          AND placed_at < ?
        ",
        $params
    )[0];

    /* Order Status Distribution */
    $mix = rows(
        "
        SELECT
            order_status status,
            COUNT(*) n
        FROM orders
        WHERE placed_at >= ?
          AND placed_at < ?
        GROUP BY order_status
        ORDER BY n DESC, status
        ",
        $params
    );

    /* Most Ordered Crops */
    $crops = rows(
        "
        SELECT
            l.crop_name crop,
            COUNT(*) orders,
            COUNT(DISTINCT l.listing_id) listings
        FROM orders o
        JOIN crop_listings l
            ON o.listing_id = l.listing_id
        WHERE o.placed_at >= ?
          AND o.placed_at < ?
        GROUP BY l.crop_name
        ORDER BY orders DESC, crop
        LIMIT 8
        ",
        $params
    );
    }

    require __DIR__ . '/includes/header.php';

?>

<!-- Report Filters -->
<form class="panel filters" method="get">

    <label>
        Report

        <select name="type">

            <?php foreach ($types as $key => $name): ?>

                <option
                    value="<?php echo h($key); ?>"
                    <?php echo $key === $type ? 'selected' : ''; ?>
                >
                    <?php echo h($name); ?>
                </option>

            <?php endforeach; ?>

        </select>
    </label>

    <label>
        From

        <input
            type="date"
            name="from"
            value="<?php echo h($from); ?>"
            required
        >
    </label>

    <label>
        Through

        <input
            type="date"
            name="to"
            value="<?php echo h($to); ?>"
            required
        >
    </label>

    <button type="submit">
        Generate report
    </button>

    <button
        type="submit"
        class="secondary"
        name="download"
        value="1"
    >
        Download CSV
    </button>

</form>


<?php if (! $valid): ?>

    <!-- Invalid Date Message -->
    <p role="alert" class="notice">
        Enter valid dates with the start date on or before the end date.
    </p>

<?php else: ?>

    <!-- Report Statistics -->
    <div class="kpis">

        <?php
            foreach (
                [
                    'orders'    => 'Orders placed',
                    'completed' => 'Completed orders',
                    'pending'   => 'Pending orders',
                    'amount'    => 'Placed order value',
                ] as $key => $name
            ):
        ?>

            <div class="metric">

                <span>
                    <?php echo $name; ?>
                </span>

                <strong>
                    <?php
                        echo($key === 'amount' ? '₹' : '') .
                        number_format(
                            $summary[$key],
                            $key === 'amount' ? 2 : 0
                        );
                    ?>
                </strong>

            </div>

        <?php endforeach; ?>

    </div>


    <!-- Report Charts -->
    <div class="charts">

        <!-- Most Ordered Crops -->
        <section class="panel">

            <h2>Most ordered crops</h2>

            <?php
                table(
                    $crops,
                    [
                        'crop'     => 'Crop',
                        'orders'   => 'Orders',
                        'listings' => 'Listings ordered',
                    ]
                );
            ?>

        </section>

        <!-- Order Status Chart -->
        <section class="panel">

            <h2>Order status in period</h2>

            <?php statusChart($mix); ?>

        </section>

    </div>


    <!-- Report Preview -->
    <section class="panel">

        <h2>
            <?php echo h($types[$type]); ?> report
        </h2>

        <p class="muted">
            Preview of the latest 25 matching records.
            The CSV includes every match.
        </p>

        <?php
            table(
                $preview,
                $cols
            );
        ?>

    </section>

<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
