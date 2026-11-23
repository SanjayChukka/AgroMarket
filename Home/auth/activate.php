
<?php

session_start();

require_once __DIR__ . "/../config/db.php";

/* Get Activation Parameters */
$uid = filter_input(
    INPUT_GET,
    "uid",
    FILTER_VALIDATE_INT
);

$token = trim($_GET["token"] ?? "");

/* Validate User ID and Token */
if (!$uid || !preg_match('/^[a-f0-9]{64}$/i', $token)) {
    header("Location: login.php?activation=invalid");
    exit;
}

/* Fetch User Account */
$sql = "
    SELECT
        user_id,
        account_status
    FROM users
    WHERE user_id = ?
      AND token = ?
      AND role IN ('farmer', 'buyer')
    LIMIT 1
";

$stmt = mysqli_prepare($link, $sql);

if (!$stmt) {
    error_log("Activation prepare failed: " . mysqli_error($link));

    header("Location: login.php?activation=error");
    exit;
}

/* Execute User Lookup */
mysqli_stmt_bind_param($stmt, "is", $uid, $token);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

/* Handle Invalid Activation Link */
if (!$user) {
    header("Location: login.php?activation=invalid");
    exit;
}

/* Handle Already Activated Account */
if ($user["account_status"] === "active") {
    header("Location: login.php?activated=already");
    exit;
}

/* Activate User Account */
$updateSql = "
    UPDATE users
    SET
        account_status = 'active',
        email_verified_at = NOW(),
        token = NULL
    WHERE user_id = ?
      AND token = ?
      AND account_status = 'pending'
";

$updateStmt = mysqli_prepare($link, $updateSql);

if (!$updateStmt) {
    error_log("Activation update prepare failed: " . mysqli_error($link));

    header("Location: login.php?activation=error");
    exit;
}

/* Execute Account Activation */
mysqli_stmt_bind_param(
    $updateStmt,
    "is",
    $uid,
    $token
);

mysqli_stmt_execute($updateStmt);

$updated = mysqli_stmt_affected_rows($updateStmt);

mysqli_stmt_close($updateStmt);

/* Redirect Based on Activation Result */
if ($updated === 1) {
    header("Location: login.php?activated=1");
} else {
    header("Location: login.php?activation=invalid");
}

exit;
