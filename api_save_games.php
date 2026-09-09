<?php
session_start();
require_once 'config.php';

// التأكد من أن المدير مسجل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'غير مصرح لك. يجب تسجيل الدخول.']);
    exit;
}

// التأكد من أن الطلب من نوع POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'طريقة طلب غير صحيحة.']);
    exit;
}

// قراءة البيانات المرسلة من لوحة التحكم
$json_data = file_get_contents('php://input');
$games = json_decode($json_data, true);

if (empty($games) || !is_array($games)) {
    http_response_code(400);
    echo json_encode(['error' => 'لم يتم استلام بيانات صحيحة.']);
    exit;
}

// تقسيم الألعاب حسب المنصة
$ps4_games = array_filter($games, fn($g) => isset($g['platform']) && $g['platform'] === 'ps4');
$ps5_games = array_filter($games, fn($g) => isset($g['platform']) && $g['platform'] === 'ps5');
$ps3_games = array_filter($games, fn($g) => isset($g['platform']) && $g['platform'] === 'ps3');

// إعادة ترتيب الفهارس (Indexes) لتكون متتالية
$ps4_games = array_values($ps4_games);
$ps5_games = array_values($ps5_games);
$ps3_games = array_values($ps3_games);

// مسار مجلد البيانات
$data_path = __DIR__ . '/data/';

// حفظ الملفات (تأكد أن المجلد لديه صلاحيات الكتابة 755 أو 775)
$saved_files = [];
if (file_put_contents($data_path . 'ps4-games.json', json_encode($ps4_games, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    $saved_files[] = 'ps4-games.json';
}
if (file_put_contents($data_path . 'ps5-games.json', json_encode($ps5_games, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    $saved_files[] = 'ps5-games.json';
}
if (file_put_contents($data_path . 'ps3-games.json', json_encode($ps3_games, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    $saved_files[] = 'ps3-games.json';
}

if (count($saved_files) > 0) {
    echo json_encode(['success' => true, 'message' => 'تم حفظ الملفات بنجاح: ' . implode(', ', $saved_files)]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'فشل في كتابة الملفات. تأكد من صلاحيات المجلد data/']);
}
