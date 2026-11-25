
<?php

    try {

    // Check whether the database connection is available.
    if (! isset($link)) {
        throw new mysqli_sql_exception("Connection unavailable");
    }

    // Fetch the three most recent reviews from active users.
    $reviews = $link->query(
        "SELECT
            u.full_name,
            u.role,
            r.rating,
            r.review_text

         FROM platform_reviews r

         JOIN users u
            ON u.user_id = r.user_id

         WHERE u.account_status = 'active'
            AND u.role IN ('farmer', 'buyer')

         ORDER BY
            r.updated_at DESC,
            r.user_id DESC

         LIMIT 3"
    )->fetch_all(MYSQLI_ASSOC);

    } catch (mysqli_sql_exception $ex) {

    // Log database errors.
    error_log('Review display: ' . $ex->getCode());

    $reviews = null;
    }

?>


<!-- Community Reviews Section -->
<aside id="community-reviews" aria-labelledby="reviews-heading">

    <!-- Section Heading -->
    <h2 id="reviews-heading">
        <?php echo e($t('Community reviews')); ?>
    </h2>


    <!-- Display Message When Reviews Are Unavailable -->
    <?php if (! $reviews): ?>

        <p>
            <?php echo e($t(
    $reviews === null
        ? 'Reviews are temporarily unavailable.'
        : 'Be the first to review AgroMarket.'
)); ?>
        </p>

    <?php else: ?>

        <!-- Display Recent Community Reviews -->
        <?php foreach ($reviews as $review): ?>

            <figure>

                <!-- Review Description -->
                <blockquote>
                    <?php echo e($review['review_text']); ?>
                </blockquote>


                <!-- Reviewer Name, Role and Rating -->
                <figcaption>

                    <?php echo e($review['full_name']); ?>

                    ·

                    <?php echo e($t(ucfirst($review['role']))); ?>

                    ·

                    <?php echo (int) $review['rating']; ?>/5

                </figcaption>

            </figure>

        <?php endforeach; ?>

    <?php endif; ?>


    <!-- Display Review Submission Notification -->
    <?php if ($reviewNotice): ?>

        <p role="status">
            <?php echo e($t($reviewNotice)); ?>
        </p>

    <?php endif; ?>


    <!-- Display Review Form for Eligible Users -->
    <?php if ($reviewer && $reviews !== null): ?>

        <details>

            <summary>
                <?php echo e($t('Write or update your review')); ?>
            </summary>


            <!-- Review Submission Form -->
            <form method="post" action="index.php#community-reviews">

                <!-- CSRF Protection -->
                <input
                    type="hidden"
                    name="token"
                    value="<?php echo e($_SESSION['review_token']); ?>"
                >


                <!-- Rating Selection -->
                <label>

                    <?php echo e($t('Rating')); ?>

                    <select name="rating" required>

                        <option value="">—</option>

                        <?php foreach ([5, 4, 3, 2, 1] as $n): ?>

                            <option value="<?php echo $n; ?>">
                                <?php echo $n; ?>/5
                            </option>

                        <?php endforeach; ?>

                    </select>

                </label>


                <!-- Review Description -->
                <label>

                    <?php echo e($t('Your public review')); ?>

                    <textarea
                        name="review_text"
                        rows="3"
                        maxlength="400"
                        required
                    ></textarea>

                </label>


                <!-- Public Review Privacy Notice -->
                <small>
                    <?php echo e($t(
    'Your name and role appear with your review.'
)); ?>
                </small>


                <!-- Publish Review Button -->
                <button name="publish_review" value="1">
                    <?php echo e($t('Publish review')); ?>
                </button>

            </form>

        </details>

    <?php elseif (! $reviewer): ?>

        <!-- Sign-in Link for Unauthenticated or Ineligible Users -->
        <a href="auth/login.php">
            <?php echo e($t('Sign In')); ?>
        </a>

    <?php endif; ?>

</aside>
