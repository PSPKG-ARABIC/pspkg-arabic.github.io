<?php
// تحديد المتغيرات الأساسية
 $pageTitle = "pspkg-arabic | تحميل ألعاب PS4, PS5, PS3 مجاناً بالعربي";
 $pageDescription = "أفضل موقع لتحميل ألعاب بلايستيشن PS4, PS5, PS3 مجاناً. ألعاب معربة كاملة بصيغة PKG جاهزة للتثبيت. تحميل مباشر وسريع بدون إعلانات مزعجة.";
 $canonicalUrl = "https://pspkg-arabic.com/";

 $categories_list = [
    ['id' => 'arabic', 'name' => 'بالعربية', 'icon' => 'fa-language'],
    ['id' => 'action', 'name' => 'أكشن', 'icon' => 'fa-bolt'],
    ['id' => 'hack-and-slash', 'name' => 'هاكسلاش', 'icon' => 'fa-hammer'],
    ['id' => 'rpg', 'name' => 'RPG', 'icon' => 'fa-dragon'],
    ['id' => 'shooter', 'name' => 'إطلاق نار', 'icon' => 'fa-crosshairs'],
    ['id' => 'racing', 'name' => 'سباق', 'icon' => 'fa-flag-checkered'],
    ['id' => 'sports', 'name' => 'رياضة', 'icon' => 'fa-football-ball'],
    ['id' => 'adventure', 'name' => 'مغامرة', 'icon' => 'fa-compass'],
    ['id' => 'metroidvania', 'name' => 'ميترويدفانيا', 'icon' => 'fa-map'],
    ['id' => 'horror', 'name' => 'رعب', 'icon' => 'fa-ghost'],
    ['id' => 'simulation', 'name' => 'محاكاة', 'icon' => 'fa-plane'],
    ['id' => 'fighting', 'name' => 'قتال', 'icon' => 'fa-fist-raised'],
    ['id' => 'puzzle', 'name' => 'ألغاز', 'icon' => 'fa-puzzle-piece'],
    ['id' => 'platformer', 'name' => 'منصات', 'icon' => 'fa-cubes'],
    ['id' => 'stealth', 'name' => 'تسلل', 'icon' => 'fa-user-ninja'],
    ['id' => 'survival', 'name' => 'بقاء', 'icon' => 'fa-campground'],
    ['id' => 'souls', 'name' => 'سولز', 'icon' => 'fa-skull-crossbones'],
    ['id' => 'open-world', 'name' =>'عالم مفتوح', 'icon' =>  'fa-globe'],
];

// تعريف الفئات
 $categories = [
    'all' => 'الكل',
    'action' => 'أكشن',
    'rpg' => 'RPG',
    'racing' => 'سباق',
    'sports' => 'رياضة',
    'adventure' => 'مغامرة',
    'horror' => 'رعب',
    'simulation' => 'محاكاة',
    'souls' => 'سولز',
    'arabic' => 'بالعربية',
    'hack-and-slash' => 'هاكسلاش',
    'survival' => 'بقاء',
    'stealth' => 'تسلل',
    'puzzle' => 'ألغاز',
    'shooter' => 'إطلاق نار',
    'fighting' => 'قتال',
    'metroidvania' => 'ميترويدفانيا',
    'open-world' => 'عالم مفتوح'
];
// الحصول على معرف التصنيف من URL
 $currentCategory = isset($_GET['category']) ? $_GET['category'] : 'action';
 $activeCategory = $currentCategory; // إضافة هذا السطر
// تحديد لغة الصفحة
 $lang = isset($_GET['lang']) ? $_GET['lang'] : 'ar';

// تحديد مسار ملفات CSS و JS
 $assetsPath = 'assets';

