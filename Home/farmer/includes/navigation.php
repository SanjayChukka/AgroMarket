<nav class="farmer-nav">
    <a href="dashboard.php"<?= $activePage === "dashboard.php" ? ' class="active" aria-current="page"' : "" ?>>Dashboard</a>
    <a href="profile.php"<?= $activePage === "profile.php" ? ' class="active" aria-current="page"' : "" ?>>My Profile</a>
    <a href="manage_listings.php"<?= $activePage === "manage_listings.php" ? ' class="active" aria-current="page"' : "" ?>>My Listings</a>
    <a href="orders.php"<?= $activePage === "orders.php" ? ' class="active" aria-current="page"' : "" ?>>Orders</a>
    <a href="complaints.php"<?= $activePage === "complaints.php" ? ' class="active" aria-current="page"' : "" ?>>My Complaints</a>
</nav>
