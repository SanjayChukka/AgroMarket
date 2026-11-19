<?php
    require_once __DIR__ . "/../config/auth_check.php";

    require_login("farmer", "../auth/login.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard | AgroMarket Connect</title>
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
        </nav>

        <div class="header-right">
            <a href="../auth/logout.php" class="logout-link">Logout</a>

            <div class="profile-menu">
                <button type="button" class="profile-avatar"
                    id="profileToggle" aria-label="Open profile details"
                    aria-expanded="false">
                    S
                </button>

                <div class="profile-popup" id="profilePopup">
                    <div class="popup-avatar">S</div>
                    <h3>Sanjay Chukka</h3>
                    <p>sanjay@example.com</p>
                    <p>+91 9876543210</p>
                    <a href="profile.php">View Profile</a>
                </div>
            </div>
        </div>
    </header>


    <!-- Main Dashboard -->
    <main class="dashboard-container">

        <!-- Welcome Section -->
        <section class="welcome-section">
            <h1>Welcome, Farmer!</h1>
            <p>Manage your crops, listings and orders from your dashboard.</p>
        </section>

        <!-- Quick Actions -->
        <section class="dashboard-section">
            <h2>Quick Actions</h2>

            <div class="action-container">

                <div class="action-card">
                    <h3>List Your Crops</h3>
                    <p>Add your available agricultural products to the marketplace.</p>
                    <a href="add_crop.php">Add Crop</a>
                </div>

                <div class="action-card">
                    <h3>Manage Listings</h3>
                    <p>View, edit and manage your existing crop listings.</p>
                    <a href="manage_listings.php">View Listings</a>
                </div>

                <div class="action-card">
                    <h3>Manage Orders</h3>
                    <p>View and manage orders received from buyers.</p>
                    <a href="orders.php">View Orders</a>
                </div>

                <div class="action-card">
                    <h3>My Profile</h3>
                    <p>View and update your personal and farming information.</p>
                    <a href="profile.php">View Profile</a>
                </div>

            </div>
        </section>

        <!-- Listing Summary -->
        <section class="dashboard-section">
            <h2>Listing Overview</h2>

            <div class="summary-container">
                <div class="summary-card">
                    <h3>Total Listings</h3>
                    <p>0</p>
                </div>

                <div class="summary-card">
                    <h3>Active Listings</h3>
                    <p>0</p>
                </div>

                <div class="summary-card">
                    <h3>Inactive Listings</h3>
                    <p>0</p>
                </div>

                <div class="summary-card">
                    <h3>Received Orders</h3>
                    <p>0</p>
                </div>
            </div>
        </section>

        <!-- Recent Listings -->
        <section class="dashboard-section">
            <h2>Recent Crop Listings</h2>

            <div class="recent-listings">
                <p>No crop listings available yet.</p>
                <a href="add_crop.php">Start Listing Your Crops</a>
            </div>
        </section>

        <!-- Recent Orders -->
        <section class="dashboard-section">
            <h2>Recent Orders</h2>

            <div class="recent-orders">
                <p>No orders received yet.</p>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="farmer-footer">
        <p>&copy; 2026 AgroMarket Connect. All Rights Reserved.</p>
    </footer>
    <!-- Script -->
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
