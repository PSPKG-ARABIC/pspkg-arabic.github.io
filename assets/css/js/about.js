document.addEventListener('DOMContentLoaded', () => {
    
    // ===== UTILITIES (دوال مساعدة للبحث والنصوص) =====
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

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }


    // ===== SEARCH FUNCTIONALITY (مربوط بصفحة البحث) =====
    function setupSearch() {
        const searchInput = document.getElementById('searchInput');
        const suggestionsBox = document.getElementById('suggestionsBox');
        const searchBtn = document.getElementById('searchBtn');

        if (!searchInput || !suggestionsBox) return;

        let searchMatches = [];
        let searchSelectedIndex = -1;
        let searchDebounceTimer = null;

        const gamesData = (typeof window.allGamesData !== 'undefined') ? window.allGamesData : (typeof window.gamesData !== 'undefined') ? window.gamesData : [];

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
                        window.open(`download.php?id=${game.id}&title=${formattedTitle}`, '_blank');
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

        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                hideSuggestions();
            }
        });
    }


    // ===== SCROLL ANIMATIONS =====
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = 1;
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.about-section, .feature-card, .contact-info, .contact-form, .privacy-content h2, .privacy-content h3').forEach(section => {
        section.style.opacity = 0;
        section.style.transform = 'translateY(20px)';
        section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(section);
    });

    document.querySelectorAll('.feature-icon').forEach(icon => {
        icon.addEventListener('mouseenter', () => { icon.style.transform = 'scale(1.1) rotate(5deg)'; icon.style.transition = 'transform 0.3s ease'; });
        icon.addEventListener('mouseleave', () => { icon.style.transform = 'scale(1) rotate(0)'; });
    });

    document.querySelectorAll('.privacy-content ul li').forEach(item => {
        item.style.transition = 'transform 0.3s ease';
        item.addEventListener('mouseenter', () => item.style.transform = 'translateX(5px)');
        item.addEventListener('mouseleave', () => item.style.transform = 'translateX(0)');
    });


    // ===== CONTACT FORM HANDLING =====
    const contactForm = document.getElementById('contactForm');
    const formMessageDiv = document.getElementById('formMessage');

    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            const name = document.getElementById('name')?.value.trim();
            const email = document.getElementById('email')?.value.trim();
            const subject = document.getElementById('subject')?.value.trim();
            const message = document.getElementById('message')?.value.trim();

            let isValid = true;
            let errorMessage = '';

            if (!name || !email || !subject || !message) {
                isValid = false;
                errorMessage = 'يرجى ملء جميع الحقول المطلوبة.';
            } else if (!isValidEmail(email)) {
                isValid = false;
                errorMessage = 'يرجى إدخال بريد إلكتروني صحيح.';
            }

            if (!isValid) {
                e.preventDefault();
                if (formMessageDiv) {
                    formMessageDiv.textContent = errorMessage;
                    formMessageDiv.className = 'form-message error';
                    formMessageDiv.style.display = 'block';
                    setTimeout(() => { formMessageDiv.style.display = 'none'; }, 5000);
                }
                return;
            }
        });
    }


    // ===== DROPDOWN MENUS =====
    const themesBtn = document.getElementById('themesBtn');
    const themesMenu = document.getElementById('themesMenu');
    const categoriesBtn = document.getElementById('categoriesBtn');
    const categoriesMenu = document.getElementById('categoriesMenu');

    function toggleDropdown(btn, menu) {
        if (!btn || !menu) return;
        const isOpen = menu.classList.contains('show');
        [themesMenu, categoriesMenu].forEach(m => m?.classList.remove('show'));
        [themesBtn, categoriesBtn].forEach(b => b?.classList.remove('active'));
        if (!isOpen) { menu.classList.add('show'); btn.classList.add('active'); }
    }

    themesBtn?.addEventListener('click', (e) => { e.stopPropagation(); toggleDropdown(themesBtn, themesMenu); });
    categoriesBtn?.addEventListener('click', (e) => { e.stopPropagation(); toggleDropdown(categoriesBtn, categoriesMenu); });

    document.addEventListener('click', () => {
        [themesMenu, categoriesMenu].forEach(m => m?.classList.remove('show'));
        [themesBtn, categoriesBtn].forEach(b => b?.classList.remove('active'));
    });

    [themesMenu, categoriesMenu].forEach(menu => {
        menu?.addEventListener('click', (e) => e.stopPropagation());
    });


    // ===== تشغيل البحث =====
    setupSearch();

});

// ===== GLOBAL ERROR HANDLING =====
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
});