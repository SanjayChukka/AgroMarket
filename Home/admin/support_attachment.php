<?php
require_once __DIR__ . '/includes/bootstrap.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$file = $id ? (rows('SELECT file_type,file_data FROM support_requests WHERE request_id=?', [$id])[0] ?? null) : null;
$types = ['application/pdf'=>'pdf','image/png'=>'png','image/jpeg'=>'jpg'];
if (!$file || !isset($types[$file['file_type']])) {
    http_response_code(404);
    exit;
}
header('Content-Type: application/octet-stream');
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: attachment; filename="support-' . $id . '.' . $types[$file['file_type']] . '"');
echo $file['file_data'];
