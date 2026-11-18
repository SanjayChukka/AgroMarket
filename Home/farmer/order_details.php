<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details | AgroMarket Connect</title>
    <link rel="stylesheet" href="farmer.css">
</head>

<body>

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
                    id="profileToggle">S</button>
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

    <main class="order-details-container">

        <div class="details-heading">
            <div>
                <h1>Order Details</h1>
                <p>Review the buyer's order information.</p>
            </div>
            <a href="orders.php">Back to Orders</a>
        </div>

        <section class="order-info-section">
            <h2>Order Information</h2>

            <div class="order-info-grid">
                <div><label>Order ID</label>
                    <p>ORD001</p>
                </div>
                <div><label>Order Date</label>
                    <p>17-11-2026</p>
                </div>
                <div><label>Order Status</label>
                    <p>Pending</p>
                </div>
                <div><label>Payment Status</label>
                    <p>Not Paid</p>
                </div>
            </div>
        </section>

        <section class="order-info-section">
            <h2>Buyer Information</h2>

            <div class="order-info-grid">
                <div><label>Buyer Name</label>
                    <p>Rahul Kumar</p>
                </div>
                <div><label>Email Address</label>
                    <p>rahul@example.com</p>
                </div>
                <div><label>Mobile Number</label>
                    <p>+91 9876543211</p>
                </div>
                <div><label>Business Name</label>
                    <p>Rahul Fresh Stores</p>
                </div>
                <div><label>Delivery Address</label>
                    <p>House No. 12, Main Road</p>
                </div>
                <div><label>Village / Town</label>
                    <p>Hyderabad</p>
                </div>
                <div><label>District</label>
                    <p>Hyderabad</p>
                </div>
                <div><label>State</label>
                    <p>Telangana</p>
                </div>
                <div><label>PIN Code</label>
                    <p>500001</p>
                </div>
            </div>
        </section>

        <section class="order-info-section">
            <h2>Crop and Order Information</h2>

            <div class="order-info-grid">
                <div><label>Crop Name</label>
                    <p>Tomato</p>
                </div>
                <div><label>Quantity Ordered</label>
                    <p>100 KG</p>
                </div>
                <div><label>Price Per KG</label>
                    <p>₹25</p>
                </div>
                <div><label>Total Amount</label>
                    <p>₹2,500</p>
                </div>
                <div><label>Delivery Preference</label>
                    <p>Farmer Delivery</p>
                </div>
                <div><label>Buyer Note</label>
                    <p>Please deliver fresh produce.</p>
                </div>
            </div>
        </section>

    </main>

    <footer>
        <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
    </footer>

    <script>
        const profileToggle = document.getElementById("profileToggle");
        const profilePopup = document.getElementById("profilePopup");

        profileToggle.addEventListener("click", function() {
            profilePopup.classList.toggle("show");
        });

        document.addEventListener("click", function(event) {
            if (!event.target.closest(".profile-menu")) {
                profilePopup.classList.remove("show");
            }
        });
    </script>

</body>

</html>