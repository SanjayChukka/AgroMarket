<?php
$pageTitle = "Admin Profile";
include "includes/header.php";
?>

<section class="content-section profile-section">
    <h2>Administrator Information</h2>

    <div class="profile-picture">
        <div class="avatar">A</div>
        <p>Admin Profile Picture</p>
        <input type="file" accept="image/*">
    </div>

    <form>
        <div class="form-grid">
            <div>
                <label>Full Name</label>
                <input type="text" value="Administrator">
            </div>

            <div>
                <label>Email Address</label>
                <input type="email" value="admin@example.com">
            </div>

            <div>
                <label>Mobile Number</label>
                <input type="tel" value="9876543210">
            </div>

            <div>
                <label>Role</label>
                <input type="text" value="Super Admin" readonly>
            </div>
        </div>

        <button type="button">Save Changes</button>
        <button type="reset" class="secondary-btn">Cancel</button>
    </form>
</section>

<?php include "includes/footer.php"; ?>