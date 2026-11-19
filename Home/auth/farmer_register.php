

<?php
    session_start();
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

    // Save submitted values, excluding passwords
    function saveFarmerValues()
    {
    $oldValues = $_POST;

    unset(
        $oldValues['password'],
        $oldValues['confirm_password'],
        $oldValues['register']
    );

    $_SESSION['farmer_old_values'] = $oldValues;
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

    // --------------------------------
    // SERVER-SIDE VALIDATION
    // --------------------------------

    if ($first_name === "") {
        $errors[] = "First name is required.";
    }

    if ($last_name === "") {
        $errors[] = "Last name is required.";
    }

    if ($username === "") {
        $errors[] = "Username is required.";
    }

    if ($email === "" || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address.";
    }

    if (! preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
        $errors[] = "Enter a valid 10-digit Indian mobile number.";
    }

    if ($password === "") {
        $errors[] = "Password is required.";
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

        saveFarmerValues();

        redirectFarmer();
    }

    // --------------------------------
    // CHECK DUPLICATES IN REQUIRED ORDER
    // Username -> Email -> Mobile
    // --------------------------------
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

                saveFarmerValues();

                redirectFarmer();
            }
        }

        // --------------------------------
        //  REGISTRATION DATA
        // --------------------------------

        $full_name = $first_name . " " . $last_name;

        $password_hash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $token = bin2hex(random_bytes(32)); // 64-character secure activation token

        $role = "farmer";
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
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        ) ? "https" : "http";

        $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        $activationLink = $scheme . "://" . $_SERVER['HTTP_HOST']
            . $basePath . "/activate.php?uid=" . urlencode((string)$farmer_id)
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

        saveFarmerValues();

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
              class="registration-form"
              onsubmit="return validate()"
              novalidate>

            <h3>Personal Information</h3>

            <div class="form-row">

                <div class="form-group">
                    <label for="first_name">First Name</label>

                    <input type="text"
                           id="first_name"
                           name="first_name"
                           class="form-control"
                           value="<?php echo cleanOutput($_POST['first_name'] ?? ''); ?>"
                           onfocus="hideError(this)"
                           onblur="checkError(this)">
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>

                    <input type="text"
                           id="last_name"
                           name="last_name"
                           class="form-control"
                           value="<?php echo cleanOutput($_POST['last_name'] ?? ''); ?>"
                           onfocus="hideError(this)"
                           onblur="checkError(this)">
                </div>

            </div>

            <div class="form-group">
                <label for="username">Username</label>

                <input type="text"
                       id="username"
                       name="username"
                       class="form-control"
                       value="<?php echo cleanOutput($_POST['username'] ?? ''); ?>"
                       onfocus="hideError(this)"
                       onblur="checkError(this)">
                       <small id="usernameError" class="field-error"></small>
            </div>

            <div class="form-row">

                <div class="form-group">
                    <label for="email">Email Address</label>

                    <input type="email"
                           id="email"
                           name="email"
                           class="form-control"
                           value="<?php echo cleanOutput($_POST['email'] ?? ''); ?>"
                           onfocus="hideError(this)"
                           onblur="validateEmail(this)">
                           <small id="emailError" class="field-error"></small>
                </div>

                <div class="form-group">
                    <label for="mobile">Mobile Number</label>

                    <input type="tel"
                           id="mobile"
                           name="mobile"
                           class="form-control"
                           maxlength="10"
                           value="<?php echo cleanOutput($_POST['mobile'] ?? ''); ?>"
                           onfocus="hideError(this)"
                           onblur="checkError(this)">
                           <small id="mobileError" class="field-error"></small>
                </div>

            </div>

            <div class="form-row">

                <div class="form-group">
                    <label for="password">Password</label>

                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control"
                           onfocus="hideError(this)"
                           onblur="checkError(this)">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>

                    <input type="password"
                           id="confirm_password"
                           name="confirm_password"
                           class="form-control"
                           onfocus="hideError(this)"
                           onblur="checkError(this)">
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
                       value="<?php echo cleanOutput($_POST['address_line'] ?? ''); ?>"
                       onfocus="hideError(this)"
                       onblur="checkError(this)">
            </div>

            <div class="form-row">

                <div class="form-group">
                    <label for="state">State</label>

                    <select id="state"
                            name="state"
                            class="form-control"
                            onfocus="hideError(this)"
                            onblur="checkError(this)">

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
                           value="<?php echo cleanOutput($_POST['district'] ?? ''); ?>"
                           onfocus="hideError(this)"
                           onblur="checkError(this)">
                </div>

            </div>

            <div class="form-row">

                <div class="form-group">
                    <label for="village">Village</label>

                    <input type="text"
                           id="village"
                           name="village"
                           class="form-control"
                           value="<?php echo cleanOutput($_POST['village'] ?? ''); ?>"
                           onfocus="hideError(this)"
                           onblur="checkError(this)">
                </div>

                <div class="form-group">
                    <label for="pincode">Pincode</label>

                    <input type="text"
                           id="pincode"
                           name="pincode"
                           class="form-control"
                           maxlength="6"
                           value="<?php echo cleanOutput($_POST['pincode'] ?? ''); ?>"
                           onfocus="hideError(this)"
                           onblur="checkError(this)">
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
<div id="popupCard" class="popup-overlay">

    <div class="popup-card">

        <div id="popupIcon" class="popup-icon"></div>

        <h2 id="popupTitle"></h2>

        <p id="popupText"></p>

        <button type="button" onclick="closePopup()">OK</button>

    </div>

