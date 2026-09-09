// Global variables and functions
let currentGame = null;
let allGamesData = [];

// ===== التغيير: إضافة نظام تشفير معزز =====
// مفتاح سري للتشفير (يمكنك تغييره لأمان إضافي)
const SECRET_KEY = "MySuperSecretKeyForPSPKG-arabic";

// ===== التغيير: تحسين دالة التشفير المخصص (XOR + Base64) =====
// تمت إضافة encodeURI() لضمان التعامل الآمن مع الأحرف الخاصة في الروابط
function customEncode(url) {
    try {
        // تأكد من أن الرابط آمن للنقل داخل السلسلة النصية
        const safeUrl = encodeURI(url);
        const encoder = new TextEncoder();
        const data = encoder.encode(safeUrl);
        const keyBytes = encoder.encode(SECRET_KEY);

        // تطبيق XOR على كل بايت
        const xoredData = new Uint8Array(data.length);
        for (let i = 0; i < data.length; i++) {
            xoredData[i] = data[i] ^ keyBytes[i % keyBytes.length];
        }

        // تحويل الناتج إلى Base64
        return btoa(String.fromCharCode(...xoredData));
    } catch (e) {
        console.error("Failed to custom encode URL:", e);
        return btoa(url); // Fallback to simple Base64
    }
}

// ===== التغيير: تحسين دالة فك التشفير المخصص (Base64 + XOR) =====
// تمت إضافة تحقق للتحقق من أن الرابط الناتج صالح قبل إرجاعه
function customDecode(encodedUrl) {
    try {
        const decodedString = atob(encodedUrl);
        const decoder = new TextDecoder();
        const data = Uint8Array.from(decodedString, c => c.charCodeAt(0));
        const keyBytes = new TextEncoder().encode(SECRET_KEY);

        // تطبيق XOR على كل بايت لفك التشفير
        const xoredData = new Uint8Array(data.length);
        for (let i = 0; i < data.length; i++) {
            xoredData[i] = data[i] ^ keyBytes[i % keyBytes.length];
        }

        const result = decoder.decode(xoredData);

        // تحقق مما إذا كان الناتج يبدو كرابط URL صالح
        if (result.startsWith('http://') || result.startsWith('https://')) {
            return result;
        } else {
            console.error('Decoded URL is not valid:', result);
            return null; // إرجاع null إذا كان الرابط غير صالح
        }
    } catch (e) {
        console.error('Failed to custom decode URL:', e);
        // محاولة فك التشفير البسيط كحل أخير
        try {
            const result = atob(encodedUrl);
            if (result.startsWith('http://') || result.startsWith('https://')) {
                return result;
            }
        } catch (fallbackError) {
            console.error('Fallback decoding also failed:', fallbackError);
        }
        return null; // إرجاع null في حالة فشل كل شيء
    }
}

// ===== DATA LOADING FUNCTIONS =====
// دالة لتحميل بيانات الألعاب
async function loadAllGamesData() {
    try {
        const [ps4Games, ps5Games, ps3Games] = await Promise.all([
            fetch('data/ps4-games.json').then(res => res.json()),
            fetch('data/ps5-games.json').then(res => res.json()),
            fetch('data/ps3-games.json').then(res => res.json())
        ]);

        const allGames = [...ps4Games, ...ps5Games, ...ps3Games];
        allGames.sort((a, b) => a.id - b.id);

        console.log('All games loaded:', allGames.length, 'games');
        return allGames;
    } catch (error) {
        console.error('Error loading games data:', error);
        return [];
    }
}

// دالة لعرض رسالة خطأ
function displayError(message) {
    const mainContent = document.querySelector('.main-content') || document.body;
    const errorHTML = `
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i>
            <p>${message}</p>
            <a href="index.php" class="back-btn" style="margin-top: 20px; display: inline-block;"><i class="fas fa-arrow-left"></i> العودة إلى الرئيسية</a>
        </div>
    `;
    mainContent.innerHTML = errorHTML;
}

// دالة لإنشاء نجوم التقييم (تعرض 10 نجوم كحد أقصى)
function createRatingStars(rating) {
    let r = parseFloat(rating) || 0;
    if (r > 10) r = 10;
    if (r < 0) r = 0;
    
    let starsHTML = '';
    // حلقة من 1 إلى 10
    for (let i = 1; i <= 10; i++) {
        if (r >= i) {
            // نجمة كاملة
            starsHTML += '<i class="fas fa-star"></i>';
        } else if (r >= i - 0.5) {
            // نصف نجمة
            starsHTML += '<i class="fas fa-star-half-alt"></i>';
        } else {
            // نجمة فارغة
            starsHTML += '<i class="far fa-star"></i>';
        }
    }
    return starsHTML;
}
// دالة لتنسيق العنوان للرابط
function formatTitleForUrl(title) {
    return title.toLowerCase()
        .replace(/\s+/g, '_')
        .replace(/[^a-z0-9_\-]/g, '')
        .replace(/_+/g, '_')
        .replace(/^_|_$/g, '');
}

// دالة مساعدة للتحقق مما إذا كان الكائن
function isObject(item) {
    return (item && typeof item === 'object' && !Array.isArray(item));
}

