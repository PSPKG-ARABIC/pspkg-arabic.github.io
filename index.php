<?php
// تضمين ملف الإعدادات
require_once 'config.php';

// دالة لتنسيق عنوان اللعبة ليكون مناسبًا للروابط
function format_title_for_url($title)
{
    if (empty($title)) return '';
    $title = strtolower(trim($title));
    $title = preg_replace('/[^a-z0-9\s\-]/', '', $title);
    $title = preg_replace('/[\s-]+/', '-', $title);
    return $title;
}

// تعيين ترميز الملف لضمان عرض الحروف العربية بشكل صحيح
header('Content-Type: text/html; charset=utf-8');

// دالة لقراءة ملف JSON وإرجاع مصفوفة PHP
function load_json_data($filename)
{
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

// ✨ ترتيب الألعاب تنازلياً حسب المعرف (ID) لجلب الأحدث أولاً
usort($ps4_games, function ($a, $b) {
    return ($b['id'] ?? 0) <=> ($a['id'] ?? 0);
});
usort($ps5_games, function ($a, $b) {
    return ($b['id'] ?? 0) <=> ($a['id'] ?? 0);
});
usort($ps3_games, function ($a, $b) {
    return ($b['id'] ?? 0) <=> ($a['id'] ?? 0);
});

$all_games = array_merge($ps4_games, $ps5_games, $ps3_games);

// تعريف قائمة التصنيفات
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
    ['id' => 'open-world', 'name' => 'عالم مفتوح', 'icon' => 'fa-globe'],
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

// تعريف المتغيرات الناقصة لقسم التصنيفات
$activeCategory = 'all';
$categoryCounts = [];
foreach ($categories as $categoryId => $categoryName) {
    if ($categoryId === 'all') {
        $categoryCounts[$categoryId] = count($all_games);
    } else {
        $categoryCounts[$categoryId] = count(array_filter($all_games, function ($game) use ($categoryId) {
            return isset($game['genre']) && is_array($game['genre']) && in_array($categoryId, $game['genre']);
        }));
    }
}

// حساب عدد الألعاب في كل تصنيف لقائمة الـ Cards
foreach ($categories_list as &$category) {
    $category['count'] = count(array_filter($all_games, function ($game) use ($category) {
        return isset($game['genre']) && is_array($game['genre']) && in_array($category['id'], $game['genre']);
    }));
}

// ✨ إزالة التصنيفات المتكررة والفارغة ✨
$seen_ids = [];
$filtered_categories_list = [];
foreach ($categories_list as $category) {
    // التحقق من عدم تكرار المعرف
    if (in_array($category['id'], $seen_ids)) {
        continue;
    }
    $seen_ids[] = $category['id'];

    // إبقاء التصنيف فقط إذا كان يحتوي على لعبة واحدة على الأقل
    if ($category['count'] > 0) {
        $filtered_categories_list[] = $category;
    }
}
$categories_list = $filtered_categories_list;

// دالة أكثر قوة للتعامل مع مسارات الصور
function get_image_path($image_path_from_json)
{
    // إذا كان المسار فارغاً، أرجع الصورة الافتراضية
    if (empty($image_path_from_json)) {
        return 'assets/images/default-game.webp';
    }

    // تحويل الشرطات العكسية \ (التي تأتي أحياناً من JSON) إلى شرطات عادية /
    $path = str_replace('\\', '/', $image_path_from_json);

    // إذا كان الرابط الخارجي (يبدأ بـ http)
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        return htmlspecialchars($path);
    }

    // إزالة النقاط من بداية المسار (مثل ../ أو ./ ) لأنها تكسر عرض الصورة في الواجهة
    $path = preg_replace('/^(\.\.\/)+/', '', $path);
    $path = preg_replace('/^(\.\/)+/', '', $path);

    // إذا كان المسار يبدأ بـ assets (بعد إزالة ../)
    if (strpos($path, 'assets/') === 0) {
        return htmlspecialchars($path);
    }

    // إذا كان مساراً مطلقاً يبدأ بـ / (مثل /assets/images/...)
    if (strpos($path, '/') === 0) {
        return htmlspecialchars(ltrim($path, '/'));
    }

    // إذا كان مجرد اسم ملف بدون مسار (مثل image.jpg)
    return 'assets/images/' . htmlspecialchars($path);
}

