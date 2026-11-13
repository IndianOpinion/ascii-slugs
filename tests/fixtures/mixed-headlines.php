<?php

/**
 * Mixed-script headlines, written for this test suite, run through the
 * default options with intl off. Each entry is [headline, expected slug],
 * where "hashed" means the hash fallback is expected.
 *
 * The set backs the 0.5 default for minCoverage (docs/ARCHITECTURE.md, D3).
 */

declare(strict_types=1);

return [
    // Hindi with English words and figures.
    ['ISRO का चंद्रयान-3 मिशन सफल', 'isro-ka-chandrayan-3-mishan-saphal'],
    ['RBI ने रेपो रेट में बदलाव नहीं किया', 'rbi-ne-repo-ret-men-badalav-nahin-kiya'],
    ['IPL 2026: मुंबई इंडियंस की लगातार तीसरी जीत', 'ipl-2026-mumbai-indiyans-ki-lagatar-tisari-jit'],
    ['दिल्ली में AQI 400 के पार, स्कूल बंद', 'dilli-men-aqi-400-ke-par-skul-band'],
    ['G20 शिखर सम्मेलन से पहले तैयारी तेज', 'g20-shikhar-sammelan-se-pahale-taiyari-tej'],
    ['UPI लेनदेन ने नया रिकॉर्ड बनाया', 'upi-lenaden-ne-naya-rikord-banaya'],
    ['बजट २०२६: टैक्स स्लैब में राहत', 'bajat-2026-taiks-slaib-men-rahat'],
    ['मोदी&#8217;s &quot;मन की बात&quot; का नया एपिसोड', 'modis-man-ki-bat-ka-naya-episod'],
    // Marathi, Nepali, Sanskrit.
    ['मुंबईत मुसळधार पाऊस, लोकल सेवा विस्कळीत', 'mumbait-musaladhar-paus-lokal-seva-viskalit'],
    ['काठमाडौंमा भूकम्पको धक्का', 'kathamadaumma-bhukampako-dhakka'],
    ['सत्यमेव जयते', 'satyamev-jayate'],
    // Bengali and Assamese.
    ['কলকাতায় দুর্গাপূজার প্রস্তুতি শুরু', 'kalakatay-durgapujar-prastuti-shuru'],
    ['ISL ফাইনালে মোহনবাগান', 'isl-phainale-mohanabagan'],
    ['গুৱাহাটীত বানপানী', 'guwahatit-banapani'],
    // Tamil.
    ['சென்னையில் கனமழை எச்சரிக்கை', 'chennaiyil-kanamazhai-echcharikkai'],
    ['IIT Madras மாணவர்கள் புதிய செயற்கைக்கோள்', 'iit-madras-manavarkal-putiya-cheyarkaikkol'],
    ['௨௦௨௬ தேர்தல் முடிவுகள்', '2026-tertal-mutivukal'],
    // Telugu, with a ZWNJ before the case ending.
    ["హైదరాబాద్\u{200C}లో మెట్రో విస్తరణ", 'haidarabadlo-metro-vistarana'],
    ['ISRO ప్రయోగం విజయవంతం', 'isro-prayogam-vijayavantam'],
    // Kannada, Malayalam, Gujarati, Gurmukhi, Odia.
    ['ಬೆಂಗಳೂರಿನಲ್ಲಿ ಟ್ರಾಫಿಕ್ ಸಮಸ್ಯೆ', 'bengalurinalli-traphik-samasye'],
    ['കൊച്ചി മെട്രോ രണ്ടാം ഘട്ടം', 'kochchi-metro-rantam-ghattam'],
    ['અમદાવાદમાં નવરાત્રિની ધૂમ', 'amadavadaman-navaratrini-dhum'],
    ['ਪੰਜਾਬ ਵਿੱਚ ਕਿਸਾਨਾਂ ਦਾ ਧਰਨਾ', 'panjab-vichch-kisanan-da-dharana'],
    ['ଭୁବନେଶ୍ୱରରେ ରଥଯାତ୍ରା ପ୍ରସ୍ତୁତି', 'bhubaneshwarare-rathajatra-prastuti'],
    // Urdu and Arabic.
    ['لاہور میں بارش', 'lahor-mein-barsh'],
    ['پاکستان اور بھارت کے درمیان مذاکرات', 'pakstan-aur-bharat-ke-drmian-mzakrat'],
    ['T20 ورلڈ کپ کا شیڈول جاری', 't20-wrld-kp-ka-shidol-jari'],
    ['القاهرة تستضيف قمة', 'alqahrh-tstzif-qmh'],
    // Two covered scripts in one headline.
    ['हिंदी लेख में রবীন্দ্রনাথ का उल्लेख', 'hindi-lekh-men-rabindranath-ka-ullekh'],
    ['उर्दू शायरी: غالب की ग़ज़लें', 'urdu-shayari-ghalb-ki-ghazalen'],
    ['తెలుగు మరియు தமிழ் సినిమా', 'telugu-mariyu-tamizh-sinima'],
    // Cyrillic, Greek, accented Latin.
    ['Путин и Моди обсудили торговлю', 'putin-i-modi-obsudili-torgovlyu'],
    ['Київ і Львів', 'kiyiv-i-lviv'],
    ['Η Αθήνα φιλοξενεί σύνοδο', 'i-athina-filoxenei-synodo'],
    ['São Paulo e Zürich: café com Ørsted', 'sao-paulo-e-zurich-cafe-com-orsted'],
    // Scripts with no built-in table, mixed with English.
    ['Bangkok ข่าวด่วนวันนี้ตลาดหุ้น', 'hashed'],
    ['ประเทศไทย 2026 election', 'hashed'],
    ['Modi meets 习近平 in Kazan', 'modi-meets-in-kazan'],
    ['北京 Olympic 2026', 'olympic-2026'],
    ['東京オリンピック', 'hashed'],
    ['BTS 서울 콘서트 매진', 'hashed'],
    ["ශ්\u{200D}රී ලංකා ක්\u{200D}රිකට්", 'hashed'],
    ['ירושלים news', 'hashed'],
    ['Sri Lanka names ශ්‍රී captain for tour', 'sri-lanka-names-captain-for-tour'],
    ['Tibet ལྷ་ས', 'tibet'],
];
