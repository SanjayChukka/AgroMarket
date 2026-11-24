<?php
$stage='verify';
require __DIR__.'/includes/recovery.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP | AgroMarket Connect</title>
    <link rel="stylesheet" href="./style/auth.css">
<link rel="icon" href="../favicon.ico" type="image/x-icon">
</head>
<body>

    <div class="auth-container">
        <?php require __DIR__ . '/../includes/brand-home.php'; ?>
        <h2>Verify OTP</h2>

        <form method="post">
            <input type="hidden" name="code_csrf" value="<?= htmlspecialchars($_SESSION['code_csrf']) ?>">
            <?php if ($message): ?><p role="alert"><?= htmlspecialchars($message) ?></p><?php endif; ?><p>If an active account matches that address, check your email for a six-digit code.</p>
            <label for="otp">Email code</label><input id="otp" name="otp" autocomplete="one-time-code" inputmode="numeric" maxlength="6" required>
            <button type="submit">Verify code</button><a href="forget_password.php">Request another code</a>
        </form>

    </div>

</body>
</html>
