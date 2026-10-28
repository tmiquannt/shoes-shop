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
        <?php echo paxig_icon('house', 22); ?>
        <span><?php echo esc_html__( 'HOME', 'paxig-shoes' ); ?></span>
    </a>
    <a href="<?php echo esc_url($shop_url); ?>" class="vyre-mob-link <?php echo function_exists('is_shop') && is_shop() ? 'is-active' : ''; ?>">
        <?php echo paxig_icon('layout-grid', 22); ?>
        <span><?php echo esc_html__( 'SHOP', 'paxig-shoes' ); ?></span>
    </a>
    <button type="button" class="vyre-mob-link paxig-header-wishlist" aria-controls="paxig-wishlist-drawer">
        <?php echo paxig_icon('heart', 22); ?>
        <span><?php echo esc_html__( 'SAVED', 'paxig-shoes' ); ?></span>
    </button>
    <a href="<?php echo esc_url($cart_url); ?>" class="vyre-mob-link paxig-header-cart" aria-controls="paxig-cart-drawer">
        <span class="vyre-mob-cart-wrap">
            <?php echo paxig_icon('shopping-bag', 22); ?>
            <span class="paxig-cart-count"><?php echo esc_html($cart_count); ?></span>
        </span>
        <span><?php echo esc_html__( 'BAG', 'paxig-shoes' ); ?></span>
    </a>
    <a href="<?php echo esc_url($account_url); ?>" class="vyre-mob-link <?php echo function_exists('is_account_page') && is_account_page() ? 'is-active' : ''; ?>">
        <?php echo paxig_icon('user-round', 22); ?>
        <span><?php echo esc_html__( 'ME', 'paxig-shoes' ); ?></span>
    </a>
</nav>
