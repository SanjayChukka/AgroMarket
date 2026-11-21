<?php
    if (session_status() === PHP_SESSION_NONE) {
    session_start();
    }

    require_once __DIR__ . "/../config/auth_check.php";
    require_login("farmer", "../auth/login.php");

    require_once __DIR__ . "/../config/db.php";

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    function e($value)
    {
    return htmlspecialchars((string) ($value ?? ""), ENT_QUOTES, "UTF-8");
    }

    $farmerId = (int) $_SESSION['user_id'];

    if (empty($_SESSION['profile_csrf'])) {
    $_SESSION['profile_csrf'] = bin2hex(random_bytes(32));
    }

    $csrfToken      = $_SESSION['profile_csrf'];
    $errors         = [];
    $successMessage = "";

    /* Fetch existing profile information */
    $sql = "SELECT
            u.full_name,
            u.email,
            u.mobile,
            fp.dob,
            fp.gender,
            fp.farm_name,
            fp.land_size,
            fp.primary_crops,
            fp.experience,
            fp.address_line,
            fp.village,
            fp.district,
            fp.state,
            fp.pincode,
            fp.profile_image
        FROM users u
        LEFT JOIN farmer_profiles fp
            ON u.user_id = fp.farmer_id
        WHERE u.user_id = ? AND u.role = 'farmer'
        LIMIT 1";

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "i", $farmerId);
    mysqli_stmt_execute($stmt);

    $result  = mysqli_stmt_get_result($stmt);
    $profile = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if (! $profile) {
    die("Farmer profile not found.");
    }

    /* Default form values */
    $form = [
    'full_name'     => $profile['full_name'],
    'email'         => $profile['email'],
    'mobile'        => $profile['mobile'],
    'dob'           => $profile['dob'],
    'gender'        => $profile['gender'],
    'farm_name'     => $profile['farm_name'],
    'farm_size'     => $profile['land_size'],
    'primary_crops' => $profile['primary_crops'],
    'experience'    => $profile['experience'],
    'address'       => $profile['address_line'],
    'village'       => $profile['village'],
    'district'      => $profile['district'],
    'state'         => $profile['state'],
    'pincode'       => $profile['pincode'],
    ];

    $currentImage = $profile['profile_image'];

    /* Display success message after redirect */
    if (! empty($_SESSION['profile_flash'])) {
    $successMessage = $_SESSION['profile_flash'];
    unset($_SESSION['profile_flash']);
    }

    /* Process profile update */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* Preserve entered values */
    foreach ($form as $key => $value) {
        if (isset($_POST[$key]) && ! is_array($_POST[$key])) {
            $form[$key] = trim($_POST[$key]);
        }
    }

    /* CSRF validation */
    $submittedToken = $_POST['csrf_token'] ?? "";

    if (
        ! is_string($submittedToken) ||
        ! hash_equals($csrfToken, $submittedToken)
    ) {
        $errors[] = "Invalid form submission. Please refresh the page and try again.";
    }

    /* Personal information */
    $fullName = $form['full_name'];
    $email    = $form['email'];
    $mobile   = preg_replace('/[\s()-]/', '', $form['mobile']);

    if ($fullName === "" || strlen($fullName) > 100) {
        $errors[] = "Please enter a valid full name (maximum 100 characters).";
    }

    if ($email !== "" && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if ($mobile !== "" && ! preg_match('/^\+?[0-9]{10,15}$/', $mobile)) {
        $errors[] = "Please enter a valid mobile number.";
    }

    if ($email === "" && $mobile === "") {
        $errors[] = "Please provide at least an email address or mobile number.";
    }

    /* Date of birth */
    $dob = $form['dob'];

    if ($dob !== "") {
        $date = DateTime::createFromFormat('Y-m-d', $dob);

        if (
            ! $date ||
            $date->format('Y-m-d') !== $dob ||
            $dob > date('Y-m-d')
        ) {
            $errors[] = "Please enter a valid date of birth.";
        }
    } else {
        $dob = null;
    }

    /* Gender */
    $gender = $form['gender'];

    if (! in_array($gender, ["", "Male", "Female", "Other"], true)) {
        $errors[] = "Please select a valid gender.";
    }

    if ($gender === "") {
        $gender = null;
    }

    /* Farm information */
    $farmName     = $form['farm_name'];
    $farmSize     = $form['farm_size'];
    $primaryCrops = $form['primary_crops'];
    $experience   = $form['experience'];

    if (strlen($farmName) > 150) {
        $errors[] = "Farm name cannot exceed 150 characters.";
    }

    if ($farmSize !== "" && (! is_numeric($farmSize) || $farmSize < 0 || $farmSize > 99999999.99)) {
        $errors[] = "Please enter a valid farm size.";
    }

    if ($farmSize === "") {
        $farmSize = null;
    }

    if ($experience !== "" && (! ctype_digit($experience) || (int) $experience > 100)) {
        $errors[] = "Please enter a valid farming experience between 0 and 100 years.";
    }

    if ($experience === "") {
        $experience = null;
    }

    /* Address information */
    $address  = $form['address'];
    $village  = $form['village'];
    $district = $form['district'];
    $state    = $form['state'];
    $pincode  = $form['pincode'];

    if ($address === "" || strlen($address) > 255) {
        $errors[] = "Please enter your address (maximum 255 characters).";
    }

    if ($village === "" || strlen($village) > 100) {
        $errors[] = "Please enter a valid village or town.";
    }

    if ($district === "" || strlen($district) > 100) {
        $errors[] = "Please enter a valid district.";
    }

    if ($state === "" || strlen($state) > 100) {
        $errors[] = "Please enter a valid state.";
    }

    if (! preg_match('/^[0-9]{4,10}$/', $pincode)) {
        $errors[] = "Please enter a valid PIN code.";
    }

    /* Check whether email or mobile belongs to another account */
    if (! $errors && $email !== "") {
        $check = mysqli_prepare(
            $link,
            "SELECT user_id FROM users WHERE email = ? AND user_id <> ? LIMIT 1"
        );

        mysqli_stmt_bind_param($check, "si", $email, $farmerId);
        mysqli_stmt_execute($check);

        if (mysqli_stmt_get_result($check)->num_rows > 0) {
            $errors[] = "This email address is already registered with another account.";
        }

        mysqli_stmt_close($check);
    }

    if (! $errors && $mobile !== "") {
        $check = mysqli_prepare(
            $link,
            "SELECT user_id FROM users WHERE mobile = ? AND user_id <> ? LIMIT 1"
        );

        mysqli_stmt_bind_param($check, "si", $mobile, $farmerId);
        mysqli_stmt_execute($check);

        if (mysqli_stmt_get_result($check)->num_rows > 0) {
            $errors[] = "This mobile number is already registered with another account.";
        }

        mysqli_stmt_close($check);
    }

    /* Profile picture validation */
    $newImagePath         = null;
    $newImagePhysicalPath = null;

    if (
        isset($_FILES['profile_picture']) &&
        $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE
    ) {
        $file = $_FILES['profile_picture'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "There was a problem uploading your profile picture.";
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = "Profile picture must be smaller than 5 MB.";
        } elseif (! is_uploaded_file($file['tmp_name'])) {
            $errors[] = "Invalid profile picture upload.";
        } else {
            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
            ];

            $imageInfo = @getimagesize($file['tmp_name']);

            if (
                ! isset($allowedTypes[$mimeType]) ||
                $imageInfo === false ||
                $imageInfo['mime'] !== $mimeType
            ) {
                $errors[] = "Only JPG, PNG and WEBP images are allowed.";
            } else {
                $extension = $allowedTypes[$mimeType];
                $fileName  = bin2hex(random_bytes(16)) . "." . $extension;

                $uploadDirectory = __DIR__ . "/../uploads/profile_images/";

                if (
                    ! is_dir($uploadDirectory) &&
                    ! mkdir($uploadDirectory, 0755, true)
                ) {
                    $errors[] = "Unable to create the profile image directory.";
                } else {
                    $newImagePhysicalPath = $uploadDirectory . $fileName;
                    $newImagePath         = "uploads/profile_images/" . $fileName;
                }
            }
        }
    }

    /* Save everything only when validation succeeds */
    if (! $errors) {

        $imagePath = $newImagePath ?? $currentImage;
        $oldImage  = $currentImage;

        try {
            /* Move the new image before saving its path */
            if ($newImagePhysicalPath !== null) {
                if (! move_uploaded_file($_FILES['profile_picture']['tmp_name'], $newImagePhysicalPath)) {
                    throw new RuntimeException("Unable to save the uploaded image.");
                }
            }

            mysqli_begin_transaction($link);

            /* Update users table */
            $userUpdate = mysqli_prepare(
                $link,
                "UPDATE users
                 SET full_name = ?, email = ?, mobile = ?
                 WHERE user_id = ? AND role = 'farmer'"
            );

            $emailValue  = $email === "" ? null : $email;
            $mobileValue = $mobile === "" ? null : $mobile;

            mysqli_stmt_bind_param(
                $userUpdate,
                "sssi",
                $fullName,
                $emailValue,
                $mobileValue,
                $farmerId
            );

            mysqli_stmt_execute($userUpdate);
            mysqli_stmt_close($userUpdate);

            /* Insert profile if missing, otherwise update it */
            $profileUpdate = mysqli_prepare(
                $link,
                "INSERT INTO farmer_profiles (
                    farmer_id,
                    farm_name,
                    land_size,
                    primary_crops,
                    experience,
                    dob,
                    gender,
                    address_line,
                    village,
                    district,
                    state,
                    pincode,
                    profile_image
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    farm_name = VALUES(farm_name),
                    land_size = VALUES(land_size),
                    primary_crops = VALUES(primary_crops),
                    experience = VALUES(experience),
                    dob = VALUES(dob),
                    gender = VALUES(gender),
                    address_line = VALUES(address_line),
                    village = VALUES(village),
                    district = VALUES(district),
                    state = VALUES(state),
                    pincode = VALUES(pincode),
                    profile_image = VALUES(profile_image)"
            );

            $profileTypes = "i" . str_repeat("s", 12);

            mysqli_stmt_bind_param(
                $profileUpdate,
                $profileTypes,
                $farmerId,
                $farmName,
                $farmSize,
                $primaryCrops,
                $experience,
                $dob,
                $gender,
                $address,
                $village,
                $district,
                $state,
                $pincode,
                $imagePath
            );

            mysqli_stmt_execute($profileUpdate);
            mysqli_stmt_close($profileUpdate);

            mysqli_commit($link);

            /* Remove previous image only after successful database update */
            if (
                $newImagePath !== null &&
                ! empty($oldImage) &&
                strpos($oldImage, "uploads/profile_images/") === 0
            ) {
                $oldImagePhysicalPath = __DIR__ . "/../" . $oldImage;

                if (is_file($oldImagePhysicalPath)) {
                    unlink($oldImagePhysicalPath);
                }
            }

            $_SESSION['profile_flash'] = "Your profile has been updated successfully.";

            header("Location: profile.php");
            exit;

        } catch (Throwable $exception) {

            mysqli_rollback($link);

            if (
                $newImagePhysicalPath !== null &&
                is_file($newImagePhysicalPath)
            ) {
                unlink($newImagePhysicalPath);
            }

            error_log("Farmer profile update error: " . $exception->getMessage());

            $errors[] = "Unable to update your profile right now. Please try again.";
        }
    }
    }

    /* Prepare profile image and avatar initial */
    $profileImageUrl = ! empty($currentImage)
    ? "../" . $currentImage
    : "";

    $profileInitial = strtoupper(substr($form['full_name'] ?: "F", 0, 1));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Profile | AgroMarket Connect</title>
    <link rel="stylesheet" href="farmer.css">
