// ========================================
// نظام إدارة الألعاب - PSGOLD4GAMER
// ========================================

// المتغيرات العامة
let games = [];
let editingGameId = null;
let debugMode = false;
let currentPage = 1;
const gamesPerPage = 30;
let currentUser = null;
let uploadedImages = [];
let selectedImageField = null;
let adminCredentials = null;

// تهيئة التطبيق عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function () {
    loadAdminCredentials();
    checkAuth();
    setupDragAndDrop();
    setupEventListeners();
});

// ========================================
// نظام إدارة بيانات الاعتماد
// ========================================

function loadAdminCredentials() {
    const stored = localStorage.getItem('adminCredentials');
    if (stored) {
        adminCredentials = JSON.parse(stored);
    } else {
        adminCredentials = {
            username: 'admin',
            password: 'admin123'
        };
        saveAdminCredentials();
    }
}

function saveAdminCredentials() {
    localStorage.setItem('adminCredentials', JSON.stringify(adminCredentials));
}

function updatePassword(newPassword) {
    adminCredentials.password = newPassword;
    saveAdminCredentials();
}

// ========================================
// نظام المصادقة والأمان
// ========================================

function checkAuth() {
    const session = localStorage.getItem('adminSession');
    if (session) {
        const sessionData = JSON.parse(session);
        if (sessionData.expires > Date.now()) {
            currentUser = sessionData.username;
            document.getElementById('currentUser').textContent = currentUser;
            document.getElementById('loginPage').style.display = 'none';
            document.getElementById('mainApp').style.display = 'block';
            initializeApp();
        } else {
            logout();
        }
    } else {
        document.getElementById('loginPage').style.display = 'flex';
        document.getElementById('mainApp').style.display = 'none';
    }
}

function handleLogin(event) {
    event.preventDefault();
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    const isValid = adminCredentials.username === username && adminCredentials.password === password;

    if (isValid) {
        const session = {
            username: username,
            expires: Date.now() + (24 * 60 * 60 * 1000)
        };

        localStorage.setItem('adminSession', JSON.stringify(session));
        currentUser = username;
        document.getElementById('currentUser').textContent = username;

        showToast('تم تسجيل الدخول بنجاح!');
        document.getElementById('loginPage').style.display = 'none';
        document.getElementById('mainApp').style.display = 'block';

        initializeApp();
    } else {
        showToast('اسم المستخدم أو كلمة المرور غير صحيحة!', 'error');
        document.getElementById('password').value = '';
    }
}

function logout() {
    localStorage.removeItem('adminSession');
    currentUser = null;
    document.getElementById('loginPage').style.display = 'flex';
    document.getElementById('mainApp').style.display = 'none';
    document.getElementById('username').value = '';
    document.getElementById('password').value = '';
    showToast('تم تسجيل الخروج بنجاح!');
}

function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('passwordToggle');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

function toggleUserMenu() {
    const menu = document.getElementById('userMenu');
    menu.classList.toggle('hidden');
}

function changePassword() {
    document.getElementById('changePasswordModal').classList.add('show');
    document.getElementById('userMenu').classList.add('hidden');
}

function closeChangePasswordModal() {
    document.getElementById('changePasswordModal').classList.remove('show');
    document.getElementById('currentPassword').value = '';
    document.getElementById('newPassword').value = '';
    document.getElementById('confirmPassword').value = '';
}

function handlePasswordChange(event) {
    event.preventDefault();

    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (currentPassword !== adminCredentials.password) {
        showToast('كلمة المرور الحالية غير صحيحة!', 'error');
        return;
    }

    if (newPassword !== confirmPassword) {
        showToast('كلمات المرور الجديدة غير متطابقة!', 'error');
        return;
    }

    if (newPassword.length < 6) {
        showToast('كلمة المرور الجديدة يجب أن تكون 6 أحرف على الأقل!', 'error');
        return;
    }

    updatePassword(newPassword);
    showToast('تم تغيير كلمة المرور بنجاح!', 'success');
    closeChangePasswordModal();
}

// ========================================
// نظام إدارة الصور
// ========================================

function setupDragAndDrop() {
    const uploadArea = document.getElementById('imageUploadArea');
    if (!uploadArea) return;

    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
    });
}

function selectImage(fieldId) {
    selectedImageField = fieldId;
    showImageManager();
}

function showImageManager() {
    document.getElementById('imageManagerModal').classList.add('show');
    loadUploadedImages();
}

function closeImageManager() {
    document.getElementById('imageManagerModal').classList.remove('show');
    selectedImageField = null;
}

function handleImageUpload(event) {
    handleFiles(event.target.files);
}

function handleFiles(files) {
    Array.from(files).forEach(file => {
        if (file.type.startsWith('image/')) {
            if (file.size > 5 * 1024 * 1024) {
                showToast(`الملف ${file.name} كبير جداً (الحد الأقصى 5MB)`, 'error');
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                const imageData = {
                    id: Date.now() + Math.random(),
                    name: file.name,
                    size: file.size,
                    type: file.type,
                    data: e.target.result,
                    uploadDate: new Date().toISOString()
                };

                uploadedImages.push(imageData);
                saveImagesToStorage();
                loadUploadedImages();
                showToast(`تم رفع الصورة ${file.name} بنجاح!`);
            };
            reader.readAsDataURL(file);
        } else {
            showToast(`الملف ${file.name} ليس صورة!`, 'error');
        }
    });
}
function loadUploadedImages() {
    const stored = localStorage.getItem('uploadedImages');
    if (stored) {
        uploadedImages = JSON.parse(stored);
    }

    const imageGrid = document.getElementById('imageGrid');
    if (!imageGrid) return;

    if (uploadedImages.length === 0) {
        imageGrid.innerHTML = '<div class="col-span-full text-center text-gray-500 py-8">لا توجد صور مرفوعة</div>';
        return;
    }

    imageGrid.innerHTML = uploadedImages.map(image => `
        <div class="image-item">
            <img src="${image.data}" alt="${image.name}">
            <div class="overlay">
                <button onclick="selectThisImage(${image.id})" class="bg-blue-500 text-white px-3 py-1 rounded mx-1">
                    <i class="fas fa-check"></i>
                </button>
                <button onclick="deleteImage(${image.id})" class="bg-red-500 text-white px-3 py-1 rounded mx-1">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="p-2 bg-gray-100">
                <p class="text-xs truncate">${image.name}</p>
                <p class="text-xs text-gray-500">${(image.size / 1024).toFixed(1)} KB</p>
            </div>
        </div>
    `).join('');

    updateTotalImageSize();
}
function selectThisImage(imageId) {
    const image = uploadedImages.find(img => img.id === imageId);
    if (!image) {
        showToast('الصورة غير موجودة!', 'error');
        return;
    }

    if (selectedImageField) {
        const imagePath = `../assets/images/${image.name}`;
        document.getElementById(selectedImageField).value = imagePath;

        if (selectedImageField === 'gameImage') {
            document.getElementById('previewImage').src = image.data;
        } else if (selectedImageField === 'gameSliderImage') {
            document.getElementById('previewSliderImage').src = image.data;
        } else if (selectedImageField === 'gameHeaderImage') {
            document.getElementById('previewHeaderImage').src = image.data;
        }

        closeImageManager();
        showToast('تم اختيار الصورة وتحديد المسار بنجاح!');
    }
}

function saveImagesToStorage() {
    localStorage.setItem('uploadedImages', JSON.stringify(uploadedImages));
}

function updateTotalImageSize() {
    const totalSize = uploadedImages.reduce((sum, img) => sum + img.size, 0);
    const sizeElement = document.getElementById('totalImageSize');
    if (sizeElement) {
        sizeElement.textContent = (totalSize / (1024 * 1024)).toFixed(2) + ' MB';
    }
}

// ========================================
// تهيئة التطبيق الرئيسي
// ========================================
function initializeApp() {
    initializeDarkMode();
    setupDarkModeKeyboardShortcuts();
    setupSystemThemeListener();
    applyDarkModeSettings();

    loadGamesFromStorage();
    loadUploadedImages();
    showTab('list');
}

