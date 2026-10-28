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
    wp_enqueue_script('paxig-vyre-hero-slideshow', get_template_directory_uri().'/assets/js/vyre-hero-slideshow.js', [], filemtime(get_template_directory().'/assets/js/vyre-hero-slideshow.js'), true);
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
    $customizer->add_section('paxig_social', ['title'=>'PAXIG · Social Links', 'priority'=>31]);
    $customizer->add_setting('paxig_instagram_url', ['default'=>'', 'sanitize_callback'=>'esc_url_raw']);
    $customizer->add_control('paxig_instagram_url', [
        'label'   => 'Instagram profile URL',
        'section' => 'paxig_social',
        'type'    => 'url',
    ]);
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
    get_template_part('template-parts/product-card', null, ['product' => $item]);
}
function paxig_home_product_section($id, $title, $items, $url = '', $rail = false, $note = '') {
    if (!$items) return;
    ?>
    <section class="paxig-home-section paxig-home-section--<?php echo esc_attr($id); ?>" id="<?php echo esc_attr($id); ?>" aria-labelledby="<?php echo esc_attr($id); ?>-title">
        <div class="paxig-home-section-head"><div><h2 id="<?php echo esc_attr($id); ?>-title"><?php echo esc_html($title); ?></h2><?php if ($note) : ?><p><?php echo esc_html($note); ?></p><?php endif; ?></div>
            <?php if ($url) : ?><a class="paxig-home-more" href="<?php echo esc_url($url); ?>"><?php echo esc_html__( 'Shop All', 'paxig-shoes' ); ?> <?php echo paxig_icon('arrow-up-right', 18); ?></a><?php endif; ?>
        </div>
        <ul class="paxig-home-products <?php echo $rail ? 'paxig-home-rail' : ''; ?>" <?php if ($rail) echo 'tabindex="0" aria-label="'.esc_attr($title).'"'; ?>><?php foreach ($items as $item) paxig_home_card($item); ?></ul>
    </section>
    <?php
}
