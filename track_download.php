<?php
// السماح بالاتصال من أي مكان (CORS)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$gameId = isset($input['id']) ? (int)$input['id'] : 0;

if ($gameId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
    exit;
}

$platforms = ['ps3', 'ps4', 'ps5'];
$gameFound = false;

// 1. تحديث عداد ملف TXT (مشترك لجميع المنصات)
$counterFile = __DIR__ . '/data/counters/' . $gameId . '.txt';
if (!is_dir(__DIR__ . '/data/counters')) {
    mkdir(__DIR__ . '/data/counters', 0777, true);
}
$count = file_exists($counterFile) ? (int)file_get_contents($counterFile) : 0;
$count++;
file_put_contents($counterFile, $count);

// 2. البحث في ملفات المنصات وتحديث العداد
foreach ($platforms as $platform) {
    // ✨ تحسين: إذا وجدنا اللعبة، لا داعي لفتح باقي الملفات
    if ($gameFound) break;

    $jsonFile = __DIR__ . "/data/{$platform}-games.json";
    if (!file_exists($jsonFile)) continue;

    $handle = fopen($jsonFile, 'r+');
    if (flock($handle, LOCK_EX)) {
        $jsonContent = stream_get_contents($handle);
        $games = json_decode($jsonContent, true);

        if (is_array($games)) {
            foreach ($games as &$game) {
                if (isset($game['id']) && $game['id'] == $gameId) {

                    if (!isset($game['downloads']) || !is_numeric($game['downloads'])) {
                        $game['downloads'] = 0;
                    }
                    $game['downloads']++;

                    $gameFound = true; // ✨ تم العثور على اللعبة
                    break; // الخروج من حلقة الألعاب
                }
            }
            unset($game);

            // حفظ التغييرات فقط على الملف الذي يحتوي على اللعبة
            if ($gameFound) {
                $newJsonContent = json_encode($games, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                ftruncate($handle, 0);
                rewind($handle);
                fwrite($handle, $newJsonContent);
            }
        }
        flock($handle, LOCK_UN);
    }
    fclose($handle);
}

// إرجاع العدد الجديد
echo json_encode(['status' => 'success', 'new_count' => $count]);
