<?php
// استدعاء ملف الإعدادات
require_once 'config.php';

// التحقق من تسجيل الدخول - إذا لم يكن مسجلاً، حوله لصفحة الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// إذا وصلنا هنا، فالمدير مسجل الدخول
$admin_user = $_SESSION['admin_user'] ?? 'المدير';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة الألعاب - PSGOLD4GAMER</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>

<body>
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Debug Info -->
    <div id="debugInfo" class="debug-info"></div>

    <!-- Login Page -->
    <div id="loginPage" class="login-container">
        <div class="login-card">
            <div class="text-center mb-8">
                <i class="fas fa-gamepad text-5xl text-purple-600 mb-4"></i>
                <h1 class="text-2xl font-bold text-gray-800">تسجيل الدخول</h1>
                <p class="text-gray-600 mt-2">نظام إدارة الألعاب - PSGOLD4GAMER</p>
            </div>

            <form id="loginForm">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                        اسم المستخدم
                    </label>
                    <div class="relative">
                        <span class="absolute right-3 top-3 text-gray-400">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" id="username"
                            class="w-full pr-10 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="أدخل اسم المستخدم" required>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                        كلمة المرور
                    </label>
                    <div class="relative">
                        <span class="absolute right-3 top-3 text-gray-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" id="password"
                            class="w-full pr-10 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="أدخل كلمة المرور" required>
                        <button type="button" onclick="togglePassword()"
                            class="absolute left-3 top-3 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye" id="passwordToggle"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-bold py-2 px-4 rounded-lg hover:from-purple-700 hover:to-indigo-700 transition duration-200">
                    <i class="fas fa-sign-in-alt ml-2"></i>
                    تسجيل الدخول
                </button>
            </form>

            <div class="mt-6 text-center text-sm text-gray-600">
                <p>معلومات تسجيل الدخول </p>
                <p class="font-mono mt-1"> </p>
            </div>
        </div>
    </div>

    <!-- Main Application -->
    <div id="mainApp" style="display: none;">
        <!-- Header -->
        <header class="bg-gradient-to-r from-purple-700 to-indigo-700 text-white shadow-lg">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <i class="fas fa-gamepad text-3xl ml-3"></i>
                        <div>
                            <h1 class="text-2xl font-bold">نظام إدارة الألعاب</h1>
                            <p class="text-sm opacity-90">إدارة محتوى موقع PSGOLD4GAMER</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-reverse space-x-4">
                        <div class="text-left">
                            <p class="text-sm">عدد الألعاب: <span id="gamesCount" class="font-bold">0</span></p>
                            <p class="text-xs opacity-75">آخر تحديث: <span id="lastUpdate">لم يتم الحفظ بعد</span></p>
                        </div>
                        <div class="relative">
                            <img src="https://picsum.photos/seed/admin/40/40.jpg" alt="Admin"
                                class="rounded-full border-2 border-white cursor-pointer" onclick="toggleUserMenu()">
                            <div id="userMenu"
                                class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden z-50">
                                <div class="px-4 py-2 border-b">
                                    <p class="text-sm font-semibold text-gray-800">مرحباً بك</p>
                                    <p class="text-xs text-gray-600" id="currentUser">admin</p>
                                </div>
                                <button onclick="showImageManager()"
                                    class="w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-images ml-2"></i>إدارة الصور
                                </button>
                                <button onclick="changePassword()"
                                    class="w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-key ml-2"></i>تغيير كلمة المرور
                                </button>
                                <hr class="my-2">
                                <button onclick="logout()"
                                    class="w-full text-right px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt ml-2"></i>تسجيل الخروج
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Dark Mode Toggle -->
            <div class="dark-mode-toggle" onclick="toggleDarkMode()" title="تبديل الوضع الليلي">
                <div class="toggle-switch">
                    <i class="fas fa-sun toggle-icon sun-icon"></i>
                    <i class="fas fa-moon toggle-icon moon-icon"></i>
                </div>
            </div>
            <button onclick="showDarkModeSettings()" class="w-full text-right px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <i class="fas fa-moon ml-2"></i>إعدادات الوضع الليلي
            </button>
        </header>

        <!-- Main Content -->
        <main class="container mx-auto px-4 py-8">
            <!-- Statistics Section -->
            <div class="stats-card mb-6">
                <h2 class="text-xl font-bold mb-4"><i class="fas fa-chart-bar ml-2"></i>إحصائيات سريعة</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold" id="ps4Count">0</div>
                        <div class="text-sm opacity-90">ألعاب PS4</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold" id="ps3Count">0</div>
                        <div class="text-sm opacity-90">ألعاب PS3</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold" id="ps5Count">0</div>
                        <div class="text-sm opacity-90">ألعاب PS5</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold" id="totalSize">0 GB</div>
                        <div class="text-sm opacity-90">إجمالي الحجم</div>
                    </div>
                </div>
            </div>

            <!-- Export Section -->
            <div class="export-section">
                <h2 class="text-xl font-bold mb-4"><i class="fas fa-rocket ml-2"></i>تصدير البيانات للموقع</h2>
                <p class="mb-4">قم بتصدير بيانات الألعاب لتحديث ملفات الموقع</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="export-card">
                        <h3 class="font-bold mb-2">تصدير حسب المنصة</h3>
                        <p class="text-sm mb-3">تصدير الملفات الثلاثة: ps4-games.json, ps3-games.json, la5-games.json
                        </p>
                        <button onclick="exportByPlatform()"
                            class="bg-white text-purple-700 px-4 py-2 rounded-md hover:bg-gray-100 transition w-full">
                            <i class="fas fa-layer-group ml-2"></i>تصدير الملفات
                        </button>
                    </div>

                    <div class="export-card">
                        <h3 class="font-bold mb-2">تصدير متقدم</h3>
                        <p class="text-sm mb-3">خيارات متقدمة للتصدير مع تحديد المنصات</p>
                        <button onclick="exportGamesWithOptions()"
                            class="bg-white text-purple-700 px-4 py-2 rounded-md hover:bg-gray-100 transition w-full">
                            <i class="fas fa-cog ml-2"></i>خيارات متقدمة
                        </button>
                    </div>

                    <div class="export-card">
                        <h3 class="font-bold mb-2">إنشاء حزمة تحديث</h3>
                        <p class="text-sm mb-3">إنشاء حزمة جاهزة تحتوي على جميع الملفات</p>
                        <button onclick="createWebsitePackage()"
                            class="bg-white text-purple-700 px-4 py-2 rounded-md hover:bg-gray-100 transition w-full">
                            <i class="fas fa-box ml-2"></i>إنشاء حزمة
                        </button>
                    </div>

                    <div class="export-card">
                        <h3 class="font-bold mb-2">النسخ الاحتياطي</h3>
                        <p class="text-sm mb-3">إنشاء نسخة احتياطية من جميع البيانات</p>
                        <button onclick="createBackup()"
                            class="bg-white text-purple-700 px-4 py-2 rounded-md hover:bg-gray-100 transition w-full">
                            <i class="fas fa-save ml-2"></i>نسخ احتياطي
                        </button>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-white bg-opacity-20 rounded-lg">
                    <p class="text-sm"><i class="fas fa-info-circle ml-2"></i><strong>ملاحظة:</strong> سيتم تصدير 3
                        ملفات فقط تتوافق مع هيكل موقعك الحالي</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                <div class="flex flex-wrap gap-2 justify-between items-center">
                    <div class="flex flex-wrap gap-2">
                        <button onclick="showTab('add')"
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition">
                            <i class="fas fa-plus ml-2"></i>إضافة لعبة جديدة
                        </button>
                        <button onclick="showTab('list')"
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                            <i class="fas fa-list ml-2"></i>قائمة الألعاب
                        </button>
                        <button onclick="document.getElementById('importFile').click()"
                            class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition">
                            <i class="fas fa-upload ml-2"></i>استيراد JSON
                        </button>
                        <input type="file" id="importFile" accept=".json" style="display: none;"
                            onchange="importGames(event)">
                        <button onclick="document.getElementById('restoreFile').click()"
                            class="bg-orange-600 text-white px-4 py-2 rounded-md hover:bg-orange-700 transition">
                            <i class="fas fa-history ml-2"></i>استعادة نسخة احتياطية
                        </button>
                        <input type="file" id="restoreFile" accept=".json" style="display: none;"
                            onchange="restoreBackup(event)">
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button onclick="exportGames()"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">
                            <i class="fas fa-download ml-2"></i>تصدير JSON
                        </button>
                        <button onclick="clearAllData()"
                            class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition">
                            <i class="fas fa-trash ml-2"></i>مسح الكل
                        </button>
                    </div>
                </div>
            </div>

            <!-- Add Game Tab -->
            <div id="addTab" class="tab-content active">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold mb-6 text-indigo-700">إضافة لعبة جديدة</h2>

                    <!-- Validation Errors -->
                    <div id="validationErrors"
                        class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                        <h3 class="font-bold mb-2">الرجاء تصحيح الأخطاء التالية:</h3>
                        <ul id="errorList" class="list-disc list-inside"></ul>
                    </div>

                    <!-- Tabs -->
                    <div class="border-b border-gray-200 mb-6">
                        <nav class="-mb-px flex space-x-reverse space-x-8 overflow-x-auto">
                            <button
                                class="sub-tab-btn py-2 px-1 border-b-2 border-indigo-500 font-medium text-sm text-indigo-600 whitespace-nowrap"
                                data-tab="basic">
                                <i class="fas fa-info-circle ml-2"></i>المعلومات الأساسية
                            </button>
                            <button
                                class="sub-tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap"
                                data-tab="media">
                                <i class="fas fa-images ml-2"></i>الوسائط
                            </button>
                            <button
                                class="sub-tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap"
                                data-tab="details">
                                <i class="fas fa-list-alt ml-2"></i>تفاصيل اللعبة
                            </button>
                            <button
                                class="sub-tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap"
                                data-tab="languages">
                                <i class="fas fa-language ml-2"></i>دعم اللغة
                            </button>
                            <button
                                class="sub-tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap"
                                data-tab="downloads">
                                <i class="fas fa-download ml-2"></i>التحميلات
                            </button>
                            <button
                                class="sub-tab-btn py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap"
                                data-tab="parts">
                                <i class="fas fa-layer-group ml-2"></i>الأجزاء
                            </button>
                        </nav>
                    </div>

                    <!-- Form -->
                    <form id="gameForm">
                        <!-- Basic Information Tab -->
                        <div id="basic" class="sub-tab-content active">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-gray-700 font-medium mb-2">معرف اللعبة (ID)</label>
                                    <input type="number" id="gameId"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        required>
                                    <span class="error-message" id="gameIdError">معرف اللعبة مطلوب ويجب أن يكون
                                        رقماً</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 font-medium mb-2">اسم اللعبة</label>
                                    <input type="text" id="gameTitle"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        required>
                                    <span class="error-message" id="gameTitleError">اسم اللعبة مطلوب</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 font-medium mb-2">المنصة</label>
                                    <select id="gamePlatform"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="ps4">PlayStation 4</option>
                                        <option value="ps5">PlayStation 5</option>
                                        <option value="ps3">PlayStation 3</option>
                                        <option value="xbox">Xbox</option>
                                        <option value="pc">PC</option>
                                        <option value="nintendo">Nintendo Switch</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-700 font-medium mb-2">حجم اللعبة</label>
                                    <input type="text" id="gameSize" placeholder="مثال: 100 GB"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        required>
                                    <span class="error-message" id="gameSizeError">حجم اللعبة مطلوب</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 font-medium mb-2">التصنيف (1-10)</label>
                                    <input type="number" id="gameRating" min="1" max="10" step="0.1"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        required>
                                    <span class="error-message" id="gameRatingError">التصنيف مطلوب ويجب أن يكون بين 1 و
                                        10</span>
                                </div>
                                <div class="md:col-span-2 lg:col-span-3">
                                    <label class="block text-gray-700 font-medium mb-2">النوع</label>
                                    <div class="flex flex-wrap gap-2">
                                        <label
                                            class="inline-flex items-center bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200 transition">
                                            <input type="checkbox" value="action"
                                                class="genre-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                            <span>أكشن</span>
                                        </label>
                                        <label
                                            class="inline-flex items-center bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200 transition">
                                            <input type="checkbox" value="adventure"
                                                class="genre-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                            <span>مغامرة</span>
                                        </label>
                                        <label
                                            class="inline-flex items-center bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200 transition">
                                            <input type="checkbox" value="rpg"
                                                class="genre-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                            <span>تقمص أدوار</span>
                                        </label>
                                        <label
                                            class="inline-flex items-center bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200 transition">
                                            <input type="checkbox" value="open-world"
                                                class="genre-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                            <span>عالم مفتوح</span>
                                        </label>
                                        <label
                                            class="inline-flex items-center bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200 transition">
                                            <input type="checkbox" value="hack-and-slash"
                                                class="genre-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                            <span>ضرب وقطع</span>
                                        </label>
                                        <label
                                            class="inline-flex items-center bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200 transition">
                                            <input type="checkbox" value="souls"
                                                class="genre-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                            <span>سولز</span>
                                        </label>
                                        <label
                                            class="inline-flex items-center bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200 transition">
                                            <input type="checkbox" value="platformer"
                                                class="genre-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                            <span>منصات</span>
                                        </label>
                                        <label
                                            class="inline-flex items-center bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200 transition">
                                            <input type="checkbox" value="shooter"
                                                class="genre-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                            <span>تصويب</span>
                                        </label>
                                        <label
                                            class="inline-flex items-center bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200 transition">
                                            <input type="checkbox" value="horror"
                                                class="genre-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                            <span>رعب</span>
                                        </label>
                                        <label
                                            class="inline-flex items-center bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200 transition">
                                            <input type="checkbox" value="survival"
                                                class="genre-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                            <span>بقاء</span>
                                        </label>
                                        <label
                                            class="inline-flex items-center bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200 transition">
                                            <input type="checkbox" value="racing"
                                                class="genre-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                            <span>سباقات</span>
                                        </label>
                                        <label
                                            class="inline-flex items-center bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200 transition">
                                            <input type="checkbox" value="sports"
                                                class="genre-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                            <span>رياضة</span>
                                        </label>
                                        <label
                                            class="inline-flex items-center bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200 transition">
                                            <input type="checkbox" value="fighting"
                                                class="genre-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                            <span>قتال</span>
                                        </label>
                                        <label
                                            class="inline-flex items-center bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200 transition">
                                            <input type="checkbox" value="stealth"
                                                class="genre-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                            <span>تسلل</span>
                                        </label>
                                        <label
                                            class="inline-flex items-center bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200 transition">
                                            <input type="checkbox" value="strategy"
                                                class="genre-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                            <span>استراتيجية</span>
                                        </label>
                                        <label
                                            class="inline-flex items-center bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200 transition">
                                            <input type="checkbox" value="simulation"
                                                class="genre-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                            <span>محاكاة</span>
                                        </label>
                                        <label
                                            class="inline-flex items-center bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200 transition">
                                            <input type="checkbox" value="puzzle"
                                                class="genre-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                            <span>ألغاز</span>
                                        </label>
                                        <label
                                            class="inline-flex items-center bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200 transition">
                                            <input type="checkbox" value="metroidvania"
                                                class="genre-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                            <span>ميترويدفانيا</span>
                                        </label>
                                        <label
                                            class="inline-flex items-center bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200 transition">
                                            <input type="checkbox" value="arabic"
                                                class="genre-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                            <span>بالعربية</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Media Tab -->
                        <div id="media" class="sub-tab-content">
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">صورة البطاقة</label>
                                        <div class="flex gap-2">
                                            <input type="url" id="gameImage"
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                placeholder="أو أدخل الرابط">
                                            <button type="button" onclick="selectImage('gameImage')"
                                                class="bg-indigo-600 text-white px-3 py-2 rounded-md hover:bg-indigo-700 transition">
                                                <i class="fas fa-upload"></i>
                                            </button>
                                        </div>
                                        <span class="error-message" id="gameImageError">رابط الصورة غير صحيح</span>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">صورة الشريحة</label>
                                        <div class="flex gap-2">
                                            <input type="url" id="gameSliderImage"
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                placeholder="أو أدخل الرابط">
                                            <button type="button" onclick="selectImage('gameSliderImage')"
                                                class="bg-indigo-600 text-white px-3 py-2 rounded-md hover:bg-indigo-700 transition">
                                                <i class="fas fa-upload"></i>
                                            </button>
                                        </div>
                                        <span class="error-message" id="gameSliderImageError">رابط الصورة غير
                                            صحيح</span>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">صورة الرأسية</label>
                                        <div class="flex gap-2">
                                            <input type="url" id="gameHeaderImage"
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                placeholder="أو أدخل الرابط">
                                            <button type="button" onclick="selectImage('gameHeaderImage')"
                                                class="bg-indigo-600 text-white px-3 py-2 rounded-md hover:bg-indigo-700 transition">
                                                <i class="fas fa-upload"></i>
                                            </button>
                                        </div>
                                        <span class="error-message" id="gameHeaderImageError">رابط الصورة غير
                                            صحيح</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-gray-700 font-medium mb-2">معاينة الصور</label>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="border rounded-lg p-2">
                                            <p class="text-sm text-gray-600 mb-2">صورة البطاقة</p>
                                            <img id="previewImage" src="https://picsum.photos/seed/card/200/280.jpg"
                                                alt="Card Image" class="w-full h-48 object-cover rounded">
                                        </div>
                                        <div class="border rounded-lg p-2">
                                            <p class="text-sm text-gray-600 mb-2">صورة الشريحة</p>
                                            <img id="previewSliderImage"
                                                src="https://picsum.photos/seed/slider/300/150.jpg" alt="Slider Image"
                                                class="w-full h-48 object-cover rounded">
                                        </div>
                                        <div class="border rounded-lg p-2">
                                            <p class="text-sm text-gray-600 mb-2">صورة الرأسية</p>
                                            <img id="previewHeaderImage"
                                                src="https://picsum.photos/seed/header/300/150.jpg" alt="Header Image"
                                                class="w-full h-48 object-cover rounded">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- قسم الميزات -->
                        <div class="mb-4">
                            <div class="flex items-center mb-2">
                                <input type="checkbox" id="hasFeatures" class="ml-2 rounded">
                                <label for="hasFeatures" class="font-medium">الميزات</label>
                            </div>
                            <div id="featuresSection" class="hidden">
                                <div class="flex justify-between items-center mb-2">
                                    <h3 class="text-sm font-medium">ميزات اللعبة</h3>
                                    <button type="button" onclick="addFeature()" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600 transition">
                                        <i class="fas fa-plus"></i> إضافة ميزة
                                    </button>
                                </div>
                                <div id="featuresList" class="space-y-2">
                                    <!-- سيتم إضافة الميزات هنا ديناميكيًا -->
                                </div>
                            </div>
                        </div>
                        <!-- Game Details Tab -->
                        <div id="details" class="sub-tab-content">
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-gray-700 font-medium mb-2">قصة اللعبة</label>
                                    <textarea id="gameStory" rows="4"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        required></textarea>
                                    <span class="error-message" id="gameStoryError">قصة اللعبة مطلوبة</span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">المطور</label>
                                        <input type="text" id="gameDeveloper"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            list="developerList" required>
                                        <datalist id="developerList"></datalist>
                                        <span class="error-message" id="gameDeveloperError">المطور مطلوب</span>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">الناشر</label>
                                        <input type="text" id="gamePublisher"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            list="publisherList" required>
                                        <datalist id="publisherList"></datalist>
                                        <span class="error-message" id="gamePublisherError">الناشر مطلوب</span>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">نظام الجهاز</label>
                                        <input type="text" id="systemVersion" placeholder="مثال: 5.05/11.00/6.72"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            required>
                                        <span class="error-message" id="systemVersionError">نظام الجهاز مطلوب</span>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">تاريخ الإصدار</label>
                                        <input type="text" id="gameReleaseDate" placeholder="مثال: 9 نوفمبر 2022"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            required>
                                        <span class="error-message" id="gameReleaseDateError">تاريخ الإصدار مطلوب</span>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">كود اللعبة</label>
                                        <input type="text" id="gameCode" placeholder="مثال: CUSA34390"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            required>
                                        <span class="error-message" id="gameCodeError">كود اللعبة مطلوب</span>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">الإصدار</label>
                                        <input type="text" id="gameVersion" placeholder="مثال: 1.05"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            required>
                                        <span class="error-message" id="gameVersionError">الإصدار مطلوب</span>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 font-medium mb-2">كلمة المرور</label>
                                        <input type="text" id="gamePassword"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            required>
                                        <span class="error-message" id="gamePasswordError">كلمة المرور مطلوبة</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Languages Tab -->
                        <div id="languages" class="sub-tab-content">
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-gray-700 font-medium mb-2">اللغات المدعومة</label>
                                    <div class="space-y-4">
                                        <div class="language-support-card bg-blue-50 p-4 rounded-lg">
                                            <div class="flex items-center mb-3">
                                                <input type="checkbox" id="arabicSupported"
                                                    class="language-main-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                                <label for="arabicSupported"
                                                    class="font-medium text-gray-700">العربية</label>
                                            </div>
                                            <div id="arabicSupportOptions" class="space-y-2 hidden">
                                                <div class="flex items-center">
                                                    <input type="checkbox" id="arabicMenu"
                                                        class="language-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2"
                                                        data-lang="arabic" data-type="menu">
                                                    <label for="arabicMenu">القائمة بالعربية</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input type="checkbox" id="arabicSubtitles"
                                                        class="language-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2"
                                                        data-lang="arabic" data-type="subtitles">
                                                    <label for="arabicSubtitles">ترجمة بالعربية</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input type="checkbox" id="arabicDubbed"
                                                        class="language-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2"
                                                        data-lang="arabic" data-type="dubbed">
                                                    <label for="arabicDubbed">دبلجة بالعربية</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="language-support-card bg-blue-50 p-4 rounded-lg">
                                            <div class="flex items-center mb-3">
                                                <input type="checkbox" id="englishSupported"
                                                    class="language-main-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                                <label for="englishSupported"
                                                    class="font-medium text-gray-700">الإنجليزية</label>
                                            </div>
                                            <div id="englishSupportOptions" class="space-y-2 hidden">
                                                <div class="flex items-center">
                                                    <input type="checkbox" id="englishMenu"
                                                        class="language-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2"
                                                        data-lang="english" data-type="menu">
                                                    <label for="englishMenu">القائمة بالإنجليزية</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input type="checkbox" id="englishSubtitles"
                                                        class="language-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2"
                                                        data-lang="english" data-type="subtitles">
                                                    <label for="englishSubtitles">ترجمة بالإنجليزية</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input type="checkbox" id="englishDubbed"
                                                        class="language-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2"
                                                        data-lang="english" data-type="dubbed">
                                                    <label for="englishDubbed">دبلجة بالإنجليزية</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="language-support-card bg-blue-50 p-4 rounded-lg">
                                            <div class="flex items-center mb-3">
                                                <input type="checkbox" id="frenchSupported"
                                                    class="language-main-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                                <label for="frenchSupported"
                                                    class="font-medium text-gray-700">الفرنسية</label>
                                            </div>
                                            <div id="frenchSupportOptions" class="space-y-2 hidden">
                                                <div class="flex items-center">
                                                    <input type="checkbox" id="frenchMenu"
                                                        class="language-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2"
                                                        data-lang="french" data-type="menu">
                                                    <label for="frenchMenu">القائمة بالفرنسية</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input type="checkbox" id="frenchSubtitles"
                                                        class="language-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2"
                                                        data-lang="french" data-type="subtitles">
                                                    <label for="frenchSubtitles">ترجمة بالفرنسية</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input type="checkbox" id="frenchDubbed"
                                                        class="language-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2"
                                                        data-lang="french" data-type="dubbed">
                                                    <label for="frenchDubbed">دبلجة بالفرنسية</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="language-support-card bg-blue-50 p-4 rounded-lg">
                                            <div class="flex items-center mb-3">
                                                <input type="checkbox" id="spanishSupported"
                                                    class="language-main-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                                <label for="spanishSupported"
                                                    class="font-medium text-gray-700">الإسبانية</label>
                                            </div>
                                            <div id="spanishSupportOptions" class="space-y-2 hidden">
                                                <div class="flex items-center">
                                                    <input type="checkbox" id="spanishMenu"
                                                        class="language-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2"
                                                        data-lang="spanish" data-type="menu">
                                                    <label for="spanishMenu">القائمة بالإسبانية</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input type="checkbox" id="spanishSubtitles"
                                                        class="language-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2"
                                                        data-lang="spanish" data-type="subtitles">
                                                    <label for="spanishSubtitles">ترجمة بالإسبانية</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input type="checkbox" id="spanishDubbed"
                                                        class="language-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2"
                                                        data-lang="spanish" data-type="dubbed">
                                                    <label for="spanishDubbed">دبلجة بالإسبانية</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="language-support-card bg-blue-50 p-4 rounded-lg">
                                            <div class="flex items-center mb-3">
                                                <input type="checkbox" id="japaneseSupported"
                                                    class="language-main-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2">
                                                <label for="japaneseSupported"
                                                    class="font-medium text-gray-700">اليابانية</label>
                                            </div>
                                            <div id="japaneseSupportOptions" class="space-y-2 hidden">
                                                <div class="flex items-center">
                                                    <input type="checkbox" id="japaneseMenu"
                                                        class="language-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2"
                                                        data-lang="japanese" data-type="menu">
                                                    <label for="japaneseMenu">القائمة باليابانية</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input type="checkbox" id="japaneseSubtitles"
                                                        class="language-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2"
                                                        data-lang="japanese" data-type="subtitles">
                                                    <label for="japaneseSubtitles">ترجمة باليابانية</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input type="checkbox" id="japaneseDubbed"
                                                        class="language-checkbox rounded text-indigo-600 focus:ring-indigo-500 ml-2"
                                                        data-lang="japanese" data-type="dubbed">
                                                    <label for="japaneseDubbed">دبلجة باليابانية</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Downloads Tab -->
                        <div id="downloads" class="sub-tab-content">
                            <div class="space-y-6">
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <div class="flex items-center mb-3">
                                        <input type="checkbox" id="hasDownloadUrl"
                                            class="ml-2 rounded text-indigo-600 focus:ring-indigo-500 w-5 h-5">
                                        <label for="hasDownloadUrl" class="font-medium text-gray-700 text-lg">اللعبة
                                            لديها رابط تحميل رئيسي</label>
                                    </div>
                                    <div id="downloadUrlSection" class="hidden">
                                        <input type="url" id="gameDownloadUrl"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            placeholder="مثال: https://example.com/download">
                                        <span class="error-message" id="gameDownloadUrlError">رابط التحميل مطلوب ويجب أن
                                            يكون صحيحاً</span>
                                    </div>
                                </div>

                                <div class="border rounded-lg p-4">
                                    <div class="flex items-center mb-4">
                                        <input type="checkbox" id="hasUpdate"
                                            class="ml-2 rounded text-indigo-600 focus:ring-indigo-500 w-5 h-5">
                                        <label for="hasUpdate" class="font-medium text-gray-700 text-lg">اللعبة لديها
                                            تحديث</label>
                                    </div>
                                    <div id="updateSection" class="space-y-4 hidden">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-gray-700 font-medium mb-2">حجم التحديث</label>
                                                <input type="text" id="updateSize" placeholder="مثال: 24 GB"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            </div>
                                            <div>
                                                <label class="block text-gray-700 font-medium mb-2">رابط التحديث</label>
                                                <input type="url" id="updateUrl"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            </div>
                                        </div>

                                        <div class="flex items-center">
                                            <input type="checkbox" id="hasUpdate-fix"
                                                class="ml-2 rounded text-indigo-600 focus:ring-indigo-500 w-5 h-5">
                                            <label for="hasUpdate-fix" class="font-medium text-gray-700">التحديث يحتوي
                                                على إصلاح (Fix)</label>
                                        </div>

                                        <div id="update-fix-section" class="hidden">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-gray-700 font-medium mb-2">حجم تحديث
                                                        الإصلاح</label>
                                                    <input type="text" id="update-fixSize" placeholder="مثال: 2.47 GB"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                </div>
                                                <div>
                                                    <label class="block text-gray-700 font-medium mb-2">رابط تحديث
                                                        الإصلاح</label>
                                                    <input type="url" id="update-fixUrl"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="border rounded-lg p-4">
                                    <div class="flex items-center mb-4">
                                        <input type="checkbox" id="hasDLC"
                                            class="ml-2 rounded text-indigo-600 focus:ring-indigo-500 w-5 h-5">
                                        <label for="hasDLC" class="font-medium text-gray-700 text-lg">اللعبة لديها
                                            DLC</label>
                                    </div>
                                    <div id="dlcSection" class="space-y-4 hidden">
                                        <div>
                                            <label class="block text-gray-700 font-medium mb-2">عدد DLCs</label>
                                            <input type="number" id="dlcCount" min="1"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        </div>

                                        <div class="bg-purple-50 p-4 rounded-lg">
                                            <h4 class="font-medium mb-2 text-purple-800">DLC 1</h4>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-gray-700 font-medium mb-2">اسم DLC</label>
                                                    <input type="text" id="dlcName"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                </div>
                                                <div>
                                                    <label class="block text-gray-700 font-medium mb-2">رابط DLC</label>
                                                    <input type="url" id="dlcUrl"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-purple-50 p-4 rounded-lg">
                                            <div class="flex items-center mb-2">
                                                <input type="checkbox" id="hasDLC2"
                                                    class="ml-2 rounded text-indigo-600 focus:ring-indigo-500">
                                                <label for="hasDLC2" class="font-medium text-gray-700">DLC 2</label>
                                            </div>
                                            <div id="dlc2Section" class="grid grid-cols-1 md:grid-cols-2 gap-4 hidden">
                                                <div>
                                                    <label class="block text-gray-700 font-medium mb-2">اسم DLC
                                                        2</label>
                                                    <input type="text" id="dlc2Name"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                </div>
                                                <div>
                                                    <label class="block text-gray-700 font-medium mb-2">رابط DLC
                                                        2</label>
                                                    <input type="url" id="dlc2Url"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Parts Tab -->
                        <div id="parts" class="sub-tab-content">
                            <div class="space-y-6">
                                <div class="border rounded-lg p-4">
                                    <div class="flex items-center mb-4">
                                        <input type="checkbox" id="hasDownloadParts"
                                            class="ml-2 rounded text-indigo-600 focus:ring-indigo-500 w-5 h-5">
                                        <label for="hasDownloadParts" class="font-medium text-gray-700 text-lg">اللعبة
                                            مقسمة إلى أجزاء</label>
                                    </div>
                                    <div id="downloadPartsSection" class="space-y-4 hidden">
                                        <div class="bg-green-50 p-4 rounded-lg">
                                            <div class="flex justify-between items-center mb-3">
                                                <h4 class="font-medium text-green-800"><i
                                                        class="fas fa-layer-group ml-2"></i>أجزاء التحميل</h4>
                                                <button type="button" onclick="addPart('downloadPartsList')"
                                                    class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 transition">
                                                    <i class="fas fa-plus ml-1"></i>إضافة جزء
                                                </button>
                                            </div>
                                            <div id="downloadPartsList" class="space-y-2"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="border rounded-lg p-4">
                                    <div class="flex items-center mb-4">
                                        <input type="checkbox" id="hasUpdateParts"
                                            class="ml-2 rounded text-indigo-600 focus:ring-indigo-500 w-5 h-5">
                                        <label for="hasUpdateParts" class="font-medium text-gray-700 text-lg">التحديث
                                            مقسم إلى أجزاء</label>
                                    </div>
                                    <div id="updatePartsSection" class="space-y-4 hidden">
                                        <div class="bg-yellow-50 p-4 rounded-lg">
                                            <div class="flex justify-between items-center mb-3">
                                                <h4 class="font-medium text-yellow-800"><i
                                                        class="fas fa-layer-group ml-2"></i>أجزاء التحديث</h4>
                                                <button type="button" onclick="addPart('updatePartsList')"
                                                    class="bg-yellow-600 text-white px-3 py-1 rounded text-sm hover:bg-yellow-700 transition">
                                                    <i class="fas fa-plus ml-1"></i>إضافة جزء
                                                </button>
                                            </div>
                                            <div id="updatePartsList" class="space-y-2"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="border rounded-lg p-4">
                                    <div class="flex items-center mb-4">
                                        <input type="checkbox" id="hasDLCParts"
                                            class="ml-2 rounded text-indigo-600 focus:ring-indigo-500 w-5 h-5">
                                        <label for="hasDLCParts" class="font-medium text-gray-700 text-lg">DLC مقسم إلى
                                            أجزاء</label>
                                    </div>
                                    <div id="dlcPartsSection" class="space-y-4 hidden">
                                        <div class="bg-purple-50 p-4 rounded-lg">
                                            <div class="flex justify-between items-center mb-3">
                                                <h4 class="font-medium text-purple-800"><i
                                                        class="fas fa-layer-group ml-2"></i>أجزاء DLC</h4>
                                                <button type="button" onclick="addPart('dlcPartsList')"
                                                    class="bg-purple-600 text-white px-3 py-1 rounded text-sm hover:bg-purple-700 transition">
                                                    <i class="fas fa-plus ml-1"></i>إضافة جزء
                                                </button>
                                            </div>
                                            <div id="dlcPartsList" class="space-y-2"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-8 flex justify-between">
                            <div class="flex space-x-reverse space-x-2">
                                <button type="button" onclick="loadExample()"
                                    class="bg-blue-200 text-blue-800 px-4 py-2 rounded-md hover:bg-blue-300 transition">
                                    <i class="fas fa-magic ml-2"></i>تحميل مثال
                                </button>
                            </div>
                            <div class="space-x-reverse space-x-2">
                                <button type="button" onclick="resetForm()"
                                    class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 transition">
                                    <i class="fas fa-redo ml-2"></i>إعادة تعيين
                                </button>
                                <button type="submit"
                                    class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-2 rounded-md hover:from-purple-700 hover:to-indigo-700 transition">
                                    <i class="fas fa-save ml-2"></i>حفظ اللعبة
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Games List Tab -->
            <div id="listTab" class="tab-content">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
                        <h2 class="text-2xl font-bold text-indigo-700">قائمة الألعاب</h2>
                        <div class="flex flex-wrap items-center gap-2">
                            <input type="text" id="searchInput" placeholder="بحث عن لعبة..."
                                class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <select id="platformFilter"
                                class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">كل المنصات</option>
                                <option value="ps4">PS4</option>
                                <option value="ps5">PS5</option>
                                <option value="ps3">PS3</option>
                                <option value="xbox">Xbox</option>
                                <option value="pc">PC</option>
                                <option value="nintendo">Nintendo</option>
                            </select>
                            <select id="genreFilter"
                                class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">كل الأنواع</option>
                                <option value="action">أكشن</option>
                                <option value="adventure">مغامرة</option>
                                <option value="rpg">ألعاب تقمص أدوار</option>
                                <option value="open world">عالم مفتوح</option>
                                <option value="hack and slash">ضرب وقطع</option>
                                <option value="platformer">منصات</option>
                                <option value="arabic">عربي</option>
                                <option value="shooter">تصويب</option>
                                <option value="survival">بقاء</option>
                                <option value="horror">رعب</option>
                                <option value="puzzle">ألغاز</option>
                                <option value="sports">رياضة</option>
                                <option value="racing">سباقات</option>
                                <option value="strategy">استراتيجية</option>
                                <option value="souls">سولز</option>
                                <option value="stealth">تسلل</option>
                                <option value="simulation">محاكاة</option>
                                <option value="metroidvania">ميترويدفانيا</option>
                                <option value="fighting">قتال</option>
                            </select>
                            <select id="ratingFilter"
                                class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">كل التقييمات</option>
                                <option value="9">9+ (ممتاز)</option>
                                <option value="8">8+ (جيد جداً)</option>
                                <option value="7">7+ (جيد)</option>
                                <option value="6">6+ (متوسط)</option>
                            </select>
                        </div>
                    </div>
                    <div id="gamesList" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                        <!-- Games will be displayed here -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Image Manager Modal -->
    <div id="imageManagerModal" class="modal">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-6xl max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b p-4 flex justify-between items-center">
                <h2 class="text-xl font-bold text-indigo-700">إدارة الصور</h2>
                <button onclick="closeImageManager()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-6">
                <!-- Upload Section -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-4">رفع صور جديدة</h3>
                    <div class="image-upload-area" id="imageUploadArea"
                        onclick="document.getElementById('imageFileInput').click()">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-600">اسحب وأفلت الصور هنا أو انقر للاختيار</p>
                        <p class="text-sm text-gray-500 mt-2">PNG, JPG, GIF حتى 5MB</p>
                        <input type="file" id="imageFileInput" multiple accept="image/*" style="display: none;"
                            onchange="handleImageUpload(event)">
                    </div>
                </div>

                <!-- Images Grid -->
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">الصور المرفوعة</h3>
                        <span class="text-sm text-gray-600">الحجم الإجمالي: <span id="totalImageSize">0 MB</span></span>
                    </div>
                    <div id="imageGrid" class="image-grid">
                        <!-- Images will be displayed here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Game Details Modal -->
    <div id="gameModal" class="modal">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b p-4 flex justify-between items-center">
                <h2 class="text-xl font-bold text-indigo-700">تفاصيل اللعبة</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="modalContent" class="p-6">
                <!-- Game details will be displayed here -->
            </div>
        </div>
    </div>

    <!-- Export Options Modal -->
    <div id="exportOptionsModal" class="modal">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <h2 class="text-xl font-bold mb-4">خيارات التصدير</h2>

            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">اختر المنصات المراد تصديرها:</label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" id="export-ps4" checked class="ml-2">
                        <span>PS4 (<span id="ps4ExportCount">0</span> لعبة)</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" id="export-ps3" checked class="ml-2">
                        <span>PS3 (<span id="ps3ExportCount">0</span> لعبة)</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" id="export-ps5" checked class="ml-2">
                        <span>PS5 (<span id="ps5ExportCount">0</span> لعبة)</span>
                    </label>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">تنسيق التصدير:</label>
                <select id="exportFormat" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="separate">ملفات منفصلة (ps4-games.json, ps3-games.json, la5-games.json)</option>
                    <option value="combined">ملف واحد يحتوي على كل الألعاب</option>
                    <option value="package">حزمة تحديث للموقع</option>
                </select>
            </div>

            <div class="flex justify-end space-x-reverse space-x-2">
                <button onclick="closeExportModal()"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition">
                    إلغاء
                </button>
                <button onclick="performExport()"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                    تصدير
                </button>
            </div>
        </div>
    </div>
    <!-- في قسم Action Buttons -->
    <div class="flex flex-wrap gap-2">
        <!-- ... الأزرار الأخرى ... -->
        <button onclick="showTab('comments')" class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 transition">
            <i class="fas fa-comments ml-2"></i>إدارة التعليقات
        </button>
        <!-- ... الأزرار الأخرى ... -->
    </div>
    <!-- زر حفظ التعديلات مباشرة على السيرفر -->
    <div class="export-card" style="background: #10b981; color: white;">
        <h3 class="font-bold mb-2">حفظ التعديلات على الموقع</h3>
        <p class="text-sm mb-3">اضغط هنا لحفظ كل التعديلات (إضافة/تعديل/حذف) في ملفات الموقع الفعلية.</p>
        <button onclick="syncToServer()" class="bg-white text-green-700 px-4 py-2 rounded-md hover:bg-gray-100 transition w-full font-bold">
            <i class="fas fa-cloud-upload-alt ml-2"></i>تحديث الموقع الآن
        </button>
    </div>
    <!-- Comments Management Tab -->
    <div id="commentsTab" class="tab-content">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-6 text-indigo-700"><i class="fas fa-comments ml-2"></i>إدارة التعليقات</h2>

            <div id="commentsManagerContent">
                <!-- سيتم تحميل محتوى إدارة التعليقات هنا ديناميكيًا -->
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-3xl text-indigo-600"></i>
                    <p class="mt-2 text-gray-600">جاري تحميل التعليقات...</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Change Password Modal -->
    <div id="changePasswordModal" class="modal">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <h2 class="text-xl font-bold mb-4">تغيير كلمة المرور</h2>

            <form id="changePasswordForm">
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">كلمة المرور الحالية</label>
                    <input type="password" id="currentPassword"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">كلمة المرور الجديدة</label>
                    <input type="password" id="newPassword"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">تأكيد كلمة المرور الجديدة</label>
                    <input type="password" id="confirmPassword"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        required>
                </div>

                <div class="flex justify-end space-x-reverse space-x-2">
                    <button type="button" onclick="closeChangePasswordModal()"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition">
                        إلغاء
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                        تغيير
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <i class="fas fa-check-circle ml-2"></i>
        <span id="toastMessage">تم الحفظ بنجاح!</span>
    </div>
    <script src="assets/js/admin.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.4.0/axios.min.js"></script>
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.0/jszip.min.js"></script>
</body>

</html>