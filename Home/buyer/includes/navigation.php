<nav class="buyer-nav">
            <a href="dashboard.php"<?= $activePage === "dashboard.php" ? ' class="active"' : "" ?>>Dashboard</a>
            <a href="browse_crops.php"<?= $activePage === "browse_crops.php" ? ' class="active"' : "" ?>>Browse Crops</a>
            <a href="find_farmers.php"<?= $activePage === "find_farmers.php" ? ' class="active"' : "" ?>>Find Farmers</a>
            <a href="my_orders.php"<?= $activePage === "my_orders.php" ? ' class="active"' : "" ?>>My Orders</a>
            <a href="order_tracking.php"<?= $activePage === "order_tracking.php" ? ' class="active"' : "" ?>>Track Orders</a>
            <?php if ($showProfileLink): ?>
            <a href="profile.php"<?= $activePage === "profile.php" ? ' class="active"' : "" ?>>My Profile</a>
        <?php endif; ?>
        </nav>
