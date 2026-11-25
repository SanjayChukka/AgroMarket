
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>How It Works | AgroMarket Connect</title>
    <link rel="stylesheet" href="./css/common.css">
<link rel="icon" href="../favicon.ico" type="image/x-icon">
</head>
<body>

    <!-- Header -->
    <?php
        $showMarketPrices = false;
        $registrationRole = null;
    require __DIR__ . "/includes/header.php"; ?>

    <main>

        <!-- Page Introduction -->
        <section class="common-hero">
            <h1>How AgroMarket Connect Works</h1>
            <p>
                A simple way to connect farmers and buyers through
                a digital agricultural marketplace.
            </p>
        </section>

        <!-- General Process -->
        <section class="common-section">
            <h2>How Our Platform Works</h2>

            <div class="process-container">

                <div class="process-card">
                    <h3>1. Create an Account</h3>
                    <p>
                        Register on AgroMarket Connect as a farmer
                        or buyer using your basic details.
                    </p>
                </div>

                <div class="process-card">
                    <h3>2. Connect to the Marketplace</h3>
                    <p>
                        Access the platform based on your account
                        type and explore the available features.
                    </p>
                </div>

                <div class="process-card">
                    <h3>3. Connect and Trade</h3>
                    <p>
                        Farmers can list products, while buyers
                        can discover products and place orders.
                    </p>
                </div>

            </div>
        </section>

        <!-- Farmer Process -->
        <section class="common-section">

            <h2>For Farmers</h2>
            <p>
                AgroMarket Connect helps farmers showcase their
                agricultural products and reach potential buyers.
            </p>

            <div class="role-process-container">

                <div class="role-process-card">
                    <h3>1. Register as a Farmer</h3>
                    <p>Create your farmer account and provide your details.</p>
                </div>

                <div class="role-process-card">
                    <h3>2. Add Products</h3>
                    <p>
                        List crops with product names, quantities,
                        prices and other relevant details.
                    </p>
                </div>

                <div class="role-process-card">
                    <h3>3. Receive Orders</h3>
                    <p>
                        View orders submitted by interested buyers.
                    </p>
                </div>

                <div class="role-process-card">
                    <h3>4. Manage Orders</h3>
                    <p>
                        Review and update the status of received orders.
                    </p>
                </div>

            </div>

            <a href="../auth/register.php">Join as a Farmer</a>

        </section>

        <!-- Buyer Process -->
        <section class="common-section">

            <h2>For Buyers</h2>
            <p>
                Buyers can explore agricultural products and connect
                with farmers through the marketplace.
            </p>

            <div class="role-process-container">

                <div class="role-process-card">
                    <h3>1. Register as a Buyer</h3>
                    <p>Create your buyer account with your basic details.</p>
                </div>

                <div class="role-process-card">
                    <h3>2. Browse Products</h3>
                    <p>
                        Explore available crops and review their
                        product and seller information.
                    </p>
                </div>

                <div class="role-process-card">
                    <h3>3. Place Orders</h3>
                    <p>
                        Select the required products and submit
                        your purchase orders.
                    </p>
                </div>

                <div class="role-process-card">
                    <h3>4. Track Orders</h3>
                    <p>
                        View your orders and check their latest status.
                    </p>
                </div>

            </div>

            <a href="../auth/register.php">Join as a Buyer</a>

        </section>

        <!-- Final CTA -->
        <section class="common-cta">
            <h2>Start Connecting Today</h2>
            <p>
                Join AgroMarket Connect and become part of a
                connected agricultural marketplace.
            </p>

            <div class="cta-actions">
                <a href="../auth/register.php">Create an Account</a>
                <a href="contact.php">Contact Us</a>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <?php require __DIR__ . "/includes/footer.php"; ?>

</body>
</html>
