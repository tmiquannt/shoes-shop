<?php
/**
 * Use WooCommerce's native catalog loop for product searches.
 * Other WordPress searches keep the theme's existing index fallback.
 */
defined('ABSPATH') || exit;

$is_product_search = function_exists('paxig_plp_product_search_page')
    && paxig_plp_product_search_page();

if (!$is_product_search || !function_exists('woocommerce_content')) {
    include get_index_template();
    return;
}

get_header();
woocommerce_content();
get_footer();
