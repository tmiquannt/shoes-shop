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
        remove_action('woocommerce_before_single_product_summary','woocommerce_show_product_sale_flash',10);
        remove_action('woocommerce_single_product_summary','paxig_shoes_product_subtitle',6);
        remove_action('woocommerce_single_product_summary','woocommerce_template_single_excerpt',20);
        remove_action('woocommerce_single_product_summary','paxig_shoes_product_details_in_summary',35);
        add_action('woocommerce_single_product_summary','paxig_reference_badge',2);
        add_action('woocommerce_single_product_summary','paxig_reference_category',3);
        add_action('woocommerce_single_product_summary', function () {
            global $product;
            $stock_html = wc_get_stock_html($product);
            $in_stock = $product->is_in_stock();
            $stock_text = $in_stock ? __( 'In Stock', 'paxig-shoes' ) : __( 'Out of Stock', 'paxig-shoes' );
            echo $stock_html ?: '<p class="stock '.($in_stock ? 'in-stock' : 'out-of-stock').'">'.esc_html($stock_text).'</p>';
            $color = $product->get_attribute('pa_color');
            $variation_attributes = $product->is_type('variable') ? $product->get_variation_attributes() : [];
            if ($color && !array_key_exists('pa_color', $variation_attributes)) {
                echo '<p class="paxig-reference-attribute">'.esc_html( sprintf( __( 'Color: %s', 'paxig-shoes' ), $color ) ).'</p>';
            }
        }, 15);
        add_action('woocommerce_single_product_summary', function () {
            echo '<div class="paxig-reference-policy">'.esc_html(paxig_shipping_summary()).' <a href="'.esc_url(home_url('/huong-dan/')).'">'.esc_html__( 'Delivery & Returns', 'paxig-shoes' ).'</a></div>';
        }, 34);
        add_action('woocommerce_single_product_summary','paxig_reference_details',35);
        add_filter('woocommerce_product_tabs', function ($tabs) { unset($tabs['additional_information']); return $tabs; }, 110);
    }
});
add_filter('gettext', function ($translation, $text, $domain) {
    if ($domain !== 'woocommerce' || !paxig_reference_page()) return $translation;
    $labels = ['Be the first to review “%s”'=>'Be the first to review “%s”', 'Be the first to review &ldquo;%s&rdquo;'=>'Be the first to review “%s”'];
    if (is_cart()) $labels['You may be interested in&hellip;'] = 'You May Also Like';
    return $labels[$text] ?? $translation;
}, 130, 3);