// دالة تنسيق عدد التحميلات
function formatDownloads($count)
{
    if (!isset($count) || !is_numeric($count)) return '0';
    if ($count >= 1000000) return round($count / 1000000, 1) . 'M';
    if ($count >= 1000) return round($count / 1000, 1) . 'K';
    return $count;
}

// دالة لإنشاء كود HTML لبطاقة لعبة واحدة
function render_game_card($game)
{
    $game_id = htmlspecialchars($game['id'] ?? '');
    $game_image = get_image_path($game['image'] ?? null);
    $game_title = htmlspecialchars($game['title'] ?? 'غير معروف');
    $game_platform = htmlspecialchars($game['platform'] ?? 'unknown');
    $game_code = htmlspecialchars($game['gameCode'] ?? 'N/A');

    $game_size = htmlspecialchars($game['size'] ?? 'N/A');
    $game_version = htmlspecialchars($game['version'] ?? '1.00');
    $game_downloads = formatDownloads($game['downloads'] ?? null);

    // التحقق مما إذا كانت اللعبة معربة
    $is_arabic = isset($game['genre']) && in_array('arabic', $game['genre']);
    $arabic_badge = $is_arabic ? "<div class='arabic-badge'><i class='fas fa-language'></i> بالعربية</div>" : '';

    $formatted_title = format_title_for_url($game_title);
    $download_link = "download.php?id={$game_id}&title={$formatted_title}";

    $languages_text = 'غير محدد';
    if (!empty($game['languages']) && is_array($game['languages'])) {
        $languages_array = array_map(function ($lang) {
            return is_array($lang) ? ($lang['name'] ?? 'غير محدد') : $lang;
        }, $game['languages']);
        $languages_text = implode(' • ', $languages_array);
    }

    return "
    <a href='{$download_link}' class='game-card-link'>
        {$arabic_badge}
        <div class='game-card {$game_platform}'>
            <img src='{$game_image}' alt='{$game_title}' class='game-card-image' loading='lazy'>
            <div class='game-card-info'>
                <h3 class='game-card-title'>{$game_title}</h3>
                <div class='game-card-meta'>
                    <span class='platform-badge {$game_platform}'>{$game_platform}</span>
                </div>
                <div class='game-card-details'>
                    <div class='game-code'><i class='fas fa-barcode'></i> {$game_code}</div>
                    <div class='game-languages'><i class='fas fa-language'></i> {$languages_text}</div>
                    
                    <div class='game-stats'>
                        <div class='game-stat'>
                            <i class='fas fa-hdd'></i>
                            <span>{$game_size}</span>
                        </div>
                        <div class='game-stat'>
                            <i class='fas fa-sync-alt'></i>
                            <span>v{$game_version}</span>
                        </div>
                        <div class='game-stat'>
                            <i class='fas fa-download'></i>
                            <span>{$game_downloads}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </a>
    ";
}

// دالة لإنشاء كود HTML لشريحة في السلايدر
function render_slide($game, $index)
{
    $game_id = htmlspecialchars($game['id'] ?? '');
    $slide_image = get_image_path($game['sliderImage'] ?? $game['image'] ?? null);
    $game_title = htmlspecialchars($game['title'] ?? 'غير معروف');
    $game_story = htmlspecialchars(substr($game['story'] ?? '', 0, 100) . '...');
    $active_class = $index === 0 ? 'active' : '';

    $formatted_title = format_title_for_url($game_title);
    $download_link = "download.php?id={$game_id}&title={$formatted_title}";

    return "
    <a href='{$download_link}' class='slide-link'>
        <div class='slide {$active_class}' style='background-image: url({$slide_image});'>
            <div class='slide-content'>
                <h2>{$game_title}</h2>
                <p>{$game_story}</p>
            </div>
        </div>
    </a>
    ";
}

