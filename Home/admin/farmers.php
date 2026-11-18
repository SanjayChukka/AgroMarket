<?php
$pageTitle = "Manage Farmers";
include "includes/header.php";
?>

<section class="content-section">
    <h2>Registered Farmers</h2>

    <div class="filter-bar">
        <input type="text" placeholder="Search farmer by name or mobile">
        <select>
            <option value="">All Statuses</option>
            <option>Active</option>
            <option>Suspended</option>
        </select>
        <button>Search</button>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Farmer Name</th>
            <th>Mobile</th>
            <th>Location</th>
            <th>Listings</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <tr>
            <td>F001</td>
            <td>Ramesh Kumar</td>
            <td>9876543210</td>
            <td>Hyderabad</td>
            <td>5</td>
            <td>Active</td>
            <td><button>View</button></td>
        </tr>
        <tr>
            <td>F002</td>
            <td>Suresh Reddy</td>
            <td>9876543211</td>
            <td>Warangal</td>
            <td>3</td>
            <td>Active</td>
            <td><button>View</button></td>
        </tr>
    </table>
</section>

<?php include "includes/footer.php"; ?>