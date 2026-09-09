<?php
// sitemap.php
require_once 'config.php';

function load_json_data($filename) {
    $filepath = __DIR__ . '/data/' . $filename;
    if (!file_exists($filepath)) {
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

// تعيين ترميز الملف
header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';

// الصفحة الرئيسية
echo '
<url>
    <loc>https://pspkg-arabic.com/</loc>
    <lastmod>' . date('Y-m-d') . '</lastmod>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
</url>';

// صفحات الألعاب
echo '
<url>
    <loc>https://pspkg-arabic.com/ps4-games.php</loc>
    <lastmod>' . date('Y-m-d') . '</lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
</url>
<url>
    <loc>https://pspkg-arabic.com/ps5-games.php</loc>
    <lastmod>' . date('Y-m-d') . '</lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
</url>
<url>
    <loc>https://pspkg-arabic.com/ps3-games.php</loc>
    <lastmod>' . date('Y-m-d') . '</lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
</url>
<url>
    <loc>https://pspkg-arabic.com/arabic-games.php</loc>
    <lastmod>' . date('Y-m-d') . '</lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.9</priority>
</url>';

// صفحات تحميل الألعاب
foreach ($all_games as $game) {
    $game_id = htmlspecialchars($game['id'] ?? '');
    $formatted_title = format_title_for_url($game['title'] ?? '');
    $download_link = "https://pspkg-arabic.com/download.php?id={$game_id}&title={$formatted_title}";
    
    echo '
<url>
    <loc>' . $download_link . '</loc>
    <lastmod>' . date('Y-m-d') . '</lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
</url>';
}

echo '</urlset>';
?>