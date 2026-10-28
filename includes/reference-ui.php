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

function paxig_reference_category() {
    global $product;
    if (!$product instanceof WC_Product) return;
    $terms = wc_get_product_terms($product->get_id(), 'product_cat', ['fields'=>'all']);
    $collection = ['new-arrivals','best-sellers','sale','trending','men','women','kids','unisex','uncategorized'];
    $names = [];
    foreach ($terms as $term) if (!in_array($term->slug, $collection, true)) $names[] = $term->name;
    if ($names) echo '<p class="paxig-reference-category">'.esc_html($names[0]).'</p>';
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
        echo '<details open><summary>'.esc_html__( 'Description', 'paxig-shoes' ).'</summary><div class="paxig-reference-content">';
        echo apply_filters('the_content', $product->get_description() ?: $product->get_short_description());
        echo '</div></details>';
    }
    if ($product->has_attributes() || $product->has_dimensions() || $product->has_weight()) {
        echo '<details><summary>'.esc_html__( 'Product Details', 'paxig-shoes' ).'</summary><div class="paxig-reference-content">';
        woocommerce_product_additional_information_tab();
        echo '</div></details>';
    }
    echo '<details><summary>'.esc_html__( 'Delivery & Returns', 'paxig-shoes' ).'</summary><div class="paxig-reference-content"><p>'.esc_html(paxig_shipping_summary()).'</p><p><a href="'.esc_url(home_url('/huong-dan/')).'">'.esc_html__( 'Read the shopping, cancellation and returns guide', 'paxig-shoes' ).'</a></p></div></details></div>';
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
            $cat = 'LIFESTYLE';
            $terms = wc_get_product_terms($product->get_id(), 'product_cat', ['fields'=>'names']);
            if (!empty($terms)) $cat = strtoupper($terms[0]);
            $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
            $home_label = $is_vi ? 'TRANG CHỦ' : 'HOME';
            $shoes_label = $is_vi ? 'GIÀY SNEAKER' : 'SNEAKERS';
            echo '<nav class="vyre-breadcrumb vyre-pdp-breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'paxig-shoes' ) . '">';
            echo '<a href="' . esc_url( home_url('/') ) . '">' . esc_html($home_label) . '</a> <span class="vyre-sep">/</span> ';
            echo '<a href="' . esc_url( wc_get_page_permalink('shop') ) . '">' . esc_html($shoes_label) . '</a> <span class="vyre-sep">/</span> ';
            echo '<a href="' . esc_url( wc_get_page_permalink('shop') ) . '">' . esc_html($cat) . '</a> <span class="vyre-sep">/</span> ';
            echo '<span>' . esc_html( strtoupper($product->get_name()) ) . '</span>';
            echo '</nav>';
        }, 5);
        remove_action('woocommerce_before_single_product_summary','woocommerce_show_product_sale_flash',10);
        remove_action('woocommerce_single_product_summary','paxig_shoes_product_subtitle',6);
        remove_action('woocommerce_single_product_summary','woocommerce_template_single_excerpt',20);
        remove_action('woocommerce_single_product_summary','paxig_shoes_product_details_in_summary',35);
        add_action('woocommerce_single_product_summary', function() {
            echo '<span class="vyre-pdp-eyebrow">' . esc_html__( 'LIFESTYLE / NEW SEASON', 'paxig-shoes' ) . '</span>';
        }, 1);
        add_action('woocommerce_single_product_summary', function() {
            echo '<p class="vyre-pdp-subtitle">' . esc_html__( 'Sandstone / Everyday sneaker', 'paxig-shoes' ) . '</p>';
            $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
            $rating_aria = $is_vi ? 'Đánh giá 4.8 trên 5 sao' : 'Rated 4.8 out of 5 stars';
            $rating_text = $is_vi ? '4.8 / 5 (320 ĐÁNH GIÁ)' : '4.8 / 5 (320 REVIEWS)';
            echo '<div class="vyre-pdp-rating" aria-label="' . esc_attr($rating_aria) . '">
                <span class="vyre-stars">★★★★★</span>
                <span class="vyre-rating-text">' . esc_html($rating_text) . '</span>
            </div>';
        }, 6);
        add_action('woocommerce_single_product_summary', function () {
            echo '<div class="vyre-pdp-colors-section">
                <div class="vyre-pdp-color-head">
                    <span class="vyre-pdp-color-label">' . esc_html__( 'COLOR: SANDSTONE', 'paxig-shoes' ) . '</span>
                    <span class="vyre-pdp-color-count">' . esc_html__( '4 AVAILABLE COLORS', 'paxig-shoes' ) . '</span>
                </div>
                <div class="vyre-pdp-color-swatches" aria-hidden="true">
                    <span class="vyre-pdp-swatch is-active" style="background:#c8b79b" title="Sandstone"></span>
                    <span class="vyre-pdp-swatch" style="background:#525844" title="Olive"></span>
                    <span class="vyre-pdp-swatch" style="background:#151514" title="Onyx"></span>
                    <span class="vyre-pdp-swatch" style="background:#eae6df" title="Cream"></span>
                </div>
            </div>';
            echo '<div class="vyre-pdp-size-head">
                <span class="vyre-pdp-size-title">' . esc_html__( 'SELECT SIZE', 'paxig-shoes' ) . '</span>
                <button type="button" class="vyre-size-guide-trigger" aria-controls="vyre-size-guide-modal">' . esc_html__( 'SIZE GUIDE ↗', 'paxig-shoes' ) . '</button>
            </div>';
        }, 15);
        add_action('woocommerce_after_add_to_cart_form', function () {
            echo '<div class="vyre-pdp-trust-badges">
                <div class="vyre-trust-item"><span class="vyre-trust-icon">🚚</span><span>' . esc_html__( 'FREE SHIPPING OVER $150', 'paxig-shoes' ) . '</span></div>
                <div class="vyre-trust-item"><span class="vyre-trust-icon">↺</span><span>' . esc_html__( '30-DAY RETURNS', 'paxig-shoes' ) . '</span></div>
                <div class="vyre-trust-item"><span class="vyre-trust-icon">🛡</span><span>' . esc_html__( 'SECURE CHECKOUT', 'paxig-shoes' ) . '</span></div>
            </div>';
        }, 25);
        add_action('woocommerce_single_product_summary','paxig_reference_details',35);
        add_filter('woocommerce_product_tabs', function ($tabs) {
            unset($tabs['additional_information']);
            unset($tabs['description']);
            unset($tabs['reviews']);
            return $tabs;
        }, 110);
    }
});
add_filter('woocommerce_product_related_products_heading', function() {
    return __( 'YOU MIGHT ALSO LIKE.', 'paxig-shoes' );
});
add_action('wp_footer', function() {
    if (!is_product()) return;
    ?>
    <div id="vyre-size-guide-modal" class="vyre-size-guide-modal" aria-hidden="true">
        <div class="vyre-size-guide-backdrop"></div>
        <div class="vyre-size-guide-dialog" role="dialog" aria-modal="true" aria-labelledby="vyre-size-guide-title">
            <button type="button" class="vyre-size-guide-close" aria-label="<?php echo esc_attr__( 'Close size guide', 'paxig-shoes' ); ?>">×</button>
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
            <button type="button" class="vyre-size-guide-gotit"><?php echo esc_html__( 'GOT IT ✓', 'paxig-shoes' ); ?></button>
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
