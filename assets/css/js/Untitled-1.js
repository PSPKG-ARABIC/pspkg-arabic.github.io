
// Global variables and functions
let currentGame = null;
let allGamesData = [];

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

// دالة لإنشاء نجوم التقييم
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
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';

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
        document.body.style.overflow = '';
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

window.handleDownloadClick = function (event, element) {
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

// ===== POPULATE PAGE FUNCTIONS =====
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

    // Update languages
    const languagesElement = document.getElementById('gameLanguages');
    if (game.languages && game.languages.length > 0) {
        const languages = game.languages.map(lang => {
            return isObject(lang) ? lang.name : lang;
        });
        languagesElement.textContent = languages.join(' • ');
    } else {
        languagesElement.textContent = 'غير محدد';
    }

    document.getElementById('gameSize').textContent = game.size;
    document.getElementById('systemVersion').textContent = game.systemVersion;
    document.getElementById('gameRating').textContent = game.rating.toFixed(1);
    document.getElementById('ratingStars').innerHTML = createRatingStars(game.rating);

    // Generate random download count
    const downloadCount = Math.floor(Math.random() * 10000) + 1000;
    document.getElementById('downloadCount').textContent = downloadCount.toLocaleString();

    // Populate download categories
    populateDownloadCategories(game);

    // Populate related games
    populateRelatedGames(game);

    // Populate popular games
    populatePopularGames(game);
}

