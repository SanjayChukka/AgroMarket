<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking | AgroMarket Connect</title>
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

        <h1>Track Your Order</h1>
        <p>Check the current progress of your order.</p>

        <section class="buyer-content-section">

            <div class="buyer-filter-section">
                <label for="orderId">Enter Order ID</label>
                <input type="text" id="orderId" placeholder="Example: ORD001">
                <button type="button">Track Order</button>
            </div>

        </section>

        <section class="buyer-content-section">
            <h2>Order Information</h2>

            <div class="buyer-info-grid">
                <div><label>Order ID</label>
                    <p>ORD001</p>
                </div>
                <div><label>Order Date</label>
                    <p>17-11-2026</p>
                </div>
                <div><label>Crop</label>
                    <p>Tomato</p>
                </div>
                <div><label>Farmer</label>
                    <p>Suresh Reddy</p>
                </div>
                <div><label>Quantity</label>
                    <p>100 KG</p>
                </div>
                <div><label>Total Amount</label>
                    <p>₹2,500</p>
                </div>
                <div><label>Current Status</label>
                    <p>Accepted</p>
                </div>
                <div><label>Expected Delivery</label>
                    <p>To be confirmed</p>
                </div>
            </div>
        </section>

        <section class="buyer-content-section">
            <h2>Order Progress</h2>

            <div class="tracking-timeline">
                <div class="tracking-step completed">
                    <span>1</span>
                    <div>
                        <h3>Order Placed</h3>
                        <p>Your order has been submitted.</p>
                    </div>
                </div>

                <div class="tracking-step completed">
                    <span>2</span>
                    <div>
                        <h3>Order Accepted</h3>
                        <p>The farmer has accepted your order.</p>
                    </div>
                </div>

                <div class="tracking-step">
                    <span>3</span>
                    <div>
                        <h3>Processing</h3>
                        <p>Your order is being prepared.</p>
                    </div>
                </div>

                <div class="tracking-step">
                    <span>4</span>
                    <div>
                        <h3>Shipped / Ready for Pickup</h3>
                        <p>Your order is ready for delivery or pickup.</p>
                    </div>
                </div>

                <div class="tracking-step">
                    <span>5</span>
                    <div>
                        <h3>Completed</h3>
                        <p>Your order has been completed.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="buyer-content-section">
            <h2>Delivery Information</h2>
            <p>Delivery Address: Hyderabad, Telangana</p>
            <p>Delivery Preference: Farmer Delivery</p>
        </section>

    </main>

    <footer>
        <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
    </footer>

    <script src="buyer.js"></script>
</body>

</html>