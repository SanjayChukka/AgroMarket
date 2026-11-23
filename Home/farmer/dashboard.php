<?php
require_once __DIR__ . "/../config/auth_check.php";
    require_login("farmer", "../auth/login.php");

    require_once __DIR__ . "/../config/db.php";

    $farmerId = (int) $_SESSION['user_id'];

    // Safely display database values in HTML
    require_once __DIR__ . "/../includes/html.php";

    // ----------------------------------
    // 1. Farmer Details
    // ----------------------------------
    $farmerSql = "SELECT full_name, email, mobile
              FROM users
              WHERE user_id = ?
              AND role = 'farmer'
              LIMIT 1";

    $stmt = mysqli_prepare($link, $farmerSql);
    mysqli_stmt_bind_param($stmt, "i", $farmerId);
    mysqli_stmt_execute($stmt);

    $farmerResult = mysqli_stmt_get_result($stmt);
    $farmer       = mysqli_fetch_assoc($farmerResult);

    mysqli_stmt_close($stmt);

    $farmerName   = $farmer['full_name'] ?? ($_SESSION['full_name'] ?? "Farmer");

    // ----------------------------------
    // 2. Listing Statistics
    // ----------------------------------
    $listingStats = [
    "total_listings"    => 0,
    "active_listings"   => 0,
    "inactive_listings" => 0,
    ];

    $statsSql = "SELECT
                COUNT(*) AS total_listings,
                COALESCE(SUM(listing_status = 'available'), 0) AS active_listings,
                COALESCE(SUM(listing_status IN ('paused', 'sold_out')), 0) AS inactive_listings
             FROM crop_listings
             WHERE farmer_id = ?
             AND listing_status <> 'removed'";

    $stmt = mysqli_prepare($link, $statsSql);
    mysqli_stmt_bind_param($stmt, "i", $farmerId);
    mysqli_stmt_execute($stmt);

    $statsResult  = mysqli_stmt_get_result($stmt);
    $listingStats = mysqli_fetch_assoc($statsResult);

    mysqli_stmt_close($stmt);

    // ----------------------------------
    // 3. Received Orders Count
    // ----------------------------------
    $receivedOrders = 0;

    $orderCountSql = "SELECT COUNT(*) AS received_orders
                  FROM orders
                  WHERE farmer_id = ?";

    $stmt = mysqli_prepare($link, $orderCountSql);
    mysqli_stmt_bind_param($stmt, "i", $farmerId);
    mysqli_stmt_execute($stmt);

    $orderCountResult = mysqli_stmt_get_result($stmt);
    $orderCountData   = mysqli_fetch_assoc($orderCountResult);

    $receivedOrders = (int) ($orderCountData['received_orders'] ?? 0);

    mysqli_stmt_close($stmt);

    // ----------------------------------
    // 4. Recent Crop Listings
    // ----------------------------------
    $recentListings = [];

    $listingsSql = "SELECT
                    listing_id,
                    crop_name,
                    quantity_available,
                    quantity_unit,
                    price_per_unit,
                    listing_status,
                    created_at
                FROM crop_listings
                WHERE farmer_id = ?
                AND listing_status <> 'removed'
                ORDER BY created_at DESC, listing_id DESC
                LIMIT 5";

    $stmt = mysqli_prepare($link, $listingsSql);
    mysqli_stmt_bind_param($stmt, "i", $farmerId);
    mysqli_stmt_execute($stmt);

    $listingsResult = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($listingsResult)) {
    $recentListings[] = $row;
    }

    mysqli_stmt_close($stmt);

    // ----------------------------------
    // 5. Recent Received Orders
    // ----------------------------------
    $recentOrders = [];

    $ordersSql = "SELECT
                    o.order_id,
                    o.order_number,
                    o.quantity,
                    o.quantity_unit,
                    o.subtotal,
                    o.order_status,
                    o.placed_at,
                    cl.crop_name
              FROM orders AS o
              INNER JOIN crop_listings AS cl
                ON o.listing_id = cl.listing_id
              WHERE o.farmer_id = ?
              ORDER BY o.placed_at DESC, o.order_id DESC
              LIMIT 5";

    $stmt = mysqli_prepare($link, $ordersSql);
    mysqli_stmt_bind_param($stmt, "i", $farmerId);
    mysqli_stmt_execute($stmt);

    $ordersResult = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($ordersResult)) {
    $recentOrders[] = $row;
    }

    mysqli_stmt_close($stmt);

    // ----------------------------------
    // 6. Unread Notification Count
    // ----------------------------------
    require_once __DIR__ . "/../includes/notifications.php";
    $unreadCount = unreadNotificationCount($link, $farmerId);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard | AgroMarket Connect</title>

    <link rel="stylesheet" href="./farmer.css">
<link rel="icon" href="../favicon.ico" type="image/x-icon">
</head>

