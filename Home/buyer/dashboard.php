<?php
    require_once __DIR__ . "/../config/auth_check.php";
    require_login("buyer", "../auth/login.php");
    require_once __DIR__ . "/../config/db.php";

    $buyerId = (int) $_SESSION['user_id'];

    // Escape output before displaying it in HTML.
    require_once __DIR__ . "/../includes/html.php";

    // Fetch buyer details.
    $userQuery = "SELECT full_name, email, mobile
              FROM users
              WHERE user_id = ?";

    $userStmt = mysqli_prepare($link, $userQuery);
    mysqli_stmt_bind_param($userStmt, "i", $buyerId);
    mysqli_stmt_execute($userStmt);

    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($userStmt));

    $fullName = $user['full_name'] ?? "Buyer";
    $email    = $user['email'] ?? "";
    $mobile   = $user['mobile'] ?? "";
    $initial  = strtoupper(substr($fullName, 0, 1));

    // Fetch order statistics.
    $statsQuery = "
    SELECT
        COUNT(*) AS total_orders,
        SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) AS pending_orders,
        SUM(CASE WHEN order_status = 'accepted' THEN 1 ELSE 0 END) AS accepted_orders,
        SUM(CASE WHEN order_status = 'completed' THEN 1 ELSE 0 END) AS completed_orders
    FROM orders
    WHERE buyer_id = ?
";

    $statsStmt = mysqli_prepare($link, $statsQuery);
    mysqli_stmt_bind_param($statsStmt, "i", $buyerId);
    mysqli_stmt_execute($statsStmt);

    $stats = mysqli_fetch_assoc(mysqli_stmt_get_result($statsStmt));

    $totalOrders     = (int) ($stats['total_orders'] ?? 0);
    $pendingOrders   = (int) ($stats['pending_orders'] ?? 0);
    $acceptedOrders  = (int) ($stats['accepted_orders'] ?? 0);
    $completedOrders = (int) ($stats['completed_orders'] ?? 0);

    // Fetch the buyer's five most recent orders.
    $ordersQuery = "
    SELECT
        o.order_id,
        o.order_number,
        o.quantity,
        o.quantity_unit,
        o.subtotal,
        o.order_status,
        cl.crop_name,
        u.full_name AS farmer_name
    FROM orders o
    LEFT JOIN crop_listings cl
        ON o.listing_id = cl.listing_id
    LEFT JOIN users u
        ON o.farmer_id = u.user_id
    WHERE o.buyer_id = ?
    ORDER BY o.placed_at DESC, o.order_id DESC
    LIMIT 5
";

    $ordersStmt = mysqli_prepare($link, $ordersQuery);
    mysqli_stmt_bind_param($ordersStmt, "i", $buyerId);
    mysqli_stmt_execute($ordersStmt);

    $recentOrders = mysqli_stmt_get_result($ordersStmt);

    // Fetch three recently added available crops.
    $cropsQuery = "
    SELECT
        cl.listing_id,
        cl.crop_name,
        cl.price_per_unit,
        cl.quantity_unit,
        cc.category_name
    FROM crop_listings cl
    INNER JOIN users farmer ON farmer.user_id = cl.farmer_id
        AND farmer.role = 'farmer' AND farmer.account_status = 'active'
    LEFT JOIN crop_categories cc
        ON cl.category_id = cc.category_id
    WHERE cl.listing_status = 'available' AND cl.quantity_available > 0
      AND (cl.available_from IS NULL OR cl.available_from <= CURDATE())
      AND (cl.available_until IS NULL OR cl.available_until >= CURDATE())
    ORDER BY cl.created_at DESC, cl.listing_id DESC
    LIMIT 3
";

    $cropsResult = mysqli_query($link, $cropsQuery);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Buyer Dashboard | AgroMarket Connect</title>

    <link rel="stylesheet" href="buyer.css">
<link rel="icon" href="../favicon.ico" type="image/x-icon">
</head>

