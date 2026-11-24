<?php
$stage='reset';
require __DIR__.'/includes/recovery.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | AgroMarket Connect</title>
    <link rel="stylesheet" href="./style/auth.css">
    <link rel="stylesheet" href="./style/password-visibility.css">
    <script src="./js/password-visibility.js" defer></script>
<link rel="icon" href="../favicon.ico" type="image/x-icon">
</head>
<body>

    <div class="auth-container">
        <?php require __DIR__ . '/../includes/brand-home.php'; ?>
        <h2>Reset Password</h2>
        <p>Create a new password for your account.</p>

        <form method="post">
            <input type="hidden" name="code_csrf" value="<?= htmlspecialchars($_SESSION['code_csrf']) ?>">
            <?php if ($message): ?><p role="alert"><?= htmlspecialchars($message) ?></p><?php endif; ?><div class="form-group">
                <label for="password">New Password</label>
                <input type="password" autocomplete="new-password" required id="password" name="password"
                       placeholder="Enter new password">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" autocomplete="new-password" required id="confirm_password"
                       name="confirm_password"
                       placeholder="Confirm new password">
            </div>

            <button type="submit">Reset Password</button>


        </form>

    </div>

</body>
</html>
