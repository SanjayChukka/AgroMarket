
<?php
    if (session_status() === PHP_SESSION_NONE) {
    session_start();
    }

    require_once __DIR__ . "/../config/auth_check.php";
    require_login("farmer", "../auth/login.php");

    require_once __DIR__ . "/../config/db.php";

    $farmerId = (int) $_SESSION['user_id'];

    // Escape output before displaying it in HTML
    function e($value)
    {
    return htmlspecialchars((string) ($value ?? ""), ENT_QUOTES, "UTF-8");
    }

    // ------------------------------------------
    // 1. Generate CSRF token
    // ------------------------------------------

    if (empty($_SESSION['listing_csrf_token'])) {
    $_SESSION['listing_csrf_token'] = bin2hex(random_bytes(32));
    }

    $csrfToken = $_SESSION['listing_csrf_token'];

    // ------------------------------------------
    // 2. Fetch farmer profile details
    // ------------------------------------------

    $farmerSql = "SELECT full_name, email, mobile
              FROM users
              WHERE user_id = ? AND role = 'farmer'
              LIMIT 1";

    $stmt = mysqli_prepare($link, $farmerSql);
    mysqli_stmt_bind_param($stmt, "i", $farmerId);
    mysqli_stmt_execute($stmt);

    $farmerResult = mysqli_stmt_get_result($stmt);
    $farmer       = mysqli_fetch_assoc($farmerResult) ?: [];

    mysqli_stmt_close($stmt);

    $farmerName   = $farmer['full_name'] ?? ($_SESSION['full_name'] ?? "Farmer");
    $farmerEmail  = $farmer['email'] ?? "Email not provided";
    $farmerMobile = $farmer['mobile'] ?? "Mobile not provided";

    $avatarInitial = strtoupper(substr(trim($farmerName), 0, 1));

    if ($avatarInitial === "") {
    $avatarInitial = "F";
    }

    // ------------------------------------------
    // 3. Process listing actions
    // ------------------------------------------

    $actionMessage = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $submittedToken = $_POST['csrf_token'] ?? "";

    if (! hash_equals($csrfToken, $submittedToken)) {

        $actionMessage = "Invalid request. Please refresh the page and try again.";

    } else {

        $listingId = filter_var(
            $_POST['listing_id'] ?? "",
            FILTER_VALIDATE_INT
        );

        $action = $_POST['action'] ?? "";

        $allowedActions = [
            "activate",
            "deactivate",
            "remove",
        ];

        if (! $listingId || ! in_array($action, $allowedActions, true)) {

            $actionMessage = "Invalid listing action.";

        } else {

            // Only update listings belonging to the logged-in farmer.
            // Removed listings cannot be modified again.

            $newStatus = "";

            if ($action === "activate") {
                $newStatus = "available";
            } elseif ($action === "deactivate") {
                $newStatus = "paused";
            } elseif ($action === "remove") {
                $newStatus = "removed";
            }

            $updateSql = "UPDATE crop_listings
                          SET listing_status = ?
                          WHERE listing_id = ?
                          AND farmer_id = ?
                          AND listing_status <> 'removed'";

            $stmt = mysqli_prepare($link, $updateSql);

            mysqli_stmt_bind_param(
                $stmt,
                "sii",
                $newStatus,
                $listingId,
                $farmerId
            );

            mysqli_stmt_execute($stmt);

            if (mysqli_stmt_affected_rows($stmt) > 0) {

                if ($action === "activate") {
                    $actionMessage = "Listing activated successfully.";
                } elseif ($action === "deactivate") {
                    $actionMessage = "Listing deactivated successfully.";
                } else {
                    $actionMessage = "Listing removed successfully.";
                }

            } else {
                $actionMessage = "No listing was updated. It may already have been removed.";
            }

            mysqli_stmt_close($stmt);
        }
    }

    // Redirect after POST to prevent duplicate actions on refresh.
    $_SESSION['listing_action_message'] = $actionMessage;

    header("Location: manage_listings.php");
    exit;
    }

    // Display action message after redirect
    if (isset($_SESSION['listing_action_message'])) {
    $actionMessage = $_SESSION['listing_action_message'];
    unset($_SESSION['listing_action_message']);
    }

    // ------------------------------------------
    // 4. Fetch listing statistics
    // ------------------------------------------

    $listingStats = [
    "total_listings"    => 0,
    "active_listings"   => 0,
    "inactive_listings" => 0,
    "sold_out_listings" => 0,
    ];

    $statsSql = "SELECT
                COUNT(*) AS total_listings,
                COALESCE(SUM(listing_status = 'available'), 0) AS active_listings,
                COALESCE(SUM(listing_status = 'paused'), 0) AS inactive_listings,
                COALESCE(SUM(listing_status = 'sold_out'), 0) AS sold_out_listings
             FROM crop_listings
             WHERE farmer_id = ?
             AND listing_status <> 'removed'";

    $stmt = mysqli_prepare($link, $statsSql);
    mysqli_stmt_bind_param($stmt, "i", $farmerId);
    mysqli_stmt_execute($stmt);

    $statsResult  = mysqli_stmt_get_result($stmt);
    $listingStats = mysqli_fetch_assoc($statsResult) ?: $listingStats;

    mysqli_stmt_close($stmt);

    // ------------------------------------------
    // 5. Fetch active categories
    // ------------------------------------------

    $categories = [];

    $categorySql = "SELECT category_id, category_name
                FROM crop_categories
                WHERE is_active = 1
                ORDER BY category_name ASC";

    $categoryResult = mysqli_query($link, $categorySql);

    if ($categoryResult) {
    while ($row = mysqli_fetch_assoc($categoryResult)) {
        $categories[] = $row;
    }
    }

    // ------------------------------------------
    // 6. Read search and filter values
    // ------------------------------------------

    $search         = trim($_GET['search'] ?? "");
    $statusFilter   = $_GET['status'] ?? "";
    $categoryFilter = $_GET['category'] ?? "";

    $allowedStatuses = [
    "available",
    "paused",
    "sold_out",
    ];

    if (! in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = "";
    }

    $categoryId = 0;

    if (ctype_digit((string) $categoryFilter)) {
    $categoryId = (int) $categoryFilter;
    }

    // ------------------------------------------
    // 7. Fetch farmer's crop listings
    // ------------------------------------------

    $listings = [];

    $searchPattern = "%" . $search . "%";

    $listingsSql = "SELECT
                    cl.listing_id,
                    cl.crop_name,
                    cl.variety,
                    cl.quantity_available,
                    cl.quantity_unit,
                    cl.price_per_unit,
                    cl.listing_status,
                    cl.created_at,
                    cc.category_name,
                    fp.village,
                    fp.district,
                    fp.state
                FROM crop_listings AS cl

                INNER JOIN crop_categories AS cc
                    ON cl.category_id = cc.category_id

                LEFT JOIN farmer_profiles AS fp
                    ON cl.farmer_id = fp.farmer_id

                WHERE cl.farmer_id = ?
                AND cl.listing_status <> 'removed'

                AND (
                    ? = ''
                    OR cl.crop_name LIKE ?
                    OR cl.variety LIKE ?
                )

                AND (
                    ? = ''
                    OR cl.listing_status = ?
                )

                AND (
                    ? = 0
                    OR cl.category_id = ?
                )

                ORDER BY cl.created_at DESC, cl.listing_id DESC";

    $stmt = mysqli_prepare($link, $listingsSql);

    mysqli_stmt_bind_param(
    $stmt,
    "isssssis",
    $farmerId,
    $search,
    $searchPattern,
    $searchPattern,
    $statusFilter,
    $statusFilter,
    $categoryId,
    $categoryId
    );

    mysqli_stmt_execute($stmt);

    $listingsResult = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($listingsResult)) {
    $listings[] = $row;
    }

    mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Listings | AgroMarket Connect</title>

    <link rel="stylesheet" href="farmer.css">
