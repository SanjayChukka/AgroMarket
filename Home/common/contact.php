<?php
$isGrievance = false;
require __DIR__ . '/../includes/support-intake.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | AgroMarket Connect</title>
    <link rel="stylesheet" href="./css/common.css">
    <link rel="stylesheet" href="./css/support.css">
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
                    <img class="support-photo" src="../images/editorial/grower.webp" alt="A grower in the field" loading="lazy">

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
                        <p>+91 7032412044</p>
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

                    <form id="support-form" action="#support-form" method="POST" enctype="multipart/form-data">
                        <?php if ($error): ?><p role="alert"><?= e($error) ?></p><?php endif; ?>
                        <?php if ($notice): ?><p role="status"><?= e($notice) ?></p><?php endif; ?>
                        <?php if ($owner): ?><p><a href="../<?= e($portalRole) ?>/complaints.php">My Complaints</a></p><?php endif; ?>
                        <input type="hidden" name="support_token" value="<?= e($_SESSION['support_token']) ?>">

                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="<?= e(supportField('name')) ?>" required
                                placeholder="Enter your full name">
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?= e(supportField('email')) ?>" required
                                placeholder="Enter your email address">
                        </div>

                        <div class="form-group">
                            <label for="mobile">Mobile Number</label>
                            <input type="tel" id="mobile" name="mobile" value="<?= e(supportField('mobile')) ?>"
                                placeholder="Enter your mobile number">
                        </div>

                        <div class="form-group">
                            <label for="user_type">I am a</label>
                            <select required id="user_type" name="user_type">
                                <option value="">Select user type</option>
                                <option value="farmer" <?= ($owner ? $portalRole : supportField('user_type')) === 'farmer' ? 'selected' : '' ?>>Farmer</option>
                                <option value="buyer" <?= ($owner ? $portalRole : supportField('user_type')) === 'buyer' ? 'selected' : '' ?>>Buyer</option>
                                <option value="other" <?= ($owner ? $portalRole : supportField('user_type')) === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <select required id="subject" name="subject">
                                <option value="">Select enquiry type</option>
                                <option value="account" <?= supportField('subject') === 'account' ? 'selected' : '' ?>>Account Related</option>
                                <option value="product" <?= supportField('subject') === 'product' ? 'selected' : '' ?>>Product Related</option>
                                <option value="order" <?= supportField('subject') === 'order' ? 'selected' : '' ?>>Order Related</option>
                                <option value="technical" <?= supportField('subject') === 'technical' ? 'selected' : '' ?>>Technical Support</option>
                                <option value="other" <?= supportField('subject') === 'other' ? 'selected' : '' ?>>Other Enquiry</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="message">Your Message</label>
                            <textarea id="message" name="message"
                                rows="5"
                                placeholder="Write your message here" required><?= e(supportField('message')) ?></textarea>
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
    <?php require __DIR__ . "/includes/footer.php"; ?>

</body>

</html>