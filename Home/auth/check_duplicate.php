<?php
    header("Content-Type: application/json");

    require_once __DIR__ . "/../config/db.php";

    $field = trim($_POST['field'] ?? "");
    $value = trim($_POST['value'] ?? "");

    // Only allow these database columns
    $allowedFields = [
    "username",
    "email",
    "mobile",
    ];

    if (! in_array($field, $allowedFields, true) || $value === "") {
    echo json_encode([
        "exists"  => false,
        "message" => "",
    ]);
    exit;
    }

    try {

    $sql = "SELECT user_id FROM users WHERE `$field` = ? LIMIT 1";

    $stmt = mysqli_prepare($link, $sql);

    mysqli_stmt_bind_param($stmt, "s", $value);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $exists = mysqli_num_rows($result) > 0;

    mysqli_stmt_close($stmt);

    $messages = [
        "username" => "This username is already taken.",
        "email"    => "This email address is already registered.",
        "mobile"   => "This mobile number is already registered.",
    ];

    echo json_encode([
        "exists"  => $exists,
        "message" => $exists ? $messages[$field] : "",
    ]);

    } catch (Throwable $e) {

    error_log("Duplicate check error: " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "exists"  => false,
        "message" => "Unable to verify this value. Please try again.",
    ]);
    }
?>
