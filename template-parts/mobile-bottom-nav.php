<?php
/**
 * Mobile Bottom Navigation Bar matching 33_Mobile_Home_Video_Hero.png
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
$account_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/');
$cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/');
$is_home = is_front_page();
$cart_count = (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 0;
?>
<nav class="vyre-mobile-nav" aria-label="<?php echo esc_attr__( 'Mobile bottom navigation', 'paxig-shoes' ); ?>">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="vyre-mob-link <?php echo $is_home ? 'is-active' : ''; ?>">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 10.5L12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1v-9.5z"/></svg>
        <span><?php echo esc_html__( 'HOME', 'paxig-shoes' ); ?></span>
    </a>
    <a href="<?php echo esc_url($shop_url); ?>" class="vyre-mob-link <?php echo function_exists('is_shop') && is_shop() ? 'is-active' : ''; ?>">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        <span><?php echo esc_html__( 'SHOP', 'paxig-shoes' ); ?></span>
    </a>
    <button type="button" class="vyre-mob-link paxig-header-wishlist" aria-controls="paxig-wishlist-drawer">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.8 8.7c0 5.2-8.8 11-8.8 11s-8.8-5.8-8.8-11A4.7 4.7 0 0 1 12 6.1a4.7 4.7 0 0 1 8.8 2.6Z"/></svg>
        <span><?php echo esc_html__( 'SAVED', 'paxig-shoes' ); ?></span>
    </button>
    <a href="<?php echo esc_url($cart_url); ?>" class="vyre-mob-link paxig-header-cart" aria-controls="paxig-cart-drawer">
        <span class="vyre-mob-cart-wrap">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 8h12l1 13H5L6 8z"/><path d="M9 8a3 3 0 0 1 6 0"/></svg>
            <span class="paxig-cart-count"><?php echo esc_html($cart_count); ?></span>
        </span>
        <span><?php echo esc_html__( 'BAG', 'paxig-shoes' ); ?></span>
    </a>
    <a href="<?php echo esc_url($account_url); ?>" class="vyre-mob-link <?php echo function_exists('is_account_page') && is_account_page() ? 'is-active' : ''; ?>">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
        <span><?php echo esc_html__( 'ME', 'paxig-shoes' ); ?></span>
    </a>
</nav>
