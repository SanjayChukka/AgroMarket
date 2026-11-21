<?php
    require __DIR__ . '/includes/bootstrap.php';
    $section   = 'profile';
    $pageTitle = 'Administrator profile';
    $record    = rows("SELECT full_name,email,mobile FROM users WHERE user_id=? AND role='admin'", [$adminId])[0] ?? null;
    if (! $record) {http_response_code(403);exit('Administrator account unavailable.');}
    $directory = __DIR__ . '/../uploads/admin_profiles';
    $photo     = $directory . '/' . $adminId . '.jpg';
    $error     = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkPost();
    if (isset($_POST['photo'])) {

        $file = $_FILES['portrait'] ?? [];
        $tmp  = $file['tmp_name'] ?? '';
        $info = is_uploaded_file($tmp) ? @getimagesize($tmp) : false;

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || ! $info ||
            $file['size'] > 2 * 1024 * 1024 || $info[0] * $info[1] > 8000000 ||
            ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            $error = 'Choose a JPG, PNG or WebP image under 2 MB and 8 megapixels.';
        } elseif (! function_exists('imagecreatefromstring')) {
            $error = 'Enable the PHP GD extension to save a profile picture.';
        } else {
            $image = @imagecreatefromstring(file_get_contents($tmp));
            $saved = false;
            if ($image && (is_dir($directory) || @mkdir($directory, 0755, true) || is_dir($directory))) {
                $staging = tempnam($directory, 'avatar-');
                if ($staging !== false) {
                    $saved = imagejpeg($image, $staging, 85) && rename($staging, $photo);
                    if (is_file($staging)) {
                        unlink($staging);
                    }

                }
            }
            if ($saved) {
                go('profile.php', 'Profile picture updated.');
            }

            $error = 'Could not save the picture. Check the upload folder permissions.';
        }
    } else {
        foreach (['full_name', 'email', 'mobile'] as $field) {
            $record[$field] = trim((string) ($_POST[$field] ?? ''));
        }

        if ($record['full_name'] === '' || strlen($record['full_name']) > 100 || ! filter_var($record['email'], FILTER_VALIDATE_EMAIL) || ! preg_match('/^[6-9]\d{9}$/D', $record['mobile'])) {
            $error = 'Enter a name, a valid email and a 10-digit Indian mobile number.';
        } else {
            try {
                $stmt = $link->prepare("UPDATE users SET full_name=?,email=?,mobile=? WHERE user_id=? AND role='admin'");
                $stmt->execute([$record['full_name'], $record['email'], $record['mobile'], $adminId]);
                $_SESSION['full_name'] = $record['full_name'];
                go('profile.php', 'Contact details saved.');
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() !== 1062) {
                    throw $e;
                }

                $error = 'The email or mobile number is already in use.';
            }
        }
    }
    }
    require __DIR__ . '/includes/header.php';
?>
<?php if ($error): ?><p class="notice" role="alert"><?php echo h($error) ?></p><?php endif; ?>
<div class="charts"><section class="panel"><h2>Contact details</h2><form class="stack" method="post"><?php csrfInput(); ?>
<label>Full name<input name="full_name" required maxlength="100" value="<?php echo h($record['full_name']) ?>"></label>
<label>Email<input type="email" required name="email" value="<?php echo h($record['email']) ?>"></label>
<label>Mobile<input type="tel" required pattern="[6-9][0-9]{9}" name="mobile" value="<?php echo h($record['mobile']) ?>"></label>
<div><button>Save changes</button> <a class="action" href="profile.php">Cancel</a></div></form></section>
<section class="panel"><h2>Profile picture</h2>
<?php if (is_file($photo)): ?><img class="avatar" alt="Current profile picture" src="../uploads/admin_profiles/<?php echo $adminId ?>.jpg?v=<?php echo filemtime($photo) ?>">
<?php else: ?><div class="avatar initial"><?php echo h(mb_substr($record['full_name'], 0, 1)) ?></div><?php endif; ?>
<form class="stack" method="post" enctype="multipart/form-data"><?php csrfInput(); ?><label>Picture (up to 2 MB)<input type="file" name="portrait" accept="image/jpeg,image/png,image/webp" required></label><div><button name="photo" value="1">Save picture</button></div></form></section></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
