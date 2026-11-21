<?php
require_once __DIR__ . "/../config/auth_check.php";
    require_login("farmer", "../auth/login.php");

    require_once __DIR__ . "/../config/db.php";

    $farmerId = (int) $_SESSION['user_id'];

    require_once __DIR__ . "/../includes/html.php";

    // -----------------------------------------
    // 1. Fetch farmer account details
    // -----------------------------------------

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

    // -----------------------------------------
    // 2. Fetch farmer location
    // -----------------------------------------

    $locationSql = "SELECT village, district, state, pincode
                FROM farmer_profiles
                WHERE farmer_id = ?
                LIMIT 1";

    $stmt = mysqli_prepare($link, $locationSql);
    mysqli_stmt_bind_param($stmt, "i", $farmerId);
    mysqli_stmt_execute($stmt);

    $locationResult = mysqli_stmt_get_result($stmt);
    $farmerLocation = mysqli_fetch_assoc($locationResult) ?: [];

    mysqli_stmt_close($stmt);

    // -----------------------------------------
    // 3. Fetch active crop categories
    // -----------------------------------------

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

    // -----------------------------------------
    // 4. Form data and messages
    // -----------------------------------------

    $errors = [];

    $formData = [
    "crop_name"              => "",
    "category_id"            => "",
    "variety"                => "",
    "description"            => "",
    "quantity"               => "",
    "unit"                   => "",
    "price"                  => "",
    "minimum_order_quantity" => "",
    "harvest_date"           => "",
    "available_from"         => "",
    "available_until"        => "",
    "quality"                => "",
    ];

    $allowedUnits     = ["kg", "quintal", "ton", "piece"];
    $allowedQualities = ["grade_a", "grade_b", "grade_c"];

    if (isset($_GET['success'])) {
    $successMessage = "Your crop listing has been submitted successfully.";
    } else {
    $successMessage = "";
    }

    // -----------------------------------------
    // 5. Process crop registration
    // -----------------------------------------

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    foreach ($formData as $field => $value) {
        $formData[$field] = trim($_POST[$field] ?? "");
    }

    // Basic validation

    if ($formData['crop_name'] === "") {
        $errors[] = "Please enter the crop name.";
    }

    if (! ctype_digit($formData['category_id']) ||
        (int) $formData['category_id'] <= 0) {
        $errors[] = "Please select a valid crop category.";
    }

    if (! is_numeric($formData['quantity']) ||
        (float) $formData['quantity'] <= 0) {
        $errors[] = "Available quantity must be greater than zero.";
    }

    if (! in_array($formData['unit'], $allowedUnits, true)) {
        $errors[] = "Please select a valid quantity unit.";
    }

    if (! is_numeric($formData['price']) ||
        (float) $formData['price'] <= 0) {
        $errors[] = "Price must be greater than zero.";
    }

    if ($formData['minimum_order_quantity'] !== "") {

        if (! is_numeric($formData['minimum_order_quantity']) ||
            (float) $formData['minimum_order_quantity'] <= 0) {

            $errors[] = "Minimum order quantity must be greater than zero.";

        } elseif (
            is_numeric($formData['quantity']) &&
            (float) $formData['minimum_order_quantity'] > (float) $formData['quantity']
        ) {
            $errors[] = "Minimum order quantity cannot exceed available quantity.";
        }
    }

    if ($formData['quality'] !== "" &&
        ! in_array($formData['quality'], $allowedQualities, true)) {
        $errors[] = "Please select a valid quality grade.";
    }

    // Validate dates

    $dateFields = [
        "harvest_date",
        "available_from",
        "available_until",
    ];

    foreach ($dateFields as $dateField) {

        if ($formData[$dateField] !== "") {

            $date = DateTime::createFromFormat("!Y-m-d", $formData[$dateField]);

            if (! $date || $date->format("Y-m-d") !== $formData[$dateField]) {
                $errors[] = "Invalid date entered for " .
                str_replace("_", " ", $dateField) . ".";
            }
        }
    }

    if (
        $formData['available_from'] !== "" &&
        $formData['available_until'] !== "" &&
        $formData['available_until'] < $formData['available_from']
    ) {
        $errors[] = "Available until date cannot be before available from date.";
    }

    // Verify category exists and is active

    if (ctype_digit($formData['category_id']) &&
        (int) $formData['category_id'] > 0) {

        $checkCategorySql = "SELECT category_id
                             FROM crop_categories
                             WHERE category_id = ?
                             AND is_active = 1
                             LIMIT 1";

        $categoryStmt = mysqli_prepare($link, $checkCategorySql);
        $categoryId   = (int) $formData['category_id'];

        mysqli_stmt_bind_param($categoryStmt, "i", $categoryId);
        mysqli_stmt_execute($categoryStmt);

        $categoryCheck = mysqli_stmt_get_result($categoryStmt);

        if (mysqli_num_rows($categoryCheck) === 0) {
            $errors[] = "Selected category is unavailable.";
        }

        mysqli_stmt_close($categoryStmt);
    }

    // -----------------------------------------
    // 6. Validate uploaded images
    // -----------------------------------------

    $uploadedImages = [];

    if (isset($_FILES['crop_images'])) {

        $imageFiles = $_FILES['crop_images'];

        $allowedMimeTypes = [
            "image/jpeg" => "jpg",
            "image/png"  => "png",
            "image/webp" => "webp",
        ];

        $fileCount = count($imageFiles['name']);

        $selectedFiles = [];

        for ($i = 0; $i < $fileCount; $i++) {

            if ($imageFiles['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $selectedFiles[] = $i;
        }

        if (count($selectedFiles) > 5) {
            $errors[] = "You can upload a maximum of 5 images.";
        }

        foreach ($selectedFiles as $index) {

            if ($imageFiles['error'][$index] !== UPLOAD_ERR_OK) {
                $errors[] = "An image upload failed. Please try again.";
                continue;
            }

            if ($imageFiles['size'][$index] > 5 * 1024 * 1024) {
                $errors[] = "Each image must be smaller than 5 MB.";
                continue;
            }

            $tmpPath = $imageFiles['tmp_name'][$index];

            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $tmpPath);
            finfo_close($fileInfo);

            if (! isset($allowedMimeTypes[$mimeType])) {
                $errors[] = "Only JPG, PNG and WEBP images are allowed.";
                continue;
            }

            if (getimagesize($tmpPath) === false) {
                $errors[] = "One of the uploaded files is not a valid image.";
                continue;
            }

            $uploadedImages[] = [
                "tmp_name"  => $tmpPath,
                "extension" => $allowedMimeTypes[$mimeType],
            ];
        }
    }

    // -----------------------------------------
    // 7. Save listing and images in database
    // -----------------------------------------

    if (empty($errors)) {

        $uploadDirectory = __DIR__ . "/../uploads/crop_images/";

        if (! is_dir($uploadDirectory)) {
            if (! mkdir($uploadDirectory, 0755, true)) {
                $errors[] = "Unable to create image upload directory.";
            }
        }

        if (empty($errors) && ! is_writable($uploadDirectory)) {
            $errors[] = "Image upload directory is not writable.";
        }

        if (empty($errors)) {

            $movedFiles = [];

            mysqli_begin_transaction($link);

            try {

                $categoryId  = (int) $formData['category_id'];
                $cropName    = $formData['crop_name'];
                $variety     = $formData['variety'];
                $description = $formData['description'];
                $quantity    = (float) $formData['quantity'];
                $unit        = $formData['unit'];
                $price       = (float) $formData['price'];

                $minimumOrder = $formData['minimum_order_quantity'] === ""
                    ? null
                    : (float) $formData['minimum_order_quantity'];

                $quality = $formData['quality'] === ""
                    ? null
                    : $formData['quality'];

                $harvestDate = $formData['harvest_date'] === ""
                    ? null
                    : $formData['harvest_date'];

                $availableFrom = $formData['available_from'] === ""
                    ? null
                    : $formData['available_from'];

                $availableUntil = $formData['available_until'] === ""
                    ? null
                    : $formData['available_until'];

                $insertSql = "INSERT INTO crop_listings (
                                farmer_id,
                                category_id,
                                crop_name,
                                variety,
                                description,
                                quantity_available,
                                quantity_unit,
                                price_per_unit,
                                minimum_order_quantity,
                                quality_grade,
                                harvest_date,
                                available_from,
                                available_until
                              ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $insertStmt = mysqli_prepare($link, $insertSql);

                if (! $insertStmt) {
                    throw new Exception("Could not prepare crop listing query.");
                }

                mysqli_stmt_bind_param(
                    $insertStmt,
                    "iisssdsddssss",
                    $farmerId,
                    $categoryId,
                    $cropName,
                    $variety,
                    $description,
                    $quantity,
                    $unit,
                    $price,
                    $minimumOrder,
                    $quality,
                    $harvestDate,
                    $availableFrom,
                    $availableUntil
                );

                if (! mysqli_stmt_execute($insertStmt)) {
                    throw new Exception("Could not save crop listing.");
                }

                $listingId = mysqli_insert_id($link);

                mysqli_stmt_close($insertStmt);

                // Save uploaded images

                if (! empty($uploadedImages)) {

                    $imageSql = "INSERT INTO listing_images
                                 (listing_id, image_path, alt_text, is_primary, sort_order)
                                 VALUES (?, ?, ?, ?, ?)";

                    $imageStmt = mysqli_prepare($link, $imageSql);

                    if (! $imageStmt) {
                        throw new Exception("Could not prepare image query.");
                    }

                    foreach ($uploadedImages as $index => $image) {

                        $fileName = bin2hex(random_bytes(16)) .
                            "." . $image['extension'];

                        $destination = $uploadDirectory . $fileName;

                        if (! move_uploaded_file($image['tmp_name'], $destination)) {
                            throw new Exception("Unable to save an uploaded image.");
                        }

                        $movedFiles[] = $destination;

                        // Relative path stored in database
                        $imagePath = "uploads/crop_images/" . $fileName;

                        $altText   = $cropName . " image";
                        $isPrimary = ($index === 0) ? 1 : 0;
                        $sortOrder = $index + 1;

                        mysqli_stmt_bind_param(
                            $imageStmt,
                            "issii",
                            $listingId,
                            $imagePath,
                            $altText,
                            $isPrimary,
                            $sortOrder
                        );

                        if (! mysqli_stmt_execute($imageStmt)) {
                            throw new Exception("Unable to save image information.");
                        }
                    }

                    mysqli_stmt_close($imageStmt);
                }

                mysqli_commit($link);

                // Prevent duplicate submission on page refresh
                header("Location: add_crop.php?success=1");
                exit;

            } catch (Throwable $exception) {

                mysqli_rollback($link);

                // Remove uploaded files if database transaction failed
                foreach ($movedFiles as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }

                error_log("Crop listing error: " . $exception->getMessage());

                $errors[] = "Unable to submit your crop listing. Please try again.";
            }
        }
    }
    }

    // CSRF protection
    if (empty($_SESSION['crop_form_token'])) {
    $_SESSION['crop_form_token'] = bin2hex(random_bytes(32));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        ! isset($_POST['csrf_token']) ||
        ! hash_equals($_SESSION['crop_form_token'], $_POST['csrf_token'])
    ) {
        $errors[] = "Invalid form submission. Please refresh and try again.";
    }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>List Your Crops | AgroMarket Connect</title>

    <link rel="stylesheet" href="./farmer.css">
