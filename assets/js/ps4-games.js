// ضبط العنوان
document.title = "أفضل موقع لتحميل ألعاب PS4, PS5, PS3 بالعربية | PSPKG";

document.addEventListener('DOMContentLoaded', () => {
    
    // --- APP STATE ---
    const state = {
        games: typeof gamesData !== 'undefined' ? gamesData : [],
        currentSlide: 0,
        sliderInterval: null,
        currentPage: typeof currentPage !== 'undefined' ? currentPage : 1,
        gamesPerPage: 20,
        activeCategory: typeof activeCategory !== 'undefined' ? activeCategory : 'all',
        categories: [
            { id: 'arabic', name: 'بالعربية', icon: 'fa-language', count: 0 },
            { id: 'action', name: 'أكشن', icon: 'fa-bolt', count: 0 },
            { id: 'hack-and-slash', name: 'هاكسلاش', icon: 'fa-hammer', count: 0 },
            { id: 'rpg', name: 'RPG', icon: 'fa-dragon', count: 0 },
            { id: 'shooter', name: 'إطلاق نار', icon: 'fa-crosshairs', count: 0 },
            { id: 'fighting', name: 'قتال', icon: 'fa-fist-raised', count: 0 },
            { id: 'adventure', name: 'مغامرة', icon: 'fa-compass', count: 0 },
            { id: 'metroidvania', name: 'ميترويدفانيا', icon: 'fa-map', count: 0 },
            { id: 'horror', name: 'رعب', icon: 'fa-ghost', count: 0 },
            { id: 'souls', name: 'سولز', icon: 'fa-skull-crossbones', count: 0 },
            { id: 'stealth', name: 'تسلل', icon: 'fa-user-ninja', count: 0 },
            { id: 'survival', name: 'بقاء', icon: 'fa-campground', count: 0 },
            { id: 'racing', name: 'سباق', icon: 'fa-flag-checkered', count: 0 },
            { id: 'sports', name: 'رياضة', icon: 'fa-football-ball', count: 0 },
            { id: 'simulation', name: 'محاكاة', icon: 'fa-plane', count: 0 },
            { id: 'puzzle', name: 'ألغاز', icon: 'fa-puzzle-piece', count: 0 },
            { id: 'open-world', name: 'عالم مفتوح', icon: 'fa-globe', count: 0 },
        ],
        // إضافة حالة البحث
        searchMatches: [],
        searchSelectedIndex: -1
    };

    // --- DOM ELEMENTS ---
    const elements = {
        slider: document.getElementById('slider'),
        dotsContainer: document.getElementById('dotsContainer'),
        categoriesGrid: document.getElementById('categoriesGrid'),
        searchInput: document.getElementById('searchInput'),
        suggestionsBox: document.getElementById('suggestionsBox'),
        scrollToTop: document.getElementById('scrollToTop'),
        loadingIndicator: document.getElementById('loadingIndicator'),
        errorMessage: document.getElementById('errorMessage'),
        pageNumbers: document.getElementById('page-numbers'),
        prevPageBtn: document.getElementById('prev-page'),
        nextPageBtn: document.getElementById('next-page'),
        paginationContainer: document.getElementById('pagination-container'),
        gamesGrid: document.getElementById('games-grid')
    };

    // --- UTILITIES ---
    
    // حماية من XSS
    function sanitize(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // تهريب أحرف الـ Regex
    function escapeRegex(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    // تمييز النص المطابق
    function highlightMatch(text, query) {
        const sanitized = sanitize(text);
        if (!query) return sanitized;
        const regex = new RegExp(`(${escapeRegex(query)})`, 'gi');
        return sanitized.replace(regex, '<mark class="search-highlight">$1</mark>');
    }

    // تنسيق العنوان للرابط
    function formatTitleForUrl(title) {
        return title.toLowerCase()
            .replace(/\s+/g, '_')
            .replace(/[^a-z0-9_\-]/g, '')
            .replace(/_+/g, '_')
            .replace(/^_|_$/g, '');
    }

    // فتح صفحة التحميل
    function openGamePage(game) {
        if (!game) return;
        const formattedTitle = formatTitleForUrl(game.title);
        window.open(`download.php?id=${game.id}&title=${formattedTitle}`, '_blank');
    }

    // --- INITIALIZATION ---
    function init() {
        // 1. فرز الألعاب
        state.games.sort((a, b) => b.id - a.id);

        // 2. إنشاء خريطة سريعة للبحث (Map أسرع من find)
        state.gamesMap = new Map();
        state.games.forEach(game => {
            state.gamesMap.set(String(game.id), game);
        });

        // 3. إعادة ترتيب البطاقات بالطريقة المحسّنة
        reorderGameCards();

        // 4. تحديث عدادات الفئات
        updateCategoryCounts();

        // 5. إعداد الأحداث
        setupEventListeners();
        setupScrollToTop();
        setupSearch();
        setupDropdowns();
        startAutoSlide();
    }

    // --- REORDER GAME CARDS (محسّن) ---
    function reorderGameCards() {
        if (!elements.gamesGrid) return;
        
        const allCards = elements.gamesGrid.querySelectorAll('.game-card');
        if (allCards.length === 0) return;

        // إنشاء DocumentFragment لتقليل إعادة الرسم
        const fragment = document.createDocumentFragment();
        
        // استخدام Map للبحث السريع بدل find
        const cardsMap = new Map();
        allCards.forEach(card => {
            const id = card.getAttribute('data-id');
            if (id) cardsMap.set(id, card);
        });

        // إضافة البطاقات بالترتيب الجديد
        state.games.forEach(game => {
            const card = cardsMap.get(String(game.id));
            if (card) {
                fragment.appendChild(card);
            }
        });

        // إضافة كل شيء مرة واحدة
        elements.gamesGrid.innerHTML = '';
        elements.gamesGrid.appendChild(fragment);
    }

    // --- UPDATE CATEGORY COUNTS ---
    function updateCategoryCounts() {
        state.categories.forEach(category => {
            category.count = state.games.filter(game =>
                Array.isArray(game.genre) && game.genre.includes(category.id)
            ).length;
        });
    }

    // --- SLIDER ---
    function startAutoSlide() {
        stopAutoSlide();
        state.sliderInterval = setInterval(nextSlide, 3000);
    }

    function stopAutoSlide() {
        if (state.sliderInterval) {
            clearInterval(state.sliderInterval);
            state.sliderInterval = null;
        }
    }

    function showSlide(index) {
        const slides = elements.slider?.querySelectorAll('.slide');
        const dots = elements.dotsContainer?.querySelectorAll('.dot');
        if (!slides?.length) return;

        slides[state.currentSlide]?.classList.remove('active');
        dots[state.currentSlide]?.classList.remove('active');
        
        state.currentSlide = (index + slides.length) % slides.length;
        
        slides[state.currentSlide]?.classList.add('active');
        dots[state.currentSlide]?.classList.add('active');
    }

    function nextSlide() { showSlide(state.currentSlide + 1); }
    function prevSlide() { showSlide(state.currentSlide - 1); }
    
    function goToSlide(index) {
        showSlide(index);
        startAutoSlide();
    }

    // --- SCROLL TO TOP ---
    function setupScrollToTop() {
        if (!elements.scrollToTop) return;

        // استخدام throttle للأداء الأفضل
        let ticking = false;
        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    elements.scrollToTop.classList.toggle('visible', window.pageYOffset > 300);
                    ticking = false;
                });
                ticking = true;
            }
        });

        elements.scrollToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // --- SEARCH FUNCTIONALITY (مربوط بصفحة البحث) ---
    function setupSearch() {
        const searchInput = elements.searchInput;
        const suggestionsBox = elements.suggestionsBox;
        const searchBtn = document.getElementById('searchBtn'); // ✅ ربط زر البحث
        
        if (!searchInput || !elements.suggestionsBox) return;
        
        // ✅ دالة التوجيه لصفحة البحث
        function goToSearchPage(query) {
            if (!query || query.trim().length < 2) return;
            hideSuggestions();
            window.location.href = `search.php?q=${encodeURIComponent(query.trim())}`;
        }

        // إخفاء الاقتراحات
        function hideSuggestions() {
            suggestionsBox.style.display = 'none';
            state.searchSelectedIndex = -1;
        }

        // تحديث التمييز البصري
        function updateSelectedHighlight() {
            const items = suggestionsBox.querySelectorAll('.suggestion-item');
            items.forEach((item, i) => {
                item.classList.toggle('selected', i === state.searchSelectedIndex);
            });
            
            // تمرير العنصر المحدد للرؤية
            if (state.searchSelectedIndex >= 0 && items[state.searchSelectedIndex]) {
                items[state.searchSelectedIndex].scrollIntoView({ block: 'nearest' });
            }
        }

        // عرض النتائج
        function renderSuggestions(matches, query) {
            state.searchMatches = matches;
            state.searchSelectedIndex = -1;
            suggestionsBox.innerHTML = '';

            // حالة: لا توجد نتائج سريعة
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
                // ✅ السماح بالذهاب لصفحة البحث حتى لو لم تظهر اقتراحات
                suggestionsBox.querySelector('.suggestion-search-all')?.addEventListener('click', () => {
                    goToSearchPage(query);
                });
                return;
            }

            // حالة: قائمة قصيرة جداً
            if (query.length < 2) {
                hideSuggestions();
                return;
            }

            suggestionsBox.style.display = 'block';

            // تقليل العدد لـ 7 ليكون الشكل أنظف
            matches.slice(0, 7).forEach((game, index) => {
                const item = document.createElement('div');
                item.className = 'suggestion-item';
                item.setAttribute('role', 'option');
                
                item.innerHTML = `
                    <span class="suggestion-title">${highlightMatch(game.title, query)}</span>
                    <span class="suggestion-platform">${game.platform.toUpperCase()}</span>
                `;

                // النقر على اقتراح = فتح صفحة التحميل مباشرة
                item.addEventListener('click', () => {
                    openGamePage(game);
                    hideSuggestions();
                });

                item.addEventListener('mouseenter', () => {
                    state.searchSelectedIndex = index;
                    updateSelectedHighlight();
                });

                suggestionsBox.appendChild(item);
            });
            
            // ✅ زر "عرض كل النتائج" في أسفل القائمة
            if (matches.length > 0) {
                const showAll = document.createElement('div');
                showAll.className = 'suggestion-show-all';
                showAll.innerHTML = `<i class="fas fa-search"></i> عرض كل النتائج لـ "${sanitize(query)}"`;
                showAll.addEventListener('click', () => {
                    goToSearchPage(query);
                });
                suggestionsBox.appendChild(showAll);
            }
        }

        // --- حدث الإدخال ---
        searchInput.addEventListener('input', (e) => {
            clearTimeout(state.searchDebounceTimer);
            state.searchDebounceTimer = setTimeout(() => {
                const query = e.target.value.trim().toLowerCase();
                const matches = state.games.filter(g => 
                    g.title.toLowerCase().includes(query)
                );
                renderSuggestions(matches, query);
            }, 300);
        });

        // ✅ حدث الضغط على زر البحث (الانتقال لصفحة النتائج)
        searchBtn?.addEventListener('click', () => {
            goToSearchPage(searchInput.value);
        });

               // --- التنقل بلوحة المفاتيح ---
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
                        state.searchSelectedIndex = state.searchSelectedIndex <= 0 
                            ? count - 1 
                            : state.searchSelectedIndex - 1;
                        updateSelectedHighlight();
                    }
                    break;

                case 'Enter':
                    e.preventDefault();
                    
                    // ✅ التعديل الجديد: يفتح التحميل "فقط" لو استخدم الأسهم لاختيار لعبة محددة
                    if (state.searchSelectedIndex >= 0 && state.searchMatches[state.searchSelectedIndex]) {
                        openGamePage(state.searchMatches[state.searchSelectedIndex]);
                        hideSuggestions();
                    } else {
                        // ✅ في كل الحالات الأخرى (حتى لو القائمة مفتوحة) يذهب لصفحة النتائج
                        goToSearchPage(searchInput.value);
                    }
                    break;

                case 'Escape':
                    hideSuggestions();
                    searchInput.blur();
                    break;
            }
        });

        // --- إغلاق عند الضغط خارج ---
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && 
                !suggestionsBox.contains(e.target)) {
                hideSuggestions();
            }
        });

        // --- اختصار / لفتح البحث ---
        document.addEventListener('keydown', (e) => {
            if (e.key === '/' && 
                document.activeElement !== searchInput &&
                !['INPUT', 'TEXTAREA'].includes(document.activeElement?.tagName)) {
                e.preventDefault();
                searchInput.focus();
            }
        });
    }

    // --- DROPDOWN MENUS ---
    function setupDropdowns() {
        const themesBtn = document.getElementById('themesBtn');
        const themesMenu = document.getElementById('themesMenu');
        const categoriesBtn = document.getElementById('categoriesBtn');
        const categoriesMenu = document.getElementById('categoriesMenu');

        function toggleDropdown(btn, menu) {
            if (!btn || !menu) return;
            
            const isOpen = menu.classList.contains('show');
            
            // إغلاق كل القوائم أولاً
            document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
            document.querySelectorAll('.dropdown-btn.active').forEach(b => b.classList.remove('active'));
            
            // فتح القائمة المطلوبة فقط إذا كانت مغلقة
            if (!isOpen) {
                menu.classList.add('show');
                btn.classList.add('active');
            }
        }

        themesBtn?.addEventListener('click', (e) => { 
            e.stopPropagation(); 
            toggleDropdown(themesBtn, themesMenu); 
        });
        
        categoriesBtn?.addEventListener('click', (e) => { 
            e.stopPropagation(); 
            toggleDropdown(categoriesBtn, categoriesMenu); 
        });

        // إغلاق القوائم عند الضغط خارجها
        document.addEventListener('click', (e) => {
            [themesBtn, categoriesBtn].forEach((btn, i) => {
                const menu = [themesMenu, categoriesMenu][i];
                if (btn && menu && !btn.contains(e.target) && !menu.contains(e.target)) {
                    menu.classList.remove('show');
                    btn.classList.remove('active');
                }
            });
        });

        // إغلاق بـ Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
                document.querySelectorAll('.dropdown-btn.active').forEach(b => b.classList.remove('active'));
            }
        });
    }

    // --- EVENT LISTENERS ---
    function setupEventListeners() {
        // أزرار السلايدر
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');

        nextBtn?.addEventListener('click', () => { nextSlide(); startAutoSlide(); });
        prevBtn?.addEventListener('click', () => { prevSlide(); startAutoSlide(); });

        // إيقاف السلايدر عند التمرير
        elements.slider?.addEventListener('mouseenter', stopAutoSlide);
        elements.slider?.addEventListener('mouseleave', startAutoSlide);

        // أزرار الصفحات
        elements.nextPageBtn?.addEventListener('click', () => {
            window.location.href = `?page=${state.currentPage + 1}&category=${state.activeCategory}`;
        });

        elements.prevPageBtn?.addEventListener('click', () => {
            if (state.currentPage > 1) {
                window.location.href = `?page=${state.currentPage - 1}&category=${state.activeCategory}`;
            }
        });

        // نقاط السلايدر (Event Delegation)
        elements.dotsContainer?.addEventListener('click', (e) => {
            const dot = e.target.closest('.dot');
            if (dot) {
                const index = parseInt(dot.getAttribute('data-slide'));
                if (!isNaN(index)) goToSlide(index);
            }
        });

       
        // ✅ بطاقات الألعاب (Event Delegation - مستمع واحد فقط!)
        elements.gamesGrid?.addEventListener('click', (e) => {
            // تجاهل الروابط داخل البطاقة
            if (e.target.tagName === 'A' || e.target.closest('a')) return;
            
            const card = e.target.closest('.game-card');
            if (!card) return;
            
            const gameId = card.getAttribute('data-id');
            const game = state.gamesMap?.get(gameId);
            
            if (game) {
                openGamePage(game);
            }
        });
    }

    // --- تشغيل التطبيق ---
    init();
});