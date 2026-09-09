// Global variables and functions
let allGamesData = [];
let currentGame = null;

// ===== DATA LOADING FUNCTIONS =====
// دالة لتحميل بيانات ألعاب PS4
async function loadPS4Games() {
    try {
        const response = await fetch('data/ps4-games.json');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const ps4Games = await response.json();
        console.log('PS4 games loaded:', ps4Games.length, 'games');
        return ps4Games;
    } catch (error) {
        console.error('Error loading PS4 games:', error);
        return [];
    }
}

// دالة لتحميل بيانات ألعاب PS5
async function loadPS5Games() {
    try {
        const response = await fetch('data/ps5-games.json');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const ps5Games = await response.json();
        console.log('PS5 games loaded:', ps5Games.length, 'games');
        return ps5Games;
    } catch (error) {
        console.error('Error loading PS5 games:', error);
        return [];
    }
}

// دالة لتحميل بيانات ألعاب PS3
async function loadPS3Games() {
    try {
        const response = await fetch('data/ps3-games.json');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const ps3Games = await response.json();
        console.log('PS3 games loaded:', ps3Games.length, 'games');
        return ps3Games;
    } catch (error) {
        console.error('Error loading PS3 games:', error);
        return [];
    }
}

// دالة لتحميل جميع بيانات الألعاب
async function loadAllGamesData() {
    try {
        // عرض مؤشر التحميل
        showLoadingIndicator();
        
        // تحميل جميع البيانات بالتوازي
        const [ps4Games, ps5Games, ps3Games] = await Promise.all([
            loadPS4Games(),
            loadPS5Games(),
            loadPS3Games()
        ]);
        
        // دمج جميع الألعاب في مصفوفة واحدة
        const allGames = [...ps4Games, ...ps5Games, ...ps3Games];
        
        // ترتيب الألعاب حسب المعرف
        allGames.sort((a, b) => a.id - b.id);
        
        // تطبيع بيانات الألعاب لضمان أن كل لعبة لديها مصفوفة تصنيفات
        const normalizedGames = normalizeGamesData(allGames);
        
        console.log('All games loaded successfully:', normalizedGames.length, 'total games');
        
        // إخفاء مؤشر التحميل
        hideLoadingIndicator();
        
        return normalizedGames;
    } catch (error) {
        console.error('Error loading games data:', error);
        hideLoadingIndicator();
        showErrorMessage('فشل في تحميل بيانات الألعاب. يرجى المحاولة مرة أخرى.');
        return [];
    }
}

// دالة لتطبيع بيانات الألعاب
function normalizeGamesData(games) {
    return games.map(game => {
        // التأكد من أن التصنيفات هي مصفوفة
        if (typeof game.genre === 'string') {
            game.genre = [game.genre];
        } else if (!Array.isArray(game.genre)) {
            game.genre = [];
        }
        
        // التأكد من أن اللغات هي مصفوفة من الكائنات
        if (typeof game.languages === 'string') {
            game.languages = [{ name: game.languages, code: game.languages.toLowerCase(), support: { menu: true, subtitles: true, dubbed: true } }];
        } else if (Array.isArray(game.languages)) {
            game.languages = game.languages.map(lang => {
                if (typeof lang === 'string') {
                    return { name: lang, code: lang.toLowerCase(), support: { menu: true, subtitles: true, dubbed: true } };
                } else if (typeof lang === 'object' && lang !== null) {
                    if (!lang.support) {
                        lang.support = { menu: true, subtitles: true, dubbed: true };
                    }
                    return lang;
                }
                return { name: 'غير محدد', code: 'unknown', support: {} };
            });
        } else {
            game.languages = [];
        }
        
        return game;
    });
}

// دالة لعرض مؤشر التحميل
function showLoadingIndicator() {
    const loadingHTML = `
        <div id="loadingIndicator" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); display: flex; align-items: center; justify-content: center; z-index: 9999;">
            <div style="text-align: center; color: white;">
                <div style="width: 50px; height: 50px; border: 3px solid #f3f3f3; border-top: 3px solid var(--accent-color); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div>
                <p style="font-size: 1.2rem; font-family: var(--font-primary);">جاري تحميل بيانات الألعاب...</p>
            </div>
        </div>
        <style>
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
    `;
    document.body.insertAdjacentHTML('beforeend', loadingHTML);
}

// دالة لإخفاء مؤشر التحميل
function hideLoadingIndicator() {
    const loadingIndicator = document.getElementById('loadingIndicator');
    if (loadingIndicator) {
        loadingIndicator.remove();
    }
}

