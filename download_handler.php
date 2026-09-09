<?php
// هذا الملف يعالج طلبات التحميل ويقدم الملفات بشكل آمن

// تحقق من وجود اسم الملف
if (!isset($_GET['file']) || empty($_GET['file'])) {
    die('خطأ: لم يتم تحديد ملف للتحميل.');
}

$fileName = basename($_GET['file']); // لمنع الوصول إلى مسارات أخرى

// NEW: تحديد مسار مجلد التحميلات بشكل آمن
// **مهم:** قم بتغيير هذا المسار إلى المسار الفعلي لمجلد التحميلات الخاص بك
$downloadsPath = __DIR__ . '/downloads/';

$filePath = $downloadsPath . $fileName;

// تحقق من وجود الملف ومن أنه ملف حقيقي
if (!file_exists($filePath) || !is_file($filePath)) {
    http_response_code(404);
    die('خطأ: الملف المطلوب غير موجود.');
}

// الحصول على نوع الملف (MIME type)
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $filePath);
finfo_close($finfo);

// إعداد الرؤوس Headers لفرض التحميل
header('Content-Description: File Transfer');
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

// قراءة الملف وإرساله إلى المستخدم
readfile($filePath);
exit;
