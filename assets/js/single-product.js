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
                button.setAttribute('aria-label', button.textContent.trim() + (available ? '' : ' — Out of stock or unavailable'));
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
});
