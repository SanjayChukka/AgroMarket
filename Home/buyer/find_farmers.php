<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Farmers | AgroMarket Connect</title>
    <link rel="stylesheet" href="buyer.css">
</head>

<body>

    <header class="buyer-header">
        <div class="header-left">
            <h2>AgroMarket Connect</h2>
        </div>
        <nav class="buyer-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="browse_crops.php">Browse Crops</a>
            <a href="find_farmers.php">Find Farmers</a>
            <a href="my_orders.php">My Orders</a>
            <a href="order_tracking.php">Track Orders</a>
            <a href="profile.php">My Profile</a>
        </nav>
        <div class="header-right">
            <a href="../auth/logout.php" class="logout-link">Logout</a>
            <div class="profile-menu">
                <button class="profile-avatar" id="profileToggle">B</button>
                <div class="profile-popup" id="profilePopup">
                    <h3>Rahul Kumar</h3>
                    <p>rahul@example.com</p>
                    <a href="profile.php">View Profile</a>
                </div>
            </div>
        </div>
    </header>

    <main class="buyer-page-container">

        <h1>Find Farmers</h1>
        <p>Connect with farmers and explore their available products.</p>

        <section class="farmer-search-section">
            <input type="search" placeholder="Search farmer name">
            <input type="text" placeholder="Search by village, district or state">
            <button type="button">Search Farmers</button>
        </section>

        <section class="farmer-list-section">
            <h2>Available Farmers</h2>

            <div class="farmer-grid">

                <div class="farmer-card">
                    <div class="farmer-image-placeholder">Profile Picture</div>
                    <h3>Suresh Reddy</h3>
                    <p>Location: Hyderabad, Telangana</p>
                    <p>Products: Tomato, Onion</p>
                    <p>Experience: 8 Years</p>
                    <a href="browse_crops.php?farmer_id=1">View Crops</a>
                </div>

                <div class="farmer-card">
                    <div class="farmer-image-placeholder">Profile Picture</div>
                    <h3>Ramesh Kumar</h3>
                    <p>Location: Nalgonda, Telangana</p>
                    <p>Products: Rice, Maize</p>
                    <p>Experience: 10 Years</p>
                    <a href="browse_crops.php?farmer_id=2">View Crops</a>
                </div>

                <div class="farmer-card">
                    <div class="farmer-image-placeholder">Profile Picture</div>
                    <h3>Mahesh</h3>
                    <p>Location: Warangal, Telangana</p>
                    <p>Products: Onion, Potato</p>
                    <p>Experience: 5 Years</p>
                    <a href="browse_crops.php?farmer_id=3">View Crops</a>
                </div>

            </div>
        </section>

    </main>

    <footer>
        <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
    </footer>

    <script src="buyer.js"></script>
</body>

</html>