<?php
try {
    if (!isset($link)) throw new mysqli_sql_exception("Connection unavailable");
    $reviews = $link->query("SELECT u.full_name,u.role,r.rating,r.review_text FROM platform_reviews r
        JOIN users u ON u.user_id=r.user_id WHERE u.account_status='active' AND u.role IN ('farmer','buyer')
        ORDER BY r.updated_at DESC,r.user_id DESC LIMIT 3")->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $ex) {
    error_log('Review display: ' . $ex->getCode());
    $reviews = null;
}
?>
<aside id="community-reviews" aria-labelledby="reviews-heading">
    <h2 id="reviews-heading"><?= e($t('Community reviews')) ?></h2>
    <?php if (!$reviews): ?>
        <p><?= e($t($reviews === null ? 'Reviews are temporarily unavailable.' : 'Be the first to review AgroMarket.')) ?></p>
    <?php else: foreach ($reviews as $review): ?>
        <figure><blockquote><?= e($review['review_text']) ?></blockquote>
        <figcaption><?= e($review['full_name']) ?> · <?= e($t(ucfirst($review['role']))) ?> · <?= (int)$review['rating'] ?>/5</figcaption></figure>
    <?php endforeach; endif; ?>
    <?php if ($reviewNotice): ?><p role="status"><?= e($t($reviewNotice)) ?></p><?php endif; ?>
    <?php if ($reviewer && $reviews !== null): ?>
        <details><summary><?= e($t('Write or update your review')) ?></summary>
        <form method="post" action="index.php#community-reviews">
            <input type="hidden" name="token" value="<?= e($_SESSION['review_token']) ?>">
            <label><?= e($t('Rating')) ?>
                <select name="rating" required><option value="">—</option>
                    <?php foreach ([5,4,3,2,1] as $n): ?><option value="<?= $n ?>"><?= $n ?>/5</option><?php endforeach; ?>
                </select>
            </label>
            <label><?= e($t('Your public review')) ?><textarea name="review_text" rows="3" maxlength="400" required></textarea></label>
            <small><?= e($t('Your name and role appear with your review.')) ?></small>
            <button name="publish_review" value="1"><?= e($t('Publish review')) ?></button>
        </form></details>
    <?php elseif (!$reviewer): ?><a href="auth/login.php"><?= e($t('Sign In')) ?></a><?php endif; ?>
</aside>