// دالة لعرض رسالة خطأ
function showErrorMessage(message) {
    const errorHTML = `
        <div id="errorMessage" style="position: fixed; top: 20px; right: 20px; background: #e74c3c; color: white; padding: 15px 20px; border-radius: 10px; z-index: 9999; max-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-exclamation-triangle" style="font-size: 1.2rem;"></i>
                <p style="margin: 0; font-family: var(--font-primary);">${message}</p>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', errorHTML);
    
    // إخفاء الرسالة تلقائياً بعد 5 ثواني
    setTimeout(() => {
        const errorMessage = document.getElementById('errorMessage');
        if (errorMessage) {
            errorMessage.remove();
        }
    }, 5000);
}

// ===== GLOBAL FUNCTIONS =====
// Make these functions globally accessible
window.downloadPart = function(url, type, partNumber) {
    console.log(`Downloading part ${partNumber} of type: ${type}`);
    // Create a temporary link to trigger download
    const link = document.createElement('a');
    link.href = url;
    link.download = `part${partNumber}.pkg`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Track the download
    const gameId = parseInt(new URLSearchParams(window.location.search).get('id'));
    if (window.trackDownload) {
        window.trackDownload(gameId, `${type}_part_${partNumber}`);
    }
};

window.handleDownloadClick = function(event, element) {
    event.preventDefault();
    element.classList.add('loading');
    const type = element.getAttribute('data-type');
    const gameId = parseInt(new URLSearchParams(window.location.search).get('id'));
    
    if (window.trackDownload) {
        window.trackDownload(gameId, type);
    }
    
    setTimeout(() => {
        window.location.href = element.href;
    }, 1500);
};

// ===== PASSWORD MODAL FUNCTIONS =====
window.openPasswordModal = function() {
    const modal = document.getElementById('passwordModal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Update modal password display with current game's password
    if (currentGame && currentGame.password) {
        document.getElementById('modalPasswordText').textContent = currentGame.password;
    }
    
    // Add entrance animation
    setTimeout(() => {
        const modalContent = modal.querySelector('.password-modal');
        modalContent.style.transform = 'scale(1) translateY(0)';
    }, 10);
};

window.closePasswordModal = function() {
    const modal = document.getElementById('passwordModal');
    const modalContent = modal.querySelector('.password-modal');
    
    // Add exit animation
    modalContent.style.transform = 'scale(0.8) translateY(50px)';
    
    setTimeout(() => {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }, 300);
};

window.copyModalPassword = function() {
    // Get current game's password
    const password = currentGame ? currentGame.password : '4GAMER';
    const copyBtn = document.getElementById('modalCopyBtn');
    const originalHTML = copyBtn.innerHTML;
    
    // Copy password to clipboard
    navigator.clipboard.writeText(password).then(() => {
        // Change button to success state
        copyBtn.classList.add('copied');
        copyBtn.innerHTML = '<i class="fas fa-check"></i><span>تم النسخ بنجاح!</span>';
        
        // Create confetti effect
        createModalConfetti();
        
        // Reset button after 2 seconds
        setTimeout(() => {
            copyBtn.classList.remove('copied');
            copyBtn.innerHTML = originalHTML;
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy password: ', err);
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = password;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        copyBtn.classList.add('copied');
        copyBtn.innerHTML = '<i class="fas fa-check"></i><span>تم النسخ بنجاح!</span>';
        setTimeout(() => {
            copyBtn.classList.remove('copied');
            copyBtn.innerHTML = originalHTML;
        }, 2000);
    });
};

window.createModalConfetti = function() {
    const colors = ['#ffc107', '#ff9800', '#ff5722', '#4caf50', '#2196f3', '#9c27b0', '#8b5cf6'];
    const confettiCount = 30;
    const modal = document.querySelector('.password-modal');
    
    for (let i = 0; i < confettiCount; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.left = Math.random() * 100 + '%';
        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.animationDelay = Math.random() * 0.5 + 's';
        confetti.style.animationDuration = (Math.random() * 2 + 1) + 's';
        confetti.style.width = (Math.random() * 8 + 5) + 'px';
        confetti.style.height = (Math.random() * 8 + 5) + 'px';
        modal.appendChild(confetti);
        
        // Remove confetti after animation
        setTimeout(() => {
            confetti.remove();
        }, 3000);
    }
};

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('passwordModal');
    if (event.target === modal) {
        closePasswordModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('passwordModal');
        if (modal.classList.contains('active')) {
            closePasswordModal();
        }
    }
});

window.copyPassword = function(buttonId) {
    const password = currentGame ? currentGame.password : '4GAMER-2024';
    const button = document.getElementById(buttonId);
    const originalHTML = button.innerHTML;
    
    // Copy password to clipboard
    navigator.clipboard.writeText(password).then(() => {
        // Change button to success state
        button.classList.add('copied');
        button.innerHTML = '<i class="fas fa-check"></i><span>تم النسخ!</span>';
        
        // Create confetti effect
        if (window.createConfetti) {
            window.createConfetti(button);
        }
        
        // Reset button after 2 seconds
        setTimeout(() => {
            button.classList.remove('copied');
            button.innerHTML = originalHTML;
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy password: ', err);
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = password;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        button.classList.add('copied');
        button.innerHTML = '<i class="fas fa-check"></i><span>تم النسخ!</span>';
        setTimeout(() => {
            button.classList.remove('copied');
            button.innerHTML = originalHTML;
        }, 2000);
    });
};

window.createConfetti = function(element) {
    const colors = ['#ffc107', '#ff9800', '#ff5722', '#4caf50', '#2196f3', '#9c27b0'];
    const confettiCount = 15;
    
    for (let i = 0; i < confettiCount; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.left = Math.random() * 100 + '%';
        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.animationDelay = Math.random() * 0.5 + 's';
        confetti.style.animationDuration = (Math.random() * 1 + 1) + 's';
        element.parentElement.appendChild(confetti);
        
        // Remove confetti after animation
        setTimeout(() => {
            confetti.remove();
        }, 2000);
    }
};

// ===== IMAGE RESIZE FUNCTION =====
window.resizeImage = function(img, targetWidth, targetHeight) {
    return new Promise((resolve) => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        canvas.width = targetWidth;
        canvas.height = targetHeight;
        
        const image = new Image();
        image.crossOrigin = 'anonymous';
        
        image.onload = function() {
            // Calculate scaling to maintain aspect ratio
            const scale = Math.min(targetWidth / image.width, targetHeight / image.height);
            const scaledWidth = Math.floor(image.width * scale);
            const scaledHeight = Math.floor(image.height * scale);
            
            // Center the image in the canvas
            const offsetX = (targetWidth - scaledWidth) / 2;
            const offsetY = (targetHeight - scaledHeight) / 2;
            
            ctx.fillStyle = 'transparent';
            ctx.fillRect(0, 0, targetWidth, targetHeight);
            
            ctx.drawImage(image, offsetX, offsetY, scaledWidth, scaledHeight);
            
            // Convert to blob and create object URL
            canvas.toBlob((blob) => {
                const url = URL.createObjectURL(blob);
                resolve(url);
            }, 'image/jpeg', 0.9);
        };
        
        image.onerror = () => {
            console.error('Failed to load image:', img.src);
            resolve(img.src); // Fallback to original image
        };
        
        image.src = img;
    });
};

document.addEventListener('DOMContentLoaded', async () => {
    // --- HELPER FUNCTIONS ---
    function getUrlParameter(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }

    function displayError(message) {
        const mainContent = document.querySelector('.main-content') || document.body;
        const errorHTML = `
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <p>${message}</p>
                <a href="index.html" class="back-btn" style="margin-top: 20px; display: inline-block;"><i class="fas fa-arrow-left"></i> العودة إلى الرئيسية</a>
            </div>
        `;
        mainContent.innerHTML = errorHTML;
    }

    function preloadImage(url) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.src = url;
            img.onload = () => resolve(img);
            img.onerror = reject;
        });
    }

    window.trackDownload = function(gameId, downloadType) {
        console.log(`Tracking download: Game ID ${gameId}, Type: ${downloadType}`);
    }

    function createRatingStars(rating) {
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 !== 0;
        const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
        
        let starsHTML = '';
        for (let i = 0; i < fullStars; i++) starsHTML += '<i class="fas fa-star"></i>';
        if (hasHalfStar) starsHTML += '<i class="fas fa-star-half-alt"></i>';
        for (let i = 0; i < emptyStars; i++) starsHTML += '<i class="far fa-star"></i>';
        
        return starsHTML;
    }

    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);

        const themeToggle = document.querySelector('.theme-toggle');
        themeToggle.innerHTML = newTheme === 'light' ? 
            '<i class="fas fa-moon"></i>' : 
            '<i class="fas fa-sun"></i>';
    }

    function formatTitleForUrl(title) {
        // Convert to lowercase and replace spaces with underscores
        // Also remove special characters except underscores and hyphens
        return title.toLowerCase()
            .replace(/\s+/g, '_')
            .replace(/[^a-z0-9_\-]/g, '')
            .replace(/_+/g, '_') // Replace multiple underscores with single one
            .replace(/^_|_$/g, ''); // Remove leading/trailing underscores
    }// ===== دالة عرض دعم اللغات (بدون أيقونات) =====
function displayLanguageSupport(languages) {
    if (!languages || languages.length === 0) return '<span>غير محدد</span>';
    
    let languagesHTML = '<div class="languages-container">';
    
    languages.forEach(lang => {
        const supportText = []; // تغيير اسم المتغير ليعكس محتواه (نصوص بدلاً من أيقونات)
        
        // التحقق من وجود كائن الدعم وأنه ليس فارغًا
        if (lang.support && typeof lang.support === 'object' && Object.keys(lang.support).length > 0) {
            if (lang.support.menu) supportText.push('قوائم '); // إضافة النص مباشرة
            if (lang.support.subtitles) supportText.push('ترجمة'); // إضافة النص مباشرة
            if (lang.support.dubbed) supportText.push('دبلجة'); // إضافة النص مباشرة
        } else {
            // إذا كان كائن الدعم فارغًا، أضف كل النصوص
            supportText.push('قوائم ');
            supportText.push('ترجمة');
            supportText.push('دبلجة');
        }
        
        if (supportText.length > 0) {
            languagesHTML += `
                <div class="language-item">
                    <span class="language-name">${lang.name}:</span>
                    <div class="language-support">
                        ${supportText.join(' ، ')} <!-- دمج النصوص مع فاصل -->
                    </div>
                </div>
            `;
        }
    });
    
    languagesHTML += '</div>';
    return languagesHTML;
}

// ===== دالة تعبئة الصفحة (محدثة) =====
function populatePage(game) {
    document.getElementById('gameTitle').textContent = game.title;
    document.getElementById('gamePlatform').textContent = game.platform.toUpperCase();
    
    // عرض جميع التصنيفات بدلاً من تصنيف واحد
    const genresText = game.genre && game.genre.length > 0 
        ? game.genre.map(g => g.toUpperCase()).join(' • ') 
        : 'غير محدد';
    document.getElementById('gameGenre').textContent = genresText;
    
    document.getElementById('gameVersion').textContent = game.version;
    document.getElementById('displayGameCode').textContent = game.gameCode;
    document.getElementById('gameStory').textContent = game.story;
    document.getElementById('gameCode').textContent = game.gameCode;
    document.getElementById('gameDeveloper').textContent = game.developer;
    document.getElementById('gamePublisher').textContent = game.publisher;
    document.getElementById('gameReleaseDate').textContent = game.releaseDate;
    
    // تحديث عرض اللغات لتناسب هيكل JSON باستخدام الدالة الجديدة
    document.getElementById('gameLanguages').innerHTML = displayLanguageSupport(game.languages);
    
    document.getElementById('gameSize').textContent = game.size;
    document.getElementById('systemVersion').textContent = game.systemVersion;
    document.getElementById('gameRating').textContent = game.rating.toFixed(1);
    document.getElementById('ratingStars').innerHTML = createRatingStars(game.rating);
    
    // توليد عدد تحميلات عشوائي
    const downloadCount = Math.floor(Math.random() * 10000) + 1000;
    document.getElementById('downloadCount').textContent = downloadCount.toLocaleString();
    
    // عرض معلومات التحديث والإصلاح
    if (game.hasUpdate) {
        const updateInfo = document.getElementById('updateInfo');
        if (updateInfo) {
            updateInfo.innerHTML = `
                <div class="update-details">
                    <div class="update-size">${game.updateSize || 'غير محدد'}</div>
                    <div class="update-url">${game.updateUrl ? '<a href="' + game.updateUrl + '" target="_blank">رابط التحديث</a>' : 'غير متوفر'}</div>
                </div>
            `;
        }
        
        // عرض معلومات إصلاح التحديث
        if (game["hasUpdate-fix"]) {
            const updateFixInfo = document.getElementById('updateFixInfo');
            if (updateFixInfo) {
                const updateFixSize = game["update-fixSize"] || (game.updateFix && game.updateFix.size) || 'غير محدد';
                const updateFixUrl = game["update-fixUrl"] || (game.updateFix && game.updateFix.url) || '#';
                
                updateFixInfo.innerHTML = `
                    <div class="update-fix-details">
                        <div class="update-fix-size">${updateFixSize}</div>
                        <div class="update-fix-url">${updateFixUrl !== '#' ? '<a href="' + updateFixUrl + '" target="_blank">رابط إصلاح التحديث</a>' : 'غير متوفر'}</div>
                    </div>
                `;
            }
        }
    }
    
    populateDownloadCategories(game);
    populateRelatedGames(game);
    populatePopularGames(game);
}

// ===== دالة إنشاء فئات التحميل (محدثة ومعدلة) =====
function populateDownloadCategories(game) {
    const downloadCategoriesContainer = document.getElementById('downloadCategories');
    let categoriesHTML = '';
    
    console.log('Creating download categories for game:', game.title);
    
    // ===== القسم 1: التحميلات الكاملة =====
    const hasCompleteDownloads = game.downloadUrl || (game.hasUpdate && game.updateUrl) || 
                               (game.hasDLC && game.dlcUrl) || (game.hasDLC2 && game.dlc2Url);
    if (hasCompleteDownloads) {
        categoriesHTML += `
            <div class="download-category">
                <div class="category-header">
                    <div class="category-title">
                        <i class="fas fa-download"></i>
                        <span>تحميل كامل</span>
                    </div>
                    <div class="category-badge">ملف واحد</div>
                </div>
                <div class="download-buttons">
        `;
        
        // زر تحميل اللعبة
        if (game.downloadUrl) {
            categoriesHTML += `
                <a href="${game.downloadUrl}" class="download-btn game" data-type="game" onclick="handleDownloadClick(event, this)">
                    <div><i class="fas fa-download"></i> <span class="btn-text">تحميل اللعبة</span></div>
                    <div class="size-info">${game.size}</div>
                </a>
            `;
        }
        
        // زر التحديث
        if (game.hasUpdate && game.updateUrl) {
            categoriesHTML += `
                <a href="${game.updateUrl}" class="download-btn update" data-type="update" onclick="handleDownloadClick(event, this)">
                    <div><i class="fas fa-sync-alt"></i> <span class="btn-text">تحميل التحديث</span></div>
                    <div class="size-info">${game.updateSize}</div>
                </a>
            `;
        }
        
        // ======== بداية تعديل زر إصلاح التحديث =========
        // --- 1. وضع الاختبار (اجعله true لرؤية الزر دائمًا) ---
        const showUpdateFixForTesting = false; // <--- غيّره إلى true للاختبار

        // --- 2. تسجيل بيانات التحقق في الكونسول ---
        console.log('بيانات التحقق لزر إصلاح التحديث:', {
            hasUpdate: game.hasUpdate,
            hasUpdateFix: game["hasUpdate-fix"], // استخدام التنسيق الصحيح
            updateFixUrl: game["update-fixUrl"], // استخدام التنسيق الصحيح
            updateFixSize: game["update-fixSize"], // استخدام التنسيق الصحيح
            // دعم هيكل بديل للبيانات
            altUpdateFix: game.updateFix
        });

        // --- 3. استخراج البيانات من الهيكلين الممكنين ---
        const primaryUrl = game["update-fixUrl"]; // استخدام التنسيق الصحيح
        const primarySize = game["update-fixSize"]; // استخدام التنسيق الصحيح
        const altUrl = game.updateFix && game.updateFix.url;
        const altSize = game.updateFix && game.updateFix.size;

        // --- 4. الشرط المحدث والمرن ---
        if (showUpdateFixForTesting || (game.hasUpdate && game["hasUpdate-fix"] && (primaryUrl || altUrl))) {
            // استخدام الرابط والحجم المتوفرين مع قيم افتراضية
            const finalUpdateFixUrl = primaryUrl || altUrl || '#'; // # كرابط افتراضي للاختبار
            const finalUpdateFixSize = primarySize || altSize || 'غير محدد';
            
            categoriesHTML += `
                <a href="${finalUpdateFixUrl}" class="download-btn update-fix" data-type="update-fix" onclick="handleDownloadClick(event, this)">
                    <div><i class="fas fa-tools"></i> <span class="btn-text">تحميل إصلاح التحديث</span></div>
                    <div class="size-info">${finalUpdateFixSize}</div>
                </a>
            `;
        }
        // ======== نهاية تعديل زر إصلاح التحديث =========
        
        // زر DLC - الأول (باستخدام اسم مخصص)
        if (game.hasDLC && game.dlcUrl) {
            const dlcName = game.dlcName || ' الاضافات (DLC )';
            categoriesHTML += `
                <a href="${game.dlcUrl}" class="download-btn dlc" data-type="dlc" onclick="handleDownloadClick(event, this)">
                    <div><i class="fas fa-puzzle-piece"></i> <span class="btn-text">تحميل ${dlcName}</span></div>
                    <div class="size-info">متعددة</div>
                </a>
            `;
        }
        
        // زر DLC - الثاني (باستخدام اسم مخصص)
        if (game.hasDLC2 && game.dlc2Url) {
            const dlc2Name = game.dlc2Name || 'الإضافة الثانية (DLC 2)';
            categoriesHTML += `
                <a href="${game.dlc2Url}" class="download-btn dlc" data-type="dlc2" onclick="handleDownloadClick(event, this)">
                    <div><i class="fas fa-puzzle-piece"></i> <span class="btn-text">تحميل ${dlc2Name}</span></div>
                    <div class="size-info">متعددة</div>
                </a>
            `;
        }
        
        categoriesHTML += `
                </div>
            </div>
        `;
    }
    
    // ===== القسم 2: التحميلات المقسمة =====
    const hasSplitDownloads = (game.downloadParts && game.downloadParts.length > 0) || 
                           (game.hasUpdate && game.updateParts && game.updateParts.length > 0) || 
                           (game.hasDLC && game.dlcParts && game.dlcParts.length > 0) ||
                           (game.hasDLC2 && game.dlc2Parts && game.dlc2Parts.length > 0);
    
    if (hasSplitDownloads) {
        categoriesHTML += `
            <div class="download-category">
                <div class="category-header">
                    <div class="category-title">
                        <i class="fas fa-layer-group"></i>
                        <span>تحميل مقسم</span>
                    </div>
                    <div class="category-badge">أجزاء متعددة</div>
                </div>
                <div class="download-buttons">
        `;
        
        // أجزاء اللعبة المقسمة
        if (game.downloadParts && game.downloadParts.length > 0) {
            categoriesHTML += createSplitPartsHTML(game.downloadParts, 'game');
        }
        
        // أجزاء التحديث المقسمة
        if (game.hasUpdate && game.updateParts && game.updateParts.length > 0) {
            categoriesHTML += createSplitPartsHTML(game.updateParts, 'update');
        }
        
        // أجزاء إصلاح التحديث المقسمة
        if (game.hasUpdate && game["hasUpdate-fix"] && game.updateFixParts && game.updateFixParts.length > 0) {
            categoriesHTML += createSplitPartsHTML(game.updateFixParts, 'update-fix');
        }
        
        // أجزاء DLC المقسمة - الأول (باستخدام اسم مخصص)
        if (game.hasDLC && game.dlcParts && game.dlcParts.length > 0) {
            const dlcName = game.dlcName || 'الإضافة ';
            categoriesHTML += createSplitPartsHTML(game.dlcParts, 'dlc', dlcName);
        }
        
        // أجزاء DLC المقسمة - الثاني (باستخدام اسم مخصص)
        if (game.hasDLC2 && game.dlc2Parts && game.dlc2Parts.length > 0) {
            const dlc2Name = game.dlc2Name || 'الإضافة الثانية';
            categoriesHTML += createSplitPartsHTML(game.dlc2Parts, 'dlc2', dlc2Name);
        }
        
        categoriesHTML += `
                </div>
            </div>
        `;
    }
    
    downloadCategoriesContainer.innerHTML = categoriesHTML;
    
    // إضافة فئات المنصة للأزرار
    downloadCategoriesContainer.querySelectorAll('.download-btn').forEach(btn => {
        btn.classList.add(game.platform);
    });
}

// ===== دالة إنشاء HTML للأجزاء المقسمة (محدثة) =====
function createSplitPartsHTML(parts, type, customName = null) {
    if (!parts || parts.length === 0) return '';
    
    console.log(`Creating split parts for ${type}, parts count: ${parts.length}`);
    
    let typeIcon = '';
    let typeTitle = '';
    let typeColor = '';
    
    switch(type) {
        case 'game':
            typeIcon = 'fa-download';
            typeTitle = 'اللعبة';
            typeColor = '#28a745';
            break;
        case 'update':
            typeIcon = 'fa-sync-alt';
            typeTitle = 'التحديث';
            typeColor = '#007bff';
            break;
        case 'update-fix':
            typeIcon = 'fa-tools';
            typeTitle = 'إصلاح التحديث';
            typeColor = '#6f42c1';
            break;
        case 'dlc':
            typeIcon = 'fa-puzzle-piece';
            typeTitle = customName || 'الإضافة ';
            typeColor = '#ffc107';
            break;
        case 'dlc2':
            typeIcon = 'fa-puzzle-piece';
            typeTitle = customName || 'الإضافة الثانية';
            typeColor = '#ff6b6b';
            break;
    }
    
    let partsHTML = `
        <div class="split-parts-container">
            <div class="split-parts-header">
                <i class="fas ${typeIcon}" style="color: ${typeColor}"></i>
                <span>${typeTitle} مقسمة إلى ${parts.length} أجزاء</span>
            </div>
            <div class="split-parts-list">
    `;
    
    parts.forEach((part, index) => {
        partsHTML += `
            <div class="split-part-item">
                <div class="split-part-info">
                    <div class="part-number">${index + 1}</div>
                    <div class="part-name">${part.name}</div>
                    <div class="part-size">${part.size || 'غير محدد'}</div>
                </div>
                <button class="download-part-btn" onclick="downloadPart('${part.url}', '${type}', ${index + 1})">
                    <i class="fas fa-download"></i>
                    <span>تحميل</span>
                </button>
            </div>
        `;
    });
    
    partsHTML += `
            </div>
        </div>
    `;
    
    return partsHTML;
}

// ===== RELATED GAMES FUNCTION (MODIFIED) =====
function populateRelatedGames(currentGame) {
    const relatedGamesGrid = document.getElementById('relatedGamesGrid');
    
    const currentGameGenres = Array.isArray(currentGame.genre) ? currentGame.genre : [currentGame.genre];
    
    const relatedGames = allGamesData.filter(game => {
        if (game.id === currentGame.id) return false;
        
        const gameGenres = Array.isArray(game.genre) ? game.genre : [game.genre];
        
        const hasMatchingGenre = currentGameGenres.some(currentGenre => 
            gameGenres.some(gameGenre => 
                currentGenre.trim().toLowerCase() === gameGenre.trim().toLowerCase()
            )
        );
        
        return hasMatchingGenre && game.platform === currentGame.platform;
    });
    
    let fallbackGames = [];
    if (relatedGames.length === 0) {
        fallbackGames = allGamesData.filter(game => {
            if (game.id === currentGame.id) return false;
            
            const gameGenres = Array.isArray(game.genre) ? game.genre : [game.genre];
            
            const hasMatchingGenre = currentGameGenres.some(currentGenre => 
                gameGenres.some(gameGenre => 
                    currentGenre.trim().toLowerCase() === gameGenre.trim().toLowerCase()
                )
            );
            
            return hasMatchingGenre;
        });
    }
    
    let finalFallbackGames = [];
    if (relatedGames.length === 0 && fallbackGames.length === 0) {
        finalFallbackGames = allGamesData.filter(game => {
            if (game.id === currentGame.id) return false;
            return game.platform === currentGame.platform;
        });
    }
    
    let gamesToDisplay = relatedGames.length > 0 ? relatedGames : 
                        fallbackGames.length > 0 ? fallbackGames : 
                        finalFallbackGames;
    
    const shuffled = gamesToDisplay.sort(() => 0.5 - Math.random());
    const selectedGames = shuffled.slice(0, 4);
    
    let relatedGamesHTML = '';
    selectedGames.forEach(game => {
        const formattedTitle = formatTitleForUrl(game.title);
        
        let badgeText = '';
        let badgeClass = '';
        
        const gameGenres = Array.isArray(game.genre) ? game.genre : [game.genre];
        
        const hasMatchingGenre = currentGameGenres.some(currentGenre => 
            gameGenres.some(gameGenre => 
                currentGenre.trim().toLowerCase() === gameGenre.trim().toLowerCase()
            )
        );
        
        if (hasMatchingGenre && game.platform === currentGame.platform) {
            badgeText = 'مطابق';
            badgeClass = 'both';
        } else if (hasMatchingGenre) {
            badgeText = ' الفئة';
            badgeClass = 'genre';
        } else if (game.platform === currentGame.platform) {
            badgeText = ' المنصة';
            badgeClass = 'platform';
        }
        
        relatedGamesHTML += `
            <div class="related-game-card" onclick="window.location.href='download.html?id=${game.id}&title=${formattedTitle}'">
                <img src="${game.image}" alt="${game.title}">
                <div class="related-game-info">
                    <div class="related-game-title">${game.title}</div>
                    <div class="related-game-meta">
                        <div class="related-game-platform">
                            <i class="fab fa-playstation"></i>
                            <span>${game.platform.toUpperCase()}</span>
                        </div>
                        <div class="related-game-size">${game.size}</div>
                    </div>
                </div>
                <div class="related-game-badge ${badgeClass}">${badgeText}</div>
            </div>
        `;
    });
    
    relatedGamesGrid.innerHTML = relatedGamesHTML;
}

// ===== POPULAR GAMES FUNCTION =====
function populatePopularGames(currentGame) {
    const popularGamesList = document.getElementById('popularGamesList');
    
    const sortedByPopularity = [...allGamesData].sort((a, b) => b.popularity - a.popularity);
    
    const topPopularGames = sortedByPopularity.filter(game => game.id !== currentGame.id).slice(0, 5);
    
    let popularGamesHTML = '';
    
    let processedCount = 0;
    
    topPopularGames.forEach((game, index) => {
        const formattedTitle = formatTitleForUrl(game.title);
        
        const starsHTML = createRatingStars(game.rating);
        
        let trendIcon = '';
        let trendClass = '';
        if (game.trend === 'up') {
            trendIcon = 'fa-arrow-up';
            trendClass = 'up';
        } else if (game.trend === 'down') {
            trendIcon = 'fa-arrow-down';
            trendClass = 'down';
        } else {
            trendIcon = 'fa-minus';
            trendClass = 'same';
        }
        
        popularGamesHTML += `
            <div class="popular-game-item" onclick="window.location.href='download.html?id=${game.id}&title=${formattedTitle}'">
                <div class="popular-game-image" id="popular-game-img-${game.id}">
                    <div class="image-placeholder">
                        <i class="fas fa-image"></i>
                    </div>
                </div>
                <div class="popular-game-info">
                    <div class="popular-game-title">${game.title}</div>
                    <div class="popular-game-meta">
                        <div class="popular-game-platform">
                            <i class="fab fa-playstation"></i>
                            <span>${game.platform.toUpperCase()}</span>
                        </div>
                        <div class="popular-game-rating">
                            ${starsHTML}
                        </div>
                    </div>
                </div>
                <div class="popular-game-trend ${trendClass}">
                    <i class="fas ${trendIcon}"></i>
                </div>
            </div>
        `;
        
        processedCount++;
    });
    
    popularGamesList.innerHTML = popularGamesHTML;
    
    topPopularGames.forEach((game, index) => {
        setTimeout(() => {
            const imageContainer = document.getElementById(`popular-game-img-${game.id}`);
            const placeholder = imageContainer.querySelector('.image-placeholder');
            
            const img = new Image();
            img.onload = function() {
                if (placeholder) {
                    placeholder.remove();
                }
                
                const actualImg = document.createElement('img');
                actualImg.src = game.image;
                actualImg.alt = game.title;
                actualImg.className = 'popular-game-actual-image';
                actualImg.style.width = '95px';
                actualImg.style.height = '95px';
                actualImg.style.objectFit = 'cover';
                actualImg.style.borderRadius = '8px';
                imageContainer.appendChild(actualImg);
            };
            img.onerror = function() {
                console.error('Failed to load image:', game.image);
                if (placeholder) {
                    placeholder.style.display = 'flex';
                }
            };
            img.src = game.image;
        }, 100 * index);
    });
}
    // --- INITIALIZATION ---
    async function init() {
        try {
            allGamesData = await loadAllGamesData();
            
            if (allGamesData.length === 0) {
                displayError('لم يتم العثور على بيانات الألعاب. يرجى التحقق من ملفات البيانات.');
                return;
            }
            
            const gameId = parseInt(getUrlParameter('id'));
            currentGame = allGamesData.find(g => g.id === gameId);

            if (currentGame) {
                try {
                    const headerImageUrl = currentGame.headerImage || currentGame.image;
                    document.getElementById('pageHeader').style.backgroundImage = `url(${headerImageUrl})`;
                    
                    preloadImage(headerImageUrl).catch(error => {
                        console.error('Error loading image:', error);
                        document.getElementById('pageHeader').style.background = `linear-gradient(135deg, var(--bg-secondary), var(--accent-color))`;
                    });
                } catch (error) {
                    console.error('Error setting background:', error);
                    document.getElementById('pageHeader').style.background = `linear-gradient(135deg, var(--bg-secondary), var(--accent-color))`;
                }

                populatePage(currentGame);
                document.title = `تحميل ${currentGame.title} - PS Games Hub`;

            } else {
                displayError('لم يتم العثور على اللعبة المطلوبة. قد يكون الرابط غير صحيح أو تم حذف اللعبة.');
            }
        } catch (error) {
            console.error('Initialization error:', error);
            displayError('حدث خطأ أثناء تحميل الصفحة. يرجى المحاولة مرة أخرى.');
        }
    }

    // --- THEME INITIALIZATION ---
    function setupTheme() {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
        
        const themeToggle = document.createElement('button');
        themeToggle.className = 'theme-toggle';
        themeToggle.innerHTML = savedTheme === 'light' ? 
            '<i class="fas fa-moon"></i>' : 
            '<i class="fas fa-sun"></i>';
        themeToggle.addEventListener('click', toggleTheme);
        document.body.appendChild(themeToggle);
    }

    // --- SCROLL TO TOP FUNCTION ---
    function setupScrollToTop() {
        const scrollToTopBtn = document.getElementById('scrollToTop');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.add('visible');
            } else {
                scrollToTopBtn.classList.remove('visible');
            }
        });
        
        scrollToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // Start the application
    setupTheme();
    setupScrollToTop();
    init();
});