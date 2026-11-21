
<?php
    session_start();

    // Prevent the browser from showing an outdated registration form.
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: 0");

    require_once __DIR__ . "/../config/db.php";

    // Retrieve previous form data and messages.
    $fieldErrors  = $_SESSION['buyer_field_errors'] ?? [];
    $oldValues    = $_SESSION['buyer_old_values'] ?? [];
    $popupType    = $_SESSION['buyer_popup_type'] ?? "";
    $popupMessage = $_SESSION['buyer_popup_message'] ?? "";

    unset(
    $_SESSION['buyer_field_errors'],
    $_SESSION['buyer_old_values'],
    $_SESSION['buyer_popup_type'],
    $_SESSION['buyer_popup_message']
    );

    // Restore values after a redirect.
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && ! empty($oldValues)) {
    $_POST = $oldValues;
    }

    // Safely display form values.
    function buyerValue($field)
    {
    return htmlspecialchars(
        $_POST[$field] ?? "",
        ENT_QUOTES,
        "UTF-8"
    );
    }

    // Check whether a select option should be selected.
    function buyerSelected($field, $value)
    {
    return (($_POST[$field] ?? "") === $value) ? "selected" : "";
    }

    // Display a field-specific server validation error.
    function buyerError($field)
    {
    global $fieldErrors;

    return htmlspecialchars(
        $fieldErrors[$field] ?? "",
        ENT_QUOTES,
        "UTF-8"
    );
    }

    // Save entered values, excluding passwords.
    function saveBuyerValues()
    {
    $oldValues = $_POST;

    unset(
        $oldValues['password'],
        $oldValues['confirm_password'],
        $oldValues['register']
    );

    $_SESSION['buyer_old_values'] = $oldValues;
    }

    // Redirect using the Post/Redirect/Get pattern.
    function redirectBuyer()
    {
    header("Location: buyer_register.php");
    exit;
    }

    // ========================================
    // BUYER REGISTRATION BACKEND
    // ========================================

    if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['register'])
    ) {
    $first_name = trim($_POST['first_name'] ?? "");
    $last_name  = trim($_POST['last_name'] ?? "");
    $username   = trim($_POST['username'] ?? "");
    $email      = trim($_POST['email'] ?? "");
    $mobile     = trim($_POST['mobile'] ?? "");

    $password         = $_POST['password'] ?? "";
    $confirm_password = $_POST['confirm_password'] ?? "";

    $business_type = trim($_POST['buyer_type'] ?? "");
    $business_name = trim($_POST['business_name'] ?? "");
    $state         = trim($_POST['state'] ?? "");
    $district      = trim($_POST['district'] ?? "");
    $address       = trim($_POST['address'] ?? "");
    $city          = trim($_POST['city'] ?? "");
    $pincode       = trim($_POST['pincode'] ?? "");

    $errors = [];

    // ------------------------------------
    // SERVER-SIDE VALIDATION
    // ------------------------------------

    if ($first_name === "") {
        $errors['first_name'] = "First name is required.";
    }

    if ($last_name === "") {
        $errors['last_name'] = "Last name is required.";
    }

    if ($username === "") {
        $errors['username'] = "Username is required.";
    } elseif (strlen($username) < 3) {
        $errors['username'] = "Username must contain at least 3 characters.";
    }

    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Enter a valid email address.";
    }

    if (! preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
        $errors['mobile'] = "Enter a valid 10-digit Indian mobile number.";
    }

    if (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[^A-Za-z0-9]/', $password)
    ) {
        $errors['password'] = "Password must contain at least 8 characters, including uppercase, lowercase, number and special character.";
    }

    if ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    $allowedTypes = [
        "individual",
        "retailer",
        "wholesaler",
        "distributor",
        "processor",
    ];

    if (! in_array($business_type, $allowedTypes, true)) {
        $errors['buyer_type'] = "Select a valid buyer type.";
    }

    $allowedStates = [
        "Telangana",
        "Andhra Pradesh",
        "Karnataka",
        "Maharashtra",
    ];

    if (! in_array($state, $allowedStates, true)) {
        $errors['state'] = "Select a valid state.";
    }

    if ($district === "") {
        $errors['district'] = "District is required.";
    }

    if ($address === "") {
        $errors['address'] = "Address is required.";
    }

    // PIN code is optional, but must contain six digits if entered.
    if (
        $pincode !== "" &&
        ! preg_match('/^[0-9]{6}$/', $pincode)
    ) {
        $errors['pincode'] = "Enter a valid 6-digit PIN code.";
    }

    // ------------------------------------
    // HANDLE VALIDATION ERRORS
    // ------------------------------------

    if (! empty($errors)) {
        $_SESSION['buyer_field_errors']  = $errors;
        $_SESSION['buyer_popup_type']    = "error";
        $_SESSION['buyer_popup_message'] =
            "Please correct the highlighted fields and try again.";

        saveBuyerValues();
        redirectBuyer();
    }

    // ------------------------------------
    // CHECK DUPLICATE USER DETAILS
    // ------------------------------------

    $duplicateChecks = [
        "username" => [
            $username,
            "This username is already registered.",
        ],
        "email"    => [
            $email,
            "This email address is already registered.",
        ],
        "mobile"   => [
            $mobile,
            "This mobile number is already registered.",
        ],
    ];

    try {
        foreach ($duplicateChecks as $column => $details) {

            [$value, $errorMessage] = $details;

            // Column names come only from this fixed allowlist.
            $sql = "SELECT user_id FROM users
                    WHERE `$column` = ? LIMIT 1";

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

                $_SESSION['buyer_popup_type']    = "error";
                $_SESSION['buyer_popup_message'] = $errorMessage;

                saveBuyerValues();
                redirectBuyer();
            }
        }

        // --------------------------------
        // PREPARE ACCOUNT DETAILS
        // --------------------------------

        $full_name = $first_name . " " . $last_name;

        $password_hash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $token = bin2hex(random_bytes(32));

        $role           = "buyer";
        $account_status = "pending";

        $business_name = $business_name === ""
            ? null
            : $business_name;

        $city = $city === ""
            ? null
            : $city;

        $pincode = $pincode === ""
            ? null
            : $pincode;

        // --------------------------------
        // DATABASE TRANSACTION
        // --------------------------------

        $transactionStarted = false;

        try {
            mysqli_begin_transaction($link);
            $transactionStarted = true;

            // Insert into users table.
            $userSql = "INSERT INTO users
                (
                    role,
                    full_name,
                    username,
                    email,
                    mobile,
                    password_hash,
                    account_status,
                    token
                )
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

            // Insert into buyer_profiles table.
            $profileSql = "INSERT INTO buyer_profiles
                (
                    buyer_id,
                    business_name,
                    business_type,
                    business_address,
                    delivery_address,
                    village_city,
                    district,
                    state,
                    pincode
                )
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

            // Save both records together.
            mysqli_commit($link);
            $transactionStarted = false;

        } catch (Throwable $e) {

            if ($transactionStarted) {
                mysqli_rollback($link);
            }

            throw $e;
        }

        // --------------------------------
        // GENERATE ACTIVATION LINK
        // --------------------------------

        $scheme = (
            (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        ) ? "https" : "http";

        $basePath = rtrim(
            str_replace(
                '\\',
                '/',
                dirname($_SERVER['SCRIPT_NAME'])
            ),
            '/'
        );

        $activationLink = $scheme . "://" .
        $_SERVER['HTTP_HOST'] .
        $basePath .
        "/activate.php?uid=" .
        urlencode((string) $buyer_id) .
        "&token=" .
        urlencode($token);

        // --------------------------------
        // SEND ACTIVATION EMAIL
        // --------------------------------

        $subject = "Activate your AgroMarket Connect buyer account";

        $emailMessage =
            "Hello " . $full_name . ",\r\n\r\n" .
            "Thank you for registering with AgroMarket Connect.\r\n\r\n" .
            "Your User ID: " . $buyer_id . "\r\n\r\n" .
            "Activate your account using this link:\r\n" .
            $activationLink . "\r\n\r\n" .
            "Regards,\r\nAgroMarket Connect";

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        $sender = trim((string) ini_get("sendmail_from"));

        if (
            $sender !== "" &&
            filter_var($sender, FILTER_VALIDATE_EMAIL)
        ) {
            $headers .= "From: AgroMarket Connect <" .
                $sender . ">\r\n";
        }

        $emailSent = mail(
            $email,
            $subject,
            $emailMessage,
            $headers
        );

        if ($emailSent) {
            $_SESSION['buyer_popup_type']    = "success";
            $_SESSION['buyer_popup_message'] =
                "Registration successful! Please check your email for the activation link.";
        } else {
            $_SESSION['buyer_popup_type']    = "error";
            $_SESSION['buyer_popup_message'] =
                "Registration was saved, but the activation email could not be sent. Your account remains pending.";
        }

    } catch (Throwable $e) {

        error_log(
            "Buyer registration error: " . $e->getMessage()
        );

        $_SESSION['buyer_popup_type'] = "error";

        if (
            $e instanceof mysqli_sql_exception &&
            (int) $e->getCode() === 1062
        ) {
            $_SESSION['buyer_popup_message'] =
                "These account details are already registered. Please check your username, email, and mobile number.";
        } else {
            $_SESSION['buyer_popup_message'] =
                "Registration failed. Please try again.";
        }

        saveBuyerValues();
    }

    redirectBuyer();
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

            <p>
                Create your buyer account to explore agricultural products.
            </p>
        </div>

        <form
            action=""
            method="POST"
            class="registration-form"
            novalidate
        >

            <h3>Personal Information</h3>

            <!-- First Name and Last Name -->
            <div class="form-row">

                <div class="form-group">
                    <label for="first_name">First Name</label>

                    <input
                        type="text"
                        id="first_name"
                        name="first_name"
                        class="form-control"
                        value="<?php echo buyerValue('first_name'); ?>"
                    >

                    <small class="field-error" id="first_nameError">
                        <?php echo buyerError('first_name'); ?>
                    </small>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>

                    <input
                        type="text"
                        id="last_name"
                        name="last_name"
                        class="form-control"
                        value="<?php echo buyerValue('last_name'); ?>"
                    >

                    <small class="field-error" id="last_nameError">
                        <?php echo buyerError('last_name'); ?>
                    </small>
                </div>

            </div>

            <!-- Username -->
            <div class="form-group">
                <label for="username">Username</label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    class="form-control"
                    value="<?php echo buyerValue('username'); ?>"
                >

                <small class="field-error" id="usernameError">
                    <?php echo buyerError('username'); ?>
                </small>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email Address</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    value="<?php echo buyerValue('email'); ?>"
                >

                <small class="field-error" id="emailError">
                    <?php echo buyerError('email'); ?>
                </small>
            </div>

            <!-- Mobile -->
            <div class="form-group">
                <label for="mobile">Mobile Number</label>

                <input
                    type="tel"
                    id="mobile"
                    name="mobile"
                    class="form-control"
                    maxlength="10"
                    inputmode="numeric"
                    value="<?php echo buyerValue('mobile'); ?>"
                >

                <small class="field-error" id="mobileError">
                    <?php echo buyerError('mobile'); ?>
                </small>
            </div>

            <!-- Passwords -->
            <div class="form-row">

                <div class="form-group">
                    <label for="password">Password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                    >

                    <small class="field-error" id="passwordError">
                        <?php echo buyerError('password'); ?>
                    </small>
                    <div class="password-feedback">
                        <div class="password-strength">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>

                        <p id="strengthText">Password strength: Not entered</p>

                        <ul class="password-requirements">
                            <li id="lengthRule">✗ At least 8 characters</li>
                            <li id="upperRule">✗ One uppercase letter</li>
                            <li id="lowerRule">✗ One lowercase letter</li>
                            <li id="numberRule">✗ One number</li>
                            <li id="specialRule">✗ One special character</li>
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="form-control"
                    >

                    <small class="field-error" id="confirm_passwordError">
                        <?php echo buyerError('confirm_password'); ?>
                    </small>
                </div>

            </div>

            <h3>Buyer Information</h3>

            <!-- Buyer Type -->
            <div class="form-group">
                <label for="buyer_type">Buyer Type</label>

                <select
                    id="buyer_type"
                    name="buyer_type"
                    class="form-control"
                >
                    <option value="">Select Buyer Type</option>

                    <option value="individual"
                        <?php echo buyerSelected('buyer_type', 'individual'); ?>>
                        Individual
                    </option>

                    <option value="retailer"
                        <?php echo buyerSelected('buyer_type', 'retailer'); ?>>
                        Retailer
                    </option>

                    <option value="wholesaler"
                        <?php echo buyerSelected('buyer_type', 'wholesaler'); ?>>
                        Wholesaler
                    </option>

                    <option value="distributor"
                        <?php echo buyerSelected('buyer_type', 'distributor'); ?>>
                        Distributor
                    </option>

                    <option value="processor"
                        <?php echo buyerSelected('buyer_type', 'processor'); ?>>
                        Food Processing Company
                    </option>
                </select>

                <small class="field-error" id="buyer_typeError">
                    <?php echo buyerError('buyer_type'); ?>
                </small>
            </div>

            <!-- Business Name -->
            <div class="form-group">
                <label for="business_name">
                    Business / Organization Name (Optional)
                </label>

                <input
                    type="text"
                    id="business_name"
                    name="business_name"
                    class="form-control"
                    value="<?php echo buyerValue('business_name'); ?>"
                >

                <small class="field-error" id="business_nameError"></small>
            </div>

            <!-- State and District -->
            <div class="form-row">

                <div class="form-group">
                    <label for="state">State</label>

                    <select
                        id="state"
                        name="state"
                        class="form-control"
                    >
                        <option value="">Select State</option>

                        <option value="Telangana"
                            <?php echo buyerSelected('state', 'Telangana'); ?>>
                            Telangana
                        </option>

                        <option value="Andhra Pradesh"
                            <?php echo buyerSelected('state', 'Andhra Pradesh'); ?>>
                            Andhra Pradesh
                        </option>

                        <option value="Karnataka"
                            <?php echo buyerSelected('state', 'Karnataka'); ?>>
                            Karnataka
                        </option>

                        <option value="Maharashtra"
                            <?php echo buyerSelected('state', 'Maharashtra'); ?>>
                            Maharashtra
                        </option>
                    </select>

                    <small class="field-error" id="stateError">
                        <?php echo buyerError('state'); ?>
                    </small>
                </div>

                <div class="form-group">
                    <label for="district">District</label>

                    <input
                        type="text"
                        id="district"
                        name="district"
                        class="form-control"
                        value="<?php echo buyerValue('district'); ?>"
                    >

                    <small class="field-error" id="districtError">
                        <?php echo buyerError('district'); ?>
                    </small>
                </div>

            </div>

            <!-- City and PIN -->
            <div class="form-row">

                <div class="form-group">
                    <label for="city">Village / Town / City</label>

                    <input
                        type="text"
                        id="city"
                        name="city"
                        class="form-control"
                        value="<?php echo buyerValue('city'); ?>"
                    >

                    <small class="field-error" id="cityError"></small>
                </div>

                <div class="form-group">
                    <label for="pincode">PIN Code (Optional)</label>

                    <input
                        type="text"
                        id="pincode"
                        name="pincode"
                        class="form-control"
                        maxlength="6"
                        inputmode="numeric"
                        value="<?php echo buyerValue('pincode'); ?>"
                    >

                    <small class="field-error" id="pincodeError">
                        <?php echo buyerError('pincode'); ?>
                    </small>
                </div>

            </div>

            <!-- Address -->
            <div class="form-group">
                <label for="address">
                    Business / Residential Address
                </label>

                <textarea
                    id="address"
                    name="address"
                    class="form-control"
                    rows="3"
                ><?php echo buyerValue('address'); ?></textarea>

                <small class="field-error" id="addressError">
                    <?php echo buyerError('address'); ?>
                </small>
            </div>

            <button type="submit" name="register">
                Create Buyer Account
            </button>

        </form>

        <div class="registration-footer">
            <p>
                Already registered?
                <a href="login.php">Login</a>
            </p>

            <a href="register.php">Back to Account Selection</a>
        </div>

    </section>

