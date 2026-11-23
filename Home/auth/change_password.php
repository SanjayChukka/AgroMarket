
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Change Password | AgroMarket Connect</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="./style/auth.css">
    <link rel="stylesheet" href="./style/password-visibility.css">

    <!-- Favicon -->
    <link rel="icon" href="../favicon.ico" type="image/x-icon">

    <!-- JavaScript -->
    <script src="./js/password-visibility.js" defer></script>
</head>

<body>

    <div class="auth-container">

        <h2>Change Password</h2>

        <form action="#" method="POST">

            <!-- Current Password -->
            <div class="form-group">
                <label for="current_password">Current Password</label>

                <input
                    type="password"
                    id="current_password"
                    name="current_password"
                >
            </div>

            <!-- New Password -->
            <div class="form-group">
                <label for="new_password">New Password</label>

                <input
                    type="password"
                    id="new_password"
                    name="new_password"
                >
            </div>

            <!-- Confirm New Password -->
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                >
            </div>

            <!-- Submit Button -->
            <button type="submit">
                Update Password
            </button>

        </form>

    </div>

</body>

</html>
