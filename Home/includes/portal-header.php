
<?php

require_once __DIR__ . "/html.php";
require_once __DIR__ . "/notifications.php";


// Fetch the notification badge count.
$badgeCount = notificationBadgeCount(
    $link,
    (int) $_SESSION['user_id']
);


// Retrieve the user's display name.
$headerName = $headerUser['full_name']
    ?? $_SESSION['full_name']
    ?? ucfirst($headerRole);


// Generate the profile avatar initial.
$headerInitial = strtoupper(substr(trim($headerName), 0, 1))
    ?: strtoupper($headerRole[0]);

?>

<!-- Portal Header -->
<header class="<?= e($headerRole); ?>-header">

    <!-- Brand and Logo -->
    <div class="header-left">

        <?php require __DIR__ . '/brand-home.php'; ?>

    </div>


    <!-- Role-specific Navigation -->
    <?php require $navigationFile; ?>


    <!-- Header Right Section -->
    <div class="header-right">

        <!-- Notification Menu -->
        <div class="notification-menu">

            <a
                href="notifications.php"
                class="notification-bell"
                aria-label="Notifications, <?= (int) $badgeCount; ?> new"
                <?= $activePage === "notifications.php"
                    ? 'aria-current="page"'
                    : '';
                ?>
            >

                <!-- Notification Bell Icon -->
                <span class="bell-icon" aria-hidden="true">
                    🔔
                </span>


                <!-- Notification Badge -->
                <?php if ($badgeCount > 0): ?>

                    <span
                        class="notification-badge"
                        aria-hidden="true"
                    >
                        <?= $badgeCount > 99
                            ? "99+"
                            : (int) $badgeCount;
                        ?>
                    </span>

                <?php endif; ?>

            </a>

        </div>


        <!-- Profile Menu -->
        <div class="profile-menu">

            <!-- Profile Avatar Button -->
            <button
                type="button"
                class="profile-avatar"
                id="profileToggle"
                aria-label="Open profile details"
                aria-expanded="false"
                aria-controls="profilePopup"
            >
                <?= e($headerInitial); ?>
            </button>


            <!-- Profile Popup -->
            <div class="profile-popup" id="profilePopup">

                <!-- Popup Avatar -->
                <div class="popup-avatar">
                    <?= e($headerInitial); ?>
                </div>


                <!-- User Full Name -->
                <h3>
                    <?= e($headerName); ?>
                </h3>


                <!-- User Email -->
                <p>
                    <?= e(
                        $headerUser['email'] ?? 'Email not provided'
                    ); ?>
                </p>


                <!-- User Mobile Number -->
                <p>
                    <?= e(
                        $headerUser['mobile'] ?? 'Mobile not provided'
                    ); ?>
                </p>


                <!-- View Profile Link -->
                <a href="profile.php">
                    View Profile
                </a>

            </div>

        </div>


        <!-- Sign-out Section -->
        <?php require __DIR__ . '/signout.php'; ?>

    </div>

</header>


<!-- Profile Menu JavaScript -->
<script src="../js/profile-menu.js" defer></script>
