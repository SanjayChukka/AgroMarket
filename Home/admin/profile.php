
<?php

    require __DIR__ . '/includes/bootstrap.php';

    $section   = 'profile';
    $pageTitle = 'My profile';

    // Fetch administrator profile details.
    $profile = rows(
    "SELECT
        u.user_id,
        u.username,
        u.full_name,
        u.email,
        u.mobile,
        u.account_status,
        u.last_login,
        u.created_at,
        p.admin_level
    FROM users u
    LEFT JOIN admin_profiles p
        ON p.admin_id = u.user_id
    WHERE u.user_id = ?
        AND u.role = 'admin'",
    [$adminId]
    )[0] ?? [];

    require __DIR__ . '/includes/header.php';

?>

<!-- Administrator Profile -->

<section class="panel">

    <?php if (! $profile): ?>

        <p>Administrator account could not be found.</p>

    <?php else: ?>

        <dl>

            <?php foreach ([
                    'full_name'      => 'Full name',
                    'username'       => 'Username',
                    'email'          => 'Email',
                    'mobile'         => 'Mobile',
                    'user_id'        => 'Account ID',
                    'admin_level'    => 'Admin level',
                    'account_status' => 'Account status',
                    'last_login'     => 'Last login',
                    'created_at'     => 'Registered',
            ] as $key => $caption): ?>

                <div>

                    <dt>
                        <?php echo h($caption); ?>
                    </dt>

                    <dd>
                        <?php echo h($profile[$key] ?? 'Not recorded'); ?>
                    </dd>

                </div>

            <?php endforeach; ?>

        </dl>

    <?php endif; ?>

</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
