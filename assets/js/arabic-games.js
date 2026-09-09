// ضبط العنوان
document.title = "    أفضل موقع لتحميل ألعاب PS-arabic , PS4, PS5, PS3| PSPKG-arabic";

document.addEventListener('DOMContentLoaded', () => {
    // --- APP STATE ---
    const state = {
        // gamesData يتم تمريره من PHP ويحتوي فقط على الألعاب المعربة
        games: typeof gamesData !== 'undefined' ? gamesData : [],
        currentSlide: 0,
        sliderInterval: null,
        currentPage: typeof currentPage !== 'undefined' ? currentPage : 1,
        gamesPerPage: 20,
        activeCategory: typeof activeCategory !== 'undefined' ? activeCategory : 'all',
        activePlatform: typeof activePlatform !== 'undefined' ? activePlatform : 'all',
        categories: [
            { id: 'action', name: 'أكشن', icon: 'fa-bolt' },
            { id: 'rpg', name: 'RPG', icon: 'fa-dragon' },
            { id: 'shooter', name: 'إطلاق نار', icon: 'fa-crosshairs' },
            { id: 'fighting', name: 'قتال', icon: 'fa-fist-raised' },
            { id: 'adventure', name: 'مغامرة', icon: 'fa-compass' },
            { id: 'open-world', name: 'عالم مفتوح', icon: 'fa-globe' },
            { id: 'racing', name: 'سباق', icon: 'fa-flag-checkered' },
            { id: 'sports', name: 'رياضة', icon: 'fa-football-ball' },
            { id: 'simulation', name: 'محاكاة', icon: 'fa-plane' },
            { id: 'puzzle', name: 'ألغاز', icon: 'fa-puzzle-piece' },
        ],
        platforms: [
            { id: 'all', name: 'الكل', icon: 'fab fa-playstation' },
            { id: 'ps3', name: 'PS3', icon: 'fab fa-playstation' },
            { id: 'ps4', name: 'PS4', icon: 'fab fa-playstation' },
            { id: 'ps5', name: 'PS5', icon: 'fab fa-playstation' },
        ],
        isLoading: false,
        searchDebounceTimer: null,
        lastDataUpdate: null,
        // ✅ متغيرات البحث الجديدة
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
        paginationContainer: document.getElementById('pagination-container')
    };

    // --- UTILITIES (دوال مساعدة للبحث والأمان) ---
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

    // --- INITIALIZATION ---
    function init() {
        setupEventListeners();
        setupScrollToTop();
        startAutoSlide();
    }

    // --- URL FORMATTING FUNCTION ---
    function formatTitleForUrl(title) {
        return title.toLowerCase()
            .replace(/\s+/g, '_')
            .replace(/[^a-z0-9_\-]/g, '')
            .replace(/_+/g, '_')
            .replace(/^_|_$/g, '');
    }

    // --- SLIDER ---
    function startAutoSlide() {
        if (state.sliderInterval) clearInterval(state.sliderInterval);
        state.sliderInterval = setInterval(nextSlide, 4000);
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

    // --- SCROLL TO TOP FUNCTION ---
    function setupScrollToTop() {
        if (!elements.scrollToTop) return;

        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                elements.scrollToTop.classList.add('visible');
            } else {
                elements.scrollToTop.classList.remove('visible');
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
        const searchBtn = document.getElementById('searchBtn');
        
        if (!searchInput || !suggestionsBox) return;
        
        function goToSearchPage(query) {
            if (!query || query.trim().length < 2) return;
            hideSuggestions();
            window.location.href = `search.php?q=${encodeURIComponent(query.trim())}`;
        }

        function hideSuggestions() {
            suggestionsBox.style.display = 'none';
            state.searchSelectedIndex = -1;
        }

        function updateSelectedHighlight() {
            const items = suggestionsBox.querySelectorAll('.suggestion-item');
            items.forEach((item, i) => item.classList.toggle('selected', i === state.searchSelectedIndex));
            if (state.searchSelectedIndex >= 0 && items[state.searchSelectedIndex]) {
                items[state.searchSelectedIndex].scrollIntoView({ block: 'nearest' });
            }
        }

        searchInput.addEventListener('input', (e) => {
            clearTimeout(state.searchDebounceTimer);
            state.searchDebounceTimer = setTimeout(() => {
                const query = e.target.value.trim().toLowerCase();
                if (query.length < 2) { 
                    hideSuggestions(); 
                    return; 
                }
                
                // البحث في قائمة الألعاب المعربة فقط
                const matches = state.games.filter(g => g.title.toLowerCase().includes(query));
                state.searchMatches = matches;
                state.searchSelectedIndex = -1;
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
                    
                    item.addEventListener('click', () => {
                        const formattedTitle = formatTitleForUrl(game.title);
                        window.open(`download.php?id=${game.id}&title=${formattedTitle}`, '_blank');
                        hideSuggestions();
                    });
                    
                    // ❌ تم حذف mouseenter لمنع الاختيار العشوائي بالماوس
                    
                    suggestionsBox.appendChild(item);
                });
                
                // زر عرض كل النتائج
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
                    if (state.searchSelectedIndex >= 0 && state.searchMatches[state.searchSelectedIndex]) {
                        const game = state.searchMatches[state.searchSelectedIndex];
                        const formattedTitle = formatTitleForUrl(game.title);
                        window.open(`download.php?id=${game.id}&title=${formattedTitle}`, '_blank');
                        hideSuggestions();
                    } else {
                        goToSearchPage(searchInput.value);
                    }
                    break;
                case 'Escape':
                    hideSuggestions();
                    searchInput.blur();
                    break;
            }
        });

        // إغلاق صندوق الاقتراحات عند النقر خارج حقل البحث
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                hideSuggestions();
            }
        });
    }

    // --- EVENT LISTENERS ---
    function setupEventListeners() {
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');

        if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); startAutoSlide(); });
        if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); startAutoSlide(); });

        if (elements.slider) {
            elements.slider.addEventListener('mouseenter', stopAutoSlide);
            elements.slider.addEventListener('mouseleave', startAutoSlide);
        }

        // أزرار التنقل بين الصفحات (تم الحفاظ على روابط arabic-games.php)
        if (elements.nextPageBtn) {
            elements.nextPageBtn.addEventListener('click', () => {
                const nextPage = state.currentPage + 1;
                window.location.href = `arabic-games.php?page=${nextPage}&category=${state.activeCategory}&platform=${state.activePlatform}`;
            });
        }

        if (elements.prevPageBtn) {
            elements.prevPageBtn.addEventListener('click', () => {
                const prevPage = state.currentPage - 1;
                window.location.href = `arabic-games.php?page=${prevPage}&category=${state.activeCategory}&platform=${state.activePlatform}`;
            });
        }

        // النقر على نقاط السلايدر (Event Delegation)
        elements.dotsContainer?.addEventListener('click', (e) => {
            const dot = e.target.closest('.dot');
            if (dot) {
                const slideIndex = parseInt(dot.getAttribute('data-slide'));
                if (!isNaN(slideIndex)) goToSlide(slideIndex);
            }
        });

        // النقر على بطاقات الألعاب (Event Delegation)
        const gamesGrid = document.getElementById('games-grid') || document.querySelector('.games-grid');
        gamesGrid?.addEventListener('click', (e) => {
            if (e.target.tagName === 'A' || e.target.closest('a')) return;
            
            const card = e.target.closest('.game-card');
            if (!card) return;
            
            const gameId = card.getAttribute('data-id');
            const game = state.games.find(g => g.id == gameId);
            
            if (game) {
                const formattedTitle = formatTitleForUrl(game.title);
                window.open(`download.php?id=${game.id}&title=${formattedTitle}`, '_blank');
            }
        });

        setupSearch();
    }

    init();
});