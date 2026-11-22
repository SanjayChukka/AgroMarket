<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Partners | AgroMarket Connect</title>
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
            <h1>Our Partners</h1>
            <p>
                Building stronger agricultural connections
                through collaboration and partnerships.
            </p>
        </section>

        <!-- Introduction -->
        <section class="common-section">
            <h2>Growing Together</h2>
            <p>
                AgroMarket Connect aims to build a reliable agricultural
                ecosystem by collaborating with organizations and
                service providers that support farmers and buyers.
            </p>
        </section>

        <!-- Partner Categories -->
        <section class="common-section">

            <h2>Our Partner Network</h2>

            <div class="partners-container">

                <div class="partner-card">
                    <h3>Agricultural Organizations</h3>
                    <p>
                        Organizations supporting farmers through
                        agricultural knowledge, resources and guidance.
                    </p>
                </div>

                <div class="partner-card">
                    <h3>Logistics Partners</h3>
                    <p>
                        Transportation and delivery service providers
                        supporting agricultural product movement.
                    </p>
                </div>

                <div class="partner-card">
                    <h3>Technology Partners</h3>
                    <p>
                        Technology service providers helping improve
                        platform reliability and digital services.
                    </p>
                </div>

                <div class="partner-card">
                    <h3>Business Partners</h3>
                    <p>
                        Agricultural businesses, wholesalers and
                        distributors supporting market connections.
                    </p>
                </div>

            </div>

        </section>

        <!-- Partnership Benefits -->
        <section class="common-section">

            <h2>Why Partner With Us?</h2>

            <div class="benefits-container">

                <div class="benefit-item">
                    <h3>Wider Reach</h3>
                    <p>Connect with farmers, buyers and agricultural businesses.</p>
                </div>

                <div class="benefit-item">
                    <h3>Business Opportunities</h3>
                    <p>Explore opportunities within the agricultural marketplace.</p>
                </div>

                <div class="benefit-item">
                    <h3>Shared Growth</h3>
                    <p>Support a more connected and accessible agricultural ecosystem.</p>
                </div>

            </div>

        </section>

        <!-- Partnership Enquiry -->
        <section class="common-cta">

            <h2>Interested in Partnering With Us?</h2>

            <p>
                Contact our team to discuss potential partnership opportunities.
            </p>

            <a href="contact.php">Contact Us</a>

        </section>

    </main>

    <!-- Footer -->
    <?php require __DIR__ . "/includes/footer.php"; ?>

</body>

</html>