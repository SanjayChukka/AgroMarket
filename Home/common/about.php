
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | AgroMarket Connect</title>
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
        <section class="about-hero">
            <h1>About AgroMarket Connect</h1>
            <p>
                Connecting Farmers and Buyers for a Better Agricultural
                Marketplace.
            </p>
        </section>

        <!-- Who We Are -->
        <section class="about-section">
            <div class="section-content">
                <h2>Who We Are</h2>
                <p>
                    AgroMarket Connect is a digital agricultural marketplace
                    designed to connect farmers directly with buyers.
                    Our platform aims to simplify agricultural trading by
                    providing a common space to discover products, connect
                    with sellers and buyers, and exchange information.
                </p>
            </div>
        </section>

        <!-- Mission and Vision -->
        <section class="mission-vision" id="mission-vision">

            <div class="content-card">
                <h2>Our Mission</h2>
                <p>
                    To create an accessible and reliable digital platform
                    that helps farmers reach potential buyers and enables
                    buyers to discover agricultural products easily.
                </p>
            </div>

            <div class="content-card">
                <h2>Our Vision</h2>
                <p>
                    To build a connected agricultural marketplace that
                    supports transparent trading, better opportunities,
                    and stronger relationships between farmers and buyers.
                </p>
            </div>

        </section>

        <!-- Objectives -->
        <section class="about-section">
            <h2>Our Objectives</h2>

            <div class="objectives-container">

                <div class="objective-card">
                    <h3>Direct Connections</h3>
                    <p>Connect farmers with buyers through one platform.</p>
                </div>

                <div class="objective-card">
                    <h3>Market Accessibility</h3>
                    <p>Make agricultural products easier to discover.</p>
                </div>

                <div class="objective-card">
                    <h3>Transparent Information</h3>
                    <p>Provide clear product and seller information.</p>
                </div>

                <div class="objective-card">
                    <h3>Digital Growth</h3>
                    <p>Encourage technology adoption in agricultural trade.</p>
                </div>

            </div>
        </section>

        <!-- Why Choose Us -->
        <section class="about-section">
            <h2>Why Choose AgroMarket Connect?</h2>

            <div class="benefits-container">

                <div class="benefit-card">
                    <h3>For Farmers</h3>
                    <ul>
                        <li>List agricultural products.</li>
                        <li>Reach potential buyers.</li>
                        <li>Manage product information.</li>
                        <li>Track incoming orders.</li>
                    </ul>
                </div>

                <div class="benefit-card">
                    <h3>For Buyers</h3>
                    <ul>
                        <li>Discover agricultural products.</li>
                        <li>Explore available listings.</li>
                        <li>Connect with farmers.</li>
                        <li>Manage purchase orders.</li>
                    </ul>
                </div>

            </div>
        </section>

        <!-- Our Platform -->
        <section class="about-section">
            <h2>Our Platform</h2>
            <p>
                AgroMarket Connect brings farmers and buyers together
                through a shared digital marketplace. Farmers can publish
                product listings, while buyers can explore available
                products and connect with sellers.
            </p>
        </section>

        <!-- Call to Action -->
        <section class="about-cta">
            <h2>Be Part of the Agricultural Connection</h2>
            <p>Join AgroMarket Connect and explore new opportunities.</p>

            <div class="cta-actions">
                <a href="../auth/register.php">Join as a Farmer or Buyer</a>
                <a href="contact.php">Contact Us</a>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <?php require __DIR__ . "/includes/footer.php"; ?>

</body>
</html>
