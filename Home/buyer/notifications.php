<?php
require_once __DIR__ . "/../config/auth_check.php";

    require_login("buyer", "../auth/login.php");

    require_once __DIR__ . "/../config/db.php";

require_once __DIR__ . "/../includes/notifications.php";

$userId = (int) $_SESSION['user_id'];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    markNotificationsRead($link, $userId, $_POST);
    header("Location: notifications.php");
    exit;
}

$unreadCount = unreadNotificationCount($link, $userId);
$notifications = notificationsForUser($link, $userId);

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
    <?php
    $activePage = "notifications.php";
    require __DIR__ . "/includes/header.php";
    ?>

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
