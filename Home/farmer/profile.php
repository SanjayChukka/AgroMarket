<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Profile | AgroMarket Connect</title>
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
                    id="profileToggle" aria-label="Open profile details"
                    aria-expanded="false">S</button>

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

    <main class="profile-container">

        <h1>My Farmer Profile</h1>
        <p>View and manage your personal and farm information.</p>

        <form action="#" method="POST">

            <section class="profile-picture-section">
                <h2>Profile Picture</h2>

                <div class="profile-picture-preview">
                    <img id="profilePreview" alt="Profile picture preview"
                        src="" hidden>
                    <span id="profileInitial">S</span>
                </div>

                <label for="profilePicture">Upload Profile Picture</label>
                <input type="file" id="profilePicture"
                    name="profile_picture" accept="image/*">
            </section>

            <section>
                <h2>Personal Information</h2>

                <label>Full Name</label>
                <input type="text" name="full_name" placeholder="Enter your full name">

                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter your email">

                <label>Mobile Number</label>
                <input type="tel" name="mobile" placeholder="Enter your mobile number">

                <label>Date of Birth</label>
                <input type="date" name="dob">

                <label>Gender</label>
                <select name="gender">
                    <option value="">Select Gender</option>
                    <option>Male</option>
                    <option>Female</option>
                    <option>Other</option>
                </select>
            </section>

            <section>
                <h2>Farm Information</h2>

                <label>Farm Name</label>
                <input type="text" name="farm_name" placeholder="Enter farm name">

                <label>Farm Size (Acres)</label>
                <input type="number" name="farm_size" placeholder="Enter farm size">

                <label>Primary Crops</label>
                <input type="text" name="primary_crops" placeholder="Enter crops you grow">

                <label>Farming Experience (Years)</label>
                <input type="number" name="experience" placeholder="Enter experience">
            </section>

            <section>
                <h2>Address Information</h2>

                <label>Address</label>
                <textarea name="address" placeholder="Enter your farm address"></textarea>

                <label>Village / Town</label>
                <input type="text" name="village">

                <label>District</label>
                <input type="text" name="district">

                <label>State</label>
                <input type="text" name="state">

                <label>PIN Code</label>
                <input type="text" name="pincode">
            </section>

            <button type="submit">Update Profile</button>

        </form>

    </main>

    <footer>
        <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
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