// ========================================
// إعداد مستمعي الأحداث
// ========================================
function setupEventListeners() {
    const gameForm = document.getElementById('gameForm');
    if (gameForm) {
        gameForm.setAttribute('novalidate', '');
    }

    document.getElementById('loginForm').addEventListener('submit', handleLogin);
    document.getElementById('changePasswordForm').addEventListener('submit', handlePasswordChange);

    document.querySelectorAll('.sub-tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const tabId = this.getAttribute('data-tab');
            switchSubTab(tabId);
        });
    });

    if (gameForm) {
        gameForm.addEventListener('submit', function (e) {
            e.preventDefault();
            saveGame();
        });
    }

    document.querySelectorAll('.language-main-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const langId = this.id.replace('Supported', '');
            const optionsDiv = document.getElementById(langId + 'SupportOptions');
            optionsDiv.classList.toggle('hidden', !this.checked);
        });
    });

    document.getElementById('gameDownloadUrl').addEventListener('input', function () {
        document.getElementById('downloadUrlSection').classList.remove('hidden');
    });
    document.getElementById('updateUrl').addEventListener('input', function () {
        document.getElementById('updateSection').classList.remove('hidden');
    });
    document.getElementById('update-fixUrl').addEventListener('input', function () {
        document.getElementById('update-fix-section').classList.remove('hidden');
        if (!document.getElementById('hasUpdate').checked) {
            document.getElementById('hasUpdate').checked = true;
            document.getElementById('updateSection').classList.remove('hidden');
        }
    });
    document.getElementById('dlcUrl').addEventListener('input', function () {
        document.getElementById('dlcSection').classList.remove('hidden');
    });
    document.getElementById('dlc2Url').addEventListener('input', function () {
        document.getElementById('dlc2Section').classList.remove('hidden');
        if (!document.getElementById('hasDLC').checked) {
            document.getElementById('hasDLC').checked = true;
            document.getElementById('dlcSection').classList.remove('hidden');
        }
    });

    document.getElementById('hasDownloadUrl').addEventListener('change', function () {
        document.getElementById('downloadUrlSection').classList.toggle('hidden', !this.checked);
    });
    document.getElementById('hasUpdate').addEventListener('change', function () {
        document.getElementById('updateSection').classList.toggle('hidden', !this.checked);
        if (!this.checked) {
            document.getElementById('hasUpdate-fix').checked = false;
            document.getElementById('update-fix-section').classList.add('hidden');
        }
    });
    document.getElementById('hasUpdate-fix').addEventListener('change', function () {
        document.getElementById('update-fix-section').classList.toggle('hidden', !this.checked);
    });
    document.getElementById('hasDLC').addEventListener('change', function () {
        document.getElementById('dlcSection').classList.toggle('hidden', !this.checked);
        if (!this.checked) {
            document.getElementById('hasDLC2').checked = false;
            document.getElementById('dlc2Section').classList.add('hidden');
        }
    });
    document.getElementById('hasDLC2').addEventListener('change', function () {
        document.getElementById('dlc2Section').classList.toggle('hidden', !this.checked);
    });

    document.getElementById('hasUpdateParts').addEventListener('change', function () {
        document.getElementById('updatePartsSection').classList.toggle('hidden', !this.checked);
    });
    document.getElementById('hasDLCParts').addEventListener('change', function () {
        document.getElementById('dlcPartsSection').classList.toggle('hidden', !this.checked);
    });
    document.getElementById('hasDownloadParts').addEventListener('change', function () {
        document.getElementById('downloadPartsSection').classList.toggle('hidden', !this.checked);
    });

    document.getElementById('hasFeatures').addEventListener('change', function () {
        document.getElementById('featuresSection').classList.toggle('hidden', !this.checked);
    });

    document.getElementById('gameImage').addEventListener('input', function () {
        if (this.value) document.getElementById('previewImage').src = this.value;
    });
    document.getElementById('gameSliderImage').addEventListener('input', function () {
        if (this.value) document.getElementById('previewSliderImage').src = this.value;
    });
    document.getElementById('gameHeaderImage').addEventListener('input', function () {
        if (this.value) document.getElementById('previewHeaderImage').src = this.value;
    });

    document.getElementById('searchInput').addEventListener('input', filterGames);
    document.getElementById('platformFilter').addEventListener('change', filterGames);
    document.getElementById('genreFilter').addEventListener('change', filterGames);
    document.getElementById('ratingFilter').addEventListener('change', filterGames);

    document.addEventListener('keydown', function (e) {
        if (e.ctrlKey && e.key === 'd') {
            e.preventDefault();
            debugMode = !debugMode;
            document.getElementById('debugInfo').classList.toggle('show', debugMode);
            updateDebugInfo();
        }
    });

    document.addEventListener('click', function (e) {
        const userMenu = document.getElementById('userMenu');
        if (!e.target.closest('.relative') && !userMenu.classList.contains('hidden')) {
            userMenu.classList.add('hidden');
        }
    });
}
function updateDebugInfo(message) {
    if (!debugMode) return;
    const debugDiv = document.getElementById('debugInfo');
    if (message) {
        debugDiv.innerHTML = message;
    } else {
        debugDiv.innerHTML = `Games: ${games.length} | Tab: ${document.querySelector('.tab-content.active').id} | Page: ${currentPage}`;
    }
}

function showLoading() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
        loadingOverlay.classList.add('show');
        loadingOverlay.style.display = 'flex';
    } else {
        console.error('Loading overlay element not found!');
    }
}

function hideLoading() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
        loadingOverlay.classList.remove('show');
        setTimeout(() => {
            loadingOverlay.style.display = 'none';
        }, 300);
    } else {
        console.error('Loading overlay element not found!');
    }
}

function showTab(tabName) {
    console.log(`Switching to tab: ${tabName}`);
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });

    const targetTab = document.getElementById(tabName + 'Tab');
    if (targetTab) {
        targetTab.classList.add('active');
    } else {
        console.error(`Tab with ID ${tabName}Tab not found!`);
        return;
    }

    if (tabName === 'list') {
        currentPage = 1;
        displayGames();
    }

    updateDebugInfo();
}
function switchSubTab(tabId) {
    document.querySelectorAll('.sub-tab-btn').forEach(btn => {
        btn.classList.remove('border-indigo-500', 'text-indigo-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });

    const activeBtn = document.querySelector(`.sub-tab-btn[data-tab="${tabId}"]`);
    if (activeBtn) {
        activeBtn.classList.remove('border-transparent', 'text-gray-500');
        activeBtn.classList.add('border-indigo-500', 'text-indigo-600');
    }

    document.querySelectorAll('.sub-tab-content').forEach(content => {
        content.classList.remove('active');
    });

    const targetContent = document.getElementById(tabId);
    if (targetContent) {
        targetContent.classList.add('active');

        setTimeout(() => {
            targetContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    }
}

// ========================================
// إدارة الأجزاء (معدلة بالكامل)
// ========================================
function addPart(listId) {
    const partsList = document.getElementById(listId);
    if (!partsList) {
        console.error(`Parts list with ID ${listId} not found`);
        return;
    }

    const partCount = partsList.children.length + 1;

    const partDiv = document.createElement('div');
    partDiv.className = 'part-item bg-white p-3 rounded-lg border border-gray-200';
    // تم تغيير الشبكة إلى 3 أعمدة وإزالة حقل الحجم
    partDiv.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center">
            <input type="text" placeholder="اسم الجزء ${partCount} (اختياري)" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <input type="text" placeholder="رابط التحميل" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <button type="button" onclick="this.closest('.part-item').remove();" class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600 transition">
                <i class="fas fa-trash"></i> حذف
            </button>
        </div>
    `;

    partsList.appendChild(partDiv);
    partDiv.querySelector('input').focus();
}

function loadParts(listId, parts) {
    const partsList = document.getElementById(listId);
    if (!partsList) {
        console.warn(`Parts list with ID ${listId} not found`);
        return;
    }

    partsList.innerHTML = '';

    if (parts && parts.length > 0) {
        parts.forEach((part, index) => {
            const partDiv = document.createElement('div');
            partDiv.className = 'part-item bg-white p-3 rounded-lg border border-gray-200';
            partDiv.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center">
                    <input type="text" value="${part.name || ''}" placeholder="اسم الجزء ${index + 1} (اختياري)" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <input type="text" value="${part.url || ''}" placeholder="رابط التحميل" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <button type="button" onclick="this.closest('.part-item').remove();" class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600 transition">
                        <i class="fas fa-trash"></i> حذف
                    </button>
                </div>
            `;
            partsList.appendChild(partDiv);
        });
    }
}
function collectParts(listId) {
    const parts = [];
    const partsList = document.getElementById(listId);
    if (!partsList) return parts;

    partsList.querySelectorAll('.part-item').forEach((item, index) => {
        const inputs = item.querySelectorAll('input');
        // تم التعديل لتصبح حقلين فقط (الاسم والرابط)
        if (inputs.length >= 2) {
            const name = inputs[0].value.trim();
            const url = inputs[1].value.trim();

            parts.push({
                name: name || `جزء ${index + 1}`,
                url: url
            });
        }
    });
    return parts;
}
// ========================================
// إدارة الميزات
// ========================================
function addFeature() {
    const featuresList = document.getElementById('featuresList');
    if (!featuresList) {
        console.error("Features list not found");
        return;
    }

    const featureCount = featuresList.children.length + 1;

    const featureDiv = document.createElement('div');
    featureDiv.className = 'feature-item bg-white p-3 rounded-lg border border-gray-200';
    featureDiv.innerHTML = `
        <div class="flex gap-2 items-center">
            <input type="text" placeholder="ميزة ${featureCount}" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <button type="button" onclick="this.closest('.feature-item').remove();" class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600 transition">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;

    featuresList.appendChild(featureDiv);
    featureDiv.querySelector('input').focus();
}

function loadFeatures(features) {
    const featuresList = document.getElementById('featuresList');
    if (!featuresList) {
        console.warn("Features list not found");
        return;
    }

    featuresList.innerHTML = '';

    if (features && features.length > 0) {
        features.forEach((feature, index) => {
            const featureDiv = document.createElement('div');
            featureDiv.className = 'feature-item bg-white p-3 rounded-lg border border-gray-200';
            featureDiv.innerHTML = `
                <div class="flex gap-2 items-center">
                    <input type="text" value="${feature || ''}" placeholder="ميزة ${index + 1}" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <button type="button" onclick="this.closest('.feature-item').remove();" class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600 transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            featuresList.appendChild(featureDiv);
        });
    }
}

