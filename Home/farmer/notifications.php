
<?php
require_once __DIR__ . "/../config/auth_check.php";

require_login("farmer", "../auth/login.php");

require_once __DIR__ . "/../config/db.php";

$user_id = (int) $_SESSION['user_id'];

// Handle notification actions
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

// Unread count
$countSql = "SELECT COUNT(*) AS unread_count
             FROM notifications
             WHERE user_id = ? AND is_read = 0";

$countStmt = mysqli_prepare($link, $countSql);

mysqli_stmt_bind_param($countStmt, "i", $user_id);
mysqli_stmt_execute($countStmt);

$countResult = mysqli_stmt_get_result($countStmt);
$countData = mysqli_fetch_assoc($countResult);

$unreadCount = (int) $countData['unread_count'];

mysqli_stmt_close($countStmt);

// Fetch notifications
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

// Notification icon
function farmerNotificationIcon($type) {

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
function farmerNotificationClass($type) {

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

            <div class="profile-menu">

                <button
                    type="button"
                    class="profile-avatar"
                    id="profileToggle"
                    aria-label="Open profile details"
                    aria-expanded="false">
                    <?php
                    echo strtoupper(
                        substr($_SESSION['full_name'] ?? 'F', 0, 1)
                    );
                    ?>
                </button>

                <div class="profile-popup" id="profilePopup">

                    <div class="popup-avatar">
                        <?php
                        echo strtoupper(
                            substr($_SESSION['full_name'] ?? 'F', 0, 1)
                        );
                        ?>
                    </div>

                    <h3>
                        <?php echo htmlspecialchars(
                            $_SESSION['full_name'] ?? 'Farmer'
                        ); ?>
                    </h3>

                    <a href="profile.php">View Profile</a>

                </div>

            </div>

        </div>

    </header>

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
                        <?php echo ((int) $notification['is_read'] === 0)
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

                            <?php if (!empty($notification['related_order_id'])): ?>

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
    <script>
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
    </script>

</body>
</html>
