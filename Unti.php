<?php
// تحديد المتغيرات الأساسية
 $pageTitle = "من نحن  - PSGOLD4GAMER";
 $pageDescription = "تواصل مع فريق PSGOLD4GAMER للحصول على الدعم أو طرح أي استفسارات حول ألعاب بلايستيشن.";
 $canonicalUrl = "https://PSGOLD4GAMER.com/contact.php";

// تعريف التصنيفات مرة واحدة فقط
 $categories = [
    'all' => 'الكل',
    'action' => 'أكشن',
    'rpg' => 'ألعاب تقمص أدوار',
    'racing' => 'سباقات',
    'sports' => 'رياضة',
    'adventure' => 'مغامرات',
    'horror' => 'رعب',
    'simulation' => 'محاكاة',
    'souls' => 'ألعاب Souls',
    'arabic' => 'ألعاب معربة',
    'hack-and-slash' => 'Hack and Slash',
    'survival' => 'بقاء',
    'stealth' => 'تسلل',
    'puzzle' => 'ألغاز',
    'shooter' => 'تصويب',
    'fighting' => 'قتال',
    'metroidvania' => 'Metroidvania'
];

// الحصول على معرف التصنيف من URL
 $currentCategory = isset($_GET['category']) ? $_GET['category'] : 'action';
 $activeCategory = $currentCategory;

// تحديد لغة الصفحة
 $lang = isset($_GET['lang']) ? $_GET['lang'] : 'ar';

// تحديد مسار ملفات CSS و JS
 $assetsPath = 'assets';

// دالة لقراءة ملف JSON وإرجاع مصفوفة PHP
function load_json_data($filename) {
    $filepath = __DIR__ . '/data/' . $filename;
    if (!file_exists($filepath)) {
        error_log("JSON file not found: " . $filepath);
        return [];
    }
    $json_data = file_get_contents($filepath);
    return json_decode($json_data, true);
}

// جلب بيانات الألعاب
 $ps4_games = load_json_data('ps4-games.json');
 $ps5_games = load_json_data('ps5-games.json');
 $ps3_games = load_json_data('ps3-games.json');
 $all_games = array_merge($ps4_games, $ps5_games, $ps3_games);

