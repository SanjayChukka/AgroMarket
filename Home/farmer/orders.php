<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | AgroMarket Connect</title>
    <link rel="stylesheet" href="farmer.css">
</head>

<body>

    <header class="farmer-header">
        <div class="header-left">
            <h2>AgroMarket Connect</h2>
        </div>

        <nav class="farmer-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="profile.php">My Profile</a>
            <a href="manage_listings.php">My Listings</a>
            <a href="orders.php">Orders</a>
        </nav>

        <div class="header-right">
            <a href="../auth/logout.php" class="logout-link">Logout</a>
            <div class="profile-menu">
                <button type="button" class="profile-avatar"
                    id="profileToggle">S</button>
                <div class="profile-popup" id="profilePopup">
                    <div class="popup-avatar">S</div>
                    <h3>Sanjay Chukka</h3>
                    <p>sanjay@example.com</p>
                    <p>+91 9876543210</p>
                    <a href="profile.php">View Profile</a>
                </div>
            </div>
        </div>
    </header>

    <main class="orders-container">

        <h1>My Orders</h1>
        <p>View and manage orders received from buyers.</p>

        <section class="order-summary">
            <div>
                <h3>Total Orders</h3>
                <p id="totalOrders">3</p>
            </div>
            <div>
                <h3>Pending</h3>
                <p id="pendingOrders">2</p>
            </div>
            <div>
                <h3>Accepted</h3>
                <p id="acceptedOrders">0</p>
            </div>
            <div>
                <h3>Rejected</h3>
                <p id="rejectedOrders">1</p>
            </div>
        </section>

        <section class="orders-table-section">
            <h2>Received Orders</h2>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Buyer Name</th>
                            <th>Crop</th>
                            <th>Quantity</th>
                            <th>Total Amount</th>
                            <th>Order Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody id="ordersTable">

                        <tr data-id="1" data-status="Pending">
                            <td>ORD001</td>
                            <td>Rahul Kumar</td>
                            <td>Tomato</td>
                            <td>100 KG</td>
                            <td>₹2,500</td>
                            <td>17-11-2026</td>
                            <td class="status-cell">Pending</td>
                            <td class="action-cell">
                                <a href="order_details.php?id=1">View</a>
                                <button type="button" onclick="openConfirmation(1, 'Accept')">Accept</button>
                                <button type="button" onclick="openConfirmation(1, 'Reject')">Reject</button>
                            </td>
                        </tr>

                        <tr data-id="2" data-status="Pending">
                            <td>ORD002</td>
                            <td>Priya Sharma</td>
                            <td>Rice</td>
                            <td>200 KG</td>
                            <td>₹9,000</td>
                            <td>16-11-2026</td>
                            <td class="status-cell">Pending</td>
                            <td class="action-cell">
                                <a href="order_details.php?id=2">View</a>
                                <button type="button" onclick="openConfirmation(2, 'Accept')">Accept</button>
                                <button type="button" onclick="openConfirmation(2, 'Reject')">Reject</button>
                            </td>
                        </tr>

                        <tr data-id="3" data-status="Rejected">
                            <td>ORD003</td>
                            <td>Arjun Reddy</td>
                            <td>Onion</td>
                            <td>50 KG</td>
                            <td>₹1,500</td>
                            <td>15-11-2026</td>
                            <td class="status-cell">Rejected</td>
                            <td class="action-cell">
                                <a href="order_details.php?id=3">View</a>
                                <span class="action-message">Order rejected</span>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <!-- Confirmation Popup -->

    <div class="confirmation-overlay" id="confirmationOverlay">
        <div class="confirmation-box">

            <button type="button" class="popup-close"
                onclick="closeConfirmation()">×</button>

            <h2 id="confirmationTitle">Confirm Order</h2>
            <p id="confirmationMessage"></p>

            <div id="termsArea">
                <label class="terms-label">
                    <input type="checkbox" id="termsCheckbox">
                    I agree to fulfil this order according to the displayed
                    quantity, price and delivery terms.
                </label>
            </div>

            <div class="confirmation-actions">
                <button type="button" onclick="closeConfirmation()">
                    Cancel
                </button>
                <button type="button" id="confirmAction">
                    Confirm
                </button>
            </div>

        </div>
    </div>

    <footer>
        <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
    </footer>

    <script>
        // Profile popup
        const profileToggle = document.getElementById("profileToggle");
        const profilePopup = document.getElementById("profilePopup");

        profileToggle.addEventListener("click", function() {
            profilePopup.classList.toggle("show");
        });

        document.addEventListener("click", function(event) {
            if (!event.target.closest(".profile-menu")) {
                profilePopup.classList.remove("show");
            }
        });

        // Order confirmation
        let selectedOrderId = null;
        let selectedAction = null;

        function openConfirmation(orderId, action) {
            selectedOrderId = orderId;
            selectedAction = action;

            const title = document.getElementById("confirmationTitle");
            const message = document.getElementById("confirmationMessage");
            const termsArea = document.getElementById("termsArea");
            const checkbox = document.getElementById("termsCheckbox");
            const confirmButton = document.getElementById("confirmAction");

            checkbox.checked = false;

            if (action === "Accept") {
                title.textContent = "Accept Order?";
                message.textContent =
                    "Are you sure you want to accept this order? Please review the order details before confirming.";

                termsArea.style.display = "block";
                confirmButton.textContent = "Accept Order";
            } else {
                title.textContent = "Reject Order?";
                message.textContent =
                    "Are you sure you want to reject this buyer's order? This action will mark the order as rejected.";

                termsArea.style.display = "none";
                confirmButton.textContent = "Reject Order";
            }

            document.getElementById("confirmationOverlay").style.display = "flex";
        }

        function closeConfirmation() {
            document.getElementById("confirmationOverlay").style.display = "none";
        }

        document.getElementById("confirmAction").addEventListener("click", function() {

            if (selectedAction === "Accept" &&
                !document.getElementById("termsCheckbox").checked) {
                alert("Please accept the terms and conditions first.");
                return;
            }

            const row = document.querySelector(
                `#ordersTable tr[data-id="${selectedOrderId}"]`
            );

            if (!row) return;

            const newStatus = selectedAction === "Accept" ? "Accepted" : "Rejected";

            row.dataset.status = newStatus;
            row.querySelector(".status-cell").textContent = newStatus;

            // Keep View; remove Accept and Reject.
            row.querySelector(".action-cell").innerHTML =
                `<a href="order_details.php?id=${selectedOrderId}">View</a>
         <span class="action-message">Order ${newStatus.toLowerCase()}</span>`;

            updateOrderSummary();
            closeConfirmation();
        });

        function updateOrderSummary() {
            const rows = document.querySelectorAll("#ordersTable tr");

            let pending = 0;
            let accepted = 0;
            let rejected = 0;

            rows.forEach(row => {
                if (row.dataset.status === "Pending") pending++;
                if (row.dataset.status === "Accepted") accepted++;
                if (row.dataset.status === "Rejected") rejected++;
            });

            document.getElementById("totalOrders").textContent = rows.length;
            document.getElementById("pendingOrders").textContent = pending;
            document.getElementById("acceptedOrders").textContent = accepted;
            document.getElementById("rejectedOrders").textContent = rejected;
        }
    </script>

</body>

</html>