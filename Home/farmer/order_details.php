<?php
require_once __DIR__ . "/../config/auth_check.php";
    require_login("farmer", "../auth/login.php");

    require_once __DIR__ . "/../config/db.php";

    require_once __DIR__ . "/../includes/html.php";

    $farmerId = (int) $_SESSION['user_id'];
    $orderId  = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (! $orderId || $orderId <= 0) {
    header("Location: orders.php");
    exit;
    }

    /* Farmer details */
    $stmt = $link->prepare("
    SELECT full_name
    FROM users
    WHERE user_id = ? AND role = 'farmer'
");

    $stmt->bind_param("i", $farmerId);
    $stmt->execute();
    $farmer = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    /* Fetch order details - verify ownership */
    $stmt = $link->prepare("
    SELECT
        o.*,
        c.crop_name,
        c.variety,
        u.full_name AS buyer_name,
        u.email AS buyer_email,
        u.mobile AS buyer_mobile
    FROM orders o
    INNER JOIN crop_listings c
        ON o.listing_id = c.listing_id
    INNER JOIN users u
        ON o.buyer_id = u.user_id
    WHERE o.order_id = ?
      AND o.farmer_id = ?
");

    $stmt->bind_param("ii", $orderId, $farmerId);
    $stmt->execute();

    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (! $order) {
    header("Location: orders.php?error=not_found");
    exit;
    }

    /* Fetch order status history */
    $stmt = $link->prepare("
    SELECT
        h.old_status,
        h.new_status,
        h.remarks,
        h.created_at,
        u.full_name AS changed_by_name
    FROM order_status_history h
    LEFT JOIN users u
        ON h.changed_by = u.user_id
    WHERE h.order_id = ?
    ORDER BY h.created_at ASC, h.history_id ASC
");

    $stmt->bind_param("i", $orderId);
    $stmt->execute();

    $history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    /* Status formatting */
    $statusLabels = [
    'pending'   => 'Pending',
    'accepted'  => 'Accepted',
    'rejected'  => 'Rejected',
    'cancelled' => 'Cancelled',
    'completed' => 'Completed',
    ];

    $status = $statusLabels[$order['order_status']] ?? ucfirst($order['order_status']);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Order Details | AgroMarket Connect</title>

    <link rel="stylesheet" href="farmer.css">
</head>

<body>

    <header class="farmer-header">

        <div class="header-left">
            <h2>AgroMarket Connect</h2>
        </div>

        <nav class="farmer-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="manage_listings.php">Manage Listings</a>
            <a href="orders.php" class="active">Orders</a>
        </nav>

        <div class="header-right">

            <div class="profile-menu">

                <span class="profile-avatar">
                    <?php echo e(strtoupper(substr($farmer['full_name'] ?? 'F', 0, 1))) ?>
                </span>

                <div class="profile-popup">
                    <p><?php echo e($farmer['full_name'] ?? 'Farmer') ?></p>
                    <a href="profile.php">My Profile</a>
                    <a href="../auth/logout.php">Logout</a>
                </div>

            </div>

        </div>

    </header>


    <main class="order-details-container">

        <div class="order-details-header">

            <div>
                <h2>Order Details</h2>
                <p>Order Number: <?php echo e($order['order_number']) ?></p>
            </div>

            <span class="order-status <?php echo e($order['order_status']) ?>">
                <?php echo e($status) ?>
            </span>

        </div>


        <!-- Order Information -->
        <section class="order-details-section">

            <h3>Order Information</h3>

            <div class="order-info-grid">

                <div>
                    <strong>Order Number</strong>
                    <p><?php echo e($order['order_number']) ?></p>
                </div>

                <div>
                    <strong>Order Date</strong>
                    <p><?php echo e(date('d M Y, h:i A', strtotime($order['placed_at']))) ?></p>
                </div>

                <div>
                    <strong>Current Status</strong>
                    <p><?php echo e($status) ?></p>
                </div>

            </div>

        </section>


        <!-- Crop Information -->
        <section class="order-details-section">

            <h3>Crop Information</h3>

            <div class="order-info-grid">

                <div>
                    <strong>Crop Name</strong>
                    <p><?php echo e($order['crop_name']) ?></p>
                </div>

                <div>
                    <strong>Variety</strong>
                    <p><?php echo e($order['variety'] ?: 'Not specified') ?></p>
                </div>

                <div>
                    <strong>Quantity</strong>
                    <p>
                        <?php echo e($order['quantity']) ?>
                        <?php echo e($order['quantity_unit']) ?>
                    </p>
                </div>

                <div>
                    <strong>Price Per Unit</strong>
                    <p>₹<?php echo number_format((float) $order['unit_price'], 2) ?></p>
                </div>

                <div>
                    <strong>Subtotal</strong>
                    <p>₹<?php echo number_format((float) $order['subtotal'], 2) ?></p>
                </div>

            </div>

        </section>


        <!-- Buyer Information -->
        <section class="order-details-section">

            <h3>Buyer Information</h3>

            <div class="order-info-grid">

                <div>
                    <strong>Buyer Name</strong>
                    <p><?php echo e($order['buyer_name']) ?></p>
                </div>

                <div>
                    <strong>Email</strong>
                    <p><?php echo e($order['buyer_email'] ?: 'Not provided') ?></p>
                </div>

                <div>
                    <strong>Mobile</strong>
                    <p><?php echo e($order['buyer_mobile'] ?: 'Not provided') ?></p>
                </div>

            </div>

        </section>


        <!-- Delivery Address -->
        <section class="order-details-section">

            <h3>Delivery Address</h3>

            <p><?php echo e($order['delivery_address']) ?></p>
            <p>
                <?php echo e($order['delivery_city']) ?>,
                <?php echo e($order['delivery_state']) ?> -
                <?php echo e($order['delivery_pincode']) ?>
            </p>

        </section>


        <!-- Buyer Note -->
        <section class="order-details-section">

            <h3>Buyer's Note</h3>

            <p>
                <?php echo nl2br(e($order['buyer_note'] ?: 'No additional note provided.')) ?>
            </p>

        </section>


        <!-- Farmer Response -->
        <section class="order-details-section">

            <h3>Your Response</h3>

            <p>
                <?php echo nl2br(e($order['farmer_response_note'] ?: 'No response added yet.')) ?>
            </p>

            <?php if ($order['terms_accepted_at']): ?>

                <p>
                    <strong>Terms accepted:</strong>
                    <?php echo e(date('d M Y, h:i A', strtotime($order['terms_accepted_at']))) ?>
                </p>

                <p>
                    <strong>Terms version:</strong>
                    <?php echo e($order['terms_version']) ?>
                </p>

            <?php endif; ?>

        </section>


        <!-- Order History -->
        <section class="order-details-section">

            <h3>Order Status History</h3>

            <?php if ($history): ?>

                <div class="order-history">

                    <?php foreach ($history as $item): ?>

                        <div class="order-history-item">

                            <strong>
                                <?php echo e($statusLabels[$item['new_status']] ?? ucfirst($item['new_status'])) ?>
                            </strong>

                            <p>
                                <?php echo e(date('d M Y, h:i A', strtotime($item['created_at']))) ?>
                            </p>

                            <p>
                                Changed by:
                                <?php echo e($item['changed_by_name'] ?? 'System') ?>
                            </p>

                            <?php if ($item['remarks']): ?>
                                <p><?php echo nl2br(e($item['remarks'])) ?></p>
                            <?php endif; ?>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <p>No order history available.</p>

            <?php endif; ?>

        </section>


        <div class="form-actions">

            <a href="orders.php">Back to Orders</a>

        </div>

    </main>

</body>
</html>
