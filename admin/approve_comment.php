<?php
header('Content-Type: application/json');

// يمكنك إضافة تحقق بسيط هنا لجلسة المسؤول إذا كان ضرورياً
// session_start();
// if (!isset($_SESSION['admin_logged_in'])) {
//     echo json_encode(['success' => false, 'message' => 'غير مصرح بالوصول']);
//     exit;
// }

 $commentFile = $_POST['file'] ?? '';
 $commentId = $_POST['id'] ?? '';

if (empty($commentFile) || empty($commentId)) {
    echo json_encode(['success' => false, 'message' => 'معلمات غير صالحة.']);
    exit;
}

// تنظيف اسم الملف للأمان
 $commentFile = basename($commentFile);
 $filePath = dirname(__DIR__) . '/data/comments/' . $commentFile;

if (!file_exists($filePath)) {
    echo json_encode(['success' => false, 'message' => 'ملف التعليقات غير موجود.']);
    exit;
}

 $comments = json_decode(file_get_contents($filePath), true);
if ($comments === null) {
    echo json_encode(['success' => false, 'message' => 'ملف التعليقات تالف أو فارغ.']);
    exit;
}

 $commentFound = false;
foreach ($comments as &$comment) {
    if ($comment['id'] === $commentId) {
        $comment['approved'] = true;
        $commentFound = true;
        break;
    }
}

if ($commentFound) {
    // حفظ المصفوفة المحدثة مرة أخرى في الملف
    if (file_put_contents($filePath, json_encode($comments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        echo json_encode(['success' => true, 'message' => 'تمت الموافقة على التعليق بنجاح.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'فشل في حفظ التغييرات.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'لم يتم العثور على التعليق.']);
}