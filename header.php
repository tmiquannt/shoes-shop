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
		<a class="paxig-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<span>PAXIG</span>
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
    $men_url = add_query_arg(['filter_gender'=>'men,unisex','query_type_gender'=>'or'], $shop_url);
    $women_url = add_query_arg(['filter_gender'=>'women,unisex','query_type_gender'=>'or'], $shop_url);
    $basketball = function_exists('paxig_home_term') ? paxig_home_term('basketball') : false;
    $sale = function_exists('paxig_home_term') ? paxig_home_term('sale') : false;
    $accessories = function_exists('paxig_home_term') ? paxig_home_term('accessories') : false;
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    $home_nav = [
        ( $is_vi ? 'NAM' : 'MEN' ) => $men_url,
        ( $is_vi ? 'NỮ' : 'WOMEN' ) => $women_url,
        ( $is_vi ? 'SNEAKER' : 'SNEAKERS' ) => $shop_url,
        ( $is_vi ? 'BÓNG RỔ' : 'BASKETBALL' ) => $basketball ? $basketball['url'] : $shop_url,
        ( $is_vi ? 'PHỤ KIỆN' : 'ACCESSORIES' ) => $accessories ? $accessories['url'] : $shop_url,
        ( $is_vi ? 'ƯU ĐÃI' : 'SALE' ) => $sale ? $sale['url'] : add_query_arg('paxig_sale', '1', $shop_url),
    ];

    if ( has_nav_menu( 'primary' ) && ! is_front_page() ) :
        wp_nav_menu(
            array(
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'paxig-nav',
                'fallback_cb'    => false,
                'depth'          => 3,
            )
        );
    else :
    ?>
        <ul class="paxig-nav paxig-nav--poster">
            <?php foreach ($home_nav as $label => $url) : ?>
                <li><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <?php if ( function_exists( 'paxig_render_language_switcher' ) ) paxig_render_language_switcher( 'paxig-lang-switch--mobile' ); ?>
</nav>
<div class="paxig-header-actions">
    <?php if ( function_exists( 'paxig_render_language_switcher' ) ) paxig_render_language_switcher(); ?>

    <form
        class="paxig-product-search"
        role="search"
        method="get"
        action="<?php echo esc_url( home_url( '/' ) ); ?>"
    >
        <label class="screen-reader-text" for="paxig-product-search">
            <?php echo esc_html__( 'Search products', 'paxig-shoes' ); ?>
        </label>

        <input
            id="paxig-product-search"
            type="search"
            name="s"
            placeholder="<?php echo esc_attr__( 'Search sneakers', 'paxig-shoes' ); ?>"
            value="<?php echo esc_attr( get_search_query() ); ?>"
        >

        <input type="hidden" name="post_type" value="product">

        <button type="submit" aria-label="<?php echo esc_attr__( 'Search', 'paxig-shoes' ); ?>">
            <svg
                viewBox="0 0 24 24"
                width="22"
                height="22"
                aria-hidden="true"
            >
                <circle cx="11" cy="11" r="7"></circle>
                <path d="M20 20l-4-4"></path>
            </svg>
        </button>
    </form>

    <a
        class="paxig-header-action"
        href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"
        aria-label="<?php echo esc_attr__( 'My Account', 'paxig-shoes' ); ?>"
    >
        <svg
            viewBox="0 0 24 24"
            width="24"
            height="24"
            aria-hidden="true"
        >
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
        <svg
            viewBox="0 0 24 24"
            width="24"
            height="24"
            aria-hidden="true"
        >
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
        <svg
            viewBox="0 0 24 24"
            width="24"
            height="24"
            aria-hidden="true"
        >
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
