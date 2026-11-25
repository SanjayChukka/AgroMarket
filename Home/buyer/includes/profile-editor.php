
<?php

require_once __DIR__ . "/../../includes/html.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once __DIR__ . "/../../config/db.php";


// Initialize Buyer Session and CSRF Token
$buyerId = (int) $_SESSION['user_id'];

$_SESSION['buyer_profile_token'] ??= bin2hex(random_bytes(32));

$success = $_SESSION['buyer_profile_saved'] ?? false;

unset($_SESSION['buyer_profile_saved']);

$errors = [];

$businessTypes = [
    'individual',
    'retailer',
    'wholesaler',
    'distributor',
    'processor'
];


// Fetch Buyer Profile Details
$stmt = $link->prepare(
    "SELECT
        u.full_name,
        u.email,
        u.mobile,
        bp.business_name,
        bp.business_type,
        bp.business_address,
        bp.delivery_address,
        bp.village_city,
        bp.district,
        bp.state,
        bp.pincode,
        bp.dob,
        bp.gst_number,
        bp.profile_image
    FROM users u
    LEFT JOIN buyer_profiles bp
        ON bp.buyer_id = u.user_id
    WHERE u.user_id = ?
        AND u.role = 'buyer'"
);

$stmt->bind_param("i", $buyerId);

$stmt->execute();

$buyer = $stmt->get_result()->fetch_assoc();

$stmt->close();


// Check Buyer Account Availability
if (!$buyer) {

    http_response_code(404);

    exit("Buyer account unavailable.");
}

$form = $buyer;


// Process Only POST Requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;
}


// Collect Form Fields
$fields = [
    'full_name',
    'email',
    'mobile',
    'dob',
    'business_name',
    'business_type',
    'business_address',
    'delivery_address',
    'village_city',
    'district',
    'state',
    'pincode',
    'gst_number'
];

foreach ($fields as $field) {

    $form[$field] = is_string($_POST[$field] ?? null)
        ? trim($_POST[$field])
        : '';
}


// Validate CSRF Token
if (
    !is_string($_POST['csrf_token'] ?? null) ||
    !hash_equals(
        $_SESSION['buyer_profile_token'],
        $_POST['csrf_token']
    )
) {
    $errors[] = "Please reload the page and submit again.";
}


// Validate Personal Information
if (
    $form['full_name'] === '' ||
    !filter_var($form['email'], FILTER_VALIDATE_EMAIL) ||
    !preg_match('/^[6-9][0-9]{9}$/D', $form['mobile'])
) {
    $errors[] = "Enter your name, a valid email address and a 10-digit Indian mobile number.";
}


// Validate Business Information
if (
    !in_array($form['business_type'], $businessTypes, true) ||
    $form['business_address'] === '' ||
    $form['district'] === '' ||
    $form['state'] === ''
) {
    $errors[] = "Business type, business address, district and state are required.";
}


// Validate PIN Code
if (
    $form['pincode'] !== '' &&
    !preg_match('/^[0-9]{6}$/D', $form['pincode'])
) {
    $errors[] = "Enter a six-digit PIN code.";
}


// Validate Date of Birth
if ($form['dob'] !== '') {

    $date = DateTimeImmutable::createFromFormat(
        '!Y-m-d',
        $form['dob']
    );

    if (
        !$date ||
        $date->format('Y-m-d') !== $form['dob'] ||
        $form['dob'] > date('Y-m-d')
    ) {
        $errors[] = "Enter a valid date of birth in the past or today.";
    }
}


// Validate GST Number
$form['gst_number'] = strtoupper($form['gst_number']);

if (
    $form['gst_number'] !== '' &&
    !preg_match('/^[0-9A-Z]{15}$/D', $form['gst_number'])
) {
    $errors[] = "Enter a 15-character GST number or leave it blank.";
}


// Stop Processing If Validation Fails
if ($errors) {
    return;
}


// Initialize Profile Photo Upload Variables
$photo = $_FILES['profile_picture'] ?? [
    'error' => UPLOAD_ERR_NO_FILE
];

$newPath = null;
$oldPath = null;

$homeDir = dirname(__DIR__, 2) . '/';

$inTransaction = false;