function collectFeatures() {
    const features = [];
    const featuresList = document.getElementById('featuresList');
    if (!featuresList) return features;

    featuresList.querySelectorAll('.feature-item').forEach(item => {
        const input = item.querySelector('input');
        if (input) {
            const feature = input.value.trim();
            if (feature) {
                features.push(feature);
            }
        }
    });
    return features;
}

// ========================================
// الحصول على الألعاب من السيرفر
// ========================================
function loadGamesFromStorage() {
    showLoading();
    fetch('api_get_games.php')
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            games = Array.isArray(data) ? data : [];
            localStorage.setItem('gamesData', JSON.stringify(games));

            updateGamesCount();
            updateStatistics();
            updateAutoCompleteLists();
            updateLastUpdateTime();
            displayGames();
            hideLoading();
        })
        .catch(error => {
            console.error('Error loading games:', error);
            const stored = localStorage.getItem('gamesData');
            if (stored) {
                games = JSON.parse(stored);
            } else {
                games = [];
            }
            updateGamesCount();
            updateStatistics();
            updateAutoCompleteLists();
            updateLastUpdateTime();
            displayGames();
            hideLoading();
            showToast('حدث خطأ أثناء جلب الألعاب من السيرفر. تأكد من وجود ملف api_get_games.php', 'error');
        });
}

function saveGamesToStorage() {
    localStorage.setItem('gamesData', JSON.stringify(games));
    updateGamesCount();
    updateStatistics();
    updateAutoCompleteLists();
    updateLastUpdateTime();
}

function updateStatistics() {
    const ps4Count = games.filter(g => g.platform === 'ps4').length;
    const ps3Count = games.filter(g => g.platform === 'ps3').length;
    const ps5Count = games.filter(g => g.platform === 'ps5').length;

    const ps4CountEl = document.getElementById('ps4Count');
    const ps3CountEl = document.getElementById('ps3Count');
    const ps5CountEl = document.getElementById('ps5Count');
    if (ps4CountEl) ps4CountEl.textContent = ps4Count;
    if (ps3CountEl) ps3CountEl.textContent = ps3Count;
    if (ps5CountEl) ps5CountEl.textContent = ps5Count;

    const ps4ExportCountEl = document.getElementById('ps4ExportCount');
    const ps3ExportCountEl = document.getElementById('ps3ExportCount');
    const ps5ExportCountEl = document.getElementById('ps5ExportCount');
    if (ps4ExportCountEl) ps4ExportCountEl.textContent = ps4Count;
    if (ps3ExportCountEl) ps3ExportCountEl.textContent = ps3Count;
    if (ps5ExportCountEl) ps5ExportCountEl.textContent = ps5Count;

    let totalSize = 0;
    games.forEach(game => {
        const sizeMatch = game.size.match(/(\d+\.?\d*)/);
        if (sizeMatch) {
            totalSize += parseFloat(sizeMatch[1]);
        }
    });
    const totalSizeEl = document.getElementById('totalSize');
    if (totalSizeEl) totalSizeEl.textContent = totalSize.toFixed(1) + ' GB';
}

function updateAutoCompleteLists() {
    const developers = [...new Set(games.map(game => game.developer).filter(Boolean))];
    const publishers = [...new Set(games.map(game => game.publisher).filter(Boolean))];

    const developerList = document.getElementById('developerList');
    const publisherList = document.getElementById('publisherList');

    if (developerList) developerList.innerHTML = developers.map(dev => `<option value="${dev}">`).join('');
    if (publisherList) publisherList.innerHTML = publishers.map(pub => `<option value="${pub}">`).join('');
}

function collectFormData() {
    const formData = {
        id: parseInt(document.getElementById('gameId').value),
        title: document.getElementById('gameTitle').value,
        platform: document.getElementById('gamePlatform').value,
        genre: Array.from(document.querySelectorAll('.genre-checkbox:checked')).map(cb => cb.value),
        size: document.getElementById('gameSize').value,
        image: document.getElementById('gameImage').value.trim(),
        sliderImage: document.getElementById('gameSliderImage').value.trim(),
        headerImage: document.getElementById('gameHeaderImage').value.trim(),
        systemVersion: document.getElementById('systemVersion').value,
        version: document.getElementById('gameVersion').value,
        gameCode: document.getElementById('gameCode').value,
        story: document.getElementById('gameStory').value,
        developer: document.getElementById('gameDeveloper').value,
        publisher: document.getElementById('gamePublisher').value,
        releaseDate: document.getElementById('gameReleaseDate').value,
        rating: parseFloat(document.getElementById('gameRating').value),
        password: document.getElementById('gamePassword').value,
        languages: collectLanguageData(),
        downloadParts: collectParts('downloadPartsList'),
        updateParts: collectParts('updatePartsList'),
        dlcParts: collectParts('dlcPartsList'),
        features: collectFeatures()
    };

    formData.downloadUrl = document.getElementById('gameDownloadUrl').value;
    formData.hasDownloadUrl = document.getElementById('hasDownloadUrl').checked;

    formData.hasUpdate = document.getElementById('hasUpdate').checked;
    if (formData.hasUpdate) {
        formData.updateSize = document.getElementById('updateSize').value;
        formData.updateUrl = document.getElementById('updateUrl').value;
        formData["hasUpdate-fix"] = document.getElementById('hasUpdate-fix').checked;
        if (formData["hasUpdate-fix"]) {
            formData["update-fixSize"] = document.getElementById('update-fixSize').value;
            formData["update-fixUrl"] = document.getElementById('update-fixUrl').value;
        }
    }

    formData.hasDLC = document.getElementById('hasDLC').checked;
    if (formData.hasDLC) {
        formData.dlcCount = parseInt(document.getElementById('dlcCount').value);
        formData.dlcName = document.getElementById('dlcName').value;
        formData.dlcUrl = document.getElementById('dlcUrl').value;
        formData.hasDLC2 = document.getElementById('hasDLC2').checked;
        if (formData.hasDLC2) {
            formData.dlc2Name = document.getElementById('dlc2Name').value;
            formData.dlc2Url = document.getElementById('dlc2Url').value;
        }
    }

    formData.hasDownloadParts = document.getElementById('hasDownloadParts').checked;
    formData.hasUpdateParts = document.getElementById('hasUpdateParts').checked;
    formData.hasDLCParts = document.getElementById('hasDLCParts').checked;
    formData.hasFeatures = document.getElementById('hasFeatures').checked;

    return formData;
}

function collectLanguageData() {
    const languages = [];
    const languageMap = { 'arabic': 'العربية', 'english': 'الإنجليزية', 'french': 'الفرنسية', 'spanish': 'الإسبانية', 'japanese': 'اليابانية' };

    Object.keys(languageMap).forEach(langCode => {
        const supportedCheckbox = document.getElementById(langCode + 'Supported');
        if (supportedCheckbox && supportedCheckbox.checked) {
            languages.push({
                name: languageMap[langCode],
                code: langCode,
                support: {
                    menu: document.getElementById(langCode + 'Menu').checked,
                    subtitles: document.getElementById(langCode + 'Subtitles').checked,
                    dubbed: document.getElementById(langCode + 'Dubbed').checked
                }
            });
        }
    });
    return languages;
}

