<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | AgroMarket Connect</title>
    <link rel="stylesheet" href="./style/auth.css">
</head>

<body>

    <div class="auth-container">
        <h2>Forgot Password?</h2>
        <p>Enter your registered email or mobile number to reset your password.</p>

        <form action="verify_reset_otp.php" method="POST">

            <div class="form-group">
                <label>Choose Authentication Method</label>

                <div class="radio-options">
                    <label>
                        <input type="radio" name="method" value="email" checked>
                        Email
                    </label>

                    <label>
                        <input type="radio" name="method" value="mobile">
                        Mobile
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="identifier">Registered Email / Mobile</label>
                <input type="text" id="identifier" name="identifier"
                    placeholder="Enter your registered email or mobile">
            </div>

            <button type="submit">Send OTP</button>

        </form>

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