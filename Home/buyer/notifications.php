
<?php
    require_once __DIR__ . "/../config/auth_check.php";

    require_login("buyer", "../auth/login.php");

    require_once __DIR__ . "/../config/db.php";

    $user_id = (int) $_SESSION['user_id'];

    $message = "";

    // Mark a notification as read
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST['mark_read'])) {

        $notification_id = (int) $_POST['notification_id'];

        $sql = "UPDATE notifications
                SET is_read = 1
                WHERE notification_id = ?
                AND user_id = ?";

        $stmt = mysqli_prepare($link, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $notification_id,
                $user_id
            );

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

    }

    if (isset($_POST['mark_all_read'])) {

        $sql = "UPDATE notifications
                SET is_read = 1
                WHERE user_id = ? AND is_read = 0";

        $stmt = mysqli_prepare($link, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    header("Location: notifications.php");
    exit;
    }

    // Get unread notification count
    $countSql = "SELECT COUNT(*) AS unread_count
             FROM notifications
             WHERE user_id = ? AND is_read = 0";

    $countStmt = mysqli_prepare($link, $countSql);
    mysqli_stmt_bind_param($countStmt, "i", $user_id);
    mysqli_stmt_execute($countStmt);

    $countResult = mysqli_stmt_get_result($countStmt);
    $countData   = mysqli_fetch_assoc($countResult);

    $unreadCount = (int) $countData['unread_count'];

    mysqli_stmt_close($countStmt);

    // Fetch all buyer notifications
    $sql = "SELECT notification_id, title, message,
               notification_type, related_order_id,
               is_read, created_at
        FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC";

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $notifications = [];

    while ($row = mysqli_fetch_assoc($result)) {
    $notifications[] = $row;
    }

    mysqli_stmt_close($stmt);

    function buyerNotificationIcon($type)
    {

    $type = strtolower($type);

    if (strpos($type, 'accept') !== false) {
        return "✓";
    }

    if (strpos($type, 'reject') !== false) {
        return "✕";
    }

    if (strpos($type, 'cancel') !== false) {
        return "!";
    }

    return "●";
    }

    function buyerNotificationClass($type)
    {

    $type = strtolower($type);

    if (strpos($type, 'accept') !== false) {
        return "notification-accepted";
    }

    if (strpos($type, 'reject') !== false) {
        return "notification-rejected";
    }

    if (strpos($type, 'cancel') !== false) {
        return "notification-cancelled";
    }

    return "notification-general";
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Notifications | AgroMarket Connect</title>

    <link rel="stylesheet" href="./buyer.css">
</head>

<body>

    <!-- Header -->
    <header class="buyer-header">

        <div class="header-left">
            <h2>AgroMarket Connect</h2>
        </div>

        <nav class="buyer-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="browse_crops.php">Browse Crops</a>
            <a href="my_orders.php">My Orders</a>
            <a href="notifications.php" class="active">
                Notifications
                <?php if ($unreadCount > 0): ?>
                    <span class="notification-nav-count">
                        <?php echo $unreadCount; ?>
                    </span>
                <?php endif; ?>
            </a>
        </nav>

        <div class="header-right">
            <a href="../auth/logout.php" class="logout-link">
                Logout
            </a>
        </div>

    </header>

    <!-- Main Content -->
    <main class="buyer-notifications-container">

        <section class="notification-heading">

            <div>
                <h1>Notifications</h1>
                <p>
                    Stay updated about your orders and purchases.
                </p>
            </div>

            <div class="notification-summary">
                <span><?php echo $unreadCount; ?></span>
                <p>Unread Notifications</p>
            </div>

        </section>

        <!-- Notification Actions -->
        <section class="notification-actions">

            <h2>All Notifications</h2>

            <?php if ($unreadCount > 0): ?>

                <form method="POST">
                    <button type="submit" name="mark_all_read">
                        Mark All as Read
                    </button>
                </form>

            <?php endif; ?>

        </section>

        <!-- Notification List -->
        <section class="notification-list">

            <?php if (count($notifications) > 0): ?>

                <?php foreach ($notifications as $notification): ?>

                    <article class="notification-card
                        <?php echo((int) $notification['is_read'] === 0)
                            ? 'unread' : ''; ?>">

                        <div class="notification-icon
                            <?php echo buyerNotificationClass(
                                $notification['notification_type']
                            ); ?>">

                            <?php echo buyerNotificationIcon(
                                    $notification['notification_type']
                            ); ?>

                        </div>

                        <div class="notification-content">

                            <h3>
                                <?php echo htmlspecialchars(
                                        $notification['title']
                                ); ?>
                            </h3>

                            <p>
                                <?php echo htmlspecialchars(
                                        $notification['message']
                                ); ?>
                            </p>

                            <small>
                                <?php echo date(
                                        "d M Y, h:i A",
                                        strtotime($notification['created_at'])
                                ); ?>
                            </small>

                            <?php if (! empty($notification['related_order_id'])): ?>

                                <a href="my_orders.php"
                                   class="notification-order-link">
                                    View Orders
                                </a>

                            <?php endif; ?>

                        </div>

                        <div class="notification-status">

                            <?php if ((int) $notification['is_read'] === 0): ?>

                                <span class="unread-label">New</span>

                                <form method="POST">

                                    <input
                                        type="hidden"
                                        name="notification_id"
                                        value="<?php echo (int) $notification['notification_id']; ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="mark_read"
                                        class="mark-read-btn">
                                        Mark Read
                                    </button>

                                </form>

                            <?php else: ?>

                                <span class="read-label">Read</span>

                            <?php endif; ?>

                        </div>

                    </article>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="notification-empty">

                    <div class="empty-icon">🔔</div>

                    <h2>No Notifications Yet</h2>

                    <p>
                        You will receive updates here when there is
                        activity related to your orders.
                    </p>

                </div>

            <?php endif; ?>

        </section>

    </main>

    <!-- Footer -->
    <footer class="buyer-footer">
        <p>
            &copy; 2026 AgroMarket Connect. All Rights Reserved.
        </p>
    </footer>

</body>
</html>
