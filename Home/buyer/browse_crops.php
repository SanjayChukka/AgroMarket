<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Crops | AgroMarket Connect</title>
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

        <h1>Browse Available Crops</h1>
        <p>Explore fresh crops directly from farmers.</p>

        <section class="crop-search-section">
            <input type="search" placeholder="Search crop name...">

            <select>
                <option value="">All Categories</option>
                <option>Vegetables</option>
                <option>Fruits</option>
                <option>Grains</option>
                <option>Pulses</option>
            </select>

            <input type="number" placeholder="Minimum price">
            <input type="number" placeholder="Maximum price">

            <input type="text" placeholder="Location">
            <button type="button">Search Crops</button>
        </section>

        <section class="crop-listing-section">
            <h2>Available Crops</h2>

            <div class="buyer-crop-grid">

                <div class="buyer-crop-card">
                    <div class="crop-image-placeholder">Crop Image</div>
                    <h3>Tomato</h3>
                    <p>Category: Vegetables</p>
                    <p>Price: ₹25 / KG</p>
                    <p>Available: 500 KG</p>
                    <p>Farmer: Suresh Reddy</p>
                    <p>Location: Hyderabad</p>
                    <a href="crop_details.php?id=1">View Details</a>
                </div>

                <div class="buyer-crop-card">
                    <div class="crop-image-placeholder">Crop Image</div>
                    <h3>Rice</h3>
                    <p>Category: Grains</p>
                    <p>Price: ₹45 / KG</p>
                    <p>Available: 1000 KG</p>
                    <p>Farmer: Ramesh Kumar</p>
                    <p>Location: Nalgonda</p>
                    <a href="crop_details.php?id=2">View Details</a>
                </div>

                <div class="buyer-crop-card">
                    <div class="crop-image-placeholder">Crop Image</div>
                    <h3>Onion</h3>
                    <p>Category: Vegetables</p>
                    <p>Price: ₹30 / KG</p>
                    <p>Available: 300 KG</p>
                    <p>Farmer: Mahesh</p>
                    <p>Location: Warangal</p>
                    <a href="crop_details.php?id=3">View Details</a>
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