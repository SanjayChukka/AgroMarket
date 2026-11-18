<?php
$pageTitle = "Platform Reports";
include "includes/header.php";
?>

<section class="stats-grid">
    <div class="stat-card">
        <h3>Total Orders</h3>
        <p>315</p>
    </div>

    <div class="stat-card">
        <h3>Completed Orders</h3>
        <p>210</p>
    </div>

    <div class="stat-card">
        <h3>Pending Orders</h3>
        <p>28</p>
    </div>

    <div class="stat-card">
        <h3>Transaction Value</h3>
        <p>₹8,50,000</p>
    </div>
</section>

<section class="content-section">
    <h2>Generate Reports</h2>

    <div class="filter-bar">
        <label>Report Type</label>
        <select>
            <option>Farmer Report</option>
            <option>Buyer Report</option>
            <option>Crop Listing Report</option>
            <option>Order Report</option>
            <option>Transaction Report</option>
        </select>

        <label>From Date</label>
        <input type="date">

        <label>To Date</label>
        <input type="date">

        <button>Generate Report</button>
    </div>
</section>

<section class="content-section">
    <h2>Popular Crops</h2>
    <table>
        <tr>
            <th>Crop Name</th>
            <th>Total Listings</th>
            <th>Total Orders</th>
            <th>Demand</th>
        </tr>
        <tr>
            <td>Tomato</td>
            <td>45</td>
            <td>32</td>
            <td>High</td>
        </tr>
        <tr>
            <td>Rice</td>
            <td>38</td>
            <td>25</td>
            <td>High</td>
        </tr>
    </table>
</section>

<?php include "includes/footer.php"; ?>