</div>

<script>

// -----------------------------------
// POPUP FUNCTIONS
// -----------------------------------


const popup = document.getElementById("popupCard");
const popupCard = document.querySelector(".popup-card");

function showPopup(type, message) {

    popup.style.display = "flex";

    if (type === "success") {

        document.getElementById("popupIcon").textContent = "✓";
        document.getElementById("popupTitle").textContent = "Success!";

        popupCard.classList.remove("error");

    } else {

        document.getElementById("popupIcon").textContent = "✕";
        document.getElementById("popupTitle").textContent = "Registration Failed";

        popupCard.classList.add("error");
    }

    document.getElementById("popupText").textContent = message;
}

function closePopup() {
    popup.style.display = "none";

    <?php if ($popupType === "success"): ?>
    window.location.href = "../index.php";
    <?php endif; ?>
}

// Display PHP session popup
<?php if (! empty($popupMessage)): ?>

showPopup(
    <?php echo json_encode($popupType); ?>,
    <?php echo json_encode($popupMessage, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>
);

<?php endif; ?>


// -----------------------------------
// MAIN FORM VALIDATION
// -----------------------------------

function validate() {

    const requiredFields = [
        "first_name",
        "last_name",
        "username",
        "email",
        "mobile",
        "password",
        "confirm_password",
        "address_line",
        "state",
        "district",
        "village",
        "pincode"
    ];

    // Check required fields
    for (let i = 0; i < requiredFields.length; i++) {

        const field = document.getElementById(requiredFields[i]);

        if (field.value.trim() === "") {

            field.focus();
            field.style.border = "2px solid red";

            return false;
        }
    }

    // Email validation
    const email = document.getElementById("email");

    if (!validateEmail(email)) {
        email.focus();
        return false;
    }

    // Mobile validation
    const mobile = document.getElementById("mobile");

    const mobilePattern = /^[6-9][0-9]{9}$/;

    if (!mobilePattern.test(mobile.value.trim())) {

        mobile.focus();
        mobile.style.border = "2px solid red";

        return false;
    }

    // Pincode validation
    const pincode = document.getElementById("pincode");

    if (!/^[0-9]{6}$/.test(pincode.value.trim())) {

        pincode.focus();
        pincode.style.border = "2px solid red";

        return false;
    }

    // Password validation
    const password = document.getElementById("password");

    const confirmPassword = document.getElementById("confirm_password");

    if (password.value === "") {

        password.focus();
        password.style.border = "2px solid red";

        return false;
    }

    if (confirmPassword.value === "") {

        confirmPassword.focus();
        confirmPassword.style.border = "2px solid red";

        return false;
    }

    if (password.value !== confirmPassword.value) {

        confirmPassword.focus();
        confirmPassword.style.border = "2px solid red";

        showPopup("error", "Passwords do not match. Please enter the same password in both fields.");
        return false;
    }

    // Prevent submission if duplicate values were detected
    for (const field of duplicateFields) {

        if (duplicateStatus[field]) {

            document.getElementById(field).focus();

            return false;
        }
    }
    return true;
}

// -----------------------------------
// FIELD ERROR HANDLING
// -----------------------------------

function hideError(element) {

    if (element.value.trim() === "") {
        element.style.border = "2px solid black";
    }
}

function checkError(element) {

    if (element.value.trim() !== "") {
        element.style.border = "2px solid black";
    } else {
        element.style.border = "2px solid red";
    }
}

// -----------------------------------
// EMAIL VALIDATION
// -----------------------------------

function validateEmail(element) {

    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (pattern.test(element.value.trim())) {

        element.style.border = "2px solid black";

        return true;

    } else {

        element.style.border = "2px solid red";

        return false;
    }
}


// =====================================
// LIVE DUPLICATE VALIDATION
// =====================================

const duplicateFields = ["username", "email", "mobile"];

const duplicateTimers = {};
const duplicateRequests = {};

const duplicateStatus = {
    username: false,
    email: false,
    mobile: false
};

function validateDuplicateFormat(field, value) {

    if (value.trim() === "") {
        return false;
    }

    if (field === "email") {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    }

    if (field === "mobile") {
        return /^[6-9][0-9]{9}$/.test(value);
    }

    return true;
}

function showFieldError(field, message) {

    const input = document.getElementById(field);
    const error = document.getElementById(field + "Error");

    input.classList.add("input-error");
    input.classList.remove("input-valid");

    error.textContent = message;
}

function clearFieldError(field) {

    const input = document.getElementById(field);
    const error = document.getElementById(field + "Error");

    input.classList.remove("input-error");
    input.classList.remove("input-valid");

    error.textContent = "";
    duplicateStatus[field] = false;
}

async function checkDuplicate(field) {

    const input = document.getElementById(field);
    const value = input.value.trim();

    // Ignore empty or invalid values
    if (!validateDuplicateFormat(field, value)) {
        clearFieldError(field);
        return;
    }

    // Create a unique request number for this field
    const requestId = (duplicateRequests[field] || 0) + 1;
    duplicateRequests[field] = requestId;

    const formData = new FormData();

    formData.append("field", field);
    formData.append("value", value);

    try {

        const response = await fetch("check_duplicate.php", {
            method: "POST",
            body: formData
        });

        if (!response.ok) {
            throw new Error("Server verification failed");
        }

        const data = await response.json();

        // Ignore outdated responses
        if (
            requestId !== duplicateRequests[field] ||
            input.value.trim() !== value
        ) {
            return;
        }

        if (data.exists) {

            duplicateStatus[field] = true;

            showFieldError(field, data.message);

        } else {

            duplicateStatus[field] = false;

            clearFieldError(field);

            input.classList.add("input-valid");
        }

    } catch (error) {

        console.error("Duplicate check:", error);

        // Do not claim the value is available if verification failed
        showFieldError(field, "Unable to verify. Please try again.");
    }
}

// Check while typing, with a short delay
duplicateFields.forEach(function(field) {

    const input = document.getElementById(field);

    input.addEventListener("input", function() {

        clearTimeout(duplicateTimers[field]);

        // Clear previous result while the new value is checked
        duplicateStatus[field] = false;
        input.classList.remove("input-error", "input-valid");

        document.getElementById(field + "Error").textContent = "";

        duplicateTimers[field] = setTimeout(function() {
            checkDuplicate(field);
        }, 400);

    });

    // Check immediately when leaving the field
    input.addEventListener("blur", function() {

        clearTimeout(duplicateTimers[field]);

        checkDuplicate(field);

    });

});

window.addEventListener("pageshow", function(event) {

    const navigation = performance.getEntriesByType("navigation")[0];

    if (
        event.persisted ||
        (navigation && navigation.type === "back_forward")
    ) {

        // Reset the form when returning through browser history
        const form = document.querySelector(".registration-form");

        if (form) {
            form.reset();
        }

        // Clear field validation messages
        document.querySelectorAll(".field-error").forEach(function(error) {
            error.textContent = "";
        });

        document.querySelectorAll(".form-control").forEach(function(input) {
            input.classList.remove("input-error", "input-valid");
            input.style.border = "";
        });
    }

});
</script>

</body>
</html>
