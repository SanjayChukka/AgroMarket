<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order | AgroMarket Connect</title>
    <link rel="stylesheet" href="buyer.css">
</head>

<body>

    <header class="buyer-header">
        <div class="header-left">
            <h2>AgroMarket Connect</h2>
        </div>
        <nav class="buyer-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="browse_crops.php">Browse Crops</a>
            <a href="find_farmers.php">Find Farmers</a>
            <a href="my_orders.php">My Orders</a>
            <a href="order_tracking.php">Track Orders</a>
        </nav>
        <div class="header-right">
            <a href="../auth/logout.php" class="logout-link">Logout</a>
            <div class="profile-menu">
                <button class="profile-avatar" id="profileToggle">B</button>
                <div class="profile-popup" id="profilePopup">
                    <h3>Rahul Kumar</h3>
                    <p>rahul@example.com</p>
                    <a href="profile.php">View Profile</a>
                </div>
            </div>
        </div>
    </header>

    <main class="buyer-page-container">

        <h1>Place Your Order</h1>
        <p>Enter the quantity and delivery information.</p>

        <form class="buyer-form" action="#" method="post">

            <section class="buyer-content-section">
                <h2>Crop Information</h2>

                <label>Crop Name</label>
                <input type="text" value="Tomato" readonly>

                <label>Farmer Name</label>
                <input type="text" value="Suresh Reddy" readonly>

                <label>Price Per KG</label>
                <input type="text" value="₹25" readonly>

                <label>Available Quantity</label>
                <input type="text" value="500 KG" readonly>
            </section>

            <section class="buyer-content-section">
                <h2>Order Quantity</h2>

                <label for="quantity">Required Quantity (KG)</label>
                <input type="number" id="quantity" name="quantity"
                    min="20" max="500" placeholder="Enter quantity">

                <label for="totalAmount">Estimated Total Amount</label>
                <input type="text" id="totalAmount" value="₹0" readonly>
            </section>

            <section class="buyer-content-section">
                <h2>Delivery Information</h2>

                <label for="deliveryType">Delivery Preference</label>
                <select id="deliveryType" name="delivery_type">
                    <option value="">Select Delivery Preference</option>
                    <option value="farmer">Farmer Delivery</option>
                    <option value="buyer">Buyer Pickup</option>
                </select>

                <label for="address">Delivery Address</label>
                <textarea id="address" name="address"
                    placeholder="Enter complete delivery address"></textarea>

                <label for="deliveryDate">Preferred Delivery Date</label>
                <input type="date" id="deliveryDate" name="delivery_date">

                <label for="buyerNote">Additional Instructions</label>
                <textarea id="buyerNote" name="buyer_note"
                    placeholder="Enter additional instructions"></textarea>
            </section>

            <div class="buyer-form-actions">
                <button type="reset">Cancel</button>
                <button type="submit">Submit Order</button>
            </div>

        </form>

    </main>

    <footer>
        <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
    </footer>

    <script src="buyer.js"></script>
    <script>
        const quantity = document.getElementById("quantity");

        quantity.addEventListener("input", function() {
            const amount = (Number(this.value) || 0) * 25;
            document.getElementById("totalAmount").value =
                "₹" + amount.toLocaleString("en-IN");
        });
    </script>

</body>

</html>