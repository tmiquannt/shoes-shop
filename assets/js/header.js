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

            submenuToggle.innerHTML = '<span data-paxig-icon="chevron-down" aria-hidden="true"></span>';

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
        if (window.innerWidth > 1200) {
            closeMenu();
        }
    });

    // Active navigation state & section jump handling
    const navList = navigation.querySelector('.paxig-nav');
    if (navList) {
        const navLinks = navList.querySelectorAll('li > a');

        function setActiveNav(targetLink) {
            navList.querySelectorAll('li').forEach(function (li) {
                li.classList.remove('current-menu-item', 'is-active');
                const a = li.querySelector('a');
                if (a) {
                    a.classList.remove('is-active');
                    a.removeAttribute('aria-current');
                }
            });

            if (targetLink) {
                const li = targetLink.closest('li');
                if (li) {
                    li.classList.add('current-menu-item', 'is-active');
                }
                targetLink.classList.add('is-active');
                targetLink.setAttribute('aria-current', 'page');
            }
        }

        navLinks.forEach(function (link) {
            link.addEventListener('click', function (e) {
                const href = link.getAttribute('href') || '';
                const hashIndex = href.indexOf('#');
                const hasHash = hashIndex !== -1;
                const hash = hasHash ? href.substring(hashIndex) : '';

                // Check if this is an anchor on the current page
                const isCurrentPageAnchor = hasHash && (
                    href.startsWith('#') ||
                    href.startsWith(window.location.origin + window.location.pathname + '#') ||
                    (window.location.pathname === '/' && (href.startsWith('/#') || href.startsWith(window.location.origin + '/#')))
                );

                if (isCurrentPageAnchor && hash) {
                    const targetElement = document.querySelector(hash) || document.querySelector(hash.replace('-title', ''));
                    if (targetElement) {
                        e.preventDefault();
                        setActiveNav(link);
                        if (window.history && window.history.pushState) {
                            window.history.pushState(null, '', hash);
                        }
                        const headerHeight = header.offsetHeight || 64;
                        const elementTop = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight - 16;
                        window.scrollTo({
                            top: Math.max(0, elementTop),
                            behavior: 'smooth'
                        });
                        closeMenu();
                        return;
                    }
                }

                setActiveNav(link);
                closeMenu();
            });
        });

        function updateActiveFromHash() {
            const hash = window.location.hash;
            if (!hash) return;

            let matchedLink = null;
            navLinks.forEach(function (link) {
                const href = link.getAttribute('href') || '';
                if (href.endsWith(hash) || (hash === '#brand-story' && href.includes('#brand-story-title')) || (hash === '#brand-story-title' && href.includes('#brand-story'))) {
                    matchedLink = link;
                }
            });

            if (matchedLink) {
                setActiveNav(matchedLink);
            }
        }

        if (window.location.hash) {
            setTimeout(updateActiveFromHash, 100);
        }
        window.addEventListener('hashchange', updateActiveFromHash);

        // Scrollspy for in-page brand story section on homepage
        const brandStorySection = document.querySelector('#brand-story') || document.querySelector('#brand-story-title');
        if (brandStorySection) {
            const storyLi = navList.querySelector('li[data-nav-key="story"]');
            const storyLink = storyLi ? storyLi.querySelector('a') : null;
            if (storyLink) {
                let isStoryActiveByScroll = false;
                window.addEventListener('scroll', function () {
                    const rect = brandStorySection.getBoundingClientRect();
                    const headerHeight = header.offsetHeight || 64;
                    if (rect.top <= headerHeight + 120 && rect.bottom >= headerHeight + 60) {
                        if (!isStoryActiveByScroll) {
                            setActiveNav(storyLink);
                            isStoryActiveByScroll = true;
                        }
                    } else if (window.pageYOffset < 250) {
                        if (isStoryActiveByScroll) {
                            setActiveNav(null);
                            isStoryActiveByScroll = false;
                        }
                    } else if (isStoryActiveByScroll && (rect.bottom < headerHeight || rect.top > window.innerHeight)) {
                        setActiveNav(null);
                        isStoryActiveByScroll = false;
                    }
                }, { passive: true });
            }
        }
    }

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
