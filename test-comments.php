<?php
// تعيين رأس الصفحة لضمان الترميز الصحيح
header('Content-Type: text/html; charset=UTF-8');

// --- منطق معالجة التعليقات ---
// استخدمنا معرف لعبة ثابت (123) للتجربة
 $gameId = 123;
 $comments = [];
 $comment_error = '';
 $comment_success = '';
 $comments_file_path = __DIR__ . '/data/comments/' . $gameId . '.json';

// التأكد من وجود مجلد التعليقات
if (!is_dir(__DIR__ . '/data/comments/')) {
    mkdir(__DIR__ . '/data/comments/', 0755, true);
}

function load_comments($file_path) {
    if (!file_exists($file_path)) return [];
    $json_data = file_get_contents($file_path);
    $comments = json_decode($json_data, true);
    return is_array($comments) ? $comments : [];
}

// معالجة إرسال التعليق
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $name = trim($_POST['comment_name']);
    $comment_text = trim($_POST['comment_text']);
    
    if (empty($name) || empty($comment_text)) {
        $comment_error = 'يرجى ملء جميع الحقول.';
    } else {
        $comments = load_comments($comments_file_path);
        $new_comment = [
            'id' => uniqid(),
            'name' => htmlspecialchars($name),
            'comment' => htmlspecialchars($comment_text),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        array_unshift($comments, $new_comment);
        
        // حفظ التعليقات
        $json_data = json_encode($comments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (file_put_contents($comments_file_path, $json_data) === false) {
            $comment_error = 'حدث خطأ أثناء حفظ التعليق.';
        } else {
            header("Location: test-comments.php?comment_success=1");
            exit();
        }
    }
}

// تحميل التعليقات وعرض رسالة النجاح
 $comments = load_comments($comments_file_path);
if (isset($_GET['comment_success'])) {
    $comment_success = 'تم إضافة تعليقك بنجاح!';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صفحة تجريبية للتعليقات</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- Variables & Base Styles --- */
        :root {
            --bg-primary: #0c0c0c;
            --bg-secondary: #1a1a1a;
            --bg-card: rgba(255, 255, 255, 0.05);
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --accent-color: #8b5cf6;
            --accent-hover: #a78bfa;
            --border-color: rgba(255, 255, 255, 0.1);
            --font-primary: 'Cairo', sans-serif;
        }

        body {
            font-family: var(--font-primary);
            background-color: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            direction: rtl;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        /* --- Comments Section Styles --- */
        .section {
            background: var(--bg-card);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
        }

        .comments-section h3 {
            margin-bottom: 20px;
            color: var(--text-primary);
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }

        .comments-section h3 i {
            margin-left: 10px;
            color: var(--accent-color);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid transparent;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .comment-form-container {
            margin-bottom: 30px;
            padding: 20px;
            background: var(--bg-secondary);
            border-radius: 8px;
        }

        .comment-form-container h4 {
            margin-bottom: 15px;
            color: var(--text-primary);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .comment-form input[type="text"],
        .comment-form textarea {
            width: 100%;
            padding: 12px;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 5px;
            color: var(--text-primary);
            font-family: var(--font-primary);
            font-size: 1rem;
            box-sizing: border-box;
        }

        .comment-form input[type="text"]:focus,
        .comment-form textarea:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 5px rgba(139, 92, 246, 0.5);
        }

        .submit-comment-btn {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 1rem;
            font-family: var(--font-primary);
            cursor: pointer;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .submit-comment-btn:hover {
            background: var(--accent-hover);
        }

        .comments-list .no-comments {
            text-align: center;
            color: var(--text-secondary);
            padding: 20px;
        }

        .comment-item {
            background: var(--bg-secondary);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }

        .comment-author {
            font-size: 1.1rem;
            color: var(--accent-color);
            margin: 0;
        }

        .comment-date {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .comment-text {
            color: var(--text-primary);
            line-height: 1.6;
            margin: 0;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1 style="text-align: center; color: var(--accent-color);">صفحة تجريبية لقسم التعليقات</h1>
        <p style="text-align: center; color: var(--text-secondary);">هذه الصفحة مخصصة لاختبار وظيفة التعليقات فقط.</p>

        <!-- ===== قسم التعليقات ===== -->
        <section class="comments-section section" style="background: red !important; border: 5px solid yellow !important;">
            <h3><i class="fas fa-comments"></i> التعليقات (<?php echo count($comments); ?>)</h3>
            
            <?php if ($comment_success): ?>
                <div class="alert alert-success"><?php echo $comment_success; ?></div>
            <?php endif; ?>
            
            <?php if ($comment_error): ?>
                <div class="alert alert-error"><?php echo $comment_error; ?></div>
            <?php endif; ?>
            
            <div class="comment-form-container">
                <h4>أضف تعليقك</h4>
                <form action="test-comments.php" method="POST" class="comment-form">
                    <div class="form-group">
                        <input type="text" name="comment_name" placeholder="اسمك" required>
                    </div>
                    <div class="form-group">
                        <textarea name="comment_text" rows="4" placeholder="اكتب تعليقك هنا..." required></textarea>
                    </div>
                    <button type="submit" name="submit_comment" class="submit-comment-btn">
                        <i class="fas fa-paper-plane"></i>
                        نشر التعليق
                    </button>
                </form>
            </div>
            
            <div class="comments-list">
                <?php if (empty($comments)): ?>
                    <p class="no-comments">لا توجد تعليقات بعد. كن أول من يعلق!</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item">
                            <div class="comment-header">
                                <h4 class="comment-author"><?php echo $comment['name']; ?></h4>
                                <span class="comment-date"><?php echo $comment['timestamp']; ?></span>
                            </div>
                            <p class="comment-text"><?php echo nl2br($comment['comment']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

</body>
</html>