<?php
require_once 'config.php';

// دالة لتنسيق عنوان اللعبة ليكون مناسبًا للروابط
function formatTitleForUrl($title) {
    if (empty($title)) return '';
    $title = strtolower(trim($title));
    $title = preg_replace('/[^a-z0-9\s\-]/u', '', $title);
    $title = preg_replace('/[\s-]+/', '-', $title);
    return trim($title, '-');
}

// ✨ دالة تنسيق عدد التحميلات ✨
function formatDownloads($count) {
    if ($count >= 1000000) {
        return round($count / 1000000, 1) . 'M';
    } elseif ($count >= 1000) {
        return round($count / 1000, 1) . 'K';
    }
    return $count;
}

// تعريف المتغيرات الأساسية للصفحة
 $pageTitle = "تحميل الألعاب المعربة مجاناً بالعربي | PSPKG-arabic";
 $currentPage = "arabic-games";

// تحميل بيانات الألعاب لجميع المنصات
function loadAllGamesData() {
    $platforms = ['ps3', 'ps4', 'ps5'];
    $allGames = [];
    
    foreach ($platforms as $platform) {
        $jsonFile = "data/{$platform}-games.json";
        if (file_exists($jsonFile)) {
            $jsonContent = file_get_contents($jsonFile);
            $platformGames = json_decode($jsonContent, true);
            if (is_array($platformGames)) {
                foreach ($platformGames as &$game) {
                    $game['platform'] = strtoupper($platform);
                }
                $allGames = array_merge($allGames, $platformGames);
            }
        }
    }
    return $allGames;
}

// تعريف قائمة التصنيفات
// ❌ تم حذف تكرار "سولز" الذي كان هنا
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

// الحصول على جميع الألعاب
 $allGames = loadAllGamesData();

// فلترة الألعاب لإظهار الألعاب المعربة فقط
 $arabicGames = array_filter($allGames, function($game) {
    return isset($game['genre']) && in_array('arabic', $game['genre']);
});

// فرز جميع الألعاب المعربة تنازليًا حسب المعرف (id) لجلب الأحدث أولاً
usort($arabicGames, function($a, $b) {
    $idA = isset($a['id']) ? $a['id'] : 0;
    $idB = isset($b['id']) ? $b['id'] : 0;
    return $idB <=> $idA;
});

// تحديد ألعاب السلايدر (آخر 8 ألعاب معربة تمت إضافتها)
 $sliderGames = array_slice($arabicGames, 0, 8);

// تحديد الصفحة الحالية للترقيم
 $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
 $gamesPerPage = 12;

// تحديد الفئة والمنصة النشطين من الرابط
 $activeCategory = isset($_GET['category']) ? $_GET['category'] : 'all';
 $activePlatform = isset($_GET['platform']) ? $_GET['platform'] : 'all';

// تصفية الألعاب المعربة حسب الفئة والمنصة
 $filteredGames = $arabicGames;

if ($activeCategory !== 'all') {
    $filteredGames = array_filter($filteredGames, function($game) use ($activeCategory) {
        return isset($game['genre']) && in_array($activeCategory, $game['genre']);
    });
}

if ($activePlatform !== 'all') {
    $filteredGames = array_filter($filteredGames, function($game) use ($activePlatform) {
        return isset($game['platform']) && strtolower($game['platform']) === $activePlatform;
    });
}

// إعادة حساب العدد الإجمالي والصفحات بعد التصفية
 $totalGames = count($filteredGames);
 $totalPages = ceil($totalGames / $gamesPerPage);
 $startIndex = ($currentPage - 1) * $gamesPerPage;
 $gamesToShow = array_slice($filteredGames, $startIndex, $gamesPerPage);

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

// تعريف المنصات
 $platforms = [
    'all' => 'الكل',
    'ps3' => 'PS3',
    'ps4' => 'PS4',
    'ps5' => 'PS5'
];

// حساب عدد الألعاب المعربة في كل فئة
 $categoryCounts = [];
foreach ($categories as $categoryId => $categoryName) {
    if ($categoryId === 'all') {
        $categoryCounts[$categoryId] = count($arabicGames);
    } else {
        $categoryCounts[$categoryId] = count(array_filter($arabicGames, function($game) use ($categoryId) {
            return isset($game['genre']) && in_array($categoryId, $game['genre']);
        }));
    }
}

