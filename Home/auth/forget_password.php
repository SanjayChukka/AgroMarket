<?php
$stage='request';
require __DIR__.'/includes/recovery.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Forgot Password | AgroMarket Connect</title>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="./style/auth.css">

    <!-- Favicon -->
    <link rel="icon" href="../favicon.ico" type="image/x-icon">
</head>

<body>

    <div class="auth-container">
        <?php require __DIR__ . '/../includes/brand-home.php'; ?>

        <!-- Page Heading -->
        <h2>Forgot password?</h2>
        <form method="post">
            <input type="hidden" name="code_csrf" value="<?= htmlspecialchars($_SESSION['code_csrf']) ?>">
            <?php if ($message): ?><p role="alert"><?= htmlspecialchars($message) ?></p><?php endif; ?><div class="form-group"><label for="email">Registered email address</label>
            <input id="email" type="email" name="email" autocomplete="email" required></div>
            <button type="submit">Send recovery code</button>
        </form>

        <!-- Back to Login -->
        <div class="auth-footer">
            <a href="login.php">Back to Login</a>
        </div>

        <!-- Back to Home -->
        <div class="back-home">
            <a href="../index.php">Back to Home</a>
        </div>

    </div>

</body>

</html>
