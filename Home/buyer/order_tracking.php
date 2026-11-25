
<?php

    require_once __DIR__ . "/../config/auth_check.php";

    require_login("buyer", "../auth/login.php");

    require_once __DIR__ . "/../config/db.php";
    require_once __DIR__ . "/../includes/html.php";
    require_once __DIR__ . "/../includes/fulfilment.php";

    processFulfilment($link, "buyer");

    $buyerId = (int) $_SESSION['user_id'];

    // Fetch Buyer Details
    $stmt = $link->prepare(
    "SELECT
        full_name,
        email
     FROM users
     WHERE user_id = ?"
    );

    $stmt->bind_param("i", $buyerId);
    $stmt->execute();

    $buyer = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    // Get Order Identifier from Form or My Orders Link
    $orderNumber = trim($_GET['order_number'] ?? '');

    $orderId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
    );

    $order   = null;
    $history = null;
    $error   = "";

    // Search Only When an Identifier Is Supplied
    if ($orderNumber !== "" || ($orderId && $orderId > 0)) {

    // Search by Order Number or Numeric Order ID
    if ($orderNumber !== "") {

        $numericId = ctype_digit($orderNumber)
            ? (int) $orderNumber
            : 0;

        $sql = "
            SELECT
                o.*,
                COALESCE(bc.amount, 0) AS buyer_fee,
                o.subtotal + COALESCE(bc.amount, 0) AS buyer_total,
                cl.crop_name,
                cl.variety,
                u.full_name AS farmer_name,
                u.mobile AS farmer_mobile

            FROM orders o

            LEFT JOIN buyer_commissions bc
                ON bc.order_id = o.order_id

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

        $stmt->bind_param(
            "isi",
            $buyerId,
            $orderNumber,
            $numericId
        );

    } else {

        // Search by Order ID
        $sql = "
            SELECT
                o.*,
                COALESCE(bc.amount, 0) AS buyer_fee,
                o.subtotal + COALESCE(bc.amount, 0) AS buyer_total,
                cl.crop_name,
                cl.variety,
                u.full_name AS farmer_name,
                u.mobile AS farmer_mobile

            FROM orders o

            LEFT JOIN buyer_commissions bc
                ON bc.order_id = o.order_id

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

    // Check Whether the Order Exists
    if (! $order) {

        $error = "Order not found. Please check the Order ID.";

    } else {

        // Fetch Actual Order Status History
        $historySql = "
            SELECT
                old_status,
                new_status,
                remarks,
                created_at

            FROM order_status_history

            WHERE order_id = ?

            ORDER BY
                created_at ASC,
                history_id ASC
        ";

        $stmt = $link->prepare($historySql);

        $stmt->bind_param("i", $order['order_id']);

        $stmt->execute();

        $history = $stmt->get_result();

        $stmt->close();
    }
    }

    // Fetch Buyer Orders for Tracking Cards
    $orderCards = [];

    if (! $order) {

    $stmt = $link->prepare(
        "SELECT
            o.order_id,
            o.order_number,
            o.order_status,
            o.placed_at,
            o.quantity,
            o.quantity_unit,
            o.subtotal + COALESCE(bc.amount, 0) AS buyer_total,
            cl.crop_name

        FROM orders o

        LEFT JOIN buyer_commissions bc
            ON bc.order_id = o.order_id

        LEFT JOIN crop_listings cl
            ON cl.listing_id = o.listing_id

        WHERE o.buyer_id = ?

        ORDER BY
            o.placed_at DESC,
            o.order_id DESC"
    );

    $stmt->execute([$buyerId]);

    $orderCards = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
    }

    // Order Status Descriptions
    $statusDescriptions = [
    'pending'          => 'Your order is waiting for the farmer to respond.',
    'accepted'         => 'The farmer has accepted your order.',
    'rejected'         => 'The farmer has rejected your order.',
    'cancelled'        => 'This order has been cancelled.',
    'completed'        => 'Your order has been completed.',
    'ready_for_pickup' => 'Your crop is ready to collect. See the pickup location in the timeline.',
    'dispatched'       => 'Your crop is on its way. Confirm receipt after delivery.',
    ];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Order Tracking | AgroMarket Connect</title>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="buyer.css">

    <!-- Favicon -->
    <link rel="icon" href="../favicon.ico" type="image/x-icon">

</head>

<body>

    <!-- Buyer Header -->
    <?php

        $activePage = "order_tracking.php";

        require __DIR__ . "/includes/header.php";

    ?>


    <main class="buyer-page-container">

        <!-- Order Placement Success Message -->
        <?php if (
                $order &&
                isset($_SESSION['placed_order_confirmation']) &&
                $_SESSION['placed_order_confirmation'] === $order['order_number']
        ): ?>

            <div class="transaction-success" role="status">

                <strong>Order placed successfully</strong>

                Your order number is <?php echo e($order['order_number']); ?>.

            </div>

            <?php unset($_SESSION['placed_order_confirmation']); ?>

        <?php endif; ?>


        <!-- Page Heading -->
        <h1>Track Your Order</h1>

        <p>Check your order details and status history.</p>


        <!-- Order Tracking Search Form -->
        <section class="buyer-content-section">

            <form
                method="GET"
                action="order_tracking.php"
                class="buyer-filter-section"
            >

                <label for="orderNumber">
                    Enter Order Number or Order ID
                </label>

                <input
                    type="search"
                    id="orderNumber"
                    name="order_number"
                    placeholder="Example: ORD001"
                    value="<?php echo e($orderNumber); ?>"
                    required
                >

                <button type="submit">
                    Track Order
                </button>

            </form>


            <!-- Display Search Error -->
            <?php if ($error !== ""): ?>

                <p>
                    <?php echo e($error); ?>
                </p>

            <?php endif; ?>

        </section>


        <!-- Display All Order Tracking Cards -->
        <?php if (! $order): ?>

            <section aria-labelledby="your-orders">

                <h2 id="your-orders">Your orders</h2>

                <?php if (! $orderCards): ?>

                    <p>
                        You have no orders yet.
                        <a href="browse_crops.php">Browse crops</a>
                    </p>

                <?php else: ?>

                    <div class="tracking-cards">

                        <?php foreach ($orderCards as $card): ?>

                            <a
                                class="tracking-card"
                                href="order_tracking.php?id=<?php echo (int) $card['order_id']; ?>"
                            >

                                <!-- Crop Name -->
                                <strong>
                                    <?php echo e($card['crop_name'] ?? 'Crop order'); ?>
                                </strong>


                                <!-- Order Number and Date -->
                                <span>
                                    <?php echo e($card['order_number']); ?>
                                    ·
                                    <?php echo e(date(
    'd M Y',
    strtotime($card['placed_at'])
)); ?>
                                </span>


                                <!-- Quantity and Total Amount -->
                                <span>
                                    <?php echo e($card['quantity']); ?>

                                    <?php echo e($card['quantity_unit']); ?>

                                    · ₹<?php echo number_format(
    (float) $card['buyer_total'],
    2
); ?>
                                </span>


                                <!-- Order Status -->
                                <b>
                                    <?php echo e(ucfirst(str_replace(
    '_',
    ' ',
    $card['order_status']
))); ?>
                                </b>

                            </a>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </section>

        <?php endif; ?>


        <!-- Display Selected Order Details -->
        <?php if ($order): ?>

            <!-- Back to All Orders -->
            <p>
                <a href="order_tracking.php">← All orders</a>
            </p>


            <!-- Order Information Section -->
            <section class="buyer-content-section">

                <h2>Order Information</h2>

                <div class="buyer-info-grid">

                    <!-- Order ID -->
                    <div>

                        <label>Order ID</label>

                        <p>
                            <?php echo e($order['order_number']); ?>
                        </p>

                    </div>


                    <!-- Order Date -->
                    <div>

                        <label>Order Date</label>

                        <p>
                            <?php echo date(
                                    'd-m-Y h:i A',
                                    strtotime($order['placed_at'])
                            ); ?>
                        </p>

                    </div>


                    <!-- Crop Name -->
                    <div>

                        <label>Crop</label>

                        <p>
                            <?php echo e($order['crop_name']); ?>
                        </p>

                    </div>


                    <!-- Farmer Name -->
                    <div>

                        <label>Farmer</label>

                        <p>
                            <?php echo e($order['farmer_name']); ?>
                        </p>

                    </div>


                    <!-- Farmer Mobile Number -->
                    <div>

                        <label>Farmer Mobile</label>

                        <p>
                            <?php echo e(
                                    $order['farmer_mobile'] ?: 'Not provided'
                            ); ?>
                        </p>

                    </div>


                    <!-- Ordered Quantity -->
                    <div>

                        <label>Quantity</label>

                        <p>
                            <?php echo e($order['quantity']); ?>

                            <?php echo e($order['quantity_unit']); ?>
                        </p>

                    </div>


                    <!-- Order Amount and Platform Commission -->
                    <div>

                        <p>
                            Crop subtotal:
                            ₹<?php echo number_format($order['subtotal'], 2); ?>
                        </p>

                        <p>
                            Buyer platform fee:
                            ₹<?php echo number_format($order['buyer_fee'], 2); ?>
                        </p>

                        <label>Total including buyer fee</label>

                        <p>
                            ₹<?php echo number_format(
                                       (float) $order['buyer_total'],
                                       2
                               ); ?>
                        </p>

                    </div>


                    <!-- Current Order Status -->
                    <div>

                        <label>Current Status</label>

                        <p>
                            <?php echo e(ucfirst(str_replace(
                                    '_',
                                    ' ',
                                    $order['order_status']
                            ))); ?>
                        </p>

                    </div>

                </div>

            </section>


            <!-- Order Progress Section -->
            <section class="buyer-content-section">

                <h2>Order Progress</h2>


                <!-- Order Fulfilment Actions -->
                <?php showFulfilment($order, "buyer"); ?>


                <!-- Current Status Description -->
                <p>
                    <?php echo e(
                            $statusDescriptions[$order['order_status']] ?? 'Status information unavailable.'
                    ); ?>
                </p>


                <!-- Order Status History Timeline -->
                <?php if ($history && $history->num_rows > 0): ?>

                    <div class="tracking-timeline">

                        <?php while ($event = $history->fetch_assoc()): ?>

                            <div class="tracking-step completed">

                                <!-- Completed Status Icon -->
                                <span>
                                    <icon name="check" />
                                </span>


                                <!-- Timeline Event Details -->
                                <div>

                                    <h3>
                                        <?php echo e(ucfirst(str_replace(
                                                '_',
                                                ' ',
                                                $event['new_status']
                                        ))); ?>
                                    </h3>

                                    <p>
                                        <?php echo e(
                                                $event['remarks']
                                                    ?: 'Order status updated.'
                                        ); ?>
                                    </p>

                                    <small>
                                        <?php echo date(
                                                'd-m-Y h:i A',
                                                strtotime($event['created_at'])
                                        ); ?>
                                    </small>

                                </div>

                            </div>

                        <?php endwhile; ?>

                    </div>

                <?php else: ?>

                    <!-- Display Message When History Is Unavailable -->
                    <p>
                        Order history is not available yet.

                        Order placed on

                        <?php echo date(
                                'd-m-Y h:i A',
                            strtotime($order['placed_at'])
                        ); ?>.
                    </p>

                <?php endif; ?>

            </section>


            <!-- Delivery Information Section -->
            <section class="buyer-content-section">

                <h2>Delivery Information</h2>


                <!-- Check the Selected Fulfilment Method -->
                <?php if (
                        ($order['fulfilment_method'] ?? 'delivery') === 'pickup'
                ): ?>

                    <p>Collection from farmer</p>

                <?php else: ?>

                    <!-- Delivery Address -->
                    <p>
                        Address:

                        <?php echo e($order['delivery_address']); ?>
                    </p>


                    <!-- Delivery City -->
                    <p>
                        City:

                        <?php echo e($order['delivery_city']); ?>
                    </p>


                    <!-- Delivery State -->
                    <p>
                        State:

                        <?php echo e($order['delivery_state']); ?>
                    </p>


                    <!-- Delivery PIN Code -->
                    <p>
                        Pincode:

                        <?php echo e($order['delivery_pincode']); ?>
                    </p>

                <?php endif; ?>


                <!-- Buyer Note -->
                <?php if (! empty($order['buyer_note'])): ?>

                    <p>
                        Buyer Note:

                        <?php echo e($order['buyer_note']); ?>
                    </p>

                <?php endif; ?>


                <!-- Farmer Response Note -->
                <?php if (! empty($order['farmer_response_note'])): ?>

                    <p>
                        Farmer Response:

                        <?php echo e($order['farmer_response_note']); ?>
                    </p>

                <?php endif; ?>

            </section>

        <?php endif; ?>

    </main>


    <!-- Footer -->
    <footer>

        <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>

    </footer>

</body>

</html>
