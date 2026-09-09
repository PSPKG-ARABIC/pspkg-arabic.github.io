<?php
// تعيين عرض الأخطاء
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>اختبار نظام التعليقات</h1>";

// الخطوة 1: التحقق من المسار الأساسي
echo "<h2>الخطوة 1: التحقق من المسارات</h2>";
echo "<p><strong>مسار مجلد العمل الحالي (__DIR__):</strong> " . __DIR__ . "</p>";
echo "<p><strong>المجلد الرئيسي للموقع (dirname(__DIR__)):</strong> " . dirname(__DIR__) . "</p>";

// الخطوة 2: بناء المسار إلى مجلد التعليقات
 $commentsDir = dirname(__DIR__) . '/data/comments/';
echo "<p><strong>المسار الكامل لمجلد التعليقات:</strong> " . $commentsDir . "</p>";

// الخطوة 3: التحقق من وجود المجلد
echo "<h2>الخطوة 2: التحقق من وجود المجلد</h2>";
if (is_dir($commentsDir)) {
    echo "<p style='color: green;'>✅ نجاح: مجلد التعليقات موجود.</p>";
    
    // الخطوة 4: التحقق من الصلاحيات
    echo "<h2>الخطوة 3: التحقق من صلاحيات المجلد</h2>";
    if (is_readable($commentsDir)) {
        echo "<p style='color: green;'>✅ نجاح: المجلد قابل للقراءة.</p>";
        
        // الخطوة 5: البحث عن ملفات JSON
        echo "<h2>الخطوة 4: البحث عن ملفات التعليقات (.json)</h2>";
        $files = glob($commentsDir . '*.json');
        
        if ($files !== false && count($files) > 0) {
            echo "<p style='color: green;'>✅ نجاح: تم العثور على " . count($files) . " ملف تعليقات.</p>";
            echo "<ul>";
            foreach ($files as $file) {
                echo "<li style='color: blue;'>" . htmlspecialchars(basename($file)) . "</li>";
            }
            echo "</ul>";

            // الخطوة 6: محاولة قراءة أول ملف
            echo "<h2>الخطوة 5: قراءة أول ملف تعليق</h2>";
            $firstFile = $files[0];
            echo "<p>محاولة قراءة الملف: " . htmlspecialchars($firstFile) . "</p>";
            
            $jsonContent = file_get_contents($firstFile);
            if ($jsonContent !== false) {
                echo "<p style='color: green;'>✅ نجاح: تم قراءة محتوى الملف.</p>";
                
                $comments = json_decode($jsonContent, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    echo "<p style='color: green;'>✅ نجاح: JSON صالح.</p>";
                    echo "<pre>" . print_r($comments, true) . "</pre>";
                } else {
                    echo "<p style='color: red;'>❌ خطأ: JSON تالف. الخطأ: " . json_last_error_msg() . "</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ خطأ: فشل قراءة محتوى الملف.</p>";
            }
            
        } else {
            echo "<p style='color: orange;'>⚠️ تحذير: دالة glob() فشلت.</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ خطأ: المجلد غير قابل للقراءة. تحقق من صلاحيات الخادم.</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ خطأ فادح: مجلد التعليقات (<code>" . htmlspecialchars($commentsDir) . "</code>) غير موجود.</p>";
    echo "<p><strong>الحل المقترح:</strong></p>";
    echo "<ol>";
    echo "<li>تأكد من أن لديك مجلد اسمه <code>data</code> في المجلد الرئيسي للموقع.</li>";
    echo "<li>تأكد من أن لديك مجلد فرعي اسمه <code>comments</code> داخل مجلد <code>data</code>.</li>";
    echo "<li>تأكد من أن الخادم لديه صلاحية للقراءة والكتابة على هذين المجلدين.</li>";
    echo "</ol>";
}
?>