<?php
/** Storefront customer account presentation. WooCommerce keeps ownership of account data and actions. */
defined('ABSPATH') || exit;

add_filter('woocommerce_account_menu_items', function($items) {
    // Shoes are not downloadable products, so keep the customer navigation focused on useful tasks.
    unset($items['downloads']);

    $labels = [
        'dashboard' => 'Overview',
        'orders' => 'Orders',
        'edit-address' => 'Addresses',
        'edit-account' => 'Profile',
        'customer-logout' => 'Log Out',
    ];

    foreach ($items as $key => $label) {
        if (isset($labels[$key])) $items[$key] = $labels[$key];
    }

    return $items;
}, 20);

add_action('wp_enqueue_scripts', function() {
    if (!function_exists('is_account_page') || !is_account_page()) return;

    $path = get_template_directory() . '/assets/css/customer-account.css';
    wp_enqueue_style(
        'paxig-customer-account',
        get_template_directory_uri() . '/assets/css/customer-account.css',
        ['paxig-store'],
        filemtime($path)
    );
}, 110);
