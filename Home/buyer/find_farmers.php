
<?php
    session_start();

    require_once __DIR__ . "/../config/auth_check.php";
    require_login("buyer", "../auth/login.php");

    require_once __DIR__ . "/../config/db.php";

    function e($value)
    {
    return htmlspecialchars((string) ($value ?? ""), ENT_QUOTES, "UTF-8");
    }

    $buyerId = (int) $_SESSION['user_id'];

    // Fetch buyer details for the header
    $stmt = $link->prepare(
    "SELECT full_name, email, mobile FROM users WHERE user_id = ?"
    );
    $stmt->bind_param("i", $buyerId);
    $stmt->execute();
    $buyer = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Search filters
    $farmerSearch   = trim($_GET['farmer'] ?? "");
    $locationSearch = trim($_GET['location'] ?? "");

    $farmerPattern   = "%" . $farmerSearch . "%";
    $locationPattern = "%" . $locationSearch . "%";

    // Fetch farmers and their crop information
    $sql = "
    SELECT
        fp.farmer_id,
        fp.farm_name,
        fp.village,
        fp.district,
        fp.state,
        fp.pincode,
        fp.profile_image,
        fp.primary_crops,
        fp.experience,
        u.full_name,

        GROUP_CONCAT(
            DISTINCT cl.crop_name
            ORDER BY cl.crop_name
            SEPARATOR ', '
        ) AS available_products,

        COUNT(DISTINCT cl.listing_id) AS available_listing_count

    FROM farmer_profiles fp

    INNER JOIN users u
        ON u.user_id = fp.farmer_id

    LEFT JOIN crop_listings cl
        ON cl.farmer_id = fp.farmer_id
        AND cl.listing_status = 'available'
        AND (cl.available_from IS NULL OR cl.available_from <= CURDATE())
        AND (cl.available_until IS NULL OR cl.available_until >= CURDATE())

    WHERE
        u.role = 'farmer'
        AND u.account_status = 'active'
        AND (u.full_name LIKE ? OR fp.farm_name LIKE ?)
        AND CONCAT_WS(
            ' ',
            fp.village,
            fp.district,
            fp.state,
            fp.pincode
        ) LIKE ?

    GROUP BY
        fp.farmer_id,
        fp.farm_name,
        fp.village,
        fp.district,
        fp.state,
        fp.pincode,
        fp.profile_image,
        fp.primary_crops,
        fp.experience,
        u.full_name

    ORDER BY u.full_name ASC
";

    $stmt = $link->prepare($sql);
    $stmt->bind_param(
    "sss",
    $farmerPattern,
    $farmerPattern,
    $locationPattern
    );
    $stmt->execute();
    $farmers = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Farmers | AgroMarket Connect</title>
    <link rel="stylesheet" href="buyer.css">
</head>

<body>

<header class="buyer-header">

    <div class="header-left">
        <h2>AgroMarket Connect</h2>
    </div>

    <nav class="buyer-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="browse_crops.php">Browse Crops</a>
        <a href="find_farmers.php" class="active">Find Farmers</a>
        <a href="my_orders.php">My Orders</a>
        <a href="order_tracking.php">Track Orders</a>
        <a href="profile.php">My Profile</a>
    </nav>

    <div class="header-right">
        <a href="../auth/logout.php" class="logout-link">Logout</a>

        <div class="profile-menu">
            <button class="profile-avatar" id="profileToggle">
                <?php echo e(strtoupper(substr($buyer['full_name'] ?? 'B', 0, 1))) ?>
            </button>

            <div class="profile-popup" id="profilePopup">
                <h3><?php echo e($buyer['full_name'] ?? 'Buyer') ?></h3>
                <p><?php echo e($buyer['email'] ?? '') ?></p>
                <a href="profile.php">View Profile</a>
            </div>
        </div>
    </div>

</header>

<main class="buyer-page-container">

    <h1>Find Farmers</h1>
    <p>Connect with farmers and explore their crops.</p>

    <section class="farmer-search-section">

        <form method="GET" action="find_farmers.php">

            <input
                type="search"
                name="farmer"
                placeholder="Search farmer name"
                value="<?php echo e($farmerSearch) ?>"
            >

            <input
                type="text"
                name="location"
                placeholder="Search village, district or state"
                value="<?php echo e($locationSearch) ?>"
            >

            <button type="submit">Search Farmers</button>

            <a href="find_farmers.php">Clear</a>

        </form>

    </section>

    <section class="farmer-list-section">

        <h2>Registered Farmers</h2>

        <div class="farmer-grid">

            <?php if ($farmers->num_rows > 0): ?>

                <?php while ($farmer = $farmers->fetch_assoc()): ?>

                    <?php
                        $products = $farmer['available_products']
                            ?: ($farmer['primary_crops'] ?: 'Not specified');

                        $location = implode(", ", array_filter([
                            $farmer['village'],
                            $farmer['district'],
                            $farmer['state'],
                        ]));
                    ?>

                    <div class="farmer-card">

                        <?php if (! empty($farmer['profile_image'])): ?>

                            <img
                                src="../<?php echo e($farmer['profile_image']) ?>"
                                alt="<?php echo e($farmer['full_name']) ?>"
                                class="farmer-image-placeholder"
                            >

                        <?php else: ?>

                            <div class="farmer-image-placeholder">
                                Profile Picture
                            </div>

                        <?php endif; ?>

                        <h3><?php echo e($farmer['full_name']) ?></h3>

                        <?php if (! empty($farmer['farm_name'])): ?>
                            <p>Farm: <?php echo e($farmer['farm_name']) ?></p>
                        <?php endif; ?>

                        <p>Location: <?php echo e($location) ?></p>

                        <p>Crops: <?php echo e($products) ?></p>

                        <p>
                            Experience:
                            <?php echo $farmer['experience'] !== null
                                    ? e($farmer['experience']) . " Years"
                                : "Not specified" ?>
                        </p>

                        <p>
                            Available Listings:
                            <?php echo (int) $farmer['available_listing_count'] ?>
                        </p>

                        <a href="browse_crops.php?farmer_id=<?php echo (int) $farmer['farmer_id'] ?>">
                            View Crops
                        </a>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <p>No farmers found. Try another name or location.</p>

            <?php endif; ?>

        </div>

    </section>

</main>

<footer>
    <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
</footer>

<script src="buyer.js"></script>

</body>
</html>

<?php
    $stmt->close();
?>
