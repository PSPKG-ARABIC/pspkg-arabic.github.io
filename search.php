<?php
require_once 'config.php';

// إذا كانت كلمة البحث فارغة، أرجعه للصفحة الرئيسية
if (!isset($_GET['q']) || trim($_GET['q']) === '') {
    header("Location: index.php");
    exit;
}

// استقبال وتأمين كلمة البحث
 $searchQuery = trim($_GET['q']);
 $safeQuery = htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8');

// دالة لتنسيق عنوان اللعبة ليكون مناسبًا للروابط
function formatTitleForUrl($title) {
    if (empty($title)) return '';
    $title = strtolower(trim($title));
    $title = preg_replace('/[^a-z0-9\s\-]/u', '', $title);
    $title = preg_replace('/[\s-]+/', '-', $title);
    return trim($title, '-');
}

// دالة تنسيق عدد التحميلات
function formatDownloads($count) {
    if (!isset($count) || !is_numeric($count)) return '0';
    if ($count >= 1000000) return round($count / 1000000, 1) . 'M';
    if ($count >= 1000) return round($count / 1000, 1) . 'K';
    return $count;
}

// دالة لتمييز كلمة البحث في النص (بدون XSS)
function highlightText($text, $query) {
    if (empty($query)) return htmlspecialchars($text);
    $safeText = htmlspecialchars($text);
    $safeQueryForRegex = preg_quote(htmlspecialchars($query), '/');
    return preg_replace('/(' . $safeQueryForRegex . ')/i', '<mark class="search-highlight">$1</mark>', $safeText);
}

// تعريف المتغيرات الأساسية
 $pageTitle = "نتائج البحث عن $safeQuery | PSPKG-arabic";

// ✨ تعريف الفئات قبل الهيدر ✨
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

// تحميل بيانات الألعاب من جميع الأنظمة (PS3, PS4, PS5)
function loadGamesData() {
    $allGames = [];
    $jsonFiles = [
        'data/ps4-games.json',
        'data/ps5-games.json',
        'data/ps3-games.json'
    ];
    
    foreach ($jsonFiles as $jsonFile) {
        if (file_exists($jsonFile)) {
            $jsonContent = file_get_contents($jsonFile);
            $games = json_decode($jsonContent, true);
            if (is_array($games)) {
                $allGames = array_merge($allGames, $games);
            }
        }
    }
    
    return $allGames;
}

 $games = loadGamesData();

// فرز الألعاب تنازليًا حسب المعرف (id)
usort($games, function($a, $b) {
    $idA = isset($a['id']) ? $a['id'] : 0;
    $idB = isset($b['id']) ? $b['id'] : 0;
    return $idB <=> $idA;
});

// فلترة الألعاب حسب كلمة البحث
 $filteredGames = array_filter($games, function($game) use ($searchQuery) {
    return isset($game['title']) && stripos($game['title'], $searchQuery) !== false;
});
 $filteredGames = array_values($filteredGames);

// تحديد الصفحة الحالية للترقيم
 $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;
 $gamesPerPage = 24;

