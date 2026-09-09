<?php
require_once 'config.php';

// مسح جميع بيانات الجلسة
$_SESSION = array();

// حذف ملف تعريف الارتباط الخاص بالجلسة
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// تدمير الجلسة نهائياً
session_destroy();

// العودة للصفحة الرئيسية
header('Location: index.php');
exit;
