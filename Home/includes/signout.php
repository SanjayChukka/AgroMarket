
<?php

require_once __DIR__ . '/signout-token.php';


// Retrieve the current user's role and display name.
$role = $_SESSION['user_role'];

$name = trim($_SESSION['full_name'] ?? '')
    ?: ucfirst($role);

?>


<!-- Sign-out Link -->
<a
    class="<?= $role === 'admin' ? 'signout' : 'logout-link'; ?>"
    id="signout-link"
    href="../auth/logout.php"
>
    Sign out
</a>


<!-- Sign-out Confirmation Dialog -->
<dialog
    id="signout-dialog"
    aria-labelledby="signout-title"
    aria-describedby="signout-description"
>

    <form method="post" action="../auth/logout.php">

        <!-- CSRF Protection -->
        <input
            type="hidden"
            name="token"
            value="<?= signoutToken(); ?>"
        >


        <!-- Dialog Heading -->
        <h2 id="signout-title">
            Sign out now?
        </h2>


        <!-- Display Current User Information -->
        <p id="signout-description">

            You are signed in as

            <strong>
                <?= htmlspecialchars(
                    $name,
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>
            </strong>

            (<?= htmlspecialchars(
                ucfirst($role),
                ENT_QUOTES,
                'UTF-8'
            ); ?>).

        </p>


        <!-- Sign-out Actions -->
        <div class="signout-actions">

            <!-- Cancel Sign-out -->
            <button
                type="button"
                id="stay-signed-in"
                autofocus
            >
                Stay signed in
            </button>


            <!-- Confirm Sign-out -->
            <button type="submit">
                Sign out
            </button>

        </div>

    </form>

</dialog>


<!-- Sign-out Confirmation JavaScript -->
<script src="../js/signout.js" defer></script>
