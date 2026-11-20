
<?php
    session_start();

    $fieldErrors = $_SESSION['buyer_field_errors'] ?? [];
    $oldValues   = $_SESSION['buyer_old_values'] ?? [];

    unset(
    $_SESSION['buyer_field_errors'],
    $_SESSION['buyer_old_values']
    );

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && ! empty($oldValues)) {
    $_POST = $oldValues;
    }

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: 0");

    require_once __DIR__ . "/../config/db.php";

    $errors      = [];
    $message     = $_SESSION['buyer_message'] ?? "";
    $messageType = $_SESSION['buyer_message_type'] ?? "";

    unset($_SESSION['buyer_message'], $_SESSION['buyer_message_type']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {

    $first_name       = trim($_POST['first_name'] ?? "");
    $last_name        = trim($_POST['last_name'] ?? "");
    $username         = trim($_POST['username'] ?? "");
    $email            = trim($_POST['email'] ?? "");
    $mobile           = trim($_POST['mobile'] ?? "");
    $password         = $_POST['password'] ?? "";
    $confirm_password = $_POST['confirm_password'] ?? "";

    $business_type = trim($_POST['buyer_type'] ?? "");
    $business_name = trim($_POST['business_name'] ?? "");
    $state         = trim($_POST['state'] ?? "");
    $district      = trim($_POST['district'] ?? "");
    $address       = trim($_POST['address'] ?? "");
    $city          = trim($_POST['city'] ?? "");
    $pincode       = trim($_POST['pincode'] ?? "");

    // Validation
    if ($first_name === "" || $last_name === "") {
        $errors[] = "Enter your full name.";
    }

    if ($username === "") {
        $errors[] = "Username is required.";
    }

    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address.";
    }

    if (! preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
        $errors[] = "Enter a valid 10-digit Indian mobile number.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must contain at least 8 characters.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    $allowedTypes = [
        "individual", "retailer", "wholesaler",
        "distributor", "processor",
    ];

    if (! in_array($business_type, $allowedTypes, true)) {
        $errors[] = "Select a valid buyer type.";
    }

    $allowedStates = [
        "Telangana", "Andhra Pradesh",
        "Karnataka", "Maharashtra",
    ];

    if (! in_array($state, $allowedStates, true)) {
        $errors[] = "Select a valid state.";
    }

    if ($district === "" || $address === "") {
        $errors[] = "District and address are required.";
    }

    if ($pincode !== "" && ! preg_match('/^[0-9]{6}$/', $pincode)) {
        $errors[] = "Enter a valid 6-digit PIN code.";
    }

    if (empty($errors)) {

        $transactionStarted = false;

        try {

            // Check duplicate username, email and mobile
            $duplicateChecks = [
                ["username", $username, "Username already exists."],
                ["email", $email, "Email is already registered."],
                ["mobile", $mobile, "Mobile number is already registered."],
            ];

            foreach ($duplicateChecks as $check) {

                [$column, $value, $errorMessage] = $check;

                $sql = "SELECT user_id FROM users
                        WHERE $column = ? LIMIT 1";

                $stmt = mysqli_prepare($link, $sql);
                mysqli_stmt_bind_param($stmt, "s", $value);
                mysqli_stmt_execute($stmt);

                $result = mysqli_stmt_get_result($stmt);
                $exists = mysqli_num_rows($result) > 0;

                mysqli_stmt_close($stmt);

                if ($exists) {

                    $_SESSION['buyer_field_errors'] = [
                        $column => $errorMessage,
                    ];

                    $oldValues = $_POST;

                    unset(
                        $oldValues['password'],
                        $oldValues['confirm_password'],
                        $oldValues['register']
                    );

                    $_SESSION['buyer_old_values'] = $oldValues;

                    header("Location: buyer_register.php");
                    exit;
                }
            }

            $full_name     = $first_name . " " . $last_name;
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $token         = bin2hex(random_bytes(32));

            $role           = "buyer";
            $account_status = "pending";

            $business_name = $business_name === "" ? null : $business_name;
            $city          = $city === "" ? null : $city;
            $pincode       = $pincode === "" ? null : $pincode;

            mysqli_begin_transaction($link);
            $transactionStarted = true;

            // Insert user account
            $userSql = "INSERT INTO users
                (role, full_name, username, email, mobile,
                 password_hash, account_status, token)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $userStmt = mysqli_prepare($link, $userSql);

            mysqli_stmt_bind_param(
                $userStmt,
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

            mysqli_stmt_execute($userStmt);
            $buyer_id = mysqli_insert_id($link);
            mysqli_stmt_close($userStmt);

            // Insert buyer profile
            $profileSql = "INSERT INTO buyer_profiles
                (buyer_id, business_name, business_type,
                 business_address, delivery_address,
                 village_city, district, state, pincode)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $profileStmt = mysqli_prepare($link, $profileSql);

            mysqli_stmt_bind_param(
                $profileStmt,
                "issssssss",
                $buyer_id,
                $business_name,
                $business_type,
                $address,
                $address,
                $city,
                $district,
                $state,
                $pincode
            );

            mysqli_stmt_execute($profileStmt);
            mysqli_stmt_close($profileStmt);

            mysqli_commit($link);
            $transactionStarted = false;

            // Generate activation link
            $scheme = (
                (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
            ) ? "https" : "http";

            $basePath = rtrim(
                str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])),
                '/'
            );

            $activationLink = $scheme . "://" . $_SERVER['HTTP_HOST']
            . $basePath . "/activate.php?uid="
            . urlencode((string) $buyer_id)
            . "&token=" . urlencode($token);

            $subject = "Activate your AgroMarket Connect buyer account";

            $emailMessage = "Hello " . $full_name . ",\r\n\r\n"
                . "Thank you for registering with AgroMarket Connect.\r\n\r\n"
                . "Your User ID: " . $buyer_id . "\r\n\r\n"
                . "Activate your account using this link:\r\n"
                . $activationLink . "\r\n\r\n"
                . "Regards,\r\nAgroMarket Connect";

            $headers = "MIME-Version: 1.0\r\n"
                . "Content-Type: text/plain; charset=UTF-8\r\n";

            $sender = trim((string) ini_get("sendmail_from"));

            if ($sender !== "" && filter_var($sender, FILTER_VALIDATE_EMAIL)) {
                $headers .= "From: AgroMarket Connect <" . $sender . ">\r\n";
            }

            $emailSent = mail($email, $subject, $emailMessage, $headers);

            $_SESSION['buyer_message_type'] = $emailSent ? "success" : "error";

            $_SESSION['buyer_message'] = $emailSent
                ? "Registration successful! Please check your email for the activation link."
                : "Registration saved, but the activation email could not be sent. Your account remains pending.";

        } catch (Throwable $e) {

            if ($transactionStarted) {
                mysqli_rollback($link);
            }

            error_log("Buyer registration error: " . $e->getMessage());

            $_SESSION['buyer_message_type'] = "error";

            $_SESSION['buyer_message'] =
            $e instanceof RuntimeException
                ? $e->getMessage()
                : "Registration failed. Please try again.";
        }

    } else {

        $_SESSION['buyer_message_type'] = "error";
        $_SESSION['buyer_message']      = implode("\n", $errors);
    }

    header("Location: buyer_register.php");
    exit;
    }
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Registration | AgroMarket Connect</title>
    <link rel="stylesheet" href="./style/register.css">
