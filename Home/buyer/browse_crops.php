<?php
require_once __DIR__ . "/../config/auth_check.php";
    require_login("buyer", "../auth/login.php");
    require_once __DIR__ . "/../config/db.php";

    // Helper function to safely display values.
    require_once __DIR__ . "/../includes/html.php";

    $buyerId = (int) $_SESSION['user_id'];

    // Fetch buyer details for the profile popup.
    $userQuery = "SELECT full_name, email, mobile
              FROM users
              WHERE user_id = ?";

    $userStmt = mysqli_prepare($link, $userQuery);
    mysqli_stmt_bind_param($userStmt, "i", $buyerId);
    mysqli_stmt_execute($userStmt);

    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($userStmt));

    $fullName = $user['full_name'] ?? "Buyer";
    $email    = $user['email'] ?? "";
    $mobile   = $user['mobile'] ?? "";
    $initial  = strtoupper(substr($fullName, 0, 1));

    // Read search filters.
    $search     = trim($_GET['search'] ?? "");
    $categoryId = filter_var(
    $_GET['category'] ?? "",
    FILTER_VALIDATE_INT
    );

    $categoryId = ($categoryId !== false && $categoryId > 0)
    ? $categoryId
    : 0;

    $minPrice = trim($_GET['min_price'] ?? "");
    $maxPrice = trim($_GET['max_price'] ?? "");
    $location = trim($_GET['location'] ?? "");

    $errors = [];

    // Validate price filters.
    if ($minPrice !== "" && (! is_numeric($minPrice) || (float) $minPrice < 0)) {
    $errors[] = "Enter a valid minimum price.";
    $minPrice = "";
    }

    if ($maxPrice !== "" && (! is_numeric($maxPrice) || (float) $maxPrice < 0)) {
    $errors[] = "Enter a valid maximum price.";
    $maxPrice = "";
    }

    if (
    $minPrice !== "" &&
    $maxPrice !== "" &&
    (float) $minPrice > (float) $maxPrice
    ) {
    $errors[] = "Minimum price cannot be greater than maximum price.";
    }

    // Fetch active crop categories.
    $categoryQuery = "
    SELECT category_id, category_name
    FROM crop_categories
    WHERE is_active = 1
    ORDER BY category_name
";

    $categories = mysqli_query($link, $categoryQuery);

    // Build the crop search query.
    $whereConditions = [
    "cl.listing_status = 'available'",
    "(cl.available_from IS NULL OR cl.available_from <= CURDATE())",
    "(cl.available_until IS NULL OR cl.available_until >= CURDATE())",
    ];

    $params = [];
    $types  = "";

    // Search by crop name or variety.
    if ($search !== "") {
    $whereConditions[] = "(cl.crop_name LIKE ? OR COALESCE(cl.variety, '') LIKE ?)";

    $pattern = "%" . $search . "%";

    $params[] = $pattern;
    $params[] = $pattern;

    $types .= "ss";
    }

    // Filter by category.
    if ($categoryId > 0) {
    $whereConditions[] = "cl.category_id = ?";

    $params[]  = $categoryId;
    $types    .= "i";
    }

    // Filter by minimum price.
    if ($minPrice !== "") {
    $whereConditions[] = "cl.price_per_unit >= ?";

    $params[]  = (float) $minPrice;
    $types    .= "d";
    }

    // Filter by maximum price.
    if ($maxPrice !== "") {
    $whereConditions[] = "cl.price_per_unit <= ?";

    $params[]  = (float) $maxPrice;
    $types    .= "d";
    }

    // Search farmer name and location.
    if ($location !== "") {
    $whereConditions[] = "(
        u.full_name LIKE ?
        OR COALESCE(fp.village, '') LIKE ?
        OR COALESCE(fp.district, '') LIKE ?
        OR COALESCE(fp.state, '') LIKE ?
        OR COALESCE(fp.pincode, '') LIKE ?
    )";

    $locationPattern = "%" . $location . "%";

    for ($i = 0; $i < 5; $i++) {
        $params[] = $locationPattern;
    }

    $types .= "sssss";
    }

    $whereSql  = implode(" AND ", $whereConditions);

    // Fetch available crops with farmer and category details.
    $cropQuery = "
    SELECT
        cl.listing_id,
        cl.crop_name,
        cl.variety,
        cl.description,
        cl.quantity_available,
        cl.quantity_unit,
        cl.price_per_unit,
        cl.minimum_order_quantity,
        cc.category_name,
        u.full_name AS farmer_name,
        fp.village,
        fp.district,
        fp.state,
        (
            SELECT li.image_path
            FROM listing_images li
            WHERE li.listing_id = cl.listing_id
            ORDER BY li.is_primary DESC, li.sort_order ASC, li.image_id ASC
            LIMIT 1
        ) AS image_path
    FROM crop_listings cl
    LEFT JOIN crop_categories cc
        ON cl.category_id = cc.category_id
    LEFT JOIN users u
        ON cl.farmer_id = u.user_id
    LEFT JOIN farmer_profiles fp
        ON cl.farmer_id = fp.farmer_id
    WHERE $whereSql
    ORDER BY cl.created_at DESC, cl.listing_id DESC
