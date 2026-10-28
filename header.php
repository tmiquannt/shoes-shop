<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="paxig-header">
	<div class="paxig-container">
		<a class="paxig-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr__( "PAXG'Shop", 'paxig-shoes' ); ?>">
			<img class="paxig-brand__mark" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/paxg-shop-symbol-header.png' ); ?>" width="176" height="166" alt="" aria-hidden="true">
			<span class="paxig-brand__wordmark"><span class="paxig-brand__wordmark-primary">PAXG</span><span class="paxig-brand__wordmark-shop">'Shop</span></span>
		</a>

<button
    class="paxig-menu-toggle"
    type="button"
    aria-controls="paxig-primary-navigation"
    aria-expanded="false"
>
    <span class="screen-reader-text"><?php echo esc_html__( 'Open menu', 'paxig-shoes' ); ?></span>

    <svg
        viewBox="0 0 24 24"
        width="26"
        height="26"
        aria-hidden="true"
    >
        <path d="M4 7h16"></path>
        <path d="M4 12h16"></path>
        <path d="M4 17h16"></path>
    </svg>
</button>

<nav
    id="paxig-primary-navigation"
    class="paxig-navigation"
    aria-label="<?php echo esc_attr__( 'Main navigation', 'paxig-shoes' ); ?>"
>
    <?php
    $shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
    $catalog_url = function_exists('get_post_type_archive_link') ? get_post_type_archive_link('product') : '';
    if (!$catalog_url) $catalog_url = $shop_url;
    $men_args = ['filter_gender'=>'men,unisex','query_type_gender'=>'or'];
    $women_args = ['filter_gender'=>'women,unisex','query_type_gender'=>'or'];
    $men_url = function_exists('paxig_plp_catalog_url') ? paxig_plp_catalog_url($men_args) : add_query_arg($men_args, $catalog_url);
    $women_url = function_exists('paxig_plp_catalog_url') ? paxig_plp_catalog_url($women_args) : add_query_arg($women_args, $catalog_url);
    $new_url = add_query_arg(['orderby'=>'date'], $catalog_url);
    $releases_url = add_query_arg(['paxig_releases'=>'1'], $catalog_url);
    $journal_url = home_url('/ve-paxig/');
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    $home_nav = [
        ( $is_vi ? 'NAM' : 'MEN' ) => $men_url,
        ( $is_vi ? 'NỮ' : 'WOMEN' ) => $women_url,
        ( $is_vi ? 'MỚI' : 'NEW' ) => $new_url,
        ( $is_vi ? 'BỘ SƯU TẬP' : 'RELEASES' ) => $releases_url,
        ( $is_vi ? 'BÀI VIẾT' : 'JOURNAL' ) => $journal_url,
    ];
    ?>
        <ul class="paxig-nav paxig-nav--poster">
            <?php foreach ($home_nav as $label => $url) : ?>
                <li><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a></li>
            <?php endforeach; ?>
        </ul>
    <?php if ( function_exists( 'paxig_render_language_switcher' ) ) paxig_render_language_switcher( 'paxig-lang-switch--mobile' ); ?>
</nav>
<div class="paxig-header-actions">
    <?php if ( function_exists( 'paxig_render_language_switcher' ) ) paxig_render_language_switcher(); ?>
    <button
        class="paxig-header-action paxig-header-search-trigger"
        type="button"
        aria-label="<?php echo esc_attr__( 'Search', 'paxig-shoes' ); ?>"
        aria-controls="vyre-search-modal"
        aria-expanded="false"
    >
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="11" cy="11" r="7"></circle>
            <path d="M20 20l-4-4"></path>
        </svg>
    </button>

    <a
        class="paxig-header-action"
        href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"
        aria-label="<?php echo esc_attr__( 'My Account', 'paxig-shoes' ); ?>"
    >
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="12" cy="8" r="4"></circle>
            <path d="M4 21a8 8 0 0 1 16 0"></path>
        </svg>
    </a>

    <button
        class="paxig-header-action paxig-header-wishlist"
        type="button"
        aria-label="<?php echo esc_attr__( 'Wishlist', 'paxig-shoes' ); ?>"
        aria-controls="paxig-wishlist-drawer"
        aria-expanded="false"
    >
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path d="M20.8 8.7c0 5.2-8.8 11-8.8 11s-8.8-5.8-8.8-11A4.7 4.7 0 0 1 12 6.1a4.7 4.7 0 0 1 8.8 2.6Z"></path>
        </svg>
        <span class="paxig-wishlist-count">0</span>
    </button>

    <a
        class="paxig-header-action paxig-header-cart"
        href="<?php echo esc_url( wc_get_cart_url() ); ?>"
        aria-label="<?php echo esc_attr__( 'Cart', 'paxig-shoes' ); ?>"
        aria-controls="paxig-cart-drawer"
        aria-expanded="false"
    >
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path d="M6 8h12l1 13H5L6 8z"></path>
            <path d="M9 8a3 3 0 0 1 6 0"></path>
        </svg>
        <span class="paxig-cart-count">
            <?php
            echo esc_html(
                WC()->cart
                    ? WC()->cart->get_cart_contents_count()
                    : 0
            );
            ?>
        </span>
    </a>