// حساب عدد الألعاب المعربة في كل منصة
 $platformCounts = [];
foreach ($platforms as $platformId => $platformName) {
    if ($platformId === 'all') {
        $platformCounts[$platformId] = count($arabicGames);
    } else {
        $platformCounts[$platformId] = count(array_filter($arabicGames, function($game) use ($platformId) {
            return isset($game['platform']) && strtolower($game['platform']) === $platformId;
        }));
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" >
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="أفضل موقع لتحميل الألعاب المعربة مجاناً. ألعاب بلايستيشن PS4, PS5, PS3 باللغة العربية كاملة بصيغة PKG جاهزة للتثبيت.">
    <meta name="keywords" content="ألعاب معربة, العاب بالعربية, PS4 معرب, PS5 معرب, PS3 معرب, تحميل ألعاب بلايستيشن, PKG, PSPKG">
    <link rel="canonical" href="https://pspkg-arabic.com/arabic-games.php">
    
    <meta property="og:title" content="<?php echo $pageTitle; ?>">
    <meta property="og:description" content="أفضل موقع لتحميل الألعاب المعربة مجاناً. ألعاب بلايستيشن PS4, PS5, PS3 باللغة العربية كاملة بصيغة PKG جاهزة للتثبيت.">
    <meta property="og:image" content="https://pspkg-arabic.com/images/og-image.jpg">
    <meta property="og:url" content="https://pspkg-arabic.com/arabic-games.php">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_AR">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $pageTitle; ?>">
    <meta name="twitter:description" content="أفضل موقع لتحميل الألعاب المعربة مجاناً. ألعاب بلايستيشن PS4, PS5, PS3 باللغة العربية كاملة بصيغة PKG جاهزة للتثبيت.">
    <meta name="twitter:image" content="https://pspkg-arabic.com/images/twitter-image.jpg">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/arabic-games.css">
</head>

<body>
    <button class="scroll-to-top" id="scrollToTop">
        <i class="fas fa-arrow-up"></i>
    </button>

    <div class="page-wrapper">
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
                                    <p><?php echo isset($game['story']) ? substr($game['story'], 0, 100) . '...' : 'استمتع بتجربة الألعاب المعربة'; ?></p>
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
                    <h1 class="categories-title">الألعاب المعربة</h1>
                </div>
                
                <div class="categories-filter">
                    <div class="categories-grid" id="categoriesGrid">
                        <a href="?category=all&platform=<?php echo $activePlatform; ?>" class="category-card <?php echo $activeCategory === 'all' ? 'active' : ''; ?>">
                            <div class="category-icon"><i class="fas fa-th"></i></div>
                            <div class="category-name">الكل</div>
                            <div class="category-count"><?php echo $categoryCounts['all']; ?></div>
                        </a>
                        
                        <?php foreach ($categories as $categoryId => $categoryName): ?>
                            <?php if ($categoryId !== 'all'): ?>
                                <a href="?category=<?php echo $categoryId; ?>&platform=<?php echo $activePlatform; ?>" class="category-card <?php echo $categoryId; ?> <?php echo $activeCategory === $categoryId ? 'active' : ''; ?>">
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
                                    <div class="category-count"><?php echo $categoryCounts[$categoryId]; ?></div>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- ===== ARABIC GAMES SECTION ===== -->
            <section class="platform-section" id="arabic-games-section">
                <div class="platform-header arabic">
                    <div class="platform-info">
                        <div class="platform-icon arabic">
                            <i class="fas fa-language"></i>
                        </div>
                        <div class="platform-details">
                            <h2>الألعاب المعربة</h2>
                            <p>تحميل ألعاب PlayStation باللغة العربية كاملة</p>
                        </div>
                    </div>
                    <div class="platform-stats">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo count($arabicGames); ?></div>
                            <div class="stat-label">لعبة معربة</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">3</div>
                            <div class="stat-label">منصات</div>
                        </div>
                    </div>
                </div>
                
                <!-- ===== FILTER SECTION ===== -->
                <section class="filter-section">
                    <div class="filter-header"></div>
                    <div class="filter-options">
                        <?php foreach ($platforms as $platformId => $platformName): ?>
                            <a href="?platform=<?php echo $platformId; ?>&category=<?php echo $activeCategory; ?>" class="filter-btn <?php echo $activePlatform === $platformId ? 'active' : ''; ?>">
                                <div class="platform-icon <?php echo $platformId; ?>">
                                    <i class="fab fa-playstation"></i>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- ===== GAMES GRID ===== -->
                <div class="games-grid" id="arabic-games">
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
                        
                        // ✨ تحديد كلاس المنصة للتوهج ✨
                        $platformClass = strtolower($game['platform'] ?? 'all');
                        ?>
                        <a href="<?php echo $download_link; ?>" class="game-card-link <?php echo $platformClass; ?>">
                            <!-- ✨ شارة معربة تظهر فقط إذا كانت اللعبة عربية ✨ -->
                            <?php if ($is_arabic): ?>
                                <div class="arabic-badge"><i class="fas fa-language"></i> بالعربية</div>
                            <?php endif; ?>
                            
                            <div class="game-card arabic" data-id="<?php echo $game['id']; ?>">
                                <img src="<?php echo $game['image']; ?>" alt="<?php echo $game['title']; ?>" class="game-card-image" loading="lazy">
                                <div class="game-card-info">
                                    <h3 class="game-card-title"><?php echo $game['title']; ?></h3>
                                    <div class="game-card-meta">
                                        <span class="platform-badge arabic"><?php echo strtoupper($game['platform']); ?></span>
                                        <span><?php echo isset($game['genre']) && is_array($game['genre']) ? implode(' • ', array_map('strtoupper', $game['genre'])) : 'غير محدد'; ?></span>
                                    </div>
                                    <div class="game-card-details">
                                        <div class="game-code"><i class="fas fa-barcode"></i> <?php echo isset($game['gameCode']) ? $game['gameCode'] : 'N/A'; ?></div>
                                        <div class="game-languages">
                                            <i class="fas fa-language"></i> 
                                            <?php 
                                            if (isset($game['languages']) && is_array($game['languages'])) {
                                                $languages = array_map(function($lang) { return is_array($lang) ? $lang['name'] : $lang; }, $game['languages']);
                                                echo implode(' • ', $languages);
                                            } else { echo 'غير محدد'; }
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
        <button class="pagination-btn arabic" id="prev-page" <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>>
            <i class="fas fa-chevron-right"></i>
                        </button>
                        <div id="page-numbers">
                            <?php
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $startPage + 4);
                            if ($endPage - $startPage < 4) $startPage = max(1, $endPage - 4);
                            
                            if ($startPage > 1) {
                                echo '<a href="?page=1&category=' . $activeCategory . '&platform=' . $activePlatform . '" class="pagination-btn">1</a>';
                                if ($startPage > 2) echo '<span class="pagination-dots">...</span>';
                            }
                            
                            for ($i = $startPage; $i <= $endPage; $i++) {
                                echo '<a href="?page=' . $i . '&category=' . $activeCategory . '&platform=' . $activePlatform . '" class="pagination-btn ' . ($i === $currentPage ? 'active' : '') . '">' . $i . '</a>';
                            }
                            
                            if ($endPage < $totalPages) {
                                if ($endPage < $totalPages - 1) echo '<span class="pagination-dots">...</span>';
                                echo '<a href="?page=' . $totalPages . '&category=' . $activeCategory . '&platform=' . $activePlatform . '" class="pagination-btn">' . $totalPages . '</a>';
                            }
                            ?>
                        </div>
                        <button class="pagination-btn arabic" id="next-page" <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>>
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </section>
        </main>

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

    <script>
        const gamesData = <?php echo json_encode(array_values($arabicGames)); ?>;
        const currentPage = <?php echo $currentPage; ?>;
        const totalPages = <?php echo $totalPages; ?>;
        const activeCategory = "<?php echo $activeCategory; ?>";
        const activePlatform = "<?php echo $activePlatform; ?>";
    </script>

    <script src="assets/js/arabic-games.js"></script>
    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
    
    <!-- حجب المطور والتحويل المباشر لليوتوب في New Tab -->
        <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
    
    <!-- حجب المطور مع قائمة فيديوهات عشوائية -->
   
</body> 
</html>