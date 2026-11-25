
<?php

$stage = 'verify';

require __DIR__ . '/includes/recovery.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Verify OTP | AgroMarket Connect</title>

    <!-- Stylesheet -->
    <link rel="stylesheet" href="./style/auth.css">

    <!-- Favicon -->
    <link rel="icon" href="../favicon.ico" type="image/x-icon">

</head>

<body>

    <div class="auth-container">

        <!-- Brand and Home Navigation -->
        <?php require __DIR__ . '/../includes/brand-home.php'; ?>


        <!-- Page Heading -->
        <h2>Verify OTP</h2>


        <!-- OTP Verification Form -->
        <form method="post">

            <!-- CSRF Protection -->
            <input
                type="hidden"
                name="code_csrf"
                value="<?= htmlspecialchars($_SESSION['code_csrf']); ?>"
            >


            <!-- Display Error Message -->
            <?php if ($message): ?>

                <p role="alert">
                    <?= htmlspecialchars($message); ?>
                </p>

            <?php endif; ?>


            <!-- OTP Verification Instructions -->
            <p>
                If an active account matches that address,
                check your email for a six-digit code.
            </p>


            <!-- OTP Input -->
            <label for="otp">
                Email code
            </label>

            <input
                id="otp"
                name="otp"
                autocomplete="one-time-code"
                inputmode="numeric"
                maxlength="6"
                required
            >


            <!-- Verify OTP Button -->
            <button type="submit">
                Verify code
            </button>


            <!-- Request Another OTP -->
            <a href="forget_password.php">
                Request another code
            </a>

        </form>

    </div>

</body>

</html>
