<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Your Crops | AgroMarket Connect</title>
    <link rel="stylesheet" href="./farmer.css">
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
                    id="profileToggle" aria-label="Open profile details">S</button>

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

    <main class="add-crop-container">

        <h1>List Your Crops</h1>
        <p>Provide your crop details to make them available to buyers.</p>

        <form action="#" method="POST" enctype="multipart/form-data">

            <section>
                <h2>Basic Crop Information</h2>

                <label>Crop Name</label>
                <input type="text" name="crop_name"
                    placeholder="Enter crop name">

                <label>Crop Category</label>
                <select name="category">
                    <option value="">Select Category</option>
                    <option value="vegetables">Vegetables</option>
                    <option value="fruits">Fruits</option>
                    <option value="grains">Grains</option>
                    <option value="pulses">Pulses</option>
                    <option value="spices">Spices</option>
                    <option value="other">Other</option>
                </select>

                <label>Crop Variety</label>
                <input type="text" name="variety"
                    placeholder="Enter crop variety">

                <label>Crop Description</label>
                <textarea name="description"
                    placeholder="Describe your crop"></textarea>
            </section>

            <section>
                <h2>Quantity and Pricing</h2>

                <label>Available Quantity</label>
                <input type="number" name="quantity"
                    placeholder="Enter quantity">

                <label>Quantity Unit</label>
                <select name="unit">
                    <option value="">Select Unit</option>
                    <option value="kg">Kilograms (KG)</option>
                    <option value="quintal">Quintals</option>
                    <option value="ton">Metric Tons</option>
                    <option value="piece">Pieces</option>
                </select>

                <label>Expected Price (₹)</label>
                <input type="number" name="price"
                    placeholder="Enter price">

                <label>Price Unit</label>
                <select name="price_unit">
                    <option value="">Select Price Unit</option>
                    <option value="kg">Per KG</option>
                    <option value="quintal">Per Quintal</option>
                    <option value="ton">Per Ton</option>
                    <option value="piece">Per Piece</option>
                </select>
            </section>

            <section>
                <h2>Harvest and Availability</h2>

                <label>Harvest Date</label>
                <input type="date" name="harvest_date">

                <label>Available From</label>
                <input type="date" name="available_from">

                <label>Quality Grade</label>
                <select name="quality">
                    <option value="">Select Quality</option>
                    <option value="grade_a">Grade A</option>
                    <option value="grade_b">Grade B</option>
                    <option value="grade_c">Grade C</option>
                </select>
            </section>

            <section>
                <h2>Crop Location</h2>

                <label>Village / Town</label>
                <input type="text" name="village"
                    placeholder="Enter village or town">

                <label>District</label>
                <input type="text" name="district"
                    placeholder="Enter district">

                <label>State</label>
                <input type="text" name="state"
                    placeholder="Enter state">

                <label>PIN Code</label>
                <input type="text" name="pincode"
                    placeholder="Enter PIN code">
            </section>

            <section>
                <h2>Crop Images</h2>

                <label>Upload Crop Images</label>
                <input type="file" name="crop_images[]"
                    accept="image/*" multiple>
            </section>

            <div class="form-actions">
                <button type="submit">Submit Crop Listing</button>
                <button type="reset">Clear Form</button>
            </div>

        </form>

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