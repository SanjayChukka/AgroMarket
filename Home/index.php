
<?php

    session_start();

    require_once __DIR__ . "/includes/html.php";

    require __DIR__ . "/includes/home-languages.php";
    require __DIR__ . "/includes/review-actions.php";

    // Initialize search and pagination variables.
    $query = is_string($_GET['q'] ?? null)
    ? trim($_GET['q'])
    : '';

    $page = max(
    1,
    min(
        1000000,
        (int) filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT)
    )
    );

    $pageSize = 12;
    $offset   = ($page - 1) * $pageSize;

    $listings     = [];
    $hasMore      = false;
    $catalogError = '';

    try {

    // Establish the database connection.
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    require_once __DIR__ . "/config/db.php";

    // Fetch available crop listings from active farmers.
    $sql = "
        SELECT
            cl.listing_id,
            cl.crop_name,
            cl.variety,
            cl.quantity_available,
            cl.quantity_unit,
            cl.price_per_unit,
            cl.description,
            u.full_name AS farmer_name,
            cc.category_name,

            (
                SELECT li.image_path
                FROM listing_images li
                WHERE li.listing_id = cl.listing_id
                ORDER BY
                    li.is_primary DESC,
                    li.sort_order,
                    li.image_id
                LIMIT 1
            ) AS image_path

        FROM crop_listings cl

        JOIN users u
            ON u.user_id = cl.farmer_id

        LEFT JOIN crop_categories cc
            ON cc.category_id = cl.category_id

        WHERE cl.listing_status = 'available'
            AND cl.quantity_available > 0
            AND u.role = 'farmer'
            AND u.account_status = 'active'

            AND (
                cl.available_from IS NULL
                OR cl.available_from <= CURDATE()
            )

            AND (
                cl.available_until IS NULL
                OR cl.available_until >= CURDATE()
            )
    ";

    // Apply search conditions when a search query is provided.
    if ($query !== '') {

        $sql .= "
            AND (
                cl.crop_name LIKE ? ESCAPE '!'
                OR cl.variety LIKE ? ESCAPE '!'
                OR u.full_name LIKE ? ESCAPE '!'
            )
        ";
    }

    // Apply sorting and pagination.
    $sql .= "
        ORDER BY
            cl.created_at DESC,
            cl.listing_id DESC
        LIMIT ? OFFSET ?
    ";

    $stmt  = $link->prepare($sql);

    $limit = $pageSize + 1;

    // Bind search and pagination parameters.
    if ($query === '') {

        $stmt->bind_param(
            "ii",
            $limit,
            $offset
        );

    } else {

        $pattern = '%' . strtr(
            $query,
            [
                '!' => '!!',
                '%' => '!%',
                '_' => '!_',
            ]
        ) . '%';

        $stmt->bind_param(
            "sssii",
            $pattern,
            $pattern,
            $pattern,
            $limit,
            $offset
        );
    }

    // Execute the query and retrieve crop listings.
    $stmt->execute();

    $listings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    // Determine whether another page of listings is available.
    $hasMore = count($listings) > $pageSize;

    if ($hasMore) {
        array_pop($listings);
    }

    } catch (mysqli_sql_exception $exception) {

    // Log database errors.
    error_log(
        "Homepage catalog: " . $exception->getMessage()
    );

    $catalogError = "The crop catalog is temporarily unavailable. Please try again shortly.";
    }

?>

<!DOCTYPE html>
<html lang="<?php echo e($homeLanguage); ?>">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AgroMarket Connect</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="./index.css">
    <link rel="stylesheet" href="./styles/home-language.css">

    <!-- Favicon -->
    <link rel="icon" href="./favicon.ico" type="image/x-icon">

</head>

