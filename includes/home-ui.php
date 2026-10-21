<?php
/** Home presentation; native Woo data and recommendation engine. */
defined('ABSPATH') || exit;
add_filter('body_class', function ($classes) {
    if (is_front_page()) { $classes[] = 'paxig-reference'; $classes[] = 'paxig-home'; }
    return $classes;
});
add_action('wp_enqueue_scripts', function () {
    if (!is_front_page()) return;
    wp_enqueue_style('paxig-reference', get_template_directory_uri().'/assets/css/reference-ui.css', ['paxig-shoes-style'], filemtime(get_template_directory().'/assets/css/reference-ui.css'));
    wp_enqueue_style('paxig-home', get_template_directory_uri().'/assets/css/home-ui.css', ['paxig-reference'], filemtime(get_template_directory().'/assets/css/home-ui.css'));
    wp_enqueue_script('paxig-reference', get_template_directory_uri().'/assets/js/reference-ui.js', [], filemtime(get_template_directory().'/assets/js/reference-ui.js'), true);
    wp_enqueue_script('wc-cart-fragments');
}, 31);
// Shared shell loads after the existing store styles; the homepage rules are scoped.
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('paxig-energy', get_template_directory_uri().'/assets/css/sport-energy.css', ['paxig-shoes-style'], filemtime(get_template_directory().'/assets/css/sport-energy.css'));
}, 120);

function paxig_energy_mark() {
    echo '<svg class="energy-brand-mark" viewBox="0 0 40 40" width="32" height="32" fill="currentColor" aria-hidden="true" focusable="false"><g transform="rotate(-22 12 22)"><ellipse cx="12" cy="24" rx="5" ry="9"/><ellipse cx="8" cy="12" rx="2.3" ry="3.3"/><ellipse cx="13" cy="10" rx="2.1" ry="3"/><ellipse cx="17" cy="12" rx="1.7" ry="2.6"/><ellipse cx="20" cy="15" rx="1.3" ry="2"/></g><g transform="translate(17 -5) rotate(18 12 22)"><ellipse cx="12" cy="24" rx="5" ry="9"/><ellipse cx="7" cy="14" rx="1.3" ry="2"/><ellipse cx="10" cy="11" rx="1.7" ry="2.6"/><ellipse cx="14" cy="10" rx="2.1" ry="3"/><ellipse cx="19" cy="12" rx="2.3" ry="3.3"/></g></svg>';
}

function paxig_energy_image($key, $fallback) {
    $id = absint(get_theme_mod('paxig_energy_'.$key));
    return ($id ? wp_get_attachment_image_url($id, 'full') : false) ?: get_template_directory_uri().'/assets/images/'.$fallback;
}
add_action('customize_register', function ($customizer) {
    $customizer->add_section('paxig_energy', ['title'=>'PAXIG · Sport Energy images', 'priority'=>30]);
    foreach (['hero'=>'Homepage banner (wide)', 'lifestyle'=>'Lifestyle collection', 'running'=>'Running collection', 'basketball'=>'Basketball collection'] as $key=>$label) {
        $setting = 'paxig_energy_'.$key;
        $customizer->add_setting($setting, ['default'=>0, 'sanitize_callback'=>'absint']);
        $customizer->add_control(new WP_Customize_Media_Control($customizer, $setting, ['label'=>$label, 'section'=>'paxig_energy', 'mime_type'=>'image']));
    }
});
add_action('wp_body_open', function () {
    if (is_front_page()) echo '<div class="paxig-home-announcement">'.esc_html(paxig_shipping_summary()).'</div>';
});
function paxig_home_term($slug) {
    $term = get_term_by('slug', $slug, 'product_cat');
    if (!$term || is_wp_error($term) || !$term->count) return false;
    $url = get_term_link($term);
    return is_wp_error($url) ? false : ['term'=>$term, 'url'=>$url];
}
function paxig_home_products($args = []) {
    return array_values(array_filter(wc_get_products(array_merge(['status'=>'publish','visibility'=>'visible','limit'=>4,'orderby'=>'date','order'=>'DESC'], $args)), fn($p) => $p->is_visible()));
}
function paxig_home_viewed_ids() {
    // Same native cookie, newest first as Woo's recently viewed widget.
    $cookie = isset($_COOKIE['woocommerce_recently_viewed']) && is_string($_COOKIE['woocommerce_recently_viewed']) ? wp_unslash($_COOKIE['woocommerce_recently_viewed']) : '';
    return array_slice(array_reverse(array_unique(array_filter(array_map('absint', explode('|', $cookie))))), 0, 8);
}
function paxig_home_card($item) {
    global $product;
    $previous = $product; $product = $item;
    ?>
    <li class="paxig-home-product" data-product-id="<?php echo esc_attr($item->get_id()); ?>">
        <a href="<?php echo esc_url($item->get_permalink()); ?>" class="paxig-home-product-link">
            <div class="paxig-reference-image"><?php paxig_reference_badge(); echo $item->get_image('woocommerce_thumbnail', ['loading'=>'lazy']); ?></div>
            <?php paxig_reference_category(); ?>
            <h3 class="screen-reader-text"><?php echo esc_html($item->get_name()); ?></h3>
            <span class="paxig-home-product-price-row"><span class="price"><?php echo wp_kses_post($item->get_price_html()); ?></span><span class="paxig-home-product-heart" aria-hidden="true"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M20.8 8.7c0 5.2-8.8 11-8.8 11s-8.8-5.8-8.8-11A4.7 4.7 0 0 1 12 6.1a4.7 4.7 0 0 1 8.8 2.6Z"/></svg></span></span>
        </a>
    </li>
    <?php $product = $previous;
}
function paxig_home_product_section($id, $title, $items, $url = '', $rail = false, $note = '') {
    if (!$items) return;
    ?>
    <section class="paxig-home-section paxig-home-section--<?php echo esc_attr($id); ?>" id="<?php echo esc_attr($id); ?>" aria-labelledby="<?php echo esc_attr($id); ?>-title">
        <div class="paxig-home-section-head"><div><h2 id="<?php echo esc_attr($id); ?>-title"><?php echo esc_html($title); ?></h2><?php if ($note) : ?><p><?php echo esc_html($note); ?></p><?php endif; ?></div>
            <?php if ($url) : ?><a class="paxig-home-more" href="<?php echo esc_url($url); ?>">Shop All <span aria-hidden="true">↗</span></a><?php endif; ?>
        </div>
        <ul class="paxig-home-products <?php echo $rail ? 'paxig-home-rail' : ''; ?>" <?php if ($rail) echo 'tabindex="0" aria-label="'.esc_attr($title).'"'; ?>><?php foreach ($items as $item) paxig_home_card($item); ?></ul>
    </section>
    <?php
}