// ===== DOWNLOAD CATEGORIES FUNCTION =====
function populateDownloadCategories(game) {
    const downloadCategoriesContainer = document.getElementById('downloadCategories');
    let categoriesHTML = '';

    console.log('Creating download categories for game:', game.title);
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

        // زر إصلاح التحديث
        const showUpdateFixForTesting = false;
        const primaryUrl = game["update-fixUrl"];
        const primarySize = game["update-fixSize"];
        const altUrl = game.updateFix && game.updateFix.url;
        const altSize = game.updateFix && game.updateFix.size;

        if (showUpdateFixForTesting || (game.hasUpdate && game["hasUpdate-fix"] && (primaryUrl || altUrl))) {
            const finalUpdateFixUrl = primaryUrl || altUrl || '#';
            const finalUpdateFixSize = primarySize || altSize || 'غير محدد';

            categoriesHTML += `
<a href="${finalUpdateFixUrl}" class="download-btn update-fix" data-type="update-fix" onclick="handleDownloadClick(event, this)">
<div><i class="fas fa-tools"></i> <span class="btn-text">تحميل إصلاح التحديث</span></div>
<div class="size-info">${finalUpdateFixSize}</div>
</a>
`;
        }

        // زر DLC - الأول
        if (game.hasDLC && game.dlcUrl) {
            const dlcName = game.dlcName || ' الاضافات (DLC )';
            categoriesHTML += `
<a href="${game.dlcUrl}" class="download-btn dlc" data-type="dlc" onclick="handleDownloadClick(event, this)">
<div><i class="fas fa-puzzle-piece"></i> <span class="btn-text">تحميل ${dlcName}</span></div>
<div class="size-info">متعددة</div>
</a>
`;
        }

        // زر DLC - الثاني
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
    const hasDlcParts = game.dlcParts && Array.isArray(game.dlcParts) && game.dlcParts.length > 0;
    console.log('Has DLC Parts:', hasDlcParts);

    const hasSplitDownloads = (game.downloadParts && game.downloadParts.length > 0) ||
        (game.hasUpdate && game.updateParts && game.updateParts.length > 0) ||
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

        // أجزاء التحديث المقسمة
        if (game.hasUpdate && game.updateParts && game.updateParts.length > 0) {
            categoriesHTML += createSplitPartsHTML(game.updateParts, 'update');
        }

        // أجزاء إصلاح التحديث المقسمة
        if (game.hasUpdate && game["hasUpdate-fix"] && game.updateFixParts && game.updateFixParts.length > 0) {
            categoriesHTML += createSplitPartsHTML(game.updateFixParts, 'update-fix');
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

// ===== RELATED GAMES FUNCTION =====
function populateRelatedGames(currentGame) {
    const relatedGamesGrid = document.getElementById('relatedGamesGrid');
    const viewAllBtn = document.querySelector('.view-all-btn');

    // الحصول على تصنيفات اللعبة الحالية
    const currentGameGenres = Array.isArray(currentGame.genre) ? currentGame.genre : [currentGame.genre];

    // البحث عن الألعاب ذات الصلة
    let relatedGames = allGamesData.filter(game => {
        if (game.id === currentGame.id) return false;

        const gameGenres = Array.isArray(game.genre) ? game.genre : [game.genre];

        const hasMatchingGenre = currentGameGenres.some(currentGenre =>
            gameGenres.some(gameGenre =>
                currentGenre.trim().toLowerCase() === gameGenre.trim().toLowerCase()
            )
        );

        return hasMatchingGenre && game.platform === currentGame.platform;
    });

    // إذا لم يتم العثور على ألعاب من نفس المنصة والتصنيف، ابحث عن ألعاب من نفس التصنيف فقط
    if (relatedGames.length === 0) {
        relatedGames = allGamesData.filter(game => {
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

    // إذا لم يتم العثور على ألعاب من نفس التصنيف، ابحث عن ألعاب من نفس المنصة فقط
    if (relatedGames.length === 0) {
        relatedGames = allGamesData.filter(game => {
            if (game.id === currentGame.id) return false;
            return game.platform === currentGame.platform;
        });
    }

    // خلط الألعاب واختيار أول 4 ألعاب للعرض المحدود
    const shuffled = [...relatedGames].sort(() => 0.5 - Math.random());
    const limitedRelatedGames = shuffled.slice(0, 4);

    // عرض الألعاب المحدودة في البداية
    let showingAllRelatedGames = false;
    renderRelatedGames(limitedRelatedGames, showingAllRelatedGames, relatedGames.length);

    // إضافة مستمع حدث لزر "عرض الكل"
    if (viewAllBtn) {
        viewAllBtn.addEventListener('click', function () {
            showingAllRelatedGames = !showingAllRelatedGames;
            renderRelatedGames(showingAllRelatedGames ? relatedGames : limitedRelatedGames, showingAllRelatedGames, relatedGames.length);
        });

        // تحديث نص الزر بناءً على عدد الألعاب
        if (relatedGames.length > 4) {
            viewAllBtn.innerHTML = `عرض الكل (${relatedGames.length}) <i class="fas fa-arrow-left"></i>`;
        } else {
            viewAllBtn.style.display = 'none';
        }
    }
}

// ===== RENDER RELATED GAMES FUNCTION =====
function renderRelatedGames(games, showingAll, totalCount) {
    const relatedGamesGrid = document.getElementById('relatedGamesGrid');
    const viewAllBtn = document.querySelector('.view-all-btn');

    let relatedGamesHTML = '';

    games.forEach(game => {
        const formattedTitle = formatTitleForUrl(game.title);

        let badgeText = '';
        let badgeClass = '';

        const gameGenres = Array.isArray(game.genre) ? game.genre : [game.genre];
        const currentGameGenres = Array.isArray(currentGame.genre) ? currentGame.genre : [currentGame.genre];

        const hasMatchingGenre = currentGameGenres.some(currentGenre =>
            gameGenres.some(gameGenre =>
                currentGenre.trim().toLowerCase() === gameGenre.trim().toLowerCase()
            )
        );

        if (hasMatchingGenre && game.platform === currentGame.platform) {
            badgeText = 'مطابق';
            badgeClass = 'both';
        } else if (hasMatchingGenre) {
            badgeText = 'الفئة';
            badgeClass = 'genre';
        } else if (game.platform === currentGame.platform) {
            badgeText = 'المنصة';
            badgeClass = 'platform';
        }

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
<div class="related-game-badge ${badgeClass}">${badgeText}</div>
</div>
`;
    });

    relatedGamesGrid.innerHTML = relatedGamesHTML;

    // تحديث نص الزر بناءً على حالة العرض
    if (viewAllBtn) {
        if (showingAll) {
            viewAllBtn.innerHTML = `عرض أقل <i class="fas fa-arrow-up"></i>`;
        } else {
            viewAllBtn.innerHTML = `عرض الكل (${totalCount}) <i class="fas fa-arrow-left"></i>`;
        }
    }
}

// ===== POPULAR GAMES FUNCTION =====
function populatePopularGames(currentGame) {
    const popularGamesList = document.getElementById('popularGamesList');

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

    // Load images with delay for better performance
    topPopularGames.forEach((game, index) => {
        setTimeout(() => {
            const imageContainer = document.getElementById(`popular-game-img-${game.id}`);
            const placeholder = imageContainer.querySelector('.image-placeholder');

            const img = new Image();
            img.onload = function () {
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
            img.onerror = function () {
                console.error('Failed to load image:', game.image);
                if (placeholder) {
                    placeholder.style.display = 'flex';
                }
            };
            img.src = game.image;
        }, 100 * index);
    });
}

// ===== SEARCH FUNCTIONALITY =====
function setupSearch() {
    const searchInput = document.getElementById('searchInput');
    const suggestionsBox = document.getElementById('suggestionsBox');

    if (!searchInput || !suggestionsBox) return;

    searchInput.addEventListener('input', (e) => {
        clearTimeout(window.searchDebounceTimer);
        window.searchDebounceTimer = setTimeout(() => {
            const query = e.target.value.toLowerCase();
            if (query.length < 2) {
                suggestionsBox.style.display = 'none';
                return;
            }

            const matches = allGamesData.filter(g => g.title.toLowerCase().includes(query));
            suggestionsBox.innerHTML = '';

            if (matches.length > 0) {
                suggestionsBox.style.display = 'block';
                matches.slice(0, 5).forEach(game => {
                    const item = document.createElement('div');
                    item.className = 'suggestion-item';
                    item.innerHTML = `${game.title} <span style="color: var(--text-secondary);">(${game.platform.toUpperCase()})</span>`;

                    item.addEventListener('click', () => {
                        const formattedTitle = formatTitleForUrl(game.title);
                        window.location.href = `download.php?id=${game.id}&title=${formattedTitle}`;
                    });

                    suggestionsBox.appendChild(item);
                });
            } else {
                suggestionsBox.style.display = 'none';
            }
        }, 300);
    });

    document.addEventListener('click', (e) => {
        if (e.target !== searchInput) {
            suggestionsBox.style.display = 'none';
        }
    });
}

// ===== EVENT LISTENERS =====
function setupEventListeners() {
    // Setup scroll to top button
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
                document.title = `تحميل ${currentGame.title} - PSGOLD4GAMER`;

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
                    document.title = `تحميل ${currentGame.title} - PSGOLD4GAMER`;

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