// ===== PASSWORD MODAL FUNCTIONS =====
window.openPasswordModal = function () {
    const modal = document.getElementById('passwordModal');
    const passwordTextElement = document.getElementById('modalPasswordText');

    // تحديث نص كلمة السر
    const password = currentGame ? currentGame.password : '4GAMER-2024';
    passwordTextElement.textContent = password;

    modal.classList.add('active');

    // === الكود الجديد للتمرير التلقائي ===
    modal.scrollIntoView({
        behavior: 'smooth', // لجعل الحركة سلسة وغير مفاجئة
        block: 'center'     // لوضع النافذة في منتصف الشاشة بعد التمرير
    });
    // === نهاية الكود الجديد ===

    // Add entrance animation
    setTimeout(() => {
        const modalContent = modal.querySelector('.password-modal');
        modalContent.style.transform = 'scale(1) translateY(0)';
    }, 10);
};
window.closePasswordModal = function () {
    const modal = document.getElementById('passwordModal');
    const modalContent = modal.querySelector('.password-modal');

    // Add exit animation
    modalContent.style.transform = 'scale(0.8) translateY(50px)';

    setTimeout(() => {
        modal.classList.remove('active');
        // document.body.style.overflow = ''; // <-- قم بحذف هذا السطر أو تعطيله
    }, 300);
};
window.copyModalPassword = function () {
    // Get current game's password
    const password = currentGame ? currentGame.password : '4GAMER-2024';
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

window.createModalConfetti = function () {
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
document.addEventListener('click', function (event) {
    const modal = document.getElementById('passwordModal');
    if (event.target === modal) {
        closePasswordModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('passwordModal');
        if (modal.classList.contains('active')) {
            closePasswordModal();
        }
    }
});

// ===== GLOBAL FUNCTIONS =====
window.downloadPart = function (url, type, partNumber) {
    console.log(`Downloading part ${partNumber} of type: ${type}`);

    const downloadUrl = customDecode(url);

    // تحقق مما إذا كان الرابط صالحًا بعد فك التشفير
    if (!downloadUrl || !downloadUrl.startsWith('http')) {
        console.error('Invalid or corrupted download URL:', downloadUrl);
        alert('حدث خطأ: رابط التحميل غير صالح. يرجى المحاولة مرة أخرى.');
        return;
    }

    // Create a temporary link to trigger download
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = `part${partNumber}.pkg`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Track the download
    const gameId = window.currentGameId;
    if (window.trackDownload && gameId) {
        window.trackDownload(gameId, `${type}_part_${partNumber}`);
    }
};

// ===== التغيير: تحسين دالة التعامل مع النقر على زر التحميل =====
/// ===== التغيير: دالة زيادة العداد في الخلفية =====
async function trackDownloadCount(gameId, type) {
    if (!gameId) return;
    try {
        const response = await fetch('track_download.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: gameId, type: type })
        });
        const data = await response.json();

        // تحديث العداد في الصفحة فوراً بدون إعادة تحميل الصفحة
        if (data.status === 'success' && data.new_count) {
            const countElement = document.getElementById('downloadCount');
            if (countElement) {
                countElement.textContent = data.new_count.toLocaleString();
            }
        }
    } catch (error) {
        console.error('Failed to track download:', error);
    }
}

// ===== التغيير: تحسين دالة التعامل مع النقر على زر التحميل =====
window.handleDownloadClick = function (event, element) {
    event.preventDefault(); // منع السلوك الافتراضي للرابط
    element.classList.add('loading');

    const type = element.getAttribute('data-type');
    const encodedUrl = element.getAttribute('data-url');
    const downloadUrl = customDecode(encodedUrl);

    // تحقق مما إذا كان الرابط صالحًا بعد فك التشفير
    if (!downloadUrl || !downloadUrl.startsWith('http')) {
        console.error('Invalid or corrupted download URL:', downloadUrl);
        element.classList.remove('loading');
        alert('حدث خطأ: رابط التحميل غير صالح. يرجى المحاولة مرة أخرى.');
        return;
    }

    // === إضافة العداد هنا ===
    const gameId = window.currentGameId;
    trackDownloadCount(gameId, type);

    // إنشاء رابط مؤقت لبدء التحميل
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.setAttribute('download', '');
    link.style.display = 'none';
    document.body.appendChild(link);

    link.click();

    setTimeout(() => {
        document.body.removeChild(link);
        element.classList.remove('loading');
    }, 1000);
};
// ===== IMAGE RESIZE FUNCTION =====
window.resizeImage = function (img, targetWidth, targetHeight) {
    return new Promise((resolve) => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');

        canvas.width = targetWidth;
        canvas.height = targetHeight;

        const image = new Image();
        image.crossOrigin = 'anonymous';

        image.onload = function () {
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

// ===== التغيير: تحسين دالة التحقق من وجود عنصر اللغات =====
// دالة لضمان وجود عنصر اللغات في الصفحة
function ensureGameLanguagesElement() {
    let languagesElement = document.getElementById('gameLanguages');
    if (!languagesElement) {
        // البحث عن مكان إضافة عنصر اللغات
        const gameInfoContainer = document.querySelector('.game-info');
        if (gameInfoContainer) {
            // إنشاء عنصر اللغات
            languagesElement = document.createElement('div');
            languagesElement.className = 'info-item';
            languagesElement.innerHTML = `
                <div class="info-label">
                    <i class="fas fa-language"></i>
                    <span>اللغات</span>
                </div>
                <div class="info-value" id="gameLanguages">غير محدد</div>
            `;

            // البحث عن مكان الإدراج (بعد عنصر النوع)
            const genreElement = document.querySelector('#gameGenre').closest('.info-item');
            if (genreElement && genreElement.nextSibling) {
                gameInfoContainer.insertBefore(languagesElement, genreElement.nextSibling);
            } else {
                gameInfoContainer.appendChild(languagesElement);
            }
        }
    }
    return languagesElement;
}

// تعديل دالة populatePage
function populatePage(game) {
    console.log('Populating page with game:', game.title);

    // Update game information
    document.getElementById('gameTitle').textContent = game.title;
    document.getElementById('gamePlatform').textContent = game.platform.toUpperCase();
    document.getElementById('gameGenre').textContent = Array.isArray(game.genre) ? game.genre.map(g => g.toUpperCase()).join(' • ') : 'غير محدد';
    document.getElementById('gameVersion').textContent = game.version;
    document.getElementById('displayGameCode').textContent = game.gameCode;
    document.getElementById('gameStory').textContent = game.story;
    document.getElementById('gameCode').textContent = game.gameCode;
    document.getElementById('gameDeveloper').textContent = game.developer;
    document.getElementById('gamePublisher').textContent = game.publisher;
    document.getElementById('gameReleaseDate').textContent = game.releaseDate;

    // ===== التغيير: تحسين معالجة بيانات اللغات =====
    // التأكد من وجود عنصر اللغات
    ensureGameLanguagesElement();

    const languagesElement = document.getElementById('gameLanguages');
    if (languagesElement) {
        console.log('Game languages data:', game.languages); // للتصحيح

        if (game.languages && Array.isArray(game.languages)) {
            const languageDetails = game.languages.map(lang => {
                // التحقق من أن العنصر هو كائن ويحتوي على 'name' و 'support'
                if (isObject(lang) && lang.name && lang.support) {
                    const name = lang.name;
                    const support = lang.support;

                    const supportedFeatures = [];
                    if (support.menu === true) {
                        supportedFeatures.push('القوائم');
                    }
                    if (support.subtitles === true) {
                        supportedFeatures.push('الترجمة');
                    }
                    if (support.dubbed === true) {
                        supportedFeatures.push('الدبلجة');
                    }

                    if (supportedFeatures.length > 0) {
                        return `${name} (${supportedFeatures.join('، ')})`;
                    } else {
                        return name;
                    }
                } else if (isObject(lang) && lang.name) {
                    // إذا كان كائنًا ولكن لا يحتوي على support
                    return lang.name;
                } else if (typeof lang === 'string') {
                    // إذا كان نصًا
                    return lang;
                }
                return null; // تجاهل العناصر غير الصالحة
            }).filter(Boolean); // إزالة القيم الفارغة

            if (languageDetails.length > 0) {
                languagesElement.textContent = languageDetails.join(' • ');
                console.log('Languages displayed:', languageDetails.join(' • ')); // للتصحيح
            } else {
                languagesElement.textContent = 'غير محدد';
                console.log('No valid languages found, displaying default'); // للتصحيح
            }
        } else {
            languagesElement.textContent = 'غير محدد';
            console.log('No languages data found, displaying default'); // للتصحيح
        }
    } else {
        console.error('gameLanguages element not found in the DOM');
    }
    document.getElementById('gameSize').textContent = game.size;
    document.getElementById('systemVersion').textContent = game.systemVersion;
    document.getElementById('gameRating').textContent = game.rating.toFixed(1);
    document.getElementById('ratingStars').innerHTML = createRatingStars(game.rating);


    // Populate features section
    populateFeatures(game);

    // Populate download categories
    populateDownloadCategories(game);

    // Populate related games
    populateRelatedGames(game);
}

// ===== FEATURES FUNCTION =====
// دالة لعرض الميزات
function populateFeatures(game) {
    const featuresSection = document.querySelector('.features-section');
    const featuresList = document.getElementById('featuresList');

    if (!featuresSection || !featuresList) return;

    // إفراغ القائمة الحالية
    featuresList.innerHTML = '';

    // التحقق من وجود ميزات
    if (game.features && Array.isArray(game.features) && game.features.length > 0) {
        game.features.forEach((feature, index) => {
            const featureItem = document.createElement('div');
            featureItem.className = 'feature-item';
            featureItem.style.animationDelay = `${index * 0.1}s`;

            const icon = document.createElement('i');
            icon.className = 'fas fa-check-circle';

            const text = document.createElement('span');
            text.textContent = feature;

            featureItem.appendChild(icon);
            featureItem.appendChild(text);
            featuresList.appendChild(featureItem);
        });
    } else {
        // عرض رسالة افتراضية إذا لم تكن هناك ميزات
        const noFeatures = document.createElement('p');
        noFeatures.style.textAlign = 'center';
        noFeatures.style.color = '#666';
        noFeatures.style.padding = '20px';
        noFeatures.textContent = 'لا توجد معلومات عن ميزات هذه اللعبة.';
        featuresList.appendChild(noFeatures);
    }
}

// ===== DOWNLOAD CATEGORIES FUNCTION =====
function populateDownloadCategories(game) {
    const downloadCategoriesContainer = document.getElementById('downloadCategories');
    let categoriesHTML = '';

    console.log('Creating download categories for game:', game.title);
    console.log('Update Parts:', game.updateParts);
    console.log('DLC Parts:', game.dlcParts);

    // ===== القسم 1: التحميلات الكاملة =====
    const hasCompleteDownloads = game.downloadUrl || (game.hasUpdate && game.updateUrl) ||
        (game.hasDLC && game.dlcUrl) || (game.hasDLC2 && game.dlc2Url);
    if (hasCompleteDownloads) {
        categoriesHTML += `
            <div class="download-category">
                <div class="category-header">
                    <div class="category-title">
                        <i class="fas fa-download"></i>
                        <span>تحميل اللعبة PKG</span>
                    </div>
                    <div class="category-badge">ملف واحد</div>
                </div>
                <div class="download-buttons">
        `;

        // زر تحميل اللعبة
        if (game.downloadUrl) {
            // ===== التغيير: تشفير الرابط المعزز =====
            const encodedUrl = customEncode(game.downloadUrl);
            categoriesHTML += `
                <a href="#" class="download-btn game" data-type="game" data-url="${encodedUrl}" onclick="handleDownloadClick(event, this)">
                    <div><i class="fas fa-download"></i> <span class="btn-text">تحميل اللعبة</span></div>
                    <div class="size-info">${game.size}</div>
                </a>
            `;
        }

        // زر التحديث
        if (game.hasUpdate && game.updateUrl) {
            // ===== التغيير: تشفير الرابط المعزز =====
            const encodedUrl = customEncode(game.updateUrl);
            categoriesHTML += `
                <a href="#" class="download-btn update" data-type="update" data-url="${encodedUrl}" onclick="handleDownloadClick(event, this)">
                    <div><i class="fas fa-sync-alt"></i> <span class="btn-text">تحميل التحديث</span></div>
                    <div class="size-info">${game.updateSize}</div>
                </a>
            `;
        }

        // زر إصلاح التحديث
        const showUpdateFixForTesting = false;
        const primaryUrl = game["update-fixUrl"];
        const primarySize = game["update-fixSize"];
        const altUrl = game.updateFix && game.updateFix.url;
        const altSize = game.updateFix && game.updateFix.size;

        if (showUpdateFixForTesting || (game.hasUpdate && game["hasUpdate-fix"] && (primaryUrl || altUrl))) {
            const finalUpdateFixUrl = primaryUrl || altUrl || '#';
            const finalUpdateFixSize = primarySize || altSize || 'غير محدد';
            // ===== التغيير: تشفير الرابط المعزز =====
            const encodedUrl = customEncode(finalUpdateFixUrl);
            categoriesHTML += `
                <a href="#" class="download-btn update-fix" data-type="update-fix" data-url="${encodedUrl}" onclick="handleDownloadClick(event, this)">
                    <div><i class="fas fa-tools"></i> <span class="btn-text">تحميل إصلاح التحديث</span></div>
                    <div class="size-info">${finalUpdateFixSize}</div>
                </a>
            `;
        }

        // زر DLC - الأول
        if (game.hasDLC && game.dlcUrl) {
            const dlcName = game.dlcName || ' الاضافات (DLC )';
            // ===== التغيير: تشفير الرابط المعزز =====
            const encodedUrl = customEncode(game.dlcUrl);
            categoriesHTML += `
                <a href="#" class="download-btn dlc" data-type="dlc" data-url="${encodedUrl}" onclick="handleDownloadClick(event, this)">
                    <div><i class="fas fa-puzzle-piece"></i> <span class="btn-text">تحميل ${dlcName}</span></div>
                    <div class="size-info">متعددة</div>
                </a>
            `;
        }

        // زر DLC - الثاني
        if (game.hasDLC2 && game.dlc2Url) {
            const dlc2Name = game.dlc2Name || 'الإضافة الثانية (DLC 2)';
            // ===== التغيير: تشفير الرابط المعزز =====
            const encodedUrl = customEncode(game.dlc2Url);
            categoriesHTML += `
                <a href="#" class="download-btn dlc" data-type="dlc2" data-url="${encodedUrl}" onclick="handleDownloadClick(event, this)">
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
    const hasDlcParts = game.dlcParts && Array.isArray(game.dlcParts) && game.dlcParts.length > 0;
    console.log('Has DLC Parts:', hasDlcParts);

    // ===== التغيير: تحسين التحقق من وجود أجزاء التحديث المقسمة =====
    const hasUpdateParts = game.updateParts && Array.isArray(game.updateParts) && game.updateParts.length > 0;
    const hasUpdateFixParts = game["update-fixParts"] && Array.isArray(game["update-fixParts"]) && game["update-fixParts"].length > 0;

    console.log('Has Update Parts:', hasUpdateParts);
    console.log('Update Parts:', game.updateParts);

    const hasSplitDownloads = (game.downloadParts && game.downloadParts.length > 0) ||
        hasUpdateParts ||
        hasUpdateFixParts ||
        hasDlcParts ||
        (game.hasDLC2 && game.dlc2Parts && game.dlc2Parts.length > 0);

    console.log('Has Split Downloads:', hasSplitDownloads);

    if (hasSplitDownloads) {
        categoriesHTML += `
            <div class="download-category">
                <div class="category-header">
                    <div class="category-title">
                        <i class="fas fa-layer-group"></i>
                        <span>تحميل اللعبة Rar/ISO</span>
                    </div>
                    <div class="category-badge">أجزاء متعددة</div>
                </div>
                <div class="download-buttons">
        `;

        // أجزاء اللعبة المقسمة
        if (game.downloadParts && game.downloadParts.length > 0) {
            categoriesHTML += createSplitPartsHTML(game.downloadParts, 'game');
        }

        // ===== التغيير: تحسين التحقق من وجود أجزاء التحديث المقسمة =====
        // أجزاء التحديث المقسمة
        if (hasUpdateParts) {
            categoriesHTML += createSplitPartsHTML(game.updateParts, 'update');
        }

        // أجزاء إصلاح التحديث المقسمة
        if (hasUpdateFixParts) {
            categoriesHTML += createSplitPartsHTML(game["update-fixParts"], 'update-fix');
        }

        // أجزاء DLC المقسمة - الأول
        if (hasDlcParts) {
            const dlcName = game.dlcName || 'الإضافة ';
            categoriesHTML += createSplitPartsHTML(game.dlcParts, 'dlc', dlcName);
        }

        // أجزاء DLC المقسمة - الثاني
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
// ===== SPLIT PARTS HTML FUNCTION =====
function createSplitPartsHTML(parts, type, customName = null) {
    if (!parts || parts.length === 0) return '';

    console.log(`Creating split parts for ${type}, parts count: ${parts.length}`);

    let typeIcon = '';
    let typeTitle = '';
    let typeColor = '';

    switch (type) {
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
        // ===== التغيير: تشفير الرابط المعزز =====
        const encodedUrl = customEncode(part.url);
        partsHTML += `
            <div class="split-part-item">
                <div class="split-part-info">
                    <div class="part-number">${index + 1}</div>
                    <div class="part-name">${part.name}</div>
                    <div class="part-size">${part.size || 'غير محدد'}</div>
                </div>
                <button class="download-part-btn" data-url="${encodedUrl}" onclick="downloadPart('${encodedUrl}', '${type}', ${index + 1})">
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

// ===== RELATED GAMES FUNCTION =====
// ===== دالة مساعدة لاستخراج الكلمات المفتاحية (تبقى كما هي) =====
function getTitleKeywords(title) {
    const stopWords = new Set(['the', 'a', 'an', 'of', 'in', 'on', 'for', 'to', 'with', 'and', 'or', 'part', 'episode', 'i', 'ii', 'iii', 'iv', 'v']);
    const words = title.toLowerCase()
        .replace(/[^a-z0-9\s]/g, '')
        .split(/\s+/)
        .filter(word => word && !stopWords.has(word));
    return new Set(words);
}

// ===== دالة الألعاب ذات الصلة (مع إضافة شرط المنصة كمرشح أساسي) =====
function populateRelatedGames(currentGame) {
    const relatedGamesGrid = document.getElementById('relatedGamesGrid');
    const viewAllBtn = document.querySelector('.view-all-btn');

    if (!currentGame || !allGamesData) return;

    const currentGameGenres = Array.isArray(currentGame.genre) ? currentGame.genre : [currentGame.genre];
    const currentGameKeywords = getTitleKeywords(currentGame.title);

    // === الخطوة 1: التصفية الأولية بناءً على المنصة (الشرط الأساسي) ===
    let candidateGames = allGamesData.filter(game => {
        // استبعاد اللعبة الحالية والتأكد من نفس المنصة
        return game.id !== currentGame.id && game.platform === currentGame.platform;
    });

    // === الخطوة 2: حساب درجة التشابه داخل القائمة المصفاة ===
    let scoredGames = candidateGames.map(game => {
        let score = 1; // نقطة أساسية لكل لعبة من نفس المنصة
        let badgeText = 'المنصة';
        let badgeClass = 'platform';

        const gameGenres = Array.isArray(game.genre) ? game.genre : [game.genre];
        const gameKeywords = getTitleKeywords(game.title);

        // --- التحقق من التشابه في التصنيف ---
        const hasGenreMatch = currentGameGenres.some(currentGenre =>
            gameGenres.some(gameGenre =>
                currentGenre.trim().toLowerCase() === gameGenre.trim().toLowerCase()
            )
        );
        if (hasGenreMatch) {
            score += 2; // إضافة 2 نقاط لمطابقة التصنيف
            badgeText = 'الفئة';
            badgeClass = 'genre';
        }

        // --- التحقق من التشابه في العنوان (الأولوية القصوى للترتيب) ---
        let sharedKeywordCount = 0;
        currentGameKeywords.forEach(keyword => {
            if (gameKeywords.has(keyword)) sharedKeywordCount++;
        });
        if (sharedKeywordCount > 0) {
            score += sharedKeywordCount * 5; // 5 نقاط لكل كلمة مفتاحية مشتركة
            badgeText = 'عنوان';
            badgeClass = 'title';
        }

        // ترقية الشارة في حالة التطابق الكامل (تصنيف + عنوان)
        if (hasGenreMatch && sharedKeywordCount > 0) {
            badgeText = 'مطابق';
            badgeClass = 'both';
        }

        return { ...game, score, badgeText, badgeClass };
    });

    // === الخطوة 3: الترتيب حسب النقاط (الأعلى أولاً) ===
    scoredGames.sort((a, b) => b.score - a.score);

    // === الخطوة 4: تحضير وعرض الألعاب ===
    const limitedRelatedGames = scoredGames.slice(0, 4);
    const expandedRelatedGames = scoredGames.slice(0, 12);

    let showingAllRelatedGames = false;
    renderRelatedGames(limitedRelatedGames, showingAllRelatedGames, scoredGames.length);

    // === الخطوة 5: إعداد زر "عرض الكل" ===
    if (viewAllBtn) {
        viewAllBtn.removeEventListener('click', handleViewAllClick);

        function handleViewAllClick() {
            showingAllRelatedGames = !showingAllRelatedGames;
            const gamesToRender = showingAllRelatedGames ? expandedRelatedGames : limitedRelatedGames;
            renderRelatedGames(gamesToRender, showingAllRelatedGames, scoredGames.length);
        }

        viewAllBtn.addEventListener('click', handleViewAllClick);

        if (scoredGames.length > 4) {
            viewAllBtn.innerHTML = `عرض الكل (${scoredGames.length}) <i class="fas fa-arrow-left"></i>`;
        } else {
            viewAllBtn.style.display = 'none';
        }
    } else if (scoredGames.length === 0) {
        // رسالة في حال عدم وجود ألعاب على نفس المنصة
        relatedGamesGrid.innerHTML = '<p style="text-align:center; color:#888;">لا توجد ألعاب أخرى على نفس المنصة.</p>';
    }
}

// ===== دالة العرض (تبقى كما هي) =====
function renderRelatedGames(games, showingAll, totalCount) {
    const relatedGamesGrid = document.getElementById('relatedGamesGrid');
    const viewAllBtn = document.querySelector('.view-all-btn');

    let relatedGamesHTML = '';

    games.forEach(game => {
        const formattedTitle = formatTitleForUrl(game.title);

        relatedGamesHTML += `
            <div class="related-game-card" onclick="window.location.href='download.php?id=${game.id}&title=${formattedTitle}'">
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
                <div class="related-game-badge ${game.badgeClass}">${game.badgeText}</div>
            </div>
        `;
    });

    relatedGamesGrid.innerHTML = relatedGamesHTML;

    if (viewAllBtn) {
        viewAllBtn.innerHTML = showingAll ? `عرض أقل <i class="fas fa-arrow-up"></i>` : `عرض الكل (${totalCount}) <i class="fas fa-arrow-left"></i>`;
    }
}
// ===== POPULAR GAMES FUNCTION =====
function populatePopularGames(currentGame) {
    const popularGamesList = document.getElementById('popularGamesList');
    if (!popularGamesList) return;

    const sortedByPopularity = [...allGamesData].sort((a, b) => b.popularity - a.popularity);
    const topPopularGames = sortedByPopularity.filter(game => game.id !== currentGame.id).slice(0, 5);

    let popularGamesHTML = '';

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
            <div class="popular-game-item" onclick="window.location.href='download.php?id=${game.id}&title=${formattedTitle}'">
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
    });

    popularGamesList.innerHTML = popularGamesHTML;

    // تحسين تحميل الصور لمنع تغير الحجم (Layout Shift)
    topPopularGames.forEach((game, index) => {
        setTimeout(() => {
            const imageContainer = document.getElementById(`popular-game-img-${game.id}`);
            if (!imageContainer) return;

            const placeholder = imageContainer.querySelector('.image-placeholder');
            
            // إنشاء الصورة مباشرة وإضافتها للـ DOM
            const actualImg = document.createElement('img');
            actualImg.src = game.image;
            actualImg.alt = game.title;
            actualImg.className = 'popular-game-actual-image';
            
            // عند تحميل الصورة بنجاح: إزالة الـ placeholder وإظهار الصورة بتأثير ناعم
            actualImg.onload = function () {
                if (placeholder) {
                    placeholder.remove();
                }
                // إضافة الكلاس لتفعيل تأثير الظهور (Opacity)
                requestAnimationFrame(() => {
                    actualImg.classList.add('loaded');
                });
            };

            // في حال فشل تحميل الصورة
            actualImg.onerror = function () {
                console.error('Failed to load image:', game.image);
                actualImg.remove();
                if (placeholder) {
                    placeholder.style.display = 'flex';
                }
            };

            imageContainer.appendChild(actualImg);
        }, 100 * index);
    });
}

// --- UTILITIES FOR SEARCH ---
function sanitize(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function escapeRegex(str) {
    return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function highlightMatch(text, query) {
    const sanitized = sanitize(text);
    if (!query) return sanitized;
    const regex = new RegExp(`(${escapeRegex(query)})`, 'gi');
    return sanitized.replace(regex, '<mark class="search-highlight">$1</mark>');
}

// ===== SEARCH FUNCTIONALITY (مربوط بصفحة البحث) =====
function setupSearch() {
    const searchInput = document.getElementById('searchInput');
    const suggestionsBox = document.getElementById('suggestionsBox');
    const searchBtn = document.getElementById('searchBtn');

    if (!searchInput || !suggestionsBox) return;

    let searchMatches = [];
    let searchSelectedIndex = -1;

    function goToSearchPage(query) {
        if (!query || query.trim().length < 2) return;
        hideSuggestions();
        window.location.href = `search.php?q=${encodeURIComponent(query.trim())}`;
    }

    function hideSuggestions() {
        suggestionsBox.style.display = 'none';
        searchSelectedIndex = -1;
    }

    function updateSelectedHighlight() {
        const items = suggestionsBox.querySelectorAll('.suggestion-item');
        items.forEach((item, i) => item.classList.toggle('selected', i === searchSelectedIndex));
        if (searchSelectedIndex >= 0 && items[searchSelectedIndex]) {
            items[searchSelectedIndex].scrollIntoView({ block: 'nearest' });
        }
    }

    searchInput.addEventListener('input', (e) => {
        clearTimeout(window.searchDebounceTimer);
        window.searchDebounceTimer = setTimeout(() => {
            const query = e.target.value.trim().toLowerCase();
            if (query.length < 2) {
                hideSuggestions();
                return;
            }

            // البحث في allGamesData الخاصة بصفحة التحميل
            const matches = allGamesData.filter(g => g.title.toLowerCase().includes(query));
            searchMatches = matches;
            searchSelectedIndex = -1;
            suggestionsBox.innerHTML = '';

            if (matches.length === 0) {
                suggestionsBox.style.display = 'block';
                suggestionsBox.innerHTML = `
                    <div class="suggestion-empty">
                        <i class="fas fa-search"></i>
                        لا توجد اقتراحات سريعة
                        <div class="suggestion-search-all">
                            ابحث عن "${sanitize(query)}" في كل الألعاب <i class="fas fa-arrow-left"></i>
                        </div>
                    </div>
                `;
                suggestionsBox.querySelector('.suggestion-search-all')?.addEventListener('click', () => goToSearchPage(query));
                return;
            }

            suggestionsBox.style.display = 'block';

            matches.slice(0, 7).forEach((game, index) => {
                const item = document.createElement('div');
                item.className = 'suggestion-item';
                item.innerHTML = `
                    <span class="suggestion-title">${highlightMatch(game.title, query)}</span>
                    <span class="suggestion-platform">${game.platform.toUpperCase()}</span>
                `;

                const formattedTitle = formatTitleForUrl(game.title);
                item.addEventListener('click', () => {
                    window.location.href = `download.php?id=${game.id}&title=${formattedTitle}`;
                    hideSuggestions();
                });

                // ❌ تم حذف mouseenter تماماً لمنع الاختيار العشوائي بالماوس

                suggestionsBox.appendChild(item);
            });

            // زر عرض كل النتائج في صفحة البحث
            const showAll = document.createElement('div');
            showAll.className = 'suggestion-show-all';
            showAll.innerHTML = `<i class="fas fa-search"></i> عرض كل النتائج لـ "${sanitize(query)}"`;
            showAll.addEventListener('click', () => goToSearchPage(query));
            suggestionsBox.appendChild(showAll);

        }, 300);
    });

    // ✅ حدث الضغط على زر البحث
    searchBtn?.addEventListener('click', () => goToSearchPage(searchInput.value));

    searchInput.addEventListener('keydown', (e) => {
        const items = suggestionsBox.querySelectorAll('.suggestion-item');
        const count = items.length;

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                if (count > 0) {
                    searchSelectedIndex = (searchSelectedIndex + 1) % count;
                    updateSelectedHighlight();
                }
                break;
            case 'ArrowUp':
                e.preventDefault();
                if (count > 0) {
                    searchSelectedIndex = searchSelectedIndex <= 0 ? count - 1 : searchSelectedIndex - 1;
                    updateSelectedHighlight();
                }
                break;
            case 'Enter':
                e.preventDefault();
                // يفتح اللعبة "فقط" لو تحركت بالأسهم عمداً واخترت لعبة
                if (searchSelectedIndex >= 0 && searchMatches[searchSelectedIndex]) {
                    const game = searchMatches[searchSelectedIndex];
                    const formattedTitle = formatTitleForUrl(game.title);
                    window.location.href = `download.php?id=${game.id}&title=${formattedTitle}`;
                    hideSuggestions();
                } else {
                    // في كل الحالات الأخرى يذهب لصفحة النتائج
                    goToSearchPage(searchInput.value);
                }
                break;
            case 'Escape':
                hideSuggestions();
                searchInput.blur();
                break;
        }
    });

    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
            hideSuggestions();
        }
    });
}
// ===== EVENT LISTENERS =====
function setupEventListeners() {
    // Setup scroll to top button
    const scrollToTopBtn = document.getElementById('scrollToTop');

    // 1. حماية: التأكد من وجود الزر في الصفحة قبل تنفيذ الكود
    if (!scrollToTopBtn) return;

    // 2. ✨ الحل السحري: نقل الزر من الـ body إلى الـ html لتجاوز مشكلة transform
    document.documentElement.appendChild(scrollToTopBtn);

    // 3. إظهار وإخفاء الزر عند التمرير
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollToTopBtn.classList.add('visible');
        } else {
            scrollToTopBtn.classList.remove('visible');
        }
    });

    // 4. الصعود للأعلى عند الضغط على الزر
    scrollToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// ===== INITIALIZATION =====
document.addEventListener('DOMContentLoaded', async () => {
    // --- HELPER FUNCTIONS ---
    function getUrlParameter(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }

    // --- INITIALIZATION ---
    async function init() {
        try {
            // Use game data passed from PHP
            if (typeof window.currentGameData !== 'undefined' && window.currentGameData !== null) {
                currentGame = window.currentGameData;
                allGamesData = window.allGamesData || [];

                console.log('Game data loaded from PHP:', currentGame);

                // Set page title
                document.title = `تحميل ${currentGame.title} - PSPKG-arabic`;

                // Set header background image
                const headerImageUrl = currentGame.headerImage || currentGame.image;
                if (headerImageUrl) {
                    document.getElementById('pageHeader').style.backgroundImage = `url(${headerImageUrl})`;
                }

                // Populate page with game data
                populatePage(currentGame);

                // Setup event listeners
                setupEventListeners();
                setupSearch();
            } else {
                // Fallback to loading data from JSON files
                console.warn('PHP data not found, falling back to JSON fetch.');
                allGamesData = await loadAllGamesData();

                if (allGamesData.length === 0) {
                    displayError('لم يتم العثور على بيانات الألعاب. يرجى التحقق من ملفات البيانات.');
                    return;
                }

                const gameId = parseInt(getUrlParameter('id'));

                if (isNaN(gameId)) {
                    displayError('لم يتم تحديد معرف اللعبة (ID). يرجى التحقق من الرابط.');
                    return;
                }

                // Find game by ID
                currentGame = allGamesData.find(g => g.id === gameId);

                if (currentGame) {
                    try {
                        const headerImageUrl = currentGame.headerImage || currentGame.image;
                        document.getElementById('pageHeader').style.backgroundImage = `url(${headerImageUrl})`;

                        // Preload image to handle errors
                        const img = new Image();
                        img.onload = function () {
                            console.log('Header image loaded successfully');
                        };
                        img.onerror = function () {
                            console.error('Error loading header image:', headerImageUrl);
                            document.getElementById('pageHeader').style.background = `linear-gradient(135deg, var(--bg-secondary), var(--accent-color))`;
                        };
                        img.src = headerImageUrl;
                    } catch (error) {
                        console.error('Error setting background:', error);
                        document.getElementById('pageHeader').style.background = `linear-gradient(135deg, var(--bg-secondary), var(--accent-color))`;
                    }

                    populatePage(currentGame);
                    document.title = `تحميل ${currentGame.title} - PSPKG-arabic`;

                    // Setup event listeners
                    setupEventListeners();
                    setupSearch();
                } else {
                    displayError('لم يتم العثور على اللعبة المطلوبة. قد يكون الرابط غير صحيح أو تم حذف اللعبة.');
                }
            }
        } catch (error) {
            console.error('Initialization error:', error);
            displayError('حدث خطأ أثناء تحميل الصفحة. يرجى المحاولة مرة أخرى.');
        }
    }

    // Start the application
    init();
});

// ===== THEMES DROPDOWN FUNCTIONALITY =====
document.addEventListener('DOMContentLoaded', function () {
    const themesBtn = document.getElementById('themesBtn');
    const themesMenu = document.getElementById('themesMenu');

    if (themesBtn && themesMenu) {
        themesBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            themesMenu.classList.toggle('show');
            themesBtn.classList.toggle('active');

            // إغلاق قائمة التصنيفات عند فتح قائمة التيمات
            const categoriesMenu = document.getElementById('categoriesMenu');
            const categoriesBtn = document.getElementById('categoriesBtn');
            if (categoriesMenu && categoriesBtn) {
                categoriesMenu.classList.remove('show');
                categoriesBtn.classList.remove('active');
            }
        });
    }

    // ===== CATEGORIES DROPDOWN FUNCTIONALITY =====
    const categoriesBtn = document.getElementById('categoriesBtn');
    const categoriesMenu = document.getElementById('categoriesMenu');

    if (categoriesBtn && categoriesMenu) {
        categoriesBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            categoriesMenu.classList.toggle('show');
            categoriesBtn.classList.toggle('active');

            // إغلاق قائمة التيمات عند فتح قائمة التصنيفات
            if (themesMenu && themesBtn) {
                themesMenu.classList.remove('show');
                themesBtn.classList.remove('active');
            }
        });
    }

    // إغلاق القوائم عند النقر في أي مكان آخر في الصفحة
    document.addEventListener('click', function () {
        if (themesMenu) themesMenu.classList.remove('show');
        if (themesBtn) themesBtn.classList.remove('active');
        if (categoriesMenu) categoriesMenu.classList.remove('show');
        if (categoriesBtn) categoriesBtn.classList.remove('active');
    });

    // منع إغلاق القائمة عند النقر داخلها
    if (themesMenu) {
        themesMenu.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    if (categoriesMenu) {
        categoriesMenu.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }
});

