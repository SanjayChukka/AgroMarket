<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Market Prices | AgroMarket Connect</title>
    <link rel="stylesheet" href="./css/common.css">
<link rel="icon" href="../favicon.ico" type="image/x-icon">
</head>

<body>

    <!-- Header -->
    <?php
        $showMarketPrices = true;
        $registrationRole = null;
    require __DIR__ . "/includes/header.php"; ?>

    <main>

        <!-- Page Introduction -->
        <section class="common-hero">
            <h1>Agricultural Market Prices</h1>
            <p>
                Explore agricultural products and compare their
                indicative market prices.
            </p>
        </section>

        <!-- Search and Filter -->
        <section class="common-section">

            <h2>Explore Crop Prices</h2>

            <div class="market-filter">

                <input type="search"
                    id="cropSearch"
                    placeholder="Search crops...">

                <select id="categoryFilter">
                    <option value="all">All Categories</option>
                    <option value="vegetables">Vegetables</option>
                    <option value="fruits">Fruits</option>
                    <option value="grains">Grains</option>
                    <option value="pulses">Pulses</option>
                </select>


                <select id="locationFilter">
                    <option value="all">All Locations</option>
                    <option value="adilabad">Adilabad</option>
                    <option value="bhadradri kothagudem">Bhadradri Kothagudem</option>
                    <option value="hanumakonda">Hanumakonda</option>
                    <option value="hyderabad">Hyderabad</option>
                    <option value="jagtial">Jagtial</option>
                    <option value="jangaon">Jangaon</option>
                    <option value="jayashankar bhupalpally">Jayashankar Bhupalpally</option>
                    <option value="jogulamba gadwal">Jogulamba Gadwal</option>
                    <option value="kamareddy">Kamareddy</option>
                    <option value="karimnagar">Karimnagar</option>
                    <option value="khammam">Khammam</option>
                    <option value="komaram bheem asifabad">Komaram Bheem Asifabad</option>
                    <option value="mahabubabad">Mahabubabad</option>
                    <option value="mahabubnagar">Mahabubnagar</option>
                    <option value="mancherial">Mancherial</option>
                    <option value="medak">Medak</option>
                    <option value="medchal malkajgiri">Medchal Malkajgiri</option>
                    <option value="mulugu">Mulugu</option>
                    <option value="nagarkurnool">Nagarkurnool</option>
                    <option value="nalgonda">Nalgonda</option>
                    <option value="narayanpet">Narayanpet</option>
                    <option value="nirmal">Nirmal</option>
                    <option value="nizamabad">Nizamabad</option>
                    <option value="peddapalli">Peddapalli</option>
                    <option value="rajanna sircilla">Rajanna Sircilla</option>
                    <option value="rangareddy">Rangareddy</option>
                    <option value="sangareddy">Sangareddy</option>
                    <option value="siddipet">Siddipet</option>
                    <option value="suryapet">Suryapet</option>
                    <option value="vikarabad">Vikarabad</option>
                    <option value="wanaparthy">Wanaparthy</option>
                    <option value="warangal">Warangal</option>
                    <option value="yadadri bhuvanagiri">Yadadri Bhuvanagiri</option>
                </select>


            </div>

            <!-- Product Cards -->
            <div class="market-products">

                <!-- Tomato -->
                <div class="market-card"
                    data-category="vegetables"
                    data-location="hyderabad">

                    <img src="../images/market/tomato.jpg"
                        alt="Fresh tomatoes">

                    <div class="market-card-content">
                        <span class="market-category">Vegetables</span>
                        <h3><a class="crop-offer-link" href="crop_offers.php?crop=Tomato">Tomato</a></h3>

                        <p class="market-price">₹25 / kg</p>

                        <p>Market: Hyderabad</p>
                        <p>Sample market price</p>
                        <strong>View farmer offers →</strong>
                    </div>

                </div>

                <!-- Onion -->
                <div class="market-card"
                    data-category="vegetables"
                    data-location="warangal">

                    <img src="../images/market/onion.jpg"
                        alt="Fresh onions">

                    <div class="market-card-content">
                        <span class="market-category">Vegetables</span>
                        <h3><a class="crop-offer-link" href="crop_offers.php?crop=Onion">Onion</a></h3>

                        <p class="market-price">₹30 / kg</p>

                        <p>Market: Warangal</p>
                        <p>Sample market price</p>
                        <strong>View farmer offers →</strong>
                    </div>

                </div>

                <!-- Potato -->
                <div class="market-card"
                    data-category="vegetables"
                    data-location="karimnagar">

                    <img src="../images/market/potato.jpg"
                        alt="Fresh potatoes">

                    <div class="market-card-content">
                        <span class="market-category">Vegetables</span>
                        <h3><a class="crop-offer-link" href="crop_offers.php?crop=Potato">Potato</a></h3>

                        <p class="market-price">₹22 / kg</p>

                        <p>Market: Karimnagar</p>
                        <p>Sample market price</p>
                        <strong>View farmer offers →</strong>
                    </div>

                </div>

                <!-- Rice -->
                <div class="market-card"
                    data-category="grains"
                    data-location="nizamabad">

                    <img src="../images/market/rice.jpg"
                        alt="Rice grains">

                    <div class="market-card-content">
                        <span class="market-category">Grains</span>
                        <h3><a class="crop-offer-link" href="crop_offers.php?crop=Rice">Rice</a></h3>

                        <p class="market-price">₹45 / kg</p>

                        <p>Market: Nizamabad</p>
                        <p>Sample market price</p>
                        <strong>View farmer offers →</strong>
                    </div>

                </div>

                <!-- Maize -->
                <div class="market-card"
                    data-category="grains"
                    data-location="warangal">

                    <img src="../images/market/maize.jpg"
                        alt="Maize grains">

                    <div class="market-card-content">
                        <span class="market-category">Grains</span>
                        <h3><a class="crop-offer-link" href="crop_offers.php?crop=Maize">Maize</a></h3>

                        <p class="market-price">₹28 / kg</p>

                        <p>Market: Warangal</p>
                        <p>Sample market price</p>
                        <strong>View farmer offers →</strong>
                    </div>

                </div>

                <!-- Mango -->
                <div class="market-card"
                    data-category="fruits"
                    data-location="hyderabad">

                    <img src="../images/market/mango.jpg"
                        alt="Fresh mangoes">

                    <div class="market-card-content">
                        <span class="market-category">Fruits</span>
                        <h3><a class="crop-offer-link" href="crop_offers.php?crop=Mango">Mango</a></h3>

                        <p class="market-price">₹60 / kg</p>

                        <p>Market: Hyderabad</p>
                        <p>Sample market price</p>
                        <strong>View farmer offers →</strong>
                    </div>

                </div>

            </div>

            <p id="noProducts" hidden>No matching products found.</p>

        </section>

        <!-- Price Information -->
        <section class="common-section">
            <h2>About Market Prices</h2>

            <p>
                Market prices displayed here are sample values for
                demonstrating the page layout. Actual prices may vary
                depending on location, quality, quantity and market demand.
            </p>

            <p>
                Always confirm the latest price with the seller or
                relevant market before making a purchase.
            </p>
        </section>

        <!-- CTA -->
        <section class="common-cta">
            <h2>Looking to Buy Agricultural Products?</h2>
            <p>Explore products listed by farmers on our platform.</p>
            <a href="../auth/register.php">Join AgroMarket Connect</a>
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
                <a href="partners.php">Our Partners</a>
            </div>

            <div>
                <h3>Market</h3>
                <a href="market_prices.php">Market Prices</a>
                <a href="how_it_works.php">How It Works</a>
            </div>

            <div>
                <h3>Support</h3>
                <a href="faq.php">FAQs</a>
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

    <!-- Basic Search and Filter -->
    <script src="js/market_prices.js" defer></script>

</body>

</html>