</head>

<body>

    <main class="registration-page">

        <section class="registration-container">

            <div class="registration-header">
                <div class="logo">AC</div>
                <h2>Buyer Registration</h2>
                <p>Create your buyer account to explore agricultural products.</p>
            </div>

            <!-- Success message for login -->
            <?php if ($message !== ""): ?>
                <p style="color: <?php echo $messageType === 'success' ? 'green' : 'red' ?>;">
                    <?php echo nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) ?>
                </p>
            <?php endif; ?>

            <form action="" method="POST" class="registration-form">

                <h3>Personal Information</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name"
                        value="<?php echo htmlspecialchars($_POST['first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name"
                        value="<?php echo htmlspecialchars($_POST['first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                        class="form-control"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required>
                    <small id="usernameError" class="field-error"><?php echo htmlspecialchars($fieldErrors['username'] ?? '') ?></small>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                        class="form-control"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required>
                    <small id="emailError" class="field-error"><?php echo htmlspecialchars($fieldErrors['email'] ?? '') ?></small>
                </div>

                <div class="form-group">
                    <label for="mobile">Mobile Number</label>
                    <input type="tel" id="mobile" name="mobile"
                        class="form-control"
                        maxlength="10"
                        value="<?php echo htmlspecialchars($_POST['mobile'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required>
                    <small id="mobileError" class="field-error"><?php echo htmlspecialchars($fieldErrors['mobile'] ?? '') ?></small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password"
                            name="confirm_password" required>
                    </div>
                </div>

                <h3>Buyer Information</h3>

                <div class="form-group">
                    <label for="buyer_type">Buyer Type</label>
                    <select id="buyer_type" name="buyer_type" required>
                        <option value="">Select Buyer Type</option>
                        <option value="individual">Individual</option>
                        <option value="retailer">Retailer</option>
                        <option value="wholesaler">Wholesaler</option>
                        <option value="distributor">Distributor</option>
                        <option value="processor">Food Processing Company</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="business_name">Business / Organization Name (Optional)</label>
                    <input type="text" id="business_name" name="business_name"
                    value="<?php echo htmlspecialchars($_POST['first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="state">State</label>
                        <select id="state" name="state" required>
                            <option value="">Select State</option>
                            <option value="Telangana">Telangana</option>
                            <option value="Andhra Pradesh">Andhra Pradesh</option>
                            <option value="Karnataka">Karnataka</option>
                            <option value="Maharashtra">Maharashtra</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="district">District</label>
                        <input type="text" id="district" name="district"
                        value="<?php echo htmlspecialchars($_POST['first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="city">Village / Town / City</label>
                        <input type="text" id="city" name="city"
                        value="<?php echo htmlspecialchars($_POST['first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="form-group">
                        <label for="pincode">PIN Code</label>
                        <input type="text" id="pincode" name="pincode"
                            maxlength="6" pattern="[0-9]{6}"
                            value="<?php echo htmlspecialchars($_POST['first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Business / Residential Address</label>
                    <textarea id="address" name="address" rows="3" required>
                        <?php echo htmlspecialchars($_POST['address'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </textarea>
                </div>

                <button type="submit" name="register">
                    Create Buyer Account
                </button>

            </form>

            <div class="registration-footer">
                <p>Already registered? <a href="login.php">Login</a></p>
                <a href="register.php">Back to Account Selection</a>
            </div>

        </section>

    </main>
    <script>

const duplicateFields = ["username", "email", "mobile"];

const duplicateStatus = {
    username: false,
    email: false,
    mobile: false
};

const duplicateTimers = {};
const duplicateRequests = {};

function validateDuplicateFormat(field, value) {

    if (value.trim() === "") return false;

    if (field === "email") {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    }

    if (field === "mobile") {
        return /^[6-9][0-9]{9}$/.test(value);
    }

    return value.trim().length >= 3;
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

    input.classList.remove("input-error", "input-valid");
    error.textContent = "";
    duplicateStatus[field] = false;
}

async function checkDuplicate(field) {

    const input = document.getElementById(field);
    const value = input.value.trim();

    if (!validateDuplicateFormat(field, value)) {
        clearFieldError(field);
        return false;
    }

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
            throw new Error("Verification failed");
        }

        const data = await response.json();

        // Ignore outdated responses
        if (
            requestId !== duplicateRequests[field] ||
            input.value.trim() !== value
        ) {
            return false;
        }

        if (data.exists) {

            duplicateStatus[field] = true;
            showFieldError(field, data.message);

            return true;

        } else {

            clearFieldError(field);
            input.classList.add("input-valid");

            return false;
        }

    } catch (error) {

        console.error(error);

        showFieldError(field, "Unable to verify. Please try again.");

        return false;
    }
}