<body>

    <?php
        $activePage = "dashboard.php";
        require __DIR__ . "/includes/header.php";
    ?>

    <main class="buyer-dashboard">

        <!-- Welcome Section -->

        <section class="welcome-section">

            <h1>Welcome, <?php echo e($fullName) ?>!</h1>

            <p>Discover fresh crops directly from farmers.</p>

            <a href="browse_crops.php">Browse Available Crops</a>

        </section>


        <!-- Dashboard Statistics -->

        <section class="buyer-summary">

            <div>
                <h3>Total Orders</h3>
                <p><?php echo $totalOrders ?></p>
            </div>

            <div>
                <h3>Pending Orders</h3>
                <p><?php echo $pendingOrders ?></p>
            </div>

            <div>
                <h3>Accepted Orders</h3>
                <p><?php echo $acceptedOrders ?></p>
            </div>

            <div>
                <h3>Completed Orders</h3>
                <p><?php echo $completedOrders ?></p>
            </div>

        </section>


        <!-- Quick Actions -->

        <section class="buyer-actions">

            <h2>Quick Actions</h2>

            <div class="action-grid">

                <div class="action-card">
                    <h3>Browse Crops</h3>
                    <p>Explore available crops from different farmers.</p>
                    <a href="browse_crops.php">Browse Now</a>
                </div>

                <div class="action-card">
                    <h3>Find Farmers</h3>
                    <p>Discover farmers and their agricultural products.</p>
                    <a href="find_farmers.php">Find Farmers</a>
                </div>

                <div class="action-card">
                    <h3>My Orders</h3>
                    <p>View and manage your placed orders.</p>
                    <a href="my_orders.php">View Orders</a>
                </div>

                <div class="action-card">
                    <h3>Track Orders</h3>
                    <p>Check the latest status of your purchases.</p>
                    <a href="order_tracking.php">Track Now</a>
                </div>

            </div>

        </section>


        <!-- Recent Orders -->

        <section class="recent-orders">

            <h2>Recent Orders</h2>

            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Crop</th>
                            <th>Farmer</th>
                            <th>Quantity</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (mysqli_num_rows($recentOrders) > 0): ?>

                            <?php while ($order = mysqli_fetch_assoc($recentOrders)): ?>

                                <tr>

                                    <td>
                                        <?php echo e($order['order_number'] ?: "ORD-" . $order['order_id']) ?>
                                    </td>

                                    <td>
                                        <?php echo e($order['crop_name'] ?? "Crop unavailable") ?>
                                    </td>

                                    <td>
                                        <?php echo e($order['farmer_name'] ?? "Farmer unavailable") ?>
                                    </td>

                                    <td>
                                        <?php echo e($order['quantity']) ?>
                                        <?php echo e($order['quantity_unit']) ?>
                                    </td>

                                    <td>
                                        ₹<?php echo number_format((float) $order['subtotal'], 2) ?>
                                    </td>

                                    <td>
                                        <?php echo e(ucfirst($order['order_status'])) ?>
                                    </td>

                                    <td>
                                        <a href="order_tracking.php?id=<?php echo (int) $order['order_id'] ?>">
                                            Track
                                        </a>
                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="7">
                                    You haven't placed any orders yet.
                                    <a href="browse_crops.php">Browse crops</a>
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>


        <!-- Featured Crops -->

        <section class="featured-crops">

            <h2>Explore Crops</h2>

            <div class="crop-grid">

                <?php if ($cropsResult && mysqli_num_rows($cropsResult) > 0): ?>

                    <?php while ($crop = mysqli_fetch_assoc($cropsResult)): ?>

                        <div class="crop-card">

                            <h3><?php echo e($crop['crop_name']) ?></h3>

                            <p>
                                Category:
                                <?php echo e($crop['category_name'] ?? "Uncategorized") ?>
                            </p>

                            <p>
                                Price:
                                ₹<?php echo number_format((float) $crop['price_per_unit'], 2) ?>
                                / <?php echo e($crop['quantity_unit']) ?>
                            </p>

                            <a href="crop_details.php?listing_id=<?php echo (int) $crop['listing_id'] ?>">
                                View Details
                            </a>

                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <p>No available crops at the moment.</p>

                <?php endif; ?>

            </div>

        </section>

    </main>


    <footer>
        <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
    </footer>


</body>

</html>
