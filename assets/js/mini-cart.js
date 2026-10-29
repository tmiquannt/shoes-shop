/**
 * Mini-Cart Slide-Out Drawer Script for PAXG'Shop (CART-1 Safe Repair).
 */
document.addEventListener('DOMContentLoaded', function () {
    const drawer = document.getElementById('paxig-cart-drawer');
    const headerCartBtn = document.querySelector('.paxig-header-cart');
    let isRequestPending = false;

    if (!drawer) return;

    function openDrawer() {
        if (!drawer.open) {
            clearAlert();
            drawer.showModal();
            document.body.classList.add('paxig-cart-drawer-open');
            if (headerCartBtn) {
                headerCartBtn.setAttribute('aria-expanded', 'true');
            }
        }
    }

    function closeDrawer() {
        if (drawer.open) {
            clearAlert();
            drawer.close();
            document.body.classList.remove('paxig-cart-drawer-open');
            if (headerCartBtn) {
                headerCartBtn.setAttribute('aria-expanded', 'false');
                headerCartBtn.focus();
            }
        }
    }

    function getAlertElement() {
        let alertEl = drawer.querySelector('.paxig-drawer-alert');
        if (!alertEl) {
            alertEl = document.createElement('div');
            alertEl.className = 'paxig-drawer-alert';
            alertEl.setAttribute('role', 'alert');
            alertEl.setAttribute('aria-live', 'assertive');
            const header = drawer.querySelector('.paxig-drawer-header');
            if (header && header.nextSibling) {
                header.parentNode.insertBefore(alertEl, header.nextSibling);
            }
        }
        return alertEl;
    }

    function showAlert(message) {
        const alertEl = getAlertElement();
        if (alertEl) {
            alertEl.textContent = message;
            alertEl.style.display = 'block';
            alertEl.style.padding = '12px 20px';
            alertEl.style.margin = '0';
            alertEl.style.fontFamily = 'var(--paxig-font-body, Arial, sans-serif)';
            alertEl.style.fontSize = 'var(--paxig-type-small, 14px)';
            alertEl.style.lineHeight = '1.4';
            alertEl.style.background = 'var(--paxig-color-surface-raised, #1a1d1c)';
            alertEl.style.color = 'var(--paxig-color-danger, #d94a43)';
            alertEl.style.borderBottom = '1px solid var(--paxig-color-border, #343735)';
        }
    }

    function clearAlert() {
        const alertEl = drawer.querySelector('.paxig-drawer-alert');
        if (alertEl) {
            alertEl.textContent = '';
            alertEl.style.display = 'none';
        }
    }

    function setControlsDisabled(disabled) {
        isRequestPending = disabled;
        const content = drawer.querySelector('.paxig-cart-drawer-content');
        if (content) {
            content.setAttribute('aria-busy', disabled ? 'true' : 'false');
            if (disabled) {
                content.style.opacity = '0.6';
                content.style.pointerEvents = 'none';
            } else {
                content.style.opacity = '';
                content.style.pointerEvents = '';
            }
        }

        // Manage button states and preserve semantic disabled state on minus buttons
        drawer.querySelectorAll('.paxig-qty-btn, .paxig-cart-item-remove, .paxig-drawer-btn').forEach(btn => {
            if (disabled) {
                btn.disabled = true;
                btn.setAttribute('tabindex', '-1');
                btn.setAttribute('aria-disabled', 'true');
            } else {
                btn.removeAttribute('tabindex');
                btn.removeAttribute('aria-disabled');

                if (btn.classList.contains('paxig-qty-btn--minus')) {
                    const control = btn.closest('.paxig-cart-qty-control');
                    const valElem = control?.querySelector('.paxig-qty-val');
                    const qty = parseInt(valElem?.textContent.trim(), 10) || 1;
                    btn.disabled = (qty <= 1);
                } else {
                    btn.disabled = false;
                }
            }
        });
    }

    // Explicit capture-phase click guard while request is pending
    drawer.addEventListener('click', function (e) {
        if (isRequestPending) {
            const actionable = e.target.closest('.paxig-drawer-btn, .paxig-qty-btn, .paxig-cart-item-remove');
            if (actionable) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                return false;
            }
        }
    }, true);

    // Explicit capture-phase keyboard guard while request is pending (Enter / Space)
    drawer.addEventListener('keydown', function (e) {
        if (isRequestPending && (e.key === 'Enter' || e.key === ' ' || e.keyCode === 13 || e.keyCode === 32)) {
            const active = document.activeElement;
            const actionable = active && active.closest('.paxig-drawer-btn, .paxig-qty-btn, .paxig-cart-item-remove');
            if (actionable) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                return false;
            }
        }
    }, true);

    // Toggle drawer on header cart button click
    if (headerCartBtn) {
        headerCartBtn.addEventListener('click', function (e) {
            if (window.paxigMiniCart && (window.paxigMiniCart.is_cart || window.paxigMiniCart.is_checkout)) {
                return;
            }
            e.preventDefault();
            if (drawer.open) {
                closeDrawer();
            } else {
                openDrawer();
            }
        });
    }

    // Close on click outside (backdrop) or on close button
    drawer.addEventListener('click', function (event) {
        if (event.target === drawer || event.target.closest('.paxig-drawer-close')) {
            closeDrawer();
        }
    });

    drawer.addEventListener('close', function () {
        document.body.classList.remove('paxig-cart-drawer-open');
        clearAlert();
        if (headerCartBtn) headerCartBtn.setAttribute('aria-expanded', 'false');
    });

    // Listen for WooCommerce added_to_cart event -> auto open drawer (skip card add-to-cart clicks)
    if (window.jQuery) {
        window.jQuery(document.body).on('added_to_cart', function (event, fragments, cartHash, $button) {
            if ($button && window.jQuery && window.jQuery($button).closest('.vyre-card-add-to-cart').length) {
                return;
            }
            openDrawer();
        });
    }

    // Direct event listener for AJAX add-to-cart (PDP / Loop)
    document.body.addEventListener('paxig_added_to_cart', function (event) {
        if (event.detail && event.detail.fragments) {
            applyFragments(event.detail.fragments);
        }
        if (!event.detail || event.detail.open_drawer !== false) {
            openDrawer();
        }
    });

    // Display error alert inside drawer if adding to cart fails
    document.body.addEventListener('paxig_add_to_cart_error', function (event) {
        if (event.detail && event.detail.message) {
            openDrawer();
            showAlert(event.detail.message);
        }
    });

    // Delegate remove item click
    drawer.addEventListener('click', function (event) {
        const removeBtn = event.target.closest('.paxig-cart-item-remove');
        if (!removeBtn || isRequestPending) return;

        event.preventDefault();
        const key = removeBtn.dataset.cartItemKey;
        if (!key) return;

        clearAlert();
        setControlsDisabled(true);

        const formData = new FormData();
        formData.append('action', 'paxig_remove_cart_item');
        formData.append('cart_item_key', key);
        formData.append('nonce', window.paxigMiniCart ? window.paxigMiniCart.nonce : '');

        fetch(window.paxigMiniCart.ajax_url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
        })
        .then(res => res.json())
        .then(response => {
            if (response.success && response.data.fragments) {
                applyFragments(response.data.fragments);
            } else {
                showAlert(response.data?.message || (window.paxigMiniCart?.i18n?.remove_error || 'Could not remove item from bag.'));
            }
        })
        .catch(() => {
            showAlert(window.paxigMiniCart?.i18n?.comm_error || 'Communication error. Please check your connection and try again.');
        })
        .finally(() => {
            setControlsDisabled(false);
        });
    });

    // Delegate quantity increment / decrement click
    drawer.addEventListener('click', function (event) {
        const btn = event.target.closest('.paxig-qty-btn');
        if (!btn || btn.disabled || isRequestPending) return;

        event.preventDefault();
        const control = btn.closest('.paxig-cart-qty-control');
        const key = control?.dataset.cartItemKey;
        const valElem = control?.querySelector('.paxig-qty-val');
        if (!key || !valElem) return;

        let currentQty = parseInt(valElem.textContent.trim(), 10) || 1;
        let newQty = btn.classList.contains('paxig-qty-btn--plus') ? currentQty + 1 : currentQty - 1;
        if (newQty < 0) newQty = 0;

        clearAlert();
        setControlsDisabled(true);

        const formData = new FormData();
        formData.append('action', 'paxig_update_cart_quantity');
        formData.append('cart_item_key', key);
        formData.append('quantity', newQty);
        formData.append('nonce', window.paxigMiniCart ? window.paxigMiniCart.nonce : '');

        fetch(window.paxigMiniCart.ajax_url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
        })
        .then(res => res.json())
        .then(response => {
            if (response.success && response.data.fragments) {
                applyFragments(response.data.fragments);
            } else {
                showAlert(response.data?.message || (window.paxigMiniCart?.i18n?.update_error || 'Could not update quantity.'));
            }
        })
        .catch(() => {
            showAlert(window.paxigMiniCart?.i18n?.comm_error || 'Communication error. Please check your connection and try again.');
        })
        .finally(() => {
            setControlsDisabled(false);
        });
    });

    // Delegate card quick add-to-cart clicks
    if (document.body && document.body.addEventListener) {
        document.body.addEventListener('click', function (event) {
            if (!event || !event.target || typeof event.target.closest !== 'function') return;
            const btn = event.target.closest('.vyre-card-add-to-cart');
            if (!btn || btn.tagName.toLowerCase() !== 'button') return;

            const productId = btn.dataset.productId;
            if (!productId || btn.classList.contains('is-loading')) return;

            event.preventDefault();
            if (typeof event.stopPropagation === 'function') {
                event.stopPropagation();
            }

            btn.classList.add('is-loading');
            btn.setAttribute('aria-busy', 'true');

            const formData = new FormData();
            formData.append('action', 'paxig_ajax_add_to_cart');
            formData.append('product_id', productId);
            formData.append('quantity', '1');
            if (window.paxigMiniCart && window.paxigMiniCart.nonce) {
                formData.append('nonce', window.paxigMiniCart.nonce);
            }

            const ajaxUrl = (window.paxigMiniCart && window.paxigMiniCart.ajax_url)
                || (window.wc_add_to_cart_params && window.wc_add_to_cart_params.ajax_url)
                || '/wp-admin/admin-ajax.php';

            fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
            })
            .then(function (res) { return res.json(); })
            .then(function (response) {
                if (response.success && response.data) {
                    btn.classList.add('is-added');
                    setTimeout(function () {
                        btn.classList.remove('is-added');
                    }, 1500);

                    if (typeof CustomEvent === 'function') {
                        document.body.dispatchEvent(new CustomEvent('paxig_added_to_cart', {
                            detail: Object.assign({}, response.data, { open_drawer: false })
                        }));
                    }
                    if (window.jQuery) {
                        window.jQuery(document.body).trigger('added_to_cart', [response.data.fragments, response.data.cart_hash, window.jQuery(btn)]);
                        window.jQuery(document.body).trigger('wc_fragment_refresh');
                    }
                } else {
                    const msg = (response.data && response.data.message)
                        || (window.paxigMiniCart && window.paxigMiniCart.i18n && window.paxigMiniCart.i18n.comm_error)
                        || 'Could not add item to bag.';
                    if (typeof CustomEvent === 'function') {
                        document.body.dispatchEvent(new CustomEvent('paxig_add_to_cart_error', {
                            detail: { message: msg }
                        }));
                    } else {
                        alert(msg);
                    }
                }
            })
            .catch(function () {
                const msg = (window.paxigMiniCart && window.paxigMiniCart.i18n && window.paxigMiniCart.i18n.comm_error)
                    || 'Communication error. Please check your connection and try again.';
                if (typeof CustomEvent === 'function') {
                    document.body.dispatchEvent(new CustomEvent('paxig_add_to_cart_error', {
                        detail: { message: msg }
                    }));
                } else {
                    alert(msg);
                }
            })
            .finally(function () {
                btn.classList.remove('is-loading');
                btn.removeAttribute('aria-busy');
            });
        });
    }

    function applyFragments(fragments) {
        for (const [selector, html] of Object.entries(fragments)) {
            document.querySelectorAll(selector).forEach(el => {
                if (el.outerHTML !== html) {
                    el.outerHTML = html;
                }
            });
        }
        if (window.jQuery) {
            window.jQuery(document.body).trigger('wc_fragments_refreshed');
        }
    }
});