// دالة للحصول على آخر الألعاب المضافة لمنصة معينة
function get_latest_platform_games($games, $count)
{
    // بما أننا قمنا بترتيب المصفوفات مسبقاً، يمكننا الاكتفاء بقص أول عناصر منها
    // ولكن سنترك الترتيب هنا أيضاً لضمان العمل الصحيح إذا تم تمرير مصفوفة غير مرتبة مستقبلاً
    usort($games, function ($a, $b) {
        $idA = isset($a['id']) ? $a['id'] : 0;
        $idB = isset($b['id']) ? $b['id'] : 0;
        return $idB <=> $idA;
    });
    return array_slice($games, 0, $count);
}

// جلب آخر 3 ألعاب من كل منصة
$latest_ps5_games = get_latest_platform_games($ps5_games, 3);
$latest_ps4_games = get_latest_platform_games($ps4_games, 3);
$latest_ps3_games = get_latest_platform_games($ps3_games, 3);

$slider_games = array_merge($latest_ps5_games, $latest_ps4_games, $latest_ps3_games);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport">
    <title>PSPKG-arabic - تحميل ألعاب PS4, PS5, PS3 مجاناً | العاب بلايستيشن بالعربية</title>
    <meta name="description" content="أفضل موقع لتحميل ألعاب بلايستيشن PS4, PS5, PS3 مجاناً. ألعاب معربة كاملة بصيغة PKG جاهزة للتثبيت.">
    <meta name="keywords" content="ألعاب PS4, ألعاب PS5, ألعاب PS3, تحميل ألعاب بلايستيشن, العاب معربة, PKG, PSPKG">
    <link rel="canonical" href="https://pspkg-arabic.com/">

    <meta property="og:title" content="PSPKG-arabic - تحميل ألعاب PS4, PS5, PS3 مجاناً">
    <meta property="og:description" content="أفضل موقع لتحميل ألعاب بلايستيشن PS4, PS5, PS3 مجاناً.">
    <meta property="og:image" content="https://pspkg-arabic.com/images/og-image.jpg">
    <meta property="og:url" content="https://pspkg-arabic.com/">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_AR">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="PSPKG-arabic - تحميل ألعاب PS4, PS5, PS3 مجاناً">
    <meta name="twitter:description" content="أفضل موقع لتحميل ألعاب بلايستيشن PS4, PS5, PS3 مجاناً.">
    <meta name="twitter:image" content="https://pspkg-arabic.com/images/twitter-image.jpg">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" href="/favicon.ico">
    <link rel="manifest" href="/site.webmanifest">

    <script>
        const allGamesData = <?php echo json_encode($all_games); ?>;
    </script>
</head>

