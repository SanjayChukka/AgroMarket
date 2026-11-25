<?php
    require_once __DIR__ . "/../config/auth_check.php";

    require_login("farmer", "../auth/login.php");

    require_once __DIR__ . "/../config/db.php";

    require_once __DIR__ . "/../includes/notifications.php";

    $userId = (int) $_SESSION['user_id'];
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
    markNotificationsRead($link, $userId, $_POST);
    header("Location: notifications.php");
    exit;
    }

    $unreadCount   = unreadNotificationCount($link, $userId);
    $notifications = notificationsForUser($link, $userId);
    acknowledgeNotificationVisit($link, $userId, $notifications);
    $stmt = $link->prepare("SELECT full_name, email, mobile FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $farmer = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    function farmerNotificationIcon($type)
    {

    $type = strtolower($type);

    if (strpos($type, 'order') !== false) {
        return "📦";
    }

    if (strpos($type, 'cancel') !== false) {
        return "!";
    }

    if (strpos($type, 'payment') !== false) {
        return "₹";
    }

    return "●";
    }

    // Notification styling
    function farmerNotificationClass($type)
    {

    $type = strtolower($type);

    if (strpos($type, 'order') !== false) {
        return "notification-order";
    }

    if (strpos($type, 'cancel') !== false) {
        return "notification-cancelled";
    }

    if (strpos($type, 'payment') !== false) {
        return "notification-payment";
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

    <link rel="stylesheet" href="./farmer.css">
<link rel="icon" href="../favicon.ico" type="image/x-icon">
</head>

<body>

    <!-- Header -->
    <?php
        $activePage   = "notifications.php";
        $headerFarmer = $farmer;
        require __DIR__ . "/includes/header.php";
    ?>

    <!-- Main Content -->
    <main class="farmer-notifications-container">

        <section class="notification-heading">

            <div>
                <h1>Notifications</h1>

                <p>
                    Stay updated about orders and marketplace activities.
                </p>
            </div>

            <div class="notification-summary">

                <span><?php echo $unreadCount; ?></span>

                <p>Unread Notifications</p>

            </div>

        </section>

        <!-- Actions -->
        <section class="notification-actions">

            <h2>All Notifications</h2>

            <?php if ($unreadCount > 0): ?>

                <form method="POST">

                    <button
                        type="submit"
                        name="mark_all_read">
                        Mark All as Read
                    </button>

                </form>

            <?php endif; ?>

        </section>

        <!-- Notifications -->
        <section class="notification-list">

            <?php if (count($notifications) > 0): ?>

                <?php foreach ($notifications as $notification): ?>

                    <article class="notification-card
                        <?php echo((int) $notification['is_read'] === 0)
                            ? 'unread' : ''; ?>">

                        <div class="notification-icon
                            <?php echo farmerNotificationClass(
                                $notification['notification_type']
                            ); ?>">

                            <?php echo farmerNotificationIcon(
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

                                <a href="orders.php"
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
                        You will receive updates here when buyers
                        place orders or perform other activities.
                    </p>

                </div>

            <?php endif; ?>

        </section>

    </main>

    <!-- Footer -->
    <footer class="farmer-footer">

        <p>
            &copy; 2026 AgroMarket Connect. All Rights Reserved.
        </p>

    </footer>

    <!-- Profile Dropdown -->


</body>
</html>