// معالجة نموذج الاتصال
 $formMessage = '';
 $formMessageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $formMessage = 'يرجى ملء جميع الحقول المطلوبة.';
        $formMessageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formMessage = 'يرجى إدخال بريد إلكتروني صحيح.';
        $formMessageType = 'error';
    } else {
        $formMessage = 'تم إرسال رسالتك بنجاح. سنتواصل معك قريباً.';
        $formMessageType = 'success';
        $name = $email = $subject = $message = '';
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $lang === 'ar' ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" >
    <title>PSPKG-arabic - تحميل ألعاب PS4, PS5, PS3 مجاناً | العاب بلايستيشن بالعربية</title>
    <meta name="description" content="أفضل موقع لتحميل ألعاب بلايستيشن PS4, PS5, PS3 مجاناً. ألعاب معربة كاملة بصيغة PKG جاهزة للتثبيت.">
    <meta name="keywords" content="ألعاب PS4, ألعاب PS5, ألعاب PS3, تحميل ألعاب بلايستيشن, العاب معربة, PKG, PSPKG">
    <link rel="canonical" href="https://pspkg-arabic.com/">
    
    <!-- Open Graph Tags للسوشيال ميديا -->
    <meta property="og:title" content="PSPKG-arabic - تحميل ألعاب PS4, PS5, PS3 مجاناً">
    <meta property="og:description" content="أفضل موقع لتحميل ألعاب بلايستيشن PS4, PS5, PS3 مجاناً. ألعاب معربة كاملة بصيغة PKG جاهزة للتثبيت.">
    <meta property="og:image" content="https://pspkg-arabic.com/images/og-image.jpg">
    <meta property="og:url" content="https://pspkg-arabic.com/">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_AR">
    
    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="PSPKG-arabic - تحميل ألعاب PS4, PS5, PS3 مجاناً">
    <meta name="twitter:description" content="أفضل موقع لتحميل ألعاب بلايستيشن PS4, PS5, PS3 مجاناً. ألعاب معربة كاملة بصيغة PKG جاهزة للتثبيت.">
    <meta name="twitter:image" content="https://pspkg-arabic.com/images/twitter-image.jpg">
    
    <!-- باقي العلامات الحالية -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- External CSS -->
    <link rel="stylesheet" href="<?php echo $assetsPath; ?>/css/about.css">
</head>
<body>
    <!-- ===== HEADER ===== -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">PSPKG-arabic</a>
              
                <div class="search-container">
                    <input type="text" class="search-bar" id="searchInput" placeholder="ابحث عن لعبة...">
                    <button class="search-btn"><i class="fas fa-search"></i></button>
                    <div class="search-suggestions" id="suggestionsBox"></div>
                </div>

                <div class="platform-nav">
                        <a href="ps4-games.php" class="platform-nav-btn ps4">
                            <i class="fab fa-playstation"></i> PS4
                        </a>
                        <a href="ps5-games.php" class="platform-nav-btn ps5">
                            <i class="fab fa-playstation"></i> PS5
                        </a>
                        <a href="ps3-games.php" class="platform-nav-btn ps3">
                            <i class="fab fa-playstation"></i> PS3
                        </a>
                        <a href="arabic-games.php" class="platform-nav-btn arabic ">
                            <i class="fas fa-language"></i> عربية
                        </a>
                    </div>
          
                <!-- قائمة الثيمات المنسدلة -->
                <div class="themes-dropdown">
                    <button class="themes-btn" id="themesBtn">
                        <i class="fas fa-palette"></i><span>THEMES</span><i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="themes-menu" id="themesMenu">
                        <div class="themes-list">
                            <a href="" class="theme-link ps4-link">
                                <div class="theme-icon"><i class="fab fa-playstation"></i></div>
                                <div class="theme-info"><span class="platform-name">PS4 Themes</span><span class="theme-count">150+ ثيم</span></div>
                            </a>
                            <a href="" class="theme-link ps5-link">
                                <div class="theme-icon"><i class="fab fa-playstation"></i></div>
                                <div class="theme-info"><span class="platform-name">PS5 Themes</span><span class="theme-count">80+ ثيم</span></div>
                            </a>
                            <a href="" class="theme-link ps3-link">
                                <div class="theme-icon"><i class="fab fa-playstation"></i></div>
                                <div class="theme-info"><span class="platform-name">PS3 Themes</span><span class="theme-count">200+ ثيم</span></div>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- قائمة التصنيفات المنسدلة -->
                <div class="categories-dropdown">
                    <button class="categories-btn" id="categoriesBtn">
                        <i class="fas fa-th-large"></i><span>التصنيفات</span><i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="categories-menu" id="categoriesMenu">
                        <div class="categories-list" id="categoriesDropdownList">
                            <?php
                            $iconMap = [
                                'all' => 'fa-border-all', 'action' => 'fa-bolt', 'rpg' => 'fa-dragon', 'racing' => 'fa-flag-checkered',
                                'sports' => 'fa-football-ball', 'adventure' => 'fa-compass', 'horror' => 'fa-ghost', 'simulation' => 'fa-plane',
                                'souls' => 'fa-skull-crossbones', 'arabic' => 'fa-language', 'hack-and-slash' => 'fa-hammer',
                                'survival' => 'fa-campground', 'stealth' => 'fa-user-ninja', 'puzzle' => 'fa-puzzle-piece',
                                'shooter' => 'fa-crosshairs', 'fighting' => 'fa-fist-raised', 'metroidvania' => 'fa-map'
                            ];
                            foreach ($categories as $categoryId => $categoryName):
                                $icon = isset($iconMap[$categoryId]) ? $iconMap[$categoryId] : 'fa-gamepad';
                                $isActive = ($activeCategory === $categoryId) ? 'active' : '';
                            ?>
                                <a href="?category=<?php echo $categoryId; ?>" class="category-dropdown-item <?php echo $isActive . ' ' . htmlspecialchars($categoryId); ?>" data-category-id="<?php echo htmlspecialchars($categoryId); ?>">
                                    <i class="fas <?php echo htmlspecialchars($icon); ?>"></i>
                                    <span><?php echo htmlspecialchars($categoryName); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- ===== PAGE HERO ===== -->
    <main class="page-hero">
        <div class="container">
            <h1>من نحن</h1>
            <p>موقعنا هو الساحة المثالية لعشاق ألعاب بلايستيشن في جميع أنحاء العالم العربي.</p>
        </div>
    </main>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="main-content container">
        <section class="about-content">
            <section class="about-section">
                <h2>قصتنا</h2>
                <p>بدأنا كفريق صغير من عشاق ألعاب بلايستيشن، نهدفنا بسيط هو توفير أفضل تجربة ممكنة للاعبين العرب.</p>
                <p>نؤمن بكل جوانبنا أن نستطيع أن نقدم محتوى عالي الجودة، وسهل نعمل بجد ونشاط لتحسين موقعنا باستمرار الوقت.</p>
            </section>

            <section class="about-section">
                <h2>مهمتنا</h2>
                <p>نحن فريق متخصص في مجال الألعاب الإلكترونيكية، من تطوير وتغطية أخبار الألعاب المختلفة.</p>
                <p>نؤمن بتقديم معلومات دقيقة وموثوقة حول الألعاب، وتزويد دعم فني للاعبين.</p>
            </section>

            <section class="about-section">
                <h2>رؤيتنا</h2>
                <p>نسعى أن نكون المصدر الأول والأكثر موثوقية لمعلومات الألعاب في العالم العربي.</p>
                <p>نهدفنا هو أن نكون المصدر الأول والموثوق للمعلومات حول الألعاب، وأن نقدم محتوى شامل يغطي جميع جوانب اللعب.</p>
            </section>
  </div>
    </main>
    
    <!-- ===== FOOTER ===== -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-left">
                    <div class="social-links">
                        <a href="https://www.youtube.com/your-channel" target="_blank" title="YouTube" aria-label="YouTube" class="youtube"><i class="fab fa-youtube"></i></a>
                        <a href="https://www.facebook.com/your-page" target="_blank" title="Facebook" aria-label="Facebook" class="facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://t.me/your-channel" target="_blank" title="Telegram" aria-label="Telegram" class="telegram"><i class="fab fa-telegram-plane"></i></a>
                    </div>
                    <div class="footer-links">
                        <a href="terms.php">اتفاقية الاستخدام</a>
                        <a href="privacy.php">سياسة الخصوصية</a>
                        <a href="about.php">من نحن</a>
                        <a href="contact.php">اتصل بنا</a>
                    </div>
                </div>
                       <div class="footer-right">
              <h3 class="footer-title">هدفنا</h3>
              <div class="footer-about-text">
                <p>لا شك أن عشاق بلايستيشن يبحثون دائمًا عن أفضل مكان لتحميل ألعابهم المفضلة مجانًا، وذلك بفضل إدارة وفريق <b>4GAMER</b> الذي يسعى لتقديم أفضل تجربة ممكنة.</p>
                <p>موقعنا هو الساحة المثالية لعشاق ألعاب <b>PS4</b> و<b>PS5</b> و<b>PS3</b>، حيث يمكنك تحميل جميع الألعاب بصيغة <b>PKG</b> بشكل مجاني تمامًا وسهل.</p>
                <p>جميع الألعاب جاهزة للتثبيت مباشرة، ولا تحتاج إلى أي خطوات إضافية بعد التحميل، كما أنها مضغوطة بدون حذف أي محتوى، مما يجعل حجمها أصغر مع الحفاظ على الجودة الكاملة.</p>
                <p>نُقدم مجموعة كبيرة من <b>الألعاب المعربة بالكامل</b>، سواء بتعريب رسمي من الشركات أو تعريب غير رسمي من فرق محترفة، لتستمتع بتجربة لعب <b>باللغة العربية</b> كما لم تعشها من قبل.</p>
                <p>لقد قمنا بتبسيط طريقة التحميل لتكون سريعة وآمنة، ونسعى لأن نكون المنصة الأفضل لتحميل ألعاب <b>PKG</b> مجانًا على جميع أجهزة بلايستيشن.</p>
                <p>نحن لا نستخدم نسخًا <b>معيوبة أو تحتوي على فيروسات</b>، بل نقدم نسخًا نظيفة وموثوقة معدّة خصيصًا لعشاق بلايستيشن.</p>
              </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> PSPKG-arabic. All Rights Reserved.</p>
            <p style="margin-top: 5px; font-size: 0.85rem;">أفضل موقع لتحميل العاب بلايستيشن مجاناً بالعربي والإنجليزية</p>
        </div>
    </div>
</footer>
    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "PSPKG-arabic",
        "url": "https://pspkg-arabic.com",
        "logo": "https://pspkg-arabic.com/assets/images/logo.png",
        "description": "أفضل موقع لتحميل العاب بلايستيشن PS4, PS5, PS3 مجاناً بالعربي والإنجليزية",
        "sameAs": [
            "https://www.youtube.com/PKGStation",
            "https://www.facebook.com/PKGStation",
            "https://t.me/PSPKG-arabic"
        ],
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "customer service",
            "availableLanguage": "Arabic"
        }
    }
    </script>

    <!-- بيانات الموقع (Website) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "PSPKG-arabic",
        "url": "https://pspkg-arabic.com",
        "description": "أفضل موقع لتحميل العاب بلايستيشن PS4, PS5, PS3 مجاناً بالعربي والإنجليزية",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "https://pspkg-arabic.com/search?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    
    <!-- Inline Script to pass PHP data to JavaScript -->
    <script>
        window.phpVars = { lang: '<?php echo $lang; ?>', assetsPath: '<?php echo $assetsPath; ?>' };
        window.allGamesData = <?php echo json_encode($all_games); ?>;
    </script>
    
    <!-- External JavaScript -->
    <script src="<?php echo $assetsPath; ?>/js/contact.js"></script>
</body>
</html>