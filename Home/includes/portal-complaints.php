<?php
require_once __DIR__ . '/../config/auth_check.php';
require_login($portalRole, '../auth/login.php');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/html.php';
$userId = (int) $_SESSION['user_id'];
$stmt = $link->prepare("SELECT ticket_number, subject, status, description, reply, created_at
    FROM support_requests WHERE user_id=?
    UNION ALL SELECT complaint_number, subject, complaint_status, description, NULL, created_at
    FROM complaints WHERE raised_by=? ORDER BY created_at DESC");
$stmt->execute([$userId, $userId]);
$tickets = $stmt->get_result();
$activePage = 'complaints.php';
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Complaints | AgroMarket Connect</title>
<link rel="stylesheet" href="<?= e($portalRole) ?>.css">
<link rel="stylesheet" href="../common/css/support.css"></head><body>
<?php require __DIR__ . '/../' . $portalRole . '/includes/header.php'; ?>
<main class="support-list"><h1>My Complaints</h1>
<p><a href="../common/grievance.php#support-form">Submit a complaint</a></p>
<?php if (!$tickets->num_rows): ?><p>You have no complaints yet.</p><?php endif; ?>
<?php foreach ($tickets as $ticket): ?>
<article><h2><?= e($ticket['subject']) ?></h2>
<p><?= e($ticket['ticket_number']) ?> · <?= e($ticket['created_at']) ?></p>
<p><strong>Status</strong> <?= e(ucwords(str_replace('_', ' ', $ticket['status']))) ?></p>
<p><?= e($ticket['description']) ?></p>
<?php if ($ticket['reply']): ?><p><strong>Support response</strong><br><?= e($ticket['reply']) ?></p><?php endif; ?>
</article>
<?php endforeach; ?></main></body></html>
