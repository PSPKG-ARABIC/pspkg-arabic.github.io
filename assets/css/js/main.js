document.addEventListener('DOMContentLoaded', () => {
    
    // --- UTILITIES (دوال مساعدة للبحث) ---
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

    function formatTitleForUrl(title) {
        return title.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_\-]/g, '').replace(/_+/g, '_').replace(/^_|_$/g, '');
    }


    // --- SLIDER FUNCTIONALITY ---
    const slider = document.getElementById('slider');
    const dotsContainer = document.getElementById('dotsContainer');
    const slides = slider ? slider.querySelectorAll('.slide') : [];
    const dots = dotsContainer ? dotsContainer.querySelectorAll('.dot') : [];
    let currentSlide = 0;
    let sliderInterval;

    function showSlide(index) {
        if (slides.length === 0) return;
        slides[currentSlide]?.classList.remove('active');
        dots[currentSlide]?.classList.remove('active');
        currentSlide = (index + slides.length) % slides.length;
        slides[currentSlide]?.classList.add('active');
        dots[currentSlide]?.classList.add('active');
    }

    function nextSlide() { showSlide(currentSlide + 1); }
    function prevSlide() { showSlide(currentSlide - 1); }

    function startAutoSlide() {
        if (sliderInterval) clearInterval(sliderInterval);
        sliderInterval = setInterval(nextSlide, 4000);
    }

    function stopAutoSlide() {
        if (sliderInterval) {
            clearInterval(sliderInterval);
            sliderInterval = null;
        }
    }

    if (slides.length > 0) {
        document.getElementById('nextBtn')?.addEventListener('click', () => { nextSlide(); startAutoSlide(); });
        document.getElementById('prevBtn')?.addEventListener('click', () => { prevSlide(); startAutoSlide(); });
        dots.forEach((dot, index) => { dot.addEventListener('click', () => { showSlide(index); startAutoSlide(); }); });
        slider.addEventListener('mouseenter', stopAutoSlide);
        slider.addEventListener('mouseleave', startAutoSlide);
        startAutoSlide();
    }


    // --- SCROLL TO TOP BUTTON ---
    const scrollToTopBtn = document.getElementById('scrollToTop');
    window.addEventListener('scroll', () => {
        scrollToTopBtn?.classList.toggle('visible', window.pageYOffset > 300);
    });
    scrollToTopBtn?.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });


    // --- DROPDOWN MENUS (THEMES & CATEGORIES) ---
    const themesBtn = document.getElementById('themesBtn');
    const themesMenu = document.getElementById('themesMenu');
    const categoriesBtn = document.getElementById('categoriesBtn');
    const categoriesMenu = document.getElementById('categoriesMenu');

    function toggleDropdown(btn, menu) {
        if (!btn || !menu) return;
        const isOpen = menu.classList.contains('show');
        document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
        document.querySelectorAll('.dropdown-btn.active').forEach(b => b.classList.remove('active'));
        
        if (!isOpen) {
            menu.classList.add('show');
            btn.classList.add('active');
        }
    }

    themesBtn?.addEventListener('click', (e) => { e.stopPropagation(); toggleDropdown(themesBtn, themesMenu); });
    categoriesBtn?.addEventListener('click', (e) => { e.stopPropagation(); toggleDropdown(categoriesBtn, categoriesMenu); });

    document.addEventListener('click', (e) => {
        if (themesBtn && themesMenu && !themesBtn.contains(e.target) && !themesMenu.contains(e.target)) {
            themesMenu.classList.remove('show');
            themesBtn.classList.remove('active');
        }
        if (categoriesBtn && categoriesMenu && !categoriesBtn.contains(e.target) && !categoriesMenu.contains(e.target)) {
            categoriesMenu.classList.remove('show');
            categoriesBtn.classList.remove('active');
        }
    });


    // --- CATEGORY CARDS NAVIGATION ---
    const categoriesGrid = document.getElementById('categoriesGrid');
    if (categoriesGrid) {
        categoriesGrid.addEventListener('click', function(event) {
            const clickedCard = event.target.closest('.category-card');
            if (clickedCard) {
                let categoryId = '';
                if (clickedCard.textContent.includes('الكل')) {
                    categoryId = 'all';
                } else {
                    const classList = clickedCard.classList;
                    for (const className of classList) {
                        if (className !== 'category-card' && className !== 'active') {
                            categoryId = className;
                            break;
                        }
                    }
                }
                if (categoryId) {
                    window.location.href = `category.php?category=${categoryId}`;
                }
            }
        });
    }

    // --- CATEGORY DROPDOWN NAVIGATION ---
    document.querySelectorAll('.category-dropdown-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const categoryId = this.getAttribute('data-category-id');
            if (categoryId) {
                window.location.href = `category.php?category=${categoryId}`;
            }
        });
    });


    // --- SEARCH FUNCTIONALITY (مربوط بصفحة البحث) ---
    const searchInput = document.getElementById('searchInput');
    const suggestionsBox = document.getElementById('suggestionsBox');
    const searchBtn = document.getElementById('searchBtn');

    if (searchInput && suggestionsBox) {
        let searchMatches = [];
        let searchSelectedIndex = -1;
        let searchDebounceTimer;

        // جلب البيانات (يدعم كل الأسماء المحتملة)
        const gamesData = (typeof window.allGamesData !== 'undefined') ? window.allGamesData : 
                           (typeof window.gamesData !== 'undefined') ? window.gamesData : [];

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
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(() => {
                const query = e.target.value.trim().toLowerCase();
                if (query.length < 2) { hideSuggestions(); return; }

                const matches = gamesData.filter(g => g.title && g.title.toLowerCase().includes(query));
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

                    item.addEventListener('click', () => {
                        const formattedTitle = formatTitleForUrl(game.title);
                        window.location.href = `download.php?id=${game.id}&title=${formattedTitle}`;
                        hideSuggestions();
                    });

                    suggestionsBox.appendChild(item);
                });

                const showAll = document.createElement('div');
                showAll.className = 'suggestion-show-all';
                showAll.innerHTML = `<i class="fas fa-search"></i> عرض كل النتائج لـ "${sanitize(query)}"`;
                showAll.addEventListener('click', () => goToSearchPage(query));
                suggestionsBox.appendChild(showAll);

            }, 300);
        });

        searchBtn?.addEventListener('click', () => goToSearchPage(searchInput.value));

        searchInput.addEventListener('keydown', (e) => {
            const items = suggestionsBox.querySelectorAll('.suggestion-item');
            const count = items.length;

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    if (count > 0) { searchSelectedIndex = (searchSelectedIndex + 1) % count; updateSelectedHighlight(); }
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    if (count > 0) { searchSelectedIndex = searchSelectedIndex <= 0 ? count - 1 : searchSelectedIndex - 1; updateSelectedHighlight(); }
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (searchSelectedIndex >= 0 && searchMatches[searchSelectedIndex]) {
                        const game = searchMatches[searchSelectedIndex];
                        const formattedTitle = formatTitleForUrl(game.title);
                        window.location.href = `download.php?id=${game.id}&title=${formattedTitle}`;
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

        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                hideSuggestions();
            }
        });
    }

});


// ===== GLOBAL ERROR HANDLING =====
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
});