// Live checking while typing
duplicateFields.forEach(function(field) {

    const input = document.getElementById(field);

    input.addEventListener("input", function() {

        clearTimeout(duplicateTimers[field]);

        // Invalidate any previous request
        duplicateRequests[field] =
            (duplicateRequests[field] || 0) + 1;

        duplicateStatus[field] = false;

        input.classList.remove("input-error", "input-valid");
        document.getElementById(field + "Error").textContent = "";

        duplicateTimers[field] = setTimeout(function() {
            checkDuplicate(field);
        }, 400);
    });

    input.addEventListener("blur", function() {

        clearTimeout(duplicateTimers[field]);
        checkDuplicate(field);
    });
});

// Check all three fields before allowing submission

const buyerForm = document.querySelector(".registration-form");

let allowBuyerSubmission = false;

buyerForm.addEventListener("submit", async function(event) {

    // Allow the final submission after duplicate checks
    if (allowBuyerSubmission) {
        return;
    }

    event.preventDefault();

    let hasDuplicate = false;

    for (const field of duplicateFields) {

        clearTimeout(duplicateTimers[field]);

        const duplicate = await checkDuplicate(field);

        if (duplicateStatus[field]) {
            hasDuplicate = true;
        }
    }

    if (hasDuplicate) {

        const firstDuplicate = duplicateFields.find(
            field => duplicateStatus[field]
        );

        document.getElementById(firstDuplicate).focus();

        return;
    }

    // Submit using the actual registration button
    allowBuyerSubmission = true;

    buyerForm.requestSubmit(
        buyerForm.querySelector('button[name="register"]')
    );
});

    </script>
</body>

</html>