</main>


<!-- Registration Popup -->

<div id="popupCard" class="popup-overlay">

    <div class="popup-card">

        <div id="popupIcon" class="popup-icon"></div>

        <h2 id="popupTitle"></h2>

        <p id="popupText"></p>

        <button type="button" onclick="closePopup()">
            OK
        </button>

    </div>

</div>


<script>
const popup = document.getElementById("popupCard");
const popupCard = document.querySelector(".popup-card");
const buyerForm = document.querySelector(".registration-form");
let currentPopupType = "";

function showPopup(type, message) {
    currentPopupType = type;
    popup.style.display = "flex";
    document.getElementById("popupIcon").textContent = type === "success" ? "✓" : "✕";
    document.getElementById("popupTitle").textContent = type === "success" ? "Success!" : "Registration Failed";
    document.getElementById("popupText").textContent = message;
    document.getElementById("popupText").style.whiteSpace = "pre-line";
    popupCard.classList.toggle("error", type !== "success");
}
function closePopup() {
    popup.style.display = "none";
    if (currentPopupType === "success") window.location.href = "../index.php";
}
<?php if ($popupMessage !== ""): ?>
showPopup(<?php echo json_encode($popupType); ?>,
<?php echo json_encode($popupMessage, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>);
<?php endif; ?>

const duplicateFields = ["username", "email", "mobile"];
const duplicateStatus = {username:false, email:false, mobile:false};
const verificationFailed = {username:false, email:false, mobile:false};
const duplicateTimers = {}, duplicateRequests = {};
const touched = new Set();
const fields = ["first_name","last_name","username","email","mobile","password",
"confirm_password","buyer_type","business_name","state","district","city","pincode","address"];

function errorNode(id) {
    const el = document.getElementById(id);
    if (!el) return null;
    let node = document.getElementById(id+"Error");
    if (!node) {
        node = document.createElement("small");
        node.id = id+"Error"; node.className = "field-error";
        el.insertAdjacentElement("afterend", node);
    }
    return node;
}
function showError(id,msg) {
    const el=document.getElementById(id), node=errorNode(id);
    if (!el || !node) return;
    el.classList.add("input-error"); el.classList.remove("input-valid");
    node.textContent=msg;
}
function clearError(id,valid=false) {
    const el=document.getElementById(id), node=errorNode(id);
    if (!el || !node) return;
    el.classList.remove("input-error","input-valid");
    if(valid && el.value.trim()) el.classList.add("input-valid");
    node.textContent="";
}
function rules(p) {
    return [p.length>=8,/[A-Z]/.test(p),/[a-z]/.test(p),/[0-9]/.test(p),/[^A-Za-z0-9]/.test(p)];
}
function updatePassword() {
    const passwordInput = document.getElementById("password");
    if (!passwordInput) return false;

    const p = passwordInput.value;
    const passed = rules(p);
    const ids = ["lengthRule", "upperRule", "lowerRule", "numberRule", "specialRule"];

    ids.forEach((id, i) => {
        const li = document.getElementById(id);
        if (!li) return;

        const label = li.dataset.label || li.textContent.replace(/^[✓✗]\s*/, "");
        li.dataset.label = label;
        li.textContent = (passed[i] ? "✓ " : "✗ ") + label;
        li.classList.toggle("valid", passed[i]);
        li.classList.toggle("invalid", !passed[i]);
        // Apply the colour directly as a fallback if another stylesheet overrides the classes.
        li.style.color = passed[i] ? "#198754" : "#dc3545";
    });

    const bar = document.getElementById("strengthBar");
    const txt = document.getElementById("strengthText");
    if (bar && txt) {
        const count = passed.filter(Boolean).length;
        const levels = [
            ["Very weak", "20%", "#dc3545"],
            ["Weak", "40%", "#fd7e14"],
            ["Medium", "60%", "#ffc107"],
            ["Strong", "80%", "#0d6efd"],
            ["Very strong", "100%", "#198754"]
        ];
        if (!p) {
            bar.style.width = "0";
            txt.textContent = "Password strength: Not entered";
        } else {
            bar.style.width = levels[count - 1][1];
            bar.style.background = levels[count - 1][2];
            txt.textContent = "Password strength: " + levels[count - 1][0];
        }
    }
    return passed.every(Boolean);
}
function validateField(id) {
    const el=document.getElementById(id); if(!el) return true;
    const v=el.value.trim(); let msg="";
    if(["first_name","last_name","district","address"].includes(id)&&!v) msg="This field is required.";
    if(id==="username"&&!v) msg="Username is required.";
    else if(id==="username"&&v.length<3) msg="Username must contain at least 3 characters.";
    if(id==="email"&&!v) msg="Email address is required.";
    else if(id==="email"&&!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) msg="Enter a valid email address.";
    if(id==="mobile"&&!v) msg="Mobile number is required.";
    else if(id==="mobile"&&!/^[6-9][0-9]{9}$/.test(v)) msg="Enter a valid 10-digit Indian mobile number.";
    if(id==="password"&&!el.value) msg="Password is required.";
    else if(id==="password"&&!rules(el.value).every(Boolean)) msg="Password does not meet all the requirements below.";
    if(id==="confirm_password"&&!el.value) msg="Please confirm your password.";
    else if(id==="confirm_password"&&el.value!==document.getElementById("password").value) msg="Passwords do not match.";
    if(["buyer_type","state"].includes(id)&&!v) msg="Please select an option.";
    if(id==="pincode"&&v&&!/^[0-9]{6}$/.test(v)) msg="Enter a valid 6-digit PIN code.";
    if(msg){showError(id,msg);return false;}
    clearError(id,!!v); return true;
}
function duplicateFormat(id,v) {
    if(id==="username") return v.length>=3;
    if(id==="email") return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
    if(id==="mobile") return /^[6-9][0-9]{9}$/.test(v);
    return false;
}
async function checkDuplicate(id) {
    const el=document.getElementById(id), value=el.value.trim();
    if(!duplicateFormat(id,value)) return false;
    const request=duplicateRequests[id]=(duplicateRequests[id]||0)+1;
    const fd=new FormData(); fd.append("field",id); fd.append("value",value);
    try {
        const response=await fetch("check_duplicate.php",{method:"POST",body:fd});
        if(!response.ok) throw new Error("Verification failed");
        const data=await response.json();
        if(request!==duplicateRequests[id]||el.value.trim()!==value) return null;
        verificationFailed[id]=false; duplicateStatus[id]=!!data.exists;
        if(data.exists){showError(id,data.message||"This value is already registered.");return true;}
        clearError(id,true); return false;
    } catch(e) {
        if(request===duplicateRequests[id]&&el.value.trim()===value){
            verificationFailed[id]=true; showError(id,"Unable to verify this value. Please try again.");
        }
        return true;
    }
}
fields.forEach(id=>{
    const el=document.getElementById(id); if(!el)return;
    el.addEventListener("blur",()=>{
        touched.add(id); validateField(id);
        if(duplicateFields.includes(id)&&duplicateFormat(id,el.value.trim())) checkDuplicate(id);
    });
    el.addEventListener("input",()=>{
        if(touched.has(id)) validateField(id);
        if(id==="password"){updatePassword();if(touched.has("confirm_password"))validateField("confirm_password");}
        if(duplicateFields.includes(id)){
            clearTimeout(duplicateTimers[id]); duplicateRequests[id]=(duplicateRequests[id]||0)+1;
            duplicateStatus[id]=false; verificationFailed[id]=false;
            if(duplicateFormat(id,el.value.trim())) duplicateTimers[id]=setTimeout(()=>checkDuplicate(id),450);
        }
    });
    el.addEventListener("change",()=>{touched.add(id);validateField(id);});
});
updatePassword();

let allowSubmit=false;
buyerForm.addEventListener("submit",async event=>{
    if(allowSubmit)return;
    event.preventDefault();
    let first=null;
    fields.forEach(id=>{touched.add(id);if(!validateField(id)&&!first)first=id;});
    updatePassword();
    if(first){document.getElementById(first).focus();showPopup("error","Please correct the highlighted fields before submitting.");return;}
    duplicateFields.forEach(id=>clearTimeout(duplicateTimers[id]));
    const results=await Promise.all(duplicateFields.map(checkDuplicate));
    const issue=duplicateFields.find(id=>duplicateStatus[id]||verificationFailed[id]);
    if(issue||results.some(x=>x!==false)){
        const id=issue||duplicateFields[0];document.getElementById(id).focus();
        showPopup("error","Please use a username, email address and mobile number that are not already registered.");return;
    }
    allowSubmit=true;buyerForm.requestSubmit(buyerForm.querySelector('button[name="register"]'));
});
window.addEventListener("pageshow",event=>{
    const nav=performance.getEntriesByType("navigation")[0];
    if(event.persisted||(nav&&nav.type==="back_forward")){
        buyerForm.reset();touched.clear();
        duplicateFields.forEach(id=>{duplicateStatus[id]=false;verificationFailed[id]=false;duplicateRequests[id]=(duplicateRequests[id]||0)+1;clearTimeout(duplicateTimers[id]);clearError(id);});
        fields.filter(id=>!duplicateFields.includes(id)).forEach(id=>clearError(id));
        document.querySelectorAll(".field-error").forEach(n=>n.textContent="");
        popup.style.display="none";allowSubmit=false;updatePassword();
    }
});
</script>

</body>
</html>
