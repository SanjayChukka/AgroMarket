
<?php

    session_start();

    /* Prevent Browser from Restoring an Old Login Page */
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: 0");

    require_once __DIR__ . "/../config/db.php";

    /* Initialize Variables */
    $loginError = "";

    $listingId = filter_input(
    INPUT_GET,
    "listing_id",
    FILTER_VALIDATE_INT
    );

    /* Handle Login Form Submission */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {

    $identifier = trim($_POST['username'] ?? "");
    $password   = $_POST['password'] ?? "";

    /* Validate Login Inputs */
    if ($identifier === "" || $password === "") {

        $loginError = "Please enter your username/email and password.";

    } else {

        /* Fetch User Details */
        $sql = "
            SELECT
                user_id,
                role,
                full_name,
                password_hash,
                account_status
            FROM users
            WHERE username = ? OR email = ?
            LIMIT 1
        ";

        $stmt = mysqli_prepare($link, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "ss",
                $identifier,
                $identifier
            );

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            $user = mysqli_fetch_assoc($result);

            mysqli_stmt_close($stmt);

            /* Verify User Credentials and Account Status */
            if (! $user || ! password_verify($password, $user['password_hash'])) {

                $loginError = "Invalid username/email or password.";

            } elseif ($user['account_status'] === 'pending') {

                $loginError = "Your account is not activated yet. Please open the activation link sent to your registered email.";

            } elseif ($user['account_status'] !== 'active') {

                $loginError = "Your account is not active. Please contact support.";

            } else {

                /* Create User Session */
                session_regenerate_id(true);

                $_SESSION['user_id']   = (int) $user['user_id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];

                /* Update Last Login Time */
                $update = mysqli_prepare(
                    $link,
                    "UPDATE users SET last_login = NOW() WHERE user_id = ?"
                );

                if ($update) {

                    mysqli_stmt_bind_param(
                        $update,
                        "i",
                        $_SESSION['user_id']
                    );

                    mysqli_stmt_execute($update);

                    mysqli_stmt_close($update);
                }

                /* Redirect According to User Role */
                if ($user['role'] === 'farmer') {

                    $dashboard = "../farmer/dashboard.php";

                } elseif ($user['role'] === 'buyer') {

                    $dashboard = $listingId > 0
                        ? "../buyer/place_order.php?listing_id=" . $listingId
                        : "../buyer/dashboard.php";

                } else {

                    $dashboard = "../admin/dashboard.php";
                }

                /* Redirect Using HTTP 303 */
                header("Location: " . $dashboard, true, 303);
                exit;
            }

        } else {

            error_log("Login prepare failed: " . mysqli_error($link));

            $loginError = "Unable to process login right now. Please try again.";
        }
    }
    }

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | AgroMarket Connect</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="./style/login.css">
    <link rel="stylesheet" href="./style/password-visibility.css">

    <!-- Favicon -->
    <link rel="icon" href="../favicon.ico" type="image/x-icon">

    <!-- JavaScript -->
    <script src="./js/password-visibility.js" defer></script>
</head>

<body>

    <main class="login-page">

        <section class="login-container">

            <!-- Login Header -->
            <div class="login-header">
                <div class="logo">AC</div>

                <h2>AgroMarket Connect</h2>

                <p>Welcome Back! Login to your Account</p>
            </div>

            <!-- Account Activation Success -->
            <?php if (isset($_GET['activated']) && $_GET['activated'] === '1'): ?>

                <p class="login-notice" style="color:green; text-align:center;">
                    Your email has been verified. You can now log in.
                </p>

            <?php endif; ?>

            <!-- Invalid Activation Link -->
            <?php if (isset($_GET['activation']) && $_GET['activation'] === 'invalid'): ?>

                <p style="color:#dc3545; text-align:center;">
                    This activation link is invalid or has already been used.
                </p>

            <!-- Account Activation Error -->
            <?php elseif (isset($_GET['activation']) && $_GET['activation'] === 'error'): ?>

                <p style="color:#dc3545; text-align:center;">
                    We could not activate your account. Please try again later.
                </p>

            <!-- Account Already Activated -->
            <?php elseif (isset($_GET['activated']) && $_GET['activated'] === 'already'): ?>

                <p style="color:green; text-align:center;">
                    Your account is already activated. Please log in.
                </p>

            <?php endif; ?>

            <!-- Login Error Message -->
            <?php if ($loginError !== ""): ?>

                <p class="login-error" style="color:#dc3545; text-align:center;">
                    <?php echo htmlspecialchars($loginError, ENT_QUOTES, "UTF-8"); ?>
                </p>

            <?php endif; ?>

            <!-- Login Form -->
            <form action="" method="POST" class="login-form">

                <!-- Email or Username -->
                <div class="form-group">
                    <label for="username">Email / Username</label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter your email or username"
                        required
                    >
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <!-- Login Options -->
                <div class="login-options">

                    <!-- Remember Me -->
                    <div class="remember-me">
                        <input
                            type="checkbox"
                            id="remember"
                            name="remember"
                        >

                        <label for="remember">Remember Me</label>
                    </div>

                    <!-- Forgot Password -->
                    <a href="./forget_password.php">
                        Forgot Password?
                    </a>

                </div>

                <!-- Login Button -->
                <button type="submit" name="login">
                    Login
                </button>

            </form>

            <!-- Registration Link -->
            <div class="register-link">
                <p>
                    Don't have an account?
                    <a href="./register.php">Register Now</a>
                </p>
            </div>

            <!-- Back to Home -->
            <div class="back-home">
                <a href="../index.php">Back to Home</a>
            </div>

        </section>

    </main>

    <!-- Login JavaScript -->
    <script src="js/login.js" defer></script>

</body>

</html>
