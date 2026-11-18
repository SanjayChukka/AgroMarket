<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crop Details | AgroMarket Connect</title>
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

        <p><a href="browse_crops.php">← Back to Crops</a></p>
        <h1>Crop Details</h1>

        <section class="crop-details-layout">

            <div class="crop-image-placeholder crop-large-image">
                Crop Image
            </div>

            <div class="crop-details-info">
                <h2>Fresh Tomatoes</h2>
                <p>Category: Vegetables</p>
                <p>Price: ₹25 / KG</p>
                <p>Available Quantity: 500 KG</p>
                <p>Minimum Order Quantity: 20 KG</p>
                <p>Harvest Date: 15-11-2026</p>
                <p>Location: Hyderabad, Telangana</p>

                <a href="place_order.php?crop_id=1">Place Order</a>
            </div>

        </section>

        <section class="buyer-content-section">
            <h2>Crop Description</h2>
            <p>Fresh tomatoes harvested and supplied directly by the farmer.</p>
        </section>

        <section class="buyer-content-section">
            <h2>Farmer Information</h2>
            <p>Farmer Name: Suresh Reddy</p>
            <p>Location: Hyderabad, Telangana</p>
            <p>Phone: +91 9876543210</p>
            <a href="find_farmers.php">View Farmer Profile</a>
        </section>

    </main>

    <footer>
        <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
    </footer>

    <script src="buyer.js"></script>
</body>

</html>