<?php
// تعيين رأس الصفحة لضمان الترميز الصحيح
header('Content-Type: text/html; charset=UTF-8');
header('HTTP/1.0 404 Not Found');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>404 - الصفحة غير موجودة | PSPKG-arabic</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap">
    <style>
        body { font-family: 'Cairo', sans-serif; background: #1a1a1a; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; text-align: center; }
        .error-container { max-width: 600px; }
        h1 { font-size: 8rem; margin: 0; background: linear-gradient(45deg, #ff6b6b, #4ecdc4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        h2 { font-size: 2rem; margin-bottom: 1rem; }
        p { font-size: 1.2rem; margin-bottom: 2rem; }
        a { display: inline-block; padding: 12px 25px; background: #007bff; color: #fff; text-decoration: none; border-radius: 5px; transition: background 0.3s; }
        a:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>404</h1>
        <h2>عذرًا، الصفحة التي تبحث عنها غير موجودة</h2>
        <p>قد يكون الرابط خاطئًا أو تم حذف اللعبة.</p>
        <a href="index.php">العودة إلى الرئيسية</a>
    </div>
</body>
</html>