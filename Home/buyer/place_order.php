
<?php
session_start();

require_once __DIR__ . "/../config/auth_check.php";
require_login("buyer", "../auth/login.php");

require_once __DIR__ . "/../config/db.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function e($value) {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}

$buyerId = (int)$_SESSION['user_id'];

$listingId = filter_input(INPUT_GET, 'listing_id', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listingId = filter_input(INPUT_POST, 'listing_id', FILTER_VALIDATE_INT);
}

if (!$listingId || $listingId <= 0) {
    die("Invalid crop listing. Please select a crop again.");
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];

$error = "";

// Preserve submitted values
$quantityInput = $_POST['quantity'] ?? "";
$addressInput = trim($_POST['address'] ?? "");
$cityInput = trim($_POST['city'] ?? "");
$stateInput = trim($_POST['state'] ?? "");
$pincodeInput = trim($_POST['pincode'] ?? "");
$buyerNoteInput = trim($_POST['buyer_note'] ?? "");

// Fetch buyer information
$stmt = $link->prepare(
    "SELECT full_name, email FROM users WHERE user_id = ? AND role = 'buyer'"
);
$stmt->bind_param("i", $buyerId);
$stmt->execute();
$buyer = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$buyer) {
    die("Buyer account not found.");
}

// Process order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        if (
            !isset($_POST['csrf_token']) ||
            !hash_equals($csrfToken, $_POST['csrf_token'])
        ) {
            throw new Exception("Invalid request. Please refresh the page and try again.");
        }

        $quantity = filter_var(
            $quantityInput,
            FILTER_VALIDATE_FLOAT
        );

        if ($quantity === false || $quantity <= 0) {
            throw new Exception("Please enter a valid order quantity.");
        }

        if (
            $addressInput === "" ||
            $cityInput === "" ||
            $stateInput === "" ||
            $pincodeInput === ""
        ) {
            throw new Exception("Please complete all delivery address fields.");
        }

        if (!preg_match('/^[0-9]{6}$/', $pincodeInput)) {
            throw new Exception("Please enter a valid 6-digit pincode.");
        }

        if (strlen($addressInput) > 255) {
            throw new Exception("Delivery address must not exceed 255 characters.");
        }

        if (
            strlen($cityInput) > 100 ||
            strlen($stateInput) > 100
        ) {
            throw new Exception("City or state name is too long.");
        }

        if (strlen($buyerNoteInput) > 5000) {
            throw new Exception("Additional instructions are too long.");
        }

        $link->begin_transaction();

        // Lock the listing while validating and placing the order
        $sql = "
            SELECT
                cl.listing_id,
                cl.farmer_id,
                cl.crop_name,
                cl.quantity_available,
                cl.quantity_unit,
                cl.price_per_unit,
                cl.minimum_order_quantity,
                cl.listing_status,
                cl.available_from,
                cl.available_until

            FROM crop_listings cl

            INNER JOIN users u
                ON u.user_id = cl.farmer_id

            WHERE cl.listing_id = ?
              AND u.role = 'farmer'
              AND u.account_status = 'active'

            FOR UPDATE
        ";

        $stmt = $link->prepare($sql);
        $stmt->bind_param("i", $listingId);
        $stmt->execute();

        $crop = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$crop) {
            throw new Exception("Crop listing not found.");
        }

        $today = date('Y-m-d');

        if ($crop['listing_status'] !== 'available') {
            throw new Exception("This crop is currently unavailable.");
        }

        if (
            !empty($crop['available_from']) &&
            $crop['available_from'] > $today
        ) {
            throw new Exception("This crop is not available yet.");
        }

        if (
            !empty($crop['available_until']) &&
            $crop['available_until'] < $today
        ) {
            throw new Exception("The availability period for this crop has ended.");
        }

        $availableQuantity = (float)$crop['quantity_available'];
        $minimumQuantity = $crop['minimum_order_quantity'] !== null
            ? (float)$crop['minimum_order_quantity']
            : 0;

        if ($quantity < $minimumQuantity) {
            throw new Exception(
                "Minimum order quantity is " .
                $minimumQuantity . " " . $crop['quantity_unit'] . "."
            );
        }

        if ($quantity > $availableQuantity) {
            throw new Exception(
                "Only " . $availableQuantity . " " .
                $crop['quantity_unit'] . " is available."
            );
        }

        $unitPrice = (float)$crop['price_per_unit'];
        $subtotal = round($quantity * $unitPrice, 2);

        $farmerId = (int)$crop['farmer_id'];

        // Generate a unique order number
        $orderNumber = "AMC-" . date('Ymd') . "-" .
            strtoupper(bin2hex(random_bytes(4)));

        // Insert order
        $insertSql = "
            INSERT INTO orders (
                order_number,
                buyer_id,
                farmer_id,
                listing_id,
                quantity,
                quantity_unit,
                unit_price,
                subtotal,
                delivery_address,
                delivery_city,
                delivery_state,
                delivery_pincode,
                buyer_note
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $link->prepare($insertSql);

        $stmt->bind_param(
            "siiidsddsssss",
            $orderNumber,
            $buyerId,
            $farmerId,
            $listingId,
            $quantity,
            $crop['quantity_unit'],
            $unitPrice,
            $subtotal,
            $addressInput,
            $cityInput,
            $stateInput,
            $pincodeInput,
            $buyerNoteInput
        );

        $stmt->execute();
        $orderId = $link->insert_id;
        $stmt->close();

        // Record initial order history
        $historySql = "
            INSERT INTO order_status_history (
                order_id,
                changed_by,
                old_status,
                new_status,
                remarks
            )
            VALUES (?, ?, NULL, 'pending', ?)
        ";

        $remarks = "Order placed by buyer.";

        $stmt = $link->prepare($historySql);
        $stmt->bind_param("iis", $orderId, $buyerId, $remarks);
        $stmt->execute();
        $stmt->close();

        // Notify farmer about the new order
        $notificationTitle = "New Order Received";
        $notificationMessage =
            "You have received a new order for " .
            $crop['crop_name'] . ". Order number: " . $orderNumber;

        $notificationType = "new_order";

        $notificationSql = "
            INSERT INTO notifications (
                user_id,
                title,
                message,
                notification_type,
                related_order_id
            )
            VALUES (?, ?, ?, ?, ?)
        ";

        $stmt = $link->prepare($notificationSql);

        $stmt->bind_param(
            "isssi",
            $farmerId,
            $notificationTitle,
            $notificationMessage,
            $notificationType,
            $orderId
        );

        $stmt->execute();
        $stmt->close();

        // Commit all changes
        $link->commit();

        // Redirect to tracking page
        header(
            "Location: order_tracking.php?order_number=" .
            urlencode($orderNumber)
        );
        exit;

    } catch (Throwable $exception) {

        $link->rollback();

        $error = $exception instanceof mysqli_sql_exception
            ? "Unable to place the order right now. Please try again."
            : $exception->getMessage();
    }
}

