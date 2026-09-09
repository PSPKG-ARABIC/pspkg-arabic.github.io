<?php
// التأكد من تعريف متغيرات التصنيفات لتجنب خطأ "Undefined variable"
if (!isset($categories) || !is_array($categories)) {
    $categories = [
        'all' => 'الكل',
        'action' => 'أكشن',
        'rpg' => 'RPG',
        'racing' => 'سباق',
        'sports' => 'رياضة',
        'adventure' => 'مغامرة',
        'horror' => 'رعب',
        'simulation' => 'محاكاة',
        'souls' => 'سولز',
        'arabic' => 'بالعربية',
        'hack-and-slash' => 'هاكسلاش',
        'survival' => 'بقاء',
        'stealth' => 'تسلل',
        'puzzle' => 'ألغاز',
        'shooter' => 'إطلاق نار',
        'fighting' => 'قتال',
        'metroidvania' => 'ميترويدفانيا',
        'open-world' => 'عالم مفتوح'
    ];
}
if (!isset($activeCategory)) {
    $activeCategory = 'all';
}
?>
<header class="main-header">
    <div class="container">
        <div class="header-content">
            <!-- ✨ ✨ ✨ إضافة جديدة: زر المدير (تسجيل الدخول/الخروج) ✨ ✨ ✨ -->
            <div class="platform-nav admin-nav">
                <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                    <!-- إذا كان المدير مسجل الدخول -->
                    <a href="admin_panel.php" class="platform-nav-btn admin-btn" style="background: #25253a; color: #fff;">
                        <i class="fas fa-cog"></i> لوحة التحكم
                    </a>
                    <a href="logout.php" class="platform-nav-btn logout-btn" style="background: #ff4d4d; color: #fff;">
                        <i class="fas fa-sign-out-alt"></i> خروج
                    </a>
                <?php else: ?>
                    <!-- إذا لم يكن مسجل الدخول -->
                    <a href="login.php" class="platform-nav-btn admin-btn" style="background: #333; color: #fff;">
                        <i class="fas fa-user-shield"></i> المدير
                    </a>
                <?php endif; ?>
            </div>
            <!-- ✨ ✨ ✨ نهاية الإضافة ✨ ✨ ✨ -->
            <div class="logo" onclick="location.href=('index.php')">

                <img src="assets/images/logo.png" alt="PS4 Logo">

            </div>

            <div class="search-container">
                <input type="text" class="search-bar" id="searchInput" placeholder="ابحث عن لعبة...">
                <button class="search-btn"><i class="fas fa-search"></i></button>
                <div class="search-suggestions" id="suggestionsBox"></div>
            </div>

            <div class="platform-nav">
                <a href="ps4-games.php" class="platform-nav-btn ps4">
                    <i class="fab fa-playstation"></i> PS4
                </a>
                <a href="ps5-games.php" class="platform-nav-btn ps5">
                    <i class="fab fa-playstation"></i> PS5
                </a>
                <a href="ps3-games.php" class="platform-nav-btn ps3">
                    <i class="fab fa-playstation"></i> PS3
                </a>
                <a href="arabic-games.php" class="platform-nav-btn arabic ">
                    <i class="fas fa-language"></i> عربية
                </a>
            </div>


            <!-- قائمة الثيمات المنسدلة -->
            <div class="themes-dropdown">
                <button class="themes-btn" id="themesBtn">
                    <i class="fas fa-palette"></i>
                    <span>THEMES</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="themes-menu" id="themesMenu">
                    <div class="themes-list">
                        <a href="" class="theme-link ps4-link">
                            <div class="theme-icon"><i class="fab fa-playstation"></i></div>
                            <div class="theme-info"><span class="platform-name">PS4 Themes</span><span class="theme-count">150+ ثيم</span></div>
                        </a>
                        <a href="" class="theme-link ps5-link">
                            <div class="theme-icon"><i class="fab fa-playstation"></i></div>
                            <div class="theme-info"><span class="platform-name">PS5 Themes</span><span class="theme-count">80+ ثيم</span></div>
                        </a>
                        <a href="" class="theme-link ps3-link">
                            <div class="theme-icon"><i class="fab fa-playstation"></i></div>
                            <div class="theme-info"><span class="platform-name">PS3 Themes</span><span class="theme-count">200+ ثيم</span></div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- قائمة التصنيفات المنسدلة -->
            <div class="categories-dropdown">
                <button class="categories-btn" id="categoriesBtn">
                    <i class="fas fa-th-large"></i>
                    <span>التصنيفات</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="categories-menu" id="categoriesMenu">
                    <div class="categories-list" id="categoriesDropdownList">
                        <!-- تم التعديل هنا أيضاً -->
                        <a href="categories.php?category=all" class="category-dropdown-item <?php echo $activeCategory === 'all' ? 'active' : ''; ?>" data-category-id="all">
                            <i class="fas fa-border-all"></i><span>الكل</span>
                        </a>
                        <!-- CORRECTED: Using $categories instead of $categories_list -->
                        <?php foreach ($categories as $categoryId => $categoryName): ?>
                            <?php if ($categoryId !== 'all'): ?>
                                <!-- تم التعديل هنا -->
                                <a href="categories.php?category=<?php echo $categoryId; ?>" class="category-dropdown-item <?php echo $activeCategory === $categoryId ? 'active' : ''; ?>" data-category-id="<?php echo $categoryId; ?>">
                                    <?php
                                    $iconMap = [
                                        'action' => 'fa-bolt',
                                        'rpg' => 'fa-dragon',
                                        'racing' => 'fa-flag-checkered',
                                        'sports' => 'fa-football-ball',
                                        'adventure' => 'fa-compass',
                                        'horror' => 'fa-ghost',
                                        'simulation' => 'fa-plane',
                                        'souls' => 'fa-skull-crossbones',
                                        'arabic' => 'fa-language',
                                        'hack-and-slash' => 'fa-hammer',
                                        'survival' => 'fa-campground',
                                        'stealth' => 'fa-user-ninja',
                                        'puzzle' => 'fa-puzzle-piece',
                                        'shooter' => 'fa-crosshairs',
                                        'fighting' => 'fa-fist-raised',
                                        'metroidvania' => 'fa-map',
                                        'open-world' => 'fa-globe'
                                    ];
                                    $icon = isset($iconMap[$categoryId]) ? $iconMap[$categoryId] : 'fa-gamepad';
                                    ?>
                                    <i class="fas <?php echo $icon; ?>"></i>
                                    <span><?php echo $categoryName; ?></span>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
</header>