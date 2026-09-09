<?php
// تعيين رأس الصفحة لضمان الترميز الصحيح للاستجابة JSON
header('Content-Type: application/json; charset=utf-8');

// --- الإعدادات ---
// ضع بريدك الإلكتروني هنا لتلقي تقارير المشاكل
define('ADMIN_EMAIL', 'mustafahanyf0@gmail.com');

// --- التحقق من طريقة الطلب ---
// يجب أن يكون الطلب باستخدام POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير صالحة.']);
    exit();
}

// --- الحصول على البيانات القادمة من JavaScript ---
// البيانات تأتي بصيغة JSON في جسم الطلب (request body)
 $jsonData = file_get_contents('php://input');
 $data = json_decode($jsonData, true);

// التحقق من صحة البيانات المستلمة
if (!$data || !isset($data['gameId']) || !isset($data['reason']) || !isset($data['details'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'بيانات غير مكتملة أو غير صالحة.']);
    exit();
}

// --- تنظيف والتحقق من البيانات ---
 $gameId = filter_var($data['gameId'], FILTER_VALIDATE_INT);
 $reason = $data['reason'];
 $details = trim($data['details']);

// قائمة بالأسباب المسموح بها (لأغراض أمنية)
 $allowedReasons = ['broken_link', 'corrupted_file', 'wrong_info', 'other'];

if ($gameId === false || !in_array($reason, $allowedReasons) || empty($details)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'بيانات التبليغ غير صالحة.']);
    exit();
}

// --- تحويل السبب إلى نص واضح بالعربية ---
 $reasonText = 'غير محدد'; // قيمة افتراضية
switch ($reason) {
    case 'broken_link':
        $reasonText = 'رابط لا يعمل';
        break;
    case 'corrupted_file':
        $reasonText = 'ملف تالف';
        break;
    case 'wrong_info':
        $reasonText = 'معلومات خاطئة';
        break;
    case 'other':
        $reasonText = 'أخرى';
        break;
}

// --- تجهيز رسالة البريد الإلكتروني ---
 $to = ADMIN_EMAIL;
 $subject = "تبليغ جديد عن مشكلة في لعبة (ID: {$gameId})";

// إنشاء جسم الرسالة بصيغة HTML
 $message = "
<html>
<head>
    <title>تبليغ جديد عن مشكلة</title>
    <style>
        body { font-family: Arial, sans-serif; direction: rtl; text-align: right; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>تم استلام تبليغ جديد من أحد الزوار</h2>
    <p>تفاصيل التبليغ:</p>
    <table>
        <tr>
            <th>معرف اللعبة</th>
            <td>{$gameId}</td>
        </tr>
        <tr>
            <th>سبب التبليغ</th>
            <td>{$reasonText}</td>
        </tr>
        <tr>
            <th>تفاصيل المشكلة</th>
            <td>" . nl2br(htmlspecialchars($details)) . "</td>
        </tr>
        <tr>
            <th>عنوان IP المُبلّغ</th>
            <td>{$_SERVER['REMOTE_ADDR']}</td>
        </tr>
        <tr>
            <th>وقت التبليغ</th>
            <td>" . date('Y-m-d H:i:s') . "</td>
        </tr>
    </table>
</body>
</html>
";

// رؤوس البريد الإلكتروني Headers
 $headers = "MIME-Version: 1.0" . "\r\n";
 $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
 $headers .= 'From: noreply@pspkg-arabic.com' . "\r\n"; // غيره إذا لزم الأمر
 $headers .= 'Reply-To: noreply@pspkg-arabic.com' . "\r\n";
 $headers .= 'X-Mailer: PHP/' . phpversion();

// --- إرسال البريد الإلكتروني ---
if (mail($to, $subject, $message, $headers)) {
    // إذا تم الإرسال بنجاح
    echo json_encode(['success' => true, 'message' => 'تم إرسال التبليغ بنجاح.']);
} else {
    // في حالة فشل الإرسال
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء إرسال البريد الإلكتروني. يرجى المحاولة لاحقًا.']);
}

?>