<?php
// تعيين رأس الصفحة لضمان الترميز الصحيح
header('Content-Type: text/html; charset=UTF-8');

// NEW: تعريف رابط الموقع كثابت لتسهيل التعديل
define('SITE_URL', 'https://pspkg-arabic.com');

// NEW: معالجة إرسال التعليقات والبلاغات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // معالجة إرسال التعليقات
    if (isset($_POST['action']) && $_POST['action'] === 'add_comment') {
        $gameId = isset($_POST['game_id']) ? intval($_POST['game_id']) : 0;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

        if (!empty($name) && !empty($comment) && $gameId > 0) {
            // إنشاء مجلد التعليقات إذا لم يكن موجودًا
            if (!is_dir(__DIR__ . '/data/comments')) {
                mkdir(__DIR__ . '/data/comments', 0777, true);
            }

            // تحضير بيانات التعليق
            $commentData = [
                'id' => uniqid(),
                'game_id' => $gameId,
                'name' => htmlspecialchars($name),
                'comment' => htmlspecialchars($comment),
                'date' => date('Y-m-d H:i:s'),
                'approved' => false // تحتاج لموافقة الإدارة قبل الظهور
            ];

            // حفظ التعليق في ملف JSON
            $commentsFile = __DIR__ . '/data/comments/game_' . $gameId . '.json';
            $comments = [];

            if (file_exists($commentsFile)) {
                $jsonContent = file_get_contents($commentsFile);
                $comments = json_decode($jsonContent, true) ?: [];
            }

            $comments[] = $commentData;
            file_put_contents($commentsFile, json_encode($comments, JSON_PRETTY_PRINT));

            // إرسال بريد إلكتروني للإدارة (إذا كانت وظيفة البريد متاحة)
            $to = 'admin@pspkg-arabic.com'; // غير هذا البريد إلى بريد الإدارة
            $subject = 'تعليق جديد على لعبة: ' . ($gameData['title'] ?? 'غير معروف');
            $message = "تم إضافة تعليق جديد:\n\n";
            $message .= "الاسم: " . $name . "\n";
            $message .= "التعليق: " . $comment . "\n";
            $message .= "تاريخ الإضافة: " . date('Y-m-d H:i:s') . "\n";
            $message .= "رابط اللعبة: " . SITE_URL . '/download.php?id=' . $gameId . "\n";

            // استخدم هذه الدالة فقط إذا كان لديك إعدادات بريد صحيحة
            // mail($to, $subject, $message);

            // إرجاع استجابة JSON
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'تم إرسال تعليقك بنجاح وسيتم مراجعته من قبل الإدارة.']);
            exit;
        }
    }

    // معالجة إرسال البلاغات
    if (isset($_POST['action']) && $_POST['action'] === 'submit_report') {
        $gameId = isset($_POST['game_id']) ? intval($_POST['game_id']) : 0;
        $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
        $details = isset($_POST['details']) ? trim($_POST['details']) : '';

        if (!empty($reason) && !empty($details) && $gameId > 0) {
            // إنشاء مجلد البلاغات إذا لم يكن موجودًا
            if (!is_dir(__DIR__ . '/data/reports')) {
                mkdir(__DIR__ . '/data/reports', 0777, true);
            }

            // تحضير بيانات البلاغ
            $reportData = [
                'id' => uniqid(),
                'game_id' => $gameId,
                'game_title' => $gameData['title'] ?? 'غير معروف',
                'reason' => htmlspecialchars($reason),
                'details' => htmlspecialchars($details),
                'date' => date('Y-m-d H:i:s'),
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT']
            ];

            // حفظ البلاغ في ملف JSON
            $reportsFile = __DIR__ . '/data/reports/reports.json';
            $reports = [];

            if (file_exists($reportsFile)) {
                $jsonContent = file_get_contents($reportsFile);
                $reports = json_decode($jsonContent, true) ?: [];
            }

            $reports[] = $reportData;
            file_put_contents($reportsFile, json_encode($reports, JSON_PRETTY_PRINT));

            // إرسال بريد إلكتروني للإدارة (إذا كانت وظيفة البريد متاحة)
            $to = 'admin@pspkg-arabic.com'; // غير هذا البريد إلى بريد الإدارة
            $subject = 'بلاغ جديد على لعبة: ' . ($gameData['title'] ?? 'غير معروف');
            $message = "تم إرسال بلاغ جديد:\n\n";
            $message .= "السبب: " . $reason . "\n";
            $message .= "التفاصيل: " . $details . "\n";
            $message .= "تاريخ الإبلاغ: " . date('Y-m-d H:i:s') . "\n";
            $message .= "عنوان IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
            $message .= "رابط اللعبة: " . SITE_URL . '/download.php?id=' . $gameId . "\n";

            // استخدم هذه الدالة فقط إذا كان لديك إعدادات بريد صحيحة
            // mail($to, $subject, $message);

            // إرجاع استجابة JSON
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'تم إرسال بلاغك بنجاح. شكرًا لك!']);
            exit;
        }
    }
}

