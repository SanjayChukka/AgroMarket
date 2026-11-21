<nav class="buyer-nav">
            <a href="dashboard.php"<?php echo $activePage === "dashboard.php" ? ' class="active" aria-current="page"' : "" ?>>Dashboard</a>
            <a href="browse_crops.php"<?php echo $activePage === "browse_crops.php" ? ' class="active" aria-current="page"' : "" ?>>Browse Crops</a>
            <a href="find_farmers.php"<?php echo $activePage === "find_farmers.php" ? ' class="active" aria-current="page"' : "" ?>>Find Farmers</a>
            <a href="my_orders.php"<?php echo $activePage === "my_orders.php" ? ' class="active" aria-current="page"' : "" ?>>My Orders</a>
            <a href="order_tracking.php"<?php echo $activePage === "order_tracking.php" ? ' class="active" aria-current="page"' : "" ?>>Track Orders</a>
            <?php if ($showProfileLink): ?>
            <a href="profile.php"<?php echo $activePage === "profile.php" ? ' class="active" aria-current="page"' : "" ?>>My Profile</a>
        <?php endif; ?>
        </nav>
