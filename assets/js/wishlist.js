/**
 * Wishlist / Favorites System Script for PAXIG's Shoes.
 */
document.addEventListener('DOMContentLoaded', function () {
    const STORAGE_KEY = 'paxig_wishlist';
    const MAX_IDS = 100;
    const config = window.paxigWishlist || {};
    const isLoggedIn = config.is_logged_in === true || config.is_logged_in === 1 || config.is_logged_in === '1';
    const drawer = document.getElementById('paxig-wishlist-drawer');
    const headerBtn = document.querySelector('.paxig-header-wishlist');
    let persistenceNotice = null;
    let persistenceError = '';
    let activeControl = null;
    let initialized = false;
    let detailRevision = 0;
    let syncRevision = 0;
    let syncQueue = Promise.resolve();

    function normalizeIds(value) {
        if (!Array.isArray(value)) return [];
        const ids = [];
        const seen = new Set();
        for (const candidate of value.slice(0, MAX_IDS)) {
            let id;
            if (typeof candidate === 'number' && Number.isSafeInteger(candidate)) {
                id = candidate;
            } else if (typeof candidate === 'string' && candidate === candidate.trim() && /^[1-9][0-9]*$/.test(candidate)) {
                id = Number(candidate);
            } else {
                return [];
            }
            if (!Number.isSafeInteger(id) || id < 1) return [];
            if (!seen.has(id)) {
                seen.add(id);
                ids.push(id);
            }
        }
        return ids;
    }

    function styleNotice(node) {
        node.style.color = 'var(--paxig-s3-ink, #f5f6f2)';
        node.style.backgroundColor = 'var(--paxig-s3-panel, #151816)';
        node.style.borderLeft = '3px solid var(--paxig-s3-accent, #dfff00)';
        node.style.margin = '0';
        node.style.padding = '12px 16px';
    }

    function renderPersistenceError() {
        const content = drawer && drawer.querySelector('.paxig-wishlist-drawer-content');
        const parent = content || (activeControl && activeControl.parentElement) || (headerBtn && headerBtn.parentElement);
        if (parent && (!persistenceNotice || persistenceNotice.parentElement !== parent)) {
            persistenceNotice = document.createElement('p');
            persistenceNotice.classList.add('woocommerce-error', 'paxig-wishlist-persistence-error');
            persistenceNotice.setAttribute('role', 'alert');
            styleNotice(persistenceNotice);
            parent.append(persistenceNotice);
        }

        if (persistenceNotice) {
            persistenceNotice.textContent = persistenceError;
            persistenceNotice.hidden = !persistenceError;
        }
    }

    function setPersistenceError(message) {
        persistenceError = message;
        renderPersistenceError();
        if (initialized && drawer && !drawer.open) openDrawer();
    }

    function clearPersistenceError() {
        persistenceError = '';
        renderPersistenceError();
    }

    function getGuestIds() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return [];
            const parsed = JSON.parse(raw);
            return normalizeIds(parsed);
        } catch (error) {
            setPersistenceError(config.i18n?.read_error || 'Could not read saved favorites on this device.');
            return [];
        }
    }

    function saveGuestIds(ids) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
            clearPersistenceError();
        } catch (error) {
            setPersistenceError(config.i18n?.save_error || 'Could not save favorites on this device.');
        }
    }

    // Authenticated state is always the server-rendered snapshot; guest storage is never imported.
    let wishlist = isLoggedIn ? normalizeIds(config.user_ids) : getGuestIds();

    function updateBadgeCount() {
        document.querySelectorAll('.paxig-wishlist-count').forEach((element) => {
            element.textContent = String(wishlist.length);
        });
    }

    function updateButtonStates() {
        document.querySelectorAll('.paxig-wishlist-btn').forEach((button) => {
            const id = Number(button.dataset.productId);
            const isFavorite = Number.isSafeInteger(id) && wishlist.includes(id);
            button.classList.toggle('is-favorited', isFavorite);
            button.setAttribute('aria-pressed', String(isFavorite));
            const label = button.querySelector('.paxig-wishlist-btn-text');
            if (label) label.textContent = isFavorite ? (config.i18n?.favorited || 'Favorited') : (config.i18n?.favorite || 'Favorite');
        });

        document.querySelectorAll('.paxig-home-product').forEach((card) => {
            const id = Number(card.dataset.productId);
            const heart = card.querySelector('.paxig-home-product-heart');
            if (heart) heart.classList.toggle('is-favorited', wishlist.includes(id));
        });
    }

    function post(action, ids) {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('nonce', config.nonce || '');
        ids.forEach((id) => formData.append('product_ids[]', String(id)));
        return fetch(config.ajax_url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
        }).then((response) => {
            if (!response.ok) throw new Error('Wishlist request failed.');
            return response.json();
        }).then((response) => {
            if (!response || response.success !== true || !response.data) {
                throw new Error('Wishlist request failed.');
            }
            return response.data;
        });
    }

    function syncAccountSnapshot() {
        const revision = ++syncRevision;
        const snapshot = wishlist.slice();
        const send = () => post('paxig_sync_user_wishlist', snapshot).then((data) => {
            if (!Array.isArray(data.saved_ids)) throw new Error('Invalid wishlist response.');
            if (revision === syncRevision) {
                wishlist = normalizeIds(data.saved_ids);
                updateBadgeCount();
                updateButtonStates();
                clearPersistenceError();
                if (drawer && drawer.open) renderWishlistDrawer();
            }
        });
        syncQueue = syncQueue.then(send, send).catch(() => {
            if (revision === syncRevision) setPersistenceError(config.i18n?.sync_error || 'Could not sync favorites to your account. Please try again.');
        });
    }

    function toggleWishlist(productId, control) {
        const id = Number(productId);
        if (!Number.isSafeInteger(id) || id < 1) return;
        if (control) activeControl = control;

        const index = wishlist.indexOf(id);
        if (index >= 0) wishlist.splice(index, 1);
        else if (wishlist.length < MAX_IDS) wishlist.push(id);
        else {
            setPersistenceError(config.i18n?.max_limit || 'You can save up to 100 favorites.');
            return;
        }

        updateBadgeCount();
        updateButtonStates();
        if (isLoggedIn) syncAccountSnapshot();
        else saveGuestIds(wishlist);

        if (drawer && drawer.open) renderWishlistDrawer();
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.paxig-wishlist-btn');
        if (button) {
            event.preventDefault();
            event.stopPropagation();
            toggleWishlist(button.dataset.productId, button);
            return;
        }

        const homeHeart = event.target.closest('.paxig-home-product-heart');
        if (homeHeart) {
            const card = homeHeart.closest('.paxig-home-product');
            if (card && card.dataset.productId) {
                event.preventDefault();
                event.stopPropagation();
                toggleWishlist(card.dataset.productId, homeHeart);
            }
        }
    });

    function openDrawer() {
        if (drawer && !drawer.open) {
            drawer.showModal();
            document.body.classList.add('paxig-wishlist-drawer-open');
            if (headerBtn) headerBtn.setAttribute('aria-expanded', 'true');
            renderWishlistDrawer();
        }
    }

    function closeDrawer() {
        if (drawer && drawer.open) {
            drawer.close();
            document.body.classList.remove('paxig-wishlist-drawer-open');
            if (headerBtn) {
                headerBtn.setAttribute('aria-expanded', 'false');
                headerBtn.focus();
            }
        }
    }

    if (headerBtn) {
        headerBtn.addEventListener('click', function (event) {
            event.preventDefault();
            if (drawer && drawer.open) closeDrawer();
            else openDrawer();
        });
    }

    if (drawer) {
        drawer.addEventListener('click', function (event) {
            if (event.target === drawer || event.target.closest('.paxig-drawer-close')) closeDrawer();
        });
        drawer.addEventListener('close', function () {
            document.body.classList.remove('paxig-wishlist-drawer-open');
            if (headerBtn) headerBtn.setAttribute('aria-expanded', 'false');
        });
        drawer.addEventListener('click', function (event) {
            const removeButton = event.target.closest('.paxig-wishlist-item-remove');
            if (removeButton && removeButton.dataset.productId) {
                event.preventDefault();
                toggleWishlist(removeButton.dataset.productId, removeButton);
            }
        });
    }

    function storeUrl(value) {
        if (typeof value !== 'string' || value.trim() === '') return '';
        try {
            const url = new URL(value, window.location.href);
            if ((url.protocol !== 'https:' && url.protocol !== 'http:') || url.origin !== window.location.origin) return '';
            return url.href;
        } catch (error) {
            return '';
        }
    }

    function renderItems(items, itemsList) {
        itemsList.replaceChildren();
        items.slice(0, MAX_IDS).forEach((item) => {
            const id = Number(item.id);
            if (!Number.isSafeInteger(id) || !wishlist.includes(id)) return;

            const permalink = storeUrl(item.permalink);
            const row = document.createElement('div');
            row.className = 'paxig-wishlist-drawer-item';
            row.setAttribute('role', 'listitem');
            row.dataset.productId = String(id);

            const media = document.createElement('div');
            media.className = 'paxig-wishlist-drawer-item__media';
            const imageLink = document.createElement('a');
            if (permalink) imageLink.href = permalink;
            const imageUrl = storeUrl(item.image_url);
            if (imageUrl) {
                const image = document.createElement('img');
                image.src = imageUrl;
                image.alt = String(item.name || '');
                imageLink.append(image);
            }
            media.append(imageLink);

            const info = document.createElement('div');
            info.className = 'paxig-wishlist-drawer-item__info';
            const header = document.createElement('div');
            header.className = 'paxig-wishlist-drawer-item__header';
            const title = document.createElement('h4');
            title.className = 'paxig-wishlist-drawer-item__title';
            const titleLink = document.createElement('a');
            if (permalink) titleLink.href = permalink;
            titleLink.textContent = String(item.name || '');
            title.append(titleLink);

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'paxig-wishlist-item-remove';
            removeButton.dataset.productId = String(id);
            removeButton.setAttribute('aria-label', config.i18n?.remove || 'Remove from favorites');
            removeButton.textContent = '×';
            header.append(title, removeButton);

            const price = document.createElement('div');
            price.className = 'paxig-wishlist-drawer-item__price';
            price.textContent = String(item.price || '');
            const action = document.createElement('div');
            action.className = 'paxig-wishlist-drawer-item__action';
            const actionLink = document.createElement('a');
            if (permalink) actionLink.href = permalink;
            actionLink.textContent = config.i18n?.select_size || 'Select Size / View →';
            action.append(actionLink);
            info.append(header, price, action);
            row.append(media, info);
            itemsList.append(row);
        });
    }

    function renderWishlistDrawer() {
        if (!drawer) return;
        const emptyState = drawer.querySelector('.paxig-wishlist-empty');
        const itemsList = drawer.querySelector('.paxig-wishlist-drawer-items');
        if (!emptyState || !itemsList) return;

        const revision = ++detailRevision;
        const ids = wishlist.slice(0, MAX_IDS);
        if (ids.length === 0) {
            emptyState.style.display = 'flex';
            itemsList.style.display = 'none';
            itemsList.replaceChildren();
            return;
        }

        emptyState.style.display = 'none';
        itemsList.style.display = 'flex';
        const loading = document.createElement('div');
        loading.className = 'paxig-wishlist-loading';
        loading.textContent = config.i18n?.loading || 'Loading saved shoes…';
        itemsList.replaceChildren(loading);

        post('paxig_get_wishlist_details', ids).then((data) => {
            if (revision !== detailRevision) return;
            if (!Array.isArray(data.items)) throw new Error('Invalid wishlist response.');
            renderItems(data.items, itemsList);
            if (itemsList.children.length === 0) {
                emptyState.style.display = 'flex';
                itemsList.style.display = 'none';
            }
        }).catch(() => {
            if (revision !== detailRevision) return;
            const message = document.createElement('div');
            message.classList.add('woocommerce-error', 'paxig-wishlist-detail-error');
            message.setAttribute('role', 'alert');
            message.textContent = config.i18n?.detail_error || 'Could not load favorites. Please try again.';
            styleNotice(message);
            itemsList.replaceChildren(message);
        });
    }

    updateBadgeCount();
    updateButtonStates();
    initialized = true;
    if (persistenceError && drawer) openDrawer();
});