// قائمة التصنيفات
 $categories = [
    [
        'id' => 'arabic', 
        'name' => 'بالعربية', 
        'icon' => 'fa-language', 
        'description' => 'استكشف الألعاب المعربة باللغة العربية لجميع منصات بلايستيشن'
    ],
    [
        'id' => 'action', 
        'name' => 'أكشن', 
        'icon' => 'fa-bolt', 
        'description' => 'استمتع بأفضل ألعاب الأكشن والمغامرات على منصات PlayStation. من المعارك الملحمية إلى المغامرات المثيرة، اكتشف عالمًا من الإثارة والتشويق.'
    ],
    [
        'id' => 'hack-and-slash', 
        'name' => 'هاكسلاش', 
        'icon' => 'fa-hammer', 
        'description' => 'استمتع بألعاب القتال السريع والمعارك الحماسية'
    ],
    [
        'id' => 'rpg', 
        'name' => 'RPG', 
        'icon' => 'fa-dragon', 
        'description' => 'انغمس في عواملك الواسعة وأكمل المهام في ألعاب تقمص الأدوار'
    ],
    [
        'id' => 'shooter', 
        'name' => 'إطلاق نار', 
        'icon' => 'fa-crosshairs', 
        'description' => 'اختبر أفضل ألعاب إطلاق النار من منظور الشخص الأول والثالث'
    ],
    [
        'id' => 'fighting', 
        'name' => 'قتال', 
        'icon' => 'fa-fist-raised', 
        'description' => 'تحدى خصومك في ألعاب القتال المباشر'
    ],
    [
        'id' => 'adventure', 
        'name' => 'مغامرة', 
        'icon' => 'fa-compass', 
        'description' => 'انطلق في رحلات ملحمية واكتشف عوالم جديدة'
    ],
    [
        'id' => 'metroidvania', 
        'name' => 'ميترويدفانيا', 
        'icon' => 'fa-map', 
        'description' => 'استكشف العوالم المترابطة واكتسب قدرات جديدة'
    ],
    [
        'id' => 'horror', 
        'name' => 'رعب', 
        'icon' => 'fa-ghost', 
        'description' => 'واجه مخاوفك في ألعاب الرعب المليئة بالتشويق'
    ],
    [
        'id' => 'souls', 
        'name' => 'سولز', 
        'icon' => 'fa-skull-crossbones', 
        'description' => 'اختبر التحديات القاسية في ألعاب سولز'
    ],
    [
        'id' => 'stealth', 
        'name' => 'تسلل', 
        'icon' => 'fa-user-ninja', 
        'description' => 'تسلل خلف الأعداء واكمل مهامك دون أن يتم اكتشافك'
    ],
    [
        'id' => 'survival', 
        'name' => 'بقاء', 
        'icon' => 'fa-campground', 
        'description' => 'كافح من أجل البقاء في بيئات قاسية'
    ],
    [
        'id' => 'racing', 
        'name' => 'سباق', 
        'icon' => 'fa-flag-checkered', 
        'description' => 'تنافس في سباقات السيارات السريعة والمثيرة'
    ],
    [
        'id' => 'sports', 
        'name' => 'رياضة', 
        'icon' => 'fa-football-ball', 
        'description' => 'شارك في الرياضات المختلفة وحقق البطولات'
    ],
    [
        'id' => 'simulation', 
        'name' => 'محاكاة', 
        'icon' => 'fa-plane', 
        'description' => 'اختبر واقع المحاكاة في مختلف المجالات'
    ],
    [
        'id' => 'puzzle', 
        'name' => 'ألغاز', 
        'icon' => 'fa-puzzle-piece', 
        'description' => 'حل الألغاز المعقدة واخترق التحديات العقلية'
    ],
    [
        'id' => 'open-world', 
        'name' => 'عالم مفتوح', 
        'icon' => 'fa-globe', 
        'description' => 'استمتع بحرية الاستكشاف في العوالم المفتوحة الواسعة'
    ],
];

// العثور على التصنيف الحالي
 $currentCategoryData = null;
foreach ($categories as $category) {
    if ($category['id'] === $currentCategory) {
        $currentCategoryData = $category;
        break;
    }
}

