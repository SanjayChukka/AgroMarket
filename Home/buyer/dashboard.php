<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard | AgroMarket Connect</title>
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
                <button type="button" class="profile-avatar"
                    id="profileToggle">B</button>

                <div class="profile-popup" id="profilePopup">
                    <div class="popup-avatar">B</div>
                    <h3>Rahul Kumar</h3>
                    <p>rahul@example.com</p>
                    <p>+91 9876543210</p>
                    <a href="profile.php">View Profile</a>
                </div>
            </div>
        </div>

    </header>

    <main class="buyer-dashboard">

        <section class="welcome-section">
            <h1>Welcome, Rahul Kumar!</h1>
            <p>Discover fresh crops directly from farmers.</p>

            <a href="browse_crops.php">Browse Available Crops</a>
        </section>

        <!-- Dashboard Statistics -->

        <section class="buyer-summary">

            <div>
                <h3>Total Orders</h3>
                <p>12</p>
            </div>

            <div>
                <h3>Pending Orders</h3>
                <p>03</p>
            </div>

            <div>
                <h3>Completed Orders</h3>
                <p>08</p>
            </div>

            <div>
                <h3>Saved Farmers</h3>
                <p>05</p>
            </div>

        </section>

        <!-- Quick Actions -->

        <section class="buyer-actions">

            <h2>Quick Actions</h2>

            <div class="action-grid">

                <div class="action-card">
                    <h3>Browse Crops</h3>
                    <p>Explore available crops from different farmers.</p>
                    <a href="browse_crops.php">Browse Now</a>
                </div>

                <div class="action-card">
                    <h3>Find Farmers</h3>
                    <p>Discover farmers and their agricultural products.</p>
                    <a href="find_farmers.php">Find Farmers</a>
                </div>

                <div class="action-card">
                    <h3>My Orders</h3>
                    <p>View and manage your placed orders.</p>
                    <a href="my_orders.php">View Orders</a>
                </div>

                <div class="action-card">
                    <h3>Track Orders</h3>
                    <p>Check the latest status of your purchases.</p>
                    <a href="order_tracking.php">Track Now</a>
                </div>

            </div>

        </section>

        <!-- Recent Orders -->

        <section class="recent-orders">

            <h2>Recent Orders</h2>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Crop</th>
                            <th>Farmer</th>
                            <th>Quantity</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>ORD001</td>
                            <td>Tomato</td>
                            <td>Suresh Reddy</td>
                            <td>100 KG</td>
                            <td>₹2,500</td>
                            <td>Accepted</td>
                            <td><a href="order_tracking.php?id=1">Track</a></td>
                        </tr>

                        <tr>
                            <td>ORD002</td>
                            <td>Rice</td>
                            <td>Ramesh Kumar</td>
                            <td>200 KG</td>
                            <td>₹9,000</td>
                            <td>Pending</td>
                            <td><a href="order_tracking.php?id=2">Track</a></td>
                        </tr>

                    </tbody>
                </table>
            </div>

        </section>

        <!-- Featured Crops -->

        <section class="featured-crops">

            <h2>Explore Crops</h2>

            <div class="crop-grid">

                <div class="crop-card">
                    <h3>Tomato</h3>
                    <p>Category: Vegetables</p>
                    <p>Price: ₹25 / KG</p>
                    <a href="browse_crops.php">View Crops</a>
                </div>

                <div class="crop-card">
                    <h3>Rice</h3>
                    <p>Category: Grains</p>
                    <p>Price: ₹45 / KG</p>
                    <a href="browse_crops.php">View Crops</a>
                </div>

                <div class="crop-card">
                    <h3>Onion</h3>
                    <p>Category: Vegetables</p>
                    <p>Price: ₹30 / KG</p>
                    <a href="browse_crops.php">View Crops</a>
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