// NEW: دالة لجلب التعليقات المعتمدة للعبة الحالية
function getApprovedComments($gameId)
{
    $commentsFile = __DIR__ . '/data/comments/game_' . $gameId . '.json';
    if (!file_exists($commentsFile)) {
        return [];
    }

    $jsonContent = file_get_contents($commentsFile);
    $comments = json_decode($jsonContent, true) ?: [];

    // إرجاع التعليقات المعتمدة فقط
    return array_filter($comments, function ($comment) {
        return isset($comment['approved']) && $comment['approved'] === true;
    });
}

// متغيرات لتخزين بيانات اللعبة
$gameData = null;
$allGamesData = [];

// تعريف التصنيفات
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

// الحصول على التصنيف النشط من الرابط
$activeCategory = isset($_GET['category']) ? $_GET['category'] : 'all';

// NEW: دالة لتنسيق عنوان اللعبة (مطلوبة للألعاب ذات الصلة)
function format_title_for_url($title)
{
    if (empty($title)) return '';
    $title = strtolower(trim($title));
    $title = preg_replace('/[^a-z0-9\s\-]/', '', $title);
    $title = preg_replace('/[\s-]+/', '-', $title);
    return $title;
}
// دالة لإنشاء النجوم بناءً على تقييم من 10
function create_php_rating_stars($rating) {
    $rating = floatval($rating);
    if ($rating > 10) $rating = 10;
    if ($rating < 0) $rating = 0;

    $scaledRating = $rating / 2; // تحويل المقياس من 10 إلى 5
    $fullStars = floor($scaledRating);
    $remainder = $scaledRating - $fullStars;
    
    $hasHalfStar = false;
    if ($remainder >= 0.25 && $remainder < 0.75) {
        $hasHalfStar = true;
    } elseif ($remainder >= 0.75) {
        $fullStars++;
    }

    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);

    $html = '';
    for ($i = 0; $i < $fullStars; $i++) $html .= '<i class="fas fa-star"></i>';
    if ($hasHalfStar) $html .= '<i class="fas fa-star-half-alt"></i>';
    for ($i = 0; $i < $emptyStars; $i++) $html .= '<i class="far fa-star"></i>';

    return $html;
}
// NEW: دالة للحصول على مسار الصورة (مطلوبة للألعاب ذات الصلة)
function get_image_path($image_path_from_json)
{
    if (empty($image_path_from_json)) return 'assets/images/default-game.jpg';
    if (strpos($image_path_from_json, 'http') === 0 || strpos($image_path_from_json, '/') === 0) {
        return htmlspecialchars($image_path_from_json);
    }
    return 'assets/images/' . htmlspecialchars(ltrim($image_path_from_json, '/'));
}

// NEW: دالة لمعالجة بيانات اللغات وعرضها بشكل منظم
function format_languages($languages)
{
    if (empty($languages)) {
        return 'غير محدد';
    }

    $result = [];

    // التحقق من أن اللغات مصفوفة
    if (is_array($languages)) {
        // التحقق إذا كانت مصفوفة من الكائنات
        if (isset($languages[0]) && is_array($languages[0])) {
            // التحقق من وجود ترجمة، دبلجة، واجهة
            if (isset($languages[0]['subtitles'])) {
                $subtitles = [];
                foreach ($languages[0]['subtitles'] as $lang) {
                    if (is_array($lang)) {
                        $subtitles[] = $lang['name'] ?? $lang['arabic'] ?? $lang['english'] ?? 'غير محدد';
                    } else {
                        $subtitles[] = $lang;
                    }
                }
                if (!empty($subtitles)) {
                    $result[] = 'ترجمة: ' . implode('، ', $subtitles);
                }
            }

            if (isset($languages[0]['audio'])) {
                $audio = [];
                foreach ($languages[0]['audio'] as $lang) {
                    if (is_array($lang)) {
                        $audio[] = $lang['name'] ?? $lang['arabic'] ?? $lang['english'] ?? 'غير محدد';
                    } else {
                        $audio[] = $lang;
                    }
                }
                if (!empty($audio)) {
                    $result[] = 'دبلجة: ' . implode('، ', $audio);
                }
            }

            if (isset($languages[0]['interface'])) {
                $interface = [];
                foreach ($languages[0]['interface'] as $lang) {
                    if (is_array($lang)) {
                        $interface[] = $lang['name'] ?? $lang['arabic'] ?? $lang['english'] ?? 'غير محدد';
                    } else {
                        $interface[] = $lang;
                    }
                }
                if (!empty($interface)) {
                    $result[] = 'واجهة: ' . implode('، ', $interface);
                }
            }

            // إذا لم توجد أنواع محددة، جرب جمع كل القيم
            if (empty($result)) {
                $allLangs = [];
                foreach ($languages as $langGroup) {
                    foreach ($langGroup as $key => $langs) {
                        if (is_array($langs)) {
                            foreach ($langs as $lang) {
                                if (is_array($lang)) {
                                    $allLangs[] = $lang['name'] ?? $lang['arabic'] ?? $lang['english'] ?? 'غير محدد';
                                } else {
                                    $allLangs[] = $lang;
                                }
                            }
                        }
                    }
                }
                if (!empty($allLangs)) {
                    $result[] = implode('، ', array_unique($allLangs));
                }
            }
        } else {
            // معالجة مصفوفة بسيطة من اللغات
            foreach ($languages as $lang) {
                if (is_array($lang)) {
                    $result[] = $lang['name'] ?? $lang['arabic'] ?? $lang['english'] ?? 'غير محدد';
                } else {
                    $result[] = $lang;
                }
            }
        }
    } elseif (is_string($languages)) {
        // إذا كانت اللغات نصًا
        $result[] = $languages;
    }

    return !empty($result) ? implode(' • ', $result) : 'غير محدد';
}

