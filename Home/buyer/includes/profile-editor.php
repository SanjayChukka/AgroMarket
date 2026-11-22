<?php
require_once __DIR__ . "/../../includes/html.php";
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once __DIR__ . "/../../config/db.php";

$buyerId = (int) $_SESSION['user_id'];
$_SESSION['buyer_profile_token'] ??= bin2hex(random_bytes(32));
$success = $_SESSION['buyer_profile_saved'] ?? false;
unset($_SESSION['buyer_profile_saved']);
$errors        = [];
$businessTypes = ['individual', 'retailer', 'wholesaler', 'distributor', 'processor'];
$stmt          = $link->prepare("SELECT u.full_name, u.email, u.mobile, bp.business_name, bp.business_type,
    bp.business_address, bp.delivery_address, bp.village_city, bp.district, bp.state, bp.pincode,
    bp.dob, bp.gst_number, bp.profile_image
    FROM users u LEFT JOIN buyer_profiles bp ON bp.buyer_id = u.user_id
    WHERE u.user_id = ? AND u.role = 'buyer'");
$stmt->bind_param("i", $buyerId);
$stmt->execute();
$buyer = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (! $buyer) {
    http_response_code(404);
    exit("Buyer account unavailable.");
}
$form = $buyer;
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;
}

$fields = ['full_name', 'email', 'mobile', 'dob', 'business_name', 'business_type', 'business_address',
    'delivery_address', 'village_city', 'district', 'state', 'pincode', 'gst_number'];
foreach ($fields as $field) {
    $form[$field] = is_string($_POST[$field] ?? null) ? trim($_POST[$field]) : '';
}
if (! is_string($_POST['csrf_token'] ?? null) || ! hash_equals($_SESSION['buyer_profile_token'], $_POST['csrf_token'])) {
    $errors[] = "Please reload the page and submit again.";
}
if ($form['full_name'] === '' || ! filter_var($form['email'], FILTER_VALIDATE_EMAIL)
    || ! preg_match('/^[6-9][0-9]{9}$/D', $form['mobile'])) {
    $errors[] = "Enter your name, a valid email address and a 10-digit Indian mobile number.";
}
if (! in_array($form['business_type'], $businessTypes, true) || $form['business_address'] === ''
    || $form['district'] === '' || $form['state'] === '') {
    $errors[] = "Business type, business address, district and state are required.";
}
if ($form['pincode'] !== '' && ! preg_match('/^[0-9]{6}$/D', $form['pincode'])) {
    $errors[] = "Enter a six-digit PIN code.";
}
if ($form['dob'] !== '') {
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $form['dob']);
    if (! $date || $date->format('Y-m-d') !== $form['dob'] || $form['dob'] > date('Y-m-d')) {
        $errors[] = "Enter a valid date of birth in the past or today.";
    }
}
$form['gst_number'] = strtoupper($form['gst_number']);
if ($form['gst_number'] !== '' && ! preg_match('/^[0-9A-Z]{15}$/D', $form['gst_number'])) {
    $errors[] = "Enter a 15-character GST number or leave it blank.";
}
if ($errors) {
    return;
}

$photo         = $_FILES['profile_picture'] ?? ['error' => UPLOAD_ERR_NO_FILE];
$newPath       = null;
$oldPath       = null;
$homeDir       = dirname(__DIR__, 2) . '/';
$inTransaction = false;
try {
    if ($photo['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($photo['error'] !== UPLOAD_ERR_OK || $photo['size'] > 2 * 1024 * 1024) {
            throw new DomainException("Choose a JPG or PNG photo under 2 MB.");
        }
        $info       = getimagesize($photo['tmp_name']);
        $extensions = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png'];
        if (! $info || ! isset($extensions[$info[2]])) {
            throw new DomainException("Choose a valid JPG or PNG photo.");
        }
        $dir = 'uploads/profile_images/';
        if (! is_dir($homeDir . $dir) && ! mkdir($homeDir . $dir, 0755, true) && ! is_dir($homeDir . $dir)) {
            throw new RuntimeException("Profile photo directory unavailable.");
        }
        $newPath = $dir . 'buyer_' . $buyerId . '_' . bin2hex(random_bytes(16)) . '.' . $extensions[$info[2]];
        if (! move_uploaded_file($photo['tmp_name'], $homeDir . $newPath)) {
            throw new RuntimeException("Profile photo upload failed.");
        }
    }

    $link->begin_transaction();
    $inTransaction = true;
    $stmt          = $link->prepare("UPDATE users SET full_name=?, email=?, mobile=? WHERE user_id=? AND role='buyer'");
    $stmt->bind_param("sssi", $form['full_name'], $form['email'], $form['mobile'], $buyerId);
    $stmt->execute();
    $stmt->close();

    $stmt = $link->prepare("SELECT profile_image FROM buyer_profiles WHERE buyer_id=? FOR UPDATE");
    $stmt->bind_param("i", $buyerId);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $oldPath     = $existing['profile_image'] ?? null;
    $photoPath   = $newPath ?? $oldPath;
    $dob         = $form['dob'] ?: null;
    $gst         = $form['gst_number'] ?: null;
    $pincode     = $form['pincode'] ?: null;
    $assignments = "business_name=?, business_type=?, business_address=?, delivery_address=?, village_city=?,
        district=?, state=?, pincode=?, dob=?, gst_number=?, profile_image=?";
    $stmt = $link->prepare($existing
            ? "UPDATE buyer_profiles SET $assignments WHERE buyer_id=?"
            : "INSERT INTO buyer_profiles SET $assignments, buyer_id=?");
    $stmt->bind_param("sssssssssssi", $form['business_name'], $form['business_type'], $form['business_address'],
        $form['delivery_address'], $form['village_city'], $form['district'], $form['state'], $pincode,
        $dob, $gst, $photoPath, $buyerId);
    $stmt->execute();
    $stmt->close();
    $link->commit();
    $inTransaction = false;
} catch (Throwable $error) {
    if ($inTransaction) {
        $link->rollback();
    }
    if ($newPath && is_file($homeDir . $newPath)) {
        unlink($homeDir . $newPath);
    }
    if ($error instanceof DomainException) {
        $errors[] = $error->getMessage();
    } elseif ($error instanceof mysqli_sql_exception && $error->getCode() === 1062) {
        $errors[] = "An account already uses these details. Check your email, mobile and GST number.";
    } else {
        error_log("Buyer profile: " . $error->getMessage());
        $errors[] = "Unable to save changes. Please try again.";
    }
    return;
}

if ($newPath && $oldPath && preg_match('~^uploads/profile_images/buyer_' . $buyerId . '_[a-f0-9]{32}\.(jpg|png)$~D', $oldPath)
    && is_file($homeDir . $oldPath)) {
    unlink($homeDir . $oldPath);
}
$_SESSION['full_name']           = $form['full_name'];
$_SESSION['buyer_profile_saved'] = true;
header("Location: profile.php", true, 303);
exit;