</head>

<body>

    <!-- Header -->
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
                    id="profileToggle" aria-expanded="false">

                    <?php echo e($avatarInitial); ?>

                </button>

                <div class="profile-popup" id="profilePopup">

                    <div class="popup-avatar">
                        <?php echo e($avatarInitial); ?>
                    </div>

                    <h3><?php echo e($farmerName); ?></h3>
                    <p><?php echo e($farmerEmail); ?></p>
                    <p><?php echo e($farmerMobile); ?></p>

                    <a href="profile.php">View Profile</a>

                </div>

            </div>

        </div>

    </header>

    <!-- Main Content -->
    <main class="listings-container">

        <!-- Heading -->
        <div class="listings-heading">

            <div>
                <h1>Manage My Listings</h1>
                <p>View and manage all your crop listings.</p>
            </div>

            <a href="add_crop.php" class="add-crop-link">
                + Add New Crop
            </a>

        </div>

        <!-- Action Message -->
        <?php if ($actionMessage !== ""): ?>

            <div class="success-message">
                <?php echo e($actionMessage); ?>
            </div>

        <?php endif; ?>

        <!-- Listing Summary -->
        <section class="listing-summary">

            <div>
                <h3>Total Listings</h3>
                <p><?php echo (int) $listingStats['total_listings']; ?></p>
            </div>

            <div>
                <h3>Active Listings</h3>
                <p><?php echo (int) $listingStats['active_listings']; ?></p>
            </div>

            <div>
                <h3>Inactive Listings</h3>
                <p><?php echo (int) $listingStats['inactive_listings']; ?></p>
            </div>

            <div>
                <h3>Sold Out</h3>
                <p><?php echo (int) $listingStats['sold_out_listings']; ?></p>
            </div>

        </section>

        <!-- Search and Filters -->
        <section class="listing-filters">

            <form method="GET" action="manage_listings.php">

                <input
                    type="search"
                    name="search"
                    placeholder="Search crop name or variety"
                    value="<?php echo e($search); ?>">

                <select name="status">

                    <option value="">All Status</option>

                    <option value="available"
                        <?php echo $statusFilter === "available" ? "selected" : ""; ?>>
                        Active
                    </option>

                    <option value="paused"
                        <?php echo $statusFilter === "paused" ? "selected" : ""; ?>>
                        Inactive
                    </option>

                    <option value="sold_out"
                        <?php echo $statusFilter === "sold_out" ? "selected" : ""; ?>>
                        Sold Out
                    </option>

                </select>

                <select name="category">

                    <option value="">All Categories</option>

                    <?php foreach ($categories as $category): ?>

                        <option
                            value="<?php echo (int) $category['category_id']; ?>"
                            <?php echo $categoryId === (int) $category['category_id'] ? "selected" : ""; ?>>

                            <?php echo e($category['category_name']); ?>

                        </option>

                    <?php endforeach; ?>

                </select>

                <button type="submit">Apply Filters</button>

                <a href="manage_listings.php">Clear Filters</a>

            </form>

        </section>

        <!-- Listings Table -->
        <section class="listings-table-section">

            <h2>My Crop Listings</h2>

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>
                            <th>Crop</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if (! empty($listings)): ?>

                            <?php foreach ($listings as $listing): ?>

                                <tr>

                                    <td>
                                        <strong><?php echo e($listing['crop_name']); ?></strong>

                                        <?php if (! empty($listing['variety'])): ?>
                                            <br>
                                            <small><?php echo e($listing['variety']); ?></small>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php echo e($listing['category_name']); ?>
                                    </td>

                                    <td>
                                        <?php echo e($listing['quantity_available']); ?>
                                        <?php echo e($listing['quantity_unit']); ?>
                                    </td>

                                    <td>
                                        ₹<?php echo number_format((float) $listing['price_per_unit'], 2); ?>
                                        / <?php echo e($listing['quantity_unit']); ?>
                                    </td>

                                    <td>
                                        <?php
                                            $locationParts = array_filter([
                                                $listing['village'] ?? "",
                                                $listing['district'] ?? "",
                                                $listing['state'] ?? "",
                                            ]);

                                            echo ! empty($locationParts)
                                                ? e(implode(", ", $locationParts))
                                                : "Location unavailable";
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                            $statusLabels = [
                                                "available" => "Active",
                                                "paused"    => "Inactive",
                                                "sold_out"  => "Sold Out",
                                            ];

                                            echo e($statusLabels[$listing['listing_status']] ?? $listing['listing_status']);
                                        ?>
                                    </td>

                                    <td>

                                        <a href="edit_crop.php?id=<?php echo (int) $listing['listing_id']; ?>">
                                            Edit
                                        </a>

                                        <?php if ($listing['listing_status'] === "available"): ?>

                                            <form method="POST" action="manage_listings.php">

                                                <input type="hidden" name="csrf_token"
                                                    value="<?php echo e($csrfToken); ?>">

                                                <input type="hidden" name="listing_id"
                                                    value="<?php echo (int) $listing['listing_id']; ?>">

                                                <input type="hidden" name="action"
                                                    value="deactivate">

                                                <button type="submit">
                                                    Deactivate
                                                </button>

                                            </form>

                                        <?php elseif ($listing['listing_status'] === "paused"): ?>

                                            <form method="POST" action="manage_listings.php">

                                                <input type="hidden" name="csrf_token"
                                                    value="<?php echo e($csrfToken); ?>">

                                                <input type="hidden" name="listing_id"
                                                    value="<?php echo (int) $listing['listing_id']; ?>">

                                                <input type="hidden" name="action"
                                                    value="activate">

                                                <button type="submit">
                                                    Activate
                                                </button>

                                            </form>

                                        <?php else: ?>

                                            <span>Sold Out</span>

                                        <?php endif; ?>

                                        <form method="POST"
                                            action="manage_listings.php"
                                            onsubmit="return confirm('Are you sure you want to remove this listing?');">

                                            <input type="hidden" name="csrf_token"
                                                value="<?php echo e($csrfToken); ?>">

                                            <input type="hidden" name="listing_id"
                                                value="<?php echo (int) $listing['listing_id']; ?>">

                                            <input type="hidden" name="action"
                                                value="remove">

                                            <button type="submit">
                                                Remove
                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="7">
                                    No crop listings found.
                                    <a href="add_crop.php">Add your first crop listing</a>.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
    </footer>

    <!-- Profile Popup -->
    <script>

        const profileToggle = document.getElementById("profileToggle");
        const profilePopup = document.getElementById("profilePopup");

        profileToggle.addEventListener("click", function() {

            profilePopup.classList.toggle("show");

            profileToggle.setAttribute(
                "aria-expanded",
                profilePopup.classList.contains("show")
            );

        });

        document.addEventListener("click", function(event) {

            if (!event.target.closest(".profile-menu")) {

                profilePopup.classList.remove("show");

                profileToggle.setAttribute("aria-expanded", "false");

            }

        });

    </script>

</body>

</html>
