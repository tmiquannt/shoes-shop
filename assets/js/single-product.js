document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.paxig-size-picker').forEach(function (picker) {
        const select = picker.querySelector('select'), form = picker.closest('form');
        if (!select || !form) return;
        function sync() {
            const variations = window.jQuery ? window.jQuery(form).data('product_variations') : null;
            picker.querySelectorAll('.paxig-size-button').forEach(function (button) {
                const option = Array.from(select.options).find(o => o.value === button.dataset.value);
                let available = !!option && !option.disabled;
                if (Array.isArray(variations)) {
                    available = variations.some(v => v.is_in_stock && v.variation_is_active && Object.entries(v.attributes).every(([name,value]) => {
                        const field = form.elements[name];
                        const chosen = name === select.name ? button.dataset.value : field?.value;
                        return !value || !chosen || value === chosen;
                    }));
                }
                button.disabled = !available;
                const selected = available && button.dataset.value === select.value;
                button.classList.toggle('is-selected', selected);
                button.setAttribute('aria-pressed', String(selected));
                button.setAttribute('aria-label', button.textContent.trim() + (available ? '' : ((window.paxigSingleProduct && window.paxigSingleProduct.i18n && window.paxigSingleProduct.i18n.unavailable_suffix) || ' — Out of stock or unavailable')));
            });
        }
        form.addEventListener('change', sync);
        if (window.jQuery) window.jQuery(form).on('woocommerce_update_variation_values found_variation reset_data', sync);
        picker.addEventListener('click', function (event) {
            const button = event.target.closest('.paxig-size-button');
            if (!button || button.disabled) return;
            select.value = button.dataset.value;
            if (window.jQuery) window.jQuery(select).trigger('change');
            else select.dispatchEvent(new Event('change', {bubbles:true}));
            sync();
        });
        sync();
    });

    // Mobile Sticky Buy Bar initialization
    const mainBuyBtn = document.querySelector('.single_add_to_cart_button');
    const productTitle = document.querySelector('.product_title');
    const priceElem = document.querySelector('.summary .price');

    if (mainBuyBtn && productTitle && window.innerWidth <= 900) {
        const addToBagText = (window.paxigSingleProduct && window.paxigSingleProduct.i18n && window.paxigSingleProduct.i18n.add_to_bag) || 'Add to Bag';
        const bar = document.createElement('div');
        bar.className = 'paxig-sticky-buy-bar';
        bar.innerHTML = `
            <div class="paxig-sticky-buy-bar__info">
                <p class="paxig-sticky-buy-bar__title"></p>
                <span class="paxig-sticky-buy-bar__price">${priceElem ? priceElem.innerHTML : ''}</span>
            </div>
            <button type="button" class="paxig-sticky-buy-bar__btn">${addToBagText}</button>
        `;
        const barTitle = bar.querySelector('.paxig-sticky-buy-bar__title');
        if (barTitle) {
            barTitle.textContent = productTitle.textContent.trim();
        }
        document.body.appendChild(bar);

        const stickyBtn = bar.querySelector('.paxig-sticky-buy-bar__btn');
        stickyBtn.addEventListener('click', function () {
            // If picker exists and no size is chosen, scroll to picker
            const unselectedPicker = document.querySelector('.paxig-size-picker');
            const hiddenSelect = unselectedPicker?.querySelector('select');
            if (hiddenSelect && !hiddenSelect.value) {
                unselectedPicker.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            mainBuyBtn.click();
        });

        // Toggle visibility with IntersectionObserver or Scroll
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(function (entries) {
                entries.forEach(entry => {
                    bar.classList.toggle('is-visible', !entry.isIntersecting);
                });
            }, { threshold: 0.1 });
            observer.observe(mainBuyBtn);
        } else {
            window.addEventListener('scroll', function () {
                const rect = mainBuyBtn.getBoundingClientRect();
                bar.classList.toggle('is-visible', rect.bottom < 0);
            }, { passive: true });
        }
    }

    // AJAX Add-to-Cart for Single Product Form (Prevents page reload on PDP)
    const cartForm = document.querySelector('form.cart');
    if (cartForm) {
        cartForm.addEventListener('submit', function (event) {
            const picker = cartForm.querySelector('.paxig-size-picker');
            const select = picker ? picker.querySelector('select') : null;
            if (select && !select.value) {
                event.preventDefault();
                picker.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            event.preventDefault();
            if (cartForm.classList.contains('is-submitting')) return;

            const submitBtn = event.submitter || cartForm.querySelector('.single_add_to_cart_button');
            const stickyBtn = document.querySelector('.paxig-sticky-buy-bar__btn');
            cartForm.classList.add('is-submitting');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('loading');
            }
            if (stickyBtn) {
                stickyBtn.disabled = true;
                stickyBtn.classList.add('loading');
            }

            const formData = new FormData(cartForm);
            if (submitBtn && submitBtn.name && submitBtn.value && !formData.has(submitBtn.name)) {
                formData.append(submitBtn.name, submitBtn.value);
            }
            if (!formData.has('product_id') && formData.has('add-to-cart')) {
                formData.append('product_id', formData.get('add-to-cart'));
            }
            if (!formData.has('action')) {
                formData.append('action', 'paxig_ajax_add_to_cart');
            }
            if (window.paxigMiniCart && window.paxigMiniCart.nonce && !formData.has('nonce')) {
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
                    const fragments = response.data.fragments;
                    const cartHash = response.data.cart_hash;

                    if (typeof CustomEvent === 'function') {
                        document.body.dispatchEvent(new CustomEvent('paxig_added_to_cart', {
                            detail: response.data
                        }));
                    }

                    if (window.jQuery) {
                        window.jQuery(document.body).trigger('added_to_cart', [fragments, cartHash, window.jQuery(submitBtn)]);
                        window.jQuery(document.body).trigger('wc_fragment_refresh');
                    }
                } else {
                    const msg = (response.data && response.data.message)
                        || (window.paxigSingleProduct && window.paxigSingleProduct.i18n && window.paxigSingleProduct.i18n.add_to_cart_error)
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
            .catch(function (err) {
                console.error('Add to cart request failed:', err);
                cartForm.submit();
            })
            .finally(function () {
                cartForm.classList.remove('is-submitting');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('loading');
                }
                if (stickyBtn) {
                    stickyBtn.disabled = false;
                    stickyBtn.classList.remove('loading');
                }
            });
        });
    }
});
