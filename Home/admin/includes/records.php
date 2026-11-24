
<?php

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/catalog.php';

/* Page Configuration */
$config = catalog($section);

$pageTitle = $section === 'listings'
    ? 'Crop listings'
    : ucfirst($section);

$path = $section . '.php';
$source = '(' . $config['sql'] . ') records';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$states = [
    'open',
    'in_progress',
    'resolved',
    'closed'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id > 0 && in_array($section, ['orders','listings'], true)) {
    require_once __DIR__ . '/manage-market.php';
    manageMarketRecord($link, $section, $id, $adminId);
}

/* Update Complaint Status */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    $section === 'complaints' &&
    $id
) {
    checkPost();

    $new = $_POST['status'] ?? '';

    if (!in_array($new, $states, true)) {
        http_response_code(400);
        exit('Invalid complaint status.');
    }

    // Positive IDs represent complaints.
    // Negative IDs represent website support requests.
    if ($id < 0) {

        $reply = trim((string) ($_POST['reply'] ?? ''));

        if (strlen($reply) > 12000) {
            go(
                $path . '?id=' . $id,
                'Shorten the response and try again.'
            );
        }

        $stmt = $link->prepare(
            'UPDATE support_requests
             SET status = ?,
                 reply = ?,
                 revision = revision + 1
             WHERE request_id = ?
               AND revision = ?'
        );

        $stmt->execute([
            $new,
            $reply,
            -$id,
            (int) ($_POST['revision'] ?? -1)
        ]);

        go(
            $path . '?id=' . $id,
            $stmt->affected_rows
                ? 'Complaint updated.'
                : 'Another administrator updated this complaint. Review it before saving.'
        );
    }

    /* Update Existing Complaint */
    $stmt = $link->prepare(
        'UPDATE complaints
         SET complaint_status = ?
         WHERE complaint_id = ?
           AND complaint_status = ?'
    );

    $stmt->execute([
        $new,
        $id,
        $_POST['previous'] ?? ''
    ]);

    go(
        $path . '?id=' . $id,
        $stmt->affected_rows
            ? 'Complaint status updated.'
            : 'No change saved. Review the current status.'
    );
}

require __DIR__ . '/header.php';

