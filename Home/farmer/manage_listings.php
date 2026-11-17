<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Listings | AgroMarket Connect</title>
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

    <main class="listings-container">

        <div class="listings-heading">
            <div>
                <h1>Manage My Listings</h1>
                <p>View and manage all your crop listings.</p>
            </div>

            <a href="add_crop.php" class="add-crop-link">
                + Add New Crop
            </a>
        </div>

        <section class="listing-summary">

            <div>
                <h3>Total Listings</h3>
                <p>05</p>
            </div>

            <div>
                <h3>Active Listings</h3>
                <p>03</p>
            </div>

            <div>
                <h3>Inactive Listings</h3>
                <p>01</p>
            </div>

            <div>
                <h3>Sold Out</h3>
                <p>01</p>
            </div>

        </section>

        <section class="listing-filters">

            <input type="search" placeholder="Search crop name">

            <select name="status">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="sold_out">Sold Out</option>
                <option value="pending">Pending</option>
            </select>

            <select name="category">
                <option value="">All Categories</option>
                <option value="vegetables">Vegetables</option>
                <option value="fruits">Fruits</option>
                <option value="grains">Grains</option>
                <option value="pulses">Pulses</option>
                <option value="spices">Spices</option>
            </select>

        </section>

        <section class="listings-table-section">

            <h2>My Crop Listings</h2>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Crop</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>Tomato</td>
                            <td>Vegetables</td>
                            <td>500 KG</td>
                            <td>₹25 / KG</td>
                            <td>Hyderabad</td>
                            <td>Active</td>
                            <td>
                                <a href="edit_crop.php?id=1">Edit</a>
                                <button type="button">Deactivate</button>
                                <button type="button">Delete</button>
                            </td>
                        </tr>

                        <tr>
                            <td>Rice</td>
                            <td>Grains</td>
                            <td>1000 KG</td>
                            <td>₹45 / KG</td>
                            <td>Warangal</td>
                            <td>Active</td>
                            <td>
                                <a href="edit_crop.php?id=2">Edit</a>
                                <button type="button">Deactivate</button>
                                <button type="button">Delete</button>
                            </td>
                        </tr>

                        <tr>
                            <td>Onion</td>
                            <td>Vegetables</td>
                            <td>200 KG</td>
                            <td>₹30 / KG</td>
                            <td>Karimnagar</td>
                            <td>Inactive</td>
                            <td>
                                <a href="edit_crop.php?id=3">Edit</a>
                                <button type="button">Activate</button>
                                <button type="button">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
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