<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | AgroMarket Connect</title>
    <link rel="stylesheet" href="./style/login.css">
</head>

<body>

    <main class="login-page">

        <!-- Login Container -->
        <section class="login-container">

            <!-- Logo and Heading -->
            <div class="login-header">
                <div class="logo">AC</div>
                <h2>AgroMarket Connect</h2>
                <p>Welcome Back! Login to your Account</p>
            </div>

            <!-- Login Form -->
            <form action="" method="POST" class="login-form">

                <div class="form-group">
                    <label for="username">Email / Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter your email or username"
                        required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required>
                </div>

                <!-- Remember Me and Forgot Password -->
                <div class="login-options">

                    <div class="remember-me">
                        <input
                            type="checkbox"
                            id="remember"
                            name="remember">
                        <label for="remember">Remember Me</label>
                    </div>

                    <a href="./forget_password.php">Forgot Password?</a>

                </div>

                <button type="submit" name="login">
                    Login
                </button>

            </form>

            <!-- Registration Link -->
            <div class="register-link">
                <p>Don't have an account? <a href="./register.php">Register Now</a></p>
            </div>

            <!-- Back to Home -->
            <div class="back-home">
                <a href="../index.php">Back to Home</a>
            </div>

        </section>

    </main>

</body>

</html>