// تحقق من وجود أخطاء JavaScript
window.addEventListener('error', function (e) {
    console.error('JavaScript Error:', e.error);
});

// تحقق من تحميل الصفحة بالكامل
window.addEventListener('load', function () {
    console.log('Page loaded completely');
    console.log('Comments section:', document.querySelector('.comments-section'));
});

// Comments Section JavaScript
document.addEventListener('DOMContentLoaded', function () {
    // Submit comment
    const submitCommentBtn = document.getElementById('submitComment');
    const commentName = document.getElementById('commentName');
    const commentText = document.getElementById('commentText');
    const commentsList = document.getElementById('commentsList');
    const commentsCount = document.getElementById('commentsCount');

    if (submitCommentBtn) {
        submitCommentBtn.addEventListener('click', function () {
            if (commentName.value.trim() === '' || commentText.value.trim() === '') {
                // Show error message
                showNotification('يرجى ملء جميع الحقول', 'error');
                return;
            }

            // Create new comment element
            const newComment = document.createElement('div');
            newComment.className = 'comment-item';

            const currentDate = new Date().toLocaleDateString('ar-SA');

            newComment.innerHTML = `
                <div class="comment-avatar">
                    <img src="https://picsum.photos/seed/${Date.now()}/60/60.jpg" alt="User Avatar">
                </div>
                <div class="comment-content">
                    <div class="comment-header">
                        <h5 class="comment-author">${commentName.value}</h5>
                        <span class="comment-date">الآن</span>
                    </div>
                    <p class="comment-text">${commentText.value}</p>
                    <div class="comment-actions">
                        <button class="comment-action-btn like-btn">
                            <i class="fas fa-thumbs-up"></i>
                            <span>0</span>
                        </button>
                        <button class="comment-action-btn reply-btn">
                            <i class="fas fa-reply"></i>
                            <span>رد</span>
                        </button>
                    </div>
                </div>
            `;

            // Add comment to the top of the list
            commentsList.insertBefore(newComment, commentsList.firstChild);

            // Update comments count
            const currentCount = parseInt(commentsCount.textContent);
            commentsCount.textContent = currentCount + 1;

            // Clear form
            commentName.value = '';
            commentText.value = '';

            // Show success message
            showNotification('تم نشر تعليقك بنجاح', 'success');

            // Add event listeners to new comment buttons
            addCommentButtonListeners(newComment);
        });
    }

    // Like button functionality
    function addCommentButtonListeners(commentElement) {
        const likeBtns = commentElement.querySelectorAll('.like-btn');
        likeBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const countSpan = this.querySelector('span');
                let count = parseInt(countSpan.textContent);

                if (this.classList.contains('liked')) {
                    this.classList.remove('liked');
                    countSpan.textContent = count - 1;
                } else {
                    this.classList.add('liked');
                    countSpan.textContent = count + 1;
                }
            });
        });

        // Reply button functionality
        const replyBtns = commentElement.querySelectorAll('.reply-btn');
        replyBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                // In a real implementation, this would open a reply form
                showNotification('سيتم إضافة نموذج الرد قريبًا', 'info');
            });
        });
    }

    // Add listeners to existing comments
    const existingComments = document.querySelectorAll('.comment-item');
    existingComments.forEach(comment => {
        addCommentButtonListeners(comment);
    });

    // Load more comments
    const loadMoreBtn = document.getElementById('loadMoreComments');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function () {
            // In a real implementation, this would load more comments from the server
            showNotification('لا توجد تعليقات إضافية', 'info');
            this.style.display = 'none';
        });
    }

    // Notification function
    function showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;

        // Add notification styles if not already added
        if (!document.querySelector('#notification-styles')) {
            const style = document.createElement('style');
            style.id = 'notification-styles';
            style.textContent = `
                .notification {
                    position: fixed;
                    top: 20px;
                    left: 50%;
                    transform: translateX(-50%);
                    background: white;
                    padding: 15px 20px;
                    border-radius: 5px;
                    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
                    z-index: 1000;
                    display: flex;
                    align-items: center;
                    min-width: 300px;
                    animation: slideDown 0.3s ease;
                }

                .notification.success {
                    border-right: 4px solid #28a745;
                }

                .notification.error {
                    border-right: 4px solid #dc3545;
                }

                .notification.info {
                    border-right: 4px solid #17a2b8;
                }

                .notification-content {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }

                .notification i {
                    font-size: 1.2rem;
                }

                .notification.success i {
                    color: #28a745;
                }

                .notification.error i {
                    color: #dc3545;
                }

                .notification.info i {
                    color: #17a2b8;
                }

                @keyframes slideDown {
                    from {
                        opacity: 0;
                        transform: translate(-50%, -20px);
                    }
                    to {
                        opacity: 1;
                        transform: translate(-50%, 0);
                    }
                }
            `;
            document.head.appendChild(style);
        }

        // Add notification to the page
        document.body.appendChild(notification);

        // Remove notification after 3 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translate(-50%, -20px)';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
});

