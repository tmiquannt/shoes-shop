document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.querySelector('.paxig-menu-toggle');
    const navigation = document.querySelector('#paxig-primary-navigation');
    const header = document.querySelector('.paxig-header');

    if (!menuToggle || !navigation || !header) {
        return;
    }

    function closeMenu() {
        navigation.classList.remove('is-open');
        menuToggle.setAttribute('aria-expanded', 'false');

        navigation
            .querySelectorAll('.menu-item-has-children')
            .forEach(function (item) {
                item.classList.remove('is-submenu-open');

                const button = item.querySelector(
                    ':scope > .paxig-submenu-toggle'
                );

                if (button) {
                    button.setAttribute('aria-expanded', 'false');
                }
            });
    }

    menuToggle.addEventListener('click', function () {
        const isOpen = navigation.classList.toggle('is-open');

        menuToggle.setAttribute(
            'aria-expanded',
            isOpen ? 'true' : 'false'
        );
    });

    navigation
        .querySelectorAll('.menu-item-has-children')
        .forEach(function (item, index) {
            const link = item.querySelector(':scope > a');
            const submenu = item.querySelector(':scope > .sub-menu');

            if (!link || !submenu) {
                return;
            }

            const submenuId = 'paxig-submenu-' + index;
            submenu.id = submenuId;

            const submenuToggle = document.createElement('button');

            submenuToggle.type = 'button';
            submenuToggle.className = 'paxig-submenu-toggle';
            submenuToggle.setAttribute('aria-expanded', 'false');
            submenuToggle.setAttribute('aria-controls', submenuId);
            submenuToggle.setAttribute(
                'aria-label',
                'Toggle ' + link.textContent.trim() + ' submenu'
            );

            submenuToggle.innerHTML = [
                '<svg viewBox="0 0 24 24" width="20" height="20"',
                ' aria-hidden="true">',
                '<path d="M6 9l6 6 6-6"></path>',
                '</svg>'
            ].join('');

            link.insertAdjacentElement('afterend', submenuToggle);

            submenuToggle.addEventListener('click', function () {
                const isOpen = item.classList.toggle(
                    'is-submenu-open'
                );

                submenuToggle.setAttribute(
                    'aria-expanded',
                    isOpen ? 'true' : 'false'
                );
            });
        });

    document.addEventListener('click', function (event) {
        if (!header.contains(event.target)) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeMenu();
            menuToggle.focus();
        }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 900) {
            closeMenu();
        }
    });

    // Search modal toggle matching 06_Search_Overlay.png
    const searchTrigger = document.querySelector('.paxig-header-search-trigger');
    const searchModal = document.querySelector('#vyre-search-modal');
    if (searchTrigger && searchModal) {
        const searchClose = searchModal.querySelector('.vyre-search-close');
        const searchBackdrop = searchModal.querySelector('.vyre-search-backdrop');
        const searchInput = searchModal.querySelector('input[type="search"]');

        function openSearch() {
            searchModal.classList.add('is-open');
            searchModal.setAttribute('aria-hidden', 'false');
            searchTrigger.setAttribute('aria-expanded', 'true');
            if (searchInput) setTimeout(function () { searchInput.focus(); }, 60);
        }

        function closeSearch() {
            searchModal.classList.remove('is-open');
            searchModal.setAttribute('aria-hidden', 'true');
            searchTrigger.setAttribute('aria-expanded', 'false');
        }

        searchTrigger.addEventListener('click', function (e) {
            e.preventDefault();
            if (searchModal.classList.contains('is-open')) {
                closeSearch();
            } else {
                openSearch();
            }
        });

        if (searchClose) searchClose.addEventListener('click', closeSearch);
        if (searchBackdrop) searchBackdrop.addEventListener('click', closeSearch);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && searchModal.classList.contains('is-open')) {
                closeSearch();
            }
        });
    }
});