// دالة لتحميل بيانات الألعاب من ملف JSON مع التحقق من الأخطاء
function loadGamesFromJson($filePath)
{
    if (!file_exists($filePath)) {
        error_log("JSON FILE NOT FOUND: " . $filePath);
        return [];
    }
    $jsonContent = file_get_contents($filePath);
    if ($jsonContent === false) {
        error_log("FAILED TO READ FILE: " . $filePath);
        return [];
    }
    $data = json_decode($jsonContent, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON DECODE ERROR in " . $filePath . ": " . json_last_error_msg());
        return [];
    }
    return is_array($data) ? $data : [];
}

// تحميل جميع بيانات الألعاب
$allGamesData = array_merge(
    loadGamesFromJson(__DIR__ . '/data/ps4-games.json'),
    loadGamesFromJson(__DIR__ . '/data/ps5-games.json'),
    loadGamesFromJson(__DIR__ . '/data/ps3-games.json')
);

// الحصول على معرف اللعبة من الرابط
$gameId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// البحث عن اللعبة المحددة
if ($gameId > 0) {
    foreach ($allGamesData as $game) {
        if (isset($game['id']) && $game['id'] === $gameId) {
            $gameData = $game;
            break;
        }
    }
}

// NEW: التعامل مع حالة عدم العثور على اللعبة
if (empty($gameData)) {
    // يجب أن يكون لديك ملف 404.php
    // include('404.php');
    // exit();
    // مؤقتًا، سنعرض رسالة خطأ بسيطة إذا لم يوجد الملف
    $gameData = [
        'title' => 'اللعبة غير موجودة',
        'story' => 'عذراً، لم يتم العثور على اللعبة التي تبحث عنها.',
        'platform' => '--',
        'genre' => [],
        'image' => 'assets/images/default-game.jpg'
    ];
}

// *** بداية التعديل: منطق عداد التحميلات (دمج JSON مع TXT) ***
$downloadCount = 0;
$counterFile = __DIR__ . '/data/counters/' . $gameId . '.txt';

// إنشاء مجلد العدادات إذا لم يكن موجودًا
if (!is_dir(__DIR__ . '/data/counters')) {
    mkdir(__DIR__ . '/data/counters', 0777, true);
}

// 1. قراءة العداد المحلي من ملف txt (التحميلات الإضافية فقط)
$localCount = 0;
if (file_exists($counterFile)) {
    $localCount = (int)file_get_contents($counterFile);
}

// 2. جلب العدد الأساسي من بيانات اللعبة (JSON)
$baseCount = isset($gameData['downloads']) ? (int)$gameData['downloads'] : 0;

// 3. العدد الإجمالي الذي سيظهر في الصفحة
$downloadCount = $baseCount + $localCount;
// *** نهاية التعديل ***

// NEW: منطق جلب الألعاب ذات الصلة
$relatedGames = [];
if (!empty($gameData['genre'])) {
    $currentGameGenres = $gameData['genre'];
    $currentGamePlatform = $gameData['platform'];

    $relatedGames = array_filter($allGamesData, function ($game) use ($currentGameGenres, $currentGamePlatform, $gameId) {
        if ($game['id'] === $gameId) return false; // استبعاد اللعبة الحالية

        $hasCommonGenre = !empty(array_intersect($game['genre'] ?? [], $currentGameGenres));
        $isSamePlatform = $game['platform'] === $currentGamePlatform;

        return $hasCommonGenre || $isSamePlatform;
    });

    shuffle($relatedGames);
    $relatedGames = array_slice($relatedGames, 0, 4);
}

// NEW: منطق جلب الألعاب الشائعة (عشوائية كمثال)
$popularGames = $allGamesData;
shuffle($popularGames);
$popularGames = array_slice($popularGames, 0, 5);

