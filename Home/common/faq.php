
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ | AgroMarket Connect</title>
    <link rel="stylesheet" href="./css/common.css">
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
            <h1>Frequently Asked Questions</h1>
            <p>
                Find answers to common questions about
                AgroMarket Connect.
            </p>
        </section>

        <!-- General Questions -->
        <section class="common-section" id = "general-questions">

            <h2>General Questions</h2>

            <div class="faq-container">

                <div class="faq-item">
                    <h3>1. What is AgroMarket Connect?</h3>
                    <p>
                        AgroMarket Connect is a digital agricultural
                        marketplace that connects farmers and buyers
                        through a common platform.
                    </p>
                </div>

                <div class="faq-item">
                    <h3>2. Who can use this platform?</h3>
                    <p>
                        Farmers who want to sell agricultural products
                        and buyers who want to purchase them can use
                        our platform.
                    </p>
                </div>

                <div class="faq-item">
                    <h3>3. Is registration required?</h3>
                    <p>
                        Visitors can explore public information.
                        Registration and login will be required for
                        account-specific activities such as listing
                        products and placing orders.
                    </p>
                </div>

            </div>

        </section>

        <!-- Farmer Questions -->
        <section class="common-section" id="farmer-questions">

            <h2>Questions for Farmers</h2>

            <div class="faq-container">

                <div class="faq-item">
                    <h3>1. How can I register as a farmer?</h3>
                    <p>
                        Select Register, choose Farmer, and complete
                        the farmer registration form.
                    </p>
                </div>

                <div class="faq-item">
                    <h3>2. How can I list my agricultural products?</h3>
                    <p>
                        After logging in, access the Add Product
                        section and provide the required product
                        details.
                    </p>
                </div>

                <div class="faq-item">
                    <h3>3. Can I update or remove my product listings?</h3>
                    <p>
                        Farmers will be able to manage their own
                        product listings through the farmer dashboard.
                    </p>
                </div>

                <div class="faq-item">
                    <h3>4. How can I view buyer orders?</h3>
                    <p>
                        Farmers can access the Manage Orders section
                        to view orders received for their products.
                    </p>
                </div>

            </div>

        </section>

        <!-- Buyer Questions -->
        <section class="common-section" id="buyer-questions">

            <h2>Questions for Buyers</h2>

            <div class="faq-container">

                <div class="faq-item">
                    <h3>1. How can I register as a buyer?</h3>
                    <p>
                        Select Register, choose Buyer, and complete
                        the buyer registration form.
                    </p>
                </div>

                <div class="faq-item">
                    <h3>2. How can I find agricultural products?</h3>
                    <p>
                        Browse the marketplace to explore available
                        products and their details.
                    </p>
                </div>

                <div class="faq-item">
                    <h3>3. How can I place an order?</h3>
                    <p>
                        Log in, select an available product, enter
                        the required quantity, and submit your order.
                    </p>
                </div>

                <div class="faq-item">
                    <h3>4. Can I track my orders?</h3>
                    <p>
                        Buyers can view their order history and
                        check order status through the buyer dashboard.
                    </p>
                </div>

            </div>

        </section>

        <!-- Account Questions -->
        <section class="common-section" id="account-security">

            <h2>Account & Security</h2>

            <div class="faq-container">

                <div class="faq-item">
                    <h3>1. What if I forget my password?</h3>
                    <p>
                        Use the Forgot Password option on the login
                        page to recover your account using registered
                        email or mobile authentication.
                    </p>
                </div>

                <div class="faq-item">
                    <h3>2. Can I change my password?</h3>
                    <p>
                        Logged-in users can change their password
                        through the Change Password option.
                    </p>
                </div>

                <div class="faq-item">
                    <h3>3. How can I contact support?</h3>
                    <p>
                        Visit our Contact Us or Help & Support page
                        to reach our support team.
                    </p>
                </div>

            </div>

        </section>

        <!-- CTA -->
        <section class="common-cta">

            <h2>Still Have Questions?</h2>

            <p>
                Our support team is here to help you.
            </p>

            <div class="cta-actions">
                <a href="contact.php">Contact Us</a>
                <a href="help.php">Visit Help & Support</a>
            </div>

        </section>

    </main>

    <!-- Footer -->
    <?php require __DIR__ . "/includes/footer.php"; ?>

</body>
</html>
