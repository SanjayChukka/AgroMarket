<?php
$reviewer = !empty($_SESSION['user_id']) && in_array($_SESSION['user_role'] ?? '', ['buyer', 'farmer'], true);
$_SESSION['review_token'] ??= bin2hex(random_bytes(32));
$reviewNotice = $_SESSION['review_notice'] ?? '';
unset($_SESSION['review_notice']);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publish_review'])) {
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
    $body = is_string($_POST['review_text'] ?? null) ? trim($_POST['review_text']) : '';
    if (!$reviewer || !is_string($_POST['token'] ?? null) || !hash_equals($_SESSION['review_token'], $_POST['token'])) {
        $reviewNotice = 'Sign in and try again.';
    } elseif (!$rating || $rating < 1 || $rating > 5 || !preg_match('/^.{1,400}$/usD', $body)) {
        $reviewNotice = 'Choose a rating and enter up to 400 characters.';
    } else {
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            require_once __DIR__ . '/../config/db.php';
            $stmt = $link->prepare("INSERT INTO platform_reviews (user_id,rating,review_text)
                SELECT user_id,?,? FROM users WHERE user_id=? AND account_status='active' AND role IN ('farmer','buyer')
                ON DUPLICATE KEY UPDATE rating=?, review_text=?, updated_at=NOW()");
            $stmt->execute([$rating,$body,$_SESSION['user_id'],$rating,$body]);
            $reviewNotice = $stmt->affected_rows ? 'Review published.' : 'No change to publish.';
        } catch (mysqli_sql_exception $ex) {
            error_log('Review submission: ' . $ex->getCode());
            $reviewNotice = 'Reviews are temporarily unavailable.';
        }
    }
    $_SESSION['review_notice'] = $reviewNotice;
    header('Location: index.php#community-reviews', true, 303);
    exit;
}