// إذا لم يتم العثور على التصنيف، استخدم التصنيف الافتراضي
if (!$currentCategoryData) {
    $currentCategoryData = $categories[1]; // الأكشن كافتراضي
    $currentCategory = $currentCategoryData['id'];
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
    <link rel="stylesheet" href="<?php echo $assetsPath; ?>/css/category.css">
</head>
<body>
    <!-- ===== HEADER ===== -->
     <?php require_once 'includes/header.php'; ?>

    <main class="container">
        <!-- ===== CATEGORY HEADER ===== -->
        <section class="category-header" id="categoryHeader">
            <div class="category-icon <?php echo $currentCategory; ?>" id="categoryIcon">
                <i class="fas <?php echo $currentCategoryData['icon']; ?>"></i>
            </div>
            <div class="category-content">
                <h1 class="category-title" id="categoryTitle"><?php echo $currentCategoryData['name']; ?></h1>
                <p class="category-description" id="categoryDescription">
                    <?php echo $currentCategoryData['description']; ?>
                </p>
            </div>
            <div class="category-stats">
                <div class="stat-item">
                    <div class="stat-number" id="totalGames">0</div>
                    <div class="stat-label"><?php echo $lang === 'ar' ? 'لعبة' : 'Games'; ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="ps3Count">0</div>
                    <div class="stat-label">PS3</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="ps4Count">0</div>
                    <div class="stat-label">PS4</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="ps5Count">0</div>
                    <div class="stat-label">PS5</div>
                </div>
            </div>
        </section>

        <!-- ===== FILTER SECTION ===== -->
        <section class="filter-section">
            <div class="filter-header">
                <h3 class="filter-title"><?php echo $lang === 'ar' ? 'تصفية حسب المنصة' : 'Filter by Platform'; ?></h3>
            </div>
            <div class="filter-options">
                <button class="filter-btn active" data-filter="all"><?php echo $lang === 'ar' ? 'الكل' : 'All'; ?></button>
                <button class="filter-btn" data-filter="ps3">PS3</button>
                <button class="filter-btn" data-filter="ps4">PS4</button>
                <button class="filter-btn" data-filter="ps5">PS5</button>
            </div>
        </section>

        <!-- ===== GAMES GRID ===== -->
        <section class="games-grid" id="gamesGrid"></section>
        
        <!-- ===== LOAD MORE BUTTON ===== -->
        <div class="load-more-container">
            <button class="load-more-btn" id="loadMoreBtn"><?php echo $lang === 'ar' ? 'تحميل المزيد من الألعاب' : 'Load More Games'; ?></button>
        </div>
    </main>
    
    <!-- ===== SCROLL TO TOP BUTTON ===== -->
    <button class="scroll-to-top" id="scrollToTop">
        <i class="fas fa-arrow-up"></i>
    </button>
   <!-- ===== FOOTER ===== -->
<footer class="main-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-left">
                <div class="social-links">
                    <!-- IMPORTANT: Replace '#' with your actual social media links -->
                    <a href="https://www.youtube.com/@mustafahanyf" target="_blank" title="YouTube" aria-label="YouTube" class="youtube">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="https://web.facebook.com/share/v/1BRSsTQf57/" target="_blank" title="Facebook" aria-label="Facebook" class="facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://t.me/GOLDHEN198" target="_blank" title="Telegram" aria-label="Telegram" class="telegram">
                        <i class="fab fa-telegram-plane"></i>
                    </a>
                </div>
                
                <div class="footer-links">
                    <a href="terms.php">اتفاقية الاستخدام</a>
                    <a href="privacy.php">سياسة الخصوصية</a>
                    <a href="about.php">من نحن</a>
                    <a href="contact.php">اتصل بنا</a>
                </div>
            </div>
                
                <div class="footer-right">
                    <h3 class="footer-title"><?php echo $lang === 'ar' ? 'هدفنا' : 'Our Goal'; ?></h3>
                    <div class="footer-about-text">
                        <?php if ($lang === 'ar'): ?>
                            <p>لا شك أن عشاق بلايستيشن يبحثون دائمًا عن أفضل مكان لتحميل ألعابهم المفضلة مجانًا، وذلك بفضل إدارة وفريق <b>4GAMER</b> الذي يسعى لتقديم أفضل تجربة ممكنة.</p>
                            
                            <p>موقعنا هو الساحة المثالية لعشاق ألعاب <b>PS4</b> و<b>PS5</b> و<b>PS3</b>، حيث يمكنك تحميل جميع الألعاب بصيغة <b>PKG</b> بشكل مجاني تمامًا وسهل.</p>
                            
                            <p>جميع الألعاب جاهزة للتثبيت مباشرة، ولا تحتاج إلى أي خطوات إضافية بعد التحميل، كما أنها مضغوطة بدون حذف أي محتوى، مما يجعل حجمها أصغر مع الحفاظ على الجودة الكاملة.</p>
                            
                            <p>نُقدم مجموعة كبيرة من <b>الألعاب المعربة بالكامل</b>، سواء بتعريب رسمي من الشركات أو تعريب غير رسمي من فرق محترفة، لتستمتع بتجربة لعب <b>باللغة العربية</b> كما لم تعشها من قبل.</p>
                            
                            <p>لقد قمنا بتبسيط طريقة التحميل لتكون سريعة وآمنة، ونسعى لأن نكون المنصة الأفضل لتحميل ألعاب <b>PKG</b> مجانًا على جميع أجهزة بلايستيشن.</p>
                            
                            <p>نحن لا نستخدم نسخًا <b>معيوبة أو تحتوي على فيروسات</b>، بل نقدم نسخًا نظيفة وموثوقة معدّة خصيصًا لعشاق بلايستيشن.</p>
                        <?php else: ?>
                            <p>PlayStation fans are always looking for the best place to download their favorite games for free, thanks to the management and team of <b>4GAMER</b> who strive to provide the best possible experience.</p>
                            
                            <p>Our site is the perfect arena for fans of <b>PS4</b>, <b>PS5</b> and <b>PS3</b> games, where you can download all games in <b>PKG</b> format completely free and easily.</p>
                            
                            <p>All games are ready for direct installation, and do not require any additional steps after downloading, as they are compressed without deleting any content, making their size smaller while maintaining full quality.</p>
                            
                            <p>We offer a large collection of <b>fully Arabic games</b>, whether with official translation from companies or unofficial translation from professional teams, so you can enjoy a gaming experience <b>in Arabic</b> like never before.</p>
                            
                            <p>We have simplified the download method to be fast and secure, and we strive to be the best platform for downloading <b>PKG</b> games for free on all PlayStation devices.</p>
                            
                            <p>We do not use <b>defective or virus-infected copies</b>, but rather provide clean and reliable copies prepared specifically for PlayStation fans.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> pspkg-arabic. All Rights Reserved.</p>
                <p style="margin-top: 5px; font-size: 0.85rem;"><?php echo $lang === 'ar' ? 'عرض احترافي لتطوير الويب الحديث.' : 'A professional showcase for modern web development.'; ?></p>
            </div>
        </div>
    </footer>
    
    <!-- بيانات المنظمة (Organization) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "pspkg-arabic.com",
        "url": "https://pspkg-arabic.com",
        "logo": "https://pspkg-arabic.com/images/logo.png",
        "description": "<?php echo $lang === 'ar' ? 'أفضل موقع لتحميل العاب بلايستيشن PS4, PS5, PS3 مجاناً بالعربي والإنجليزية' : 'Best site to download PlayStation PS4, PS5, PS3 games for free in Arabic and English'; ?>",
        "sameAs": [
            "https://www.youtube.com/pspkg-arabic.com",
            "https://www.facebook.com/pspkg-arabic.com",
            "https://t.me/pspkg-arabic.com"
        ],
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "customer service",
            "availableLanguage": "<?php echo $lang === 'ar' ? 'Arabic' : 'English'; ?>"
        }
    }
    </script>
    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "PSPKG-arabic",
        "url": "https://pspkg-arabic.com",
        "logo": "https://pspkg-arabic.com/assets/images/logo.png",
        "description": "أفضل موقع لتحميل العاب بلايستيشن PS4, PS5, PS3 مجاناً بالعربي والإنجليزية",
        "sameAs": [
            "https://www.youtube.com/pspkg-arabic",
            "https://www.facebook.com/pspkg-arabic",
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

    <!-- تمرير متغيرات PHP إلى JavaScript -->
    <script>
        // متغيرات من PHP إلى JavaScript
        window.phpVars = {
            currentCategory: '<?php echo $currentCategory; ?>',
            lang: '<?php echo $lang; ?>',
            assetsPath: '<?php echo $assetsPath; ?>',
            categories: <?php echo json_encode($categories); ?>
        };
    </script>
    
    <script src="<?php echo $assetsPath; ?>/js/category.js"></script>
</body>
</html>