</head>

<body>

    <header class="farmer-header">
        <div class="header-left">
            <h2>AgroMarket Connect</h2>
        </div>

        <nav class="farmer-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="profile.php">My Profile</a>
            <a href="manage_listings.php">My Listings</a>
            <a href="orders.php">Orders</a>
        </nav>

        <div class="header-right">
            <a href="../auth/logout.php" class="logout-link">Logout</a>

            <div class="profile-menu">
                <button type="button" class="profile-avatar"
                    id="profileToggle" aria-label="Open profile details"
                    aria-expanded="false">
                    <?php echo e($profileInitial) ?>
                </button>

                <div class="profile-popup" id="profilePopup">
                    <div class="popup-avatar">
                        <?php echo e($profileInitial) ?>
                    </div>

                    <h3><?php echo e($form['full_name']) ?></h3>
                    <p><?php echo e($form['email']) ?></p>
                    <p><?php echo e($form['mobile']) ?></p>
                    <a href="profile.php">View Profile</a>
                </div>
            </div>
        </div>
    </header>

    <main class="profile-container">

        <h1>My Farmer Profile</h1>
        <p>View and manage your personal and farm information.</p>

        <?php if ($successMessage !== ""): ?>
            <p role="status"><?php echo e($successMessage) ?></p>
        <?php endif; ?>

        <?php if (! empty($errors)): ?>
            <div role="alert">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="profile.php" method="POST" enctype="multipart/form-data">

            <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken) ?>">

            <section class="profile-picture-section">
                <h2>Profile Picture</h2>

                <div class="profile-picture-preview">
                    <img id="profilePreview"
                        alt="Profile picture preview"
                        src="<?php echo e($profileImageUrl) ?>"
                        <?php echo $profileImageUrl === "" ? "hidden" : "" ?>>

                    <span id="profileInitial"
                        <?php echo $profileImageUrl !== "" ? "hidden" : "" ?>>
                        <?php echo e($profileInitial) ?>
                    </span>
                </div>

                <label for="profilePicture">Upload Profile Picture</label>
                <input type="file"
                    id="profilePicture"
                    name="profile_picture"
                    accept="image/jpeg,image/png,image/webp">

                <p>Accepted formats: JPG, PNG and WEBP. Maximum size: 5 MB.</p>
            </section>

            <section>
                <h2>Personal Information</h2>

                <label for="fullName">Full Name</label>
                <input type="text" id="fullName"
                    name="full_name"
                    value="<?php echo e($form['full_name']) ?>"
                    maxlength="100"
                    placeholder="Enter your full name"
                    required>

                <label for="email">Email Address</label>
                <input type="email" id="email"
                    name="email"
                    value="<?php echo e($form['email']) ?>"
                    maxlength="150"
                    placeholder="Enter your email">

                <label for="mobile">Mobile Number</label>
                <input type="tel" id="mobile"
                    name="mobile"
                    value="<?php echo e($form['mobile']) ?>"
                    maxlength="15"
                    placeholder="Enter your mobile number">

                <label for="dob">Date of Birth</label>
                <input type="date" id="dob"
                    name="dob"
                    value="<?php echo e($form['dob']) ?>"
                    max="<?php echo date('Y-m-d') ?>">

                <label for="gender">Gender</label>
                <select id="gender" name="gender">
                    <option value="">Select Gender</option>
                    <option value="Male" <?php echo $form['gender'] === "Male" ? "selected" : "" ?>>Male</option>
                    <option value="Female" <?php echo $form['gender'] === "Female" ? "selected" : "" ?>>Female</option>
                    <option value="Other" <?php echo $form['gender'] === "Other" ? "selected" : "" ?>>Other</option>
                </select>
            </section>

            <section>
                <h2>Farm Information</h2>

                <label for="farmName">Farm Name</label>
                <input type="text" id="farmName"
                    name="farm_name"
                    value="<?php echo e($form['farm_name']) ?>"
                    maxlength="150"
                    placeholder="Enter farm name">

                <label for="farmSize">Farm Size (Acres)</label>
                <input type="number" id="farmSize"
                    name="farm_size"
                    value="<?php echo e($form['farm_size']) ?>"
                    min="0" step="0.01"
                    placeholder="Enter farm size">

                <label for="primaryCrops">Primary Crops</label>
                <input type="text" id="primaryCrops"
                    name="primary_crops"
                    value="<?php echo e($form['primary_crops']) ?>"
                    placeholder="Enter crops you grow">

                <label for="experience">Farming Experience (Years)</label>
                <input type="number" id="experience"
                    name="experience"
                    value="<?php echo e($form['experience']) ?>"
                    min="0" max="100" step="1"
                    placeholder="Enter experience">
            </section>

            <section>
                <h2>Address Information</h2>

                <label for="address">Address</label>
                <textarea id="address"
                    name="address"
                    maxlength="255"
                    placeholder="Enter your farm address"
                    required><?php echo e($form['address']) ?></textarea>

                <label for="village">Village / Town</label>
                <input type="text" id="village"
                    name="village"
                    value="<?php echo e($form['village']) ?>"
                    maxlength="100" required>

                <label for="district">District</label>
                <input type="text" id="district"
                    name="district"
                    value="<?php echo e($form['district']) ?>"
                    maxlength="100" required>

                <label for="state">State</label>
                <input type="text" id="state"
                    name="state"
                    value="<?php echo e($form['state']) ?>"
                    maxlength="100" required>

                <label for="pincode">PIN Code</label>
                <input type="text" id="pincode"
                    name="pincode"
                    value="<?php echo e($form['pincode']) ?>"
                    maxlength="10"
                    required>
            </section>

            <button type="submit">Update Profile</button>

        </form>

    </main>

    <footer>
        <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
    </footer>

    <script>
        const profileToggle = document.getElementById("profileToggle");
        const profilePopup = document.getElementById("profilePopup");

        profileToggle.addEventListener("click", function() {
            profilePopup.classList.toggle("show");

            profileToggle.setAttribute(
                "aria-expanded",
                profilePopup.classList.contains("show")
            );
        });

        document.addEventListener("click", function(event) {
            if (!event.target.closest(".profile-menu")) {
                profilePopup.classList.remove("show");
                profileToggle.setAttribute("aria-expanded", "false");
            }
        });

        // Preview the selected profile picture
        const profilePicture = document.getElementById("profilePicture");
        const profilePreview = document.getElementById("profilePreview");
        const profileInitialElement = document.getElementById("profileInitial");

        profilePicture.addEventListener("change", function() {
            const file = this.files[0];

            if (!file) {
                return;
            }

            const allowedTypes = ["image/jpeg", "image/png", "image/webp"];

            if (!allowedTypes.includes(file.type)) {
                alert("Please select a JPG, PNG or WEBP image.");
                this.value = "";
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert("Profile picture must be smaller than 5 MB.");
                this.value = "";
                return;
            }

            const reader = new FileReader();

            reader.onload = function(event) {
                profilePreview.src = event.target.result;
                profilePreview.hidden = false;
                profileInitialElement.hidden = true;
            };

            reader.readAsDataURL(file);
        });
    </script>

</body>
</html>