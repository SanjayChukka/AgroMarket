<?php
$pageTitle = "Crop Listings";
include "includes/header.php";
?>

<section class="content-section">
    <h2>All Crop Listings</h2>

    <div class="filter-bar">
        <input type="text" placeholder="Search crop name">
        <select>
            <option value="">All Categories</option>
            <option>Vegetables</option>
            <option>Fruits</option>
            <option>Grains</option>
        </select>
        <select>
            <option value="">All Statuses</option>
            <option>Available</option>
            <option>Sold Out</option>
        </select>
        <button>Search</button>
    </div>

    <table>
        <tr>
            <th>Listing ID</th>
            <th>Crop</th>
            <th>Farmer</th>
            <th>Quantity</th>
            <th>Price / Kg</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <tr>
            <td>C001</td>
            <td>Tomato</td>
            <td>Ramesh Kumar</td>
            <td>500 Kg</td>
            <td>₹25</td>
            <td>Available</td>
            <td><button>View</button></td>
        </tr>
        <tr>
            <td>C002</td>
            <td>Rice</td>
            <td>Suresh Reddy</td>
            <td>1000 Kg</td>
            <td>₹45</td>
            <td>Available</td>
            <td><button>View</button></td>
        </tr>
    </table>
</section>

<?php include "includes/footer.php"; ?>