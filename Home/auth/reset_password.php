
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | AgroMarket Connect</title>
    <link rel="stylesheet" href="./style/auth.css">
    <link rel="stylesheet" href="./style/password-visibility.css">
    <script src="./js/password-visibility.js" defer></script>
</head>
<body>

    <div class="auth-container">
        <h2>Reset Password</h2>
        <p>Create a new password for your account.</p>

        <form action="#" method="POST">

            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password"
                       placeholder="Enter new password">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password"
                       name="confirm_password"
                       placeholder="Confirm new password">
            </div>

            <button type="submit">Reset Password</button>

        </form>

    </div>

</body>
</html>
