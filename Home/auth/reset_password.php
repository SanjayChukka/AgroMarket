
<?php

    $stage = 'reset';

    require __DIR__ . '/includes/recovery.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reset Password | AgroMarket Connect</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="./style/auth.css">
    <link rel="stylesheet" href="./style/password-visibility.css">

    <!-- Password Visibility JavaScript -->
    <script src="./js/password-visibility.js" defer></script>

    <!-- Favicon -->
    <link rel="icon" href="../favicon.ico" type="image/x-icon">

</head>

<body>

    <div class="auth-container">

        <!-- Brand and Home Navigation -->
        <?php require __DIR__ . '/../includes/brand-home.php'; ?>


        <!-- Page Heading -->
        <h2>Reset Password</h2>

        <p>Create a new password for your account.</p>


        <!-- Password Reset Form -->
        <form method="post">

            <!-- CSRF Protection -->
            <input
                type="hidden"
                name="code_csrf"
                value="<?php echo htmlspecialchars($_SESSION['code_csrf']); ?>"
            >


            <!-- Display Error Message -->
            <?php if ($message): ?>

                <p role="alert">
                    <?php echo htmlspecialchars($message); ?>
                </p>

            <?php endif; ?>


            <!-- New Password -->
            <div class="form-group">

                <label for="password">
                    New Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="new-password"
                    placeholder="Enter new password"
                    required
                >

            </div>


            <!-- Confirm New Password -->
            <div class="form-group">

                <label for="confirm_password">
                    Confirm New Password
                </label>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    autocomplete="new-password"
                    placeholder="Confirm new password"
                    required
                >

            </div>


            <!-- Reset Password Button -->
            <button type="submit">
                Reset Password
            </button>

        </form>

    </div>

</body>

</html>
