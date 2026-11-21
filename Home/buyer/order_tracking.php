
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

    // Fetch buyer details
    $stmt = $link->prepare(
    "SELECT full_name, email FROM users WHERE user_id = ?"
    );
    $stmt->bind_param("i", $buyerId);
    $stmt->execute();
    $buyer = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Get order identifier from form or My Orders link
    $orderNumber = trim($_GET['order_number'] ?? '');
    $orderId     = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    $order   = null;
    $history = null;
    $error   = "";

    // Search only when an identifier is supplied
    if ($orderNumber !== "" || ($orderId && $orderId > 0)) {

    if ($orderNumber !== "") {

        $numericId = ctype_digit($orderNumber)
            ? (int) $orderNumber
            : 0;

        $sql = "
            SELECT
                o.*,
                cl.crop_name,
                cl.variety,
                u.full_name AS farmer_name,
                u.mobile AS farmer_mobile

            FROM orders o

            INNER JOIN crop_listings cl
                ON cl.listing_id = o.listing_id

            INNER JOIN users u
                ON u.user_id = o.farmer_id

            WHERE o.buyer_id = ?
              AND (
                  o.order_number = ?
                  OR o.order_id = ?
              )

            LIMIT 1
        ";

        $stmt = $link->prepare($sql);
        $stmt->bind_param("isi", $buyerId, $orderNumber, $numericId);

    } else {

        $sql = "
            SELECT
                o.*,
                cl.crop_name,
                cl.variety,
                u.full_name AS farmer_name,
                u.mobile AS farmer_mobile

            FROM orders o

            INNER JOIN crop_listings cl
                ON cl.listing_id = o.listing_id

            INNER JOIN users u
                ON u.user_id = o.farmer_id

            WHERE o.buyer_id = ?
              AND o.order_id = ?

            LIMIT 1
        ";

        $stmt = $link->prepare($sql);
        $stmt->bind_param("ii", $buyerId, $orderId);
    }

    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (! $order) {

        $error = "Order not found. Please check the Order ID.";

    } else {

        // Fetch actual order status history
        $historySql = "
            SELECT
                old_status,
                new_status,
                remarks,
                created_at
            FROM order_status_history
            WHERE order_id = ?
            ORDER BY created_at ASC, history_id ASC
        ";

        $stmt = $link->prepare($historySql);
        $stmt->bind_param("i", $order['order_id']);
        $stmt->execute();
        $history = $stmt->get_result();
        $stmt->close();
    }
    }

    // Status descriptions
    $statusDescriptions = [
    'pending'   => 'Your order is waiting for the farmer to respond.',
    'accepted'  => 'The farmer has accepted your order.',
    'rejected'  => 'The farmer has rejected your order.',
    'cancelled' => 'This order has been cancelled.',
    'completed' => 'Your order has been completed.',
    ];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking | AgroMarket Connect</title>
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
        <a href="find_farmers.php">Find Farmers</a>
        <a href="my_orders.php">My Orders</a>
        <a href="order_tracking.php" class="active">Track Orders</a>
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

    <h1>Track Your Order</h1>
    <p>Check your order details and status history.</p>

    <section class="buyer-content-section">

        <form method="GET" action="order_tracking.php"
              class="buyer-filter-section">

            <label for="orderNumber">Enter Order Number or Order ID</label>

            <input
                type="search"
                id="orderNumber"
                name="order_number"
                placeholder="Example: ORD001"
                value="<?php echo e($orderNumber) ?>"
                required
            >

            <button type="submit">Track Order</button>

        </form>

        <?php if ($error !== ""): ?>

            <p><?php echo e($error) ?></p>

        <?php endif; ?>

    </section>

    <?php if ($order): ?>

        <section class="buyer-content-section">

            <h2>Order Information</h2>

            <div class="buyer-info-grid">

                <div>
                    <label>Order ID</label>
                    <p><?php echo e($order['order_number']) ?></p>
                </div>

                <div>
                    <label>Order Date</label>
                    <p><?php echo date('d-m-Y h:i A', strtotime($order['placed_at'])) ?></p>
                </div>

                <div>
                    <label>Crop</label>
                    <p><?php echo e($order['crop_name']) ?></p>
                </div>

                <div>
                    <label>Farmer</label>
                    <p><?php echo e($order['farmer_name']) ?></p>
                </div>

                <div>
                    <label>Farmer Mobile</label>
                    <p><?php echo e($order['farmer_mobile'] ?: 'Not provided') ?></p>
                </div>

                <div>
                    <label>Quantity</label>
                    <p>
                        <?php echo e($order['quantity']) ?>
                        <?php echo e($order['quantity_unit']) ?>
                    </p>
                </div>

                <div>
                    <label>Total Amount</label>
                    <p>₹<?php echo number_format((float) $order['subtotal'], 2) ?></p>
                </div>

                <div>
                    <label>Current Status</label>
                    <p><?php echo e(ucfirst($order['order_status'])) ?></p>
                </div>

            </div>

        </section>

        <section class="buyer-content-section">

            <h2>Order Progress</h2>

            <p>
                <?php echo e($statusDescriptions[$order['order_status']] ?? 'Status information unavailable.') ?>
            </p>

            <?php if ($history && $history->num_rows > 0): ?>

                <div class="tracking-timeline">

                    <?php while ($event = $history->fetch_assoc()): ?>

                        <div class="tracking-step completed">

                            <span>
                                <icon name="check" />
                            </span>

                            <div>

                                <h3>
                                    <?php echo e(ucfirst($event['new_status'])) ?>
                                </h3>

                                <p>
                                    <?php echo e($event['remarks'] ?: 'Order status updated.') ?>
                                </p>

                                <small>
                                    <?php echo date('d-m-Y h:i A', strtotime($event['created_at'])) ?>
                                </small>

                            </div>

                        </div>

                    <?php endwhile; ?>

                </div>

            <?php else: ?>

                <p>
                    Order history is not available yet.
                    Order placed on
                    <?php echo date('d-m-Y h:i A', strtotime($order['placed_at'])) ?>.
                </p>

            <?php endif; ?>

        </section>

        <section class="buyer-content-section">

            <h2>Delivery Information</h2>

            <p>
                Address:
                <?php echo e($order['delivery_address']) ?>
            </p>

            <p>
                City:
                <?php echo e($order['delivery_city']) ?>
            </p>

            <p>
                State:
                <?php echo e($order['delivery_state']) ?>
            </p>

            <p>
                Pincode:
                <?php echo e($order['delivery_pincode']) ?>
            </p>

            <?php if (! empty($order['buyer_note'])): ?>

                <p>
                    Buyer Note:
                    <?php echo e($order['buyer_note']) ?>
                </p>

            <?php endif; ?>

            <?php if (! empty($order['farmer_response_note'])): ?>

                <p>
                    Farmer Response:
                    <?php echo e($order['farmer_response_note']) ?>
                </p>

            <?php endif; ?>

        </section>

    <?php endif; ?>

</main>

<footer>
    <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
</footer>

<script src="buyer.js"></script>

</body>
</html>