// Report Form JavaScript
document.addEventListener('DOMContentLoaded', function () {
    const reportForm = document.getElementById('reportForm');
    const reportSuccessMessage = document.getElementById('reportSuccessMessage');

    if (reportForm) {
        reportForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const reason = document.getElementById('reportReason').value;
            const details = document.getElementById('reportDetails').value.trim();

            if (!reason || !details) {
                showMessage(reportSuccessMessage, 'الرجاء ملء جميع الحقول', 'error');
                return;
            }

            // إرسال البلاغ عبر API
            fetch('api/reports.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add_report',
                    game_id: window.currentGameId,
                    game_title: window.currentGameData.title,
                    reason: reason,
                    details: details
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(reportSuccessMessage, data.message, 'success');
                        reportForm.reset();
                    } else {
                        showMessage(reportSuccessMessage, 'حدث خطأ أثناء إرسال البلاغ', 'error');
                    }
                })
                .catch(error => {
                    showMessage(reportSuccessMessage, 'حدث خطأ أثناء إرسال البلاغ', 'error');
                    console.error('Error:', error);
                });
        });
    }

    // دالة لعرض الرسائل
    function showMessage(element, message, type) {
        element.textContent = message;
        element.style.display = 'block';

        if (type === 'error') {
            element.style.backgroundColor = '#f44336';
        } else {
            element.style.backgroundColor = '#4CAF50';
        }

        // إخفاء الرسالة بعد 5 ثوانٍ
        setTimeout(() => {
            element.style.display = 'none';
        }, 5000);
    }
});

