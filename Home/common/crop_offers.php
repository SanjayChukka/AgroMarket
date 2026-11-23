<?php
require_once __DIR__ . '/../includes/html.php';

$crop = is_string($_GET['crop'] ?? null) ? trim($_GET['crop']) : '';
if ($crop === '' || strlen($crop) > 600) {
    header('Location: market_prices.php');
    exit;
}

$categories = [
    'tomato' => 'Vegetables', 'onion' => 'Vegetables', 'potato' => 'Vegetables',
    'rice' => 'Cereals & Millets', 'maize' => 'Cereals & Millets', 'mango' => 'Fruits',
];
$category = $categories[strtolower($crop)] ?? '';
$error = false;

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    require __DIR__ . '/../config/db.php';

    $available = " FROM crop_listings cl JOIN users u ON u.user_id = cl.farmer_id
        WHERE u.role = 'farmer' AND u.account_status = 'active'
          AND cl.listing_status = 'available' AND cl.quantity_available > 0
          AND (cl.available_from IS NULL OR cl.available_from <= CURDATE())
          AND (cl.available_until IS NULL OR cl.available_until >= CURDATE())";

    $stmt = $link->prepare("SELECT u.full_name, cl.variety, cl.price_per_unit,
        cl.quantity_unit, cl.quantity_available, cl.description" . $available .
        " AND cl.crop_name = ? ORDER BY u.full_name, cl.listing_id DESC");
    $stmt->bind_param('s', $crop);
    $stmt->execute();
    $offers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $stmt = $link->prepare("SELECT DISTINCT cl.crop_name" . $available .
        " AND cl.crop_name <> ? AND cl.category_id IN (
            SELECT category_id FROM crop_listings WHERE crop_name = ?
            UNION SELECT category_id FROM crop_categories WHERE category_name = ?
        ) ORDER BY cl.crop_name");
    $stmt->bind_param('sss', $crop, $crop, $category);
    $stmt->execute();
    $related = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (mysqli_sql_exception $exception) {
    error_log($exception->getMessage());
    $error = true;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($crop) ?> farmer offers | AgroMarket Connect</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="icon" href="../favicon.ico">
</head>
<body>
<?php
$showMarketPrices = true;
$registrationRole = null;
require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="common-hero">
        <a href="market_prices.php">← Market Prices</a>
        <h1><?= e($crop) ?> farmer offers</h1>
    </section>
    <?php if ($error): ?>
        <p class="common-section">Farmer offers could not be loaded. Please try again.</p>
    <?php else: ?>
        <section class="common-section">
            <h2>Available offers</h2>
            <?php if (!$offers): ?><p>No farmers currently list this crop.</p><?php endif; ?>
            <div class="market-products">
                <?php foreach ($offers as $offer): ?>
                    <article class="market-card">
                        <div class="market-card-content">
                            <h3><?= e($offer['full_name']) ?></h3>
                            <p><?= e($crop) ?> <?= e($offer['variety']) ?></p>
                            <p class="market-price">₹<?= number_format($offer['price_per_unit'], 2) ?> / <?= e($offer['quantity_unit']) ?></p>
                            <p>Available: <?= e($offer['quantity_available']) ?> <?= e($offer['quantity_unit']) ?></p>
                            <p><?= e($offer['description']) ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
        <section class="common-section">
            <h2>Related crops</h2>
            <?php if (!$related): ?><p>No other crops in this category are available.</p><?php endif; ?>
            <ul>
                <?php foreach ($related as $item): ?>
                    <li><a href="?crop=<?= rawurlencode($item['crop_name']) ?>"><?= e($item['crop_name']) ?> farmer offers</a></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
