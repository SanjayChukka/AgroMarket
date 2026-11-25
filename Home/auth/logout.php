
<?php

session_start();

header('Cache-Control: no-store');


// Check whether the user is authenticated.
if (!isset($_SESSION['user_id'], $_SESSION['user_role'])) {
    header('Location: ../index.php', true, 303);
    exit;
}


require_once __DIR__ . '/../includes/signout-token.php';


// Display the sign-out confirmation page for GET requests.
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $role = $_SESSION['user_role'];

    $dashboard = in_array($role, ['buyer', 'farmer', 'admin'], true)
        ? "../$role/dashboard.php"
        : '../index.php';

    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Confirm sign out | AgroMarket Connect</title>

        <!-- Stylesheet -->
        <link rel="stylesheet" href="../styles/theme.css">

        <!-- Favicon -->
        <link rel="icon" href="../favicon.ico" type="image/x-icon">

    </head>

    <body>

        <main class="signout-fallback">

            <!-- Brand and Home Navigation -->
            <?php require __DIR__ . '/../includes/brand-home.php'; ?>


            <!-- Sign-out Confirmation -->
            <h1>Sign out now?</h1>

            <p>
                <?= htmlspecialchars(
                    $_SESSION['full_name'] ?? ucfirst($role),
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>, confirm to end this session.
            </p>


            <!-- Sign-out Form -->
            <form method="post">

                <!-- Security Token -->
                <input
                    type="hidden"
                    name="token"
                    value="<?= signoutToken(); ?>"
                >

                <a href="<?= $dashboard; ?>">
                    Stay signed in
                </a>

                <button type="submit">
                    Sign out
                </button>

            </form>

        </main>

    </body>

    </html>

    <?php

    exit;
}


// Validate the request method and sign-out security token.
if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !is_string($_POST['token'] ?? null) ||
    !hash_equals(signoutToken(), $_POST['token'])
) {
    http_response_code(403);

    exit(
        'Your session changed. Return to your dashboard and try again.'
    );
}


// Remove all session data.
$_SESSION = [];


// Destroy the session cookie.
if (ini_get("session.use_cookies")) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}


// Destroy the session.
session_destroy();


// Return to the homepage.
header("Location: ../index.php", true, 303);

exit;
