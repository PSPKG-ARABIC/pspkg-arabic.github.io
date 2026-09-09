<?php
require_once 'config.php';
require_once 'admin_config.php';

// إذا كان المدير مسجل الدخول بالفعل، حوله للوحة التحكم
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_panel.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // التحقق من تطابق اسم المستخدم وكلمة المرور
    if ($username === $ADMIN_USERNAME && $password === $ADMIN_PASSWORD) {
        // كلمة المرور صحيحة، إنشاء الجلسة
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $username;

        // تحديث معرف الجلسة لمنع ثغرات Session Fixation
        session_regenerate_id(true);

        header('Location: admin_panel.php');
        exit;
    } else {
        $error = 'اسم المستخدم أو كلمة المرور غير صحيحة!';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>تسجيل دخول المدير</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: #1a1a24;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-box {
            background: #25253a;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            width: 350px;
        }

        .login-box h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #007bff;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #444;
            border-radius: 8px;
            background: #1a1a24;
            color: #fff;
            box-sizing: border-box;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-login:hover {
            background: #0056b3;
        }

        .error {
            color: #ff4d4d;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="login-box">
        <h2><i class="fas fa-user-shield"></i> لوحة التحكم</h2>
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label>اسم المستخدم</label>
                <input type="text" name="username" required autocomplete="off" value="admin">
            </div>
            <div class="form-group">
                <label>كلمة المرور</label>
                <input type="password" name="password" required value="admin123">
            </div>
            <button type="submit" class="btn-login">دخول</button>
        </form>
    </div>
</body>

</html>