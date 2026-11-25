
<?php

    /* Admin Navigation Links */
    $nav = [
    'dashboard'   => 'Overview',
    'farmers'     => 'Farmers',
    'buyers'      => 'Buyers',
    'listings'    => 'Crop listings',
    'orders'      => 'Orders',
    'complaints'  => 'Complaints',
    'reports'     => 'Reports',
    'commissions' => 'Platform commissions',
    'profile'     => 'My profile',
    ];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        <?php echo h($pageTitle); ?> | AgroMarket Connect
    </title>

    <link rel="stylesheet" href="admin.css">
    <?php if ($section === 'commissions'): ?>
        <link rel="stylesheet" href="../styles/order-money.css">
    <?php endif; ?>
    <link rel="icon" href="../favicon.ico" type="image/x-icon">
</head>

<body>

    <!-- Skip Navigation -->
    <a class="skip" href="#main">
        Skip to content
    </a>

    <div class="shell">

        <!-- Admin Sidebar -->
        <aside>

            <!-- Brand -->
            <?php require __DIR__ . '/../../includes/brand-home.php'; ?>

            <!-- Navigation Menu -->
            <nav aria-label="Administration">

                <?php foreach ($nav as $key => $name): ?>

                    <a
                        href="<?php echo $key; ?>.php"
                        <?php echo $section === $key
                                ? 'aria-current="page"'
                            : ''; ?>
                    >
                        <?php echo h($name); ?>
                    </a>

                <?php endforeach; ?>

            </nav>

            <!-- Sign Out -->
            <?php require __DIR__ . '/../../includes/signout.php'; ?>

        </aside>


        <!-- Main Content -->
        <main id="main">

            <!-- Top Header -->
            <header class="top">

                <span>Administration</span>

                <a href="profile.php">
                    <?php echo h($_SESSION['full_name'] ?? 'Administrator'); ?>
                </a>

            </header>


            <!-- Page Content -->
            <div class="page">

                <h1>
                    <?php echo h($pageTitle); ?>
                </h1>

                <!-- Admin Notification -->
                <?php if (isset($_SESSION['admin_notice'])): ?>

                    <p role="status" class="notice">
                        <?php echo h($_SESSION['admin_notice']); ?>
                    </p>

                    <?php unset($_SESSION['admin_notice']); ?>

                <?php endif; ?>