// Fetch crop information for display
$cropSql = "
    SELECT
        cl.listing_id,
        cl.crop_name,
        cl.quantity_available,
        cl.quantity_unit,
        cl.price_per_unit,
        cl.minimum_order_quantity,
        cl.listing_status,
        cl.available_from,
        cl.available_until,
        u.full_name AS farmer_name

    FROM crop_listings cl

    INNER JOIN users u
        ON u.user_id = cl.farmer_id

    WHERE cl.listing_id = ?
      AND u.role = 'farmer'
      AND u.account_status = 'active'
";

$stmt = $link->prepare($cropSql);
$stmt->bind_param("i", $listingId);
$stmt->execute();
$crop = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$crop) {
    die("Crop listing not found.");
}

$today = date('Y-m-d');

$isAvailable =
    $crop['listing_status'] === 'available'
    && (empty($crop['available_from']) || $crop['available_from'] <= $today)
    && (empty($crop['available_until']) || $crop['available_until'] >= $today);

$minimumQuantity = $crop['minimum_order_quantity'] !== null
    ? (float)$crop['minimum_order_quantity']
    : 0;

$price = (float)$crop['price_per_unit'];

?>

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
            <button class="profile-avatar" id="profileToggle">
                <?= e(strtoupper(substr($buyer['full_name'] ?? 'B', 0, 1))) ?>
            </button>

            <div class="profile-popup" id="profilePopup">
                <h3><?= e($buyer['full_name']) ?></h3>
                <p><?= e($buyer['email']) ?></p>
                <a href="profile.php">View Profile</a>
            </div>
        </div>

    </div>

</header>