function saveGame() {
    const formData = collectFormData();
    const errors = validateGameData(formData);
    if (errors.length > 0) {
        showValidationErrors(errors);
        return;
    }

    let targetPage = currentPage;

    if (editingGameId) {
        const index = games.findIndex(g => g.id === editingGameId);
        if (index !== -1) {
            games[index] = formData;
            showToast('تم تحديث اللعبة بنجاح!');

            const filteredGames = getFilteredGames();
            const gameIndex = filteredGames.findIndex(g => g.id === editingGameId);
            if (gameIndex !== -1) {
                targetPage = Math.floor(gameIndex / gamesPerPage) + 1;
            }
        }
        editingGameId = null;
    } else {
        if (games.find(g => g.id === formData.id)) {
            showToast('معرف اللعبة موجود بالفعل!', 'error');
            return;
        }
        games.push(formData);
        showToast('تم إضافة اللعبة بنجاح!');

        const filteredGames = getFilteredGames();
        targetPage = Math.ceil(filteredGames.length / gamesPerPage);
    }

    saveGamesToStorage();
    resetForm();

    showTab('list');
    currentPage = targetPage;
    displayGames();
}
function validateGameData(data) {
    const errors = [];
    if (!data.id || isNaN(data.id)) errors.push("معرف اللعبة يجب أن يكون رقماً");
    if (!data.title || data.title.trim() === '') errors.push("اسم اللعبة مطلوب");
    if (!data.size || data.size.trim() === '') errors.push("حجم اللعبة مطلوب");
    if (!data.rating || data.rating < 1 || data.rating > 10) errors.push("التصنيف مطلوب ويجب أن يكون بين 1 و 10");
    if (!data.story || data.story.trim() === '') errors.push("قصة اللعبة مطلوبة");
    if (!data.developer || data.developer.trim() === '') errors.push("المطور مطلوب");
    if (!data.publisher || data.publisher.trim() === '') errors.push("الناشر مطلوب");
    if (!data.systemVersion || data.systemVersion.trim() === '') errors.push("نظام الجهاز مطلوب");
    if (!data.releaseDate || data.releaseDate.trim() === '') errors.push("تاريخ الإصدار مطلوب");
    if (!data.gameCode || data.gameCode.trim() === '') errors.push("كود اللعبة مطلوب");
    if (!data.version || data.version.trim() === '') errors.push("الإصدار مطلوب");
    if (!data.password || data.password.trim() === '') errors.push("كلمة المرور مطلوبة");

    function isValidImage(imgValue) {
        if (!imgValue || imgValue.trim() === '') return true;
        const hasImageExtension = /\.(jpg|jpeg|png|webp|gif|svg)$/i.test(imgValue);
        const isBase64 = /^data:image\//i.test(imgValue);
        return hasImageExtension || isBase64;
    }

    if (data.image && !isValidImage(data.image)) {
        errors.push("صيغة الصورة الرئيسية غير صحيحة (أدخل مساراً صحيحاً ينتهي بـ .jpg أو .webp)");
    }
    if (data.sliderImage && !isValidImage(data.sliderImage)) {
        errors.push("صيغة صورة السلايدر غير صحيحة");
    }
    if (data.headerImage && !isValidImage(data.headerImage)) {
        errors.push("صيغة صورة الهيدر غير صحيحة");
    }

    return errors;
}

function showValidationErrors(errors) {
    const validationDiv = document.getElementById('validationErrors');
    const errorList = document.getElementById('errorList');
    if (!validationDiv || !errorList) {
        alert("Validation error display elements not found!");
        console.error("Validation error display elements not found!");
        return;
    }
    errorList.innerHTML = errors.map(error => `<li>${error}</li>`).join('');
    validationDiv.classList.remove('hidden');
    setTimeout(() => {
        validationDiv.classList.add('hidden');
        document.querySelectorAll('.validation-error').forEach(el => el.classList.remove('validation-error'));
        document.querySelectorAll('.error-message.show').forEach(el => el.classList.remove('show'));
    }, 5000);
}

