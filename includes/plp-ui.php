<?php
/** Shared archive presentation. Native Woo query/loop/filter widgets stay authoritative. */
defined('ABSPATH') || exit;
function paxig_plp_product_search_page() {
    if (!function_exists('is_search') || !is_search()) return false;
    $post_type = get_query_var('post_type');
    return $post_type === 'product' || (is_array($post_type) && in_array('product', $post_type, true));
}
function paxig_plp_page() {
    $is_product_archive = function_exists('is_shop') && (is_shop() || is_product_taxonomy());
    return $is_product_archive || paxig_plp_product_search_page();
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
        if (preg_match('/^(filter_|query_type_)/', $key) || in_array($key,['orderby','min_price','max_price','rating_filter','paxig_sale','paxig_best_sellers','paxig_releases','paxig_categories','s','post_type'],true)) $args[sanitize_key($key)] = wc_clean($value);
    }
    return $args;
}
function paxig_plp_catalog_url($args = []) {
    $url = function_exists('get_post_type_archive_link') ? get_post_type_archive_link('product') : '';
    if (!$url) $url = wc_get_page_permalink('shop');
    if (paxig_plp_product_search_page()) $args = array_merge(paxig_plp_args(), $args);
    return add_query_arg($args, $url);
}
function paxig_plp_term_url($term) {
    $url = get_term_link($term);
    if (is_wp_error($url)) return '';
    return add_query_arg(paxig_plp_args(), $url);
}
function paxig_plp_filter_url($args) {
    if (is_product_taxonomy()) {
        $term = get_queried_object();
        if ($term instanceof WP_Term) {
            $url = get_term_link($term);
            if (!is_wp_error($url)) return add_query_arg($args, $url);
        }
    }
    if (paxig_plp_product_search_page()) return add_query_arg($args, home_url('/'));
    $url = get_post_type_archive_link('product');
    if (!$url) $url = wc_get_page_permalink('shop');
    return add_query_arg($args, $url);
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
    if (paxig_plp_product_search_page()) {
        // Keep the search request and sort while clearing every active refinement.
        $args = array_intersect_key($args, array_flip(['s','post_type','orderby']));
        return add_query_arg($args, home_url('/'));
    }
    // Reset refinements while keeping the archive audience and chosen sort.
    $args = array_intersect_key($args, array_flip(['filter_gender','query_type_gender','orderby']));
    return paxig_plp_catalog_url($args);
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
    if (paxig_plp_product_search_page()) return $title;
    if (isset($_GET['paxig_releases']) && is_string($_GET['paxig_releases']) && wp_unslash($_GET['paxig_releases']) === '1') {
        $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
        return $is_vi ? 'BỘ SƯU TẬP GIỚI HẠN' : __( 'LIMITED EDITION', 'paxig-shoes' );
    }
    $profile = paxig_plp_profile();
    if ($profile === 'men') return __( 'MEN’S SNEAKERS', 'paxig-shoes' );
    if ($profile === 'women') return __( 'WOMEN’S SNEAKERS', 'paxig-shoes' );
    if ($profile === 'kids') return __( 'KIDS’ SNEAKERS', 'paxig-shoes' );
    if (is_product_category()) return strtoupper(single_cat_title('', false));
    return __( 'ALL SNEAKERS', 'paxig-shoes' );
}, 120);
function paxig_plp_header() {
    $title = woocommerce_page_title(false);
    $is_release_collection = isset($_GET['paxig_releases']) && is_string($_GET['paxig_releases']) && wp_unslash($_GET['paxig_releases']) === '1';
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    $release_label = $is_vi ? 'BỘ SƯU TẬP' : __( 'RELEASES', 'paxig-shoes' );
    $cat_name = is_product_category() ? strtoupper(get_queried_object()->name) : ($is_release_collection ? strtoupper($release_label) : (paxig_plp_product_search_page() ? strtoupper( __( 'SEARCH', 'paxig-shoes' ) ) : 'ALL'));
    echo '<header class="woocommerce-products-header vyre-plp-header">';
    echo '<nav class="vyre-breadcrumb" aria-label="Breadcrumb">';
    echo '<a href="' . esc_url( home_url('/') ) . '">HOME</a> ';
    echo '<span class="vyre-sep">/</span> ';
    echo '<a href="' . esc_url( wc_get_page_permalink('shop') ) . '">SNEAKERS</a> ';
    echo '<span class="vyre-sep">/</span> ';
    echo '<span>' . esc_html( $cat_name ) . '</span>';
    echo '</nav>';
    echo '<h1 class="woocommerce-products-header__title page-title vyre-plp-title">' . esc_html($title) . '</h1>';
    echo '<p class="vyre-plp-subtitle">' . esc_html__( 'Find your next favorite pair. Made to move with you.', 'paxig-shoes' ) . '</p>';
    echo '</header>';
}
function paxig_plp_toolbar() {
    $total = wc_get_loop_prop('total');
    $count_text = sprintf( _n( '%s PRODUCT', '%s PRODUCTS', $total, 'paxig-shoes' ), number_format_i18n( $total ) );
    echo '<div class="paxig-plp-toolbar vyre-plp-toolbar">';
    echo '<button type="button" class="paxig-plp-filter-toggle" aria-controls="paxig-plp-filters" aria-expanded="true"><span>' . esc_html__( 'Hide Filters', 'paxig-shoes' ) . '</span>' . paxig_icon('sliders-horizontal', 20) . '</button>';
    echo '<div class="vyre-plp-toolbar-right">';
    echo '<p class="paxig-plp-count vyre-plp-count">' . esc_html($count_text) . '</p>';
    echo '<div class="paxig-plp-sort vyre-plp-sort">';
    echo '<span class="vyre-sort-label">' . esc_html__( 'SORT BY', 'paxig-shoes' ) . '</span>';
    woocommerce_catalog_ordering();
    echo '</div></div></div>';
}
function paxig_plp_group($id,$title,$content,$open=true) {
    if (!trim($content)) return;
    echo '<details class="paxig-plp-group paxig-plp-group-'.esc_attr($id).'" '.($open?'open':'').'><summary>'.esc_html($title).paxig_disclosure_icons().'</summary><div class="paxig-plp-group-body">'.$content.'</div></details>';
}
function paxig_plp_attribute_filters() {
    if (!function_exists('wc_get_attribute_taxonomies')) return;
    $labels = [
        'gender' => __( 'GENDER', 'paxig-shoes' ),
        'sport' => __( 'SPORT', 'paxig-shoes' ),
        'kich-thuoc' => __( 'SIZE', 'paxig-shoes' ),
        'size' => __( 'SIZE', 'paxig-shoes' ),
        'color' => __( 'COLOR', 'paxig-shoes' ),
    ];
    $attributes = wc_get_attribute_taxonomies();
    if (!is_array($attributes) || !$attributes) return;
    $attributes = array_values(array_filter($attributes, function($attribute) {
        return is_object($attribute) && !empty($attribute->attribute_name);
    }));
    if (!$attributes) return;
    $priority = array_flip(['gender','sport','kich-thuoc','size','color']);
    usort($attributes, function($left, $right) use ($priority) {
        return ($priority[$left->attribute_name] ?? 100) <=> ($priority[$right->attribute_name] ?? 100);
    });
    foreach ($attributes as $attribute) {
        $slug = sanitize_title($attribute->attribute_name);
        if ($slug === 'gender') continue;
        $taxonomy = wc_attribute_taxonomy_name($slug);
        if (!taxonomy_exists($taxonomy)) continue;
        $terms = get_terms(['taxonomy'=>$taxonomy,'hide_empty'=>true,'number'=>1]);
        if (is_wp_error($terms) || !$terms) continue;
        $label = $labels[$slug] ?? wc_attribute_label($taxonomy);
        $widget = paxig_plp_widget('WC_Widget_Layered_Nav',['title'=>'','attribute'=>$slug,'display_type'=>'list','query_type'=>'or']);
        paxig_plp_group($slug,$label,$widget);
    }
}
function paxig_plp_gender_filter() {
    if (!function_exists('wc_attribute_taxonomy_name')) return;
    $taxonomy = wc_attribute_taxonomy_name('gender');
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    $known_labels = [
        'men' => $is_vi ? 'Nam' : 'Men',
        'women' => $is_vi ? 'Nữ' : 'Women',
        'kids' => $is_vi ? 'Trẻ em' : 'Kids',
        'unisex' => 'Unisex',
    ];
    $has_gender_taxonomy = taxonomy_exists($taxonomy);
    $terms = [];
    foreach ($known_labels as $slug => $label) {
        $term = $has_gender_taxonomy ? get_term_by('slug', $slug, $taxonomy) : false;
        $terms[] = $term && !is_wp_error($term) ? $term : (object) ['slug' => $slug, 'name' => $label];
    }

    $args = paxig_plp_args();
    $selected = isset($args['filter_gender']) ? array_values(array_filter(explode(',', $args['filter_gender']))) : [];
    $selected = array_values(array_intersect($selected, array_keys($known_labels)));
    $items = '<ul class="woocommerce-widget-layered-nav-list paxig-plp-gender-list paxig-plp-gender-radio-list">';
    foreach ($terms as $term) {
        $is_selected = in_array($term->slug, $selected, true);
        $next_selected = $is_selected ? [] : [$term->slug];
        $next_args = $args;
        if ($next_selected) {
            $next_args['filter_gender'] = $term->slug;
            $next_args['query_type_gender'] = 'or';
        } else {
            unset($next_args['filter_gender'], $next_args['query_type_gender']);
        }
        $label = $known_labels[$term->slug] ?? $term->name;
        $items .= '<li class="woocommerce-widget-layered-nav-list__item' . ($is_selected ? ' chosen' : '') . '">';
        $items .= '<a ' . ($is_selected ? 'aria-current="true" ' : '') . 'href="' . esc_url(paxig_plp_filter_url($next_args)) . '">' . esc_html($label) . '</a>';
        $items .= '</li>';
    }
    $items .= '</ul>';
    $title = $is_vi ? 'GIỚI TÍNH' : __('GENDER', 'paxig-shoes');
    paxig_plp_group('gender', $title, $items);
}
function paxig_plp_category_filter($slugs) {
    $terms=paxig_plp_terms($slugs);
    if (!$terms) return;
    $args = paxig_plp_args();
    $selected = isset($args['paxig_categories']) ? array_values(array_filter(array_map('sanitize_title', explode(',', $args['paxig_categories'])))) : [];
    $selected = array_values(array_intersect($selected, $slugs));
    if (!array_key_exists('paxig_categories', $args) && is_product_category()) {
        $current = get_queried_object();
        if ($current instanceof WP_Term && in_array($current->slug, $slugs, true)) $selected[] = $current->slug;
    }

    $html='<ul class="woocommerce-widget-layered-nav-list paxig-plp-category-list">';
    foreach ($terms as $term) {
        $is_selected = in_array($term->slug, $selected, true);
        $next_selected = $is_selected
            ? array_values(array_diff($selected, [$term->slug]))
            : array_values(array_unique(array_merge($selected, [$term->slug])));
        $next_args = $args;
        if ($next_selected) $next_args['paxig_categories'] = implode(',', $next_selected);
        else unset($next_args['paxig_categories']);
        $html .= '<li class="woocommerce-widget-layered-nav-list__item' . ($is_selected ? ' chosen' : '') . '">';
        $html .= '<a ' . ($is_selected ? 'aria-current="true" ' : '') . 'href="' . esc_url(paxig_plp_catalog_url($next_args)) . '">' . esc_html($term->name) . '</a></li>';
    }
    $html .= '</ul>';
    paxig_plp_group('categories', __( 'CATEGORY', 'paxig-shoes' ), $html);
}
function paxig_plp_widget($class,$instance) {
    if (!class_exists($class)) return '';
    ob_start();
    the_widget($class,$instance,['before_widget'=>'<div class="paxig-plp-native-widget">','after_widget'=>'</div>','before_title'=>'<span class="screen-reader-text">','after_title'=>'</span>']);
    return ob_get_clean();
}
function paxig_plp_layout_open() {
    echo '<div class="paxig-shop-layout paxig-plp-layout vyre-shop-layout"><aside class="paxig-shop-sidebar vyre-shop-sidebar" id="paxig-plp-filters" aria-label="'.esc_attr__( 'Product filters', 'paxig-shoes' ).'">';
    echo '<div class="paxig-plp-drawer-head"><h2 id="paxig-plp-drawer-title">' . esc_html__( 'Filters', 'paxig-shoes' ) . '</h2><button type="button" class="paxig-plp-drawer-close" aria-label="' . esc_attr__('Close filters', 'paxig-shoes') . '">' . paxig_icon('x', 22) . '</button></div>';
    echo '<div class="vyre-sidebar-head"><span class="vyre-sidebar-title">'.esc_html__( 'FILTERS', 'paxig-shoes' ).'</span><a class="vyre-sidebar-clear" href="'.esc_url(paxig_plp_reset_url()).'">'.esc_html__( 'CLEAR ALL', 'paxig-shoes' ).'</a></div><div class="paxig-plp-filter-content">';
    paxig_plp_category_filter(['lifestyle','running','basketball','football','training']);
    paxig_plp_gender_filter();
    paxig_plp_attribute_filters();
    paxig_plp_group('price', __( 'PRICE', 'paxig-shoes' ), paxig_plp_widget('WC_Widget_Price_Filter',['title'=>'']));
    echo '</div><div class="paxig-plp-drawer-actions"><button type="button" class="paxig-plp-drawer-view">' . esc_html__('View products', 'paxig-shoes') . '</button></div></aside><div class="paxig-shop-products vyre-shop-products">';
}
function paxig_plp_layout_close() { echo '</div></div>'; }
function paxig_plp_empty_reset() { echo '<p class="paxig-plp-empty-reset"><a class="button" href="'.esc_url(paxig_plp_reset_url()).'">'.esc_html__( 'Clear Filters', 'paxig-shoes' ).'</a></p>'; }
add_action('wp',function () {
    if (!paxig_plp_page()) return;
    remove_action('woocommerce_before_main_content','woocommerce_breadcrumb',20);
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
    if (is_shop() || (paxig_plp_product_search_page() && !is_product_taxonomy())) {
        $url = paxig_plp_catalog_url(paxig_plp_args());
    }
    foreach (['paxig_sale','paxig_best_sellers','paxig_releases'] as $key) {
        $args=paxig_plp_args();
        if (isset($args[$key])) $url=add_query_arg($key,$args[$key],$url);
    }
    return $url;
});
add_action('pre_get_posts', function ($query) {
    if (is_admin() || !$query->is_main_query()) return;
    $post_type = $query->get('post_type');
    $is_product_search = $query->is_search() && ($post_type === 'product' || (is_array($post_type) && in_array('product', $post_type, true)));
    $shop_page_id = function_exists('wc_get_page_id') ? wc_get_page_id('shop') : 0;
    $is_shop_page = $shop_page_id > 0 && $query->is_page($shop_page_id);
    if (!$query->is_post_type_archive('product') && !$is_shop_page && !$is_product_search) return;

    $tax_query = $query->get('tax_query');
    if (!is_array($tax_query)) $tax_query = [];

    $category_slugs = isset($_GET['paxig_categories']) && is_string($_GET['paxig_categories'])
        ? array_values(array_unique(array_filter(array_map('sanitize_title', explode(',', wp_unslash($_GET['paxig_categories']))))))
        : [];
    $category_slugs = array_values(array_filter($category_slugs, function ($slug) {
        $term = get_term_by('slug', $slug, 'product_cat');
        return $term && !is_wp_error($term);
    }));
    if ($category_slugs) {
        $tax_query[] = [
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => $category_slugs,
            'operator' => 'IN',
        ];
    }

    $release_filter = isset($_GET['paxig_releases']) && is_string($_GET['paxig_releases'])
        ? sanitize_text_field(wp_unslash($_GET['paxig_releases']))
        : '';
    if ($release_filter === '1') {
        $tax_query[] = [
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => ['limited-edition'],
        ];
    }
    if ($tax_query) $query->set('tax_query', $tax_query);
});
add_filter('gettext',function($translated,$text,$domain){
    if ($domain!=='woocommerce'||!paxig_plp_page()) return $translated;
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    if ($is_vi) {
        return ['Filter'=>'Áp dụng','Price:'=>'Giá:','Min price'=>'Giá tối thiểu','Max price'=>'Giá tối đa'][$text] ?? $translated;
    }
    return ['Filter'=>'Apply Price','Price:'=>'Price:'][$text] ?? $translated;
},140,3);
