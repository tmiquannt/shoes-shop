<?php
/** Shared archive presentation. Native Woo query/loop/filter widgets stay authoritative. */
defined('ABSPATH') || exit;
function paxig_plp_page() {
    return function_exists('is_shop') && (is_shop() || is_product_taxonomy());
}
function paxig_plp_profile() {
    $slug = is_product_category() ? get_queried_object()->slug : '';
    if (in_array($slug, ['men','women','kids'], true)) return $slug;
    $gender = isset($_GET['filter_gender']) && is_string($_GET['filter_gender']) ? explode(',', wc_clean(wp_unslash($_GET['filter_gender']))) : [];
    foreach (['men','women','kids'] as $profile) if (in_array($profile, $gender, true)) return $profile;
    return '';
}
function paxig_plp_args() {
    $args = [];
    foreach (wp_unslash($_GET) as $key=>$value) {
        if (!is_string($value)) continue;
        if (preg_match('/^(filter_|query_type_)/', $key) || in_array($key,['orderby','min_price','max_price','rating_filter','paxig_sale','paxig_best_sellers','s','post_type'],true)) $args[sanitize_key($key)] = wc_clean($value);
    }
    return $args;
}
function paxig_plp_term_url($term) {
    $url = get_term_link($term);
    if (is_wp_error($url)) return '';
    return add_query_arg(paxig_plp_args(), $url);
}
function paxig_plp_terms($slugs) {
    $terms = [];
    foreach ($slugs as $slug) {
        $term = get_term_by('slug',$slug,'product_cat');
        if ($term && !is_wp_error($term) && $term->count && paxig_plp_term_url($term)) $terms[] = $term;
    }
    return $terms;
}
function paxig_plp_reset_url() {
    $args = paxig_plp_args();
    // Reset refinements while keeping the archive audience and chosen sort.
    $args = array_intersect_key($args, array_flip(['filter_gender','query_type_gender','orderby']));
    return add_query_arg($args, wc_get_page_permalink('shop'));
}
add_filter('body_class', function ($classes) { if (paxig_plp_page()) $classes[]='paxig-plp'; return $classes; });
add_action('wp_enqueue_scripts', function () {
    if (!paxig_plp_page()) return;
    wp_enqueue_style('paxig-plp',get_template_directory_uri().'/assets/css/plp-ui.css',['paxig-reference'],filemtime(get_template_directory().'/assets/css/plp-ui.css'));
    wp_enqueue_script('paxig-plp',get_template_directory_uri().'/assets/js/plp-ui.js',[],filemtime(get_template_directory().'/assets/js/plp-ui.js'),true);
    wp_localize_script('paxig-plp', 'paxigPlpLabels', [
        'filters'      => __( 'Filters', 'paxig-shoes' ),
        'hide_filters' => __( 'Hide Filters', 'paxig-shoes' ),
        'show_filters' => __( 'Show Filters', 'paxig-shoes' ),
        'show_more'    => __( '+ Show more', 'paxig-shoes' ),
        'show_less'    => __( '− Show less', 'paxig-shoes' ),
        'sort_by'      => __( 'Sort By: ', 'paxig-shoes' ),
    ]);
}, 31);
add_filter('woocommerce_page_title',function ($title) {
    if (!paxig_plp_page()) return $title;
    return [
        'men'   => __( 'Men’s Shoes', 'paxig-shoes' ),
        'women' => __( 'Women’s Shoes', 'paxig-shoes' ),
        'kids'  => __( 'Kids’ Shoes', 'paxig-shoes' ),
    ][paxig_plp_profile()] ?? $title;
}, 120);
function paxig_plp_header() {
    $profile = paxig_plp_profile();
    $slugs = ['men'=>['lifestyle','running','basketball','football','training','jordan'],'women'=>['lifestyle','running','training','jordan','dunk','air-max'],'kids'=>['lifestyle','running','basketball','football','jordan']][$profile] ?? ['lifestyle','running','basketball','football','training'];
    $total = wc_get_loop_prop('total');
    $count_text = sprintf( _n( '%s product', '%s products', $total, 'paxig-shoes' ), number_format_i18n( $total ) );
    echo '<header class="woocommerce-products-header paxig-plp-heading"><div class="paxig-plp-title-row"><h1 class="woocommerce-products-header__title page-title">'.esc_html(woocommerce_page_title(false)).'</h1><p class="paxig-plp-count">'.esc_html($count_text).'</p></div>';
    do_action('woocommerce_archive_description');
    $terms = paxig_plp_terms($slugs);
    if ($terms) {
        echo '<nav class="paxig-plp-quick" aria-label="'.esc_attr__( 'Explore categories', 'paxig-shoes' ).'"><ul>';
        foreach ($terms as $term) echo '<li><a '.(is_product_category($term->slug) ? 'aria-current="page" ' : '').'href="'.esc_url(paxig_plp_term_url($term)).'">'.esc_html($term->name).'</a></li>';
        echo '</ul></nav>';
    }
    echo '</header>';
}
function paxig_plp_toolbar() {
    echo '<div class="paxig-plp-toolbar"><button class="paxig-plp-filter-toggle" type="button" aria-controls="paxig-plp-filters" aria-expanded="true"><span>'.esc_html__( 'Hide Filters', 'paxig-shoes' ).'</span><svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M4 7h16M4 17h16M8 4v6M16 14v6"/></svg></button><div class="paxig-plp-sort">';
    woocommerce_catalog_ordering();
    echo '</div></div>';
}
function paxig_plp_group($id,$title,$content,$open=true) {
    if (!trim($content)) return;
    echo '<details class="paxig-plp-group paxig-plp-group-'.esc_attr($id).'" '.($open?'open':'').'><summary>'.esc_html($title).'</summary><div class="paxig-plp-group-body">'.$content.'</div></details>';
}
function paxig_plp_term_group($id,$title,$slugs) {
    $terms=paxig_plp_terms($slugs);
    if (!$terms) return;
    $html='<ul class="paxig-plp-term-list">';
    foreach ($terms as $term) $html.='<li><a '.(is_product_category($term->slug)?'aria-current="page" ':'').'href="'.esc_url(paxig_plp_term_url($term)).'">'.esc_html($term->name).'</a></li>';
    paxig_plp_group($id,$title,$html.'</ul>');
}
function paxig_plp_widget($class,$instance) {
    if (!class_exists($class)) return '';
    ob_start();
    the_widget($class,$instance,['before_widget'=>'<div class="paxig-plp-native-widget">','after_widget'=>'</div>','before_title'=>'<span class="screen-reader-text">','after_title'=>'</span>']);
    return ob_get_clean();
}
function paxig_plp_layout_open() {
    echo '<div class="paxig-shop-layout paxig-plp-layout"><aside class="paxig-shop-sidebar" id="paxig-plp-filters" aria-label="'.esc_attr__( 'Product filters', 'paxig-shoes' ).'"><div class="paxig-plp-drawer-head"><h2 id="paxig-plp-drawer-title">'.esc_html__( 'Filters', 'paxig-shoes' ).'</h2><button type="button" class="paxig-plp-drawer-close" aria-label="'.esc_attr__( 'Close filters', 'paxig-shoes' ).'">×</button></div><div class="paxig-plp-filter-content">';
    paxig_plp_term_group('categories', __( 'Category', 'paxig-shoes' ), ['lifestyle','running','basketball','football','training']);
    foreach (['gender'=>__( 'Gender', 'paxig-shoes' ),'sport'=>__( 'Sport', 'paxig-shoes' ),'kich-thuoc'=>__( 'Size', 'paxig-shoes' ),'color'=>__( 'Color', 'paxig-shoes' )] as $attribute=>$label) {
        paxig_plp_group($attribute,$label,paxig_plp_widget('WC_Widget_Layered_Nav',['title'=>'','attribute'=>$attribute,'display_type'=>'list','query_type'=>'or']));
    }
    paxig_plp_group('price', __( 'Price', 'paxig-shoes' ), paxig_plp_widget('WC_Widget_Price_Filter',['title'=>'']));
    paxig_plp_term_group('collections', __( 'Collection', 'paxig-shoes' ), ['air-force-1','jordan','air-max','dunk','cortez','pegasus','vomero','revolution','metcon','mercurial']);
    // Existing sale filter is supported only on the native shop archive.
    if (is_shop()) {
        $args=paxig_plp_args(); $selected=isset($args['paxig_sale']) && $args['paxig_sale']==='1';
        if ($selected) unset($args['paxig_sale']); else $args['paxig_sale']='1';
        paxig_plp_group('sale', __( 'Sale', 'paxig-shoes' ), '<a class="paxig-plp-sale-link" '.($selected?'aria-current="true" ':'').'href="'.esc_url(add_query_arg($args,wc_get_page_permalink('shop'))).'">'.esc_html__( 'Sale items', 'paxig-shoes' ).'</a>');
    }
    echo '</div><div class="paxig-plp-drawer-actions"><a class="paxig-plp-reset" href="'.esc_url(paxig_plp_reset_url()).'">'.esc_html__( 'Clear Filters', 'paxig-shoes' ).'</a><button class="paxig-plp-drawer-view" type="button">'.esc_html__( 'View Results', 'paxig-shoes' ).'</button></div></aside><div class="paxig-shop-products">';
}
function paxig_plp_layout_close() { echo '</div></div>'; }
function paxig_plp_empty_reset() { echo '<p class="paxig-plp-empty-reset"><a class="button" href="'.esc_url(paxig_plp_reset_url()).'">'.esc_html__( 'Clear Filters', 'paxig-shoes' ).'</a></p>'; }
add_action('wp',function () {
    if (!paxig_plp_page()) return;
    remove_action('woocommerce_shop_loop_header','woocommerce_product_taxonomy_archive_header',10);
    add_action('woocommerce_shop_loop_header','paxig_plp_header',10);
    remove_action('woocommerce_before_shop_loop','paxig_shoes_shop_layout_open',5);
    remove_action('woocommerce_after_shop_loop','paxig_shoes_shop_layout_close',99);
    remove_action('woocommerce_before_shop_loop','woocommerce_result_count',20);
    remove_action('woocommerce_before_shop_loop','woocommerce_catalog_ordering',30);
    add_action('woocommerce_before_shop_loop','paxig_plp_toolbar',20);
    add_action('woocommerce_before_shop_loop','paxig_plp_layout_open',40);
    add_action('woocommerce_after_shop_loop','paxig_plp_layout_close',99);
    add_action('woocommerce_no_products_found','paxig_plp_toolbar',1);
    add_action('woocommerce_no_products_found','paxig_plp_layout_open',5);
    add_action('woocommerce_no_products_found','paxig_plp_empty_reset',21);
    add_action('woocommerce_no_products_found','paxig_plp_layout_close',99);
    remove_action('woocommerce_before_shop_loop_item_title','woocommerce_show_product_loop_sale_flash',10);
    remove_action('woocommerce_after_shop_loop_item','woocommerce_template_loop_add_to_cart',10);
    add_action('woocommerce_before_shop_loop_item_title',function(){echo '<div class="paxig-reference-image">';},9);
    add_action('woocommerce_before_shop_loop_item_title',function(){echo '</div>';paxig_reference_badge();},21);
    add_action('woocommerce_shop_loop_item_title','paxig_reference_category',11);
},40);
add_filter('woocommerce_layered_nav_term_html',function($html,$term,$link,$count){
    if (!paxig_plp_page()) return $html;
    $chosen=WC_Query::get_layered_nav_chosen_attributes();
    if (in_array($term->slug,$chosen[$term->taxonomy]['terms'] ?? [],true)) $html=str_replace('<a ','<a aria-current="true" ',$html);
    $colors=['red-black'=>'linear-gradient(135deg,#b32928 50%,#111 50%)','neutral'=>'linear-gradient(135deg,#fff 50%,#999 50%)','lime-black'=>'linear-gradient(135deg,#7eb832 50%,#111 50%)'];
    if ($term->taxonomy==='pa_color' && isset($colors[$term->slug])) $html=preg_replace('/(<a\b[^>]*>)/','$1<span class="paxig-plp-swatch" aria-hidden="true" style="background:'.esc_attr($colors[$term->slug]).'"></span>',$html,1);
    return $html;
},10,4);
// Keep existing collection refinements when native widgets build their URLs.
add_filter('woocommerce_widget_get_current_page_url',function($url){
    if (!paxig_plp_page()) return $url;
    foreach (['paxig_sale','paxig_best_sellers'] as $key) {
        $args=paxig_plp_args();
        if (isset($args[$key])) $url=add_query_arg($key,$args[$key],$url);
    }
    return $url;
});
add_filter('gettext',function($translated,$text,$domain){
    if ($domain!=='woocommerce'||!paxig_plp_page()) return $translated;
    return ['Filter'=>'Apply Price','Price:'=>'Price:'][$text] ?? $translated;
},140,3);