// حساب الترقيم بناءً على النتائج المفلترة
 $totalGames = count($filteredGames);
 $totalPages = ceil($totalGames / $gamesPerPage);
 $startIndex = ($currentPage - 1) * $gamesPerPage;
 $gamesToShow = array_slice($filteredGames, $startIndex, $gamesPerPage);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" >
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="نتائج البحث عن <?php echo $safeQuery; ?> - تحميل ألعاب بلايستيشن PS4, PS5, PS3 مجاناً بالعربي على PSPKG.">
    <meta name="robots" content="noindex, follow">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/ps4-games.css">
    
    <style>
        /* شريط البحث في الأعلى */
        .search-page-form {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        .search-page-form input {
            flex: 1;
            padding: 14px 20px;
            border: 2px solid var(--border-color, #333);
            border-radius: 12px;
            background: var(--bg-secondary, #1a1a2e);
            color: var(--text-primary, #fff);
            font-size: 1.1rem;
            font-family: 'Cairo', sans-serif;
            outline: none;
            transition: border-color 0.3s;
        }
        .search-page-form input:focus {
            border-color: var(--accent-color, #0070d1);
        }
        .search-page-form button {
            padding: 14px 30px;
            background: var(--accent-color, #0070d1);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: opacity 0.3s;
        }
        .search-page-form button:hover { opacity: 0.85; }

        /* معلومات نتائج البحث */
        .search-info-bar {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color, #333);
        }
        .search-info-bar h2 {
            margin: 0;
            font-size: 1.3rem;
            color: var(--text-primary, #fff);
        }
        .search-info-bar h2 span { color: var(--accent-color, #0070d1); }
        .search-info-bar p { margin: 5px 0 0; color: var(--text-secondary, #aaa); font-size: 0.9rem; }

        /* تمييز كلمة البحث */
        .search-highlight {
            background: var(--accent-color, #0070d1);
            color: #fff;
            padding: 0 4px;
            border-radius: 4px;
        }

        /* حالة عدم وجود نتائج */
        .no-results-container {
            text-align: center;
            padding: 80px 20px;
            grid-column: 1 / -1;
        }
        .no-results-container i { font-size: 5rem; color: #555; margin-bottom: 20px; }
        .no-results-container h3 { font-size: 1.8rem; margin-bottom: 10px; color: var(--text-primary, #fff); }
        .no-results-container p { color: var(--text-secondary, #aaa); margin-bottom: 30px; }
        
        /* شارات المنصات */
        .platform-badge.ps3 { background: #2c3e50; }
        .platform-badge.ps5 { background: #006FCD; }

        /* ✨ إصلاح شارة العربية لصفحة البحث ✨ */
        .game-card-link .game-card {
            position: relative; /* ضروري ليثبت الشارة داخل الكرت */
        }

        .arabic-badge {
            position: absolute;
            top: 5px;
            right: 10px; /* أو left: 10px; حسب رغبتك */
            background: linear-gradient(135deg, #00c853, #00e676);
            color: #151414;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 4px 15px rgba(0, 200, 83, 0.4);
        }

        /* ✨ تعديل ارتفاع القسم لصفحة البحث ✨ */
        .search-games-section .games-grid {
            margin-top: 40px; 
        }

        .game-card.ps3:hover { box-shadow: 0 20px 40px rgba(74, 14, 78, 0.3); border-color: var(--ps3-color); }
        .game-card.ps4:hover { box-shadow: 0 20px 40px rgba(0, 55, 145, 0.3); border-color: var(--ps4-color); }
        .game-card.ps5:hover { box-shadow: 0 20px 40px rgba(0, 112, 243, 0.3); border-color: var(--ps5-color); }
        
        /* ✨ الخط الفاصل بين البحث والألعاب ✨ */
        .search-separator {
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--border-color, #333), var(--ps4-color, #003791), var(--border-color, #333), transparent);
            margin: 10px 0 30px;
            position: relative;
            border-radius: 2px;
        }

        .search-separator::before {
            content: '\f002';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--bg-primary, #0c0c0c);
            padding: 0 16px;
            color: var(--ps4-color, #003791);
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <button class="scroll-to-top" id="scrollToTop">
        <i class="fas fa-arrow-up"></i>
    </button>

    <div class="page-wrapper">
        <?php require_once 'includes/header.php'; ?>

        <main class="container">
            
            <!-- ===== شريط البحث ===== -->
            <section style="margin-top: 30px;">
                <form action="search.php" method="GET" class="search-page-form">
                    <input type="text" name="q" value="<?php echo $safeQuery; ?>" placeholder="ابحث عن لعبة أخرى..." required autofocus>
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </section>

            <!-- ===== معلومات النتائج ===== -->
            <section class="search-info-bar">
                <h2>نتائج البحث عن: "<span><?php echo $safeQuery; ?></span>"</h2>
                <p>تم العثور على <?php echo $totalGames; ?> لعبة</p>
            </section>

            <!-- ✨ الخط الفاصل ✨ -->
            <div class="search-separator"></div>

            <!-- ===== قسم عرض الألعاب ===== -->
            <section class="platform-section search-games-section" id="search-section">
                
                <div class="games-grid" id="search-games">
                    <?php if ($totalGames === 0): ?>
                        <div class="no-results-container">
                            <i class="fas fa-search"></i>
                            <h3>لم نجد ما تبحث عنه!</h3>
                            <p>تأكد من صحة كتابة اسم اللعبة أو جرب كلمات بحث أقل</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($gamesToShow as $game): ?>
                            <?php 
                            $game_id = htmlspecialchars($game['id'] ?? '');
                            $formatted_title = formatTitleForUrl($game['title'] ?? '');
                            $download_link = "download.php?id={$game_id}&title={$formatted_title}";
                            
                            $platformClass = strtolower($game['platform'] ?? 'ps4');
                            $is_arabic = isset($game['genre']) && in_array('arabic', $game['genre']);
                            $hasGif = !empty($game['gif']);
                            ?>
                            
                            <!-- ✨ هيكل الكرت الصحيح: الشارة أصبحت داخل الكرت ✨ -->
                            <a href="<?php echo $download_link; ?>" class="game-card-link <?php echo $platformClass; ?>">
                                <div class="game-card <?php echo $platformClass; ?>" data-id="<?php echo $game['id']; ?>">
                                    <?php if ($is_arabic): ?>
                                        <div class="arabic-badge"><i class="fas fa-language"></i> بالعربية</div>
                                    <?php endif; ?>

                                    <?php if ($hasGif): ?>
                                        <img src="<?php echo $game['gif']; ?>" alt="" class="game-card-gif" loading="lazy">
                                    <?php endif; ?>
                                    
                                    <img src="<?php echo $game['image']; ?>" alt="<?php echo htmlspecialchars($game['title']); ?>" class="game-card-image" loading="lazy">
                                    
                                    <div class="game-card-info">
                                        <h3 class="game-card-title"><?php echo highlightText($game['title'], $searchQuery); ?></h3>
                                        <div class="game-card-meta">
                                            <span class="platform-badge <?php echo $platformClass; ?>"><?php echo strtoupper($game['platform']); ?></span>
                                            <span><?php echo isset($game['genre']) && is_array($game['genre']) ? implode(' • ', array_map('strtoupper', $game['genre'])) : 'غير محدد'; ?></span>
                                        </div>
                                        <div class="game-card-details">
                                            <div class="game-code"><i class="fas fa-barcode"></i> <?php echo isset($game['gameCode']) ? $game['gameCode'] : 'N/A'; ?></div>
                                            <div class="game-languages">
                                                <i class="fas fa-language"></i> 
                                                <?php 
                                                if (isset($game['languages']) && is_array($game['languages'])) {
                                                    $languages = array_map(function($lang) {
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
                        <button class="pagination-btn ps4" onclick="window.location.href='?q=<?php echo urlencode($searchQuery); ?>&page=<?php echo $currentPage - 1; ?>'" <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>>
                            <i class="fas fa-chevron-right"></i>
                        </button>

                        <div id="page-numbers">
                            <?php
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($totalPages, $startPage + 4);
                            if ($endPage - $startPage < 4) $startPage = max(1, $endPage - 4);
                            
                            if ($startPage > 1) {
                                echo '<a href="?q=' . urlencode($searchQuery) . '&page=1" class="pagination-btn">1</a>';
                                if ($startPage > 2) echo '<span class="pagination-dots">...</span>';
                            }
                            
                            for ($i = $startPage; $i <= $endPage; $i++) {
                                $activeClass = ($i === $currentPage) ? 'active' : '';
                                echo '<a href="?q=' . urlencode($searchQuery) . '&page=' . $i . '" class="pagination-btn ' . $activeClass . '">' . $i . '</a>';
                            }
                            
                            if ($endPage < $totalPages) {
                                if ($endPage < $totalPages - 1) echo '<span class="pagination-dots">...</span>';
                                echo '<a href="?q=' . urlencode($searchQuery) . '&page=' . $totalPages . '" class="pagination-btn">' . $totalPages . '</a>';
                            }
                            ?>
                        </div>

                        <button class="pagination-btn ps4" onclick="window.location.href='?q=<?php echo urlencode($searchQuery); ?>&page=<?php echo $currentPage + 1; ?>'" <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>>
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </section>
        </main>

        <?php require_once 'includes/footer.php'; ?>
    </div>

    <script>
        const scrollToTopBtn = document.getElementById('scrollToTop');
        if (scrollToTopBtn) {
            // ✨ نقل الزر لتجاوز مشكلة transform في الـ CSS
            document.documentElement.appendChild(scrollToTopBtn);

            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    scrollToTopBtn.classList.add('visible');
                } else {
                    scrollToTopBtn.classList.remove('visible');
                }
            });
            scrollToTopBtn.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
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