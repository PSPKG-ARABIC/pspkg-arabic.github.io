// نظام إدارة التعليقات والبلاغات مع التصحيح
class CommentsReportsManager {
    constructor() {
        this.debugMode = true;
        this.apiBase = 'api/';
        this.currentGameId = null;
        this.currentGameData = null;
        
        this.init();
    }
    
    init() {
        // الحصول على معرف اللعبة من الصفحة
        this.currentGameId = window.currentGameId || document.querySelector('meta[name="game-id"]')?.content;
        this.currentGameData = window.currentGameData;
        
        this.log('Initializing CommentsReportsManager', {
            gameId: this.currentGameId,
            gameData: this.currentGameData
        });
        
        this.setupEventListeners();
        this.loadApprovedComments();
    }
    
    log(message, data = null) {
        if (this.debugMode) {
            console.log(`[CommentsReportsManager] ${message}`, data);
        }
    }
    
    error(message, error = null) {
        console.error(`[CommentsReportsManager] ${message}`, error);
    }
    
    setupEventListeners() {
        // إرسال تعليق
        const submitCommentBtn = document.getElementById('submitComment');
        if (submitCommentBtn) {
            submitCommentBtn.addEventListener('click', (e) => this.handleCommentSubmit(e));
        }
        
        // إرسال بلاغ
        const reportForm = document.getElementById('reportForm');
        if (reportForm) {
            reportForm.addEventListener('submit', (e) => this.handleReportSubmit(e));
        }
        
        this.log('Event listeners setup completed');
    }
    
    async handleCommentSubmit(e) {
        e.preventDefault();
        
        const name = document.getElementById('commentName')?.value?.trim();
        const comment = document.getElementById('commentText')?.value?.trim();
        const messageElement = document.getElementById('commentMessage');
        
        if (!name || !comment) {
            this.showMessage(messageElement, 'الرجاء ملء جميع الحقول', 'error');
            return;
        }
        
        this.log('Submitting comment', { name, comment });
        
        try {
            const response = await this.apiRequest('comments.php', {
                action: 'add_comment',
                game_id: this.currentGameId,
                name: name,
                comment: comment
            });
            
            if (response.success) {
                this.showMessage(messageElement, response.message, 'success');
                document.getElementById('commentName').value = '';
                document.getElementById('commentText').value = '';
                
                // إضافة التعليق للواجهة
                this.addCommentToList({
                    id: response.data?.id || Date.now(),
                    name: name,
                    comment: comment,
                    date: new Date().toLocaleDateString('ar-SA'),
                    status: 'pending'
                });
            } else {
                this.showMessage(messageElement, response.message, 'error');
            }
        } catch (error) {
            this.error('Error submitting comment', error);
            this.showMessage(messageElement, 'حدث خطأ أثناء إرسال التعليق', 'error');
        }
    }
    
    async handleReportSubmit(e) {
        e.preventDefault();
        
        const reason = document.getElementById('reportReason')?.value;
        const details = document.getElementById('reportDetails')?.value?.trim();
        const messageElement = document.getElementById('reportSuccessMessage');
        
        if (!reason || !details) {
            this.showMessage(messageElement, 'الرجاء ملء جميع الحقول', 'error');
            return;
        }
        
        this.log('Submitting report', { reason, details });
        
        try {
            const response = await this.apiRequest('reports.php', {
                action: 'add_report',
                game_id: this.currentGameId,
                game_title: this.currentGameData?.title || 'Unknown Game',
                reason: reason,
                details: details
            });
            
            if (response.success) {
                this.showMessage(messageElement, response.message, 'success');
                document.getElementById('reportForm').reset();
            } else {
                this.showMessage(messageElement, response.message, 'error');
            }
        } catch (error) {
            this.error('Error submitting report', error);
            this.showMessage(messageElement, 'حدث خطأ أثناء إرسال البلاغ', 'error');
        }
    }
    
