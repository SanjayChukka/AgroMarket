<?php
session_start();
require_once __DIR__ . "/includes/html.php";

$query = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
$page = max(1, min(1000000, (int) filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT)));
$pageSize = 12;
$offset = ($page - 1) * $pageSize;
$listings = [];
$hasMore = false;
$catalogError = '';

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    require_once __DIR__ . "/config/db.php";
    $sql = "SELECT cl.listing_id, cl.crop_name, cl.variety, cl.quantity_available,
                   cl.quantity_unit, cl.price_per_unit, cl.description,
                   u.full_name AS farmer_name, cc.category_name,
                   (SELECT li.image_path FROM listing_images li WHERE li.listing_id = cl.listing_id
                    ORDER BY li.is_primary DESC, li.sort_order, li.image_id LIMIT 1) AS image_path
            FROM crop_listings cl JOIN users u ON u.user_id = cl.farmer_id
            LEFT JOIN crop_categories cc ON cc.category_id = cl.category_id
            WHERE cl.listing_status = 'available' AND cl.quantity_available > 0
              AND u.role = 'farmer' AND u.account_status = 'active'
              AND (cl.available_from IS NULL OR cl.available_from <= CURDATE())
              AND (cl.available_until IS NULL OR cl.available_until >= CURDATE())";
    if ($query !== '') {
        $sql .= " AND (cl.crop_name LIKE ? ESCAPE '!' OR cl.variety LIKE ? ESCAPE '!'
                   OR u.full_name LIKE ? ESCAPE '!')";
    }
    $sql .= " ORDER BY cl.created_at DESC, cl.listing_id DESC LIMIT ? OFFSET ?";
    $stmt = $link->prepare($sql);
    $limit = $pageSize + 1;
    if ($query === '') {
        $stmt->bind_param("ii", $limit, $offset);
    } else {
        $pattern = '%' . strtr($query, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
        $stmt->bind_param("sssii", $pattern, $pattern, $pattern, $limit, $offset);
    }
    $stmt->execute();
    $listings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $hasMore = count($listings) > $pageSize;
    if ($hasMore) {
        array_pop($listings);
    }
} catch (mysqli_sql_exception $exception) {
    error_log("Homepage catalog: " . $exception->getMessage());
    $catalogError = "The crop catalog is temporarily unavailable. Please try again shortly.";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroMarket Connect</title>
    <link rel="stylesheet" href="./index.css">
</head>

<body>

    <!-- Header -->
    <header class="header">
        <div class="logo-section">
            <div class="logo">AC</div>
            <h3>AgroMarket Connect</h3>
            <span>Marketplace</span>
        </div>

        <!-- Nav Bar -->
        <nav class="navbar">
            <form class="market-search" action="index.php#marketplace" method="get" role="search">
                <input type="search" name="q" aria-label="Crop or farmer name" maxlength="120"
                       placeholder="Search crops or farmers" value="<?= e($query) ?>">
                <button type="submit">Search</button>
            </form>
            <div class="language">🌐 EN</div>
            <a href="./auth/login.php">Sign In</a>
            <a href="./auth/register.php">Sign Up</a>
        </nav>
    </header>


    <!-- Main section -->
    <main>

        <!-- Hero Section -->
        <section class="hero">

            <div class="hero-content">

                <p class="availability">
                    Now Available in 3 Languages
                </p>

                <h1>
                    Empowering Farmers,
                    <span>Feeding the Nation</span>
                </h1>

                <p class="description">
                    The first multilingual agricultural marketplace
                    connecting small farmers directly with buyers.
                    Fair prices, transparent trading, sustainable growth.
                </p>

                <div class="hero-buttons">
                    <a href="./auth/farmer_register.php">Start Selling as Farmer</a>
                    <a href="./auth/buyer_register.php">Start Buying</a>
                </div>

            </div>

            <!-- Statistics -->
            <div class="statistics">

                <div class="stat-item">
                    <h2>50,000+</h2>
                    <p>Registered Farmers</p>
                </div>

                <div class="stat-item">
                    <h2>25,000+</h2>
                    <p>Active Buyers</p>
                </div>

                <div class="stat-item">
                    <h2>₹100 Cr+</h2>
                    <p>Total Transactions</p>
                </div>

                <div class="stat-item">
                    <h2>500+</h2>
                    <p>Crops Listed Daily</p>
                </div>

            </div>

        </section>


        <!-- Why Choose Us Section -->
        <section class="features">

            <div class="section-heading">
                <h2>Why Choose AgroMarket Connect?</h2>
                <p>
                    Built specifically for Indian agriculture with
                    features that matter to farmers and buyers.
                </p>
            </div>

            <div class="features-container">

                <div class="feature-card">
                    <div class="feature-icon">Icon</div>
                    <h3>Direct Farm-to-Market</h3>
                    <p>
                        Connect farmers directly with buyers
                        without unnecessary intermediaries.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">Icon</div>
                    <h3>Live Market Prices</h3>
                    <p>
                        Access transparent market prices
                        and make better trading decisions.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">Icon</div>
                    <h3>Secure Transactions</h3>
                    <p>
                        Trade confidently with secure
                        transactions and reliable connections.
                    </p>
                </div>

            </div>

        </section>

        <section class="featured-crops" id="marketplace">
            <div class="section-heading">
                <h2><?= $query === '' ? 'Available Crops' : 'Search Results' ?></h2>
                <?php if ($query !== ''): ?>
                    <p>Listings matching “<?= e($query) ?>” · <a href="index.php#marketplace">Clear search</a></p>
                <?php endif; ?>
            </div>
            <?php if ($catalogError !== ''): ?>
                <p><?= e($catalogError) ?></p>
            <?php elseif (!$listings): ?>
                <p><?= $query === '' ? 'No available crops on this page.' : 'No matches on this page. Try another crop or farmer name.' ?></p>
            <?php else: ?>
                <div class="crops-container">
                <?php foreach ($listings as $listing): ?>
                    <article class="crop-card">
                        <div class="crop-image">
                            <?php if (!empty($listing['image_path'])): ?>
                                <img src="<?= e($listing['image_path']) ?>" alt="<?= e($listing['crop_name']) ?>" loading="lazy">
                            <?php else: ?>
                                No photo available
                            <?php endif; ?>
                        </div>
                        <h3><?= e($listing['crop_name']) ?></h3>
                        <p><?= e($listing['farmer_name']) ?></p>
                        <strong>₹<?= number_format((float) $listing['price_per_unit'], 2) ?> / <?= e($listing['quantity_unit']) ?></strong>
                        <details>
                            <summary>View details</summary>
                            <p>Available: <?= e($listing['quantity_available']) ?> <?= e($listing['quantity_unit']) ?></p>
                            <?php foreach (['variety', 'category_name', 'description'] as $field): ?>
                                <?php if (!empty($listing[$field])): ?><p><?= e($listing[$field]) ?></p><?php endif; ?>
                            <?php endforeach; ?>
                        </details>
                        <?php if (($_SESSION['user_role'] ?? '') === 'buyer'): ?>
                            <a href="buyer/place_order.php?listing_id=<?= (int) $listing['listing_id'] ?>">Place order</a>
                        <?php else: ?>
                            <a href="auth/login.php?listing_id=<?= (int) $listing['listing_id'] ?>">Sign in as buyer to order</a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ($catalogError === '' && ($page > 1 || $hasMore)): ?>
                <nav class="market-pages" aria-label="Crop result pages">
                    <?php if ($page > 1): ?><a href="?<?= e(http_build_query(['q' => $query, 'page' => $page - 1])) ?>#marketplace">Previous</a><?php endif; ?>
                    <span>Page <?= $page ?></span>
                    <?php if ($hasMore): ?><a href="?<?= e(http_build_query(['q' => $query, 'page' => $page + 1])) ?>#marketplace">Next</a><?php endif; ?>
                </nav>
            <?php endif; ?>
        </section>

        <!-- How It Works Section -->
        <section class="how-it-works">

            <div class="section-heading">
                <h2>How AgroMarket Connect Works</h2>
                <p>A simple process connecting farmers and buyers.</p>
            </div>

            <div class="process-container">

                <div class="process-card">
                    <div class="process-icon">01</div>
                    <h3>Farmer Registration</h3>
                    <p>Farmers register and create their profiles.</p>
                </div>

                <div class="process-card">
                    <div class="process-icon">02</div>
                    <h3>List Your Crops</h3>
                    <p>Farmers add crop details, quantity and prices.</p>
                </div>

                <div class="process-card">
                    <div class="process-icon">03</div>
                    <h3>Connect with Buyers</h3>
                    <p>Buyers explore available crops and contact farmers.</p>
                </div>

                <div class="process-card">
                    <div class="process-icon">04</div>
                    <h3>Complete Trading</h3>
                    <p>Farmers and buyers coordinate and complete transactions.</p>
                </div>

            </div>
        </section>


        <!-- Call To Action Section -->
        <section class="join-section">

            <div class="join-content">
                <h2>Grow Together with AgroMarket Connect</h2>

                <p>
                    Whether you are a farmer looking for better markets
                    or a buyer searching for quality agricultural products,
                    AgroMarket Connect brings you together.
                </p>

                <div class="join-buttons">
                    <a href="./auth/farmer_register.php">Join as Farmer</a>
                    <a href="./auth/buyer_register.php">Join as Buyer</a>
                </div>
            </div>

        </section>

    </main>

    <footer class="footer">

        <!-- About AgroMarket -->
        <div class="footer-section about">
            <h3 class="foot-head">ABOUT AGROMARKET</h3>
            <ul class="footer-links">
                <li><a href="./common/about.php">About Us</a></li>
                <li><a href="./common/about.php#mission-vision">Our Mission</a></li>
                <li><a href="./common/how_it_works.php">How It Works</a></li>
                <li><a href="./common/partners.php">Our Partners</a></li>
                <li><a href="./common/contact.php">Contact Us</a></li>
            </ul>
        </div>

        <!-- Farmers Section -->
        <div class="footer-section farmers">
            <h3 class="foot-head">FOR FARMERS</h3>
            <ul class="footer-links">
                <li><a href="./auth/farmer_register.php">Register as Farmer</a></li>
                <li><a href="./auth/login.php">List Your Crops</a></li>
                <li><a href="./auth/login.php">Manage Listings</a></li>
                <li><a href="./common/market_prices.php">Market Price Updates</a></li>
                <li><a href="./common/farmer_guidelines.php">Farmer Guidelines</a></li>
            </ul>
        </div>

        <!-- Buyers Section -->
        <div class="footer-section buyers">
            <h3 class="foot-head">FOR BUYERS</h3>
            <ul class="footer-links">
                <li><a href="./auth/login.php">Browse Crops</a></li>
                <li><a href="./auth/login.php">Find Farmers</a></li>
                <li><a href="./auth/login.php">Place Orders</a></li>
                <li><a href="./auth/login.php">Track Orders</a></li>
                <li><a href="./common/buyer_guidelines.php">Buyer Guidelines</a></li>
            </ul>
        </div>

        <!-- Support and Policies -->
        <div class="footer-section support">
            <h3 class="foot-head">SUPPORT & POLICIES</h3>
            <ul class="footer-links">
                <li><a href="./common/help.php">Help Center</a></li>
                <li><a href="./common/faq.php">FAQs</a></li>
                <li><a href="./common/terms.php">Terms & Conditions</a></li>
                <li><a href="./common/privacy.php">Privacy Policy</a></li>
                <li><a href="./common/cancellation_policy.php">Cancellation Policy</a></li>
                <li><a href="./common/grievance.php">Grievance Redressal</a></li>
            </ul>
        </div>

        <!-- Contact Section -->
        <div class="footer-section contact">
            <h3 class="foot-head">CONTACT US</h3>

            <address>
                AgroMarket Connect<br>
                Hyderabad, Telangana, India
            </address>

            <p>Email: <a href="mailto:your-email@example.com">
                    your-email@example.com
                </a></p>

            <p>Phone: <a href="tel:+910000000000">
                    +91 XXXXX XXXXX
                </a></p>

            <!-- Social Media -->
            <div class="social">
                <a href="#" aria-label="Facebook">Facebook</a>
                <a href="#" aria-label="Instagram">Instagram</a>
                <a href="#" aria-label="LinkedIn">LinkedIn</a>
                <a href="#" aria-label="YouTube">YouTube</a>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <p>&copy; 2026 AgroMarket Connect. All Rights Reserved.</p>
            <p>Empowering Farmers, Feeding the Nation.</p>
        </div>

    </footer>

</body>

</html>