<body>
    <button class="scroll-to-top" id="scrollToTop"><i class="fas fa-arrow-up"></i></button>

    <div class="page-wrapper">
        <?php require_once 'includes/header.php'; ?>

        <main class="container">
            <!-- ===== SLIDER SECTION ===== -->
            <section class="slider-section">
                <div class="slider-container" id="slider">
                    <?php foreach ($slider_games as $index => $game): ?>
                        <?= render_slide($game, $index) ?>
                    <?php endforeach; ?>
                </div>
                <button class="slider-nav slider-prev" id="prevBtn"><i class="fas fa-chevron-right"></i></button>
                <button class="slider-nav slider-next" id="nextBtn"><i class="fas fa-chevron-left"></i></button>
                <div class="slider-dots" id="dotsContainer">
                    <?php foreach ($slider_games as $index => $game): ?>
                        <span class="dot <?= $index === 0 ? 'active' : '' ?>"></span>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- ===== CATEGORIES SECTION ===== -->
            <section class="categories-section">
                <div class="categories-header">
                    <h1 class="categories-title">جميع التصنيفات</h1>
                </div>
                <div class="categories-grid" id="categoriesGrid">
                    <a href="index.php" class="category-card active">
                        <div class="category-icon"><i class="fas fa-th"></i></div>
                        <div class="category-name">الكل</div>
                        <div class="category-count"><?= count($all_games) ?> لعبة</div>
                    </a>

                    <?php foreach ($categories_list as $category): ?>
                        <a href="category.php?category=<?php echo $category['id']; ?>" class="category-card <?php echo $category['id']; ?>">
                            <div class="category-icon">
                                <i class="fas <?php echo $category['icon']; ?>"></i>
                            </div>
                            <div class="category-name"><?php echo $category['name']; ?></div>
                            <div class="category-count"><?php echo $category['count']; ?> لعبة</div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- ===== PS4 SECTION ===== -->
            <section class="platform-section" id="ps4-section">
                <div class="platform-header ps4">
                    <div class="platform-info">
                        <div class="platform-icon ps4"><i class="fab fa-playstation"></i></div>
                        <div class="platform-details">
                            <h2>PlayStation 4</h2>
                            <p>تحميل العاب PS4 بالعربية والإنجليزية</p>
                        </div>
                    </div>
                    <div class="platform-stats">
                        <div class="stat-item">
                            <div class="stat-number"><?= count($ps4_games) ?></div>
                            <div class="stat-label">لعبة</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">2013</div>
                            <div class="stat-label">سنة الإصدار</div>
                        </div>
                    </div>
                </div>
                <div class="games-grid" id="ps4-games">
                    <?php foreach (array_slice($ps4_games, 0, 4) as $game): ?>
                        <?= render_game_card($game) ?>
                    <?php endforeach; ?>
                </div>
                <div class="load-more-container">
                    <a href="ps4-games.php" class="load-more-btn ps4">عرض جميع ألعاب PS4</a>
                </div>
            </section>

            <div class="section-separator"></div>

            <!-- ===== PS5 SECTION ===== -->
            <section class="platform-section" id="ps5-section">
                <div class="platform-header ps5">
                    <div class="platform-info">
                        <div class="platform-icon ps5"><i class="fab fa-playstation"></i></div>
                        <div class="platform-details">
                            <h2>PlayStation 5</h2>
                            <p>تحميل العاب PS5 بالعربية والإنجليزية</p>
                        </div>
                    </div>
                    <div class="platform-stats">
                        <div class="stat-item">
                            <div class="stat-number"><?= count($ps5_games) ?></div>
                            <div class="stat-label">لعبة</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">2020</div>
                            <div class="stat-label">سنة الإصدار</div>
                        </div>
                    </div>
                </div>
                <div class="games-grid" id="ps5-games">
                    <?php foreach (array_slice($ps5_games, 0, 4) as $game): ?>
                        <?= render_game_card($game) ?>
                    <?php endforeach; ?>
                </div>
                <div class="load-more-container">
                    <a href="ps5-games.php" class="load-more-btn ps5">عرض جميع ألعاب PS5</a>
                </div>
            </section>

            <div class="section-separator"></div>

            <!-- ===== PS3 SECTION ===== -->
            <section class="platform-section" id="ps3-section">
                <div class="platform-header ps3">
                    <div class="platform-info">
                        <div class="platform-icon ps3"><i class="fab fa-playstation"></i></div>
                        <div class="platform-details">
                            <h2>PlayStation 3</h2>
                            <p>تحميل العاب PS3 بالعربية والإنجليزية</p>
                        </div>
                    </div>
                    <div class="platform-stats">
                        <div class="stat-item">
                            <div class="stat-number"><?= count($ps3_games) ?></div>
                            <div class="stat-label">لعبة</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">2006</div>
                            <div class="stat-label">سنة الإصدار</div>
                        </div>
                    </div>
                </div>
                <div class="games-grid" id="ps3-games">
                    <?php foreach (array_slice($ps3_games, 0, 4) as $game): ?>
                        <?= render_game_card($game) ?>
                    <?php endforeach; ?>
                </div>
                <div class="load-more-container">
                    <a href="ps3-games.php" class="load-more-btn ps3">عرض جميع ألعاب PS3</a>
                </div>
            </section>


            <!-- ===== TOP DOWNLOADS SECTION ===== -->
            <section class="platform-section" id="top-downloads-section">
                <div class="platform-header top-downloads-header">
                    <div class="platform-info">
                        <div class="platform-icon top-downloads-icon"><i class="fas fa-fire"></i></div>
                        <div class="platform-details">
                            <h2>الأكثر تحميلاً</h2>
                            <p>الألعاب الأكثر شعبية وتحميلاً في الموقع</p>
                        </div>
                    </div>
                    <div class="platform-stats">
                        <div class="stat-item">
                            <div class="stat-number"><?= count($all_games) ?></div>
                            <div class="stat-label">لعبة متاحة</div>
                        </div>
                    </div>
                </div>

                <div class="games-grid" id="top-downloads-grid">
                    <?php
                    $top_downloads = array_filter($all_games, function ($game) {
                        return isset($game['downloads']) && is_numeric($game['downloads']) && $game['downloads'] > 0;
                    });

                    usort($top_downloads, function ($a, $b) {
                        $dlA = isset($a['downloads']) ? $a['downloads'] : 0;
                        $dlB = isset($b['downloads']) ? $b['downloads'] : 0;
                        return $dlB <=> $dlA;
                    });

                    $top_8_downloads = array_slice($top_downloads, 0, 8);

                    foreach ($top_8_downloads as $game): ?>
                        <?= render_game_card($game) ?>
                    <?php endforeach; ?>
                </div>
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
            "logo": "https://pspkg-arabic.com/assets/images/logo.png"
        }
    </script>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebSite",
            "name": "PSPKG-arabic",
            "url": "https://pspkg-arabic.com",
            "potentialAction": {
                "@type": "SearchAction",
                "target": "https://pspkg-arabic.com/search?q={search_term_string}",
                "query-input": "required name=search_term_string"
            }
        }
    </script>

    <script src="assets/js/main.js"></script>

    <script>
        const warningVideos = [
            "https://www.youtube.com/watch?v=_c9N6OpzXK4&t=100s",
            "https://www.youtube.com/watch?v=2AVgQ2lq0e0&t=329s",
            "https://www.youtube.com/watch?v=tofwNIjP5mE&t=2s",
            "https://www.youtube.com/watch?v=qSaxmTUhHRA&t=7s",
            "https://www.youtube.com/watch?v=YTFosbbwqBw&t=102s",
            "https://www.youtube.com/watch?v=2AVgQ2lq0e0&t=331s",
            "https://www.youtube.com/watch?v=qSaxmTUhHRA",
            "https://www.youtube.com/watch?v=tofwNIjP5mE&t=24s",
            "https://www.youtube.com/watch?v=dUVqjZPr_cQ",
            "https://www.youtube.com/watch?v=3dcYqItASWY&t=64s",
            "https://www.youtube.com/watch?v=Gk2G9AuAFnk&t=611s",
            "https://www.youtube.com/watch?v=sOEYwcZzA7A&t=98s",
            "https://www.youtube.com/watch?v=XHIhrh5jifE&t=45s",
            "https://www.youtube.com/watch?v=dUVqjZPr_cQ",
        ];

        function getRandomWarningVideo() {
            const randomIndex = Math.floor(Math.random() * warningVideos.length);
            return warningVideos[randomIndex];
        }

        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            window.open(getRandomWarningVideo(), '_blank');
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C')) || (e.ctrlKey && e.key === 'u')) {
                e.preventDefault();
                window.open(getRandomWarningVideo(), '_blank');
            }
        });

        setInterval(function() {
            const widthThreshold = window.outerWidth - window.innerWidth > 160;
            const heightThreshold = window.outerHeight - window.innerHeight > 160;
            if (widthThreshold || heightThreshold) {
                window.open(getRandomWarningVideo(), '_blank');
            }
        }, 1500);
    </script>
</body>

</html>