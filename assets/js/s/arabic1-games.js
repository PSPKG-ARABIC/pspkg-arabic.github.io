document.addEventListener('DOMContentLoaded', () => {
    // --- APP STATE ---
    const state = {
        games: typeof gamesData !== 'undefined' ? gamesData : [], // البيانات المعروضة في الصفحة الحالية
        allGames: typeof gamesData !== 'undefined' ? gamesData : [], // يمكن استخدامه للبحث إذا أردت البحث في كل الألعاب
        currentSlide: 0,
        sliderInterval: null,
        currentPage: typeof currentPage !== 'undefined' ? currentPage : 1,
        gamesPerPage: 12,
        activeCategory: typeof activeCategory !== 'undefined' ? activeCategory : 'all',
        activePlatform: typeof activePlatform !== 'undefined' ? activePlatform : 'all',
        isLoading: false,
        searchDebounceTimer: null,
    };

    // --- DOM ELEMENTS ---
    const elements = {
        slider: document.getElementById('slider'),
        dotsContainer: document.getElementById('dotsContainer'),
        gamesGrid: document.getElementById('arabic-games'),
        searchInput: document.getElementById('searchInput'),
        suggestionsBox: document.getElementById('suggestionsBox'),
        scrollToTop: document.getElementById('scrollToTop'),
        prevPageBtn: document.getElementById('prev-page'),
        nextPageBtn: document.getElementById('next-page'),
        paginationContainer: document.getElementById('pagination-container')
    };

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
        state.sliderInterval = setInterval(nextSlide, 3000);
    }
    function stopAutoSlide() {
        if (state.sliderInterval) { clearInterval(state.sliderInterval); state.sliderInterval = null; }
    }
    function showSlide(index) {
        const slides = elements.slider.querySelectorAll('.slide');
        const dots = elements.dotsContainer.querySelectorAll('.dot');
        if (!slides.length) return;
        slides[state.currentSlide].classList.remove('active');
        dots[state.currentSlide].classList.remove('active');
        state.currentSlide = (index + slides.length) % slides.length;
        slides[state.currentSlide].classList.add('active');
        dots[state.currentSlide].classList.add('active');
    }
    function nextSlide() { showSlide(state.currentSlide + 1); }
    function prevSlide() { showSlide(state.currentSlide - 1); }
    function goToSlide(index) { showSlide(index); startAutoSlide(); }

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

    // --- SEARCH FUNCTIONALITY ---
    function setupSearch() {
        if (!elements.searchInput || !elements.suggestionsBox) return;
        elements.searchInput.addEventListener('input', (e) => {
            clearTimeout(state.searchDebounceTimer);
            state.searchDebounceTimer = setTimeout(() => {
                const query = e.target.value.toLowerCase();
                if (query.length < 2) { elements.suggestionsBox.style.display = 'none'; return; }
                
                // البحث في الألعاب المعروضة حاليًا
                const matches = state.games.filter(g => g.title.toLowerCase().includes(query));
                elements.suggestionsBox.innerHTML = '';
                
                if (matches.length > 0) {
                    elements.suggestionsBox.style.display = 'block';
                    matches.slice(0, 10).forEach(game => {
                        const item = document.createElement('div');
                        item.className = 'suggestion-item';
                        item.innerHTML = `${game.title} <span style="color: var(--text-secondary);">(${game.platform.toUpperCase()})</span>`;
                        
                        item.addEventListener('click', () => {
                            const formattedTitle = formatTitleForUrl(game.title);
                            window.open(`download.php?id=${game.id}&title=${formattedTitle}`, '_blank');
                        });
                        
                        elements.suggestionsBox.appendChild(item);
                    });
                } else { 
                    elements.suggestionsBox.style.display = 'none'; 
                }
            }, 300);
        });
    }
    
    // --- EVENT LISTENERS ---
    function setupEventListeners() {
        // --- Filter Buttons ---
        const filterButtons = document.querySelectorAll('.filter-btn');
        const gameCards = document.querySelectorAll('.game-card');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                button.classList.add('active');
                
                // Get filter value
                const filterValue = button.getAttribute('data-filter');
                
                // Filter games
                gameCards.forEach(card => {
                    if (filterValue === 'all') {
                        card.style.display = 'block';
                    } else {
                        const cardPlatform = card.classList.contains(filterValue);
                        card.style.display = cardPlatform ? 'block' : 'none';
                    }
                });
            });
        });

        // --- Slider Navigation ---
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); startAutoSlide(); });
        if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); startAutoSlide(); });
        if (elements.slider) {
            elements.slider.addEventListener('mouseenter', stopAutoSlide);
            elements.slider.addEventListener('mouseleave', startAutoSlide);
        }
        if (elements.dotsContainer) {
            const dots = elements.dotsContainer.querySelectorAll('.dot');
            dots.forEach(dot => {
                dot.addEventListener('click', () => {
                    const slideIndex = parseInt(dot.getAttribute('data-slide'));
                    goToSlide(slideIndex);
                });
            });
        }

        // --- Game Card Clicks ---
        if (elements.gamesGrid) {
            elements.gamesGrid.addEventListener('click', function(e) {
                if (e.target.tagName === 'A' || e.target.closest('a')) return;
                const gameCard = e.target.closest('.game-card');
                if (gameCard) {
                    const gameId = gameCard.getAttribute('data-id');
                    const game = state.games.find(g => g.id == gameId);
                    if (game) {
                        const formattedTitle = formatTitleForUrl(game.title);
                        window.open(`download.php?id=${game.id}&title=${formattedTitle}`, '_blank');
                    }
                }
            });
        }
        
        // --- Pagination ---
        if (elements.nextPageBtn && !elements.nextPageBtn.disabled) {
            elements.nextPageBtn.addEventListener('click', () => {
                const nextPage = state.currentPage + 1;
                window.location.href = `arabic-games.php?page=${nextPage}&platform=${state.activePlatform}&category=${state.activeCategory}`;
            });
        }
        if (elements.prevPageBtn && !elements.prevPageBtn.disabled) {
            elements.prevPageBtn.addEventListener('click', () => {
                const prevPage = state.currentPage - 1;
                window.location.href = `arabic-games.php?page=${prevPage}&platform=${state.activePlatform}&category=${state.activeCategory}`;
            });
        }

        setupSearch();

        // --- Close suggestions on outside click ---
        document.addEventListener('click', (e) => {
            if (elements.suggestionsBox && elements.suggestionsBox.style.display === 'block') {
                if (!elements.searchInput.contains(e.target) && !elements.suggestionsBox.contains(e.target)) {
                    elements.suggestionsBox.style.display = 'none';
                }
            }
        });
    }

    init();
});