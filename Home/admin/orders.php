<?php
$pageTitle = "Manage Orders";
include "includes/header.php";
?>

<section class="content-section">
    <h2>All Platform Orders</h2>

    <div class="filter-bar">
        <input type="text" placeholder="Search order ID">
        <select>
            <option value="">All Statuses</option>
            <option>Pending</option>
            <option>Accepted</option>
            <option>Rejected</option>
            <option>Completed</option>
        </select>
        <button>Search</button>
    </div>

    <table>
        <tr>
            <th>Order ID</th>
            <th>Buyer</th>
            <th>Farmer</th>
            <th>Crop</th>
            <th>Quantity</th>
            <th>Order Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <tr>
            <td>ORD001</td>
            <td>Rahul Sharma</td>
            <td>Ramesh Kumar</td>
            <td>Tomato</td>
            <td>100 Kg</td>
            <td>17-11-2026</td>
            <td>Pending</td>
            <td><button>View</button></td>
        </tr>
        <tr>
            <td>ORD002</td>
            <td>Priya Traders</td>
            <td>Suresh Reddy</td>
            <td>Rice</td>
            <td>200 Kg</td>
            <td>16-11-2026</td>
            <td>Accepted</td>
            <td><button>View</button></td>
        </tr>
    </table>
</section>

<?php include "includes/footer.php"; ?>