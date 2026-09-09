document.addEventListener('DOMContentLoaded', () => {
    // --- DOM Elements ---
    const filterTabs = document.querySelectorAll('.filter-tab');
    const searchInput = document.querySelector('.search-input');
    const previewOverlay = document.getElementById('previewOverlay');
    const previewClose = document.getElementById('previewClose');
    const swiperWrapper = document.getElementById('swiperWrapper');
    const loadMoreBtn = document.querySelector('.load-more-btn');

    let currentTheme = null;
    let swiper = null;
    let allThemes = []; // To store the fetched themes
    let themesGrid = null; // Will be assigned dynamically

    // --- Core Functions ---

    /**
     * Fetches theme data from the specified JSON file.
     * @param {string} platform - The platform (e.g., 'ps4', 'ps5').
     */
    async function loadThemesData(platform) {
        try {
            // Show a loading message
            themesGrid.innerHTML = `<p style="color: var(--text-secondary); text-align: center; width: 100%;">جاري تحميل الثيمات...</p>`;
            
            const response = await fetch(`data/${platform}-themes.json`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            allThemes = await response.json();
            initializePage(allThemes);
        } catch (error) {
            console.error("Could not load themes data:", error);
            themesGrid.innerHTML = `<p style="color: var(--text-secondary); text-align: center; width: 100%;">عذراً، لم نتمكن من تحميل الثيمات. يرجى المحاولة مرة أخرى لاحقاً.</p>`;
        }
    }

    /**
     * Initializes the page after data is loaded.
     * @param {Array} themes - The array of theme objects.
     */
    function initializePage(themes) {
        generateThemeCards(themes);
        attachEventListeners();
    }

    /**
     * Generates theme cards from the provided themes array.
     * @param {Array} themes - The array of theme objects.
     */
    function generateThemeCards(themes) {
        themesGrid.innerHTML = '';
        
        themes.forEach((theme, index) => {
            const isDynamic = theme.title.toLowerCase().includes('dynamic');
            const isArabic = theme.title.includes('عربي') || theme.title.includes('معرب');
            const isPopular = index < 10;
            
            const categories = [];
            if (isDynamic) categories.push('dynamic');
            else categories.push('static');
            if (isArabic) categories.push('arabic');
            if (isPopular) categories.push('popular');
            
            const card = document.createElement('div');
            card.className = 'theme-card';
            card.setAttribute('data-category', categories.join(' '));
            card.setAttribute('data-title', theme.title);
            card.setAttribute('data-link', theme.link);
            card.setAttribute('data-images', JSON.stringify(theme.images));
            card.setAttribute('data-size', theme.size);
            
            card.innerHTML = `
                <img src="${theme.images[0]}" alt="${theme.title}" class="theme-card-image">
                <div class="theme-card-info">
                    <h3 class="theme-card-title">${theme.title}</h3>
                    <div class="theme-card-meta">
                        <span class="theme-type ${isDynamic ? 'dynamic' : 'static'}">${isDynamic ? 'ديناميكي' : 'ثابت'}</span>
                        <span class="theme-size">${theme.size}</span>
                    </div>
                    <p class="theme-card-description">ثيم ${isDynamic ? 'ديناميكي' : 'ثابت'} احترافي</p>
                    <div class="theme-actions">
                        <a href="${theme.link}" class="download-btn" target="_blank">
                            <i class="fas fa-download"></i> تحميل
                        </a>
                        <a href="#" class="preview-btn">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            `;
            
            themesGrid.appendChild(card);
        });
    }

    /**
     * Attaches event listeners to all preview buttons.
     */
    function attachEventListeners() {
        const previewBtns = document.querySelectorAll('.preview-btn');
        previewBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const themeCard = btn.closest('.theme-card');
                openPreview(themeCard);
            });
        });
    }

    /**
     * Filters theme cards based on the selected category.
     * @param {string} filter - The category to filter by.
     */
    function filterThemes(filter) {
        const themeCards = document.querySelectorAll('.theme-card');
        themeCards.forEach(card => {
            if (filter === 'all') {
                card.style.display = 'block';
            } else {
                const categories = card.getAttribute('data-category').split(' ');
                card.style.display = categories.includes(filter) ? 'block' : 'none';
            }
        });
    }

    /**
     * Initializes the Swiper carousel for the preview modal.
     */
    function initSwiper() {
        swiper = new Swiper('#previewSwiper', {
            loop: true,
            autoplay: { delay: 4000, disableOnInteraction: false, pauseOnMouseEnter: true },
            pagination: { el: '.swiper-pagination', clickable: true },
            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
            effect: 'fade',
            fadeEffect: { crossFade: true },
            keyboard: { enabled: true },
        });
    }

    /**
     * Opens the preview modal with the selected theme's images.
     * @param {HTMLElement} themeCard - The theme card element to preview.
     */
    function openPreview(themeCard) {
        const title = themeCard.getAttribute('data-title');
        const link = themeCard.getAttribute('data-link');
        const images = JSON.parse(themeCard.getAttribute('data-images'));
        
        currentTheme = { title, images, link };

        const slides = currentTheme.images.map((image, index) => `
            <div class="swiper-slide">
                <img src="${image}" alt="${currentTheme.title}" />
                <div class="preview-slide-overlay">
                    <h2 class="preview-title">${currentTheme.title}</h2>
                    <p class="preview-subtitle">${index === 0 ? 'ثيم احترافي' : 'معاينة الثيم بالكامل'}</p>
                    <div class="preview-actions">
                        <button class="preview-download-btn" onclick="downloadTheme('${currentTheme.link}')">
                            <i class="fas fa-download"></i>
                            <span>تحميل الثيم</span>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');

        swiperWrapper.innerHTML = slides;

        if (swiper) {
            swiper.update(); swiper.slideTo(0); swiper.autoplay.start();
        } else {
            initSwiper();
        }

        previewOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    /**
     * Closes the preview modal.
     */
    function closePreview() {
        previewOverlay.classList.remove('active');
        document.body.style.overflow = 'auto';
        if (swiper) swiper.autoplay.stop();
    }

    /**
     * Opens the download link in a new tab.
     * @param {string} link - The URL to download.
     */
    function downloadTheme(link) {
        window.open(link, '_blank');
    }
    window.downloadTheme = downloadTheme; // Make globally accessible

    // --- Event Listeners ---
    filterTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            filterTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            filterThemes(tab.getAttribute('data-filter'));
        });
    });

    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const themeCards = document.querySelectorAll('.theme-card');
        themeCards.forEach(card => {
            const title = card.querySelector('.theme-card-title').textContent.toLowerCase();
            card.style.display = title.includes(searchTerm) ? 'block' : 'none';
        });
    });

    previewClose.addEventListener('click', closePreview);
    previewOverlay.addEventListener('click', (e) => { if (e.target === previewOverlay) closePreview(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && previewOverlay.classList.contains('active')) closePreview(); });

    loadMoreBtn.addEventListener('click', () => {
        console.log('تحميل المزيد من الثيمات...');
        // Add logic here to fetch more themes and append them
    });

    // --- Initial Page Load ---
    const platform = document.body.dataset.platform;
    if (platform) {
        // Dynamically find the correct themes grid based on the platform
        themesGrid = document.getElementById(`${platform}-themes`);
        if (themesGrid) {
            loadThemesData(platform);
        } else {
            console.error(`Themes grid element with ID '${platform}-themes' not found.`);
        }
    } else {
        console.error("Platform not specified in body tag. Add data-platform attribute to <body>.");
    }
});