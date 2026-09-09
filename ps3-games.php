<?php
require_once 'config.php';
// دالة تنسيق عدد التحميلات
function formatDownloads($count)
{
    if (!isset($count) || !is_numeric($count)) return '0';
    if ($count >= 1000000) return round($count / 1000000, 1) . 'M';
    if ($count >= 1000) return round($count / 1000, 1) . 'K';
    return $count;
}
// دالة لتنسيق عنوان اللعبة ليكون مناسبًا للروابط
function formatTitleForUrl($title)
{
    if (empty($title)) return '';
    // تحويل العنوان إلى حروف صغيرة واستبدال المسافات بشرطات
    $title = strtolower(trim($title));
    // إزالة الحروف غير الإنجليزية والأرقام والمسافات والشرطات
    $title = preg_replace('/[^a-z0-9\s\-]/u', '', $title);
    // استبدال أي مسافات متتالية بشرطة واحدة
    $title = preg_replace('/[\s-]+/', '-', $title);
    return trim($title, '-');
}

// تعريف المتغيرات الأساسية
$pageTitle = "تحميل ألعاب PS3 مجاناً بالعربي | PSPKG-arabic";
$currentPage = "ps3-games";

// تحميل بيانات الألعاب
function loadGamesData()
{
    $jsonFile = 'data/ps3-games.json';

    if (file_exists($jsonFile)) {
        $jsonContent = file_get_contents($jsonFile);
        return json_decode($jsonContent, true);
    }

    return [];
}

// الحصول على الألعاب
$games = loadGamesData();

// ======== التعديل الرئيسي: فرز الألعاب أولاً ========
// فرز جميع ألعاب PS3 تنازليًا حسب المعرف (id) لجلب الأحدث أولاً
usort($games, function ($a, $b) {
    // التأكد من وجود معرف للعبتين لتجنب الأخطاء
    $idA = isset($a['id']) ? $a['id'] : 0;
    $idB = isset($b['id']) ? $b['id'] : 0;
    return $idB <=> $idA; // <=> (Spaceship operator) يرتب تنازليًا
});

// تحديد الصفحة الحالية للترقيم
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$gamesPerPage = 24;

// تحديد الفئة النشطة
$activeCategory = isset($_GET['category']) ? $_GET['category'] : 'all';

// تصفية الألعاب حسب الفئة (بعد الفرز)
if ($activeCategory !== 'all') {
    $filteredGames = array_filter($games, function ($game) use ($activeCategory) {
        return isset($game['genre']) && in_array($activeCategory, $game['genre']);
    });

    // إعادة فهرسة المصفوفة بعد التصفية
    $filteredGames = array_values($filteredGames);

    $totalGames = count($filteredGames);
    $totalPages = ceil($totalGames / $gamesPerPage);
    $startIndex = ($currentPage - 1) * $gamesPerPage;
    $gamesToShow = array_slice($filteredGames, $startIndex, $gamesPerPage);
} else {
    $totalGames = count($games);
    $totalPages = ceil($totalGames / $gamesPerPage);
    $startIndex = ($currentPage - 1) * $gamesPerPage;
    $gamesToShow = array_slice($games, $startIndex, $gamesPerPage);
}

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

// حساب عدد الألعاب في كل فئة
$categoryCounts = [];
foreach ($categories as $categoryId => $categoryName) {
    if ($categoryId === 'all') {
        $categoryCounts[$categoryId] = count($games);
    } else {
        $categoryCounts[$categoryId] = count(array_filter($games, function ($game) use ($categoryId) {
            return isset($game['genre']) && in_array($categoryId, $game['genre']);
        }));
    }
}

// أخذ آخر 8 ألعاب من القائمة المرتبة (التي أصبحت الأحدث في البداية)
$sliderGames = array_slice($games, 0, 8);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="أفضل موقع لتحميل ألعاب بلايستيشن PS3 مجاناً. ألعاب معربة كاملة بصيغة PKG جاهزة للتثبيت.">
    <meta name="keywords" content="ألعاب PS3, تحميل ألعاب بلايستيشن, العاب معربة, PKG, PSPKG">
    <link rel="canonical" href="https://pspkg-arabic.com/ps3-games.php">

    <!-- Open Graph Tags للسوشيال ميديا -->
    <meta property="og:title" content="<?php echo $pageTitle; ?>">
    <meta property="og:description" content="أفضل موقع لتحميل ألعاب بلايستيشن PS3 مجاناً. ألعاب معربة كاملة بصيغة PKG جاهزة للتثبيت.">
    <meta property="og:image" content="https://pspkg-arabic.com/images/og-image.jpg">
    <meta property="og:url" content="https://pspkg-arabic.com/ps3-games.php">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_AR">

    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $pageTitle; ?>">
    <meta name="twitter:description" content="أفضل موقع لتحميل ألعاب بلايستيشن PS3 مجاناً. ألعاب معربة كاملة بصيغة PKG جاهزة للتثبيت.">
    <meta name="twitter:image" content="https://pspkg-arabic.com/images/twitter-image.jpg">

    <!-- باقي العلامات الحالية -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/ps3-games.css">
