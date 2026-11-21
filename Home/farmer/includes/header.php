<?php
require_once __DIR__ . "/../../includes/html.php";
require_once __DIR__ . "/../../includes/notifications.php";

$headerUnread = $unreadCount ?? unreadNotificationCount($link, (int) $_SESSION['user_id']);
$headerName = $headerFarmer['full_name'] ?? $_SESSION['full_name'] ?? "Farmer";
$headerInitial = strtoupper(substr(trim($headerName), 0, 1)) ?: "F";
?>
<header class="farmer-header">
    <div class="header-left">
        <h2>AgroMarket Connect</h2>
    </div>

    <?php require __DIR__ . "/navigation.php"; ?>

    <div class="header-right">
        <div class="notification-menu">
            <a href="notifications.php" class="notification-bell"
               aria-label="Notifications, <?= (int) $headerUnread ?> unread"
               <?= $activePage === "notifications.php" ? 'aria-current="page"' : '' ?>>
                <span class="bell-icon" aria-hidden="true">🔔</span>
                <?php if ($headerUnread > 0): ?>
                    <span class="notification-badge" aria-hidden="true"><?= $headerUnread > 99 ? "99+" : (int) $headerUnread ?></span>
                <?php endif; ?>
            </a>
        </div>
        <div class="profile-menu">
            <button type="button" class="profile-avatar" id="profileToggle"
                    aria-label="Open profile details" aria-expanded="false" aria-controls="profilePopup">
                <?= e($headerInitial) ?>
            </button>
            <div class="profile-popup" id="profilePopup">
                <div class="popup-avatar"><?= e($headerInitial) ?></div>
                <h3><?= e($headerName) ?></h3>
                <p><?= e($headerFarmer['email'] ?? 'Email not provided') ?></p>
                <p><?= e($headerFarmer['mobile'] ?? 'Mobile not provided') ?></p>
                <a href="profile.php">View Profile</a>
            </div>
        </div>
        <a href="../auth/logout.php" class="logout-link">Logout</a>
    </div>
</header>
<script src="../js/profile-menu.js" defer></script>
