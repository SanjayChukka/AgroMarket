<?php
require_once __DIR__ . "/../config/auth_check.php";
    require_login("buyer", "../auth/login.php");

    require_once __DIR__ . "/../config/db.php";

    require_once __DIR__ . "/../includes/html.php";

    $buyerId = (int) $_SESSION['user_id'];

    // Fetch buyer details
    $stmt = $link->prepare(
    "SELECT full_name, email FROM users WHERE user_id = ?"
    );
    $stmt->bind_param("i", $buyerId);
    $stmt->execute();
    $buyer = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Fetch order statistics
    $statsSql = "
    SELECT
        COUNT(*) AS total_orders,
        COALESCE(SUM(order_status = 'pending'), 0) AS pending_orders,
        COALESCE(SUM(order_status = 'accepted'), 0) AS accepted_orders,
        COALESCE(SUM(order_status = 'completed'), 0) AS completed_orders
    FROM orders
    WHERE buyer_id = ?
";

    $stmt = $link->prepare($statsSql);
    $stmt->bind_param("i", $buyerId);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Search and filters
    $search    = trim($_GET['search'] ?? "");
    $status    = $_GET['status'] ?? "";
    $orderDate = $_GET['order_date'] ?? "";

    // Allow only statuses that exist in the database
    $allowedStatuses = [
    'pending',
    'accepted',
    'rejected',
    'cancelled',
    'completed',
    ];

    if (! in_array($status, $allowedStatuses, true)) {
    $status = "";
    }

    // Validate date
    if (
    $orderDate !== "" &&
    ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $orderDate)
    ) {
    $orderDate = "";
    } elseif ($orderDate !== "") {
    $date = DateTime::createFromFormat('!Y-m-d', $orderDate);

    if (! $date || $date->format('Y-m-d') !== $orderDate) {
        $orderDate = "";
    }
    }

    $searchPattern = "%" . $search . "%";

    // Fetch buyer's orders
    $sql = "
    SELECT
        o.order_id,
        o.order_number,
        o.quantity,
        o.quantity_unit,
        o.unit_price,
        o.subtotal,
        o.order_status,
        o.placed_at,

        cl.crop_name,

        u.full_name AS farmer_name

    FROM orders o

    INNER JOIN crop_listings cl
        ON cl.listing_id = o.listing_id

    INNER JOIN users u
        ON u.user_id = o.farmer_id

    WHERE o.buyer_id = ?

      AND (
          ? = ''
          OR o.order_number LIKE ?
          OR cl.crop_name LIKE ?
      )

      AND (? = '' OR o.order_status = ?)

      AND (
          ? = ''
          OR DATE(o.placed_at) = ?
      )

    ORDER BY o.placed_at DESC, o.order_id DESC
";

    $stmt = $link->prepare($sql);

    $stmt->bind_param(
    "isssssss",
    $buyerId,
    $search,
    $searchPattern,
    $searchPattern,
    $status,
    $status,
    $orderDate,
    $orderDate
    );

    $stmt->execute();
    $orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | AgroMarket Connect</title>
    <link rel="stylesheet" href="buyer.css">
<link rel="icon" href="../favicon.ico" type="image/x-icon">
</head>

<body>

<?php
    $activePage = "my_orders.php";
    require __DIR__ . "/includes/header.php";
    ?>

<main class="buyer-page-container">

    <h1>My Orders</h1>
    <p>View all your placed orders and their current status.</p>

    <section class="buyer-summary">

        <div>
            <h3>Total Orders</h3>
            <p><?php echo (int) $stats['total_orders'] ?></p>
        </div>

        <div>
            <h3>Pending</h3>
            <p><?php echo (int) $stats['pending_orders'] ?></p>
        </div>

        <div>
            <h3>Accepted</h3>
            <p><?php echo (int) $stats['accepted_orders'] ?></p>
        </div>

        <div>
            <h3>Completed</h3>
            <p><?php echo (int) $stats['completed_orders'] ?></p>
        </div>

    </section>

    <section class="buyer-content-section">

        <h2>Order History</h2>

        <form method="GET" action="my_orders.php" class="buyer-filter-section">

            <input
                type="search"
                name="search"
                placeholder="Search Order ID or Crop"
                value="<?php echo e($search) ?>"
            >

            <select name="status">

                <option value="">All Statuses</option>

                <option value="pending" <?php echo $status === 'pending' ? 'selected' : '' ?>>
                    Pending
                </option>

                <option value="accepted" <?php echo $status === 'accepted' ? 'selected' : '' ?>>
                    Accepted
                </option>

                <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : '' ?>>
                    Rejected
                </option>

                <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : '' ?>>
                    Cancelled
                </option>

                <option value="completed" <?php echo $status === 'completed' ? 'selected' : '' ?>>
                    Completed
                </option>

            </select>

            <input
                type="date"
                name="order_date"
                value="<?php echo e($orderDate) ?>"
            >

            <button type="submit">Apply Filters</button>

            <a href="my_orders.php">Clear</a>

        </form>

        <div class="table-wrapper">

            <table>

                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Crop</th>
                        <th>Farmer</th>
                        <th>Quantity</th>
                        <th>Total Amount</th>
                        <th>Order Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($orders->num_rows > 0): ?>

                        <?php while ($order = $orders->fetch_assoc()): ?>

                            <tr>

                                <td><?php echo e($order['order_number']) ?></td>

                                <td><?php echo e($order['crop_name']) ?></td>

                                <td><?php echo e($order['farmer_name']) ?></td>

                                <td>
                                    <?php echo e($order['quantity']) ?>
                                    <?php echo e($order['quantity_unit']) ?>
                                </td>

                                <td>
                                    ₹<?php echo number_format((float) $order['subtotal'], 2) ?>
                                </td>

                                <td>
                                    <?php echo date('d-m-Y', strtotime($order['placed_at'])) ?>
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
                            <td colspan="8">
                                No orders found.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</main>

<footer>
    <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
</footer>


</body>
</html>

<?php
    $stmt->close();
?>