</head>

<body>
    <!-- ===== SCROLL TO TOP BUTTON ===== -->
    <button class="scroll-to-top" id="scrollToTop">
        <i class="fas fa-arrow-up"></i>
    </button>

    <div class="page-wrapper">
        <!-- ===== HEADER ===== -->
        <?php require_once 'includes/header.php'; ?>


        <main class="container">
            <!-- ===== SLIDER SECTION ===== -->
            <section class="slider-section">
                <div class="slider-container" id="slider">
                    <?php foreach ($sliderGames as $index => $game): ?>
                        <?php
                        $game_id = htmlspecialchars($game['id'] ?? '');
                        $formatted_title = formatTitleForUrl($game['title'] ?? '');
                        $download_link = "download.php?id={$game_id}&title={$formatted_title}";
                        ?>
                        <a href="<?php echo $download_link; ?>" class="slide-link">
                            <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>" style="background-image: url('<?php echo isset($game['sliderImage']) ? $game['sliderImage'] : $game['image']; ?>')">
                                <div class="slide-content">
                                    <h2><?php echo $game['title']; ?></h2>
                                    <p><?php echo isset($game['story']) ? substr($game['story'], 0, 100) . '...' : 'استمتع بتجربة gaming فريدة مع PS3'; ?></p>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
                <button class="slider-nav slider-prev" id="prevBtn"><i class="fas fa-chevron-right"></i></button>
                <button class="slider-nav slider-next" id="nextBtn"><i class="fas fa-chevron-left"></i></button>
                <div class="slider-dots" id="dotsContainer">
                    <?php foreach ($sliderGames as $index => $game): ?>
                        <span class="dot <?php echo $index === 0 ? 'active' : ''; ?>" data-slide="<?php echo $index; ?>"></span>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- ===== CATEGORIES SECTION ===== -->
            <section class="categories-section">
                <div class="categories-header">
                    <h1 class="categories-title"> GAMES PS3</h1>
                </div>
                <div class="categories-grid" id="categoriesGrid">
                    <div class="category-card <?php echo $activeCategory === 'all' ? 'active' : ''; ?>">
                        <div class="category-icon"><i class="fas fa-th"></i></div>
                        <div class="category-name">الكل</div>
                        <div class="category-count"><?php echo $categoryCounts['all']; ?> لعبة</div>
                    </div>

                    <?php foreach ($categories as $categoryId => $categoryName): ?>
                        <?php if ($categoryId !== 'all'): ?>
                            <div class="category-card <?php echo $categoryId; ?> <?php echo $activeCategory === $categoryId ? 'active' : ''; ?>" data-category="<?php echo $categoryId; ?>">
                                <div class="category-icon">
                                    <?php
                                    $iconMap = [
                                        'action' => 'fa-bolt',
                                        'rpg' => 'fa-dragon',
                                        'racing' => 'fa-flag-checkered',
                                        'sports' => 'fa-football-ball',
                                        'adventure' => 'fa-compass',
                                        'horror' => 'fa-ghost',
                                        'simulation' => 'fa-plane',
                                        'souls' => 'fa-skull-crossbones',
                                        'arabic' => 'fa-language',
                                        'hack-and-slash' => 'fa-hammer',
                                        'survival' => 'fa-campground',
                                        'stealth' => 'fa-user-ninja',
                                        'puzzle' => 'fa-puzzle-piece',
                                        'shooter' => 'fa-crosshairs',
                                        'fighting' => 'fa-fist-raised',
                                        'metroidvania' => 'fa-map',
                                        'open-world' => 'fa-globe'
                                    ];
                                    $icon = isset($iconMap[$categoryId]) ? $iconMap[$categoryId] : 'fa-gamepad';
                                    ?>
                                    <i class="fas <?php echo $icon; ?>"></i>
                                </div>
                                <div class="category-name"><?php echo $categoryName; ?></div>
                                <div class="category-count"><?php echo $categoryCounts[$categoryId]; ?> لعبة</div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- ===== PS3 SECTION ===== -->
            <section class="platform-section" id="ps3-section">
                <div class="platform-header ps3">
                    <div class="platform-info">
                        <div class="platform-icon ps3">
                            <i class="fab fa-playstation"></i>
                        </div>
                        <div class="platform-details">
                            <h2>PlayStation 3</h2>
                            <p>تحميل العاب PS3 بالعربية والإنجليزية</p>
                        </div>
                    </div>
                    <div class="platform-stats">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo count($games); ?></div>
                            <div class="stat-label">لعبة</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">2006</div>
                            <div class="stat-label">سنة الإصدار</div>
                        </div>
                    </div>
                </div>

                <div class="games-grid" id="ps3-games">
                    <?php if (empty($gamesToShow)): ?>
                        <p style="grid-column: 1/-1; text-align: center;">لا توجد ألعاب متاحة حالياً لهذا النوع.</p>
                    <?php else: ?>
                        <?php foreach ($gamesToShow as $game): ?>
                            <?php
                            $game_id = htmlspecialchars($game['id'] ?? '');
                            $formatted_title = formatTitleForUrl($game['title'] ?? '');
                            $download_link = "download.php?id={$game_id}&title={$formatted_title}";

                            // ✨ التحقق مما إذا كانت اللعبة معربة ✨
                            $is_arabic = isset($game['genre']) && in_array('arabic', $game['genre']);
                            ?>
                            <a href="<?php echo $download_link; ?>" class="game-card-link">
                                <!-- ✨ شارة معربة تظهر فقط إذا كانت اللعبة عربية ✨ -->
                                <?php if ($is_arabic): ?>
                                    <div class="arabic-badge"><i class="fas fa-language"></i> بالعربية</div>
                                <?php endif; ?>

                                <div class="game-card ps3" data-id="<?php echo $game['id']; ?>">
                                    <img src="<?php echo $game['image']; ?>" alt="<?php echo $game['title']; ?>" class="game-card-image" loading="lazy">
                                    <div class="game-card-info">
                                        <h3 class="game-card-title"><?php echo $game['title']; ?></h3>
                                        <div class="game-card-meta">
                                            <span class="platform-badge ps3"><?php echo strtoupper($game['platform']); ?></span>
                                            <span><?php echo isset($game['genre']) && is_array($game['genre']) ? implode(' • ', array_map('strtoupper', $game['genre'])) : 'غير محدد'; ?></span>
                                        </div>
                                        <div class="game-card-details">
                                            <div class="game-code"><i class="fas fa-barcode"></i> <?php echo isset($game['gameCode']) ? $game['gameCode'] : 'N/A'; ?></div>
                                            <div class="game-languages">
                                                <i class="fas fa-language"></i>
                                                <?php
                                                if (isset($game['languages']) && is_array($game['languages'])) {
                                                    $languages = array_map(function ($lang) {
                                                        return is_array($lang) ? $lang['name'] : $lang;
                                                    }, $game['languages']);
                                                    echo implode(' • ', $languages);
                                                } else {
                                                    echo 'غير محدد';
                                                }
                                                ?>
                                            </div>

                                            <div class="game-stats">
                                                <div class="game-stat">
                                                    <i class="fas fa-hdd"></i>
                                                    <span><?php echo isset($game['size']) ? $game['size'] : 'N/A'; ?></span>
                                                </div>
                                                <div class="game-stat">
                                                    <i class="fas fa-sync-alt"></i>
                                                    <span>v<?php echo isset($game['version']) ? $game['version'] : '1.00'; ?></span>
                                                </div>
                                                <div class="game-stat">
                                                    <i class="fas fa-download"></i>
                                                    <span><?php echo formatDownloads($game['downloads'] ?? null); ?></span>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>


                <!-- ===== PAGINATION ===== -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination-container" id="pagination-container">
                        <button class="pagination-btn ps3" id="prev-page" <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>>
                            <i class="fas fa-chevron-right"></i>
                        </button>

                        <div id="page-numbers">
                            <?php
                            // حساب نطاق الصفحات الذي سيتم عرضه
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $startPage + 4);

                            // تعديل نطاق البداية إذا كان النطاق النهائي قريبًا من النهاية
                            if ($endPage - $startPage < 4) {
                                $startPage = max(1, $endPage - 4);
                            }

                            // إضافة صفحة البداية والنقاط إذا لزم الأمر
                            if ($startPage > 1) {
                                echo '<a href="?page=1" class="pagination-btn">1</a>';
                                if ($startPage > 2) {
                                    echo '<span class="pagination-dots">...</span>';
                                }
                            }

                            // إضافة أرقام الصفحات
                            for ($i = $startPage; $i <= $endPage; $i++) {
                                echo '<a href="?page=' . $i . '" class="pagination-btn ' . ($i === $currentPage ? 'active' : '') . '">' . $i . '</a>';
                            }

                            // إضافة النقاط وصفحة النهاية إذا لزم الأمر
                            if ($endPage < $totalPages) {
                                if ($endPage < $totalPages - 1) {
                                    echo '<span class="pagination-dots">...</span>';
                                }
                                echo '<a href="?page=' . $totalPages . '" class="pagination-btn">' . $totalPages . '</a>';
                            }
                            ?>
                        </div>

                        <button class="pagination-btn ps3" id="next-page" <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>>
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </section>
        </main>
        <!-- ===== FOOTER ===== -->
        <?php require_once 'includes/footer.php'; ?>
    </div>

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

    <!-- بيانات الألعاب للاستخدام في JavaScript -->
    <script>
        const gamesData = <?php echo json_encode($games); ?>;
        const currentPage = <?php echo $currentPage; ?>;
        const totalPages = <?php echo $totalPages; ?>;
        const activeCategory = "<?php echo $activeCategory; ?>";
    </script>

    <!-- JavaScript -->
    <script src="assets/js/ps3-games.js"></script>
    <script>
        // 1. قائمة الفيديوهات (أضف هنا روابط اليوتوب التي تريدها)
        const warningVideos = [
            "https://www.youtube.com/watch?v=_c9N6OpzXK4&t=100s", // فيديو 1
            "https://www.youtube.com/watch?v=2AVgQ2lq0e0&t=329s", // فيديو 2
            "https://www.youtube.com/watch?v=tofwNIjP5mE&t=2s", // فيديو 3 (غير الرابط)
            "https://www.youtube.com/watch?v=qSaxmTUhHRA&t=7s", // فيديو 4 (غير الرابط)
            "https://www.youtube.com/watch?v=YTFosbbwqBw&t=102s", // فيديو 5 (غير الرابط)
            "https://www.youtube.com/watch?v=2AVgQ2lq0e0&t=331s", // فيديو 6 (غير الرابط)
            "https://www.youtube.com/watch?v=qSaxmTUhHRA", // فيديو 7 (غير الرابط)
            "https://www.youtube.com/watch?v=tofwNIjP5mE&t=24s", // فيديو 8 (غير الرابط)
            "https://www.youtube.com/watch?v=dUVqjZPr_cQ", // فيديو 9 (غير الرابط)
            "https://www.youtube.com/watch?v=3dcYqItASWY&t=64s", // فيديو 10 (غير الرابط)
            "https://www.youtube.com/watch?v=Gk2G9AuAFnk&t=611s", // فيديو 11 (غير الرابط)
            "https://www.youtube.com/watch?v=sOEYwcZzA7A&t=98s", // فيديو 12 (غير الرابط)
            "https://www.youtube.com/watch?v=XHIhrh5jifE&t=45s", // فيديو 13 (غير الرابط)
            "https://www.youtube.com/watch?v=dUVqjZPr_cQ", // فيديو 14 (غير الرابط)


            // أضف المزيد من الروابط كلما أردت بوضع فاصلة في النهاية
        ];

        // 2. دالة لاختيار فيديو عشوائي من القائمة
        function getRandomWarningVideo() {
            const randomIndex = Math.floor(Math.random() * warningVideos.length);
            return warningVideos[randomIndex];
        }

        // 3. عند الضغط على زر الفأرة الأيمن -> يفتح فيديو عشوائي في تبويب جديد
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            window.open(getRandomWarningVideo(), '_blank');
        });

        // 4. عند ضغط F12 أو اختصارات فحص الموقع -> يفتح فيديو عشوائي في تبويب جديد
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F12' ||
                (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C')) ||
                (e.ctrlKey && e.key === 'u')) {
                e.preventDefault();
                window.open(getRandomWarningVideo(), '_blank');
            }
        });

        // 5. الكشف عن فتح أدوات المطور بالطريقة العادية -> يفتح فيديو عشوائي في تبويب جديد
        setInterval(function() {
            const widthThreshold = window.outerWidth - window.innerWidth > 160;
            const heightThreshold = window.outerHeight - window.innerHeight > 160;

            if (widthThreshold || heightThreshold) {
                window.open(getRandomWarningVideo(), '_blank');
            }
        }, 1500); // كل 1.5 ثانية يفتح له فيديو مختلف حتى يغلق الأدوات
    </script>
</body>

</html>