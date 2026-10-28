/* Presentation only: native filter links/forms continue to navigate normally. */
document.addEventListener('DOMContentLoaded', () => {
    if (!document.body.classList.contains('paxig-plp')) return;
    const layout = document.querySelector('.paxig-plp-layout');
    const sidebar = document.querySelector('#paxig-plp-filters');
    const toggle = document.querySelector('.paxig-plp-filter-toggle');
    if (!layout || !sidebar || !toggle) return;
    const mobile = matchMedia('(max-width: 900px)');
    const drawer = document.createElement('dialog');
    drawer.className = 'paxig-plp-dialog';
    drawer.setAttribute('aria-labelledby', 'paxig-plp-drawer-title');
    document.body.append(drawer);
    let desktopVisible = true;
    const labels = window.paxigPlpLabels || {};
    const lblFilters = labels.filters || 'Filters';
    const lblHide = labels.hide_filters || 'Hide Filters';
    const lblShow = labels.show_filters || 'Show Filters';
    const lblShowMore = labels.show_more || '+ Show more';
    const lblShowLess = labels.show_less || '− Show less';
    const lblSortBy = labels.sort_by || 'Sort By: ';

    const close = () => { if (drawer.open) drawer.close(); };
    drawer.addEventListener('close', () => { document.body.classList.remove('paxig-plp-drawer-open'); toggle.setAttribute('aria-expanded', 'false'); toggle.focus(); });
    drawer.addEventListener('click', event => {
        const box = drawer.getBoundingClientRect();
        if (event.target === drawer && (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom)) close();
    });
    sidebar.querySelector('.paxig-plp-drawer-close').addEventListener('click', close);
    sidebar.querySelector('.paxig-plp-drawer-view').addEventListener('click', close);
    const update = () => {
        close();
        if (mobile.matches) {
            drawer.append(sidebar);
            layout.classList.remove('is-filters-hidden');
            toggle.querySelector('span').textContent = lblFilters;
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-haspopup', 'dialog');
        } else {
            layout.prepend(sidebar);
            layout.classList.toggle('is-filters-hidden', !desktopVisible);
            toggle.querySelector('span').textContent = desktopVisible ? lblHide : lblShow;
            toggle.setAttribute('aria-expanded', String(desktopVisible));
            toggle.removeAttribute('aria-haspopup');
        }
    };
    toggle.addEventListener('click', () => {
        if (mobile.matches) {
            drawer.showModal();
            document.body.classList.add('paxig-plp-drawer-open');
            toggle.setAttribute('aria-expanded', 'true');
            sidebar.querySelector('.paxig-plp-drawer-close').focus();
        } else {
            desktopVisible = !desktopVisible;
            update();
        }
    });
    update(); mobile.addEventListener('change', update);
    sidebar.querySelectorAll('.paxig-plp-group').forEach(group => {
        const list = group.querySelector('ul');
        if (!list) return;
        const options = Array.from(list.children);
        const limit = group.classList.contains('paxig-plp-group-kich-thuoc') ? 8 : 5;
        if (options.length <= limit) return;
        const extras = options.slice(limit).filter(item => !item.classList.contains('chosen') && !item.querySelector('[aria-current]'));
        if (!extras.length) return;
        const more = document.createElement('button');
        more.type = 'button'; more.className = 'paxig-plp-more';
        list.id = 'paxig-plp-options-' + Array.from(sidebar.querySelectorAll('.paxig-plp-group')).indexOf(group);
        more.setAttribute('aria-controls', list.id);
        let expanded = false;
        const show = () => {
            extras.forEach(item => item.hidden = !expanded);
            more.textContent = (expanded ? lblShowLess : lblShowMore).replace(/^[+−]\s*/, '') + ' ';
            const icon = document.createElement('span');
            icon.setAttribute('data-paxig-icon', expanded ? 'minus' : 'plus');
            icon.setAttribute('data-icon-size', '16');
            icon.setAttribute('aria-hidden', 'true');
            more.append(icon);
            more.setAttribute('aria-expanded', String(expanded));
        };
        more.addEventListener('click', () => { expanded = !expanded; show(); });
        group.querySelector('.paxig-plp-group-body').append(more); show();
    });
    const sort = document.querySelector('.woocommerce-ordering select');
    if (sort) {
        sort.setAttribute('aria-label', lblSortBy.replace(/[:\s]+$/, ''));
    }
});