try {

    // Handle Profile Photo Upload
    if ($photo['error'] !== UPLOAD_ERR_NO_FILE) {

        // Validate Upload Status and File Size
        if (
            $photo['error'] !== UPLOAD_ERR_OK ||
            $photo['size'] > 2 * 1024 * 1024
        ) {
            throw new DomainException(
                "Choose a JPG or PNG photo under 2 MB."
            );
        }


        // Validate Image Type
        $info = getimagesize($photo['tmp_name']);

        $extensions = [
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG  => 'png'
        ];

        if (!$info || !isset($extensions[$info[2]])) {

            throw new DomainException(
                "Choose a valid JPG or PNG photo."
            );
        }


        // Create Profile Image Directory If Required
        $dir = 'uploads/profile_images/';

        if (
            !is_dir($homeDir . $dir) &&
            !mkdir($homeDir . $dir, 0755, true) &&
            !is_dir($homeDir . $dir)
        ) {
            throw new RuntimeException(
                "Profile photo directory unavailable."
            );
        }


        // Generate a Unique Profile Image Path
        $newPath = $dir
            . 'buyer_'
            . $buyerId
            . '_'
            . bin2hex(random_bytes(16))
            . '.'
            . $extensions[$info[2]];


        // Move Uploaded Image to the Destination Directory
        if (
            !move_uploaded_file(
                $photo['tmp_name'],
                $homeDir . $newPath
            )
        ) {
            throw new RuntimeException(
                "Profile photo upload failed."
            );
        }
    }


    // Begin Database Transaction
    $link->begin_transaction();

    $inTransaction = true;


    // Update Personal Information in Users Table
    $stmt = $link->prepare(
        "UPDATE users
         SET
            full_name = ?,
            email = ?,
            mobile = ?
         WHERE user_id = ?
            AND role = 'buyer'"
    );

    $stmt->bind_param(
        "sssi",
        $form['full_name'],
        $form['email'],
        $form['mobile'],
        $buyerId
    );

    $stmt->execute();

    $stmt->close();


    // Fetch Existing Profile Image and Lock the Profile Record
    $stmt = $link->prepare(
        "SELECT profile_image
         FROM buyer_profiles
         WHERE buyer_id = ?
         FOR UPDATE"
    );

    $stmt->bind_param("i", $buyerId);

    $stmt->execute();

    $existing = $stmt->get_result()->fetch_assoc();

    $stmt->close();


    // Prepare Profile Values
    $oldPath = $existing['profile_image'] ?? null;

    $photoPath = $newPath ?? $oldPath;

    $dob = $form['dob'] ?: null;

    $gst = $form['gst_number'] ?: null;

    $pincode = $form['pincode'] ?: null;


    // Prepare Buyer Profile Column Assignments
    $assignments = "
        business_name = ?,
        business_type = ?,
        business_address = ?,
        delivery_address = ?,
        village_city = ?,
        district = ?,
        state = ?,
        pincode = ?,
        dob = ?,
        gst_number = ?,
        profile_image = ?
    ";


    // Update Existing Profile or Insert a New Profile
    $stmt = $link->prepare(
        $existing
            ? "UPDATE buyer_profiles
               SET $assignments
               WHERE buyer_id = ?"

            : "INSERT INTO buyer_profiles
               SET $assignments, buyer_id = ?"
    );

    $stmt->bind_param(
        "sssssssssssi",
        $form['business_name'],
        $form['business_type'],
        $form['business_address'],
        $form['delivery_address'],
        $form['village_city'],
        $form['district'],
        $form['state'],
        $pincode,
        $dob,
        $gst,
        $photoPath,
        $buyerId
    );

    $stmt->execute();

    $stmt->close();


    // Commit Database Changes
    $link->commit();

    $inTransaction = false;

} catch (Throwable $error) {

    // Roll Back Database Changes If Necessary
    if ($inTransaction) {
        $link->rollback();
    }


    // Remove Newly Uploaded Image If Saving Fails
    if ($newPath && is_file($homeDir . $newPath)) {

        unlink($homeDir . $newPath);
    }


    // Handle Validation and Database Errors
    if ($error instanceof DomainException) {

        $errors[] = $error->getMessage();

    } elseif (
        $error instanceof mysqli_sql_exception &&
        $error->getCode() === 1062
    ) {

        // Handle Duplicate Account Information
        $errors[] = "An account already uses these details. Check your email, mobile and GST number.";

    } else {

        // Log Unexpected Errors
        error_log("Buyer profile: " . $error->getMessage());

        $errors[] = "Unable to save changes. Please try again.";
    }

    return;
}


// Remove Previous Profile Image After Successful Update
if (
    $newPath &&
    $oldPath &&
    preg_match(
        '~^uploads/profile_images/buyer_'
        . $buyerId
        . '_[a-f0-9]{32}\.(jpg|png)$~D',
        $oldPath
    ) &&
    is_file($homeDir . $oldPath)
) {
    unlink($homeDir . $oldPath);
}


// Update Session Information
$_SESSION['full_name'] = $form['full_name'];

$_SESSION['buyer_profile_saved'] = true;


// Redirect After Successful Profile Update
header("Location: profile.php", true, 303);

exit;
