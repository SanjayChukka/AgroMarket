
<?php

    require_once __DIR__ . '/includes/bootstrap.php';

    /* Get Support Request ID */
    $id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
    );

    /* Fetch Attached File */
    $file = $id
    ? (
    rows(
        'SELECT file_type, file_data
             FROM support_requests
             WHERE request_id = ?',
        [$id]
    )[0] ?? null
    )
    : null;

    /* Allowed File Types */
    $types = [
    'application/pdf' => 'pdf',
    'image/png'       => 'png',
    'image/jpeg'      => 'jpg',
    ];

    /* Validate File */
    if (! $file || ! isset($types[$file['file_type']])) {
    http_response_code(404);
    exit;
    }

    /* Set Download Headers */
    header('Content-Type: application/octet-stream');

    header('X-Content-Type-Options: nosniff');

    header(
    'Content-Disposition: attachment; filename="support-' .
    $id . '.' . $types[$file['file_type']] . '"'
    );

    /* Download File */
echo $file['file_data'];
