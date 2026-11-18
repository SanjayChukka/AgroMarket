<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | AgroMarket Connect</title>
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

        <h1>My Orders</h1>
        <p>View all your placed orders and their current status.</p>

        <section class="buyer-summary">
            <div>
                <h3>Total Orders</h3>
                <p>12</p>
            </div>
            <div>
                <h3>Pending</h3>
                <p>03</p>
            </div>
            <div>
                <h3>Accepted</h3>
                <p>04</p>
            </div>
            <div>
                <h3>Completed</h3>
                <p>05</p>
            </div>
        </section>

        <section class="buyer-content-section">
            <h2>Order History</h2>

            <div class="buyer-filter-section">
                <input type="search" placeholder="Search Order ID or Crop">

                <select name="status">
                    <option value="">All Statuses</option>
                    <option>Pending</option>
                    <option>Accepted</option>
                    <option>Processing</option>
                    <option>Shipped</option>
                    <option>Completed</option>
                    <option>Rejected</option>
                    <option>Cancelled</option>
                </select>

                <input type="date" name="order_date">
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Crop</th>
                            <th>Farmer</th>
                            <th>Quantity</th>
                            <th>Total Amount</th>
                            <th>Order Date</th>
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
                            <td>17-11-2026</td>
                            <td>Accepted</td>
                            <td><a href="order_tracking.php?id=1">Track</a></td>
                        </tr>

                        <tr>
                            <td>ORD002</td>
                            <td>Rice</td>
                            <td>Ramesh Kumar</td>
                            <td>200 KG</td>
                            <td>₹9,000</td>
                            <td>16-11-2026</td>
                            <td>Pending</td>
                            <td><a href="order_tracking.php?id=2">Track</a></td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <footer>
        <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
    </footer>

    <script src="buyer.js"></script>
</body>

</html>