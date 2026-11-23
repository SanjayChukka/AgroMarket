
<?php

    /* Authentication and Database Connection */
    require_once __DIR__ . '/../../config/auth_check.php';

    require_login('admin', '../auth/login.php');

    require_once __DIR__ . '/../../config/db.php';

    /* MySQLi Error Reporting */
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    /* Admin Session */
    $adminId = (int) $_SESSION['user_id'];

    $_SESSION['admin_csrf'] ??= bin2hex(random_bytes(32));

    /* Escape HTML Output */
    function h($value): string
    {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
    }

    /* Format Status Labels */
    function label($value): string
    {
    return ucwords(
        str_replace('_', ' ', (string) $value)
    );
    }

    /* Fetch Multiple Database Records */
    function rows(string $sql, array $params = []): array
    {
    global $link;

    $stmt = $link->prepare($sql);

    $stmt->execute($params);

    $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return $data;
    }

    /* Fetch a Single Count Value */
    function countValue(string $sql, array $params = []): int
    {
    return (int) array_values(
        rows($sql, $params)[0]
    )[0];
    }

    /* Validate CSRF Token */
    function checkPost(): void
    {
    if (
        ! hash_equals(
            $_SESSION['admin_csrf'],
            (string) ($_POST['csrf'] ?? '')
        )
    ) {
        http_response_code(403);

        exit('Your form expired. Reload the page and try again.');
    }
    }

    /* Generate CSRF Hidden Input */
    function csrfInput(): void
    {
    echo '<input type="hidden" name="csrf" value="' .
    h($_SESSION['admin_csrf']) .
        '">';
    }

    /* Redirect with Notification */
    function go(string $path, string $message): void
    {
    $_SESSION['admin_notice'] = $message;

    header('Location: ' . $path, true, 303);

    exit;
    }

    /* Generate Dynamic HTML Table */
    function table(
    array $records,
    array $columns,
    ?string $detailPage = null
    ): void {

    /* Handle Empty Records */
    if (! $records) {
        echo '<p class="empty">No records found.</p>';
        return;
    }

    /* Table Header */
    echo '<div class="table-wrap">';
    echo '<table>';
    echo '<thead>';
    echo '<tr>';

    foreach ($columns as $heading) {
        echo '<th scope="col">' . h($heading) . '</th>';
    }

    if ($detailPage) {
        echo '<th scope="col">Details</th>';
    }

    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    /* Table Rows */
    foreach ($records as $row) {

        echo '<tr>';

        foreach ($columns as $key => $heading) {

            $value = $row[$key] ?? null;

            echo '<td>';

            if ($key === 'status') {

                echo '<span class="status">' .
                h(label($value)) .
                    '</span>';

            } elseif ($value === null || $value === '') {

                echo '—';

            } else {

                echo h($value);
            }

            echo '</td>';
        }

        /* View Details Link */
        if ($detailPage) {

            echo '<td><a href="' .
            h(
                $detailPage . '?' .
                http_build_query(
                    array_merge(
                        $_GET,
                        ['id' => (int) $row['id']]
                    )
                )
            ) .
                '">View</a></td>';
        }

        echo '</tr>';
    }

    /* Close Table */
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}
