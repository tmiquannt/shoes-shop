/* Small progressive enhancements for the native archive and quantity controls. */
document.addEventListener('DOMContentLoaded', () => {
    const gallery = document.querySelector('.woocommerce-product-gallery');
    if (gallery) {
        const galleryControls = {
            '.pswp__button--zoom': 'zoom-in',
            '.pswp__button--fs': 'maximize',
            '.pswp__button--share': 'share-2',
            '.pswp__button--close': 'x',
            '.pswp__button--arrow--left': 'chevron-left',
            '.pswp__button--arrow--right': 'chevron-right',
        };
        Object.entries(galleryControls).forEach(([selector, icon]) => {
            document.querySelectorAll(selector).forEach(button => {
                button.innerHTML = '<span data-paxig-icon="' + icon + '" data-icon-size="24" aria-hidden="true"></span>';
            });
        });
        const localZoomIcon = () => {
            const trigger = gallery.querySelector('.woocommerce-product-gallery__trigger');
            if (!trigger || trigger.dataset.paxigLocalIcon) return;
            trigger.dataset.paxigLocalIcon = 'true';
            trigger.innerHTML = '<span data-paxig-icon="zoom-in" aria-hidden="true"></span>';
        };
        const enhanceThumbnails = () => gallery.querySelectorAll('.flex-control-thumbs img:not([tabindex])').forEach((thumb, index) => {
            thumb.tabIndex = 0; thumb.setAttribute('role','button');
            thumb.setAttribute('aria-label','View product image ' + (index + 1));
            thumb.addEventListener('keydown', event => {
                if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); thumb.click(); }
            });
        });
        enhanceThumbnails();
        localZoomIcon();
        new MutationObserver(() => { enhanceThumbnails(); localZoomIcon(); }).observe(gallery, {childList:true,subtree:true});
    }
    const sidebar = document.querySelector('.paxig-shop-sidebar');
    if (sidebar && !document.body.classList.contains('paxig-plp')) {
        const disclosure = document.createElement('details');
        disclosure.className = 'paxig-reference-filters';
        const summary = document.createElement('summary');
        summary.textContent = 'Filters';
        const label = sidebar.querySelector('h2');
        if (label) label.textContent = 'Category';
        sidebar.before(disclosure);
        disclosure.append(summary, sidebar);
        const media = matchMedia('(min-width: 1101px)');
        const update = () => { disclosure.open = media.matches; };
        update(); media.addEventListener('change', update);
    }
    const ordering = document.querySelector('.woocommerce-ordering select');
    if (ordering && !document.body.classList.contains('paxig-plp')) {
        ordering.setAttribute('aria-label', 'Sort products');
        Array.from(ordering.options).forEach(option => { option.textContent = 'Sort By: ' + option.textContent; });
    }
    const enhanceQuantities = () => document.querySelectorAll('.summary form.cart .quantity, .woocommerce-cart-form .quantity').forEach(wrapper => {
        const input = wrapper.querySelector('input.qty');
        if (!input || input.type === 'hidden' || wrapper.querySelector('.paxig-quantity-step')) return;
        const minus = document.createElement('button'), plus = document.createElement('button');
        for (const [button, icon, label, method] of [[minus, 'minus', 'Decrease quantity', 'stepDown'], [plus, 'plus', 'Increase quantity', 'stepUp']]) {
            button.type = 'button'; button.className = 'paxig-quantity-step';
            button.innerHTML = '<span data-paxig-icon="' + icon + '" data-icon-size="16" aria-hidden="true"></span>';
            button.setAttribute('aria-label', label);
            button.addEventListener('click', () => {
                try { input[method](); } catch (_) { return; }
                input.dispatchEvent(new Event('input', {bubbles:true}));
                input.dispatchEvent(new Event('change', {bubbles:true}));
            });
        }
        wrapper.prepend(minus); wrapper.append(plus);
    });
    enhanceQuantities();
    if (window.jQuery) window.jQuery(document.body).on('updated_wc_div', enhanceQuantities);
    const search = document.querySelector('.paxig-product-search');
    const actions = document.querySelector('.paxig-header-actions');
    if (search && actions) {
        const toggle = document.createElement('button');
        toggle.type = 'button'; toggle.className = 'paxig-reference-search-toggle';
        toggle.setAttribute('aria-label','Open search'); toggle.setAttribute('aria-expanded','false');
        toggle.setAttribute('aria-controls','paxig-reference-search'); search.id = 'paxig-reference-search';
        toggle.innerHTML = '<span data-paxig-icon="search" data-icon-size="22" aria-hidden="true"></span>';
        actions.prepend(toggle);
        const close = () => { search.classList.remove('is-open'); toggle.setAttribute('aria-expanded','false'); };
        toggle.addEventListener('click', () => {
            const open = search.classList.toggle('is-open'); toggle.setAttribute('aria-expanded',String(open));
            if (open) search.querySelector('input[type=search]').focus();
        });
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && search.classList.contains('is-open')) { close(); toggle.focus(); } });
    }
});
