<?php
    $nav = ['dashboard' => 'Overview', 'farmers' => 'Farmers', 'buyers' => 'Buyers', 'listings' => 'Crop listings', 'orders' => 'Orders', 'complaints' => 'Complaints', 'reports' => 'Reports', 'profile' => 'My profile'];
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo h($pageTitle) ?> | AgroMarket Connect</title><link rel="stylesheet" href="admin.css"></head><body>
<a class="skip" href="#main">Skip to content</a>
<div class="shell"><aside>
<a class="brand" href="../index.php"><span class="seed" aria-hidden="true"></span>AgroMarket<span class="brand-sub">Connect</span></a>
<nav aria-label="Administration">
<?php foreach ($nav as $key => $name): ?>
<a href="<?php echo $key ?>.php" <?php echo $section === $key ? 'aria-current="page"' : '' ?>><?php echo h($name) ?></a>
<?php endforeach; ?></nav>
<a class="signout" href="../auth/logout.php">Sign out ↗</a></aside>
<main id="main"><header class="top"><span>Administration</span><a href="profile.php"><?php echo h($_SESSION['full_name'] ?? 'Administrator') ?></a></header>
<div class="page"><h1><?php echo h($pageTitle) ?></h1>
<?php if (isset($_SESSION['admin_notice'])): ?><p role="status" class="notice"><?php echo h($_SESSION['admin_notice']) ?></p><?php unset($_SESSION['admin_notice']);endif; ?>