// NEW: جلب التعليقات المعتمدة للعبة الحالية
$approvedComments = getApprovedComments($gameId);

// *** بداية التعديلات على الـ SEO ***
// تجهيز المتغيرات للاستخدام في الـ meta tags
$gameTitle = htmlspecialchars($gameData['title'] ?? 'لعبة غير معروفة');
$gamePlatform = htmlspecialchars($gameData['platform'] ?? '');
$gameGenres = is_array($gameData['genre'] ?? []) ? implode('، ', array_map('htmlspecialchars', $gameData['genre'])) : htmlspecialchars($gameData['genre'] ?? '');
$gameSize = htmlspecialchars($gameData['size'] ?? '');
$gameDescription = htmlspecialchars($gameData['story'] ?? '');
$gameImage = get_image_path($gameData['image'] ?? '');
$currentUrl = SITE_URL . '/download.php?id=' . $gameId;

// إنشاء وصف ديناميكي ومختصر مع إضافة اسم الموقع الجديد للعلامة التجارية
$metaDescription = "تحميل لعبة {$gameTitle} لمنصة {$gamePlatform} مجاناً. {$gameGenres} بحجم {$gameSize}. | PSPKG-arabic - أفضل موقع لتحميل العاب بلايستيشن PS4, PS5, PS3 مجاناً بالعربي والإنجليزية";
// *** نهاية التعديلات على الـ SEO ***
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- ===== تعديل SEO ديناميكي مع الاسم الجديد ===== -->
    <!-- تم التعديل هنا لجعل العنوان ديناميكيًا لكل لعبة -->
    <title><?php echo $gameTitle; ?> - PSPKG-arabic | أفضل موقع لتحميل العاب بلايستيشن PS4, PS5, PS3 مجاناً</title>
    <meta name="description" content="<?php echo $metaDescription; ?>">
    <meta name="keywords" content="<?php echo $gameTitle; ?>, <?php echo $gamePlatform; ?>, تحميل <?php echo $gameTitle; ?>, <?php echo $gameGenres; ?>, PSPKG, PKG, ألعاب بلايستيشن">
    <link rel="canonical" href="<?php echo $currentUrl; ?>">

    <!-- Open Graph Tags للسوشيال ميديا (ديناميكي) -->
    <meta property="og:title" content="<?php echo $gameTitle; ?> - PSPKG-arabic | أفضل موقع لتحميل العاب بلايستيشن">
    <meta property="og:description" content="<?php echo $metaDescription; ?>">
    <meta property="og:image" content="<?php echo $gameImage; ?>">
    <meta property="og:url" content="<?php echo $currentUrl; ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_AR">

    <!-- Twitter Card Tags (ديناميكي) -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $gameTitle; ?> - PSPKG-arabic | أفضل موقع لتحميل العاب بلايستيشن">
    <meta name="twitter:description" content="<?php echo $metaDescription; ?>">
    <meta name="twitter:image" content="<?php echo $gameImage; ?>"
        <--=====نهاية تعديل SEO=====-->

    <!-- باقي العلامات الحالية -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- ===== روابط Favicon ===== -->
    <!-- تأكد من وجود هذه الملفات في مجلد الموقع الرئيسي -->
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" href="/favicon.ico">
    <link rel="manifest" href="/site.webmanifest">

    <link rel="stylesheet" href="assets/css/download.css">

    <!-- CSS إضافي لتحسين التصميم -->
    <style>
        .success-message {
            display: none;
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
            text-align: center;
        }

        .comment-item {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-right: 4px solid #007bff;
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .comment-name {
            font-weight: bold;
            color: #007bff;
        }

        .comment-date {
            font-size: 0.85rem;
            color: #666;
        }

        .comment-text {
            line-height: 1.5;
        }
    </style>

    <!-- ===== إضافة بيانات منظمة (Schema.org) للعبة مع الميزات ===== -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "VideoGame",
            "name": "<?php echo $gameTitle; ?>",
            "description": "<?php echo $gameDescription; ?>",
            "genre": <?php echo json_encode($gameData['genre'] ?? []); ?>,
            "gamePlatform": "<?php echo $gamePlatform; ?>",
            "publisher": "<?php echo htmlspecialchars($gameData['publisher'] ?? ''); ?>",
            "datePublished": "<?php echo htmlspecialchars($gameData['releaseDate'] ?? ''); ?>",
            "image": "<?php echo $gameImage; ?>",
            "url": "<?php echo $currentUrl; ?>",
            "featureList": <?php echo json_encode($gameData['features'] ?? []); ?>
        }
    </script>
</head>

