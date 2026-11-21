
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/auth_check.php";
require_login("farmer", "../auth/login.php");

require_once __DIR__ . "/../config/db.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$farmerId = (int)$_SESSION['user_id'];

function e($value) {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}

// ------------------------------------------
// 1. CSRF token
// ------------------------------------------

if (empty($_SESSION['order_csrf_token'])) {
    $_SESSION['order_csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['order_csrf_token'];

$actionMessage = "";
$actionType = "";

// ------------------------------------------
// 2. Process Accept / Reject actions
// ------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $submittedToken = $_POST['csrf_token'] ?? "";

    $orderId = filter_var(
        $_POST['order_id'] ?? "",
        FILTER_VALIDATE_INT
    );

    $action = $_POST['action'] ?? "";
    $termsAccepted = $_POST['terms_accepted'] ?? "0";

    $transactionStarted = false;

    try {

        // Validate CSRF token
        if (!hash_equals($csrfToken, $submittedToken)) {
            throw new RuntimeException(
                "Invalid request. Please refresh the page and try again."
            );
        }

        // Validate order ID and action
        if (!$orderId || !in_array($action, ["accept", "reject"], true)) {
            throw new RuntimeException("Invalid order request.");
        }

        // Accepting requires terms acceptance
        if ($action === "accept" && $termsAccepted !== "1") {
            throw new RuntimeException(
                "Please accept the order terms before proceeding."
            );
        }

        mysqli_begin_transaction($link);
        $transactionStarted = true;

        // ----------------------------------
        // 3. Lock and verify the order
        // ----------------------------------

        $orderSql = "SELECT
                        o.order_id,
                        o.order_number,
                        o.buyer_id,
                        o.order_status,
                        cl.crop_name

                     FROM orders AS o

                     INNER JOIN crop_listings AS cl
                        ON o.listing_id = cl.listing_id

                     WHERE o.order_id = ?
                     AND o.farmer_id = ?

                     FOR UPDATE";

        $stmt = mysqli_prepare($link, $orderSql);

        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $orderId,
            $farmerId
        );

        mysqli_stmt_execute($stmt);

        $orderResult = mysqli_stmt_get_result($stmt);
        $order = mysqli_fetch_assoc($orderResult);

        mysqli_stmt_close($stmt);

        if (!$order) {
            throw new RuntimeException(
                "Order not found or you are not authorized to manage it."
            );
        }

        // Only pending orders can be accepted or rejected
        if ($order['order_status'] !== "pending") {
            throw new RuntimeException(
                "This order has already been processed."
            );
        }

        $oldStatus = $order['order_status'];

        // ----------------------------------
        // 4. Update the order
        // ----------------------------------

        if ($action === "accept") {

            $newStatus = "accepted";

            $farmerNote = "Farmer accepted the order after agreeing to the displayed terms.";

            $termsVersion = "1.0";

            $updateSql = "UPDATE orders

                          SET order_status = 'accepted',
                              farmer_response_note = ?,
                              terms_accepted_at = NOW(),
                              terms_version = ?

                          WHERE order_id = ?
                          AND farmer_id = ?
                          AND order_status = 'pending'";

            $stmt = mysqli_prepare($link, $updateSql);

            mysqli_stmt_bind_param(
                $stmt,
                "ssii",
                $farmerNote,
                $termsVersion,
                $orderId,
                $farmerId
            );

        } else {

            $newStatus = "rejected";

            $farmerNote = "Farmer rejected the order.";

            $updateSql = "UPDATE orders

                          SET order_status = 'rejected',
                              farmer_response_note = ?

                          WHERE order_id = ?
                          AND farmer_id = ?
                          AND order_status = 'pending'";

            $stmt = mysqli_prepare($link, $updateSql);

            mysqli_stmt_bind_param(
                $stmt,
                "sii",
                $farmerNote,
                $orderId,
                $farmerId
            );
        }

        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) !== 1) {
            mysqli_stmt_close($stmt);

            throw new RuntimeException(
                "The order could not be updated. Please try again."
            );
        }

        mysqli_stmt_close($stmt);

        // ----------------------------------
        // 5. Insert order status history
        // ----------------------------------

        $remarks = $action === "accept"
            ? "Farmer accepted the order."
            : "Farmer rejected the order.";

        $historySql = "INSERT INTO order_status_history
                       (
                           order_id,
                           changed_by,
                           old_status,
                           new_status,
                           remarks
                       )

                       VALUES (?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($link, $historySql);

        mysqli_stmt_bind_param(
            $stmt,
            "iisss",
            $orderId,
            $farmerId,
            $oldStatus,
            $newStatus,
            $remarks
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // ----------------------------------
        // 6. Create buyer notification
        // ----------------------------------

        $orderNumber = $order['order_number'];
        $cropName = $order['crop_name'];
        $buyerId = (int)$order['buyer_id'];

        if ($action === "accept") {

            $notificationTitle = "Order Accepted";

            $notificationMessage =
                "Your order " . $orderNumber .
                " for " . $cropName .
                " has been accepted by the farmer.";

        } else {

            $notificationTitle = "Order Rejected";

            $notificationMessage =
                "Your order " . $orderNumber .
                " for " . $cropName .
                " has been rejected by the farmer.";
        }

        $notificationType = "order_update";

        $notificationSql = "INSERT INTO notifications
                            (
                                user_id,
                                title,
                                message,
                                notification_type,
                                related_order_id
                            )

                            VALUES (?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($link, $notificationSql);

        mysqli_stmt_bind_param(
            $stmt,
            "isssi",
            $buyerId,
            $notificationTitle,
            $notificationMessage,
            $notificationType,
            $orderId
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // ----------------------------------
        // 7. Commit transaction
        // ----------------------------------

        mysqli_commit($link);
        $transactionStarted = false;

        $actionMessage = $action === "accept"
            ? "Order accepted successfully. The buyer has been notified."
            : "Order rejected successfully. The buyer has been notified.";

        $actionType = "success";

    } catch (Throwable $exception) {

        if ($transactionStarted) {
            mysqli_rollback($link);
        }

        if ($exception instanceof RuntimeException) {

            $actionMessage = $exception->getMessage();

        } else {

            error_log("Farmer order action error: " . $exception->getMessage());

            $actionMessage =
                "Something went wrong while processing the order. Please try again.";
        }

        $actionType = "error";
    }

    // Redirect after POST to prevent duplicate submissions
    $_SESSION['order_action_message'] = $actionMessage;
    $_SESSION['order_action_type'] = $actionType;

    header("Location: orders.php");
    exit;
}

// ------------------------------------------
// 8. Display message after redirect
// ------------------------------------------

if (isset($_SESSION['order_action_message'])) {

    $actionMessage = $_SESSION['order_action_message'];
    $actionType = $_SESSION['order_action_type'] ?? "";

    unset($_SESSION['order_action_message']);
    unset($_SESSION['order_action_type']);
}

// ------------------------------------------
// 9. Fetch farmer profile details
// ------------------------------------------

$profileSql = "SELECT full_name, email, mobile
               FROM users
               WHERE user_id = ?
               AND role = 'farmer'
               LIMIT 1";

$stmt = mysqli_prepare($link, $profileSql);

mysqli_stmt_bind_param($stmt, "i", $farmerId);

mysqli_stmt_execute($stmt);

$profileResult = mysqli_stmt_get_result($stmt);
$farmer = mysqli_fetch_assoc($profileResult) ?: [];

mysqli_stmt_close($stmt);

$farmerName = $farmer['full_name'] ?? "Farmer";
$farmerEmail = $farmer['email'] ?? "Email not provided";
$farmerMobile = $farmer['mobile'] ?? "Mobile not provided";

$avatarInitial = strtoupper(substr(trim($farmerName), 0, 1));

if ($avatarInitial === "") {
    $avatarInitial = "F";
}

// ------------------------------------------
// 10. Fetch order summary
// ------------------------------------------

$summarySql = "SELECT
                   COUNT(*) AS total_orders,

                   COALESCE(
                       SUM(order_status = 'pending'), 0
                   ) AS pending_orders,

                   COALESCE(
                       SUM(order_status = 'accepted'), 0
                   ) AS accepted_orders,

                   COALESCE(
                       SUM(order_status = 'rejected'), 0
                   ) AS rejected_orders

               FROM orders
               WHERE farmer_id = ?";

$stmt = mysqli_prepare($link, $summarySql);

mysqli_stmt_bind_param($stmt, "i", $farmerId);

mysqli_stmt_execute($stmt);

$summaryResult = mysqli_stmt_get_result($stmt);

$orderSummary = mysqli_fetch_assoc($summaryResult) ?: [
    "total_orders" => 0,
    "pending_orders" => 0,
    "accepted_orders" => 0,
    "rejected_orders" => 0
];

mysqli_stmt_close($stmt);

// ------------------------------------------
// 11. Fetch received orders
// ------------------------------------------

$orders = [];

$ordersSql = "SELECT
                  o.order_id,
                  o.order_number,
                  o.quantity,
                  o.quantity_unit,
                  o.subtotal,
                  o.order_status,
                  o.placed_at,

                  b.full_name AS buyer_name,

                  cl.crop_name

              FROM orders AS o

              INNER JOIN crop_listings AS cl
                  ON o.listing_id = cl.listing_id

              LEFT JOIN users AS b
                  ON o.buyer_id = b.user_id

              WHERE o.farmer_id = ?

              ORDER BY o.placed_at DESC, o.order_id DESC";

$stmt = mysqli_prepare($link, $ordersSql);

mysqli_stmt_bind_param($stmt, "i", $farmerId);

mysqli_stmt_execute($stmt);

$ordersResult = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($ordersResult)) {
    $orders[] = $row;
}

mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Orders | AgroMarket Connect</title>

    <link rel="stylesheet" href="farmer.css">

</head>

<body>

    <!-- Header -->

    <header class="farmer-header">

        <div class="header-left">
            <h2>AgroMarket Connect</h2>
        </div>

        <nav class="farmer-nav">

            <a href="dashboard.php">Dashboard</a>
            <a href="profile.php">My Profile</a>
            <a href="manage_listings.php">My Listings</a>
            <a href="orders.php">Orders</a>

        </nav>

        <div class="header-right">

            <a href="../auth/logout.php" class="logout-link">
                Logout
            </a>

            <div class="profile-menu">

                <button
                    type="button"
                    class="profile-avatar"
                    id="profileToggle"
                    aria-expanded="false">

                    <?php echo e($avatarInitial); ?>

                </button>

                <div class="profile-popup" id="profilePopup">

                    <div class="popup-avatar">
                        <?php echo e($avatarInitial); ?>
                    </div>

                    <h3><?php echo e($farmerName); ?></h3>

                    <p><?php echo e($farmerEmail); ?></p>
                    <p><?php echo e($farmerMobile); ?></p>

                    <a href="profile.php">View Profile</a>

                </div>

            </div>

        </div>

    </header>

    <!-- Main Content -->

    <main class="orders-container">

        <h1>My Orders</h1>

        <p>View and manage orders received from buyers.</p>

        <!-- Action Message -->

        <?php if ($actionMessage !== ""): ?>

            <div class="<?php echo $actionType === 'success'
                ? 'success-message'
                : 'error-message'; ?>">

                <?php echo e($actionMessage); ?>

            </div>

        <?php endif; ?>

        <!-- Order Summary -->

        <section class="order-summary">

            <div>
                <h3>Total Orders</h3>
                <p><?php echo (int)$orderSummary['total_orders']; ?></p>
            </div>

            <div>
                <h3>Pending</h3>
                <p><?php echo (int)$orderSummary['pending_orders']; ?></p>
            </div>

            <div>
                <h3>Accepted</h3>
                <p><?php echo (int)$orderSummary['accepted_orders']; ?></p>
            </div>

            <div>
                <h3>Rejected</h3>
                <p><?php echo (int)$orderSummary['rejected_orders']; ?></p>
            </div>

        </section>

        <!-- Received Orders -->

        <section class="orders-table-section">

            <h2>Received Orders</h2>

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>
                            <th>Order ID</th>
                            <th>Buyer Name</th>
                            <th>Crop</th>
                            <th>Quantity</th>
                            <th>Total Amount</th>
                            <th>Order Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>

                    </thead>

                    <tbody id="ordersTable">

                        <?php if (!empty($orders)): ?>

                            <?php foreach ($orders as $order): ?>

                                <tr>

                                    <td>
                                        <?php echo e($order['order_number']); ?>
                                    </td>

                                    <td>
                                        <?php echo e($order['buyer_name'] ?? "Buyer"); ?>
                                    </td>

                                    <td>
                                        <?php echo e($order['crop_name']); ?>
                                    </td>

                                    <td>
                                        <?php echo e($order['quantity']); ?>
                                        <?php echo e($order['quantity_unit']); ?>
                                    </td>

                                    <td>
                                        ₹<?php echo number_format((float)$order['subtotal'], 2); ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo !empty($order['placed_at'])
                                            ? e(date("d-m-Y", strtotime($order['placed_at'])))
                                            : "Not available";
                                        ?>
                                    </td>

                                    <td class="status-cell">

                                        <?php
                                        $statusLabels = [
                                            "pending" => "Pending",
                                            "accepted" => "Accepted",
                                            "rejected" => "Rejected",
                                            "cancelled" => "Cancelled",
                                            "completed" => "Completed"
                                        ];

                                        echo e(
                                            $statusLabels[$order['order_status']]
                                            ?? $order['order_status']
                                        );
                                        ?>

                                    </td>

                                    <td class="action-cell">

                                        <a href="order_details.php?id=<?php echo (int)$order['order_id']; ?>">
                                            View
                                        </a>

                                        <?php if ($order['order_status'] === "pending"): ?>

                                            <button
                                                type="button"
                                                onclick="openConfirmation(<?php echo (int)$order['order_id']; ?>, 'accept')">

                                                Accept

                                            </button>

                                            <button
                                                type="button"
                                                onclick="openConfirmation(<?php echo (int)$order['order_id']; ?>, 'reject')">

                                                Reject

                                            </button>

                                        <?php else: ?>

                                            <span class="action-message">
                                                Order <?php echo e($order['order_status']); ?>
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="8">
                                    No orders have been received yet.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

    <!-- Confirmation Popup -->

    <div class="confirmation-overlay" id="confirmationOverlay">

        <div class="confirmation-box">

            <button
                type="button"
                class="popup-close"
                onclick="closeConfirmation()">

                ×

            </button>

            <h2 id="confirmationTitle">Confirm Order</h2>

            <p id="confirmationMessage"></p>

            <!-- This form submits the decision to PHP -->

            <form method="POST" action="orders.php" id="orderActionForm">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo e($csrfToken); ?>">

                <input
                    type="hidden"
                    name="order_id"
                    id="selectedOrderId">

                <input
                    type="hidden"
                    name="action"
                    id="selectedAction">

                <input
                    type="hidden"
                    name="terms_accepted"
                    id="termsAccepted"
                    value="0">

                <div id="termsArea">

                    <label class="terms-label">

                        <input
                            type="checkbox"
                            id="termsCheckbox">

                        I agree to fulfil this order according to the displayed
                        quantity, price and delivery terms.

                    </label>

                </div>

                <div class="confirmation-actions">

                    <button
                        type="button"
                        onclick="closeConfirmation()">

                        Cancel

                    </button>

                    <button type="button" id="confirmAction">
                        Confirm
                    </button>

                </div>

            </form>

        </div>

    </div>

    <!-- Footer -->

    <footer>
        <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
    </footer>

    <script>

        // --------------------------------
        // Profile Popup
        // --------------------------------

        const profileToggle = document.getElementById("profileToggle");
        const profilePopup = document.getElementById("profilePopup");

        profileToggle.addEventListener("click", function() {

            profilePopup.classList.toggle("show");

            profileToggle.setAttribute(
                "aria-expanded",
                profilePopup.classList.contains("show")
            );

        });

        document.addEventListener("click", function(event) {

            if (!event.target.closest(".profile-menu")) {

                profilePopup.classList.remove("show");

                profileToggle.setAttribute("aria-expanded", "false");

            }

        });


        // --------------------------------
        // Order Confirmation
        // --------------------------------

        let selectedAction = null;

        const confirmationOverlay =
            document.getElementById("confirmationOverlay");

        const confirmationTitle =
            document.getElementById("confirmationTitle");

        const confirmationMessage =
            document.getElementById("confirmationMessage");

        const termsArea =
            document.getElementById("termsArea");

        const termsCheckbox =
            document.getElementById("termsCheckbox");

        const confirmButton =
            document.getElementById("confirmAction");


        function openConfirmation(orderId, action) {

            selectedAction = action;

            document.getElementById("selectedOrderId").value = orderId;

            document.getElementById("selectedAction").value = action;

            document.getElementById("termsAccepted").value = "0";

            termsCheckbox.checked = false;

            if (action === "accept") {

                confirmationTitle.textContent = "Accept Order?";

                confirmationMessage.textContent =
                    "Are you sure you want to accept this order? Please review the order details before confirming.";

                termsArea.style.display = "block";

                confirmButton.textContent = "Accept Order";

            } else {

                confirmationTitle.textContent = "Reject Order?";

                confirmationMessage.textContent =
                    "Are you sure you want to reject this buyer's order?";

                termsArea.style.display = "none";

                confirmButton.textContent = "Reject Order";

            }

            confirmationOverlay.style.display = "flex";

        }


        function closeConfirmation() {

            confirmationOverlay.style.display = "none";

            selectedAction = null;

        }


        // --------------------------------
        // Submit Decision to PHP
        // --------------------------------

        confirmButton.addEventListener("click", function() {

            if (selectedAction === "accept") {

                if (!termsCheckbox.checked) {

                    alert("Please accept the terms and conditions first.");

                    return;

                }

                document.getElementById("termsAccepted").value = "1";

            } else {

                document.getElementById("termsAccepted").value = "0";

            }

            document.getElementById("orderActionForm").submit();

        });


        // Close popup when clicking outside
        confirmationOverlay.addEventListener("click", function(event) {

            if (event.target === confirmationOverlay) {
                closeConfirmation();
            }

        });

    </script>

</body>

</html>
