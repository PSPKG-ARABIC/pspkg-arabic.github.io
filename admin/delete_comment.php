<?php
header('Content-Type: application/json');

// يمكنك إضافة تحقق من الجلسة هنا أيضًا
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

 $foundAndDeleted = false;
// تصفية التعليقات وإزالة التعليق المطلوب
 $updatedComments = array_filter($comments, function($comment) use ($commentId, &$foundAndDeleted) {
    if ($comment['id'] === $commentId) {
        $foundAndDeleted = true;
        return false; // إزالة هذا التعليق من المصفوفة
    }
    return true;
});

if ($foundAndDeleted) {
    // حفظ المصفوفة المحدثة مرة أخرى في الملف
    if (file_put_contents($filePath, json_encode(array_values($updatedComments), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        echo json_encode(['success' => true, 'message' => 'تم حذف التعليق بنجاح.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'فشل في حفظ التغييرات.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'لم يتم العثور على التعليق.']);
}