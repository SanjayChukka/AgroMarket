<header>
        <nav class="navbar">
            <div class="logo">
                <?php require __DIR__ . '/../../includes/brand-home.php'; ?>
            </div>

            <ul class="nav-links">
                <li><a href="../index.php">Home</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="how_it_works.php">How It Works</a></li>
                <li><?php if ($showMarketPrices): ?><a href="market_prices.php">Market Prices</a><?php else: ?><a href="faq.php">FAQ</a><?php endif; ?></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>

            <div class="nav-actions">
                <a href="../auth/login.php">Login</a>
                <?php if ($registrationRole === 'farmer'): ?>
                    <a href="../auth/farmer_register.php">Register as Farmer</a>
                <?php elseif ($registrationRole === 'buyer'): ?>
                    <a href="../auth/buyer_register.php">Register as Buyer</a>
                <?php else: ?>
                    <a href="../auth/register.php">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
