<?php
$pageTitle = "Admin Dashboard";
include "includes/header.php";
?>

<section class="welcome">
    <h2>Welcome, Administrator!</h2>
    <p>Monitor and manage the AgroMarket platform from here.</p>
</section>

<section class="stats-grid">
    <div class="stat-card">
        <h3>Total Farmers</h3>
        <p>250</p>
    </div>

    <div class="stat-card">
        <h3>Total Buyers</h3>
        <p>180</p>
    </div>

    <div class="stat-card">
        <h3>Crop Listings</h3>
        <p>420</p>
    </div>

    <div class="stat-card">
        <h3>Total Orders</h3>
        <p>315</p>
    </div>

    <div class="stat-card">
        <h3>Pending Orders</h3>
        <p>28</p>
    </div>

    <div class="stat-card">
        <h3>Complaints</h3>
        <p>6</p>
    </div>
</section>

<section class="content-section">
    <h2>Recent Registrations</h2>

    <table>
        <tr>
            <th>Name</th>
            <th>Role</th>
            <th>Registration Date</th>
            <th>Status</th>
        </tr>
        <tr>
            <td>Ramesh Kumar</td>
            <td>Farmer</td>
            <td>17-11-2026</td>
            <td>Active</td>
        </tr>
        <tr>
            <td>Rahul Sharma</td>
            <td>Buyer</td>
            <td>17-11-2026</td>
            <td>Active</td>
        </tr>
    </table>
</section>

<section class="content-section">
    <h2>Recent Platform Activity</h2>
    <ul>
        <li>New farmer registered.</li>
        <li>New crop listing added.</li>
        <li>New buyer order placed.</li>
    </ul>
</section>

<?php include "includes/footer.php"; ?>