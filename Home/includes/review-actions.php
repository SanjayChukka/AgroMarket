
<?php

// Check whether the current user is eligible to publish a review.
$reviewer = !empty($_SESSION['user_id']) &&
    in_array(
        $_SESSION['user_role'] ?? '',
        ['buyer', 'farmer'],
        true
    );


// Initialize the review CSRF token.
$_SESSION['review_token'] ??= bin2hex(random_bytes(32));


// Retrieve and clear the previous review notification.
$reviewNotice = $_SESSION['review_notice'] ?? '';

unset($_SESSION['review_notice']);


// Process the review form submission.
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['publish_review'])
) {

    // Retrieve the rating and review text.
    $rating = filter_input(
        INPUT_POST,
        'rating',
        FILTER_VALIDATE_INT
    );

    $body = is_string($_POST['review_text'] ?? null)
        ? trim($_POST['review_text'])
        : '';


    // Validate user authentication and CSRF token.
    if (
        !$reviewer ||
        !is_string($_POST['token'] ?? null) ||
        !hash_equals(
            $_SESSION['review_token'],
            $_POST['token']
        )
    ) {

        $reviewNotice = 'Sign in and try again.';

    } elseif (
        !$rating ||
        $rating < 1 ||
        $rating > 5 ||
        !preg_match('/^.{1,400}$/usD', $body)
    ) {

        // Validate the rating and review length.
        $reviewNotice = 'Choose a rating and enter up to 400 characters.';

    } else {

        try {

            // Enable MySQLi exception handling.
            mysqli_report(
                MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT
            );

            require_once __DIR__ . '/../config/db.php';


            // Insert a new review or update the existing review.
            $stmt = $link->prepare(
                "INSERT INTO platform_reviews (
                    user_id,
                    rating,
                    review_text
                )
                SELECT
                    user_id,
                    ?,
                    ?
                FROM users
                WHERE user_id = ?
                    AND account_status = 'active'
                    AND role IN ('farmer', 'buyer')

                ON DUPLICATE KEY UPDATE
                    rating = ?,
                    review_text = ?,
                    updated_at = NOW()"
            );

            $stmt->execute([
                $rating,
                $body,
                $_SESSION['user_id'],
                $rating,
                $body
            ]);


            // Set the review submission result.
            $reviewNotice = $stmt->affected_rows
                ? 'Review published.'
                : 'No change to publish.';

        } catch (mysqli_sql_exception $ex) {

            // Log database errors.
            error_log(
                'Review submission: ' . $ex->getCode()
            );

            $reviewNotice = 'Reviews are temporarily unavailable.';
        }
    }


    // Store the review notification in the session.
    $_SESSION['review_notice'] = $reviewNotice;


    // Redirect to the community reviews section.
    header(
        'Location: index.php#community-reviews',
        true,
        303
    );

    exit;
}