</head>

<body>

    <!-- Header -->
    <header class="farmer-header">

        <div class="header-left">
            <h2>AgroMarket Connect</h2>
        </div>

        <?php
    $activePage = "";
    require __DIR__ . "/includes/navigation.php";
    ?>

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

    <!-- Main Form -->
    <main class="add-crop-container">

        <h1>List Your Crops</h1>

        <p>Provide your crop details to make them available to buyers.</p>

        <!-- Success Message -->
        <?php if ($successMessage !== ""): ?>

            <div class="success-message">
                <?php echo e($successMessage); ?>
            </div>

        <?php endif; ?>

        <!-- Error Messages -->
        <?php if (! empty($errors)): ?>

            <div class="error-message">

                <?php foreach ($errors as $error): ?>

                    <p><?php echo e($error); ?></p>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

        <form action="add_crop.php" method="POST" enctype="multipart/form-data">

            <input type="hidden" name="csrf_token"
                value="<?php echo e($_SESSION['crop_form_token']); ?>">

            <!-- Basic Crop Information -->
            <section>

                <h2>Basic Crop Information</h2>

                <label for="crop_name">Crop Name</label>

                <input type="text" id="crop_name" name="crop_name"
                    placeholder="Enter crop name"
                    value="<?php echo e($formData['crop_name']); ?>"
                    maxlength="120" required>

                <label for="category_id">Crop Category</label>

                <select id="category_id" name="category_id" required>

                    <option value="">Select Category</option>

                    <?php foreach ($categories as $category): ?>

                        <option
                            value="<?php echo (int) $category['category_id']; ?>"
                            <?php echo((string) $formData['category_id'] ===
                                (string) $category['category_id']) ? "selected" : ""; ?>>

                            <?php echo e($category['category_name']); ?>

                        </option>

                    <?php endforeach; ?>

                </select>

                <label for="variety">Crop Variety</label>

                <input type="text" id="variety" name="variety"
                    placeholder="Enter crop variety"
                    value="<?php echo e($formData['variety']); ?>"
                    maxlength="120">

                <label for="description">Crop Description</label>

                <textarea id="description" name="description"
                    placeholder="Describe your crop"><?php echo e($formData['description']); ?></textarea>

            </section>

            <!-- Quantity and Pricing -->
            <section>

                <h2>Quantity and Pricing</h2>

                <label for="quantity">Available Quantity</label>

                <input type="number" id="quantity" name="quantity"
                    placeholder="Enter quantity"
                    min="0.001" step="0.001"
                    value="<?php echo e($formData['quantity']); ?>"
                    required>

                <label for="unit">Quantity Unit</label>

                <select id="unit" name="unit" required>

                    <option value="">Select Unit</option>

                    <option value="kg" <?php echo $formData['unit'] === "kg" ? "selected" : ""; ?>>Kilograms (KG)</option>

                    <option value="quintal" <?php echo $formData['unit'] === "quintal" ? "selected" : ""; ?>>Quintals</option>

                    <option value="ton" <?php echo $formData['unit'] === "ton" ? "selected" : ""; ?>>Metric Tons</option>

                    <option value="piece" <?php echo $formData['unit'] === "piece" ? "selected" : ""; ?>>Pieces</option>

                </select>

                <label for="price">Expected Price (₹ per selected unit)</label>

                <input type="number" id="price" name="price"
                    placeholder="Enter price"
                    min="0.01" step="0.01"
                    value="<?php echo e($formData['price']); ?>"
                    required>

                <label for="minimum_order_quantity">Minimum Order Quantity (Optional)</label>

                <input type="number" id="minimum_order_quantity"
                    name="minimum_order_quantity"
                    placeholder="Enter minimum order quantity"
                    min="0.001" step="0.001"
                    value="<?php echo e($formData['minimum_order_quantity']); ?>">

            </section>

            <!-- Harvest and Availability -->
            <section>

                <h2>Harvest and Availability</h2>

                <label for="harvest_date">Harvest Date</label>

                <input type="date" id="harvest_date" name="harvest_date"
                    value="<?php echo e($formData['harvest_date']); ?>">

                <label for="available_from">Available From</label>

                <input type="date" id="available_from" name="available_from"
                    value="<?php echo e($formData['available_from']); ?>">

                <label for="available_until">Available Until</label>

                <input type="date" id="available_until" name="available_until"
                    value="<?php echo e($formData['available_until']); ?>">

                <label for="quality">Quality Grade</label>

                <select id="quality" name="quality">

                    <option value="">Select Quality</option>

                    <option value="grade_a" <?php echo $formData['quality'] === "grade_a" ? "selected" : ""; ?>>Grade A</option>

                    <option value="grade_b" <?php echo $formData['quality'] === "grade_b" ? "selected" : ""; ?>>Grade B</option>

                    <option value="grade_c" <?php echo $formData['quality'] === "grade_c" ? "selected" : ""; ?>>Grade C</option>

                </select>

            </section>

            <!-- Farmer Location -->
            <section>

                <h2>Crop Location</h2>

                <p>
                    Your saved farmer profile location will be used for this listing.
                    To change it, update your profile.
                </p>

                <label>Village / Town</label>

                <input type="text" value="<?php echo e($farmerLocation['village'] ?? ''); ?>" readonly>

                <label>District</label>

                <input type="text" value="<?php echo e($farmerLocation['district'] ?? ''); ?>" readonly>

                <label>State</label>

                <input type="text" value="<?php echo e($farmerLocation['state'] ?? ''); ?>" readonly>

                <label>PIN Code</label>

                <input type="text" value="<?php echo e($farmerLocation['pincode'] ?? ''); ?>" readonly>

            </section>

            <!-- Crop Images -->
            <section>

                <h2>Crop Images</h2>

                <label for="crop_images">Upload Crop Images</label>

                <input type="file" id="crop_images"
                    name="crop_images[]"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    multiple>

                <p>Maximum 5 images. Each image must be smaller than 5 MB. JPG, PNG and WEBP only.</p>

            </section>

            <!-- Form Actions -->
            <div class="form-actions">

                <button type="submit">Submit Crop Listing</button>

                <button type="reset">Clear Form</button>

            </div>

        </form>

    </main>

    <footer>
        <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
    </footer>

    <!-- Profile Popup -->


<script src="../js/profile-menu.js" defer></script>
</body>

</html>
