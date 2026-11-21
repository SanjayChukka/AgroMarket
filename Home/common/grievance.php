<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grievance Redressal | AgroMarket Connect</title>
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
            <h1>Grievance Redressal</h1>
            <p>
                We value your feedback and are committed to
                addressing user concerns responsibly.
            </p>
        </section>

        <!-- Introduction -->
        <section class="common-section">

            <h2>Our Commitment</h2>

            <p>
                AgroMarket Connect aims to provide a reliable
                marketplace experience for farmers and buyers.
                Users can raise concerns regarding accounts,
                products, orders and platform services.
            </p>

        </section>

        <!-- Types of Grievances -->
        <section class="common-section">

            <h2>Types of Grievances</h2>

            <div class="grievance-container">

                <div class="grievance-item">
                    <h3>Account Issues</h3>
                    <p>
                        Registration problems, login difficulties
                        and unauthorized account access.
                    </p>
                </div>

                <div class="grievance-item">
                    <h3>Product Issues</h3>
                    <p>
                        Incorrect product details, misleading prices
                        or unavailable product listings.
                    </p>
                </div>

                <div class="grievance-item">
                    <h3>Order Issues</h3>
                    <p>
                        Order cancellations, fulfilment problems
                        and disagreements between users.
                    </p>
                </div>

                <div class="grievance-item">
                    <h3>Technical Issues</h3>
                    <p>
                        Website errors, access problems and
                        other technical difficulties.
                    </p>
                </div>

            </div>

        </section>

        <!-- Grievance Process -->
        <section class="common-section">

            <h2>Grievance Resolution Process</h2>

            <div class="grievance-steps">

                <div class="grievance-step">
                    <h3>Step 1: Submit Your Complaint</h3>
                    <p>
                        Provide your details and explain
                        the issue clearly.
                    </p>
                </div>

                <div class="grievance-step">
                    <h3>Step 2: Complaint Review</h3>
                    <p>
                        The support team reviews the complaint
                        and available information.
                    </p>
                </div>

                <div class="grievance-step">
                    <h3>Step 3: Investigation</h3>
                    <p>
                        Additional information may be requested
                        when required.
                    </p>
                </div>

                <div class="grievance-step">
                    <h3>Step 4: Resolution</h3>
                    <p>
                        The user is informed about the outcome
                        and any applicable next steps.
                    </p>
                </div>

            </div>

        </section>

        <!-- Grievance Submission Form -->
        <section class="common-section">

            <h2>Submit Your Grievance</h2>

            <div class="grievance-form-container">

                <form action="#" method="POST">

                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name"
                            placeholder="Enter your full name">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email"
                            placeholder="Enter your email address">
                    </div>

                    <div class="form-group">
                        <label for="mobile">Mobile Number</label>
                        <input type="tel" id="mobile" name="mobile"
                            placeholder="Enter your mobile number">
                    </div>

                    <div class="form-group">
                        <label for="user_type">User Type</label>
                        <select id="user_type" name="user_type">
                            <option value="">Select user type</option>
                            <option value="farmer">Farmer</option>
                            <option value="buyer">Buyer</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="grievance_type">Grievance Category</label>
                        <select id="grievance_type" name="grievance_type">
                            <option value="">Select category</option>
                            <option value="account">Account Issue</option>
                            <option value="product">Product Issue</option>
                            <option value="order">Order Issue</option>
                            <option value="payment">Payment Issue</option>
                            <option value="technical">Technical Issue</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="reference">Order / Reference ID (Optional)</label>
                        <input type="text" id="reference" name="reference"
                            placeholder="Enter order or reference ID">
                    </div>

                    <div class="form-group">
                        <label for="description">Complaint Description</label>
                        <textarea id="description" name="description"
                            rows="6"
                            placeholder="Describe your issue in detail"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="attachment">Supporting Document (Optional)</label>
                        <input type="file" id="attachment" name="attachment">
                    </div>

                    <button type="submit">Submit Grievance</button>

                </form>

            </div>

        </section>

        <!-- Contact Information -->
        <section class="common-cta">

            <h2>Need Immediate Assistance?</h2>

            <p>
                Contact our support team for further assistance
                with your concerns.
            </p>

            <a href="contact.php">Contact Support</a>

        </section>

    </main>

    <!-- Footer -->
    <footer class="footer">

        <div class="footer-content">

            <div>
                <h3>AgroMarket Connect</h3>
                <p>Connecting Farmers and Buyers.</p>
            </div>

            <div>
                <h3>Quick Links</h3>
                <a href="../index.php">Home</a>
                <a href="about.php">About Us</a>
                <a href="how_it_works.php">How It Works</a>
            </div>

            <div>
                <h3>Support</h3>
                <a href="faq.php">FAQs</a>
                <a href="help.php">Help Center</a>
                <a href="contact.php">Contact Us</a>
            </div>

            <div>
                <h3>Policies</h3>
                <a href="terms.php">Terms & Conditions</a>
                <a href="privacy.php">Privacy Policy</a>
                <a href="cancellation_policy.php">Cancellation Policy</a>
                <a href="grievance.php">Grievance Redressal</a>
            </div>

        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
        </div>

    </footer>

</body>

</html>