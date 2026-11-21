<?php
require_once __DIR__ . "/../config/auth_check.php";
    require_login("farmer", "../auth/login.php");

    require_once __DIR__ . "/../config/db.php";

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    require_once __DIR__ . "/../includes/html.php";

    $farmerId = (int) $_SESSION['user_id'];

    $listingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (! $listingId) {
    header("Location: manage_listings.php");
    exit;
    }

    $errors  = [];
    $success = "";

    /* CSRF token */
    if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $csrfToken = $_SESSION['csrf_token'];

    /* Fetch farmer details */
    $stmt = $link->prepare("
    SELECT full_name, email, mobile
    FROM users
    WHERE user_id = ? AND role = 'farmer'
");

    $stmt->bind_param("i", $farmerId);
    $stmt->execute();

    $farmer = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    /* Fetch listing owned by this farmer */
    $stmt = $link->prepare("
    SELECT *
    FROM crop_listings
    WHERE listing_id = ? AND farmer_id = ?
      AND listing_status != 'removed'
");

    $stmt->bind_param("ii", $listingId, $farmerId);
    $stmt->execute();

    $crop = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (! $crop) {
    header("Location: manage_listings.php");
    exit;
    }

    /* Fetch categories */
    $stmt = $link->prepare("
    SELECT category_id, category_name
    FROM crop_categories
    WHERE is_active = 1 OR category_id = ?
    ORDER BY category_name
");

    $stmt->bind_param("i", $crop['category_id']);
    $stmt->execute();

    $categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    /* Fetch existing images */
    function getListingImages($link, $listingId)
    {
    $stmt = $link->prepare("
        SELECT image_id, image_path, alt_text, is_primary
        FROM listing_images
        WHERE listing_id = ?
        ORDER BY is_primary DESC, sort_order ASC, image_id ASC
    ");

    $stmt->bind_param("i", $listingId);
    $stmt->execute();

    $images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $images;
    }

    $images = getListingImages($link, $listingId);

    /* Form values */
    $form = [
    'crop_name'              => $crop['crop_name'],
    'category_id'            => $crop['category_id'],
    'variety'                => $crop['variety'],
    'description'            => $crop['description'],
    'quantity'               => $crop['quantity_available'],
    'unit'                   => $crop['quantity_unit'],
    'price'                  => $crop['price_per_unit'],
    'minimum_order_quantity' => $crop['minimum_order_quantity'],
    'quality'                => $crop['quality_grade'],
    'harvest_date'           => $crop['harvest_date'],
    'available_from'         => $crop['available_from'],
    'available_until'        => $crop['available_until'],
    ];

    /* Image upload settings */
    $uploadDirectory = __DIR__ . "/../uploads/crop_images/";
    $maxFileSize     = 5 * 1024 * 1024;
    $maxImages       = 5;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* Verify CSRF */
    if (
        ! isset($_POST['csrf_token']) ||
        ! hash_equals($csrfToken, $_POST['csrf_token'])
    ) {
        $errors[] = "Invalid form request. Please refresh and try again.";
    }

    /* Collect form values */
    $form['crop_name']              = trim($_POST['crop_name'] ?? '');
    $form['category_id']            = (int) ($_POST['category_id'] ?? 0);
    $form['variety']                = trim($_POST['variety'] ?? '');
    $form['description']            = trim($_POST['description'] ?? '');
    $form['quantity']               = trim($_POST['quantity'] ?? '');
    $form['unit']                   = trim($_POST['unit'] ?? '');
    $form['price']                  = trim($_POST['price'] ?? '');
    $form['minimum_order_quantity'] = trim($_POST['minimum_order_quantity'] ?? '');
    $form['quality']                = trim($_POST['quality'] ?? '');
    $form['harvest_date']           = $_POST['harvest_date'] ?? '';
    $form['available_from']         = $_POST['available_from'] ?? '';
    $form['available_until']        = $_POST['available_until'] ?? '';

    /* Validate required fields */
    if ($form['crop_name'] === '' || strlen($form['crop_name']) > 120) {
        $errors[] = "Enter a valid crop name (maximum 120 characters).";
    }

    if ($form['category_id'] <= 0) {
        $errors[] = "Please select a crop category.";
    }

    if (! is_numeric($form['quantity']) || (float) $form['quantity'] <= 0) {
        $errors[] = "Enter a valid available quantity.";
    }

    if ($form['unit'] === '' || strlen($form['unit']) > 20) {
        $errors[] = "Please select a valid quantity unit.";
    }

    if (! is_numeric($form['price']) || (float) $form['price'] <= 0) {
        $errors[] = "Enter a valid price.";
    }

    if (
        $form['minimum_order_quantity'] !== '' &&
        (
            ! is_numeric($form['minimum_order_quantity']) ||
            (float) $form['minimum_order_quantity'] <= 0
        )
    ) {
        $errors[] = "Enter a valid minimum order quantity.";
    }

    if (
        $form['variety'] !== '' &&
        strlen($form['variety']) > 120
    ) {
        $errors[] = "Variety must not exceed 120 characters.";
    }

    if (
        $form['quality'] !== '' &&
        strlen($form['quality']) > 50
    ) {
        $errors[] = "Quality grade must not exceed 50 characters.";
    }

    /* Validate category */
    $categoryStmt = $link->prepare("
        SELECT category_id
        FROM crop_categories
        WHERE category_id = ?
          AND (is_active = 1 OR category_id = ?)
    ");

    $categoryStmt->bind_param(
        "ii",
        $form['category_id'],
        $crop['category_id']
    );

    $categoryStmt->execute();
    $validCategory = $categoryStmt->get_result()->fetch_assoc();
    $categoryStmt->close();

    if (! $validCategory) {
        $errors[] = "Selected crop category is not valid.";
    }

    /* Validate dates */
    $dateFields = [
        'harvest_date',
        'available_from',
        'available_until',
    ];

    foreach ($dateFields as $field) {
        if (
            $form[$field] !== '' &&
            ! DateTime::createFromFormat('!Y-m-d', $form[$field])
        ) {
            $errors[] = "Please enter valid dates.";
            break;
        }
    }

    if (
        $form['available_from'] !== '' &&
        $form['available_until'] !== '' &&
        $form['available_from'] > $form['available_until']
    ) {
        $errors[] = "Available Until must be after Available From.";
    }

    /* Determine images selected for removal */
    $removeImageIds = array_map(
        'intval',
        $_POST['remove_images'] ?? []
    );

    $existingImageIds = array_column($images, 'image_id');

    $removeImageIds = array_values(
        array_intersect($removeImageIds, $existingImageIds)
    );

    $remainingImageCount = count($images) - count($removeImageIds);

    /* Validate newly uploaded images */
    $newImageCount = 0;

    if (
        isset($_FILES['crop_images']) &&
        is_array($_FILES['crop_images']['name'])
    ) {
        foreach ($_FILES['crop_images']['error'] as $error) {
            if ($error !== UPLOAD_ERR_NO_FILE) {
                $newImageCount++;
            }
        }
    }

    if ($remainingImageCount + $newImageCount > $maxImages) {
        $errors[] = "You can keep or upload a maximum of 5 crop images.";
    }

    $uploadedFiles = [];
    $deletedFiles  = [];

    if (! $errors) {
        try {

            $link->begin_transaction();

            /* Lock and verify the listing again */
            $lockStmt = $link->prepare("
                SELECT listing_id
                FROM crop_listings
                WHERE listing_id = ?
                  AND farmer_id = ?
                  AND listing_status != 'removed'
                FOR UPDATE
            ");

            $lockStmt->bind_param("ii", $listingId, $farmerId);
            $lockStmt->execute();

            $lockedListing = $lockStmt->get_result()->fetch_assoc();
            $lockStmt->close();

            if (! $lockedListing) {
                throw new Exception("This crop listing is no longer available.");
            }

            /* Update crop listing */
            $quantity = (float) $form['quantity'];
            $price    = (float) $form['price'];

            $minimumOrder = $form['minimum_order_quantity'] === ''
                ? null
                : (float) $form['minimum_order_quantity'];

            $variety     = $form['variety'] === '' ? null : $form['variety'];
            $description = $form['description'] === '' ? null : $form['description'];
            $quality     = $form['quality'] === '' ? null : $form['quality'];

            $harvestDate = $form['harvest_date'] === ''
                ? null : $form['harvest_date'];

            $availableFrom = $form['available_from'] === ''
                ? null : $form['available_from'];

            $availableUntil = $form['available_until'] === ''
                ? null : $form['available_until'];

            $updateStmt = $link->prepare("
                UPDATE crop_listings
                SET crop_name = ?,
                    category_id = ?,
                    variety = ?,
                    description = ?,
                    quantity_available = ?,
                    quantity_unit = ?,
                    price_per_unit = ?,
                    minimum_order_quantity = ?,
                    quality_grade = ?,
                    harvest_date = ?,
                    available_from = ?,
                    available_until = ?
                WHERE listing_id = ? AND farmer_id = ?
            ");

            $updateStmt->bind_param(
                "sissdsddssssii",
                $form['crop_name'],
                $form['category_id'],
                $variety,
                $description,
                $quantity,
                $form['unit'],
                $price,
                $minimumOrder,
                $quality,
                $harvestDate,
                $availableFrom,
                $availableUntil,
                $listingId,
                $farmerId
            );

            $updateStmt->execute();
            $updateStmt->close();

            /* Remove selected existing images */
            foreach ($removeImageIds as $imageId) {

                $imageStmt = $link->prepare("
                    SELECT image_path
                    FROM listing_images
                    WHERE image_id = ? AND listing_id = ?
                ");

                $imageStmt->bind_param("ii", $imageId, $listingId);
                $imageStmt->execute();

                $image = $imageStmt->get_result()->fetch_assoc();
                $imageStmt->close();

                if ($image) {
                    $deletedFiles[] = $image['image_path'];

                    $deleteStmt = $link->prepare("
                        DELETE FROM listing_images
                        WHERE image_id = ? AND listing_id = ?
                    ");

                    $deleteStmt->bind_param("ii", $imageId, $listingId);
                    $deleteStmt->execute();
                    $deleteStmt->close();
                }
            }

            /* Upload new images */
            if (! is_dir($uploadDirectory)) {
                if (! mkdir($uploadDirectory, 0755, true)) {
                    throw new Exception("Unable to create image upload folder.");
                }
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);

            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
            ];

            if (isset($_FILES['crop_images'])) {

                foreach ($_FILES['crop_images']['tmp_name'] as $index => $tmpName) {

                    $error = $_FILES['crop_images']['error'][$index];

                    if ($error === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }

                    if ($error !== UPLOAD_ERR_OK) {
                        throw new Exception("An image upload failed.");
                    }

                    $fileSize = $_FILES['crop_images']['size'][$index];

                    if ($fileSize > $maxFileSize) {
                        throw new Exception("Each image must be 5 MB or smaller.");
                    }

                    $mimeType = $finfo->file($tmpName);

                    if (! isset($allowedTypes[$mimeType])) {
                        throw new Exception("Only JPG, PNG and WEBP images are allowed.");
                    }

                    if (getimagesize($tmpName) === false) {
                        throw new Exception("An uploaded file is not a valid image.");
                    }

                    $extension = $allowedTypes[$mimeType];
                    $fileName  = bin2hex(random_bytes(16)) . "." . $extension;

                    $destination = $uploadDirectory . $fileName;

                    if (! move_uploaded_file($tmpName, $destination)) {
                        throw new Exception("Unable to save an uploaded image.");
                    }

                    $uploadedFiles[] = $destination;

                    $relativePath = "uploads/crop_images/" . $fileName;

                    $insertImage = $link->prepare("
                        INSERT INTO listing_images
                        (listing_id, image_path, alt_text, is_primary, sort_order)
                        VALUES (?, ?, ?, 0, ?)
                    ");

                    $altText   = $form['crop_name'];
                    $sortOrder = $remainingImageCount + count($uploadedFiles);

                    $insertImage->bind_param(
                        "issi",
                        $listingId,
                        $relativePath,
                        $altText,
                        $sortOrder
                    );

                    $insertImage->execute();
                    $insertImage->close();
                }
            }

            /* Ensure the first remaining image is primary */
            $link->query("
                UPDATE listing_images
                SET is_primary = 0
                WHERE listing_id = " . (int) $listingId
            );

            $primaryStmt = $link->prepare("
                UPDATE listing_images
                SET is_primary = 1
                WHERE listing_id = ?
                ORDER BY sort_order ASC, image_id ASC
                LIMIT 1
            ");

            $primaryStmt->bind_param("i", $listingId);
            $primaryStmt->execute();
            $primaryStmt->close();

            $link->commit();

            /* Delete removed image files after successful commit */
            foreach ($deletedFiles as $oldPath) {

                $safePath      = realpath(__DIR__ . "/../" . $oldPath);
                $safeDirectory = realpath($uploadDirectory);

                if (
                    $safePath &&
                    $safeDirectory &&
                    strpos($safePath, $safeDirectory . DIRECTORY_SEPARATOR) === 0 &&
                    is_file($safePath)
                ) {
                    unlink($safePath);
                }
            }

            header("Location: manage_listings.php?updated=1");
            exit;

        } catch (Throwable $exception) {

            $link->rollback();

            /* Remove newly uploaded files if the database operation failed */
            foreach ($uploadedFiles as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            $errors[] = $exception->getMessage();
        }
    }
    }

    /* Refresh images for display */
    $images = getListingImages($link, $listingId);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Crop | AgroMarket Connect</title>

    <link rel="stylesheet" href="farmer.css">
</head>

<body>

    <?php
    $activePage = "manage_listings.php";
    $headerFarmer = $farmer;
    require __DIR__ . "/includes/header.php";
    ?>


    <main class="add-crop-container">

        <h2>Edit Crop Listing</h2>

        <p>Update your crop information and manage its images.</p>

        <?php if ($errors): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>


        <form method="POST" enctype="multipart/form-data">

            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo e($csrfToken) ?>"
            >


            <!-- Basic Crop Information -->
            <section>

                <h3>Basic Crop Information</h3>

                <div class="form-group">
                    <label for="crop_name">Crop Name</label>

                    <input
                        type="text"
                        id="crop_name"
                        name="crop_name"
                        maxlength="120"
                        value="<?php echo e($form['crop_name']) ?>"
                        required
                    >
                </div>


                <div class="form-group">
                    <label for="category_id">Crop Category</label>

                    <select id="category_id" name="category_id" required>

                        <option value="">Select Category</option>

                        <?php foreach ($categories as $category): ?>

                            <option
                                value="<?php echo (int) $category['category_id'] ?>"
                                <?php echo (int) $form['category_id'] === (int) $category['category_id'] ? 'selected' : '' ?>
                            >
                                <?php echo e($category['category_name']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </div>


                <div class="form-group">
                    <label for="variety">Variety</label>

                    <input
                        type="text"
                        id="variety"
                        name="variety"
                        maxlength="120"
                        value="<?php echo e($form['variety']) ?>"
                    >
                </div>


                <div class="form-group">
                    <label for="description">Description</label>

                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                    ><?php echo e($form['description']) ?></textarea>
                </div>

            </section>


            <!-- Quantity and Pricing -->
            <section>

                <h3>Quantity and Pricing</h3>

                <div class="form-group">
                    <label for="quantity">Available Quantity</label>

                    <input
                        type="number"
                        id="quantity"
                        name="quantity"
                        step="0.001"
                        min="0.001"
                        value="<?php echo e($form['quantity']) ?>"
                        required
                    >
                </div>


                <div class="form-group">
                    <label for="unit">Quantity Unit</label>

                    <select id="unit" name="unit" required>

                        <?php
                            $units = ['kg', 'quintal', 'ton', 'litre', 'piece', 'dozen'];
                        ?>

                        <option value="">Select Unit</option>

                        <?php foreach ($units as $unit): ?>

                            <option
                                value="<?php echo e($unit) ?>"
                                <?php echo $form['unit'] === $unit ? 'selected' : '' ?>
                            >
                                <?php echo e(ucfirst($unit)) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </div>


                <div class="form-group">
                    <label for="price">Price Per Unit (₹)</label>

                    <input
                        type="number"
                        id="price"
                        name="price"
                        step="0.01"
                        min="0.01"
                        value="<?php echo e($form['price']) ?>"
                        required
                    >
                </div>


                <div class="form-group">
                    <label for="minimum_order_quantity">
                        Minimum Order Quantity
                    </label>

                    <input
                        type="number"
                        id="minimum_order_quantity"
                        name="minimum_order_quantity"
                        step="0.001"
                        min="0.001"
                        value="<?php echo e($form['minimum_order_quantity']) ?>"
                    >
                </div>

            </section>


            <!-- Harvest and Availability -->
            <section>

                <h3>Harvest and Availability</h3>

                <div class="form-group">
                    <label for="harvest_date">Harvest Date</label>

                    <input
                        type="date"
                        id="harvest_date"
                        name="harvest_date"
                        value="<?php echo e($form['harvest_date']) ?>"
                    >
                </div>


                <div class="form-group">
                    <label for="available_from">Available From</label>

                    <input
                        type="date"
                        id="available_from"
                        name="available_from"
                        value="<?php echo e($form['available_from']) ?>"
                    >
                </div>


                <div class="form-group">
                    <label for="available_until">Available Until</label>

                    <input
                        type="date"
                        id="available_until"
                        name="available_until"
                        value="<?php echo e($form['available_until']) ?>"
                    >
                </div>


                <div class="form-group">
                    <label for="quality">Quality Grade</label>

                    <input
                        type="text"
                        id="quality"
                        name="quality"
                        maxlength="50"
                        value="<?php echo e($form['quality']) ?>"
                    >
                </div>

            </section>


            <!-- Crop Location -->
            <section>

                <h3>Crop Location</h3>

                <p>
                    Crop location is taken from your farmer profile.
                    To change it, update your profile.
                </p>

                <?php
                    $locationStmt = $link->prepare("
                    SELECT village, district, state, pincode
                    FROM farmer_profiles
                    WHERE farmer_id = ?
                ");

                    $locationStmt->bind_param("i", $farmerId);
                    $locationStmt->execute();

                    $location = $locationStmt->get_result()->fetch_assoc();
                    $locationStmt->close();
                ?>

                <div class="form-group">
                    <label>Village</label>
                    <input type="text" value="<?php echo e($location['village'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label>District</label>
                    <input type="text" value="<?php echo e($location['district'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label>State</label>
                    <input type="text" value="<?php echo e($location['state'] ?? '') ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Pincode</label>
                    <input type="text" value="<?php echo e($location['pincode'] ?? '') ?>" readonly>
                </div>

            </section>


            <!-- Existing Crop Images -->
            <section>

                <h3>Existing Crop Images</h3>

                <?php if ($images): ?>

                    <div class="existing-images">

                        <?php foreach ($images as $image): ?>

                            <div class="existing-image">

                                <img
                                    src="../<?php echo e($image['image_path']) ?>"
                                    alt="<?php echo e($image['alt_text'] ?? 'Crop image') ?>"
                                    width="130"
                                >

                                <?php if ($image['is_primary']): ?>
                                    <p>Primary Image</p>
                                <?php endif; ?>

                                <label>
                                    <input
                                        type="checkbox"
                                        name="remove_images[]"
                                        value="<?php echo (int) $image['image_id'] ?>"
                                    >
                                    Remove Image
                                </label>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <p>No images uploaded for this crop.</p>

                <?php endif; ?>

            </section>


            <!-- Add New Crop Images -->
            <section>

                <h3>Add New Crop Images</h3>

                <div class="form-group">

                    <label for="crop_images">Upload Images</label>

                    <input
                        type="file"
                        id="crop_images"
                        name="crop_images[]"
                        accept="image/jpeg,image/png,image/webp"
                        multiple
                    >

                    <p>Maximum 5 images in total. JPG, PNG or WEBP. Maximum 5 MB per image.</p>

                </div>

            </section>


            <!-- Form Actions -->
            <div class="form-actions">

                <button type="submit">
                    Save Changes
                </button>

                <a href="manage_listings.php">
                    Cancel
                </a>

            </div>

        </form>

    </main>

</body>
</html>
