<?php
    $priceRows    = [];
    $sellerQuotes = false;
    if (isset($link) && $link instanceof mysqli) {
    try {
        // Some installations also store a price_unit or quantity_unit on each report.
        $result = $link->query("SELECT * FROM market_prices WHERE date <= CURDATE()
            AND min_price IS NOT NULL AND max_price IS NOT NULL
            ORDER BY date DESC, price_id DESC LIMIT 4");
        $priceRows = $result->fetch_all(MYSQLI_ASSOC);
    } catch (mysqli_sql_exception $exception) {
        error_log('Market price reports: ' . $exception->getMessage());
    }
    if (! $priceRows) {
        $sellerQuotes = true;
        try {
            $result = $link->query("SELECT cl.crop_name, cl.price_per_unit, cl.quantity_unit, cl.created_at AS date
                FROM crop_listings cl JOIN users u ON u.user_id = cl.farmer_id
                WHERE cl.listing_status='available' AND cl.quantity_available > 0
                  AND u.role='farmer' AND u.account_status='active'
                  AND (cl.available_from IS NULL OR cl.available_from <= CURDATE())
                  AND (cl.available_until IS NULL OR cl.available_until >= CURDATE())
                ORDER BY cl.created_at DESC, cl.listing_id DESC LIMIT 4");
            $priceRows = $result->fetch_all(MYSQLI_ASSOC);
        } catch (mysqli_sql_exception $exception) {
            error_log('Market listing prices: ' . $exception->getMessage());
        }
    }
    }
?>
<section id="market-prices" class="market-board" aria-labelledby="market-price-title">
    <h2 id="market-price-title"><?php echo e($t('Market Price Updates')) ?></h2>
    <?php if (! $priceRows): ?>
        <p><?php echo e($t('No prices have been published yet.')) ?></p>
    <?php else: ?>
        <p><?php echo e($t($sellerQuotes ? 'Latest asking prices from farmer listings' : 'Latest reported market rates')) ?></p>
        <ul>
        <?php foreach ($priceRows as $row):
                $unit      = $row['price_unit'] ?? $row['quantity_unit'] ?? $row['unit'] ?? '';
                $unitLabel = $unit !== '' ? '/ ' . $unit : $t('Unit unspecified');
                $date      = substr($row['date'], 0, 10);
        ?>
            <li><h3><?php echo e($row['crop_name']) ?></h3>
                <strong>₹<?php echo number_format((float) ($sellerQuotes ? $row['price_per_unit'] : $row['min_price']), 2) ?><?php if (! $sellerQuotes && $row['max_price'] != $row['min_price']): ?> – ₹<?php echo number_format((float) $row['max_price'], 2) ?><?php endif; ?></strong>
                <span><?php echo e($unitLabel) ?></span>
                <small><?php if (! empty($row['location'])): ?><?php echo e($row['location']) ?> · <?php endif; ?><time datetime="<?php echo e($date) ?>"><?php echo e($date) ?></time></small>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