// دالة لتحميل التعليقات المعتمدة
function loadApprovedComments() {
    const commentsList = document.getElementById('commentsList');
    if (!commentsList) return;

    fetch(`api/comments.php?action=get_approved_comments&game_id=${window.currentGameId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.comments && data.comments.length > 0) {
                // عرض التعليقات المعتمدة
                data.comments.forEach(comment => {
                    const commentElement = document.createElement('div');
                    commentElement.className = 'comment-item';
                    commentElement.innerHTML = `
                        <div class="comment-avatar">
                            <img src="https://picsum.photos/seed/${comment.id}/60/60.jpg" alt="${comment.name}">
                        </div>
                        <div class="comment-content">
                            <div class="comment-header">
                                <h5 class="comment-author">${comment.name}</h5>
                                <span class="comment-date">${comment.date}</span>
                            </div>
                            <p class="comment-text">${comment.comment}</p>
                        </div>
                    `;
                    commentsList.appendChild(commentElement);
                });

                // تحديث عداد التعليقات
                const commentsCount = document.getElementById('commentsCount');
                if (commentsCount) {
                    commentsCount.textContent = data.comments.length;
                }
            } else {
                // عرض رسالة عدم وجود تعليقات
                const noComments = document.createElement('div');
                noComments.className = 'no-comments';
                noComments.innerHTML = '<p>لا توجد تعليقات بعد. كن أول من يعلق!</p>';
                commentsList.appendChild(noComments);
            }
        })
        .catch(error => {
            console.error('Error loading comments:', error);
        });
}

// تعديل دالة إرسال التعليق
document.addEventListener('DOMContentLoaded', function () {
    const commentForm = document.getElementById('submitComment');
    const commentName = document.getElementById('commentName');
    const commentText = document.getElementById('commentText');
    const commentMessage = document.getElementById('commentMessage');

    if (commentForm) {
        commentForm.addEventListener('click', function (e) {
            e.preventDefault();

            const name = commentName.value.trim();
            const comment = commentText.value.trim();

            if (!name || !comment) {
                showMessage(commentMessage, 'الرجاء ملء جميع الحقول', 'error');
                return;
            }

            // إرسال التعليق عبر API
            fetch('api/comments.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add_comment',
                    game_id: window.currentGameId,
                    name: name,
                    comment: comment
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(commentMessage, data.message, 'success');
                        commentName.value = '';
                        commentText.value = '';

                        // إضافة التعليق الجديد إلى القائمة (اختياري)
                        addCommentToList({
                            id: Date.now(),
                            name: name,
                            comment: comment,
                            date: new Date().toLocaleDateString('ar-SA'),
                            status: 'pending'
                        });
                    } else {
                        showMessage(commentMessage, 'حدث خطأ أثناء إرسال التعليق', 'error');
                    }
                })
                .catch(error => {
                    showMessage(commentMessage, 'حدث خطأ أثناء إرسال التعليق', 'error');
                    console.error('Error:', error);
                });
        });
    }

    // دالة لعرض الرسائل
    function showMessage(element, message, type) {
        if (!element) {
            element = document.createElement('div');
            element.style.position = 'fixed';
            element.style.top = '20px';
            element.style.left = '50%';
            element.style.transform = 'translateX(-50%)';
            element.style.padding = '15px 20px';
            element.style.borderRadius = '5px';
            element.style.zIndex = '1000';
            element.style.minWidth = '300px';
            document.body.appendChild(element);
        }

        element.textContent = message;
        element.style.display = 'block';

        if (type === 'error') {
            element.style.backgroundColor = '#f44336';
            element.style.color = 'white';
        } else {
            element.style.backgroundColor = '#4CAF50';
            element.style.color = 'white';
        }

        // إخفاء الرسالة بعد 5 ثوانٍ
        setTimeout(() => {
            element.style.display = 'none';
        }, 5000);
    }

    // دالة لإضافة تعليق جديد إلى القائمة (اختياري)
    function addCommentToList(comment) {
        const commentsList = document.getElementById('commentsList');
        if (!commentsList) return;

        const newComment = document.createElement('div');
        newComment.className = 'comment-item';
        newComment.innerHTML = `
            <div class="comment-avatar">
                <img src="https://picsum.photos/seed/${comment.id}/60/60.jpg" alt="${comment.name}">
            </div>
            <div class="comment-content">
                <div class="comment-header">
                    <h5 class="comment-author">${comment.name}</h5>
                    <span class="comment-date">${comment.date}</span>
                </div>
                <p class="comment-text">${comment.comment}</p>
                <div class="comment-status">
                    <span class="status-badge pending">في انتظار الموافقة</span>
                </div>
            </div>
        `;

        // إضافة التعليق في بداية القائمة
        if (commentsList.firstChild) {
            commentsList.insertBefore(newComment, commentsList.firstChild);
        } else {
            commentsList.appendChild(newComment);
        }

        // تحديث عداد التعليقات
        const commentsCount = document.getElementById('commentsCount');
        if (commentsCount) {
            const currentCount = parseInt(commentsCount.textContent);
            commentsCount.textContent = currentCount + 1;
        }
    }

    // تحميل التعليقات المعتمدة عند تحميل الصفحة
    loadApprovedComments();
});

// دالة لتحميل التعليقات المعتمدة
function loadApprovedComments() {
    const commentsList = document.getElementById('commentsList');
    if (!commentsList) return;

    fetch(`api/comments.php?action=get_approved_comments&game_id=${window.currentGameId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.comments && data.comments.length > 0) {
                // عرض التعليقات المعتمدة
                data.comments.forEach(comment => {
                    const commentElement = document.createElement('div');
                    commentElement.className = 'comment-item';
                    commentElement.innerHTML = `
                        <div class="comment-avatar">
                            <img src="https://picsum.photos/seed/${comment.id}/60/60.jpg" alt="${comment.name}">
                        </div>
                        <div class="comment-content">
                            <div class="comment-header">
                                <h5 class="comment-author">${comment.name}</h5>
                                <span class="comment-date">${comment.date}</span>
                            </div>
                            <p class="comment-text">${comment.comment}</p>
                        </div>
                    `;
                    commentsList.appendChild(commentElement);
                });

                // تحديث عداد التعليقات
                const commentsCount = document.getElementById('commentsCount');
                if (commentsCount) {
                    commentsCount.textContent = data.comments.length;
                }
            } else {
                // عرض رسالة عدم وجود تعليقات
                const noComments = document.createElement('div');
                noComments.className = 'no-comments';
                noComments.innerHTML = '<p>لا توجد تعليقات بعد. كن أول من يعلق!</p>';
                commentsList.appendChild(noComments);
            }
        })
        .catch(error => {
            console.error('Error loading comments:', error);
        });
}
// تعديل دالة إرسال البلاغ
const reportForm = document.getElementById('reportForm');
const reportSuccessMessage = document.getElementById('reportSuccessMessage');

