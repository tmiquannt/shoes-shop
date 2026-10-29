<?php
/** Presentation of the approved PLP/PDP references; native Woo remains authoritative. */
defined('ABSPATH') || exit;

function paxig_reference_page() {
    return function_exists('is_shop') && (is_shop() || is_product_taxonomy() || is_product() || is_cart() || is_checkout() || is_account_page() || (is_search() && get_query_var('post_type') === 'product'));
}
add_filter('body_class', function ($classes) {
    if (paxig_reference_page()) $classes[] = 'paxig-reference';
    if (function_exists('is_cart') && (is_cart() || is_checkout() || is_account_page())) $classes[] = 'paxig-reference-commerce';
    return $classes;
});
add_action('wp_enqueue_scripts', function () {
    if (!paxig_reference_page()) return;
    wp_enqueue_style('paxig-reference', get_template_directory_uri().'/assets/css/reference-ui.css', ['paxig-shoes-style'], filemtime(get_template_directory().'/assets/css/reference-ui.css'));
    wp_enqueue_script('paxig-reference', get_template_directory_uri().'/assets/js/reference-ui.js', [], filemtime(get_template_directory().'/assets/js/reference-ui.js'), true);
    wp_enqueue_script('wc-cart-fragments');
}, 30);
add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    $fragments['span.paxig-cart-count'] = '<span class="paxig-cart-count">'.esc_html(WC()->cart ? WC()->cart->get_cart_contents_count() : 0).'</span>';
    return $fragments;
});

