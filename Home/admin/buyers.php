<?php
$pageTitle = "Manage Buyers";
include "includes/header.php";
?>

<section class="content-section">
    <h2>Registered Buyers</h2>

    <div class="filter-bar">
        <input type="text" placeholder="Search buyer by name or mobile">
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
            <th>Buyer Name</th>
            <th>Business Name</th>
            <th>Mobile</th>
            <th>Orders</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <tr>
            <td>B001</td>
            <td>Rahul Sharma</td>
            <td>Fresh Mart</td>
            <td>9876543212</td>
            <td>12</td>
            <td>Active</td>
            <td><button>View</button></td>
        </tr>
        <tr>
            <td>B002</td>
            <td>Priya Enterprises</td>
            <td>Priya Traders</td>
            <td>9876543213</td>
            <td>8</td>
            <td>Active</td>
            <td><button>View</button></td>
        </tr>
    </table>
</section>

<?php include "includes/footer.php"; ?>