if (reportForm) {
    reportForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const reason = document.getElementById('reportReason').value;
        const details = document.getElementById('reportDetails').value.trim();

        if (!reason || !details) {
            showMessage(reportSuccessMessage, 'الرجاء ملء جميع الحقول', 'error');
            return;
        }

        // إرسال البلاغ عبر API
        fetch('api/reports.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add_report',
                game_id: window.currentGameId,
                game_title: window.currentGameData.title,
                reason: reason,
                details: details
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(reportSuccessMessage, data.message, 'success');
                    reportForm.reset();
                } else {
                    showMessage(reportSuccessMessage, 'حدث خطأ أثناء إرسال البلاغ', 'error');
                }
            })
            .catch(error => {
                showMessage(reportSuccessMessage, 'حدث خطأ أثناء إرسال البلاغ', 'error');
                console.error('Error:', error);
            });
    });
}

// دالة لعرض الرسائل
function showMessage(element, message, type) {
    element.textContent = message;
    element.style.display = 'block';

    if (type === 'error') {
        element.style.backgroundColor = '#f44336';
    } else {
        element.style.backgroundColor = '#4CAF50';
    }

    // إخفاء الرسالة بعد 5 ثوانٍ
    setTimeout(() => {
        element.style.display = 'none';
    }, 5000);
}

