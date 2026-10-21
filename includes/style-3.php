<?php
/** Shared Style 3 art direction. Presentation only; WooCommerce remains authoritative. */
defined('ABSPATH') || exit;

add_filter('body_class', function ($classes) {
    if (!is_admin() && !(defined('REST_REQUEST') && REST_REQUEST)) $classes[] = 'paxig-style-3';
    return $classes;
}, 100);

add_action('wp_enqueue_scripts', function () {
    if (is_admin()) return;
    $path = get_template_directory().'/assets/css/style-3.css';
    wp_enqueue_style('paxig-style-3', get_template_directory_uri().'/assets/css/style-3.css', [], filemtime($path));
}, 999);

add_action('wp_head', function () {
    if (is_admin()) return;
    $font_base = get_template_directory_uri().'/assets/fonts/';
    foreach (['oswald-latin.woff2', 'oswald-vietnamese.woff2'] as $font_file) {
        printf(
            "<link rel=\"preload\" href=\"%s\" as=\"font\" type=\"font/woff2\" crossorigin />\n",
            esc_url($font_base.$font_file)
        );
    }
}, 1);
