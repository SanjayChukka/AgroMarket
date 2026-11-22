<?php
require_once __DIR__ . "/../config/auth_check.php";
require_login("buyer", "../auth/login.php");
require __DIR__ . "/includes/profile-editor.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | AgroMarket Connect</title>
    <link rel="stylesheet" href="buyer.css">
<link rel="icon" href="../favicon.ico" type="image/x-icon">
</head>

<body>

    <?php
    $activePage = "profile.php";
    require __DIR__ . "/includes/header.php";
    ?>

    <main class="buyer-profile-container">

        <div class="profile-heading">
            <h1>My Buyer Profile</h1>
            <p>View and manage your personal and business information.</p>
        </div>

        <?php if ($success): ?><p role="status">Your profile has been saved.</p><?php endif; ?>
        <?php foreach ($errors as $message): ?><p class="form-error" role="alert"><?= e($message) ?></p><?php endforeach; ?>

        <form method="post" action="profile.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['buyer_profile_token']) ?>">

        <!-- Profile Picture -->

        <section class="buyer-profile-section">

            <h2>Profile Picture</h2>

            <div class="buyer-profile-picture">
                <div class="buyer-profile-preview">
                    <?php if (!empty($buyer['profile_image'])): ?>
                        <img src="../<?= e($buyer['profile_image']) ?>" alt="Current profile photo"
                             style="width:100%;height:100%;border-radius:50%;object-fit:cover">
                    <?php else: ?>
                        <?= e(strtoupper(substr($buyer['full_name'], 0, 1))) ?>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="profilePicture">Upload Profile Picture</label>
                    <input type="file" id="profilePicture"
                        name="profile_picture" accept="image/jpeg,image/png">
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
                        name="full_name" required value="<?= e($form['full_name']) ?>">
                </div>

                <div class="profile-field">
                    <label for="buyerEmail">Email Address</label>
                    <input type="email" id="buyerEmail"
                        name="email" required value="<?= e($form['email']) ?>">
                </div>

                <div class="profile-field">
                    <label for="buyerMobile">Mobile Number</label>
                    <input type="tel" id="buyerMobile"
                        name="mobile" required value="<?= e($form['mobile']) ?>">
                </div>

                <div class="profile-field">
                    <label for="buyerDob">Date of Birth</label>
                    <input type="date" id="buyerDob" name="dob" value="<?= e($form['dob']) ?>">
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
                        name="business_name" value="<?= e($form['business_name']) ?>"
                        placeholder="Enter your business name">
                </div>

                <div class="profile-field">
                    <label for="businessType">Business Type</label>
                    <select id="businessType" name="business_type">
                        <option value="">Select Business Type</option>
                        <?php foreach ($businessTypes as $type): ?>
                            <option value="<?= e($type) ?>" <?= $form['business_type'] === $type ? 'selected' : '' ?>><?= e(ucfirst($type)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="profile-field">
                    <label for="gstNumber">GST Number</label>
                    <input type="text" id="gstNumber"
                        name="gst_number" value="<?= e($form['gst_number']) ?>"
                        placeholder="Enter GST number (if applicable)">
                </div>

                <div class="profile-field">
                    <label for="businessAddress">Business Address</label>
                    <input type="text" id="businessAddress"
                        name="business_address" value="<?= e($form['business_address']) ?>"
                        placeholder="Enter business address">
                </div>

            </div>

        </section>

        <!-- Address Information -->

        <section class="buyer-profile-section">

            <h2>Address Information</h2>

            <div class="buyer-profile-grid">

                <div class="profile-field">
                    <label for="deliveryAddress">Delivery Address</label>
                    <input id="deliveryAddress" name="delivery_address" value="<?= e($form['delivery_address']) ?>">
                </div>
                <div class="profile-field">
                    <label for="village">Village / Town / City</label>
                    <input type="text" id="village" name="village_city" value="<?= e($form['village_city']) ?>"
                        placeholder="Enter city">
                </div>

                <div class="profile-field">
                    <label for="district">District</label>
                    <input type="text" id="district" name="district" value="<?= e($form['district']) ?>"
                        placeholder="Enter district">
                </div>

                <div class="profile-field">
                    <label for="state">State</label>
                    <input type="text" id="state" name="state" value="<?= e($form['state']) ?>"
                        placeholder="Enter state">
                </div>

                <div class="profile-field">
                    <label for="pincode">PIN Code</label>
                    <input type="text" id="pincode" name="pincode" value="<?= e($form['pincode']) ?>"
                        maxlength="6" placeholder="Enter PIN code">
                </div>

            </div>

        </section>

        <!-- Actions -->

        <div class="buyer-profile-actions">
            <a href="profile.php">Cancel</a>
            <button type="submit">Save Changes</button>
        </div>

        </form>
    </main>

    <footer>
        <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
    </footer>

    <script src="js/profile.js" defer></script>


</body>

</html>