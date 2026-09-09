document.addEventListener('DOMContentLoaded', () => {
    
    // --- APP STATE ---
    const state = {
        games: [],
        currentCategory: window.phpVars ? window.phpVars.currentCategory : 'action',
        currentPlatform: 'all',
        shownGames: 12,
        isLoading: false,
        searchDebounceTimer: null,
        lang: window.phpVars ? window.phpVars.lang : 'ar',
        // ✅ متغيرات البحث الجديدة
        searchMatches: [],
        searchSelectedIndex: -1,
        categories: window.phpVars ? window.phpVars.categories : [
            { id: 'arabic', name: 'بالعربية', icon: 'fa-language', description: 'استكشف الألعاب المعربة باللغة العربية لجميع منصات بلايستيشن' },
            { id: 'action', name: 'أكشن', icon: 'fa-bolt', description: 'استمتع بأفضل ألعاب الأكشن والمغامرات على منصات PlayStation.' },
            { id: 'hack-and-slash', name: 'هاكسلاش', icon: 'fa-hammer', description: 'استمتع بألعاب القتال السريع والمعارك الحماسية' },
            { id: 'rpg', name: 'RPG', icon: 'fa-dragon', description: 'انغمس في عواملك الواسعة وأكمل المهام في ألعاب تقمص الأدوار' },
            { id: 'shooter', name: 'إطلاق نار', icon: 'fa-crosshairs', description: 'اختبر أفضل ألعاب إطلاق النار من منظور الشخص الأول والثالث' },
            { id: 'fighting', name: 'قتال', icon: 'fa-fist-raised', description: 'تحدى خصومك في ألعاب القتال المباشر' },
            { id: 'adventure', name: 'مغامرة', icon: 'fa-compass', description: 'انطلق في رحلات ملحمية واكتشف عوالم جديدة' },
            { id: 'metroidvania', name: 'ميترويدفانيا', icon: 'fa-map', description: 'استكشف العوالم المترابطة واكتسب قدرات جديدة' },
            { id: 'horror', name: 'رعب', icon: 'fa-ghost', description: 'واجه مخاوفك في ألعاب الرعب المليئة بالتشويق' },
            { id: 'souls', name: 'سولز', icon: 'fa-skull-crossbones', description: 'اختبر التحديات القاسية في ألعاب سولز' },
            { id: 'stealth', name: 'تسلل', icon: 'fa-user-ninja', description: 'تسلل خلف الأعداء واكمل مهامك دون أن يتم اكتشافك' },
            { id: 'survival', name: 'بقاء', icon: 'fa-campground', description: 'كافح من أجل البقاء في بيئات قاسية' },
            { id: 'racing', name: 'سباق', icon: 'fa-flag-checkered', description: 'تنافس في سباقات السيارات السريعة والمثيرة' },
            { id: 'sports', name: 'رياضة', icon: 'fa-football-ball', description: 'شارك في الرياضات المختلفة وحقق البطولات' },
            { id: 'simulation', name: 'محاكاة', icon: 'fa-plane', description: 'اختبر واقع المحاكاة في مختلف المجالات' },
            { id: 'puzzle', name: 'ألغاز', icon: 'fa-puzzle-piece', description: 'حل الألغاز المعقدة واخترق التحديات العقلية' },
            { id: 'open-world', name: 'عالم مفتوح', icon: 'fa-globe', description: 'استمتع بحرية الاستكشاف في العوالم المفتوحة الواسعة' },
        ]
    };

    // --- DOM ELEMENTS ---
    const elements = {
        categoryTitle: document.getElementById('categoryTitle'),
        categoryIcon: document.getElementById('categoryIcon'),
        categoryDescription: document.getElementById('categoryDescription'),
        totalGames: document.getElementById('totalGames'),
        ps3Count: document.getElementById('ps3Count'),
        ps4Count: document.getElementById('ps4Count'),
        ps5Count: document.getElementById('ps5Count'),
        gamesGrid: document.getElementById('gamesGrid'),
        loadMoreBtn: document.getElementById('loadMoreBtn'),
        filterBtns: document.querySelectorAll('.filter-btn'),
        platformNavBtns: document.querySelectorAll('.platform-nav-btn'),
        scrollToTop: document.getElementById('scrollToTop'),
        searchInput: document.getElementById('searchInput'),
        suggestionsBox: document.getElementById('suggestionsBox')
    };

    // --- UTILITIES (دوال مساعدة للبحث والأمان) ---
    function sanitize(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
    // دالة تنسيق عدد التحميلات
    function formatDownloads(count) {
        if (!count) return '0';
        if (count >= 1000000) return (count / 1000000).toFixed(1) + 'M';
        if (count >= 1000) return (count / 1000).toFixed(1) + 'K';
        return count.toString();
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

    // --- INITIALIZATION ---
    function init() {
        updateCategoryInfo();
        loadGameData().catch(error => {
            console.error('Initial load failed:', error);
            showErrorMessage(state.lang === 'ar' ? 'فشل تحميل البيانات الأولية.' : 'Failed to load initial data.');
        });
        setupEventListeners();
        setupScrollToTop();
        setupDropdowns();
    }

    // --- UPDATE CATEGORY INFO ---
    function updateCategoryInfo() {
        const category = state.categories.find(c => c.id === state.currentCategory);
        if (category) {
            elements.categoryTitle.textContent = category.name;
            elements.categoryDescription.textContent = category.description;
            elements.categoryIcon.innerHTML = `<i class="fas ${category.icon}"></i>`;
            elements.categoryIcon.className = `category-icon ${state.currentCategory}`;
            updateStatsColor();
        }
    }

    // --- UPDATE STATS COLOR ---
    function updateStatsColor() {
        const categoryColor = getComputedStyle(document.documentElement).getPropertyValue(`--${state.currentCategory}-color`);
        const elementsToUpdate = [elements.totalGames, elements.ps3Count, elements.ps4Count, elements.ps5Count];
        const colorToApply = (categoryColor && categoryColor.trim() !== '') ? categoryColor.trim() : getComputedStyle(document.documentElement).getPropertyValue('--accent-color').trim();
        
        elementsToUpdate.forEach(el => {
            if (el) el.style.color = colorToApply;
        });
    }

    // --- LOAD GAME DATA ---
    async function loadGameData() {
        try {
            const timestamp = new Date().getTime();
            const [ps3Games, ps4Games, ps5Games] = await Promise.all([
                fetch(`data/ps3-games.json?t=${timestamp}`).then(r => r.ok ? r.json() : []),
                fetch(`data/ps4-games.json?t=${timestamp}`).then(r => r.ok ? r.json() : []),
                fetch(`data/ps5-games.json?t=${timestamp}`).then(r => r.ok ? r.json() : [])
            ]);
            
            state.games = normalizeGamesData([...ps3Games, ...ps4Games, ...ps5Games]);
            filterGamesByCategory();
        } catch (error) {
            console.error('Error loading game data:', error);
            throw error;
        }
    }

    // --- NORMALIZE GAMES DATA ---
    function normalizeGamesData(games) {
        return games.map(game => {
            if (typeof game.genre === 'string') game.genre = [game.genre];
            else if (!Array.isArray(game.genre)) game.genre = [];
            
            if (!Array.isArray(game.languages)) game.languages = [];
            else {
                game.languages = game.languages.map(lang => {
                    if (typeof lang === 'string') return { name: lang, code: lang.toLowerCase() };
                    return lang || { name: 'غير محدد', code: 'unknown' };
                });
            }
            return game;
        });
    }

    // --- FILTER GAMES BY CATEGORY ---
    function filterGamesByCategory() {
        let filteredGames = state.currentCategory === 'all' 
            ? state.games 
            : state.games.filter(game => Array.isArray(game.genre) && game.genre.includes(state.currentCategory));
        
        updateCategoryStats(filteredGames);
        
        if (state.currentPlatform !== 'all') {
            filteredGames = filteredGames.filter(game => game.platform === state.currentPlatform);
        }
        renderGames(filteredGames);
    }

    // --- UPDATE CATEGORY STATS ---
    function updateCategoryStats(games) {
        if(elements.totalGames) elements.totalGames.textContent = games.length;
        if(elements.ps3Count) elements.ps3Count.textContent = games.filter(g => g.platform === 'ps3').length;
        if(elements.ps4Count) elements.ps4Count.textContent = games.filter(g => g.platform === 'ps4').length;
        if(elements.ps5Count) elements.ps5Count.textContent = games.filter(g => g.platform === 'ps5').length;
        updateStatsColor();
    }

// --- RENDER GAMES ---
function renderGames(games) {
    const gamesToShow = games.slice(0, state.shownGames);
    elements.gamesGrid.innerHTML = '';
    
    if (gamesToShow.length === 0) {
        elements.gamesGrid.innerHTML = `<p style="grid-column: 1/-1; text-align: center;">لا توجد ألعاب متاحة حالياً لهذا التصنيف.</p>`;
        return;
    }
    
    gamesToShow.forEach(game => {
        const card = document.createElement('div');
        card.className = `game-card ${game.platform}`;
        card.setAttribute('data-id', game.id);
        
        const languagesText = game.languages.length > 0 ? game.languages.map(l => l.name || l).join(' • ') : 'غير محدد';
        const genresText = game.genre.length > 0 ? game.genre.map(g => g.toUpperCase()).join(' • ') : 'غير محدد';
        
        // ✨ التحقق مما إذا كانت اللعبة معربة ✨
        const isArabic = game.genre && Array.isArray(game.genre) && game.genre.includes('arabic');
        const arabicBadge = isArabic ? '<div class="arabic-badge"><i class="fas fa-language"></i> بالعربية</div>' : '';
        
        // ✨ تنسيق عدد التحميلات ✨
        let downloadsText = '0';
        if (game.downloads) {
            if (game.downloads >= 1000000) downloadsText = (game.downloads / 1000000).toFixed(1) + 'M';
            else if (game.downloads >= 1000) downloadsText = (game.downloads / 1000).toFixed(1) + 'K';
            else downloadsText = game.downloads;
        }
        
        card.innerHTML = `
            ${arabicBadge}
            <img src="${game.image}" alt="${game.title}" class="game-card-image" loading="lazy">
            <div class="game-card-info">
                <h3 class="game-card-title">${game.title}</h3>
                <div class="game-card-meta">
                    <span class="platform-badge ${game.platform}">${game.platform.toUpperCase()}</span>
                    <span>${genresText}</span>
                </div>
                <div class="game-card-details">
                    <div class="game-code"><i class="fas fa-barcode"></i> ${game.gameCode || 'N/A'}</div>
                    <div class="game-languages"><i class="fas fa-language"></i> ${languagesText}</div>
                    
                    <!-- ✨ الإحصائيات الجديدة ✨ -->
                    <div class="game-stats">
                        <div class="game-stat">
                            <i class="fas fa-hdd"></i>
                            <span>${game.size || 'N/A'}</span>
                        </div>
                        <div class="game-stat">
                            <i class="fas fa-sync-alt"></i>
                            <span>v${game.version || '1.00'}</span>
                        </div>
                        <div class="game-stat">
                            <i class="fas fa-download"></i>
                            <span>${downloadsText}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        card.addEventListener('click', function() {
            const formattedTitle = formatTitleForUrl(game.title);
            window.open(`download.php?id=${game.id}&title=${formattedTitle}&lang=${state.lang}`, '_blank');
        });
        
        elements.gamesGrid.appendChild(card);
    });
    
    if (state.shownGames >= games.length) {
        elements.loadMoreBtn.disabled = true;
        elements.loadMoreBtn.textContent = 'تم عرض كل الألعاب';
    } else {
        elements.loadMoreBtn.disabled = false;
        elements.loadMoreBtn.textContent = 'تحميل المزيد من الألعاب';
    }
}
    // --- URL FORMATTING FUNCTION ---
    function formatTitleForUrl(title) {
        return title.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_\-]/g, '').replace(/_+/g, '_').replace(/^_|_$/g, '');
    }

    // --- EVENT LISTENERS ---
    function setupEventListeners() {
        elements.filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                elements.filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                state.currentPlatform = btn.dataset.filter;
                elements.platformNavBtns.forEach(navBtn => {
                    navBtn.classList.toggle('active', navBtn.dataset.platform === state.currentPlatform);
                });
                state.shownGames = 12;
                filterGamesByCategory();
            });
        });
        
        elements.platformNavBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                elements.platformNavBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                state.currentPlatform = btn.dataset.platform;
                elements.filterBtns.forEach(filterBtn => {
                    filterBtn.classList.toggle('active', filterBtn.dataset.filter === state.currentPlatform);
                });
                state.shownGames = 12;
                filterGamesByCategory();
            });
        });
        
        elements.loadMoreBtn.addEventListener('click', () => {
            state.shownGames += 12;
            filterGamesByCategory();
        });
        
        setupSearch();
    }

     // --- SEARCH FUNCTIONALITY (مربوط بصفحة البحث) ---
    function setupSearch() {
        const searchInput = elements.searchInput;
        const suggestionsBox = elements.suggestionsBox;
        const searchBtn = document.getElementById('searchBtn');
        
        if (!searchInput || !suggestionsBox) return;
        
        function goToSearchPage(query) {
            if (!query || query.trim().length < 2) return;
            hideSuggestions();
            window.location.href = `search.php?q=${encodeURIComponent(query.trim())}`;
        }

        function hideSuggestions() {
            suggestionsBox.style.display = 'none';
            state.searchSelectedIndex = -1; // إعادة تعيين الفهرس دائماً
        }

        function updateSelectedHighlight() {
            const items = suggestionsBox.querySelectorAll('.suggestion-item');
            items.forEach((item, i) => item.classList.toggle('selected', i === state.searchSelectedIndex));
            if (state.searchSelectedIndex >= 0 && items[state.searchSelectedIndex]) {
                items[state.searchSelectedIndex].scrollIntoView({ block: 'nearest' });
            }
        }

        function renderSuggestions(matches, query) {
            state.searchMatches = matches;
            state.searchSelectedIndex = -1; // التأكد من بدايته بـ -1
            suggestionsBox.innerHTML = '';

            if (matches.length === 0 && query.length >= 2) {
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

            if (query.length < 2) { hideSuggestions(); return; }

            suggestionsBox.style.display = 'block';
            matches.slice(0, 7).forEach((game, index) => {
                const item = document.createElement('div');
                item.className = 'suggestion-item';
                item.innerHTML = `
                    <span class="suggestion-title">${highlightMatch(game.title, query)}</span>
                    <span class="suggestion-platform">${game.platform.toUpperCase()}</span>
                `;

                item.addEventListener('click', () => {
                    const formattedTitle = formatTitleForUrl(game.title);
                    window.open(`download.php?id=${game.id}&title=${formattedTitle}&lang=${state.lang}`, '_blank');
                    hideSuggestions();
                });

                // ❌ تم حذف mouseenter تماماً لمنع الاختيار العشوائي
                
                suggestionsBox.appendChild(item);
            });
            
            if (matches.length > 0) {
                const showAll = document.createElement('div');
                showAll.className = 'suggestion-show-all';
                showAll.innerHTML = `<i class="fas fa-search"></i> عرض كل النتائج لـ "${sanitize(query)}"`;
                showAll.addEventListener('click', () => goToSearchPage(query));
                suggestionsBox.appendChild(showAll);
            }
        }

        searchInput.addEventListener('input', (e) => {
            clearTimeout(state.searchDebounceTimer);
            state.searchDebounceTimer = setTimeout(() => {
                const query = e.target.value.trim().toLowerCase();
                const matches = state.games.filter(g => g.title.toLowerCase().includes(query));
                renderSuggestions(matches, query);
            }, 300);
        });

        searchBtn?.addEventListener('click', () => goToSearchPage(searchInput.value));

        searchInput.addEventListener('keydown', (e) => {
            const items = suggestionsBox.querySelectorAll('.suggestion-item');
            const count = items.length;

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    if (count > 0) { 
                        state.searchSelectedIndex = (state.searchSelectedIndex + 1) % count; 
                        updateSelectedHighlight(); 
                    }
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    if (count > 0) { 
                        state.searchSelectedIndex = state.searchSelectedIndex <= 0 ? count - 1 : state.searchSelectedIndex - 1; 
                        updateSelectedHighlight(); 
                    }
                    break;
                case 'Enter':
                    e.preventDefault();
                    // الآن هو سيفتح اللعبة "فقط" لو تحركت بالأسهم عمداً واخترت لعبة
                    if (state.searchSelectedIndex >= 0 && state.searchMatches[state.searchSelectedIndex]) {
                        const game = state.searchMatches[state.searchSelectedIndex];
                        const formattedTitle = formatTitleForUrl(game.title);
                        window.open(`download.php?id=${game.id}&title=${formattedTitle}&lang=${state.lang}`, '_blank');
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
            if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) hideSuggestions();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === '/' && document.activeElement !== searchInput && !['INPUT', 'TEXTAREA'].includes(document.activeElement?.tagName)) {
                e.preventDefault();
                searchInput.focus();
            }
        });
    }
        // --- SCROLL TO TOP FUNCTION ---
    function setupScrollToTop() {
        if (!elements.scrollToTop) return;
        
        // ✨ الحل السحري: نقل الزر من الـ body إلى الـ html لتجاوز مشكلة transform
        document.documentElement.appendChild(elements.scrollToTop);

        window.addEventListener('scroll', () => {
            elements.scrollToTop.classList.toggle('visible', window.pageYOffset > 300);
        });
        
        elements.scrollToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
    // --- SHOW ERROR MESSAGE ---
    function showErrorMessage(message) {
        const errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        errorElement.textContent = message;
        errorElement.style.cssText = 'position:fixed; top:20px; left:50%; transform:translateX(-50%); background:var(--accent-color); color:white; padding:15px 20px; border-radius:5px; z-index:9999;';
        document.body.appendChild(errorElement);
        setTimeout(() => errorElement.remove(), 5000);
    }

    // --- DROPDOWN MENUS (تم دمجها وتنظيفها من التكرار) ---
    function setupDropdowns() {
        const themesBtn = document.getElementById('themesBtn');
        const themesMenu = document.getElementById('themesMenu');
        const categoriesBtn = document.getElementById('categoriesBtn');
        const categoriesMenu = document.getElementById('categoriesMenu');

        function toggleDropdown(btn, menu, otherBtn, otherMenu) {
            if (!btn || !menu) return;
            const isOpen = menu.classList.contains('show');
            
            // إغلاق الكل أولاً
            [themesMenu, categoriesMenu].forEach(m => m?.classList.remove('show'));
            [themesBtn, categoriesBtn].forEach(b => b?.classList.remove('active'));
            
            // فتح المطلوب فقط إذا كان مغلقاً
            if (!isOpen) {
                menu.classList.add('show');
                btn.classList.add('active');
            }
        }

        themesBtn?.addEventListener('click', (e) => { e.stopPropagation(); toggleDropdown(themesBtn, themesMenu, categoriesBtn, categoriesMenu); });
        categoriesBtn?.addEventListener('click', (e) => { e.stopPropagation(); toggleDropdown(categoriesBtn, categoriesMenu, themesBtn, themesMenu); });

        // إغلاق عند النقر خارجها
        document.addEventListener('click', () => {
            themesMenu?.classList.remove('show'); themesBtn?.classList.remove('active');
            categoriesMenu?.classList.remove('show'); categoriesBtn?.classList.remove('active');
        });

        // منع الإغلاق عند النقر داخل القائمة
        [themesMenu, categoriesMenu].forEach(menu => {
            menu?.addEventListener('click', (e) => e.stopPropagation());
        });
    }

    // --- START THE APP ---
    init();
});