
<?php

require_once __DIR__ . '/../includes/html.php';


// Retrieve and validate the selected crop.
$crop = is_string($_GET['crop'] ?? null)
    ? trim($_GET['crop'])
    : '';

if ($crop === '' || strlen($crop) > 600) {

    header('Location: market_prices.php');

    exit;
}


// Define crop categories.
$categories = [
    'tomato' => 'Vegetables',
    'onion'  => 'Vegetables',
    'potato' => 'Vegetables',
    'rice'   => 'Cereals & Millets',
    'maize'  => 'Cereals & Millets',
    'mango'  => 'Fruits'
];

$category = $categories[strtolower($crop)] ?? '';

$error = false;


try {

    // Establish the database connection.
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    require __DIR__ . '/../config/db.php';


    // Define common conditions for available crop listings.
    $available = "
        FROM crop_listings cl

        JOIN users u
            ON u.user_id = cl.farmer_id

        WHERE u.role = 'farmer'
            AND u.account_status = 'active'
            AND cl.listing_status = 'available'
            AND cl.quantity_available > 0

            AND (
                cl.available_from IS NULL
                OR cl.available_from <= CURDATE()
            )

            AND (
                cl.available_until IS NULL
                OR cl.available_until >= CURDATE()
            )
    ";


    // Fetch available farmer offers for the selected crop.
    $stmt = $link->prepare(
        "SELECT
            u.full_name,
            cl.variety,
            cl.price_per_unit,
            cl.quantity_unit,
            cl.quantity_available,
            cl.description"
        . $available .
        " AND cl.crop_name = ?

        ORDER BY
            u.full_name,
            cl.listing_id DESC"
    );

    $stmt->bind_param('s', $crop);

    $stmt->execute();

    $offers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt->close();


    // Fetch related crops from the same category.
    $stmt = $link->prepare(
        "SELECT DISTINCT
            cl.crop_name"
        . $available .
        " AND cl.crop_name <> ?

        AND cl.category_id IN (

            SELECT category_id
            FROM crop_listings
            WHERE crop_name = ?

            UNION

            SELECT category_id
            FROM crop_categories
            WHERE category_name = ?
        )

        ORDER BY cl.crop_name"
    );

    $stmt->bind_param(
        'sss',
        $crop,
        $crop,
        $category
    );

    $stmt->execute();

    $related = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

} catch (mysqli_sql_exception $exception) {

    // Log database errors.
    error_log($exception->getMessage());

    $error = true;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        <?php echo e($crop); ?> farmer offers | AgroMarket Connect
    </title>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="css/common.css">

    <!-- Favicon -->
    <link rel="icon" href="../favicon.ico">

</head>

<body>

    <!-- Common Header -->
    <?php

    $showMarketPrices = true;

    $registrationRole = null;

    require __DIR__ . '/includes/header.php';

    ?>


    <main>

        <!-- Page Heading -->
        <section class="common-hero">

            <a href="market_prices.php">
                ← Market Prices
            </a>

            <h1>
                <?php echo e($crop); ?> farmer offers
            </h1>

        </section>


        <!-- Display Database Error -->
        <?php if ($error): ?>

            <p class="common-section">
                Farmer offers could not be loaded. Please try again.
            </p>

        <?php else: ?>


            <!-- Available Farmer Offers -->
            <section class="common-section">

                <h2>Available offers</h2>


                <!-- Display Message When No Offers Are Available -->
                <?php if (!$offers): ?>

                    <p>
                        No farmers currently list this crop.
                    </p>

                <?php endif; ?>


                <!-- Farmer Offer Cards -->
                <div class="market-products">

                    <?php foreach ($offers as $offer): ?>

                        <article class="market-card">

                            <div class="market-card-content">

                                <!-- Farmer Name -->
                                <h3>
                                    <?php echo e($offer['full_name']); ?>
                                </h3>


                                <!-- Crop Name and Variety -->
                                <p>
                                    <?php echo e($crop); ?>
                                    <?php echo e($offer['variety']); ?>
                                </p>


                                <!-- Crop Price -->
                                <p class="market-price">

                                    ₹<?php echo number_format(
                                        $offer['price_per_unit'],
                                        2
                                    ); ?>

                                    /

                                    <?php echo e($offer['quantity_unit']); ?>

                                </p>


                                <!-- Available Quantity -->
                                <p>

                                    Available:

                                    <?php echo e(
                                        $offer['quantity_available']
                                    ); ?>

                                    <?php echo e(
                                        $offer['quantity_unit']
                                    ); ?>

                                </p>


                                <!-- Crop Description -->
                                <p>
                                    <?php echo e($offer['description']); ?>
                                </p>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            </section>


            <!-- Related Crops Section -->
            <section class="common-section">

                <h2>Related crops</h2>


                <!-- Display Message When No Related Crops Are Available -->
                <?php if (!$related): ?>

                    <p>
                        No other crops in this category are available.
                    </p>

                <?php endif; ?>


                <!-- Related Crop Links -->
                <ul>

                    <?php foreach ($related as $item): ?>

                        <li>

                            <a href="?crop=<?php echo rawurlencode($item['crop_name']); ?>">

                                <?php echo e($item['crop_name']); ?>
                                farmer offers

                            </a>

                        </li>

                    <?php endforeach; ?>

                </ul>

            </section>

        <?php endif; ?>

    </main>

</body>

</html>