<body>

    <!-- Header -->
    <header class="header">

        <!-- Logo Section -->
        <div class="logo-section">

            <a
                class="agro-home"
                href="./index.php"
                aria-label="AgroMarket Connect home"
            >

                <img
                    class="brand-mark"
                    src="./images/brand/agromarket-icon.png"
                    width="64"
                    height="64"
                    alt=""
                >

                <span class="home-brand-name">
                    AgroMarket Connect
                </span>

            </a>

            <span>
                <?php echo e($t('Marketplace')); ?>
            </span>

        </div>


        <!-- Navigation Bar -->
        <nav class="navbar">

            <!-- Market Prices -->
            <a
                class="market-prices-button"
                href="./common/market_prices.php"
            >
                <?php echo e($t('Market Prices')); ?>
            </a>


            <!-- Crop and Farmer Search -->
            <form
                class="market-search"
                action="index.php#marketplace"
                method="get"
                role="search"
            >

                <input
                    type="search"
                    name="q"
                    aria-label="<?php echo e($t('Crop or farmer name')); ?>"
                    maxlength="120"
                    placeholder="<?php echo e($t('Search crops or farmers')); ?>"
                    value="<?php echo e($query); ?>"
                >

                <button type="submit">
                    <?php echo e($t('Search')); ?>
                </button>

            </form>


            <!-- Language Selection -->
            <form class="language" method="post">

                <label for="home-language">

                    <span aria-hidden="true">🌐</span>

                    <?php echo e($t('Language')); ?>

                </label>

                <select id="home-language" name="home_language">

                    <?php foreach ($homeLanguages as $code => $name): ?>

                        <option
                            value="<?php echo e($code); ?>"
                            lang="<?php echo e($code); ?>"
                            <?php echo $code === $homeLanguage
                                    ? 'selected'
                                    : '';
                            ?>
                        >
                            <?php echo e($name); ?>
                        </option>

                    <?php endforeach; ?>

                </select>

                <noscript>

                    <button type="submit">
                        <?php echo e($t('Apply')); ?>
                    </button>

                </noscript>

            </form>


            <!-- Authentication Links -->
            <a href="./auth/login.php">
                <?php echo e($t('Sign In')); ?>
            </a>

            <a href="./auth/register.php">
                <?php echo e($t('Sign Up')); ?>
            </a>

        </nav>

    </header>


    <!-- Main Section -->
    <main>

        <!-- Hero Section -->
        <section class="hero">

            <div class="hero-content">

                <!-- Language Availability -->
                <p class="availability">
                    <?php echo e($t('Now Available in 3 Languages')); ?>
                </p>


                <!-- Main Heading -->
                <h1>

                    <?php echo e($t('Empowering Farmers,')); ?>

                    <span>
                        <?php echo e($t('Feeding the Nation')); ?>
                    </span>

                </h1>


                <!-- Hero Description -->
                <p class="description">

                    <?php echo e($t(
                            'The first multilingual agricultural marketplace connecting small farmers directly with buyers. Fair prices, transparent trading, sustainable growth.'
                    )); ?>

                </p>


                <!-- Registration Buttons -->
                <div class="hero-buttons">

                    <a href="./auth/farmer_register.php">
                        <?php echo e($t('Start Selling as Farmer')); ?>
                    </a>

                    <a href="./auth/buyer_register.php">
                        <?php echo e($t('Start Buying')); ?>
                    </a>

                </div>

            </div>


            <!-- Hero Image -->
            <img
                class="hero-photo"
                src="images/harvest.webp"
                width="1440"
                height="960"
                alt="<?php echo e($t('Hands gathering leafy vegetables in a woven harvest basket')); ?>"
                fetchpriority="high"
            >


            <!-- Statistics -->
            <div class="statistics">

                <div class="stat-item">

                    <h2>50,000+</h2>

                    <p>
                        <?php echo e($t('Registered Farmers')); ?>
                    </p>

                </div>


                <div class="stat-item">

                    <h2>25,000+</h2>

                    <p>
                        <?php echo e($t('Active Buyers')); ?>
                    </p>

                </div>


                <div class="stat-item">

                    <h2>
                        <?php echo e($t('₹100 Cr+')); ?>
                    </h2>

                    <p>
                        <?php echo e($t('Total Transactions')); ?>
                    </p>

                </div>


                <div class="stat-item">

                    <h2>500+</h2>

                    <p>
                        <?php echo e($t('Crops Listed Daily')); ?>
                    </p>

                </div>

            </div>

        </section>


        <!-- Why Choose Us Section -->
        <section class="features">

            <div class="section-heading">

                <h2>
                    <?php echo e($t('Why Choose AgroMarket Connect?')); ?>
                </h2>

                <p>
                    <?php echo e($t(
                            'Built specifically for Indian agriculture with features that matter to farmers and buyers.'
                    )); ?>
                </p>

            </div>


            <div class="features-container">

                <!-- Direct Farm-to-Market -->
                <div class="feature-card">

                    <h3>
                        <?php echo e($t('Direct Farm-to-Market')); ?>
                    </h3>

                    <p>
                        <?php echo e($t(
                                'Connect farmers directly with buyers without unnecessary intermediaries.'
                        )); ?>
                    </p>

                </div>


                <!-- Market Prices -->
                <div class="feature-card">

                    <h3>
                        <?php echo e($t('Market Prices')); ?>
                    </h3>

                    <p>
                        <?php echo e($t(
                                'Access transparent market prices and make better trading decisions.'
                        )); ?>
                    </p>

                    <a
                        class="price-card-link"
                        href="./common/market_prices.php"
                    >
                        <?php echo e($t('View market prices')); ?>
                    </a>

                </div>


                <!-- Secure Transactions -->
                <div class="feature-card">

                    <h3>
                        <?php echo e($t('Secure Transactions')); ?>
                    </h3>

                    <p>
                        <?php echo e($t(
                                'Trade confidently with secure transactions and reliable connections.'
                        )); ?>
                    </p>

                </div>

            </div>

        </section>


        <!-- Available Crops and Search Results -->
        <section class="featured-crops" id="marketplace">

            <div class="section-heading">

                <h2>
                    <?php echo e($t(
                            $query === ''
                                ? 'Available Crops'
                                : 'Search Results'
                    )); ?>
                </h2>


                <!-- Display the Active Search Query -->
                <?php if ($query !== ''): ?>

                    <p>

                        <?php echo e(sprintf(
                                $t('Listings matching “%s”'),
                                $query
                        )); ?>

                        ·

                        <a href="index.php#marketplace">
                            <?php echo e($t('Clear search')); ?>
                        </a>

                    </p>

                <?php endif; ?>

            </div>


            <!-- Display Catalog Error -->
            <?php if ($catalogError !== ''): ?>

                <p>
                    <?php echo e($t($catalogError)); ?>
                </p>

            <?php elseif (! $listings): ?>

                <!-- Display Message When No Listings Are Available -->
                <p>
                    <?php echo e($t(
                            $query === ''
                                ? 'No available crops on this page.'
                                : 'No matches on this page. Try another crop or farmer name.'
                    )); ?>
                </p>

            <?php else: ?>

                <!-- Crop Listing Cards -->
                <div class="crops-container">

                    <?php foreach ($listings as $listing): ?>

                        <article class="crop-card">

                            <!-- Crop Image -->
                            <div class="crop-image">

                                <?php if (! empty($listing['image_path'])): ?>

                                    <img
                                        src="<?php echo e($listing['image_path']); ?>"
                                        alt="<?php echo e($listing['crop_name']); ?>"
                                        loading="lazy"
                                    >

                                <?php else: ?>

                                    <?php echo e($t('No photo available')); ?>

                                <?php endif; ?>

                            </div>


                            <!-- Crop Name -->
                            <h3>
                                <?php echo e($listing['crop_name']); ?>
                            </h3>


                            <!-- Farmer Name -->
                            <p>
                                <?php echo e($listing['farmer_name']); ?>
                            </p>


                            <!-- Crop Price -->
                            <strong>

                                ₹<?php echo number_format(
                                           (float) $listing['price_per_unit'],
                                           2
                                   ); ?>

                                /

                                <?php echo e($listing['quantity_unit']); ?>

                            </strong>


                            <!-- Expandable Crop Details -->
                            <details>

                                <summary>
                                    <?php echo e($t('View details')); ?>
                                </summary>

                                <p>

                                    <?php echo e($t('Available:')); ?>

                                    <?php echo e(
                                            $listing['quantity_available']
                                    ); ?>

                                    <?php echo e(
                                            $listing['quantity_unit']
                                    ); ?>

                                </p>


                                <!-- Additional Crop Information -->
                                <?php foreach (
                                        ['variety', 'category_name', 'description'] as $field
                                ): ?>

                                    <?php if (! empty($listing[$field])): ?>

                                        <p>
                                            <?php echo e($listing[$field]); ?>
                                        </p>

                                    <?php endif; ?>

                                <?php endforeach; ?>

                            </details>


                            <!-- Role-based Order Action -->
                            <?php if (
                                    ($_SESSION['user_role'] ?? '') === 'buyer'
                            ): ?>

                                <a
                                    class="place-order-button"
                                    href="buyer/place_order.php?listing_id=<?php echo (int) $listing['listing_id']; ?>"
                                >
                                    <?php echo e($t('Place order')); ?>
                                </a>

                            <?php else: ?>

                                <a href="auth/login.php?listing_id=<?php echo (int) $listing['listing_id']; ?>">

                                    <?php echo e($t(
                                            'Sign in as buyer to order'
                                    )); ?>

                                </a>

                            <?php endif; ?>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>


            <!-- Catalog Pagination -->
            <?php if (
                    $catalogError === '' &&
                    ($page > 1 || $hasMore)
            ): ?>

                <nav
                    class="market-pages"
                    aria-label="<?php echo e($t('Crop result pages')); ?>"
                >

                    <!-- Previous Page -->
                    <?php if ($page > 1): ?>

                        <a href="?<?php echo e(http_build_query([
                                          'q'    => $query,
                                      'page' => $page - 1,
                                  ])); ?>#marketplace">

                            <?php echo e($t('Previous')); ?>

                        </a>

                    <?php endif; ?>


                    <!-- Current Page Number -->
                    <span>
                        <?php echo e(sprintf(
                                $t('Page %s'),
                                $page
                        )); ?>
                    </span>


                    <!-- Next Page -->
                    <?php if ($hasMore): ?>

                        <a href="?<?php echo e(http_build_query([
                                          'q'    => $query,
                                      'page' => $page + 1,
                                  ])); ?>#marketplace">

                            <?php echo e($t('Next')); ?>

                        </a>

                    <?php endif; ?>

                </nav>

            <?php endif; ?>

        </section>


        <!-- Community Layout -->
        <div class="community-layout">

            <!-- How It Works Section -->
            <section class="how-it-works">

                <div class="section-heading">

                    <h2>
                        <?php echo e($t(
                                'How AgroMarket Connect Works'
                        )); ?>
                    </h2>

                    <p>
                        <?php echo e($t(
                                'A simple process connecting farmers and buyers.'
                        )); ?>
                    </p>

                </div>


                <div class="process-container">

                    <!-- Step 1: Farmer Registration -->
                    <div class="process-card">

                        <div class="process-icon">01</div>

                        <h3>
                            <?php echo e($t('Farmer Registration')); ?>
                        </h3>

                        <p>
                            <?php echo e($t(
                                    'Farmers register and create their profiles.'
                            )); ?>
                        </p>

                    </div>


                    <!-- Step 2: List Crops -->
                    <div class="process-card">

                        <div class="process-icon">02</div>

                        <h3>
                            <?php echo e($t('List Your Crops')); ?>
                        </h3>

                        <p>
                            <?php echo e($t(
                                    'Farmers add crop details, quantity and prices.'
                            )); ?>
                        </p>

                    </div>


                    <!-- Step 3: Connect with Buyers -->
                    <div class="process-card">

                        <div class="process-icon">03</div>

                        <h3>
                            <?php echo e($t('Connect with Buyers')); ?>
                        </h3>

                        <p>
                            <?php echo e($t(
                                    'Buyers explore available crops and contact farmers.'
                            )); ?>
                        </p>

                    </div>


                    <!-- Step 4: Complete Trading -->
                    <div class="process-card">

                        <div class="process-icon">04</div>

                        <h3>
                            <?php echo e($t('Complete Trading')); ?>
                        </h3>

                        <p>
                            <?php echo e($t(
                                    'Farmers and buyers coordinate and complete transactions.'
                            )); ?>
                        </p>

                    </div>

                </div>

            </section>


            <!-- Community Reviews -->
            <?php require __DIR__ . '/includes/review-aside.php'; ?>

        </div>


        <!-- Call To Action Section -->
        <section class="join-section">

            <div class="join-content">

                <h2>
                    <?php echo e($t(
                            'Grow Together with AgroMarket Connect'
                    )); ?>
                </h2>

                <p>
                    <?php echo e($t(
                            'Whether you are a farmer looking for better markets or a buyer searching for quality agricultural products, AgroMarket Connect brings you together.'
                    )); ?>
                </p>


                <!-- Join Buttons -->
                <div class="join-buttons">

                    <a href="./auth/farmer_register.php">
                        <?php echo e($t('Join as Farmer')); ?>
                    </a>

                    <a href="./auth/buyer_register.php">
                        <?php echo e($t('Join as Buyer')); ?>
                    </a>

                </div>

            </div>

        </section>

    </main>


    <!-- Footer -->
    <footer class="footer">

        <!-- About AgroMarket -->
        <div class="footer-section about">

            <h3 class="foot-head">
                <?php echo e($t('ABOUT AGROMARKET')); ?>
            </h3>

            <ul class="footer-links">

                <li>
                    <a href="./common/about.php">
                        <?php echo e($t('About Us')); ?>
                    </a>
                </li>

                <li>
                    <a href="./common/about.php#mission-vision">
                        <?php echo e($t('Our Mission')); ?>
                    </a>
                </li>

                <li>
                    <a href="./common/how_it_works.php">
                        <?php echo e($t('How It Works')); ?>
                    </a>
                </li>

                <li>
                    <a href="./common/partners.php">
                        <?php echo e($t('Our Partners')); ?>
                    </a>
                </li>

                <li>
                    <a href="./common/contact.php">
                        <?php echo e($t('Contact Us')); ?>
                    </a>
                </li>

            </ul>

        </div>


        <!-- Farmers Section -->
        <div class="footer-section farmers">

            <h3 class="foot-head">
                <?php echo e($t('FOR FARMERS')); ?>
            </h3>

            <ul class="footer-links">

                <li>
                    <a href="./auth/farmer_register.php">
                        <?php echo e($t('Register as Farmer')); ?>
                    </a>
                </li>

                <li>
                    <a href="./auth/login.php">
                        <?php echo e($t('List Your Crops')); ?>
                    </a>
                </li>

                <li>
                    <a href="./auth/login.php">
                        <?php echo e($t('Manage Listings')); ?>
                    </a>
                </li>

                <li>
                    <a href="./common/market_prices.php">
                        <?php echo e($t('Market Prices')); ?>
                    </a>
                </li>

                <li>
                    <a href="./common/farmer_guidelines.php">
                        <?php echo e($t('Farmer Guidelines')); ?>
                    </a>
                </li>

            </ul>

        </div>


        <!-- Buyers Section -->
        <div class="footer-section buyers">

            <h3 class="foot-head">
                <?php echo e($t('FOR BUYERS')); ?>
            </h3>

            <ul class="footer-links">

                <li>
                    <a href="./auth/login.php">
                        <?php echo e($t('Browse Crops')); ?>
                    </a>
                </li>

                <li>
                    <a href="./auth/login.php">
                        <?php echo e($t('Find Farmers')); ?>
                    </a>
                </li>

                <li>
                    <a href="./auth/login.php">
                        <?php echo e($t('Place Orders')); ?>
                    </a>
                </li>

                <li>
                    <a href="./auth/login.php">
                        <?php echo e($t('Track Orders')); ?>
                    </a>
                </li>

                <li>
                    <a href="./common/buyer_guidelines.php">
                        <?php echo e($t('Buyer Guidelines')); ?>
                    </a>
                </li>

            </ul>

        </div>


        <!-- Support and Policies -->
        <div class="footer-section support">

            <h3 class="foot-head">
                <?php echo e($t('SUPPORT & POLICIES')); ?>
            </h3>

            <ul class="footer-links">

                <li>
                    <a href="./common/help.php">
                        <?php echo e($t('Help Center')); ?>
                    </a>
                </li>

                <li>
                    <a href="./common/faq.php">
                        <?php echo e($t('FAQs')); ?>
                    </a>
                </li>

                <li>
                    <a href="./common/terms.php">
                        <?php echo e($t('Terms & Conditions')); ?>
                    </a>
                </li>

                <li>
                    <a href="./common/privacy.php">
                        <?php echo e($t('Privacy Policy')); ?>
                    </a>
                </li>

                <li>
                    <a href="./common/cancellation_policy.php">
                        <?php echo e($t('Cancellation Policy')); ?>
                    </a>
                </li>

                <li>
                    <a href="./common/grievance.php">
                        <?php echo e($t('Grievance Redressal')); ?>
                    </a>
                </li>

            </ul>

        </div>


        <!-- Contact Section -->
        <div class="footer-section contact">

            <h3 class="foot-head">
                <?php echo e($t('CONTACT US')); ?>
            </h3>


            <!-- Business Address -->
            <address>

                AgroMarket Connect<br>

                <?php echo e($t('Hyderabad, Telangana, India')); ?>

            </address>


            <!-- Contact Email -->
            <p>

                <?php echo e($t('Email:')); ?>

                <a href="mailto:support@agromarketconnect.com">
                    support@agromarketconnect.com
                </a>

            </p>


            <!-- Contact Phone -->
            <p>

                <?php echo e($t('Phone:')); ?>

                <a href="tel:+917032412044">
                    +91 7032412044
                </a>

            </p>


            <!-- Social Media Links -->
            <div class="social">

                <!-- Facebook -->
                <a
                    href="https://www.facebook.com"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Facebook"
                >
                    <img src="icons/facebook.svg" alt="Facebook">
                </a>


                <!-- Instagram -->
                <a
                    href="https://www.instagram.com"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Instagram"
                >
                    <img src="icons/instagram.svg" alt="Instagram">
                </a>


                <!-- LinkedIn -->
                <a
                    href="https://www.linkedin.com"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="LinkedIn"
                >
                    <img src="icons/linkedin.svg" alt="LinkedIn">
                </a>


                <!-- YouTube -->
                <a
                    href="https://www.youtube.com"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="YouTube"
                >
                    <img src="icons/youtube.svg" alt="YouTube">
                </a>

            </div>

        </div>


        <!-- Footer Bottom -->
        <div class="footer-bottom">

            <p>

                &copy; 2026 AgroMarket Connect.

                <?php echo e($t('All Rights Reserved.')); ?>

            </p>

            <p>
                <?php echo e($t(
                        'Empowering Farmers, Feeding the Nation.'
                )); ?>
            </p>

        </div>

    </footer>


    <!-- Language Selection JavaScript -->
    <script>

        document.getElementById('home-language').addEventListener('change', function () {

            this.form.requestSubmit();

        });

    </script>

</body>

</html>