/* Record Details */
if (isset($_GET['id'])) {

    $record = $id
        ? (rows("SELECT * FROM $source WHERE id = ?", [$id])[0] ?? null)
        : null;

    $back = $_GET;
    unset($back['id']);

    echo '<p><a href="' .
        h($path . ($back ? '?' . http_build_query($back) : '')) .
        '">← Back to ' .
        h($pageTitle) .
        '</a></p>';

    if (!$record) {

        echo '<p class="empty">Record not found.</p>';

    } else {

        /* Display Record Information */
        echo '<section class="panel">';
        echo '<h2>Record details</h2>';
        echo '<dl>';

        foreach ($config['columns'] + $config['extra'] as $key => $title) {
            echo '<div>';
            echo '<dt>' . h($title) . '</dt>';
            echo '<dd>' . h($record[$key] ?? '—') . '</dd>';
            echo '</div>';
        }

        echo '</dl>';
        echo '</section>';

        $actions = [];
        if ($section === 'orders' && $record['status'] === 'pending') {
            if ($record['method'] === 'delivery') $actions['address'] = 'Save street address';
            $actions['cancel'] = 'Cancel pending order';
        }
        if ($section === 'listings' && $record['status'] === 'available') $actions['pause'] = 'Pause listing';
        if ($section === 'listings' && $record['status'] === 'paused' && $record['quantity'] > 0) $actions['resume'] = 'Resume listing';
        foreach ($actions as $action => $caption): ?>
            <form method="post" class="panel filters">
            <?php csrfInput(); ?>
            <input type="hidden" name="action" value="<?= h($action) ?>">
            <?php if ($action === 'address'): ?>
            <input type="hidden" name="previous_address" value="<?= h($record['address']) ?>">
            <label>Delivery street address <input name="address" value="<?= h($record['address']) ?>" maxlength="255" required></label>
            <?php endif; ?>
            <label>Reason shared with the affected user(s) <input name="reason" maxlength="400" required></label>
            <button><?= h($caption) ?></button></form>
        <?php endforeach;
        /* Complaint Management Form */
        if ($section === 'complaints') {
            ?>

            <form class="panel filters" method="post">

                <?php csrfInput(); ?>

                <?php if ($id < 0): ?>

                    <input
                        type="hidden"
                        name="revision"
                        value="<?php echo (int) $record['revision']; ?>"
                    >

                    <?php if ($record['file_type']): ?>

                        <a href="support_attachment.php?id=<?php echo -$id; ?>">
                            Download supporting document
                        </a>

                    <?php endif; ?>

                    <label>
                        Support response

                        <textarea
                            name="reply"
                            rows="4"
                        ><?php echo h($record['reply']); ?></textarea>
                    </label>

                <?php endif; ?>

                <input
                    type="hidden"
                    name="previous"
                    value="<?php echo h($record['status']); ?>"
                >

                <label>
                    Complaint status

                    <select name="status">

                        <?php foreach ($states as $state): ?>

                            <option
                                <?php echo strcasecmp($state, $record['status']) === 0
                                    ? 'selected'
                                    : ''; ?>
                            >
                                <?php echo h($state); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </label>

                <button>Save status</button>

            </form>

            <?php
        }
    }

} else {

    /* Search and Filter Parameters */
    $q = trim((string) ($_GET['q'] ?? ''));
    $status = (string) ($_GET['status'] ?? '');
    $category = (string) ($_GET['category'] ?? '');

    $where = [];
    $params = [];

    /* Search Filter */
    if ($q !== '') {

        $where[] = '(' .
            implode(
                ' OR ',
                array_map(
                    fn($key) => "$key LIKE ?",
                    $config['search']
                )
            ) .
            ')';

        $params = array_fill(
            0,
            count($config['search']),
            '%' . $q . '%'
        );
    }

    /* Status Filter */
    if ($status !== '') {
        $where[] = 'status=?';
        $params[] = $status;
    }

    /* Category Filter */
    if ($section === 'listings' && $category !== '') {
        $where[] = 'category=?';
        $params[] = $category;
    }

    /* Pagination */
    $filter = $where
        ? ' WHERE ' . implode(' AND ', $where)
        : '';

    $total = countValue(
        "SELECT COUNT(*) FROM $source $filter",
        $params
    );

    $pages = max(1, (int) ceil($total / 25));

    $page = max(
        1,
        min($pages, (int) ($_GET['page'] ?? 1))
    );

    $offset = ($page - 1) * 25;

    /* Fetch Records */
    $records = rows(
        "SELECT *
         FROM $source
         $filter
         ORDER BY recorded DESC, id DESC
         LIMIT 25 OFFSET $offset",
        $params
    );

    ?>

    <!-- Records List -->
    <section class="panel">

        <!-- Search and Filters -->
        <form class="filters" method="get">

            <label>
                Search

                <input
                    type="search"
                    name="q"
                    value="<?php echo h($q); ?>"
                    placeholder="Search <?php echo h($pageTitle); ?>"
                >
            </label>

            <label>
                Status

                <select name="status">

                    <option value="">All statuses</option>

                    <?php
                    foreach (
                        rows("SELECT DISTINCT status FROM $source ORDER BY status")
                        as $r
                    ):
                    ?>

                        <option
                            value="<?php echo h($r['status']); ?>"
                            <?php echo $status === $r['status'] ? 'selected' : ''; ?>
                        >
                            <?php echo h(label($r['status'])); ?>
                        </option>

                    <?php endforeach; ?>

                </select>
            </label>

            <?php if ($section === 'listings'): ?>

                <label>
                    Category

                    <select name="category">

                        <option value="">All categories</option>

                        <?php
                        foreach (
                            rows(
                                'SELECT category_name
                                 FROM crop_categories
                                 ORDER BY category_name'
                            ) as $r
                        ):
                        ?>

                            <option
                                <?php echo $category === $r['category_name']
                                    ? 'selected'
                                    : ''; ?>
                            >
                                <?php echo h($r['category_name']); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </label>

            <?php endif; ?>

            <button>Apply filters</button>

            <a href="<?php echo h($path); ?>">Reset</a>

        </form>

        <!-- Total Records -->
        <p class="muted">
            <?php echo number_format($total); ?> matching records
        </p>

        <!-- Records Table -->
        <?php
        table(
            $records,
            $config['columns'],
            $path
        );
        ?>

        <!-- Pagination -->
        <div class="pager">

            <?php
            foreach (
                [
                    'Previous' => $page - 1,
                    'Next' => $page + 1
                ] as $name => $n
            ):

                if ($n < 1 || $n > $pages) {
                    continue;
                }
            ?>

                <a href="?<?php
                    echo h(
                        http_build_query(
                            array_merge($_GET, ['page' => $n])
                        )
                    );
                ?>">
                    <?php echo $name; ?>
                </a>

            <?php endforeach; ?>

            <span>
                Page <?php echo $page; ?> of <?php echo $pages; ?>
            </span>

        </div>

    </section>

<?php
}

require __DIR__ . '/footer.php';
