<?php
    require_once __DIR__ . "/../config/auth_check.php";
    require_login("buyer", "../auth/login.php");

    require_once __DIR__ . "/../config/db.php";

    require_once __DIR__ . "/../includes/html.php";

    // Get listing ID from URL
    $listingId = filter_input(INPUT_GET, 'listing_id', FILTER_VALIDATE_INT);

    if (! $listingId || $listingId <= 0) {
    die("Invalid crop listing ID. Please go back and select a crop.");
    }

    // Fetch buyer details
    $buyerId = (int) $_SESSION['user_id'];

    $stmt = $link->prepare(
    "SELECT full_name, email FROM users WHERE user_id = ?"
    );
    $stmt->bind_param("i", $buyerId);
    $stmt->execute();
    $buyer = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Fetch crop and farmer details
    $sql = "
    SELECT
        cl.listing_id,
        cl.crop_name,
        cl.variety,
        cl.description,
        cl.quantity_available,
        cl.quantity_unit,
        cl.price_per_unit,
        cl.minimum_order_quantity,
        cl.quality_grade,
        cl.harvest_date,
        cl.available_from,
        cl.available_until,
        cl.listing_status,

        cc.category_name,

        u.user_id AS farmer_id,
        u.full_name AS farmer_name,
        u.mobile AS farmer_mobile,

        fp.farm_name,
        fp.village,
        fp.district,
        fp.state,
        fp.pincode,
        fp.profile_image,

        (
            SELECT li.image_path
            FROM listing_images li
            WHERE li.listing_id = cl.listing_id
            ORDER BY li.is_primary DESC, li.sort_order ASC, li.image_id ASC
            LIMIT 1
        ) AS crop_image

    FROM crop_listings cl

    LEFT JOIN crop_categories cc
        ON cc.category_id = cl.category_id

    INNER JOIN users u
        ON u.user_id = cl.farmer_id

    LEFT JOIN farmer_profiles fp
        ON fp.farmer_id = cl.farmer_id

    WHERE cl.listing_id = ?
      AND u.role = 'farmer'
      AND u.account_status = 'active'
";

    $stmt = $link->prepare($sql);
    $stmt->bind_param("i", $listingId);
    $stmt->execute();

    $crop = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Listing not found
    if (! $crop) {
    die("Crop listing not found.");
    }

    // Check whether the crop can currently be ordered
    $isAvailable =
    $crop['listing_status'] === 'available'
    && (empty($crop['available_from']) || $crop['available_from'] <= date('Y-m-d'))
    && (empty($crop['available_until']) || $crop['available_until'] >= date('Y-m-d'));

    $location = implode(", ", array_filter([
    $crop['village'],
    $crop['district'],
    $crop['state'],
    $crop['pincode'],
    ]));

    $cropImage = ! empty($crop['crop_image'])
    ? "../" . $crop['crop_image']
    : null;

    $farmerImage = ! empty($crop['profile_image'])
    ? "../" . $crop['profile_image']
    : null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($crop['crop_name']) ?> Details | AgroMarket Connect</title>
    <link rel="stylesheet" href="buyer.css">
<link rel="icon" href="../favicon.ico" type="image/x-icon">
</head>

<body>

<?php
    $activePage = "browse_crops.php";
    require __DIR__ . "/includes/header.php";
?>

<main class="buyer-page-container">

    <p>
        <a href="browse_crops.php">← Back to Crops</a>
    </p>

    <h1>Crop Details</h1>

    <section class="crop-details-layout">

        <?php if ($cropImage): ?>

            <img
                src="<?php echo e($cropImage) ?>"
                alt="<?php echo e($crop['crop_name']) ?>"
                class="crop-image-placeholder crop-large-image"
            >

        <?php else: ?>

            <div class="crop-image-placeholder crop-large-image">
                Crop Image Not Available
            </div>

        <?php endif; ?>

        <div class="crop-details-info">

            <h2><?php echo e($crop['crop_name']) ?></h2>

            <?php if (! empty($crop['variety'])): ?>
                <p>Variety: <?php echo e($crop['variety']) ?></p>
            <?php endif; ?>

            <p>Category: <?php echo e($crop['category_name'] ?? 'Not specified') ?></p>

            <p>
                Price:
                ₹<?php echo number_format((float) $crop['price_per_unit'], 2) ?>
                / <?php echo e($crop['quantity_unit']) ?>
            </p>

            <p>
                Available Quantity:
                <?php echo e($crop['quantity_available']) ?>
                <?php echo e($crop['quantity_unit']) ?>
            </p>

            <p>
                Minimum Order Quantity:
                <?php echo $crop['minimum_order_quantity'] !== null
                        ? e($crop['minimum_order_quantity']) . " " . e($crop['quantity_unit'])
                    : "Not specified" ?>
            </p>

            <p>
                Quality:
                <?php echo e($crop['quality_grade'] ?: 'Not specified') ?>
            </p>

            <p>
                Harvest Date:
                <?php echo ! empty($crop['harvest_date'])
                        ? date('d-m-Y', strtotime($crop['harvest_date']))
                    : "Not specified" ?>
            </p>

            <p>Location: <?php echo e($location) ?></p>

            <p>
                Availability:
                <?php echo $isAvailable ? "Available" : "Currently Unavailable" ?>
            </p>

            <?php if ($isAvailable): ?>

                <a class="place-order-button" href="place_order.php?listing_id=<?php echo (int) $crop['listing_id'] ?>">
                    Place Order
                </a>

            <?php else: ?>

                <p>This crop cannot currently be ordered.</p>

            <?php endif; ?>

        </div>

    </section>

    <section class="buyer-content-section">

        <h2>Crop Description</h2>

        <p>
            <?php echo ! empty($crop['description'])
                    ? nl2br(e($crop['description']))
                : "No description provided by the farmer." ?>
        </p>

    </section>

    <section class="buyer-content-section">

        <h2>Farmer Information</h2>

        <?php if ($farmerImage): ?>

            <img
                src="<?php echo e($farmerImage) ?>"
                alt="<?php echo e($crop['farmer_name']) ?>"
                width="100"
                height="100"
            >

        <?php endif; ?>

        <p>Farmer Name: <?php echo e($crop['farmer_name']) ?></p>

        <?php if (! empty($crop['farm_name'])): ?>
            <p>Farm Name: <?php echo e($crop['farm_name']) ?></p>
        <?php endif; ?>

        <p>Location: <?php echo e($location) ?></p>

        <p>
            Phone:
            <?php echo ! empty($crop['farmer_mobile'])
                    ? e($crop['farmer_mobile'])
                : "Not provided" ?>
        </p>

        <a href="browse_crops.php?farmer_id=<?php echo (int) $crop['farmer_id'] ?>">
            View Other Crops From This Farmer
        </a>

    </section>

</main>

<footer>
    <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
</footer>


</body>
</html>
