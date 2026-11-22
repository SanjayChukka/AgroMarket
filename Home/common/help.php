
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support | AgroMarket Connect</title>
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
            <h1>Help & Support</h1>
            <p>
                Need assistance? Find guidance and support
                for using AgroMarket Connect.
            </p>
        </section>

        <!-- Help Categories -->
        <section class="common-section">

            <h2>How Can We Help You?</h2>

            <div class="help-container">

                <div class="help-card">
                    <h3>Account & Registration</h3>
                    <p>
                        Get help with creating an account, logging in,
                        updating profile information and password recovery.
                    </p>
                    <a href="faq.php#account-security">View Account Help</a>
                </div>

                <div class="help-card">
                    <h3>Farmer Support</h3>
                    <p>
                        Learn how to add products, update listings,
                        manage product information and view orders.
                    </p>
                    <a href="faq.php#farmer-questions">View Farmer Help</a>
                </div>

                <div class="help-card">
                    <h3>Buyer Support</h3>
                    <p>
                        Get assistance with browsing products,
                        placing orders and managing purchases.
                    </p>
                    <a href="faq.php#buyer-questions">View Buyer Help</a>
                </div>

                <div class="help-card">
                    <h3>Orders & Transactions</h3>
                    <p>
                        Find information about order placement,
                        order status and order management.
                    </p>
                    <a href="faq.php">View Order FAQs</a>
                </div>

            </div>

        </section>

        <!-- Step-by-Step Guidance -->
        <section class="common-section">

            <h2>Getting Started</h2>

            <div class="help-steps">

                <div class="help-step">
                    <h3>1. Create Your Account</h3>
                    <p>
                        Register as a farmer or buyer by providing
                        the required information.
                    </p>
                </div>

                <div class="help-step">
                    <h3>2. Access Your Dashboard</h3>
                    <p>
                        Log in to access the features available
                        for your account type.
                    </p>
                </div>

                <div class="help-step">
                    <h3>3. Use the Marketplace</h3>
                    <p>
                        List agricultural products as a farmer
                        or explore products as a buyer.
                    </p>
                </div>

                <div class="help-step">
                    <h3>4. Get Assistance</h3>
                    <p>
                        If you face any issues, check our FAQs
                        or contact our support team.
                    </p>
                </div>

            </div>

        </section>

        <!-- Common Issues -->
        <section class="common-section">

            <h2>Common Issues</h2>

            <div class="issue-container">

                <div class="issue-item">
                    <h3>Unable to Login</h3>
                    <p>
                        Check your registered email or username
                        and password. If you forgot your password,
                        use the Forgot Password option.
                    </p>
                </div>

                <div class="issue-item">
                    <h3>OTP Not Received</h3>
                    <p>
                        Check your registered email or mobile number
                        and request another OTP if available.
                    </p>
                </div>

                <div class="issue-item">
                    <h3>Unable to Add a Product</h3>
                    <p>
                        Make sure all required product information
                        is provided correctly.
                    </p>
                </div>

                <div class="issue-item">
                    <h3>Order-Related Issues</h3>
                    <p>
                        Review your order details and contact
                        support if the issue continues.
                    </p>
                </div>

            </div>

        </section>

        <!-- FAQ Link -->
        <section class="common-section">

            <h2>Frequently Asked Questions</h2>

            <p>
                Find answers to common questions about account
                registration, product listings and orders.
            </p>

            <a href="faq.php">Visit FAQs</a>

        </section>

        <!-- Contact Support -->
        <section class="common-cta">

            <h2>Still Need Help?</h2>

            <p>
                Contact our support team for further assistance.
            </p>

            <div class="cta-actions">
                <a href="contact.php">Contact Support</a>
                <a href="../index.php">Back to Home</a>
            </div>

        </section>

    </main>

    <!-- Footer -->
    <?php require __DIR__ . "/includes/footer.php"; ?>

</body>
</html>
