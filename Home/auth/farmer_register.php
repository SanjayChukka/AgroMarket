<?php
    session_start();
    require_once __DIR__ . "/includes/registration.php";
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: 0");
    require_once __DIR__ . "/../config/db.php";

    $errors       = $_SESSION['farmer_errors'] ?? [];
    $popupType    = $_SESSION['farmer_popup']['type'] ?? "";
    $popupMessage = $_SESSION['farmer_popup']['message'] ?? "";

    // Restore previous form values after a redirect
    $oldValues = $_SESSION['farmer_old_values'] ?? [];

    unset(
    $_SESSION['farmer_errors'],
    $_SESSION['farmer_popup'],
    $_SESSION['farmer_old_values']
    );

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && ! empty($oldValues)) {
    $_POST = $oldValues;
    }

    function cleanOutput($value)
    {
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
    }


    // Redirect to prevent form resubmission
    function redirectFarmer()
    {
    header("Location: farmer_register.php");
    exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {

    // Collect personal information
    $first_name       = trim($_POST['first_name'] ?? "");
    $last_name        = trim($_POST['last_name'] ?? "");
    $username         = trim($_POST['username'] ?? "");
    $email            = trim($_POST['email'] ?? "");
    $mobile           = trim($_POST['mobile'] ?? "");
    $password         = $_POST['password'] ?? "";
    $confirm_password = $_POST['confirm_password'] ?? "";

    // Collect farm information
    $farm_name    = trim($_POST['farm_name'] ?? "");
    $address_line = trim($_POST['address_line'] ?? "");
    $village      = trim($_POST['village'] ?? "");
    $district     = trim($_POST['district'] ?? "");
    $state        = trim($_POST['state'] ?? "");
    $pincode      = trim($_POST['pincode'] ?? "");
    $land_size    = trim($_POST['land_size'] ?? "");

    // SERVER-SIDE VALIDATION
    if ($first_name === "") {
        $errors[] = "First name is required.";
    }

    if ($last_name === "") {
        $errors[] = "Last name is required.";
    }

    if ($username === "") {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must contain at least 3 characters.";
    }

    if ($email === "" || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address.";
    }

    if (! preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
        $errors[] = "Enter a valid 10-digit Indian mobile number.";
    }

    if ($password === "") {
        $errors[] = "Password is required.";
    } elseif (
        strlen($password) < 8 ||
        ! preg_match('/[A-Z]/', $password) ||
        ! preg_match('/[a-z]/', $password) ||
        ! preg_match('/[0-9]/', $password) ||
        ! preg_match('/[^A-Za-z0-9]/', $password)
    ) {
        $errors[] = "Password must contain at least 8 characters, including uppercase, lowercase, number and special character.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if ($address_line === "") {
        $errors[] = "Address is required.";
    }

    if ($village === "") {
        $errors[] = "Village is required.";
    }

    if ($district === "") {
        $errors[] = "District is required.";
    }

    $allowedStates = [
        "Telangana",
        "Andhra Pradesh",
        "Karnataka",
        "Maharashtra",
    ];

    if (! in_array($state, $allowedStates, true)) {
        $errors[] = "Please select a valid state.";
    }

    if (! preg_match('/^[0-9]{6}$/', $pincode)) {
        $errors[] = "Enter a valid 6-digit pincode.";
    }

    if (
        $land_size !== "" &&
        (! is_numeric($land_size) || (float) $land_size <= 0)
    ) {
        $errors[] = "Enter a valid land size.";
    }

    // Redirect after validation errors
    if (! empty($errors)) {

        $_SESSION['farmer_errors'] = $errors;

        saveRegistrationValues('farmer_old_values');

        redirectFarmer();
    }

    // CHECK DUPLICATES IN REQUIRED ORDER
    // Username -> Email -> Mobile
    $transactionStarted = false;
    try {

        $duplicateChecks = [
            [
                "column"  => "username",
                "value"   => $username,
                "message" => "This username is already taken.",
            ],
            [
                "column"  => "email",
                "value"   => $email,
                "message" => "This email address is already registered.",
            ],
            [
                "column"  => "mobile",
                "value"   => $mobile,
                "message" => "This mobile number is already registered.",
            ],
        ];

        foreach ($duplicateChecks as $check) {

            $column = $check['column'];

            // Column names are from our fixed list above
            $check_sql = "SELECT user_id FROM users
                          WHERE $column = ?
                          LIMIT 1";

            $check_stmt = mysqli_prepare($link, $check_sql);

            mysqli_stmt_bind_param(
                $check_stmt,
                "s",
                $check['value']
            );

            mysqli_stmt_execute($check_stmt);

            $result = mysqli_stmt_get_result($check_stmt);

            $exists = mysqli_num_rows($result) > 0;

            mysqli_stmt_close($check_stmt);

            if ($exists) {

                $_SESSION['farmer_popup'] = [
                    "type"    => "error",
                    "message" => $check['message'],
                ];

                saveRegistrationValues('farmer_old_values');

                redirectFarmer();
            }
        }

        //  REGISTRATION DATA
        $full_name = $first_name . " " . $last_name;

        $password_hash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $token = bin2hex(random_bytes(32)); // 64-character secure activation token

        $role           = "farmer";
        $account_status = "pending";

        $farm_name = ($farm_name === "") ? null : $farm_name;

        $land_size = ($land_size === "")
            ? null
            : (float) $land_size;

        // Start transaction
        mysqli_begin_transaction($link);
        $transactionStarted = true;

        // Insert into users
        $user_sql = "INSERT INTO users
            (role, full_name, username, email, mobile,
             password_hash, account_status, token)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $user_stmt = mysqli_prepare($link, $user_sql);

        mysqli_stmt_bind_param(
            $user_stmt,
            "ssssssss",
            $role,
            $full_name,
            $username,
            $email,
            $mobile,
            $password_hash,
            $account_status,
            $token
        );

        mysqli_stmt_execute($user_stmt);

        $farmer_id = mysqli_insert_id($link);

        mysqli_stmt_close($user_stmt);

        // Insert into farmer_profiles
        $farmer_sql = "INSERT INTO farmer_profiles
            (farmer_id, farm_name, address_line, village,
             district, state, pincode, land_size)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $farmer_stmt = mysqli_prepare($link, $farmer_sql);

        mysqli_stmt_bind_param(
            $farmer_stmt,
            "issssssd",
            $farmer_id,
            $farm_name,
            $address_line,
            $village,
            $district,
            $state,
            $pincode,
            $land_size
        );

        mysqli_stmt_execute($farmer_stmt);

        mysqli_stmt_close($farmer_stmt);

        // Save both records
        mysqli_commit($link);
        $transactionStarted = false;

        // Clear previously saved form values
        unset(
            $_SESSION['farmer_old_values'],
            $_SESSION['farmer_errors']
        );

        // Build the activation URL for the current installation.
        // Example: http://localhost/Home/auth/activate.php?uid=12&token=...
        $scheme = (
            (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        ) ? "https" : "http";

        $basePath       = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        $activationLink = $scheme . "://" . $_SERVER['HTTP_HOST']
        . $basePath . "/activate.php?uid=" . urlencode((string) $farmer_id)
        . "&token=" . urlencode($token);

        $subject = "Activate your AgroMarket Connect farmer account";

        $emailMessage = "Hello " . $full_name . ",\r\n\r\n"
            . "Thank you for registering with AgroMarket Connect.\r\n\r\n"
            . "Your User ID: " . $farmer_id . "\r\n\r\n"
            . "Please activate your account by opening this link:\r\n"
            . $activationLink . "\r\n\r\n"
            . "After activation, you can log in using your username or email and password.\r\n\r\n"
            . "If you did not create this account, you can ignore this email.\r\n\r\n"
            . "Regards,\r\nAgroMarket Connect";

        $emailHeaders = "MIME-Version: 1.0\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n";

        // Use the configured sender address when PHP has one.
        $configuredSender = trim((string) ini_get("sendmail_from"));
        if ($configuredSender !== "" && filter_var($configuredSender, FILTER_VALIDATE_EMAIL)) {
            $emailHeaders .= "From: AgroMarket Connect <" . $configuredSender . ">\r\n";
        }

        $emailSent = mail($email, $subject, $emailMessage, $emailHeaders);

        if ($emailSent) {
            $_SESSION['farmer_popup'] = [
                "type"    => "success",
                "message" => "Registration successful! Your account is pending activation. We have sent an activation link and your User ID to " . $email . ". Please check your inbox or spam folder. Click OK to return to the homepage.",
            ];
        } else {
            // The database account remains pending; do not claim that an email was sent.
            $_SESSION['farmer_popup'] = [
                "type"    => "error",
                "message" => "Your registration was saved, but the activation email could not be sent. Please check the email configuration and contact support. Your account is still pending activation.",
            ];
        }

        header("Location: farmer_register.php");
        exit;

    } catch (Throwable $e) {

        if ($transactionStarted) {
            mysqli_rollback($link);
            $transactionStarted = false;
        }

        error_log("Farmer registration error: " . $e->getMessage());
        // Handle a duplicate detected by the database itself
        if ((int) $e->getCode() === 1062) {

            $errorMessage = "A username, email address or mobile number is already registered. Please check your details.";

        } else {

            $errorMessage = "Registration failed due to a server error. Please try again.";
        }

        $_SESSION['farmer_popup'] = [
            "type"    => "error",
            "message" => $errorMessage,
        ];

        saveRegistrationValues('farmer_old_values');

        redirectFarmer();
    }
    }
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Farmer Registration | AgroMarket Connect</title>

    <link rel="stylesheet" href="./style/register.css">
    <link rel="stylesheet" href="./style/password-visibility.css">
    <script src="./js/password-visibility.js" defer></script>
</head>

<body>

<main class="registration-page">

    <section class="registration-container">

        <div class="registration-header">
            <div class="logo">AC</div>
            <h2>Farmer Registration</h2>
            <p>Create your farmer account to start selling crops.</p>
        </div>

        <!-- Server-side validation errors -->
        <?php if (! empty($errors)): ?>

            <div class="error-message" style="color:red;">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo cleanOutput($error); ?></p>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

        <form action="" method="POST"
              class="registration-form" data-role="farmer"

              novalidate>

            <h3>Personal Information</h3>

            <div class="form-row">

                <div class="form-group">
                    <label for="first_name">First Name</label>

                    <input type="text"
                           id="first_name"
                           name="first_name"
                           class="form-control"
                           value="<?php echo cleanOutput($_POST['first_name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>

                    <input type="text"
                           id="last_name"
                           name="last_name"
                           class="form-control"
                           value="<?php echo cleanOutput($_POST['last_name'] ?? ''); ?>">
                </div>

            </div>

            <div class="form-group">
                <label for="username">Username</label>

                <input type="text"
                       id="username"
                       name="username"
                       class="form-control"
                       value="<?php echo cleanOutput($_POST['username'] ?? ''); ?>">
                       <small id="usernameError" class="field-error"></small>
            </div>

            <div class="form-row">

                <div class="form-group">
                    <label for="email">Email Address</label>

                    <input type="email"
                           id="email"
                           name="email"
                           class="form-control"
                           value="<?php echo cleanOutput($_POST['email'] ?? ''); ?>">
                           <small id="emailError" class="field-error"></small>
                </div>

                <div class="form-group">
                    <label for="mobile">Mobile Number</label>

                    <input type="tel"
                           id="mobile"
                           name="mobile"
                           class="form-control"
                           maxlength="10"
                           value="<?php echo cleanOutput($_POST['mobile'] ?? ''); ?>">
                           <small id="mobileError" class="field-error"></small>
                </div>

            </div>

            <div class="form-row">

                <div class="form-group">
                    <label for="password">Password</label>

                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control">
                    <div class="password-feedback" id="farmerPasswordRules">
                        <div class="password-strength" aria-hidden="true"><div class="strength-bar" id="strengthBar"></div></div>
                        <p id="strengthText" aria-live="polite">Password strength: Not entered</p>
                        <ul class="password-requirements">
                            <li id="farmerLengthRule" class="invalid">✗ At least 8 characters</li>
                            <li id="farmerUpperRule" class="invalid">✗ One uppercase letter</li>
                            <li id="farmerLowerRule" class="invalid">✗ One lowercase letter</li>
                            <li id="farmerNumberRule" class="invalid">✗ One number</li>
                            <li id="farmerSpecialRule" class="invalid">✗ One special character</li>
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>

                    <input type="password"
                           id="confirm_password"
                           name="confirm_password"
                           class="form-control">
                </div>

            </div>

            <h3>Farm Information</h3>

            <div class="form-group">
                <label for="farm_name">Farm Name (Optional)</label>

                <input type="text"
                       id="farm_name"
                       name="farm_name"
                       class="form-control"
                       value="<?php echo cleanOutput($_POST['farm_name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="address_line">Address</label>

                <input type="text"
                       id="address_line"
                       name="address_line"
                       class="form-control"
                       value="<?php echo cleanOutput($_POST['address_line'] ?? ''); ?>">
            </div>

            <div class="form-row">

                <div class="form-group">
                    <label for="state">State</label>

                    <select id="state"
                            name="state"
                            class="form-control">

                        <option value="">Select State</option>

                        <option value="Telangana"
                            <?php echo(($_POST['state'] ?? '') === 'Telangana') ? 'selected' : ''; ?>>
                            Telangana
                        </option>

                        <option value="Andhra Pradesh"
                            <?php echo(($_POST['state'] ?? '') === 'Andhra Pradesh') ? 'selected' : ''; ?>>
                            Andhra Pradesh
                        </option>

                        <option value="Karnataka"
                            <?php echo(($_POST['state'] ?? '') === 'Karnataka') ? 'selected' : ''; ?>>
                            Karnataka
                        </option>

                        <option value="Maharashtra"
                            <?php echo(($_POST['state'] ?? '') === 'Maharashtra') ? 'selected' : ''; ?>>
                            Maharashtra
                        </option>

                    </select>
                </div>

                <div class="form-group">
                    <label for="district">District</label>

                    <input type="text"
                           id="district"
                           name="district"
                           class="form-control"
                           value="<?php echo cleanOutput($_POST['district'] ?? ''); ?>">
                </div>

            </div>

            <div class="form-row">

                <div class="form-group">
                    <label for="village">Village</label>

                    <input type="text"
                           id="village"
                           name="village"
                           class="form-control"
                           value="<?php echo cleanOutput($_POST['village'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="pincode">Pincode</label>

                    <input type="text"
                           id="pincode"
                           name="pincode"
                           class="form-control"
                           maxlength="6"
                           value="<?php echo cleanOutput($_POST['pincode'] ?? ''); ?>">
                </div>

            </div>

            <div class="form-group">
                <label for="land_size">Land Size (Acres) - Optional</label>

                <input type="number"
                       id="land_size"
                       name="land_size"
                       class="form-control"
                       min="0"
                       step="0.01"
                       value="<?php echo cleanOutput($_POST['land_size'] ?? ''); ?>">
            </div>

            <button type="submit" name="register">
                Create Farmer Account
            </button>

        </form>

        <div class="registration-footer">
            <p>Already registered? <a href="login.php">Login</a></p>
            <a href="register.php">Back to Account Selection</a>
        </div>

    </section>

</main>

<!-- Reusable popup card -->
<?php require __DIR__ . "/includes/registration_popup.php"; ?>
<script src="js/registration.js" defer></script>

</body>
</html>
