<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | AgroMarket Connect</title>
    <link rel="stylesheet" href="./css/common.css">
</head>

<body>

    <!-- Header -->
    <header>
        <nav class="navbar">

            <div class="logo">
                <a href="../index.php">AgroMarket Connect</a>
            </div>

            <ul class="nav-links">
                <li><a href="../index.php">Home</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="how_it_works.php">How It Works</a></li>
                <li><a href="faq.php">FAQ</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>

            <div class="nav-actions">
                <a href="../auth/login.php">Login</a>
                <a href="../auth/register.php">Register</a>
            </div>

        </nav>
    </header>

    <main>

        <!-- Page Introduction -->
        <section class="common-hero">
            <h1>Contact Us</h1>
            <p>
                Have a question or need assistance?
                Get in touch with AgroMarket Connect.
            </p>
        </section>

        <!-- Contact Information and Form -->
        <section class="common-section">

            <div class="contact-container">

                <!-- Contact Information -->
                <div class="contact-info">

                    <h2>Get in Touch</h2>

                    <p>
                        We are here to help farmers, buyers and
                        visitors with their questions and enquiries.
                    </p>

                    <div class="contact-detail">
                        <h3>Email Address</h3>
                        <p>support@agromarketconnect.com</p>
                    </div>

                    <div class="contact-detail">
                        <h3>Phone Number</h3>
                        <p>+91 XXXXXXXXXX</p>
                    </div>

                    <div class="contact-detail">
                        <h3>Address</h3>
                        <p>Hyderabad, Telangana, India</p>
                    </div>

                    <div class="contact-detail">
                        <h3>Support Hours</h3>
                        <p>Monday - Saturday</p>
                    </div>

                </div>

                <!-- Contact Form -->
                <div class="contact-form-container">

                    <h2>Send Us a Message</h2>

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
                            <label for="user_type">I am a</label>
                            <select id="user_type" name="user_type">
                                <option value="">Select user type</option>
                                <option value="farmer">Farmer</option>
                                <option value="buyer">Buyer</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <select id="subject" name="subject">
                                <option value="">Select enquiry type</option>
                                <option value="account">Account Related</option>
                                <option value="product">Product Related</option>
                                <option value="order">Order Related</option>
                                <option value="technical">Technical Support</option>
                                <option value="other">Other Enquiry</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="message">Your Message</label>
                            <textarea id="message" name="message"
                                rows="5"
                                placeholder="Write your message here"></textarea>
                        </div>

                        <button type="submit">Submit Message</button>

                    </form>

                </div>

            </div>

        </section>

        <!-- Additional Support -->
        <section class="common-cta">

            <h2>Need Quick Assistance?</h2>

            <p>
                Check our frequently asked questions or visit
                the Help & Support section.
            </p>

            <div class="cta-actions">
                <a href="faq.php">View FAQs</a>
                <a href="help.php">Help & Support</a>
            </div>

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
                <a href="faq.php">FAQ</a>
                <a href="help.php">Help & Support</a>
                <a href="contact.php">Contact Us</a>
            </div>

            <div>
                <h3>Legal</h3>
                <a href="terms.php">Terms & Conditions</a>
                <a href="privacy.php">Privacy Policy</a>
            </div>

        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 AgroMarket Connect. All rights reserved.</p>
        </div>

    </footer>

</body>

</html>