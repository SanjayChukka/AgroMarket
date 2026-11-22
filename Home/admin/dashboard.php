<?php
    require __DIR__ . '/includes/bootstrap.php';
    require __DIR__ . '/includes/charts.php';
    $section   = 'dashboard';
    $pageTitle = 'Marketplace overview';
    $metrics   = rows("SELECT
 (SELECT COUNT(*) FROM users WHERE role='farmer') farmers,
 (SELECT COUNT(*) FROM users WHERE role='buyer') buyers,
 (SELECT COUNT(*) FROM crop_listings WHERE listing_status <> 'removed') listings,
 (SELECT COUNT(*) FROM orders) orders,
 (SELECT COUNT(*) FROM orders WHERE order_status='pending') pending,
 ((SELECT COUNT(*) FROM complaints WHERE complaint_status IN ('open','in_progress')) +
 (SELECT COUNT(*) FROM support_requests WHERE status IN ('open','in_progress'))) complaints")[0];
    $today = new DateTimeImmutable(rows('SELECT CURRENT_DATE today')[0]['today']);
    $start = $today->modify('-6 days');
    $daily = [];
    foreach (rows('SELECT DATE(placed_at) day,COUNT(*) n FROM orders WHERE placed_at>=? AND placed_at<? GROUP BY DATE(placed_at)', [$start->format('Y-m-d'), $today->modify('+1 day')->format('Y-m-d')]) as $row) {
    $daily[$row['day']] = $row['n'];
    }

    $trend = [];
    for ($i = 0; $i < 7; $i++) {
    $day                      = $start->modify("+$i days");
    $trend[$day->format('D')] = (int) ($daily[$day->format('Y-m-d')] ?? 0);
    }
    $mix      = rows('SELECT order_status status,COUNT(*) n FROM orders GROUP BY order_status ORDER BY n DESC, status');
    $recent   = rows("SELECT full_name name,role,created_at recorded,account_status status FROM users WHERE role IN ('buyer','farmer') ORDER BY created_at DESC,user_id DESC LIMIT 5");
    $activity = rows("SELECT * FROM (
    SELECT * FROM (SELECT 'Order placed' event,order_number item,placed_at recorded FROM orders ORDER BY placed_at DESC LIMIT 5) order_events
    UNION ALL SELECT * FROM (SELECT 'Crop listed',crop_name,created_at FROM crop_listings ORDER BY created_at DESC LIMIT 5) listing_events
    UNION ALL SELECT * FROM (SELECT 'Registration',full_name,created_at FROM users WHERE role IN ('farmer','buyer') ORDER BY created_at DESC LIMIT 5) account_events
) events ORDER BY recorded DESC LIMIT 5");
    require __DIR__ . '/includes/header.php';
?>
<div class="kpis">
<?php foreach (['farmers' => ['Farmers', 'farmers.php'], 'buyers' => ['Buyers', 'buyers.php'], 'listings' => ['Current listings', 'listings.php'], 'orders' => ['Total orders', 'orders.php'], 'pending' => ['Pending orders', 'orders.php?status=pending'], 'complaints' => ['Open complaints', 'complaints.php']] as $key => [$name, $url]): ?>
<a class="metric" href="<?php echo h($url) ?>"><span><?php echo $name ?></span><strong><?php echo number_format($metrics[$key]) ?></strong></a>
<?php endforeach; ?></div>
<div class="charts"><section class="panel"><h2>Order activity</h2><?php barChart($trend, $start->format('d M') . ' – ' . $today->format('d M Y')); ?></section>
<section class="panel"><h2>Order status</h2><p class="muted">All time</p><?php statusChart($mix); ?></section></div>
<section class="panel"><div class="section-head"><h2>Recent registrations</h2></div><?php table($recent, ['name' => 'Name', 'role' => 'Role', 'recorded' => 'Registered', 'status' => 'Status']); ?></section>
<section class="panel"><h2>Recent platform activity</h2><?php table($activity, ['event' => 'Activity', 'item' => 'Record', 'recorded' => 'Time']); ?></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