<body>

    <!-- Header -->
    <?php
    $activePage = "dashboard.php";
    $headerFarmer = $farmer;
    require __DIR__ . "/includes/header.php";
    ?>

    <!-- Main Dashboard -->
    <main class="dashboard-container">

        <!-- Welcome Section -->
        <section class="welcome-section">

            <h1>Welcome, <?php echo e($farmerName); ?>!</h1>

            <p>
                Manage your crops, listings and orders from your dashboard.
            </p>

        </section>

        <!-- Quick Actions -->
        <section class="dashboard-section">

            <h2>Quick Actions</h2>

            <div class="action-container">

                <div class="action-card">
                    <h3>List Your Crops</h3>
                    <p>Add your available agricultural products to the marketplace.</p>
                    <a href="add_crop.php">Add Crop</a>
                </div>

                <div class="action-card">
                    <h3>Manage Listings</h3>
                    <p>View, edit and manage your existing crop listings.</p>
                    <a href="manage_listings.php">View Listings</a>
                </div>

                <div class="action-card">
                    <h3>Manage Orders</h3>
                    <p>View and manage orders received from buyers.</p>
                    <a href="orders.php">View Orders</a>
                </div>

                <div class="action-card">
                    <h3>My Profile</h3>
                    <p>View and update your personal and farming information.</p>
                    <a href="profile.php">View Profile</a>
                </div>

            </div>

        </section>

        <!-- Listing Summary -->
        <section class="dashboard-section">

            <h2>Listing Overview</h2>

            <div class="summary-container">

                <div class="summary-card">
                    <h3>Total Listings</h3>
                    <p><?php echo (int) $listingStats['total_listings']; ?></p>
                </div>

                <div class="summary-card">
                    <h3>Active Listings</h3>
                    <p><?php echo (int) $listingStats['active_listings']; ?></p>
                </div>

                <div class="summary-card">
                    <h3>Inactive Listings</h3>
                    <p><?php echo (int) $listingStats['inactive_listings']; ?></p>
                </div>

                <div class="summary-card">
                    <h3>Received Orders</h3>
                    <p><?php echo $receivedOrders; ?></p>
                </div>

            </div>

        </section>

        <!-- Recent Listings -->
        <section class="dashboard-section">

            <h2>Recent Crop Listings</h2>

            <div class="recent-listings">

                <?php if (! empty($recentListings)): ?>

                    <?php foreach ($recentListings as $listing): ?>

                        <div class="recent-listing-item">

                            <h3><?php echo e($listing['crop_name']); ?></h3>

                            <p>
                                Quantity:
                                <?php echo e($listing['quantity_available']); ?>
                                <?php echo e($listing['quantity_unit']); ?>
                            </p>

                            <p>
                                Price:
                                ₹<?php echo number_format((float) $listing['price_per_unit'], 2); ?>
                                per unit
                            </p>

                            <p>
                                Status:
                                <?php echo e(ucwords(str_replace('_', ' ', $listing['listing_status']))); ?>
                            </p>

                            <p>
                                Listed on:
                                <?php
                                    echo ! empty($listing['created_at'])
                                        ? e(date("d M Y", strtotime($listing['created_at'])))
                                        : "Date not available";
                                ?>
                            </p>

                        </div>

                    <?php endforeach; ?>

                    <a href="manage_listings.php">View All Listings</a>

                <?php else: ?>

                    <p>No crop listings available yet.</p>
                    <a href="add_crop.php">Start Listing Your Crops</a>

                <?php endif; ?>

            </div>

        </section>

        <!-- Recent Orders -->
        <section class="dashboard-section">

            <h2>Recent Orders</h2>

            <div class="recent-orders">

                <?php if (! empty($recentOrders)): ?>

                    <?php foreach ($recentOrders as $order): ?>

                        <div class="recent-order-item">

                            <h3><?php echo e($order['crop_name']); ?></h3>

                            <p>
                                Order Number:
                                <?php echo e($order['order_number']); ?>
                            </p>

                            <p>
                                Quantity:
                                <?php echo e($order['quantity']); ?>
                                <?php echo e($order['quantity_unit']); ?>
                            </p>

                            <p>
                                Amount:
                                ₹<?php echo number_format((float) $order['subtotal'], 2); ?>
                            </p>

                            <p>
                                Status:
                                <?php echo e(ucfirst(str_replace('_', ' ', $order['order_status']))); ?>
                            </p>

                            <p>
                                Order Date:
                                <?php
                                    echo ! empty($order['placed_at'])
                                        ? e(date("d M Y", strtotime($order['placed_at'])))
                                        : "Date not available";
                                ?>
                            </p>

                        </div>

                    <?php endforeach; ?>

                    <a href="orders.php">View All Orders</a>

                <?php else: ?>

                    <p>No orders received yet.</p>

                <?php endif; ?>

            </div>

        </section>

    </main>

    <!-- Footer -->
    <footer class="farmer-footer">
        <p>&copy; 2026 AgroMarket Connect. All Rights Reserved.</p>
    </footer>

    <!-- Profile and Notification Scripts -->


</body>
</html>