<main class="buyer-page-container">

    <h1>Place Your Order</h1>
    <p>Enter the quantity and delivery information.</p>

    <?php if ($error !== ""): ?>
        <p class="form-error"><?= e($error) ?></p>
    <?php endif; ?>

    <?php if (!$isAvailable): ?>

        <section class="buyer-content-section">
            <h2>Crop Unavailable</h2>
            <p>This crop cannot currently be ordered.</p>
            <a href="browse_crops.php">Back to Browse Crops</a>
        </section>

    <?php else: ?>

        <form class="buyer-form" action="place_order.php?listing_id=<?= (int)$listingId ?>"
              method="POST">

            <input type="hidden" name="listing_id" value="<?= (int)$listingId ?>">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

            <section class="buyer-content-section">

                <h2>Crop Information</h2>

                <label>Crop Name</label>
                <input type="text" value="<?= e($crop['crop_name']) ?>" readonly>

                <label>Farmer Name</label>
                <input type="text" value="<?= e($crop['farmer_name']) ?>" readonly>

                <label>Price Per <?= e($crop['quantity_unit']) ?></label>
                <input type="text"
                    value="₹<?= number_format($price, 2) ?>"
                    readonly>

                <label>Available Quantity</label>
                <input type="text"
                    value="<?= e($crop['quantity_available']) ?> <?= e($crop['quantity_unit']) ?>"
                    readonly>

            </section>

            <section class="buyer-content-section">

                <h2>Order Quantity</h2>

                <label for="quantity">
                    Required Quantity (<?= e($crop['quantity_unit']) ?>)
                </label>

                <input
                    type="number"
                    id="quantity"
                    name="quantity"
                    min="<?= $minimumQuantity > 0 ? e($minimumQuantity) : '0.001' ?>"
                    max="<?= e($crop['quantity_available']) ?>"
                    step="0.001"
                    value="<?= e($quantityInput) ?>"
                    placeholder="Enter quantity"
                    required
                >

                <p>
                    Minimum order:
                    <?= $minimumQuantity > 0
                        ? e($minimumQuantity) . " " . e($crop['quantity_unit'])
                        : "No minimum specified" ?>
                </p>

                <label for="totalAmount">Estimated Total Amount</label>

                <input
                    type="text"
                    id="totalAmount"
                    value="₹0.00"
                    readonly
                >

            </section>

            <section class="buyer-content-section">

                <h2>Delivery Information</h2>

                <label for="address">Delivery Address</label>
                <textarea
                    id="address"
                    name="address"
                    maxlength="255"
                    placeholder="House number, street and area"
                    required
                ><?= e($addressInput) ?></textarea>

                <label for="city">City / Village</label>
                <input
                    type="text"
                    id="city"
                    name="city"
                    maxlength="100"
                    value="<?= e($cityInput) ?>"
                    required
                >

                <label for="state">State</label>
                <input
                    type="text"
                    id="state"
                    name="state"
                    maxlength="100"
                    value="<?= e($stateInput) ?>"
                    required
                >

                <label for="pincode">Pincode</label>
                <input
                    type="text"
                    id="pincode"
                    name="pincode"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    value="<?= e($pincodeInput) ?>"
                    placeholder="6-digit pincode"
                    required
                >

                <label for="buyerNote">Additional Instructions</label>
                <textarea
                    id="buyerNote"
                    name="buyer_note"
                    placeholder="Enter additional instructions"
                ><?= e($buyerNoteInput) ?></textarea>

            </section>

            <div class="buyer-form-actions">
                <a href="crop_details.php?listing_id=<?= (int)$listingId ?>">
                    Cancel
                </a>

                <button type="submit">Submit Order</button>
            </div>

        </form>

    <?php endif; ?>

</main>

<footer>
    <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
</footer>

<script src="buyer.js"></script>

<script>
const quantityInput = document.getElementById("quantity");
const totalAmount = document.getElementById("totalAmount");

const pricePerUnit = <?= json_encode($price) ?>;

function calculateTotal() {
    const quantity = Number(quantityInput.value) || 0;
    const amount = quantity * pricePerUnit;

    totalAmount.value = "₹" + amount.toLocaleString("en-IN", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

if (quantityInput) {
    quantityInput.addEventListener("input", calculateTotal);
    calculateTotal();
}
</script>

</body>
</html>