// دالة لإضافة تعليق جديد إلى القائمة (اختياري)
function addCommentToList(comment) {
    const commentsList = document.getElementById('commentsList');
    if (!commentsList) return;

    const newComment = document.createElement('div');
    newComment.className = 'comment-item';
    newComment.innerHTML = `
            <div class="comment-avatar">
                <img src="https://picsum.photos/seed/${comment.id}/60/60.jpg" alt="${comment.name}">
            </div>
            <div class="comment-content">
                <div class="comment-header">
                    <h5 class="comment-author">${comment.name}</h5>
                    <span class="comment-date">${comment.date}</span>
                </div>
                <p class="comment-text">${comment.comment}</p>
                <div class="comment-status">
                    <span class="status-badge pending">في انتظار الموافقة</span>
                </div>
            </div>
        `;

    // إضافة التعليق في بداية القائمة
    if (commentsList.firstChild) {
        commentsList.insertBefore(newComment, commentsList.firstChild);
    } else {
        commentsList.appendChild(newComment);
    }

    // تحديث عداد التعليقات
    const commentsCount = document.getElementById('commentsCount');
    if (commentsCount) {
        const currentCount = parseInt(commentsCount.textContent);
        commentsCount.textContent = currentCount + 1;
    }
}

// تحميل التعليقات المعتمدة عند تحميل الصفحة
loadApprovedComments();