function displayGames() {
    const gamesList = document.getElementById('gamesList');
    if (!gamesList) {
        console.error("Games list container not found!");
        return;
    }
    const filteredGames = getFilteredGames();
    const totalPages = Math.ceil(filteredGames.length / gamesPerPage);

    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    const startIndex = (currentPage - 1) * gamesPerPage;
    const endIndex = startIndex + gamesPerPage;
    const gamesToShow = filteredGames.slice(startIndex, endIndex);

    if (gamesToShow.length === 0) {
        gamesList.innerHTML = '<div class="col-span-full text-center py-8 text-gray-500">لا توجد ألعاب للعرض</div>';
        return;
    }

    gamesList.innerHTML = gamesToShow.map(game => {
        const languageSupport = getLanguageSupportInfo(game.languages);
        return `
            <div class="game-card bg-white border rounded-lg overflow-hidden">
                <img src="${game.image || 'https://picsum.photos/seed/' + game.id + '/300/200.jpg'}" alt="${game.title}" class="w-full h-48 object-cover">
                <div class="p-4">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-bold text-lg">${game.title}</h3>
                        <span class="platform-badge platform-${game.platform}">${game.platform.toUpperCase()}</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-2">${game.developer}</p>
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-sm">⭐ ${game.rating}/10</span>
                        <span class="text-sm">${game.size}</span>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="showGameDetails(${game.id})" class="flex-1 bg-indigo-500 text-white px-3 py-1 rounded text-sm hover:bg-indigo-600 transition" title="تفاصيل">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="editGame(${game.id})" class="flex-1 bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 transition" title="تعديل">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="duplicateGame(${game.id})" class="flex-1 bg-yellow-500 text-white px-3 py-1 rounded text-sm hover:bg-yellow-600 transition" title="نسخ">
                            <i class="fas fa-copy"></i>
                        </button>
                        <button onclick="deleteGame(${game.id})" class="flex-1 bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600 transition" title="حذف">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    if (totalPages > 1) {
        const paginationDiv = document.createElement('div');
        paginationDiv.className = 'col-span-full flex justify-center mt-4';
        let paginationHTML = `<button onclick="changePage(${currentPage - 1})" class="mx-1 px-3 py-1 rounded ${currentPage === 1 ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : 'bg-indigo-500 text-white hover:bg-indigo-600'}" ${currentPage === 1 ? 'disabled' : ''}><i class="fas fa-chevron-right"></i></button>`;
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                paginationHTML += `<button onclick="changePage(${i})" class="mx-1 px-3 py-1 rounded ${i === currentPage ? 'bg-indigo-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}">${i}</button>`;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                paginationHTML += '<span class="mx-1">...</span>';
            }
        }
        paginationHTML += `<button onclick="changePage(${currentPage + 1})" class="mx-1 px-3 py-1 rounded ${currentPage === totalPages ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : 'bg-indigo-500 text-white hover:bg-indigo-600'}" ${currentPage === totalPages ? 'disabled' : ''}><i class="fas fa-chevron-left"></i></button>`;
        paginationDiv.innerHTML = paginationHTML;
        gamesList.appendChild(paginationDiv);
    }
}

function changePage(page) {
    const filteredGames = getFilteredGames();
    const totalPages = Math.ceil(filteredGames.length / gamesPerPage);
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        displayGames();
    }
}

function getFilteredGames() {
    const searchInput = document.getElementById('searchInput');
    const platformFilter = document.getElementById('platformFilter');
    const genreFilter = document.getElementById('genreFilter');
    const ratingFilter = document.getElementById('ratingFilter');

    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const platform = platformFilter ? platformFilter.value : '';
    const genre = genreFilter ? genreFilter.value : '';
    const rating = ratingFilter ? ratingFilter.value : '';

    return games.filter(game => {
        const matchesSearch = game.title.toLowerCase().includes(searchTerm) || game.developer.toLowerCase().includes(searchTerm);
        const matchesPlatform = !platform || game.platform === platform;
        const matchesGenre = !genre || (game.genre && game.genre.includes(genre));
        const matchesRating = !rating || game.rating >= parseFloat(rating);
        return matchesSearch && matchesPlatform && matchesGenre && matchesRating;
    });
}

function filterGames() {
    currentPage = 1;
    displayGames();
}

function getLanguageSupportInfo(languages) {
    if (!languages || languages.length === 0) return [];
    return languages.map(lang => ({ name: lang.name, menu: lang.support.menu, subtitles: lang.support.subtitles, dubbed: lang.support.dubbed }));
}

function populateFormWithGameData(game) {
    console.log("Populating form with game data:", game);
    try {
        const el = (id) => document.getElementById(id);
        if (el('gameTitle')) el('gameTitle').value = game.title || '';
        if (el('gamePlatform')) el('gamePlatform').value = game.platform || 'ps4';
        if (el('gameSize')) el('gameSize').value = game.size || '';
        if (el('gameRating')) el('gameRating').value = game.rating || '';

        document.querySelectorAll('.genre-checkbox').forEach(cb => {
            cb.checked = game.genre && game.genre.includes(cb.value);
        });

        if (el('gameImage')) { el('gameImage').value = game.image || ''; if (game.image && el('previewImage')) el('previewImage').src = game.image; }
        if (el('gameSliderImage')) { el('gameSliderImage').value = game.sliderImage || ''; if (game.sliderImage && el('previewSliderImage')) el('previewSliderImage').src = game.sliderImage; }
        if (el('gameHeaderImage')) { el('gameHeaderImage').value = game.headerImage || ''; if (game.headerImage && el('previewHeaderImage')) el('previewHeaderImage').src = game.headerImage; }

        if (el('gameStory')) el('gameStory').value = game.story || '';
        if (el('gameDeveloper')) el('gameDeveloper').value = game.developer || '';
        if (el('gamePublisher')) el('gamePublisher').value = game.publisher || '';
        if (el('systemVersion')) el('systemVersion').value = game.systemVersion || '';
        if (el('gameReleaseDate')) el('gameReleaseDate').value = game.releaseDate || '';
        if (el('gameCode')) el('gameCode').value = game.gameCode || '';
        if (el('gameVersion')) el('gameVersion').value = game.version || '';
        if (el('gamePassword')) el('gamePassword').value = game.password || '';

        if (game.languages && game.languages.length > 0) {
            game.languages.forEach(lang => {
                const langCode = lang.code;
                const supportedCheckbox = el(langCode + 'Supported');
                if (supportedCheckbox) {
                    supportedCheckbox.checked = true;
                    const optionsDiv = el(langCode + 'SupportOptions');
                    if (optionsDiv) {
                        optionsDiv.classList.remove('hidden');
                        if (lang.support) {
                            if (el(langCode + 'Menu')) el(langCode + 'Menu').checked = lang.support.menu || false;
                            if (el(langCode + 'Subtitles')) el(langCode + 'Subtitles').checked = lang.support.subtitles || false;
                            if (el(langCode + 'Dubbed')) el(langCode + 'Dubbed').checked = lang.support.dubbed || false;
                        }
                    }
                }
            });
        }

        if (el('gameDownloadUrl')) el('gameDownloadUrl').value = game.downloadUrl || '';
        if (game.downloadUrl) {
            if (el('hasDownloadUrl')) el('hasDownloadUrl').checked = true;
            if (el('downloadUrlSection')) el('downloadUrlSection').classList.remove('hidden');
        }

        if (game.hasUpdate) {
            if (el('hasUpdate')) el('hasUpdate').checked = true;
            if (el('updateSection')) el('updateSection').classList.remove('hidden');
            if (el('updateSize')) el('updateSize').value = game.updateSize || '';
            if (el('updateUrl')) el('updateUrl').value = game.updateUrl || '';
            if (game["hasUpdate-fix"]) {
                if (el('hasUpdate-fix')) el('hasUpdate-fix').checked = true;
                if (el('update-fix-section')) el('update-fix-section').classList.remove('hidden');
                if (el('update-fixSize')) el('update-fixSize').value = game["update-fixSize"] || '';
                if (el('update-fixUrl')) el('update-fixUrl').value = game["update-fixUrl"] || '';
            }
        }

        if (game.hasDLC) {
            if (el('hasDLC')) el('hasDLC').checked = true;
            if (el('dlcSection')) el('dlcSection').classList.remove('hidden');
            if (el('dlcCount')) el('dlcCount').value = game.dlcCount || 1;
            if (el('dlcName')) el('dlcName').value = game.dlcName || '';
            if (el('dlcUrl')) el('dlcUrl').value = game.dlcUrl || '';
            if (game.hasDLC2) {
                if (el('hasDLC2')) el('hasDLC2').checked = true;
                if (el('dlc2Section')) el('dlc2Section').classList.remove('hidden');
                if (el('dlc2Name')) el('dlc2Name').value = game.dlc2Name || '';
                if (el('dlc2Url')) el('dlc2Url').value = game.dlc2Url || '';
            }
        }

        if (game.downloadParts && game.downloadParts.length > 0) {
            if (el('hasDownloadParts')) el('hasDownloadParts').checked = true;
            if (el('downloadPartsSection')) el('downloadPartsSection').classList.remove('hidden');
            loadParts('downloadPartsList', game.downloadParts);
        }
        if (game.updateParts && game.updateParts.length > 0) {
            if (el('hasUpdateParts')) el('hasUpdateParts').checked = true;
            if (el('updatePartsSection')) el('updatePartsSection').classList.remove('hidden');
            loadParts('updatePartsList', game.updateParts);
        }
        if (game.dlcParts && game.dlcParts.length > 0) {
            if (el('hasDLCParts')) el('hasDLCParts').checked = true;
            if (el('dlcPartsSection')) el('dlcPartsSection').classList.remove('hidden');
            loadParts('dlcPartsList', game.dlcParts);
        }

        if (game.hasFeatures) {
            if (el('hasFeatures')) el('hasFeatures').checked = true;
            if (el('featuresSection')) el('featuresSection').classList.remove('hidden');
            if (game.features && game.features.length > 0) {
                loadFeatures(game.features);
            }
        }

        if (typeof switchSubTab === 'function') {
            switchSubTab('basic');
        }

    } catch (error) {
        console.error("Error populating form:", error);
        showToast('حدث خطأ أثناء تحميل بيانات التعديل', 'error');
        hideLoading();
    }
}

function editGame(gameId) {
    console.log('Starting edit for game ID:', gameId);
    showLoading();
    const game = games.find(g => g.id === gameId);
    if (!game) {
        console.error('Game not found with ID:', gameId);
        hideLoading();
        showToast('لم يتم العثور على اللعبة!', 'error');
        return;
    }

    editingGameId = gameId;
    showTab('add');

    setTimeout(() => {
        resetForm(false);
        populateFormWithGameData(game);
        if (document.getElementById('gameId')) document.getElementById('gameId').value = game.id;
        hideLoading();
        showToast('تم تحميل بيانات اللعبة للتعديل!');
    }, 300);
}

function duplicateGame(gameId) {
    const game = games.find(g => g.id === gameId);
    if (!game) {
        showToast('لم يتم العثور على اللعبة!', 'error');
        return;
    }

    editingGameId = null;
    showTab('add');

    setTimeout(() => {
        resetForm(false);
        populateFormWithGameData(game);
        if (document.getElementById('gameId')) document.getElementById('gameId').value = '';
        showToast('تم نسخ بيانات اللعبة. الرجاء تعيين معرف جديد وحفظ اللعبة.');
    }, 300);
}

function deleteGame(gameId) {
    if (confirm('هل أنت متأكد من حذف هذه اللعبة؟')) {
        games = games.filter(g => g.id !== gameId);
        saveGamesToStorage();
        displayGames();
        showToast('تم حذف اللعبة بنجاح! (تذكر الضغط على تحديث الموقع لحفظ التغييرات)');
    }
}

function showGameDetails(gameId) {
    const game = games.find(g => g.id === gameId);
    if (!game) {
        showToast('لم يتم العثور على اللعبة!', 'error');
        return;
    }

    const modal = document.getElementById('gameModal');
    const modalContent = document.getElementById('modalContent');
    if (!modal || !modalContent) {
        console.error("Modal elements not found!");
        return;
    }

    const languageSupport = getLanguageSupportInfo(game.languages);

       function createPartsHTML(parts, title) {
        if (!parts || !Array.isArray(parts) || parts.length === 0) return '';
        let html = `<div class="mt-4"><h4 class="font-bold mb-2">${title}</h4><div class="bg-gray-50 p-3 rounded-lg"><div class="space-y-2">`;
        parts.forEach((part, index) => {
            // تم إزالة الحجم من العرض
            html += `<div class="bg-white p-2 rounded border"><div class="flex justify-between items-center"><div><span class="font-medium">${part.name || `جزء ${index + 1}`}</span></div><a href="${part.url || '#'}" target="_blank" class="text-blue-600 hover:underline"><i class="fas fa-download"></i> تحميل</a></div></div>`;
        });
        html += `</div></div></div>`;
        return html;
    }

    function createFeaturesHTML(features) {
        if (!features || !Array.isArray(features) || features.length === 0) return '';
        let html = `<div class="mt-4"><h4 class="font-bold mb-2">ميزات اللعبة</h4><div class="bg-gray-50 p-3 rounded-lg"><ul class="space-y-2">`;
        features.forEach((feature, index) => {
            html += `<li class="flex items-start"><i class="fas fa-check-circle text-green-500 mt-1 ml-2"></i><span>${feature}</span></li>`;
        });
        html += `</ul></div></div>`;
        return html;
    }

    modalContent.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div><img src="${game.image || 'https://picsum.photos/seed/' + game.id + '/400/300.jpg'}" alt="${game.title}" class="w-full rounded-lg mb-4"></div>
            <div>
                <h2 class="text-2xl font-bold mb-2">${game.title}</h2>
                <div class="flex items-center mb-4">
                    <span class="platform-badge platform-${game.platform}">${game.platform.toUpperCase()}</span>
                    <span class="mx-2">⭐ ${game.rating}/10</span>
                    <span>${game.size}</span>
                </div>
                <div class="mb-4"><h3 class="font-bold mb-2">المطور والناشر</h3><p>${game.developer} / ${game.publisher}</p></div>
                <div class="mb-4"><h3 class="font-bold mb-2">تاريخ الإصدار</h3><p>${game.releaseDate}</p></div>
                <div class="mb-4"><h3 class="font-bold mb-2">النوع</h3><div class="flex flex-wrap gap-2">${game.genre ? game.genre.map(g => `<span class="bg-gray-200 px-2 py-1 rounded text-sm">${g}</span>`).join('') : ''}</div></div>
                ${game.systemVersion ? `<div class="mb-4"><h3 class="font-bold mb-2">نظام الجهاز</h3><p class="bg-blue-50 px-3 py-2 rounded">${game.systemVersion}</p></div>` : ''}
            </div>
        </div>
        <div class="mt-6"><h3 class="font-bold mb-2">القصة</h3><p class="text-gray-700">${game.story}</p></div>
        <div class="mt-6"><h3 class="font-bold mb-2">دعم اللغة</h3><div class="bg-gray-50 p-4 rounded-lg"><div class="grid grid-cols-1 md:grid-cols-2 gap-4">${languageSupport.map(lang => `<div class="bg-white p-3 rounded border"><h4 class="font-medium mb-2">${lang.name}</h4><div class="space-y-1"><div class="flex items-center"><span class="ml-2">القائمة:</span>${lang.menu ? '<i class="fas fa-check-circle supported"></i>' : '<i class="fas fa-times-circle not-supported"></i>'}</div><div class="flex items-center"><span class="ml-2">الترجمة:</span>${lang.subtitles ? '<i class="fas fa-check-circle supported"></i>' : '<i class="fas fa-times-circle not-supported"></i>'}</div><div class="flex items-center"><span class="ml-2">الدبلجة:</span>${lang.dubbed ? '<i class="fas fa-check-circle supported"></i>' : '<i class="fas fa-times-circle not-supported"></i>'}</div></div></div>`).join('')}</div></div></div>
        ${createFeaturesHTML(game.features)}
        <div class="mt-6"><h3 class="font-bold mb-2">معلومات التحميل</h3><div class="bg-gray-50 p-4 rounded-lg"><div class="grid grid-cols-1 md:grid-cols-2 gap-4"><div><p class="text-sm text-gray-600">كلمة المرور</p><p class="font-medium">${game.password}</p></div><div><p class="text-sm text-gray-600">رابط التحميل</p><a href="${game.downloadUrl}" target="_blank" class="text-blue-600 hover:underline">تحميل</a></div>${game.hasUpdate ? `<div><p class="text-sm text-gray-600">حجم التحديث</p><p class="font-medium">${game.updateSize}</p></div><div><p class="text-sm text-gray-600">رابط التحديث</p><a href="${game.updateUrl}" target="_blank" class="text-blue-600 hover:underline">تحميل</a></div>${game["hasUpdate-fix"] ? `<div><p class="text-sm text-gray-600">التحديث يحتوي على إصلاح</p><p class="font-medium text-green-600">نعم</p></div><div><p class="text-sm text-gray-600">حجم تحديث الإصلاح</p><p class="font-medium">${game["update-fixSize"] || 'غير محدد'}</p></div><div><p class="text-sm text-gray-600">رابط تحديث الإصلاح</p><a href="${game["update-fixUrl"]}" target="_blank" class="text-blue-600 hover:underline">تحميل الإصلاح</a></div>` : ''}` : ''}${game.hasDLC ? `<div><p class="text-sm text-gray-600">عدد DLCs</p><p class="font-medium">${game.dlcCount}</p></div><div><p class="text-sm text-gray-600">اسم DLC</p><p class="font-medium">${game.dlcName}</p></div>` : ''}</div>${createPartsHTML(game.downloadParts, 'أجزاء التحميل')}${createPartsHTML(game.updateParts, 'أجزاء التحديث')}${createPartsHTML(game.dlcParts, 'أجزاء DLC')}</div></div>
    `;

    modal.classList.add('show');
}

function closeModal() {
    const modal = document.getElementById('gameModal');
    if (modal) modal.classList.remove('show');
}

function exportGames() {
    if (games.length === 0) { showToast('لا توجد ألعاب للتصدير!', 'warning'); return; }
    const dataStr = JSON.stringify(games, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,' + encodeURIComponent(dataStr);
    const exportFileDefaultName = `games_${new Date().toISOString().slice(0, 10)}.json`;
    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();
    showToast('تم تصدير الألعاب بنجاح!');
}

function exportByPlatform() {
    if (games.length === 0) { showToast('لا توجد ألعاب للتصدير!', 'warning'); return; }
    const platforms = ['ps4', 'ps3', 'ps5'];
    platforms.forEach(platform => {
        const platformGames = games.filter(g => g.platform === platform);
        if (platformGames.length > 0) {
            const dataStr = JSON.stringify(platformGames, null, 2);
            const dataUri = 'data:application/json;charset=utf-8,' + encodeURIComponent(dataStr);
            const fileName = platform === 'ps5' ? 'la5-games.json' : `${platform}-games.json`;
            const linkElement = document.createElement('a');
            linkElement.setAttribute('href', dataUri);
            linkElement.setAttribute('download', fileName);
            linkElement.click();
        }
    });
    showToast('تم تصدير ملفات المنصات بنجاح!');
}

function exportGamesWithOptions() {
    if (games.length === 0) { showToast('لا توجد ألعاب للتصدير!', 'warning'); return; }
    updateStatistics();
    const exportOptionsModal = document.getElementById('exportOptionsModal');
    if (exportOptionsModal) exportOptionsModal.classList.add('show');
}

function closeExportModal() {
    const exportOptionsModal = document.getElementById('exportOptionsModal');
    if (exportOptionsModal) exportOptionsModal.classList.remove('show');
}

function performExport() {
    const exportPs4 = document.getElementById('export-ps4')?.checked || false;
    const exportPs3 = document.getElementById('export-ps3')?.checked || false;
    const exportPs5 = document.getElementById('export-ps5')?.checked || false;
    const exportFormat = document.getElementById('exportFormat')?.value || 'separate';

    closeExportModal();

    if (exportFormat === 'separate') {
        if (exportPs4) { const ps4Games = games.filter(g => g.platform === 'ps4'); if (ps4Games.length > 0) downloadJson(ps4Games, 'ps4-games.json'); }
        if (exportPs3) { const ps3Games = games.filter(g => g.platform === 'ps3'); if (ps3Games.length > 0) downloadJson(ps3Games, 'ps3-games.json'); }
        if (exportPs5) { const ps5Games = games.filter(g => g.platform === 'ps5'); if (ps5Games.length > 0) downloadJson(ps5Games, 'la5-games.json'); }
        showToast('تم تصدير الملفات بنجاح!');
    } else if (exportFormat === 'combined') {
        const selectedGames = games.filter(g => (exportPs4 && g.platform === 'ps4') || (exportPs3 && g.platform === 'ps3') || (exportPs5 && g.platform === 'ps5'));
        if (selectedGames.length > 0) { downloadJson(selectedGames, `games_${new Date().toISOString().slice(0, 10)}.json`); showToast('تم تصدير الألعاب بنجاح!'); } else { showToast('لم يتم اختيار أي ألعاب للتصدير!', 'warning'); }
    } else if (exportFormat === 'package') {
        createWebsitePackageWithOptions(exportPs4, exportPs3, exportPs5);
    }
}

function downloadJson(data, filename) {
    const dataStr = JSON.stringify(data, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,' + encodeURIComponent(dataStr);
    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', filename);
    linkElement.click();
}

function createWebsitePackageWithOptions(exportPs4, exportPs3, exportPs5) {
    const packageData = { timestamp: new Date().toISOString(), files: {}, instructions: `تعليمات التثبيت: 1. استبدل الملفات في مجلد data/ بالموقع 2. قم بتحديث الصفحة الرئيسية` };
    if (exportPs4) packageData.files['ps4-games.json'] = games.filter(g => g.platform === 'ps4');
    if (exportPs3) packageData.files['ps3-games.json'] = games.filter(g => g.platform === 'ps3');
    if (exportPs5) packageData.files['la5-games.json'] = games.filter(g => g.platform === 'ps5');
    const dataStr = JSON.stringify(packageData, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,' + encodeURIComponent(dataStr);
    const exportFileDefaultName = `psgold4gamer-update-${new Date().toISOString().slice(0, 10)}.json`;
    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();
    showToast('تم إنشاء حزمة التحديث بنجاح!');
}

function createWebsitePackage() {
    if (games.length === 0) { showToast('لا توجد ألعاب لإنشاء حزمة!', 'warning'); return; }
    const packageData = {
        timestamp: new Date().toISOString(),
        files: { 'ps4-games.json': games.filter(g => g.platform === 'ps4'), 'ps3-games.json': games.filter(g => g.platform === 'ps3'), 'la5-games.json': games.filter(g => g.platform === 'ps5') },
        instructions: `تعليمات التثبيت: 1. استبدل الملفات الثلاثة في مجلد data/ بالموقع 2. ps4-games.json ← ألعاب PS4 3. ps3-games.json ← ألعاب PS3 4. la5-games.json ← ألعاب PS5 5. قم بتحديث الصفحة الرئيسية`
    };
    const dataStr = JSON.stringify(packageData, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,' + encodeURIComponent(dataStr);
    const exportFileDefaultName = `psgold4gamer-update-${new Date().toISOString().slice(0, 10)}.json`;
    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();
    showToast('تم إنشاء حزمة التحديث بنجاح!');
}

function createBackup() {
    if (games.length === 0) { showToast('لا توجد ألعاب لإنشاء نسخة احتياطية!', 'warning'); return; }
    const backupData = {
        timestamp: new Date().toISOString(), games: games, version: "1.0",
        metadata: {
            totalGames: games.length, platforms: {
                ps4: games.filter(g => g.platform === 'ps4').length, ps3: games.filter(g => g.platform === 'ps3').length, ps5: games.filter(g => g.platform === 'ps5').length,
                xbox: games.filter(g => g.platform === 'xbox').length, pc: games.filter(g => g.platform === 'pc').length, nintendo: games.filter(g => g.platform === 'nintendo').length
            }
        }
    };
    const dataStr = JSON.stringify(backupData, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,' + encodeURIComponent(dataStr);
    const exportFileDefaultName = `psgold4gamer-backup-${new Date().toISOString().slice(0, 10)}.json`;
    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();
    showToast('تم إنشاء نسخة احتياطية بنجاح!');
}

function restoreBackup(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        try {
            const backupData = JSON.parse(e.target.result);
            if (backupData.games && Array.isArray(backupData.games)) {
                if (confirm(`هل تريد استعادة نسخة احتياطية بتاريخ ${new Date(backupData.timestamp).toLocaleDateString('ar-SA')} تحتوي على ${backupData.games.length} لعبة؟ سيتم استبدال البيانات الحالية.`)) {
                    games = backupData.games;
                    saveGamesToStorage();
                    displayGames();
                    showToast('تم استعادة النسخة الاحتياطية بنجاح!');
                }
            } else { showToast('ملف النسخة الاحتياطية غير صالح!', 'error'); }
        } catch (error) { showToast('خطأ في قراءة ملف النسخة الاحتياطية!', 'error'); }
    };
    reader.readAsText(file);
    event.target.value = '';
}

function importGames(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        try {
            const data = JSON.parse(e.target.result);
            let importedGames = [];
            let sourceDescription = "";
            if (data && data.files && typeof data.files === 'object') {
                sourceDescription = "حزمة تصدير (Package)";
                if (data.files['ps4-games.json']) importedGames.push(...data.files['ps4-games.json']);
                if (data.files['ps3-games.json']) importedGames.push(...data.files['ps3-games.json']);
                if (data.files['la5-games.json']) importedGames.push(...data.files['la5-games.json']);
            } else if (data && data.games && Array.isArray(data.games)) {
                sourceDescription = "مصفوفة مغلفة (Wrapped Array)";
                importedGames = data.games;
            } else if (Array.isArray(data)) {
                sourceDescription = "مصفوفة مباشرة (Direct Array)";
                importedGames = data;
            } else {
                showToast('ملف JSON غير صالح أو هيكل غير مدعوم!', 'error');
                return;
            }
            if (importedGames.length === 0) { showToast('لم يتم العثور على ألعاب في الملف!', 'warning'); return; }
            if (confirm(`هل تريد استيراد ${importedGames.length} لعبة؟ سيتم استبدال البيانات الحالية.`)) {
                games = importedGames;
                saveGamesToStorage();
                displayGames();
                showToast(`تم استيراد ${importedGames.length} لعبة بنجاح!`);
            }
        } catch (error) { showToast('خطأ في قراءة الملف! قد يكون الملف تالفًا.', 'error'); }
    };
    reader.readAsText(file);
    event.target.value = '';
}

function clearAllData() {
    if (confirm('هل أنت متأكد من مسح جميع البيانات؟ لا يمكن التراجع عن هذا الإجراء!')) {
        games = [];
        saveGamesToStorage();
        displayGames();
        showToast('تم مسح جميع البيانات!');
    }
}

function updateGamesCount() {
    const gamesCountElement = document.getElementById('gamesCount');
    if (gamesCountElement) gamesCountElement.textContent = games.length;
}

function updateLastUpdateTime() {
    const lastUpdateElement = document.getElementById('lastUpdate');
    if (lastUpdateElement) {
        const now = new Date();
        const timeString = now.toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit' });
        lastUpdateElement.textContent = timeString;
    }
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    if (!toast || !toastMessage) {
        console.error("Toast elements not found!");
        alert(message);
        return;
    }
    toast.className = 'toast';
    if (type === 'error') toast.classList.add('error');
    if (type === 'warning') toast.classList.add('warning');
    if (type === 'info') toast.classList.add('info');
    toastMessage.textContent = message;
    toast.classList.add('show');
    setTimeout(() => { toast.classList.remove('show'); }, 3000);
}

function updateDownloadsUI() {
    const downloadUrlSection = document.getElementById('downloadUrlSection');
    if (downloadUrlSection) downloadUrlSection.classList.toggle('hidden', !document.getElementById('hasDownloadUrl').checked);
    const updateSection = document.getElementById('updateSection');
    if (updateSection) updateSection.classList.toggle('hidden', !document.getElementById('hasUpdate').checked);
    const updateFixSection = document.getElementById('update-fix-section');
    if (updateFixSection) updateFixSection.classList.toggle('hidden', !document.getElementById('hasUpdate-fix').checked);
    const dlcSection = document.getElementById('dlcSection');
    if (dlcSection) dlcSection.classList.toggle('hidden', !document.getElementById('hasDLC').checked);
    const dlc2Section = document.getElementById('dlc2Section');
    if (dlc2Section) dlc2Section.classList.toggle('hidden', !document.getElementById('hasDLC2').checked);
    const updatePartsSection = document.getElementById('updatePartsSection');
    if (updatePartsSection) updatePartsSection.classList.toggle('hidden', !document.getElementById('hasUpdateParts').checked);
    const dlcPartsSection = document.getElementById('dlcPartsSection');
    if (dlcPartsSection) dlcPartsSection.classList.toggle('hidden', !document.getElementById('hasDLCParts').checked);
    const downloadPartsSection = document.getElementById('downloadPartsSection');
    if (downloadPartsSection) downloadPartsSection.classList.toggle('hidden', !document.getElementById('hasDownloadParts').checked);
    const featuresSection = document.getElementById('featuresSection');
    if (featuresSection) featuresSection.classList.toggle('hidden', !document.getElementById('hasFeatures').checked);
}

function loadExample() {
    const exampleGame = {
        id: 999, title: "The Last of Us Part I", platform: "ps4", genre: ["action", "adventure", "horror", "arabic"], size: "79 GB",
        image: "https://i.postimg.cc/3J8L3X0W/The-Last-of-Us-Part-I.jpg", sliderImage: "https://i.postimg.cc/3J8L3X0W/The-Last-of-Us-Part-I.jpg", headerImage: "https://i.postimg.cc/3J8L3X0W/The-Last-of-Us-Part-I.jpg",
        systemVersion: "9.00", gameCode: "CUSA31219", story: "في عالم مدمر، يصحب جويل إيلي في رحلة خطرة عبر الولايات المتحدة.",
        developer: "Naughty Dog", publisher: "Sony Interactive Entertainment", releaseDate: "2 سبتمبر 2022", rating: 9.8,
        password: "THE-LAST-OF-US-4GAMER", downloadUrl: "https://example.com/download", hasDownloadUrl: true, hasUpdate: false, hasDLC: false, version: "1.0",
        languages: [{ name: "العربية", code: "arabic", support: { menu: true, subtitles: true, dubbed: false } }, { name: "الإنجليزية", code: "english", support: { menu: true, subtitles: true, dubbed: true } }],
        downloadParts: [], updateParts: [], dlcParts: [],
        hasFeatures: true, features: ["قصة مؤثرة ومشوقة في عالم ما بعد الكارثة", "عالم مفتوح غني بالتفاصيل والأسرار", "معارك استراتيجية ومواجهات عنيفة", "تطوير الشخصيات وعلاقاتها", "رسوميات واقعية ومؤثرات بصرية مذهلة"]
    };
    populateFormWithGameData(exampleGame);
    showToast('تم تحميل مثال لعبة!');
}

function resetForm(clearAll = true) {
    const gameForm = document.getElementById('gameForm');
    if (!gameForm) {
        console.error("Game form not found, cannot reset.");
        return;
    }
    if (clearAll) {
        gameForm.reset();
        editingGameId = null;
    }
    const sectionsToHide = ['downloadUrlSection', 'updateSection', 'update-fix-section', 'dlcSection', 'dlc2Section', 'downloadPartsSection', 'updatePartsSection', 'dlcPartsSection', 'featuresSection'];
    sectionsToHide.forEach(id => { const el = document.getElementById(id); if (el) el.classList.add('hidden'); });
    document.querySelectorAll('.language-main-checkbox').forEach(cb => cb.checked = false);
    document.querySelectorAll('.language-checkbox').forEach(cb => cb.checked = false);
    document.querySelectorAll('[id$="SupportOptions"]').forEach(div => div.classList.add('hidden'));
    const listsToClear = ['downloadPartsList', 'updatePartsList', 'dlcPartsList', 'featuresList'];
    listsToClear.forEach(id => { const el = document.getElementById(id); if (el) el.innerHTML = ''; });
    const previewImage = document.getElementById('previewImage');
    const previewSliderImage = document.getElementById('previewSliderImage');
    const previewHeaderImage = document.getElementById('previewHeaderImage');
    if (previewImage) previewImage.src = 'https://picsum.photos/seed/card/200/280.jpg';
    if (previewSliderImage) previewSliderImage.src = 'https://picsum.photos/seed/slider/300/150.jpg';
    if (previewHeaderImage) previewHeaderImage.src = 'https://picsum.photos/seed/header/300/150.jpg';
    const validationErrors = document.getElementById('validationErrors');
    if (validationErrors) validationErrors.classList.add('hidden');
    document.querySelectorAll('.validation-error').forEach(el => el.classList.remove('validation-error'));
    document.querySelectorAll('.error-message.show').forEach(el => el.classList.remove('show'));
}

// ========================================
// نظام الوضع الليلي
// ========================================
function initializeDarkMode() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateDarkModeIcon(savedTheme);
}

function toggleDarkMode() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    document.body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateDarkModeIcon(newTheme);
    const message = newTheme === 'dark' ? 'تم تفعيل الوضع الليلي' : 'تم تفعيل الوضع النهاري';
    showToast(message, 'info');
    updateComponentsForTheme(newTheme);
}

function updateDarkModeIcon(theme) {
    const sunIcon = document.querySelector('.sun-icon');
    const moonIcon = document.querySelector('.moon-icon');
    if (sunIcon && moonIcon) {
        if (theme === 'dark') { sunIcon.style.opacity = '0'; moonIcon.style.opacity = '1'; } else { sunIcon.style.opacity = '1'; moonIcon.style.opacity = '0'; }
    }
}

function updateComponentsForTheme(theme) {
    const isDark = theme === 'dark';
    updateChartsTheme(isDark);
    updateExternalComponents(isDark);
}

function updateChartsTheme(isDark) { }

function updateExternalComponents(isDark) { }

function detectSystemTheme() {
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) { return 'dark'; }
    return 'light';
}

function applySystemTheme() {
    const systemTheme = detectSystemTheme();
    document.documentElement.setAttribute('data-theme', systemTheme);
    localStorage.setItem('theme', systemTheme);
    updateDarkModeIcon(systemTheme);
}

function setupSystemThemeListener() {
    if (window.matchMedia) {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        mediaQuery.addListener((e) => {
            if (localStorage.getItem('theme') === 'system') {
                const newTheme = e.matches ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', newTheme);
                updateDarkModeIcon(newTheme);
                updateComponentsForTheme(newTheme);
            }
        });
    }
}

function setupDarkModeKeyboardShortcuts() {
    document.addEventListener('keydown', function (e) {
        if (e.ctrlKey && e.shiftKey && e.key === 'D') { e.preventDefault(); toggleDarkMode(); }
    });
}

function updateTimeBasedTheme() {
    const hour = new Date().getHours();
    const isNightTime = hour >= 20 || hour < 6;
    if (localStorage.getItem('theme') === 'auto') {
        const newTheme = isNightTime ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', newTheme);
        updateDarkModeIcon(newTheme);
        updateComponentsForTheme(newTheme);
    }
}

function setupTimeBasedTheme() {
    updateTimeBasedTheme();
    setInterval(updateTimeBasedTheme, 60000);
}

function showDarkModeSettings() {
    const existingModal = document.querySelector('.dark-mode-settings-modal');
    if (existingModal) { existingModal.remove(); }
    const modal = document.createElement('div');
    modal.className = 'modal dark-mode-settings-modal show';
    modal.innerHTML = `
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md p-6" style="background-color: var(--card-bg); color: var(--text-primary);">
            <h2 class="text-xl font-bold mb-4">إعدادات الوضع الليلي</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2" style="color: var(--text-primary);">وضع السمة</label>
                    <select id="themeMode" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md" style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);">
                        <option value="light">وضع نهاري دائم</option>
                        <option value="dark">وضع ليلي دائم</option>
                        <option value="system">تلقائي حسب النظام</option>
                        <option value="auto">تلقائي حسب الوقت</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2" style="color: var(--text-primary);">شدة الإضاءة في الوضع الليلي</label>
                    <input type="range" id="darknessLevel" min="0" max="100" value="100" class="w-full" style="accent-color: var(--button-primary);">
                    <div class="flex justify-between text-sm" style="color: var(--text-secondary);"><span>فاتح</span><span>معتدل</span><span>داكن</span></div>
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" id="reduceAnimations" class="ml-2 rounded">
                        <span style="color: var(--text-primary);">تقليل الحركات في الوضع الليلي</span>
                    </label>
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" id="blueLightFilter" class="ml-2 rounded">
                        <span style="color: var(--text-primary);">فلتر الضوء الأزرق</span>
                    </label>
                </div>
            </div>
            <div class="flex justify-end space-x-reverse space-x-2 mt-6">
                <button onclick="this.closest('.dark-mode-settings-modal').remove()" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 transition">إلغاء</button>
                <button onclick="saveDarkModeSettings()" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">حفظ</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    const currentMode = localStorage.getItem('theme') || 'light';
    document.getElementById('themeMode').value = currentMode;
    const darknessLevel = localStorage.getItem('darknessLevel') || '100';
    document.getElementById('darknessLevel').value = darknessLevel;
    const reduceAnimations = localStorage.getItem('reduceAnimations') === 'true';
    document.getElementById('reduceAnimations').checked = reduceAnimations;
    const blueLightFilter = localStorage.getItem('blueLightFilter') === 'true';
    document.getElementById('blueLightFilter').checked = blueLightFilter;
}

function saveDarkModeSettings() {
    const themeMode = document.getElementById('themeMode').value;
    const darknessLevel = document.getElementById('darknessLevel').value;
    const reduceAnimations = document.getElementById('reduceAnimations').checked;
    const blueLightFilter = document.getElementById('blueLightFilter').checked;
    localStorage.setItem('theme', themeMode);
    localStorage.setItem('darknessLevel', darknessLevel);
    localStorage.setItem('reduceAnimations', reduceAnimations);
    localStorage.setItem('blueLightFilter', blueLightFilter);
    applyDarkModeSettings();
    document.querySelector('.dark-mode-settings-modal').remove();
    showToast('تم حفظ إعدادات الوضع الليلي', 'success');
}

function applyDarkModeSettings() {
    const themeMode = localStorage.getItem('theme') || 'light';
    const darknessLevel = localStorage.getItem('darknessLevel') || '100';
    const reduceAnimations = localStorage.getItem('reduceAnimations') === 'true';
    const blueLightFilter = localStorage.getItem('blueLightFilter') === 'true';
    let targetTheme = themeMode;
    if (themeMode === 'system') { targetTheme = detectSystemTheme(); } else if (themeMode === 'auto') {
        const hour = new Date().getHours();
        targetTheme = (hour >= 20 || hour < 6) ? 'dark' : 'light';
    }
    document.documentElement.setAttribute('data-theme', targetTheme);
    updateDarkModeIcon(targetTheme);
    if (targetTheme === 'dark') {
        const darkness = 1 - (darknessLevel / 100) * 0.5;
        const grayValue = Math.round(26 * darkness);
        document.documentElement.style.setProperty('--bg-primary', `rgb(${grayValue}, ${grayValue}, ${grayValue})`);
    } else {
        document.documentElement.style.removeProperty('--bg-primary');
    }
    if (reduceAnimations) {
        document.documentElement.style.setProperty('--animation-duration', '0.1s');
        document.body.classList.add('reduce-animations');
    } else {
        document.documentElement.style.removeProperty('--animation-duration');
        document.body.classList.remove('reduce-animations');
    }
    if (blueLightFilter) { document.body.classList.add('blue-light-filter'); } else { document.body.classList.remove('blue-light-filter'); }
    updateComponentsForTheme(targetTheme);
}

const style = document.createElement('style');
style.textContent = `
    .blue-light-filter { filter: sepia(20%) saturate(140%) hue-rotate(330deg); }
    .reduce-animations * { animation-duration: 0.1s !important; transition-duration: 0.1s !important; }
`;
document.head.appendChild(style);

document.addEventListener('keydown', function (e) {
    if (e.ctrlKey && e.shiftKey && e.key === 'D') { e.preventDefault(); toggleDarkMode(); }
});

// ========================================
// مزامنة البيانات مع السيرفر
// ========================================

function syncToServer() {
    if (games.length === 0) {
        showToast('لا توجد ألعاب للحفظ!', 'warning');
        return;
    }

    if (!confirm('هل أنت متأكد من تحديث ملفات الموقع؟ سيتم استبدال الملفات القديمة بالتعديلات الحالية.')) {
        return;
    }

    showLoading();

    fetch('api_save_games.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(games)
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            hideLoading();
            if (data.success) {
                showToast(data.message, 'success');
            } else {
                showToast('حدث خطأ: ' + (data.error || 'غير معروف'), 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showToast('فشل الاتصال بالسيرفر. تأكد من تسجيل الدخول ومسار الملف.', 'error');
        });
}