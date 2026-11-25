
<?php

require_once __DIR__ . '/../config/auth_check.php';

require_login($portalRole, '../auth/login.php');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/html.php';


// Retrieve the current user ID.
$userId = (int) $_SESSION['user_id'];


// Fetch support requests and complaints submitted by the user.
$stmt = $link->prepare(
    "SELECT
        ticket_number,
        subject,
        status,
        description,
        reply,
        created_at
    FROM support_requests
    WHERE user_id = ?

    UNION ALL

    SELECT
        complaint_number,
        subject,
        complaint_status,
        description,
        NULL,
        created_at
    FROM complaints
    WHERE raised_by = ?

    ORDER BY created_at DESC"
);

$stmt->execute([
    $userId,
    $userId
]);

$tickets = $stmt->get_result();


// Set the active navigation page.
$activePage = 'complaints.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>My Complaints | AgroMarket Connect</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= e($portalRole); ?>.css">
    <link rel="stylesheet" href="../common/css/support.css">

    <!-- Favicon -->
    <link rel="icon" href="../favicon.ico" type="image/x-icon">

</head>

<body>

    <!-- Role-specific Header -->
    <?php
    require __DIR__ . '/../' . $portalRole . '/includes/header.php';
    ?>


    <!-- Main Complaints Container -->
    <main class="support-list">

        <!-- Complaints Introduction -->
        <section class="complaint-intro">

            <div>

                <h1>My Complaints</h1>

                <p>
                    <a href="../common/grievance.php#support-form">
                        Submit a complaint
                    </a>
                </p>

            </div>


            <!-- Role-specific Support Image -->
            <img
                src="../images/support/<?= $portalRole === 'farmer' ? 'farmer' : 'buyer'; ?>.webp"
                alt=""
            >

        </section>


        <!-- Display Message When No Complaints Exist -->
        <?php if (!$tickets->num_rows): ?>

            <p>You have no complaints yet.</p>

        <?php endif; ?>


        <!-- Display Submitted Complaints -->
        <?php foreach ($tickets as $ticket): ?>

            <article>

                <!-- Complaint Subject -->
                <h2>
                    <?= e($ticket['subject']); ?>
                </h2>


                <!-- Ticket Number and Submission Date -->
                <p>
                    <?= e($ticket['ticket_number']); ?>
                    ·
                    <?= e($ticket['created_at']); ?>
                </p>


                <!-- Complaint Status -->
                <p>

                    <strong>Status</strong>

                    <?= e(ucwords(str_replace(
                        '_',
                        ' ',
                        $ticket['status']
                    ))); ?>

                </p>


                <!-- Complaint Description -->
                <p>
                    <?= e($ticket['description']); ?>
                </p>


                <!-- Display Support Response If Available -->
                <?php if ($ticket['reply']): ?>

                    <p>

                        <strong>Support response</strong>

                        <br>

                        <?= e($ticket['reply']); ?>

                    </p>

                <?php endif; ?>

            </article>

        <?php endforeach; ?>

    </main>

</body>

</html>
