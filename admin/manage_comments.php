<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة التعليقات</title>
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #333; }
        .comment { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; }
        .comment-header { font-weight: bold; margin-bottom: 10px; }
        .comment-meta { font-size: 0.9em; color: #777; margin-bottom: 10px; }
        .comment-body { margin-bottom: 15px; }
        .approve-btn { background-color: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .approve-btn:hover { background-color: #218838; }
        .no-comments { text-align: center; color: #777; }
        .success-message { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h1>التعليقات التي تنتظر الموافقة</h1>

        <?php
        if (isset($_GET['status']) && $_GET['status'] === 'approved') {
            echo '<div class="success-message">تمت الموافقة على التعليق بنجاح!</div>';
        }

        $commentsDir = __DIR__ . '/data/comments/';
        $pendingComments = [];

        if (is_dir($commentsDir)) {
            $files = glob($commentsDir . '*.json');
            foreach ($files as $file) {
                $gameId = basename($file, '.json');
                $gameComments = json_decode(file_get_contents($file), true) ?: [];
                
                foreach ($gameComments as $comment) {
                    if (isset($comment['approved']) && $comment['approved'] === false) {
                        $comment['game_id'] = $gameId;
                        $comment['file_name'] = basename($file);
                        $pendingComments[] = $comment;
                    }
                }
            }
        }
        ?>

        <?php if (empty($pendingComments)): ?>
            <p class="no-comments">لا توجد تعليقات تنتظر الموافقة حاليًا.</p>
        <?php else: ?>
            <?php foreach ($pendingComments as $comment): ?>
                <div class="comment">
                    <div class="comment-header">
                        <?php echo htmlspecialchars($comment['name']); ?>
                    </div>
                    <div class="comment-meta">
                        على لعبة ذات المعرف: <?php echo htmlspecialchars($comment['game_id']); ?> | في تاريخ: <?php echo date('Y-m-d H:i', strtotime($comment['date'])); ?>
                    </div>
                    <div class="comment-body">
                        <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                    </div>
                    <a href="approve_comment.php?file=<?php echo urlencode($comment['file_name']); ?>&id=<?php echo urlencode($comment['id']); ?>" class="approve-btn">
                        <i class="fas fa-check"></i> موافقة
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>