// دالة لتحميل التعليقات المعتمدة
function loadApprovedComments() {
    const commentsList = document.getElementById('commentsList');
    if (!commentsList) return;

    fetch(`api/comments.php?action=get_approved_comments&game_id=${window.currentGameId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.comments && data.comments.length > 0) {
                // عرض التعليقات المعتمدة
                data.comments.forEach(comment => {
                    const commentElement = document.createElement('div');
                    commentElement.className = 'comment-item';
                    commentElement.innerHTML = `
                        <div class="comment-avatar">
                            <img src="https://picsum.photos/seed/${comment.id}/60/60.jpg" alt="${comment.name}">
                        </div>
                        <div class="comment-content">
                            <div class="comment-header">
                                <h5 class="comment-author">${comment.name}</h5>
                                <span class="comment-date">${comment.date}</span>
                            </div>
                            <p class="comment-text">${comment.comment}</p>
                        </div>
                    `;
                    commentsList.appendChild(commentElement);
                });

                // تحديث عداد التعليقات
                const commentsCount = document.getElementById('commentsCount');
                if (commentsCount) {
                    commentsCount.textContent = data.comments.length;
                }
            } else {
                // عرض رسالة عدم وجود تعليقات
                const noComments = document.createElement('div');
                noComments.className = 'no-comments';
                noComments.innerHTML = '<p>لا توجد تعليقات بعد. كن أول من يعلق!</p>';
                commentsList.appendChild(noComments);
            }
        })
        .catch(error => {
            console.error('Error loading comments:', error);
        });
}

// دالة لتحميل التعليقات المعتمدة من الخادم
function getApprovedComments(gameId) {
    return fetch(`api/comments.php?action=get_approved_comments&game_id=${gameId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                return data.comments;
            }
            return [];
        })
        .catch(error => {
            console.error('Error loading comments:', error);
            return [];
        });
}

// تحسين دالة handleDownloadClick للتعامل مع التشفير
window.handleDownloadClick = function (event, element) {
    event.preventDefault();

    // ✨ 1. احتساب التحميل فوراً في أول سطر (قبل فك التشفير)
    const gameId = window.currentGameId;
    if (gameId) {
        const type = element.getAttribute('data-type') || 'main';
        trackDownloadCount(gameId, type);
    }

    element.classList.add('loading');

    const encodedUrl = element.getAttribute('data-url');
    const downloadUrl = customDecode(encodedUrl);

    // تحقق مما إذا كان الرابط صالحًا بعد فك التشفير
    if (!downloadUrl || !downloadUrl.startsWith('http')) {
        console.error('Invalid or corrupted download URL:', downloadUrl);
        element.classList.remove('loading');
        alert('حدث خطأ: رابط التحميل غير صالح. يرجى المحاولة مرة أخرى.');
        return;
    }

    console.log('Attempting to download from URL:', downloadUrl);

    // إنشاء رابط مؤقت لبدء التحميل
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.setAttribute('download', '');
    link.style.display = 'none';
    document.body.appendChild(link);

    link.click();

    setTimeout(() => {
        document.body.removeChild(link);
        element.classList.remove('loading');
    }, 1000);
};

// تحسين دالة downloadPart للتعامل مع التشفير
window.downloadPart = function (url, type, partNumber) {
    // ✨ 1. احتساب التحميل فوراً في أول سطر
    const gameId = window.currentGameId;
    if (gameId) {
        trackDownloadCount(gameId, `${type}_part_${partNumber}`);
    }

    console.log(`Downloading part ${partNumber} of type: ${type}`);

    const downloadUrl = customDecode(url);

    // تحقق مما إذا كان الرابط صالحًا بعد فك التشفير
    if (!downloadUrl || !downloadUrl.startsWith('http')) {
        console.error('Invalid or corrupted download URL:', downloadUrl);
        alert('حدث خطأ: رابط التحميل غير صالح. يرجى المحاولة مرة أخرى.');
        return;
    }

    // Create a temporary link to trigger download
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = `part${partNumber}.pkg`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
// دالة لعرض الميزات
function populateFeatures(game) {
    const featuresSection = document.querySelector('.features-section');
    const featuresList = document.getElementById('featuresList');

    if (!featuresSection || !featuresList) return;

    // إفراغ القائمة الحالية
    featuresList.innerHTML = '';

    // التحقق من وجود ميزات
    if (game.features && Array.isArray(game.features) && game.features.length > 0) {
        game.features.forEach((feature, index) => {
            const featureItem = document.createElement('div');
            featureItem.className = 'feature-item';
            featureItem.style.animationDelay = `${index * 0.1}s`;

            const icon = document.createElement('i');
            icon.className = 'fas fa-check-circle';

            const text = document.createElement('span');
            text.textContent = feature;

            featureItem.appendChild(icon);
            featureItem.appendChild(text);
            featuresList.appendChild(featureItem);
        });
    } else {
        // عرض رسالة افتراضية إذا لم تكن هناك ميزات
        const noFeatures = document.createElement('p');
        noFeatures.style.textAlign = 'center';
        noFeatures.style.color = '#666';
        noFeatures.style.padding = '20px';
        noFeatures.textContent = 'لا توجد معلومات عن ميزات هذه اللعبة.';
        featuresList.appendChild(noFeatures);
    }
}
function closePopup() {
    const popup = document.getElementById("alertPopup");
    popup.style.display = "none";
}