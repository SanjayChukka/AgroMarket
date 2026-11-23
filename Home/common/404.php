<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | AgroMarket Connect</title>
    <link rel="stylesheet" href="./css/common.css">
<link rel="icon" href="../favicon.ico" type="image/x-icon">
</head>

<body>

    <main class="error-page">

        <div class="error-container">

            <!-- Brand -->
            <div class="error-logo">
                <?php require __DIR__ . '/../includes/brand-home.php'; ?>
            </div>

            <!-- Error Content -->
            <div class="error-content">

                <div class="error-number">404</div>

                <h1>Page Not Found!</h1>

                <p>
                    Sorry, the page you are looking for
                    doesn't exist, has been moved or is temporarily unavailable.
                </p>

                <!-- Actions -->
                <div class="error-actions">

                    <a href="../index.php" class="home-btn">
                        Go to Homepage
                    </a>

                    <button type="button" id="goBack">
                        Go Back
                    </button>

                </div>

            </div>

            <!-- Footer -->
            <div class="error-footer">
                <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
            </div>

        </div>

    </main>

<script src="js/404.js" defer></script>
</body>

</html>