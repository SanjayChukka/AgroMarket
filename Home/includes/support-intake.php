<?php
require_once __DIR__ . '/html.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$_SESSION['support_token'] ??= bin2hex(random_bytes(32));
$notice = $_SESSION['support_notice'] ?? '';
unset($_SESSION['support_notice']);
$error = '';
function supportField(string $name): string {
    return is_string($_POST[$name] ?? null) ? trim($_POST[$name]) : '';
}
$portalRole = $_SESSION['user_role'] ?? '';
$owner = in_array($portalRole, ['buyer','farmer'], true) ? ($_SESSION['user_id'] ?? null) : null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = supportField('name');
    $email = supportField('email');
    $mobile = supportField('mobile');
    $role = $owner ? $portalRole : supportField('user_type');
    $category = supportField($isGrievance ? 'grievance_type' : 'subject');
    $description = supportField($isGrievance ? 'description' : 'message');
    $reference = supportField('reference');
    $file = $_FILES['attachment'] ?? [];
    $mime = $bytes = null;
    if (!hash_equals($_SESSION['support_token'], supportField('support_token'))) {
        $error = 'Your form expired. Please submit it again.';
    } elseif ($name === '' || strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254 ||
        !in_array($role, ['farmer','buyer','other'], true) || !in_array($category, ['account','product','technical','order','payment','other'], true) ||
        $description === '' || strlen($description) > 12000 || strlen($mobile) > 24 || strlen($reference) > 100) {
        $error = 'Enter your name, email, user type, category and a description of up to 12,000 bytes.';
    } elseif (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2 * 1024 * 1024 || !is_uploaded_file($file['tmp_name'])) {
            $error = 'Choose a PDF, PNG or JPEG file up to 2 MB.';
        } else {
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
            if (in_array($mime, ['application/pdf','image/png','image/jpeg'], true)) {
                $bytes = file_get_contents($file['tmp_name']);
            }
            if (!$bytes) $error = 'Choose a valid PDF, PNG or JPEG attachment.';
        }
    }
    if ($error === '') {
        require_once __DIR__ . '/../config/db.php';
        $number = 'AMC-' . strtoupper(bin2hex(random_bytes(8)));
        $subject = ucfirst($category) . ($isGrievance ? ' complaint' : ' enquiry');
        try {
            $stmt = $link->prepare('INSERT INTO support_requests
                (ticket_number,user_id,contact_name,email,mobile,contact_role,subject,reference_text,description,file_type,file_data)
                VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$number,$owner,$name,$email,$mobile,$role,$subject,$reference,$description,$mime,$bytes]);
            $_SESSION['support_notice'] = 'Submitted successfully. Your reference is ' . $number . '.';
            header('Location: ' . ($isGrievance ? 'grievance.php' : 'contact.php') . '#support-form', true, 303);
            exit;
        } catch (mysqli_sql_exception $exception) {
            error_log('Support intake database error ' . $exception->getCode());
            $error = 'Your message could not be saved. Please try again.';
        }
    }
}