";

    $cropStmt = mysqli_prepare($link, $cropQuery);

    // Bind the filters safely.
    if (! empty($params)) {
    $bindParams = [$types];

    foreach ($params as $key => $value) {
        $bindParams[] = &$params[$key];
    }

    call_user_func_array(
        [$cropStmt, "bind_param"],
        $bindParams
    );
    }

    mysqli_stmt_execute($cropStmt);
    $cropsResult = mysqli_stmt_get_result($cropStmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Browse Crops | AgroMarket Connect</title>

    <link rel="stylesheet" href="buyer.css">
</head>

<body>

    <header class="buyer-header">

        <div class="header-left">
            <h2>AgroMarket Connect</h2>
        </div>

        <?php
    $activePage = "";
    $showProfileLink = true;
    require __DIR__ . "/includes/navigation.php";
    ?>

        <div class="header-right">

            <a href="../auth/logout.php" class="logout-link">Logout</a>

            <div class="profile-menu">

                <button type="button"
                    class="profile-avatar"
                    id="profileToggle"
                    aria-label="Open profile details"
                    aria-expanded="false">
                    <?php echo e($initial) ?>
                </button>

                <div class="profile-popup" id="profilePopup">

                    <div class="popup-avatar">
                        <?php echo e($initial) ?>
                    </div>

                    <h3><?php echo e($fullName) ?></h3>

                    <p><?php echo e($email) ?></p>
                    <p><?php echo e($mobile) ?></p>

                    <a href="profile.php">View Profile</a>

                </div>

            </div>

        </div>

    </header>


    <main class="buyer-page-container">

        <h1>Browse Available Crops</h1>

        <p>Explore fresh crops directly from farmers.</p>


        <!-- Crop Search and Filters -->

        <section class="crop-search-section">

            <form action="browse_crops.php" method="GET">

                <input
                    type="search"
                    name="search"
                    placeholder="Search crop name..."
                    value="<?php echo e($search) ?>">

                <select name="category">

                    <option value="">All Categories</option>

                    <?php while ($category = mysqli_fetch_assoc($categories)): ?>

                        <option
                            value="<?php echo (int)$category['category_id'] ?>"
                            <?php echo $categoryId == $category['category_id'] ? 'selected' : '' ?>>

                            <?php echo e($category['category_name']) ?>

                        </option>

                    <?php endwhile; ?>

                </select>

                <input
                    type="number"
                    name="min_price"
                    placeholder="Minimum price"
                    min="0"
                    step="0.01"
                    value="<?php echo e($minPrice) ?>">

                <input
                    type="number"
                    name="max_price"
                    placeholder="Maximum price"
                    min="0"
                    step="0.01"
                    value="<?php echo e($maxPrice) ?>">

                <input
                    type="text"
                    name="location"
                    placeholder="Farmer name or location"
                    value="<?php echo e($location) ?>">

                <button type="submit">Search Crops</button>

                <a href="browse_crops.php">Clear Filters</a>

            </form>

        </section>


        <!-- Available Crop Listings -->

        <section class="crop-listing-section">

            <h2>Available Crops</h2>

            <?php if (! empty($errors)): ?>

                <div class="error-message">

                    <?php foreach ($errors as $error): ?>
                        <p><?php echo e($error) ?></p>
                    <?php endforeach; ?>

                </div>

            <?php elseif (mysqli_num_rows($cropsResult) > 0): ?>

                <div class="buyer-crop-grid">

                    <?php while ($crop = mysqli_fetch_assoc($cropsResult)): ?>

                        <div class="buyer-crop-card">

                            <?php if (! empty($crop['image_path'])): ?>

                                <img
                                    src="../<?php echo e($crop['image_path']) ?>"
                                    alt="<?php echo e($crop['crop_name']) ?>"
                                    class="crop-image">

                            <?php else: ?>

                                <div class="crop-image-placeholder">
                                    Crop Image
                                </div>

                            <?php endif; ?>


                            <h3><?php echo e($crop['crop_name']) ?></h3>

                            <?php if (! empty($crop['variety'])): ?>
                                <p>Variety: <?php echo e($crop['variety']) ?></p>
                            <?php endif; ?>

                            <p>
                                Category:
                                <?php echo e($crop['category_name'] ?? "Uncategorized") ?>
                            </p>

                            <p>
                                Price:
                                ₹<?php echo number_format((float)$crop['price_per_unit'], 2) ?>
                                / <?php echo e($crop['quantity_unit']) ?>
                            </p>

                            <p>
                                Available:
                                <?php echo e($crop['quantity_available']) ?>
                                <?php echo e($crop['quantity_unit']) ?>
                            </p>

                            <?php if ($crop['minimum_order_quantity'] !== null): ?>
                                <p>
                                    Minimum Order:
                                    <?php echo e($crop['minimum_order_quantity']) ?>
                                    <?php echo e($crop['quantity_unit']) ?>
                                </p>
                            <?php endif; ?>

                            <p>
                                Farmer:
                                <?php echo e($crop['farmer_name'] ?? "Farmer unavailable") ?>
                            </p>

                            <p>
                                Location:
                                <?php echo e(implode(", ", array_filter([
    $crop['village'],
    $crop['district'],
    $crop['state'],
])) ?: "Not provided") ?>
                            </p>

                            <a href="crop_details.php?listing_id=<?php echo (int)$crop['listing_id'] ?>">
                                View Details
                            </a>

                        </div>

                    <?php endwhile; ?>

                </div>

            <?php else: ?>

                <p>No available crops match your search.</p>

            <?php endif; ?>

        </section>

    </main>


    <footer>
        <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
    </footer>

<script src="../js/profile-menu.js" defer></script>
</body>

</html>