<body>

    <!-- ===== HEADER ===== -->
    <?php require_once 'includes/header.php'; ?>
    <!-- ===== SCROLL TO TOP BUTTON ===== -->
    <button class="scroll-to-top" id="scrollToTop">
        <i class="fas fa-arrow-up"></i>
    </button>


    <!-- ===== MAIN PAGE CONTENT ===== -->
    <div id="pageContentWrapper">
        <header class="page-header" id="pageHeader" style="background-image: url('<?php echo htmlspecialchars($gameData['headerImage'] ?? $gameData['image'] ?? ''); ?>');">
            <div class="container">
                <h1 class="game-title" id="gameTitle"><?php echo $gameTitle; ?></h1>

                <div class="game-code-info-bar">
                    <div class="meta-item">
                        <i class="fab fa-playstation"></i>
                        <span>المنصة:</span>
                        <span id="gamePlatform"><?php echo $gamePlatform; ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-tag"></i>
                        <span>النوع:</span>
                        <span id="gameGenre"><?php echo $gameGenres; ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-code-branch"></i>
                        <span>التحديث:</span>
                        <span id="gameVersion"><?php echo htmlspecialchars($gameData['version'] ?? '--'); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-microchip"></i>
                        <span>الكود:</span>
                        <h4 id="displayGameCode"><?php echo htmlspecialchars($gameData['gameCode'] ?? 'CUSA-----'); ?></h4>
                    </div>
                </div>
            </div>
        </header>

        <main class="main-content container">
            <div class="content-grid">
                <div class="left-column">
                    <section class="story-section section">
                        <h2>القصة</h2>
                        <p id="gameStory"><?php echo $gameDescription; ?></p>
                    </section>

                    <!-- ===== قسم الميزات (تحت القصة مباشرة) ===== -->
                    <section class="features-section section">
                        <h2>ميزات اللعبة</h2>
                        <div class="features-list" id="featuresList">
                            <?php
                            if (!empty($gameData['features']) && is_array($gameData['features'])) {
                                foreach ($gameData['features'] as $feature) {
                                    echo '<div class="feature-item">';
                                    echo '<i class="fas fa-check-circle"></i>';
                                    echo '<span>' . htmlspecialchars($feature) . '</span>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p>لا توجد معلومات عن ميزات هذه اللعبة.</p>';
                            }
                            ?>
                        </div>
                    </section>

                    <section class="download-section section">
                        <!-- ===== NEW COMPACT UPLOAD STATUS SECTION ===== -->


                        <h3>روابط التحميل </h3>
                        <div id="downloadCategories">
                            <!-- Download categories will be dynamically generated here -->
                        </div>

                        <!-- ===== NEW DOWNLOAD COUNTER SECTION ===== -->
                        <div class="download-counter">
                            <i class="fas fa-download"></i>
                            <!-- MODIFIED: استخدام العداد الحقيقي -->
                            <span class="download-counter-text">تم تحميل هذه اللعبة <span class="download-counter-number" id="downloadCount"><?php echo number_format($downloadCount); ?></span> مرة</span>
                        </div>

                        <!-- ===== NEW ENHANCED PASSWORD SECTION ===== -->
                        <div class="password-section">
                            <div class="password-header">
                                <i class="fas fa-key"></i>
                                <h4>كلمة السر لفك الضغط</h4>
                            </div>
                            <button class="password-modal-trigger" onclick="openPasswordModal()">
                                <i class="fas fa-lock"></i>
                                <span>اضغط هنا للحصول على كلمة السر</span>
                            </button>
                            <p class="password-note">
                                <i class="fas fa-info-circle"></i>
                                اضغط على الزر أعلاه لعرض كلمة السر في نافذة خاصة
                            </p>
                        </div>
                    </section>
                    <div class="popup-box" id="alertPopup">
                        <button class="close-btn" onclick="closePopup()">&times;</button>

                        <!-- تم تغيير العنوان -->
                        <h1>كلمات السر ⚠️</h1>

                        <!-- قائمة كلمات السر -->
                        <ul>
                            <li>SuperPSX</li>
                            <li>pspkg-arabic</li>
                            <li>DLPSGAME.COM</li>
                            <li>downloadgameps3.com</li>
                            <li>hako</li>
                            <li>free-wargamer.com</li>
                            <li>FREE WAR GAMER</li>
                        </ul>
                    </div>
                    <!-- ===== NEW RELATED GAMES SECTION (REPLACING COMMENTS) ===== -->
                    <section class="related-games-section section">
                        <div class="related-games-header">
                            <h4><i class="fas fa-gamepad"></i> ألعاب ذات صلة</h4>
                            <button class="view-all-btn">
                                عرض الكل
                                <i class="fas fa-arrow-left"></i>
                            </button>
                        </div>
                        <!-- NEW: عرض الألعاب ذات صلة ديناميكيًا -->
                        <div class="related-games-grid" id="relatedGamesGrid">
                            <?php foreach ($relatedGames as $game): ?>
                                <a href="download.php?id=<?php echo $game['id']; ?>&title=<?php echo format_title_for_url($game['title']); ?>" class="related-game-card">
                                    <img src="<?php echo get_image_path($game['image']); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>">
                                    <h5><?php echo htmlspecialchars($game['title']); ?></h5>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>

                <aside class="sidebar">
                    <div class="sidebar-card">
                        <h4><i class="fas fa-info-circle"></i> معلومات إضافية</h4>
                        <div class="info-list">
                            <p>كود اللعبة <span id="gameCode"><?php echo htmlspecialchars($gameData['gameCode'] ?? '--'); ?></span></p>
                            <p>المطور <span id="gameDeveloper"><?php echo htmlspecialchars($gameData['developer'] ?? '--'); ?></span></p>
                            <p>الناشر <span id="gamePublisher"><?php echo htmlspecialchars($gameData['publisher'] ?? '--'); ?></span></p>
                            <p>تاريخ الإصدار <span id="gameReleaseDate"><?php echo htmlspecialchars($gameData['releaseDate'] ?? '--'); ?></span></p>
                            <p>لغة اللعبة <span id="gameLanguages"><?php
                                                                    $languages = $gameData['languages'] ?? [];
                                                                    if (is_array($languages)) {
                                                                        echo htmlspecialchars(implode('، ', array_column($languages, 'name')));
                                                                    } else {
                                                                        echo htmlspecialchars($languages);
                                                                    }
                                                                    ?></span></p>
                            <p>حجم اللعبة <span id="gameSize"><?php echo $gameSize; ?></span></p>
                            <p>نظام الجهاز <span id="systemVersion"><?php echo htmlspecialchars($gameData['systemVersion'] ?? '--'); ?></span></p>
                        </div>
                    </div>

                    <div class="sidebar-card">
                        <h4><i class="fas fa-star"></i> التقييم</h4>
                        <div class="rating" id="gameRating"><?php echo htmlspecialchars($gameData['rating'] ?? '--'); ?></div>
                        <div class="rating-stars" id="ratingStars"></div>
                    </div>

                                    <!-- ===== NEW POPULAR GAMES SECTION ===== -->
                    <div class="sidebar-card">
                        <h4><i class="fas fa-fire"></i> الألعاب الشائعة</h4>
                        <div class="popular-games-section">
                            <div class="popular-games-list" id="popularGamesList">
                                <?php foreach ($popularGames as $game): 
                                    $formattedTitle = format_title_for_url($game['title']);
                                    $trend = isset($game['trend']) ? $game['trend'] : 'up';
                                    $trendIcon = ($trend == 'down') ? 'fa-arrow-down' : (($trend == 'same') ? 'fa-minus' : 'fa-arrow-up');
                                ?>
                                    <div class="popular-game-item" onclick="window.location.href='download.php?id=<?php echo $game['id']; ?>&title=<?php echo $formattedTitle; ?>'">
                                        <div class="popular-game-image">
                                            <img src="<?php echo get_image_path($game['image']); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>" class="popular-game-actual-image loaded" style="opacity: 1;">
                                        </div>
                                        <div class="popular-game-info">
                                            <div class="popular-game-title"><?php echo htmlspecialchars($game['title']); ?></div>
                                            <div class="popular-game-meta">
                                                <div class="popular-game-platform">
                                                    <i class="fab fa-playstation"></i>
                                                    <span><?php echo strtoupper($game['platform']); ?></span>
                                                </div>
                                                <div class="popular-game-rating">
                                                    <?php echo create_php_rating_stars($game['rating'] ?? 0); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="popular-game-trend <?php echo $trend; ?>">
                                            <i class="fas <?php echo $trendIcon; ?>"></i>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </main>


        <!-- ===== NEW COMMENTS SECTION ===== -->
        <section class="comments-section section">
            <div class="comments-header">
                <h4><i class="fas fa-comments"></i> التعليقات</h4>
                <div class="comments-count">
                    <span id="commentsCount"><?php echo count($approvedComments); ?></span> تعليق
                </div>
            </div>

            <!-- Add Comment Form -->
            <div class="comment-form-container">
                <div class="comment-form">
                    <div class="form-group">
                        <input type="text" id="commentName" placeholder="اسمك" class="comment-input">
                    </div>
                    <div class="form-group">
                        <textarea id="commentText" placeholder="اكتب تعليقك هنا..." class="comment-textarea"></textarea>
                    </div>
                    <button class="comment-submit-btn" id="submitComment">
                        <i class="fas fa-paper-plane"></i>
                        <span>نشر التعليق</span>
                    </button>
                    <div id="commentMessage" class="success-message"></div>
                </div>
            </div>

            <!-- Comments List -->
            <div class="comments-list" id="commentsList">
                <?php if (empty($approvedComments)): ?>
                    <p class="no-comments">لا توجد تعليقات بعد. كن أول من يعلق!</p>
                <?php else: ?>
                    <?php foreach ($approvedComments as $comment): ?>
                        <div class="comment-item">
                            <div class="comment-header">
                                <span class="comment-name"><?php echo $comment['name']; ?></span>
                                <span class="comment-date"><?php echo date('d/m/Y', strtotime($comment['date'])); ?></span>
                            </div>
                            <div class="comment-text"><?php echo nl2br($comment['comment']); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Load More Comments -->
            <div class="load-more-container">
                <button class="load-more-btn" id="loadMoreComments" style="display: none;">
                    <i class="fas fa-chevron-down"></i>
                    <span>عرض المزيد من التعليقات</span>
                </button>
            </div>
        </section>

        <!-- ===== NEW REPORT SECTION ===== -->
        <section class="report-section section">
            <div class="report-header">
                <h4><i class="fas fa-exclamation-triangle"></i> التبليغ عن مشكلة</h4>
            </div>

            <!-- Report Form -->
            <div class="report-form-container">
                <form id="reportForm" class="report-form">
                    <div class="form-group">
                        <label for="reportReason">سبب التبليغ:</label>
                        <select id="reportReason" name="reason" required>
                            <option value="">اختر السبب</option>
                            <option value="broken_link">رابط لا يعمل</option>
                            <option value="corrupted_file">ملف تالف</option>
                            <option value="wrong_info">معلومات خاطئة</option>
                            <option value="other">أخرى</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="reportDetails">تفاصيل المشكلة:</label>
                        <textarea id="reportDetails" name="details" placeholder="اكتب تفاصيل المشكلة هنا..." required></textarea>
                    </div>
                    <button type="submit" class="report-submit-btn">
                        <i class="fas fa-paper-plane"></i>
                        <span>إرسال التبليغ</span>
                    </button>
                </form>

                <!-- Success Message -->
                <div id="reportSuccessMessage" class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <span>تم إرسال تقريرك بنجاح. شكرًا لك!</span>
                </div>
            </div>
        </section>
    </div>
    <!-- ===== FOOTER ===== -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-left">
                    <div class="social-links">
                        <!-- IMPORTANT: Replace '#' with your actual social media links -->
                        <a href="https://www.youtube.com/your-channel" target="_blank" title="YouTube" aria-label="YouTube" class="youtube">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="https://www.facebook.com/your-page" target="_blank" title="Facebook" aria-label="Facebook" class="facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://t.me/your-channel" target="_blank" title="Telegram" aria-label="Telegram" class="telegram">
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
                    <h3 class="footer-title">هدفنا</h3>
                    <div class="footer-about-text">
                        <p>لا شك أن عشاق بلايستيشن يبحثون دائمًا عن أفضل مكان لتحميل ألعابهم المفضلة مجانًا، وذلك بفضل إدارة وفريق <b>pspkg-arabic</b> الذي يسعى لتقديم أفضل تجربة ممكنة.</p>

                        <p>موقعنا هو الساحة المثالية لعشاق ألعاب <b>PS4</b> و<b>PS5</b> و<b>PS3</b>، حيث يمكنك تحميل جميع الألعاب بصيغة <b>WIRAR/PKG</b> بشكل مجاني تمامًا وسهل.</p>

                        <p>جميع الألعاب جاهزة للتثبيت مباشرة، ولا تحتاج إلى أي خطوات إضافية بعد التحميل، كما أنها مضغوطة بدون حذف أي محتوى، مما يجعل حجمها أصغر مع الحفاظ على الجودة الكاملة.</p>

                        <p>نُقدم مجموعة كبيرة من <b>الألعاب المعربة بالكامل</b>، سواء بتعريب رسمي من الشركات أو تعريب غير رسمي من فرق محترفة، لتستمتع بتجربة لعب <b>باللغة العربية</b> كما لم تعشها من قبل.</p>

                        <p>لقد قمنا بتبسيط طريقة التحميل لتكون سريعة وآمنة، ونسعى لأن نكون المنصة الأفضل لتحميل ألعاب <b>PKG</b> مجانًا على جميع أجهزة بلايستيشن.</p>

                        <p>نحن لا نستخدم نسخًا <b>معيوبة أو تحتوي على فيروسات</b>، بل نقدم نسخًا نظيفة وموثوقة معدّة خصيصًا لعشاق بلايستيشن.</p>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> PSPKG-arabic. All Rights Reserved.</p>
                <p style="margin-top: 5px; font-size: 0.85rem;">أفضل موقع لتحميل العاب بلايستيشن مجاناً بالعربي والإنجليزية</p>
            </div>
        </div>
    </footer>

    <!-- ===== NEW PASSWORD MODAL ===== -->
    <div class="password-modal-overlay" id="passwordModal">
        <div class="password-modal">
            <button class="modal-close-btn" onclick="closePasswordModal()">
                <i class="fas fa-times"></i>
            </button>

            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h2 class="modal-title">كلمة السر السحرية</h2>
                <p class="modal-subtitle">انسخ هذه الكلمة لفك ضغط ملفات اللعبة</p>
            </div>

            <div class="modal-password-display">
                <div class="modal-password-text" id="modalPasswordText"><?php echo htmlspecialchars($gameData['password'] ?? '4GAMER-2024'); ?></div>
            </div>

            <div class="modal-actions">
                <button class="modal-copy-btn" id="modalCopyBtn" onclick="copyModalPassword()">
                    <i class="fas fa-copy"></i>
                    <span>نسخ كلمة السر</span>
                </button>
            </div>

            <div class="modal-steps">
                <h4><i class="fas fa-list-ol"></i> خطوات فك الضغط:</h4>
                <div class="step-list">
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <span>قم بتحميل جميع أجزاء اللعبة</span>
                    </div>
                    <div class="step-item">
                        <div class="step-number">2</div>
                        <span>ضع جميع الملفات في مجلد واحد</span>
                    </div>
                    <div class="step-item">
                        <div class="step-number">3</div>
                        <span>استخدم برنامج WinRAR أو 7-Zip</span>
                    </div>
                    <div class="step-item">
                        <div class="step-number">4</div>
                        <span>أدخل كلمة السر عند الطلب</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- بيانات المنظمة (Organization) -->
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


    <!-- تمرير بيانات اللعبة وكل الألعاب إلى JavaScript -->
    <script>
        // بيانات اللعبة الحالية من PHP
        window.currentGameData = <?php echo json_encode($gameData); ?>;
        // بيانات كل الألعاب للبحث والألعاب ذات الصلة
        window.allGamesData = <?php echo json_encode(array_values($allGamesData)); ?>;
        // معرف اللعبة الحالية
        window.currentGameId = <?php echo $gameId; ?>;
    </script>

    <!-- JavaScript للتعامل مع التعليقات والبلاغات -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // التعامل مع نموذج التعليقات
            const commentForm = document.getElementById('submitComment');
            const commentName = document.getElementById('commentName');
            const commentText = document.getElementById('commentText');
            const commentMessage = document.getElementById('commentMessage');

            if (commentForm) {
                commentForm.addEventListener('click', function(e) {
                    e.preventDefault();

                    const name = commentName.value.trim();
                    const comment = commentText.value.trim();

                    if (!name || !comment) {
                        showMessage(commentMessage, 'الرجاء ملء جميع الحقول', 'error');
                        return;
                    }

                    // إرسال التعليق عبر AJAX
                    const formData = new FormData();
                    formData.append('action', 'add_comment');
                    formData.append('game_id', window.currentGameId);
                    formData.append('name', name);
                    formData.append('comment', comment);

                    fetch('download.php?id=' + window.currentGameId, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showMessage(commentMessage, data.message, 'success');
                                commentName.value = '';
                                commentText.value = '';
                            } else {
                                showMessage(commentMessage, 'حدث خطأ أثناء إرسال التعليق', 'error');
                            }
                        })
                        .catch(error => {
                            showMessage(commentMessage, 'حدث خطأ أثناء إرسال التعليق', 'error');
                            console.error('Error:', error);
                        });
                });
            }

            // التعامل مع نموذج البلاغات
            const reportForm = document.getElementById('reportForm');
            const reportSuccessMessage = document.getElementById('reportSuccessMessage');

            if (reportForm) {
                reportForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const reason = document.getElementById('reportReason').value;
                    const details = document.getElementById('reportDetails').value.trim();

                    if (!reason || !details) {
                        showMessage(reportSuccessMessage, 'الرجاء ملء جميع الحقول', 'error');
                        return;
                    }

                    // إرسال البلاغ عبر AJAX
                    const formData = new FormData();
                    formData.append('action', 'submit_report');
                    formData.append('game_id', window.currentGameId);
                    formData.append('reason', reason);
                    formData.append('details', details);

                    fetch('download.php?id=' + window.currentGameId, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showMessage(reportSuccessMessage, data.message, 'success');
                                reportForm.reset();
                            } else {
                                showMessage(reportSuccessMessage, 'حدث خطأ أثناء إرسال البلاغ', 'error');
                            }
                        })
                        .catch(error => {
                            showMessage(reportSuccessMessage, 'حدث خطأ أثناء إرسال البلاغ', 'error');
                            console.error('Error:', error);
                        });
                });
            }

            // دالة لعرض الرسائل
            function showMessage(element, message, type) {
                element.textContent = message;
                element.style.display = 'block';

                if (type === 'error') {
                    element.style.backgroundColor = '#f44336';
                } else {
                    element.style.backgroundColor = '#4CAF50';
                }

                // إخفاء الرسالة بعد 5 ثوانٍ
                setTimeout(() => {
                    element.style.display = 'none';
                }, 5000);
            }
        });
    </script>

    <!-- JavaScript -->
    <script src="assets/js/download.js"></script>
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