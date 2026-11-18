<?php
$pageTitle = "Complaints & Grievances";
include "includes/header.php";
?>

<section class="content-section">
    <h2>Manage Complaints</h2>

    <div class="filter-bar">
        <input type="text" placeholder="Search complaint ID">
        <select>
            <option value="">All Statuses</option>
            <option>Open</option>
            <option>In Progress</option>
            <option>Resolved</option>
        </select>
        <button>Search</button>
    </div>

    <table>
        <tr>
            <th>Complaint ID</th>
            <th>Raised By</th>
            <th>Role</th>
            <th>Subject</th>
            <th>Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <tr>
            <td>GR001</td>
            <td>Rahul Sharma</td>
            <td>Buyer</td>
            <td>Order issue</td>
            <td>17-11-2026</td>
            <td>Open</td>
            <td><button>View</button></td>
        </tr>
        <tr>
            <td>GR002</td>
            <td>Ramesh Kumar</td>
            <td>Farmer</td>
            <td>Payment enquiry</td>
            <td>16-11-2026</td>
            <td>In Progress</td>
            <td><button>View</button></td>
        </tr>
    </table>
</section>

<?php include "includes/footer.php"; ?>