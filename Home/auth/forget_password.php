
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

        <!-- Page Heading -->
        <h2>Forgot Password?</h2>

        <p>
            Enter your registered email or mobile number to reset your password.
        </p>

        <!-- Forgot Password Form -->
        <form action="verify_reset_opt.php" method="POST">

            <!-- Authentication Method -->
            <div class="form-group">
                <label>Choose Authentication Method</label>

                <div class="radio-options">

                    <!-- Email Option -->
                    <label>
                        <input
                            type="radio"
                            name="method"
                            value="email"
                            checked
                        >
                        Email
                    </label>

                    <!-- Mobile Option -->
                    <label>
                        <input
                            type="radio"
                            name="method"
                            value="mobile"
                        >
                        Mobile
                    </label>

                </div>
            </div>

            <!-- Registered Email or Mobile -->
            <div class="form-group">
                <label for="identifier">
                    Registered Email / Mobile
                </label>

                <input
                    type="text"
                    id="identifier"
                    name="identifier"
                    placeholder="Enter your registered email or mobile"
                >
            </div>

            <!-- Submit Button -->
            <button type="submit">
                Send OTP
            </button>

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
