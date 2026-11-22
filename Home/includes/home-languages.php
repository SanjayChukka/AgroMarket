<?php
$homeLanguages = ['en' => 'English', 'hi' => 'हिन्दी', 'te' => 'తెలుగు'];
$homeLanguage = $_SESSION['home_language'] ?? $_COOKIE['agromarket_language'] ?? 'en';
if (!is_string($homeLanguage) || !isset($homeLanguages[$homeLanguage])) {
    $homeLanguage = 'en';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['home_language'])) {
    $choice = $_POST['home_language'];
    if (is_string($choice) && isset($homeLanguages[$choice])) {
        $_SESSION['home_language'] = $choice;
        setcookie('agromarket_language', $choice, [
            'expires' => time() + 31536000,
            'path' => rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    $params = array_intersect_key($_GET, ['q' => true, 'page' => true]);
    $suffix = http_build_query($params);
    header('Location: index.php' . ($suffix === '' ? '' : '?' . $suffix . '#marketplace'), true, 303);
    exit;
}
$homeCopy = [
    'Marketplace' => ['hi' => 'कृषि बाज़ार', 'te' => 'వ్యవసాయ మార్కెట్'],
    'Language' => ['hi' => 'भाषा', 'te' => 'భాష'],
    'Apply' => ['hi' => 'लागू करें', 'te' => 'వర్తింపజేయండి'],
    'Crop or farmer name' => ['hi' => 'फसल या किसान का नाम', 'te' => 'పంట లేదా రైతు పేరు'],
    'Search crops or farmers' => ['hi' => 'फसल या किसान खोजें', 'te' => 'పంట లేదా రైతు కోసం వెతకండి'],
    'Search' => ['hi' => 'खोजें', 'te' => 'వెతకండి'],
    'Sign In' => ['hi' => 'लॉग इन करें', 'te' => 'లాగిన్ అవ్వండి'],
    'Sign Up' => ['hi' => 'पंजीकरण करें', 'te' => 'నమోదు చేసుకోండి'],
    'Now Available in 3 Languages' => ['hi' => 'अब 3 भाषाओं में उपलब्ध', 'te' => 'ఇప్పుడు 3 భాషల్లో అందుబాటులో ఉంది'],
    'Empowering Farmers,' => ['hi' => 'किसानों को सशक्त बनाएँ,', 'te' => 'రైతులకు అండగా,'],
    'Feeding the Nation' => ['hi' => 'देश का पेट भरें', 'te' => 'దేశానికి అన్నం అందిద్దాం'],
    'The first multilingual agricultural marketplace connecting small farmers directly with buyers. Fair prices, transparent trading, sustainable growth.' => ['hi' => 'एक बहुभाषी कृषि बाज़ार जहाँ छोटे किसान सीधे खरीदारों से जुड़ते हैं। उचित दाम, पारदर्शी व्यापार और टिकाऊ विकास।', 'te' => 'చిన్న రైతులను నేరుగా కొనుగోలుదారులతో కలిపే బహుభాషా వ్యవసాయ మార్కెట్. సరైన ధరలు, పారదర్శక వ్యాపారం, సుస్థిర అభివృద్ధి.'],
    'Start Selling as Farmer' => ['hi' => 'किसान के रूप में बिक्री शुरू करें', 'te' => 'రైతుగా అమ్మకాలు ప్రారంభించండి'],
    'Start Buying' => ['hi' => 'खरीदारी शुरू करें', 'te' => 'కొనుగోలు ప్రారంభించండి'],
    'Hands gathering leafy vegetables in a woven harvest basket' => ['hi' => 'बुनी हुई टोकरी में पत्तेदार सब्ज़ियाँ रखते हाथ', 'te' => 'అల్లిన బుట్టలో ఆకుకూరలు పెడుతున్న చేతులు'],
    'Registered Farmers' => ['hi' => 'पंजीकृत किसान', 'te' => 'నమోదైన రైతులు'],
    'Active Buyers' => ['hi' => 'सक्रिय खरीदार', 'te' => 'క్రియాశీల కొనుగోలుదారులు'],
    '₹100 Cr+' => ['hi' => '₹100 करोड़ से अधिक', 'te' => '₹100 కోట్లకు పైగా'],
    'Total Transactions' => ['hi' => 'कुल लेनदेन मूल्य', 'te' => 'మొత్తం లావాదేవీల విలువ'],
    'Crops Listed Daily' => ['hi' => 'रोज़ सूचीबद्ध फ़सलें', 'te' => 'రోజూ నమోదయ్యే పంటలు'],
    'Why Choose AgroMarket Connect?' => ['hi' => 'AgroMarket Connect क्यों चुनें?', 'te' => 'AgroMarket Connect ఎందుకు ఎంచుకోవాలి?'],
    'Built specifically for Indian agriculture with features that matter to farmers and buyers.' => ['hi' => 'भारतीय कृषि के लिए बनाया गया यह बाज़ार किसानों और खरीदारों की ज़रूरतों को ध्यान में रखता है।', 'te' => 'భారతీయ రైతులు, కొనుగోలుదారుల అవసరాలకు అనుగుణంగా రూపొందించిన వేదిక.'],
    'Direct Farm-to-Market' => ['hi' => 'खेत से सीधे बाज़ार तक', 'te' => 'పొలం నుంచి నేరుగా మార్కెట్‌కు'],
    'Connect farmers directly with buyers without unnecessary intermediaries.' => ['hi' => 'किसान बिना बिचौलियों के सीधे खरीदारों से जुड़ सकते हैं।', 'te' => 'రైతులు మధ్యవర్తులు లేకుండా నేరుగా కొనుగోలుదారులను సంప్రదించవచ్చు.'],
    'Live Market Prices' => ['hi' => 'बाज़ार के ताज़ा भाव', 'te' => 'తాజా మార్కెట్ ధరలు'],
    'Access transparent market prices and make better trading decisions.' => ['hi' => 'बाज़ार के भाव देखकर खरीद-बिक्री के बेहतर फ़ैसले लें।', 'te' => 'మార్కెట్ ధరలు చూసి కొనుగోలు, అమ్మకాలపై నిర్ణయం తీసుకోండి.'],
    'Secure Transactions' => ['hi' => 'सुरक्षित लेनदेन', 'te' => 'సురక్షిత లావాదేవీలు'],
    'Trade confidently with secure transactions and reliable connections.' => ['hi' => 'भरोसेमंद संपर्कों और सुरक्षित लेनदेन के साथ निश्चिंत होकर व्यापार करें।', 'te' => 'నమ్మకమైన పరిచయాలు, సురక్షిత లావాదేవీలతో ధైర్యంగా వ్యాపారం చేయండి.'],
    'Available Crops' => ['hi' => 'उपलब्ध फ़सलें', 'te' => 'అందుబాటులో ఉన్న పంటలు'],
    'Search Results' => ['hi' => 'खोज के नतीजे', 'te' => 'శోధన ఫలితాలు'],
    'Listings matching “%s”' => ['hi' => '“%s” से मिलती-जुलती फ़सलें', 'te' => '“%s” కోసం దొరికిన పంటలు'],
    'Clear search' => ['hi' => 'खोज हटाएँ', 'te' => 'శోధనను తొలగించండి'],
    'The crop catalog is temporarily unavailable. Please try again shortly.' => ['hi' => 'फ़सलों की सूची अभी उपलब्ध नहीं है। थोड़ी देर बाद फिर कोशिश करें।', 'te' => 'పంటల జాబితా ప్రస్తుతం అందుబాటులో లేదు. కాసేపటి తర్వాత మళ్లీ ప్రయత్నించండి.'],
    'No available crops on this page.' => ['hi' => 'इस पृष्ठ पर कोई फ़सल उपलब्ध नहीं है।', 'te' => 'ఈ పేజీలో పంటలు అందుబాటులో లేవు.'],
    'No matches on this page. Try another crop or farmer name.' => ['hi' => 'इस पृष्ठ पर कोई नतीजा नहीं मिला। दूसरी फ़सल या किसी और किसान का नाम खोजें।', 'te' => 'ఈ పేజీలో ఫలితాలు లేవు. మరో పంట లేదా రైతు పేరుతో వెతకండి.'],
    'No photo available' => ['hi' => 'तस्वीर उपलब्ध नहीं है', 'te' => 'ఫోటో అందుబాటులో లేదు'],
    'View details' => ['hi' => 'विवरण देखें', 'te' => 'వివరాలు చూడండి'],
    'Available:' => ['hi' => 'उपलब्ध मात्रा:', 'te' => 'అందుబాటులో ఉన్న పరిమాణం:'],
    'Place order' => ['hi' => 'ऑर्डर दें', 'te' => 'ఆర్డర్ చేయండి'],
    'Sign in as buyer to order' => ['hi' => 'ऑर्डर देने के लिए खरीदार के रूप में लॉग इन करें', 'te' => 'ఆర్డర్ చేయడానికి కొనుగోలుదారుగా లాగిన్ అవ్వండి'],
    'Crop result pages' => ['hi' => 'फ़सल खोज के पृष्ठ', 'te' => 'పంటల శోధన ఫలితాల పేజీలు'],
    'Previous' => ['hi' => 'पिछला', 'te' => 'మునుపటి'],
    'Page %s' => ['hi' => 'पृष्ठ %s', 'te' => 'పేజీ %s'],
    'Next' => ['hi' => 'अगला', 'te' => 'తదుపరి'],
    'How AgroMarket Connect Works' => ['hi' => 'AgroMarket Connect कैसे काम करता है?', 'te' => 'AgroMarket Connect ఎలా పనిచేస్తుంది?'],
    'A simple process connecting farmers and buyers.' => ['hi' => 'किसानों और खरीदारों को जोड़ने की आसान प्रक्रिया।', 'te' => 'రైతులను, కొనుగోలుదారులను కలిపే సులభమైన విధానం.'],
    'Farmer Registration' => ['hi' => 'किसान पंजीकरण', 'te' => 'రైతు నమోదు'],
    'Farmers register and create their profiles.' => ['hi' => 'किसान पंजीकरण करके अपनी प्रोफ़ाइल बनाते हैं।', 'te' => 'రైతులు నమోదు చేసుకుని తమ ప్రొఫైల్‌ను రూపొందిస్తారు.'],
    'List Your Crops' => ['hi' => 'अपनी फ़सलें सूचीबद्ध करें', 'te' => 'మీ పంటలను నమోదు చేయండి'],
    'Farmers add crop details, quantity and prices.' => ['hi' => 'किसान फ़सल का विवरण, मात्रा और दाम दर्ज करते हैं।', 'te' => 'రైతులు పంట వివరాలు, పరిమాణం, ధర నమోదు చేస్తారు.'],
    'Connect with Buyers' => ['hi' => 'खरीदारों से जुड़ें', 'te' => 'కొనుగోలుదారులతో కలవండి'],
    'Buyers explore available crops and contact farmers.' => ['hi' => 'खरीदार उपलब्ध फ़सलें देखकर किसानों से संपर्क करते हैं।', 'te' => 'కొనుగోలుదారులు అందుబాటులో ఉన్న పంటలు చూసి రైతులను సంప్రదిస్తారు.'],
    'Complete Trading' => ['hi' => 'सौदा पूरा करें', 'te' => 'వ్యాపారం పూర్తి చేయండి'],
    'Farmers and buyers coordinate and complete transactions.' => ['hi' => 'किसान और खरीदार मिलकर लेनदेन पूरा करते हैं।', 'te' => 'రైతులు, కొనుగోలుదారులు పరస్పరం మాట్లాడుకుని లావాదేవీ పూర్తి చేస్తారు.'],
    'Grow Together with AgroMarket Connect' => ['hi' => 'AgroMarket Connect के साथ मिलकर आगे बढ़ें', 'te' => 'AgroMarket Connectతో కలిసి ఎదగండి'],
    'Whether you are a farmer looking for better markets or a buyer searching for quality agricultural products, AgroMarket Connect brings you together.' => ['hi' => 'चाहे आप बेहतर बाज़ार ढूँढ़ रहे किसान हों या अच्छी उपज तलाश रहे खरीदार, AgroMarket Connect आपको एक-दूसरे से जोड़ता है।', 'te' => 'మంచి మార్కెట్ కోసం చూస్తున్న రైతులనూ, నాణ్యమైన పంటల కోసం వెతుకుతున్న కొనుగోలుదారులనూ AgroMarket Connect ఒకే వేదికపై కలుపుతుంది.'],
    'Join as Farmer' => ['hi' => 'किसान के रूप में जुड़ें', 'te' => 'రైతుగా చేరండి'],
    'Join as Buyer' => ['hi' => 'खरीदार के रूप में जुड़ें', 'te' => 'కొనుగోలుదారుగా చేరండి'],
    'ABOUT AGROMARKET' => ['hi' => 'AgroMarket के बारे में', 'te' => 'AgroMarket గురించి'],
    'About Us' => ['hi' => 'हमारे बारे में', 'te' => 'మా గురించి'],
    'Our Mission' => ['hi' => 'हमारा उद्देश्य', 'te' => 'మా లక్ష్యం'],
    'How It Works' => ['hi' => 'यह कैसे काम करता है', 'te' => 'ఇది ఎలా పనిచేస్తుంది'],
    'Our Partners' => ['hi' => 'हमारे सहयोगी', 'te' => 'మా భాగస్వాములు'],
    'Contact Us' => ['hi' => 'हमसे संपर्क करें', 'te' => 'మమ్మల్ని సంప్రదించండి'],
    'FOR FARMERS' => ['hi' => 'किसानों के लिए', 'te' => 'రైతుల కోసం'],
    'Register as Farmer' => ['hi' => 'किसान पंजीकरण', 'te' => 'రైతుగా నమోదు చేసుకోండి'],
    'Manage Listings' => ['hi' => 'अपनी फ़सलों की सूची सँभालें', 'te' => 'పంటల జాబితాను నిర్వహించండి'],
    'Market Price Updates' => ['hi' => 'बाज़ार भाव की जानकारी', 'te' => 'మార్కెట్ ధరల సమాచారం'],
    'Farmer Guidelines' => ['hi' => 'किसानों के लिए दिशा-निर्देश', 'te' => 'రైతులకు మార్గదర్శకాలు'],
    'FOR BUYERS' => ['hi' => 'खरीदारों के लिए', 'te' => 'కొనుగోలుదారుల కోసం'],
    'Browse Crops' => ['hi' => 'फ़सलें देखें', 'te' => 'పంటలను చూడండి'],
    'Find Farmers' => ['hi' => 'किसान खोजें', 'te' => 'రైతులను వెతకండి'],
    'Place Orders' => ['hi' => 'ऑर्डर दें', 'te' => 'ఆర్డర్లు చేయండి'],
    'Track Orders' => ['hi' => 'ऑर्डर की स्थिति देखें', 'te' => 'ఆర్డర్ల స్థితి చూడండి'],
    'Buyer Guidelines' => ['hi' => 'खरीदारों के लिए दिशा-निर्देश', 'te' => 'కొనుగోలుదారులకు మార్గదర్శకాలు'],
    'SUPPORT & POLICIES' => ['hi' => 'सहायता और नीतियाँ', 'te' => 'సహాయం మరియు విధానాలు'],
    'Help Center' => ['hi' => 'सहायता केंद्र', 'te' => 'సహాయ కేంద్రం'],
    'FAQs' => ['hi' => 'अक्सर पूछे जाने वाले प्रश्न', 'te' => 'తరచుగా అడిగే ప్రశ్నలు'],
    'Terms & Conditions' => ['hi' => 'नियम और शर्तें', 'te' => 'నిబంధనలు మరియు షరతులు'],
    'Privacy Policy' => ['hi' => 'गोपनीयता नीति', 'te' => 'గోప్యతా విధానం'],
    'Cancellation Policy' => ['hi' => 'रद्द करने की नीति', 'te' => 'రద్దు విధానం'],
    'Grievance Redressal' => ['hi' => 'शिकायत निवारण', 'te' => 'ఫిర్యాదుల పరిష్కారం'],
    'CONTACT US' => ['hi' => 'संपर्क करें', 'te' => 'సంప్రదించండి'],
    'Hyderabad, Telangana, India' => ['hi' => 'हैदराबाद, तेलंगाना, भारत', 'te' => 'హైదరాబాద్, తెలంగాణ, భారతదేశం'],
    'Email:' => ['hi' => 'ईमेल:', 'te' => 'ఈమెయిల్:'],
    'Phone:' => ['hi' => 'फ़ोन:', 'te' => 'ఫోన్:'],
    'All Rights Reserved.' => ['hi' => 'सर्वाधिकार सुरक्षित।', 'te' => 'సర్వ హక్కులు ప్రత్యేకించబడ్డాయి.'],
    'Empowering Farmers, Feeding the Nation.' => ['hi' => 'किसानों को सशक्त बनाएँ, देश का पेट भरें।', 'te' => 'రైతులకు అండగా, దేశానికి అన్నం అందిద్దాం.'],
];
$t = static function (string $english) use ($homeCopy, $homeLanguage): string {
    return $homeCopy[$english][$homeLanguage] ?? $english;
};