function paxig_reference_category_term() {
    global $product;
    if (!$product instanceof WC_Product) return null;
    $terms = wc_get_product_terms($product->get_id(), 'product_cat', ['fields'=>'all']);
    $collection = ['new-arrivals','best-sellers','sale','trending','men','women','kids','unisex','uncategorized'];
    foreach ($terms as $term) if (!in_array($term->slug, $collection, true)) return $term;
    return null;
}
function paxig_reference_category() {
    $term = paxig_reference_category_term();
    if ($term) echo '<p class="paxig-reference-category">'.esc_html($term->name).'</p>';
}
function paxig_reference_badge() {
    global $product;
    if (!$product instanceof WC_Product) return;
    if ($product->is_on_sale()) echo '<span class="paxig-reference-badge is-sale">'.esc_html__( 'Sale', 'paxig-shoes' ).'</span>';
    elseif (has_term('new-arrivals','product_cat',$product->get_id())) echo '<span class="paxig-reference-badge">'.esc_html__( 'New', 'paxig-shoes' ).'</span>';
    elseif (has_term('best-sellers','product_cat',$product->get_id())) echo '<span class="paxig-reference-badge">'.esc_html__( 'Curated Pick', 'paxig-shoes' ).'</span>';
}
function paxig_reference_details() {
    global $product;
    if (!$product instanceof WC_Product) return;
    echo '<div class="paxig-reference-accordions">';
    if ($product->get_description() || $product->get_short_description()) {
        echo '<details open><summary>'.esc_html__( 'Description', 'paxig-shoes' ).paxig_disclosure_icons().'</summary><div class="paxig-reference-content">';
        echo apply_filters('the_content', $product->get_description() ?: $product->get_short_description());
        echo '</div></details>';
    }
    if ($product->has_attributes() || $product->has_dimensions() || $product->has_weight()) {
        echo '<details><summary>'.esc_html__( 'Product Details', 'paxig-shoes' ).paxig_disclosure_icons().'</summary><div class="paxig-reference-content">';
        woocommerce_product_additional_information_tab();
        echo '</div></details>';
    }
    echo '<details><summary>'.esc_html__( 'Delivery', 'paxig-shoes' ).paxig_disclosure_icons().'</summary><div class="paxig-reference-content"><p>'.esc_html(paxig_shipping_summary()).'</p>';
    $guide_url = function_exists('paxig_storefront_page_url') ? paxig_storefront_page_url('guide') : '';
    if ($guide_url) echo '<p><a href="'.esc_url($guide_url).'">'.esc_html__( 'Read the shopping guide', 'paxig-shoes' ).'</a></p>';
    echo '</div></details></div>';
}
add_action('wp', function () {
    if (!paxig_reference_page()) return;
    if (function_exists('paxig_plp_page') && paxig_plp_page()) return;
    if (is_checkout() || is_account_page()) return;
    remove_action('woocommerce_before_shop_loop_item_title','woocommerce_show_product_loop_sale_flash',10);
    add_action('woocommerce_before_shop_loop_item_title', function () { echo '<div class="paxig-reference-image">'; paxig_reference_badge(); }, 9);
    add_action('woocommerce_before_shop_loop_item_title', function () { echo '</div>'; paxig_reference_category(); }, 21);
    if (is_cart()) return;
    if (!is_product()) {
        remove_action('woocommerce_before_shop_loop','woocommerce_result_count',20);
        add_action('woocommerce_before_shop_loop', function () {
            $total = wc_get_loop_prop('total');
            $count_text = sprintf( _n( '%s product', '%s products', $total, 'paxig-shoes' ), number_format_i18n( $total ) );
            echo '<p class="paxig-reference-count">'.esc_html($count_text).'</p>';
        }, 1);
    } else {
        remove_action('woocommerce_before_main_content','woocommerce_breadcrumb',20);
        add_action('woocommerce_before_single_product', function() {
            global $product;
            if (!$product) return;
            $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
            $home_label = $is_vi ? 'TRANG CHỦ' : 'HOME';
            $category = paxig_reference_category_term();
            $known_cats = [
                'lifestyle'  => $is_vi ? 'PHONG CÁCH' : 'LIFESTYLE',
                'running'    => $is_vi ? 'CHẠY BỘ' : 'RUNNING',
                'basketball' => $is_vi ? 'BÓNG RỔ' : 'BASKETBALL',
                'football'   => $is_vi ? 'BÓNG ĐÁ' : 'FOOTBALL',
                'training'   => $is_vi ? 'TẬP LUYỆN' : 'TRAINING',
            ];
            $category_label = $category ? ($known_cats[$category->slug] ?? strtoupper($category->name)) : $shoes_label;
            $category_url = $category ? get_term_link($category) : wc_get_page_permalink('shop');
            if (is_wp_error($category_url)) $category_url = wc_get_page_permalink('shop');
            echo '<nav class="vyre-breadcrumb vyre-pdp-breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'paxig-shoes' ) . '">';
            echo '<a href="' . esc_url( home_url('/') ) . '">' . esc_html($home_label) . '</a> <span class="vyre-sep">/</span> ';
            echo '<a href="' . esc_url( wc_get_page_permalink('shop') ) . '">' . esc_html($shoes_label) . '</a> <span class="vyre-sep">/</span> ';
            echo '<a href="' . esc_url( $category_url ) . '">' . esc_html($category_label) . '</a> <span class="vyre-sep">/</span> ';
            echo '<span>' . esc_html( strtoupper($product->get_name()) ) . '</span>';
            echo '</nav>';
        }, 5);
        remove_action('woocommerce_before_single_product_summary','woocommerce_show_product_sale_flash',10);
        remove_action('woocommerce_single_product_summary','paxig_shoes_product_subtitle',6);
        remove_action('woocommerce_single_product_summary','woocommerce_template_single_excerpt',20);
        remove_action('woocommerce_single_product_summary','paxig_shoes_product_details_in_summary',35);
        add_action('woocommerce_single_product_summary', function() {
            paxig_reference_category();
        }, 1);
        add_action('woocommerce_single_product_summary', function() {
            global $product;
            if (!$product instanceof WC_Product) return;
            $subtitle = trim(wp_trim_words(wp_strip_all_tags($product->get_short_description()), 24, ''));
            if ($subtitle) echo '<p class="vyre-pdp-subtitle">' . esc_html($subtitle) . '</p>';

            $review_count = $product->get_review_count();
            if ($review_count > 0) {
                $average_rating = (float) $product->get_average_rating();
                echo '<div class="vyre-pdp-rating">' . wp_kses_post(wc_get_rating_html($average_rating, $review_count));
                echo '<a class="vyre-rating-text" href="#reviews">' . esc_html(sprintf(_n('%1$s / 5 (%2$s review)', '%1$s / 5 (%2$s reviews)', $review_count, 'paxig-shoes'), number_format_i18n($average_rating, 1), number_format_i18n($review_count))) . '</a></div>';
            } elseif (comments_open($product->get_id()) && get_option('woocommerce_enable_reviews') === 'yes') {
                echo '<p class="vyre-pdp-rating"><a class="vyre-rating-text" href="#reviews">' . esc_html__( 'Be the first to review this product', 'paxig-shoes' ) . '</a></p>';
            }
        }, 6);
        add_action('woocommerce_single_product_summary', function () {
            global $product;
            if (!$product instanceof WC_Product) return;
            $color = trim(wp_strip_all_tags($product->get_attribute('pa_color')));
            if ($color) {
                $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
                $color_label = $is_vi ? sprintf('MÀU SẮC: %s', $color) : sprintf(__('COLOR: %s', 'paxig-shoes'), $color);
                echo '<div class="vyre-pdp-colors-section"><div class="vyre-pdp-color-head"><span class="vyre-pdp-color-label">' . esc_html($color_label) . '</span></div></div>';
            }
            echo '<div class="vyre-pdp-size-head">
                <span class="vyre-pdp-size-title">' . esc_html__( 'SELECT SIZE', 'paxig-shoes' ) . '</span>
                <button type="button" class="vyre-size-guide-trigger" aria-controls="vyre-size-guide-modal">' . paxig_icon_text(esc_html__( 'SIZE GUIDE ↗', 'paxig-shoes' )) . '</button>
            </div>';
        }, 15);
        add_action('woocommerce_after_add_to_cart_form', function () {
            echo '<div class="vyre-pdp-trust-badges">
                <div class="vyre-trust-item"><span class="vyre-trust-icon">' . paxig_icon('truck', 22) . '</span><span>' . esc_html__( 'Shipping options confirmed at checkout', 'paxig-shoes' ) . '</span></div>
                <div class="vyre-trust-item"><span class="vyre-trust-icon">' . paxig_icon('ruler', 22) . '</span><span>' . esc_html__( 'Size guide available', 'paxig-shoes' ) . '</span></div>
            </div>';
        }, 25);
        add_action('woocommerce_single_product_summary','paxig_reference_details',35);
        add_filter('woocommerce_product_tabs', function ($tabs) {
            unset($tabs['additional_information']);
            unset($tabs['description']);
            return $tabs;
        }, 110);
    }
});
add_filter('woocommerce_product_related_products_heading', function() {
    return __( 'YOU MIGHT ALSO LIKE', 'paxig-shoes' );
});
add_action('wp_footer', function() {
    if (!is_product()) return;
    ?>
    <div id="vyre-size-guide-modal" class="vyre-size-guide-modal" aria-hidden="true">
        <div class="vyre-size-guide-backdrop"></div>
        <div class="vyre-size-guide-dialog" role="dialog" aria-modal="true" aria-labelledby="vyre-size-guide-title">
            <button type="button" class="vyre-size-guide-close" aria-label="<?php echo esc_attr__( 'Close size guide', 'paxig-shoes' ); ?>"><?php echo paxig_icon('x', 22); ?></button>
            <span class="vyre-size-guide-eyebrow"><?php echo esc_html__( 'YOUR PERFECT FIT', 'paxig-shoes' ); ?></span>
            <h2 id="vyre-size-guide-title" class="vyre-size-guide-title"><?php echo esc_html__( 'SIZE GUIDE.', 'paxig-shoes' ); ?></h2>
            <p class="vyre-size-guide-desc"><?php echo esc_html__( 'Measure your foot from heel to toe. If you\'re between sizes, we recommend sizing up for all-day comfort.', 'paxig-shoes' ); ?></p>
            <div class="vyre-size-table-wrap">
                <table class="vyre-size-table">
                    <thead>
                        <tr><th>US</th><th>EU</th><th>UK</th><th><?php echo esc_html__( 'FOOT LENGTH (CM)', 'paxig-shoes' ); ?></th></tr>
                    </thead>
                    <tbody>
                        <tr><td>7</td><td>40</td><td>6</td><td>25.0</td></tr>
                        <tr><td>8</td><td>41</td><td>7</td><td>25.6</td></tr>
                        <tr><td>9</td><td>42</td><td>8</td><td>26.3</td></tr>
                        <tr><td>10</td><td>43</td><td>9</td><td>26.9</td></tr>
                        <tr><td>11</td><td>44</td><td>10</td><td>27.6</td></tr>
                        <tr><td>12</td><td>45</td><td>11</td><td>28.2</td></tr>
                    </tbody>
                </table>
            </div>
            <button type="button" class="vyre-size-guide-gotit"><?php echo paxig_icon_text(esc_html__( 'GOT IT ✓', 'paxig-shoes' )); ?></button>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const triggers = document.querySelectorAll('.vyre-size-guide-trigger');
        const modal = document.querySelector('#vyre-size-guide-modal');
        if (modal) {
            const closeBtn = modal.querySelector('.vyre-size-guide-close');
            const backdrop = modal.querySelector('.vyre-size-guide-backdrop');
            const gotIt = modal.querySelector('.vyre-size-guide-gotit');
            function openModal() { modal.classList.add('is-open'); modal.setAttribute('aria-hidden','false'); }
            function closeModal() { modal.classList.remove('is-open'); modal.setAttribute('aria-hidden','true'); }
            triggers.forEach(t => t.addEventListener('click', openModal));
            if (closeBtn) closeBtn.addEventListener('click', closeModal);
            if (backdrop) backdrop.addEventListener('click', closeModal);
            if (gotIt) gotIt.addEventListener('click', closeModal);
        }
    });
    </script>
    <?php
});
add_filter('gettext', function ($translation, $text, $domain) {
    if ($domain !== 'woocommerce' || !paxig_reference_page()) return $translation;
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    if ($is_vi) {
        $labels = [
            'Be the first to review “%s”'              => 'Hãy là người đầu tiên đánh giá “%s”',
            'Be the first to review &ldquo;%s&rdquo;'  => 'Hãy là người đầu tiên đánh giá “%s”',
        ];
        if (is_cart()) $labels['You may be interested in&hellip;'] = 'Có thể bạn sẽ thích…';
        return $labels[$text] ?? $translation;
    }
    $labels = ['Be the first to review “%s”'=>'Be the first to review “%s”', 'Be the first to review &ldquo;%s&rdquo;'=>'Be the first to review “%s”'];
    if (is_cart()) $labels['You may be interested in&hellip;'] = 'You May Also Like';
    return $labels[$text] ?? $translation;
}, 130, 3);
