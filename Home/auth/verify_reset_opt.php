
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP | AgroMarket Connect</title>
    <link rel="stylesheet" href="./style/auth.css">
</head>
<body>

    <div class="auth-container">
        <h2>Verify OTP</h2>
        <p>Enter the OTP sent to your registered email or mobile number.</p>

        <form action="reset_password.php" method="POST">

            <div class="form-group">
                <label for="otp">Enter OTP</label>
                <input type="text" id="otp" name="otp"
                       placeholder="Enter 6-digit OTP" maxlength="6">
            </div>

            <button type="submit">Verify OTP</button>

            <p class="form-note">
                Didn't receive OTP? <a href="forget_password.php">Resend OTP</a>
            </p>

        </form>

    </div>

</body>
</html>
