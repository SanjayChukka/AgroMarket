<?php
require_once __DIR__ . "/../../config/auth_check.php";

require_login("admin", "../../auth/login.php");

$pageTitle = $pageTitle ?? "Admin Dashboard";
?>
<?php
$pageTitle = $pageTitle ?? "Admin Dashboard";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | AgroMarket</title>
    <link rel="stylesheet" href="admin.css">
</head>

<body>

    <div class="admin-layout">

        <aside class="sidebar">
            <h2>AgroMarket</h2>
            <p class="admin-label">ADMIN PANEL</p>

            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="farmers.php">Farmers</a>
                <a href="buyers.php">Buyers</a>
                <a href="listings.php">Crop Listings</a>
                <a href="orders.php">Orders</a>
                <a href="reports.php">Reports</a>
                <a href="complaints.php">Complaints</a>
                <a href="profile.php">My Profile</a>
                <a href="../auth/logout.php">Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
                <span>Administrator</span>
            </header>