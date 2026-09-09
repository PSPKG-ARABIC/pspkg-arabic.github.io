<?php
require_once 'config.php';

/**
 * الحصول على بيانات الألعاب
 * @param string $platform منصة الألعاب (ps3, ps4, ps5)
 * @return array مصفوفة بيانات الألعاب
 */
function getGamesData($platform) {
    $file = GAMES_DATA_PATH . $platform . '-games.json';
    
    if (!file_exists($file)) {
        return [];
    }
    
    $json = file_get_contents($file);
    return json_decode($json, true) ?: [];
}

/**
 * الحصول على جميع بيانات الألعاب
 * @return array جميع بيانات الألعاب
 */
function getAllGamesData() {
    $ps3Games = getGamesData('ps3');
    $ps4Games = getGamesData('ps4');
    $ps5Games = getGamesData('ps5');
    
    // إضافة معرف المنصة لكل لعبة
    foreach ($ps3Games as &$game) {
        $game['platform'] = 'ps3';
    }
    
    foreach ($ps4Games as &$game) {
        $game['platform'] = 'ps4';
    }
    
    foreach ($ps5Games as &$game) {
        $game['platform'] = 'ps5';
    }
    
    return array_merge($ps3Games, $ps4Games, $ps5Games);
}

/**
 * تصفية الألعاب حسب التصنيف
 * @param array $games بيانات الألعاب
 * @param string $category معرف التصنيف
 * @return array الألعاب المفلترة
 */
function filterGamesByCategory($games, $category) {
    if ($category === 'all') {
        return $games;
    }
    
    $filteredGames = [];
    foreach ($games as $game) {
        if (isset($game['genre']) && is_array($game['genre']) && in_array($category, $game['genre'])) {
            $filteredGames[] = $game;
        }
    }
    
    return $filteredGames;
}

/**
 * تصفية الألعاب حسب المنصة
 * @param array $games بيانات الألعاب
 * @param string $platform معرف المنصة (all, ps3, ps4, ps5)
 * @return array الألعاب المفلترة
 */
function filterGamesByPlatform($games, $platform) {
    if ($platform === 'all') {
        return $games;
    }
    
    $filteredGames = [];
    foreach ($games as $game) {
        if ($game['platform'] === $platform) {
            $filteredGames[] = $game;
        }
    }
    
    return $filteredGames;
}

/**
 * الحصول على معلومات التصنيف
 * @param string $categoryId معرف التصنيف
 * @return array|null معلومات التصنيف
 */
function getCategoryInfo($categoryId) {
    $categories = [
        'arabic' => ['name' => 'بالعربية', 'icon' => 'fa-language', 'description' => 'استكشف الألعاب المعربة باللغة العربية لجميع منصات بلايستيشن'],
        'action' => ['name' => 'أكشن', 'icon' => 'fa-bolt', 'description' => 'استمتع بأفضل ألعاب الأكشن والمغامرات على منصات PlayStation. من المعارك الملحمية إلى المغامرات المثيرة، اكتشف عالمًا من الإثارة والتشويق.'],
        'hack-and-slash' => ['name' => 'هاكسلاش', 'icon' => 'fa-hammer', 'description' => 'استمتع بألعاب القتال السريع والمعارك الحماسية'],
        'rpg' => ['name' => 'RPG', 'icon' => 'fa-dragon', 'description' => 'انغمس في عواملك الواسعة وأكمل المهام في ألعاب تقمص الأدوار'],
        'shooter' => ['name' => 'إطلاق نار', 'icon' => 'fa-crosshairs', 'description' => 'اختبر أفضل ألعاب إطلاق النار من منظور الشخص الأول والثالث'],
        'fighting' => ['name' => 'قتال', 'icon' => 'fa-fist-raised', 'description' => 'تحدى خصومك في ألعاب القتال المباشر'],
        'adventure' => ['name' => 'مغامرة', 'icon' => 'fa-compass', 'description' => 'انطلق في رحلات ملحمية واكتشف عوالم جديدة'],
        'metroidvania' => ['name' => 'ميترويدفانيا', 'icon' => 'fa-map', 'description' => 'استكشف العوالم المترابطة واكتسب قدرات جديدة'],
        'horror' => ['name' => 'رعب', 'icon' => 'fa-ghost', 'description' => 'واجه مخاوفك في ألعاب الرعب المليئة بالتشويق'],
        'souls' => ['name' => 'سولز', 'icon' => 'fa-skull-crossbones', 'description' => 'اختبر التحديات القاسية في ألعاب سولز'],
        'stealth' => ['name' => 'تسلل', 'icon' => 'fa-user-ninja', 'description' => 'تسلل خلف الأعداء واكمل مهامك دون أن يتم اكتشافك'],
        'survival' => ['name' => 'بقاء', 'icon' => 'fa-campground', 'description' => 'كافح من أجل البقاء في بيئات قاسية'],
        'racing' => ['name' => 'سباق', 'icon' => 'fa-flag-checkered', 'description' => 'تنافس في سباقات السيارات السريعة والمثيرة'],
        'sports' => ['name' => 'رياضة', 'icon' => 'fa-football-ball', 'description' => 'شارك في الرياضات المختلفة وحقق البطولات'],
        'simulation' => ['name' => 'محاكاة', 'icon' => 'fa-plane', 'description' => 'اختبر واقع المحاكاة في مختلف المجالات'],
        'puzzle' => ['name' => 'ألغاز', 'icon' => 'fa-puzzle-piece', 'description' => 'حل الألغاز المعقدة واخترق التحديات العقلية'],
        'open-world' => ['name' => 'عالم مفتوح', 'icon' => 'fa-globe', 'description' => 'استمتع بحرية الاستكشاف في العوالم المفتوحة الواسعة'],
    ];
    
    return isset($categories[$categoryId]) ? $categories[$categoryId] : null;
}