</div>
	</div>
</header>

<div id="vyre-search-modal" class="vyre-search-modal" aria-hidden="true">
    <div class="vyre-search-backdrop"></div>
    <div class="vyre-search-dialog" role="dialog" aria-modal="true" aria-labelledby="vyre-search-eyebrow">
        <button type="button" class="vyre-search-close" aria-label="<?php echo esc_attr__( 'Close search', 'paxig-shoes' ); ?>">×</button>
        <span id="vyre-search-eyebrow" class="vyre-search-eyebrow"><?php echo esc_html__( 'WHAT ARE YOU LOOKING FOR?', 'paxig-shoes' ); ?></span>
        <form class="vyre-search-form" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
            <div class="vyre-search-input-wrap">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="M20 20l-4-4"></path></svg>
                <input type="search" name="s" placeholder="<?php echo esc_attr__( 'Search sneakers, collections...', 'paxig-shoes' ); ?>" autocomplete="off">
                <input type="hidden" name="post_type" value="product">
                <button type="submit" class="vyre-search-submit" aria-label="<?php echo esc_attr__( 'Submit search', 'paxig-shoes' ); ?>">→</button>
            </div>
        </form>
        <div class="vyre-search-cols">
            <div class="vyre-search-col">
                <h4><?php echo esc_html__( 'TRENDING SEARCHES', 'paxig-shoes' ); ?></h4>
                <ul class="vyre-trending-list">
                    <li><a href="<?php echo esc_url( add_query_arg('orderby', 'date', $shop_url) ); ?>"><?php echo esc_html__( 'New arrivals', 'paxig-shoes' ); ?> ↗</a></li>
                    <li><a href="<?php echo esc_url( add_query_arg(['product_cat' => 'lifestyle', 'post_type' => 'product'], home_url('/')) ); ?>"><?php echo esc_html__( 'Lifestyle sneakers', 'paxig-shoes' ); ?> ↗</a></li>
                    <li><a href="<?php echo esc_url( add_query_arg(['s' => 'Court', 'post_type' => 'product'], home_url('/')) ); ?>"><?php echo esc_html__( 'Court Series', 'paxig-shoes' ); ?> ↗</a></li>
                    <li><a href="<?php echo esc_url( add_query_arg(['paxig_releases' => '1'], $shop_url) ); ?>"><?php echo esc_html__( 'Limited edition', 'paxig-shoes' ); ?> ↗</a></li>
                </ul>
            </div>
            <div class="vyre-search-col">
                <h4><?php echo esc_html__( 'RECOMMENDED FOR YOU', 'paxig-shoes' ); ?></h4>
                <div class="vyre-search-recs">
                    <a href="<?php echo esc_url( $shop_url ); ?>" class="vyre-rec-card"><div class="vyre-rec-img"></div><span>PAXG 001</span></a>
                    <a href="<?php echo esc_url( $shop_url ); ?>" class="vyre-rec-card"><div class="vyre-rec-img"></div><span>PAXG 002</span></a>
                    <a href="<?php echo esc_url( $shop_url ); ?>" class="vyre-rec-card"><div class="vyre-rec-img"></div><span>PAXG 003</span></a>
                </div>
                <a href="<?php echo esc_url( $shop_url ); ?>" class="vyre-search-explore"><?php echo esc_html__( 'Shop the latest · Discover more ↗', 'paxig-shoes' ); ?></a>
            </div>
        </div>
    </div>
</div>
