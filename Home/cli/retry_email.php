<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/../config/db.php';
require __DIR__.'/../includes/email-service.php';
$rows = $link->query('SELECT message_key FROM transactional_emails WHERE sent_at IS NULL AND retry_at<=NOW() ORDER BY retry_at LIMIT 100');
foreach ($rows as $row) dispatchAgroMail($link,$row['message_key']);
