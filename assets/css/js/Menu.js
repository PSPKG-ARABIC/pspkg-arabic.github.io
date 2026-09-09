
    // --- DROPDOWN MENUS (THEMES & CATEGORIES) ---
    const themesBtn = document.getElementById('themesBtn');
    const themesMenu = document.getElementById('themesMenu');
    const categoriesBtn = document.getElementById('categoriesBtn');
    const categoriesMenu = document.getElementById('categoriesMenu');

    function toggleDropdown(btn, menu) {
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
        if (!themesBtn.contains(e.target) && !themesMenu.contains(e.target)) {
            themesMenu.classList.remove('show');
            themesBtn.classList.remove('active');
        }
        if (!categoriesBtn.contains(e.target) && !categoriesMenu.contains(e.target)) {
            categoriesMenu.classList.remove('show');
            categoriesBtn.classList.remove('active');
        }
    });