<?php
require_once __DIR__ . '/signout-token.php';
$role = $_SESSION['user_role'];
$name = trim($_SESSION['full_name'] ?? '') ?: ucfirst($role);
?>
<a class="<?= $role === 'admin' ? 'signout' : 'logout-link' ?>" id="signout-link" href="../auth/logout.php">Sign out</a>
<dialog id="signout-dialog" aria-labelledby="signout-title" aria-describedby="signout-description">
    <form method="post" action="../auth/logout.php">
        <input type="hidden" name="token" value="<?= signoutToken() ?>">
        <h2 id="signout-title">Sign out now?</h2>
        <p id="signout-description">You are signed in as <strong><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></strong> (<?= htmlspecialchars(ucfirst($role), ENT_QUOTES, 'UTF-8') ?>).</p>
        <div class="signout-actions">
            <button type="button" id="stay-signed-in" autofocus>Stay signed in</button>
            <button type="submit">Sign out</button>
        </div>
    </form>
</dialog>
<script src="../js/signout.js" defer></script>