    async apiRequest(endpoint, data) {
        this.log(`Making API request to ${endpoint}`, data);
        
        try {
            const response = await fetch(this.apiBase + endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            this.log(`API response from ${endpoint}`, result);
            
            return result;
        } catch (error) {
            this.error(`API request failed for ${endpoint}`, error);
            throw error;
        }
    }
    
    async loadApprovedComments() {
        if (!this.currentGameId) {
            this.log('No game ID provided, skipping comments loading');
            return;
        }
        
        try {
            const response = await fetch(`${this.apiBase}comments.php?action=get_approved_comments&game_id=${this.currentGameId}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success && result.data) {
                this.displayApprovedComments(result.data);
            } else {
                this.log('No approved comments found');
                this.displayNoCommentsMessage();
            }
        } catch (error) {
            this.error('Error loading approved comments', error);
            this.displayNoCommentsMessage();
        }
    }
    
    displayApprovedComments(comments) {
        const commentsList = document.getElementById('commentsList');
        if (!commentsList) return;
        
        commentsList.innerHTML = '';
        
        if (comments.length === 0) {
            this.displayNoCommentsMessage();
            return;
        }
        
        comments.forEach(comment => {
            const commentElement = this.createCommentElement(comment);
            commentsList.appendChild(commentElement);
        });
        
        // تحديث العداد
        const commentsCount = document.getElementById('commentsCount');
        if (commentsCount) {
            commentsCount.textContent = comments.length;
        }
        
        this.log(`Displayed ${comments.length} approved comments`);
    }
    
    displayNoCommentsMessage() {
        const commentsList = document.getElementById('commentsList');
        if (!commentsList) return;
        
        const noComments = document.createElement('div');
        noComments.className = 'no-comments';
        noComments.innerHTML = '<p>لا توجد تعليقات بعد. كن أول من يعلق!</p>';
        commentsList.appendChild(noComments);
    }
    
    createCommentElement(comment) {
        const div = document.createElement('div');
        div.className = 'comment-item';
        div.innerHTML = `
            <div class="comment-avatar">
                <img src="https://picsum.photos/seed/${comment.id}/60/60.jpg" alt="${comment.name}">
            </div>
            <div class="comment-content">
                <div class="comment-header">
                    <h5 class="comment-author">${this.escapeHtml(comment.name)}</h5>
                    <span class="comment-date">${comment.date}</span>
                </div>
                <p class="comment-text">${this.escapeHtml(comment.comment)}</p>
            </div>
        `;
        return div;
    }
    
    addCommentToList(comment) {
        const commentsList = document.getElementById('commentsList');
        if (!commentsList) return;
        
        // إزالة رسالة "لا توجد تعليقات" إذا كانت موجودة
        const noComments = commentsList.querySelector('.no-comments');
        if (noComments) {
            noComments.remove();
        }
        
        const commentElement = this.createCommentElement(comment);
        
        // إضافة شارة "في انتظار الموافقة"
        const statusBadge = document.createElement('div');
        statusBadge.className = 'comment-status';
        statusBadge.innerHTML = '<span class="status-badge pending">في انتظار الموافقة</span>';
        commentElement.querySelector('.comment-content').appendChild(statusBadge);
        
        // إضافة التعليق في بداية القائمة
        commentsList.insertBefore(commentElement, commentsList.firstChild);
        
        // تحديث العداد
        const commentsCount = document.getElementById('commentsCount');
        if (commentsCount) {
            const currentCount = parseInt(commentsCount.textContent) || 0;
            commentsCount.textContent = currentCount + 1;
        }
        
        this.log('Added new comment to list', comment);
    }
    
    showMessage(element, message, type) {
        if (!element) return;
        
        element.textContent = message;
        element.style.display = 'block';
        
        if (type === 'error') {
            element.style.backgroundColor = '#f44336';
        } else {
            element.style.backgroundColor = '#4CAF50';
        }
        
        setTimeout(() => {
            element.style.display = 'none';
        }, 5000);
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// تهيئة المدير عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', () => {
    window.commentsReportsManager = new CommentsReportsManager();
});