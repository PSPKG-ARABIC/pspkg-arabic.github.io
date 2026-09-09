<?php
// بدء تسجيل الأخطاء
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug_log.txt'); // سيتم إنشاء ملف debug_log.txt في نفس المجلد

// دالة مساعدة للتسجيل
function log_debug($message) {
    $log_file = __DIR__ . '/debug_log.txt';
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

log_debug("=== SCRIPT STARTED ===");

// التحقق من وجود مجلد التعليقات
 $commentsDir = dirname(__DIR__) . '/data/comments/';
log_debug("Checking for comments directory at: " . $commentsDir);

if (!is_dir($commentsDir)) {
    log_debug("ERROR: Comments directory does not exist. Attempting to create...");
    if (!mkdir($commentsDir, 0777, true)) {
        log_debug("FATAL ERROR: Failed to create comments directory.");
        // إخراج رسالة خطأ واضحة للمتصفح
        header('Content-Type: application/json');
        echo json_encode(['error' => 'DirectoryError', 'message' => 'Could not create or find the /data/comments directory. Please check permissions.']);
        exit;
    } else {
        log_debug("SUCCESS: Comments directory created.");
    }
}

 $pendingComments = [];

log_debug("Scanning for .json files in comments directory...");

 $files = glob($commentsDir . '*.json');
if ($files === false) {
    log_debug("ERROR: glob() failed. This could be a permissions issue.");
} else {
    log_debug("Found " . count($files) . " files.");
}

if ($files) {
    foreach ($files as $file) {
        log_debug("Processing file: " . basename($file));
        $gameId = basename($file, '.json');
        $jsonContent = file_get_contents($file);
        
        if ($jsonContent === false) {
            log_debug("WARNING: Could not read file contents for " . basename($file));
            continue;
        }
        
        $gameComments = json_decode($jsonContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            log_debug("ERROR: JSON decode error in " . basename($file) . ": " . json_last_error_msg());
            continue;
        }
        
        if (is_array($gameComments)) {
            foreach ($gameComments as $comment) {
                if (isset($comment['approved']) && $comment['approved'] === false) {
                    $comment['game_id'] = $gameId;
                    $comment['file_name'] = basename($file);
                    $pendingComments[] = $comment;
                }
            }
        }
    }
}

log_debug("Total pending comments found: " . count($pendingComments));

// إعادة ترتيب التعليقات حسب التاريخ (الأحدث أولاً)
usort($pendingComments, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

log_debug("=== SCRIPT FINISHED, PREPARING OUTPUT ===");

?>
<div class="comments-management-header">
    <h3><i class="fas fa-exclamation-triangle"></i> التعليقات التي تنتظر الموافقة</h3>
    <button class="refresh-btn" onclick="loadCommentsManager()">
        <i class="fas fa-sync-alt"></i> تحديث
    </button>
</div>

<div id="commentsListContainer">
    <?php if (empty($pendingComments)): ?>
        <p class="no-comments">لا توجد تعليقات تنتظر الموافقة حاليًاً.</p>
    <?php else: ?>
        <?php foreach ($pendingComments as $comment): ?>
            <div class="comment-item" id="comment-<?php echo htmlspecialchars($comment['id']); ?>">
                <div class="comment-header">
                    <span class="comment-name"><?php echo htmlspecialchars($comment['name']); ?></span>
                    <span class="comment-meta">
                        على لعبة: <strong><?php echo htmlspecialchars($comment['game_id']); ?></strong> | 
                        في تاريخ: <?php echo date('Y-m-d H:i', strtotime($comment['date'])); ?>
                    </span>
                </div>
                <div class="comment-body">
                    <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                </div>
                <div class="comment-actions">
                    <button class="approve-btn" onclick="approveComment('<?php echo htmlspecialchars($comment['file_name']); ?>', '<?php echo htmlspecialchars($comment['id']); ?>')">
                        <i class="fas fa-check"></i> موافقة
                    </button>
                    <button class="delete-btn" onclick="deleteComment('<?php echo htmlspecialchars($comment['file_name']); ?>', '<?php echo htmlspecialchars($comment['id']); ?>')">
                        <i class="fas fa-trash"></i> حذف
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
    .comments-management-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .refresh-btn { background: #6366f1; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; }
    .refresh-btn:hover { background: #4f46e5; }
    .comment-item { border: 1px solid #e5e7eb; padding: 15px; margin-bottom: 15px; border-radius: 8px; background-color: #f9fafb; }
    .comment-header { font-weight: bold; margin-bottom: 10px; }
    .comment-name { color: #4f46e5; }
    .comment-meta { font-size: 0.9em; color: #6b7280; }
    .comment-body { margin-bottom: 15px; line-height: 1.6; }
    .comment-actions { display: flex; gap: 10px; }
    .approve-btn, .delete-btn { padding: 6px 12px; border: none; border-radius: 5px; cursor: pointer; color: white; font-weight: bold; }
    .approve-btn { background-color: #10b981; }
    .approve-btn:hover { background-color: #059669; }
    .delete-btn { background-color: #ef4444; }
    .delete-btn:hover { background-color: #dc2626; }
    .no-comments { text-align: center; color: #6b7280; padding: 20px; }
</style>