/**
 * تنسيق العنوان ليكون مناسبًا للرابط
 * @param string $title عنوان اللعبة
 * @return string الرابط المنسق
 */
function formatTitleForUrl($title) {
    $title = strtolower($title);
    $title = preg_replace('/\s+/', '_', $title);
    $title = preg_replace('/[^a-z0-9_\-]/', '', $title);
    $title = preg_replace('/_+/', '_', $title);
    return trim($title, '_');
}

/**
 * الحصول على معلمات GET بأمان
 * @param string $param اسم المعامل
 * @param string $default القيمة الافتراضية
 * @return string قيمة المعامل
 */
function getGetParam($param, $default = '') {
    return isset($_GET[$param]) ? htmlspecialchars($_GET[$param], ENT_QUOTES, 'UTF-8') : $default;
}

/**
 * عرض بطاقة اللعبة
 * @param array $game بيانات اللعبة
 * @return string نص HTML
 */
function renderGameCard($game) {
    $languagesText = 'غير محدد';
    if (isset($game['languages']) && is_array($game['languages']) && !empty($game['languages'])) {
        $languages = [];
        foreach ($game['languages'] as $lang) {
            if (is_string($lang)) {
                $languages[] = $lang;
            } elseif (isset($lang['name'])) {
                $languages[] = $lang['name'];
            }
        }
        $languagesText = implode(' • ', $languages);
    }
    
    $genresText = 'غير محدد';
    if (isset($game['genre']) && is_array($game['genre']) && !empty($game['genre'])) {
        $genresText = implode(' • ', array_map('strtoupper', $game['genre']));
    }
    
    $gameCode = isset($game['gameCode']) ? $game['gameCode'] : 'N/A';
    $formattedTitle = formatTitleForUrl($game['title']);
    $downloadUrl = "download.php?id={$game['id']}&title={$formattedTitle}";
    
    $platform = isset($game['platform']) ? $game['platform'] : 'ps4';
    $platformUpper = strtoupper($platform);
    
    return "
    <div class=\"game-card {$platform}\">
        <img src=\"{$game['image']}\" alt=\"{$game['title']}\" class=\"game-card-image\" loading=\"lazy\">
        <div class=\"game-card-info\">
            <h3 class=\"game-card-title\">{$game['title']}</h3>
            <div class=\"game-card-meta\">
                <span class=\"platform-badge {$platform}\">{$platformUpper}</span>
                <span>{$genresText}</span>
            </div>
            <div class=\"game-card-details\">
                <div class=\"game-code\"><i class=\"fas fa-barcode\"></i> {$gameCode}</div>
                <div class=\"game-languages\"><i class=\"fas fa-language\"></i> {$languagesText}</div>
            </div>
        </div>
    </div>
    ";
}

/**
 * الحصول على الرابط الكامل للصفحة الحالية
 * @return string رابط الصفحة الحالية
 */
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    return "{$protocol}://{$host}{$uri}";
}
?>