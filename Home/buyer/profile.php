<?php
require_once __DIR__ . "/../config/auth_check.php";
require_login("buyer", "../auth/login.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | AgroMarket Connect</title>
    <link rel="stylesheet" href="buyer.css">
</head>

<body>

    <header class="buyer-header">

        <div class="header-left">
            <h2>AgroMarket Connect</h2>
        </div>

        <?php
    $activePage = "";
    $showProfileLink = true;
    require __DIR__ . "/includes/navigation.php";
    ?>

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

    <main class="buyer-profile-container">

        <div class="profile-heading">
            <h1>My Buyer Profile</h1>
            <p>View and manage your personal and business information.</p>
        </div>

        <!-- Profile Picture -->

        <section class="buyer-profile-section">

            <h2>Profile Picture</h2>

            <div class="buyer-profile-picture">
                <div class="buyer-profile-preview">B</div>

                <div>
                    <label for="profilePicture">Upload Profile Picture</label>
                    <input type="file" id="profilePicture"
                        name="profile_picture" accept="image/*">
                </div>
            </div>

        </section>

        <!-- Personal Information -->

        <section class="buyer-profile-section">

            <h2>Personal Information</h2>

            <div class="buyer-profile-grid">

                <div class="profile-field">
                    <label for="buyerName">Full Name</label>
                    <input type="text" id="buyerName"
                        name="buyer_name" value="Rahul Kumar">
                </div>

                <div class="profile-field">
                    <label for="buyerEmail">Email Address</label>
                    <input type="email" id="buyerEmail"
                        name="email" value="rahul@example.com">
                </div>

                <div class="profile-field">
                    <label for="buyerMobile">Mobile Number</label>
                    <input type="tel" id="buyerMobile"
                        name="mobile" value="+91 9876543210">
                </div>

                <div class="profile-field">
                    <label for="buyerDob">Date of Birth</label>
                    <input type="date" id="buyerDob" name="dob">
                </div>

            </div>

        </section>

        <!-- Business Information -->

        <section class="buyer-profile-section">

            <h2>Business Information</h2>

            <div class="buyer-profile-grid">

                <div class="profile-field">
                    <label for="businessName">Business Name</label>
                    <input type="text" id="businessName"
                        name="business_name"
                        placeholder="Enter your business name">
                </div>

                <div class="profile-field">
                    <label for="businessType">Business Type</label>
                    <select id="businessType" name="business_type">
                        <option value="">Select Business Type</option>
                        <option value="retailer">Retailer</option>
                        <option value="wholesaler">Wholesaler</option>
                        <option value="distributor">Distributor</option>
                        <option value="restaurant">Restaurant / Hotel</option>
                        <option value="processor">Food Processing Unit</option>
                        <option value="individual">Individual Buyer</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="profile-field">
                    <label for="gstNumber">GST Number</label>
                    <input type="text" id="gstNumber"
                        name="gst_number"
                        placeholder="Enter GST number (if applicable)">
                </div>

                <div class="profile-field">
                    <label for="businessAddress">Business Address</label>
                    <input type="text" id="businessAddress"
                        name="business_address"
                        placeholder="Enter business address">
                </div>

            </div>

        </section>

        <!-- Address Information -->

        <section class="buyer-profile-section">

            <h2>Address Information</h2>

            <div class="buyer-profile-grid">

                <div class="profile-field">
                    <label for="village">Village / Town / City</label>
                    <input type="text" id="village" name="city"
                        placeholder="Enter city">
                </div>

                <div class="profile-field">
                    <label for="district">District</label>
                    <input type="text" id="district" name="district"
                        placeholder="Enter district">
                </div>

                <div class="profile-field">
                    <label for="state">State</label>
                    <input type="text" id="state" name="state"
                        placeholder="Enter state">
                </div>

                <div class="profile-field">
                    <label for="pincode">PIN Code</label>
                    <input type="text" id="pincode" name="pincode"
                        maxlength="6" placeholder="Enter PIN code">
                </div>

            </div>

        </section>

        <!-- Actions -->

        <div class="buyer-profile-actions">
            <button type="button">Cancel</button>
            <button type="button">Save Changes</button>
        </div>

    </main>

    <footer>
        <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
    </footer>

    <script src="js/profile.js" defer></script>

<script src="../js/profile-menu.js" defer></script>
</body>

</html>