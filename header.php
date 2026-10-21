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
    <span class="screen-reader-text">Open menu</span>

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
    aria-label="Main navigation"
>
    <?php if (is_front_page()) :
        $shop_url = wc_get_page_permalink('shop');
        $men_url = add_query_arg(['filter_gender'=>'men,unisex','query_type_gender'=>'or'], $shop_url);
        $women_url = add_query_arg(['filter_gender'=>'women,unisex','query_type_gender'=>'or'], $shop_url);
        $basketball = function_exists('paxig_home_term') ? paxig_home_term('basketball') : false;
        $sale = function_exists('paxig_home_term') ? paxig_home_term('sale') : false;
        $accessories = function_exists('paxig_home_term') ? paxig_home_term('accessories') : false;
        $home_nav = [
            'NAM' => $men_url,
            'NỮ' => $women_url,
            'SNEAKER' => $shop_url,
            'BASKETBALL' => $basketball ? $basketball['url'] : $shop_url,
            'PHỤ KIỆN' => $accessories ? $accessories['url'] : $shop_url,
            'ƯU ĐÃI' => $sale ? $sale['url'] : add_query_arg('paxig_sale', '1', $shop_url),
        ];
    ?>
        <ul class="paxig-nav paxig-nav--poster">
            <?php foreach ($home_nav as $label => $url) : ?>
                <li><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a></li>
            <?php endforeach; ?>
        </ul>
    <?php else :
        wp_nav_menu(
            array(
                'theme_location' => 'primary',
                'container'      => false,
                'menu_class'     => 'paxig-nav',
                'fallback_cb'    => false,
                'depth'          => 3,
            )
        );
    endif; ?>
</nav>
<div class="paxig-header-actions">
    <form
        class="paxig-product-search"
        role="search"
        method="get"
        action="<?php echo esc_url( home_url( '/' ) ); ?>"
    >
        <label class="screen-reader-text" for="paxig-product-search">
            Search products
        </label>

        <input
            id="paxig-product-search"
            type="search"
            name="s"
            placeholder="Search sneakers"
            value="<?php echo esc_attr( get_search_query() ); ?>"
        >

        <input type="hidden" name="post_type" value="product">

        <button type="submit" aria-label="Search">
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
        aria-label="My Account"
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

    <a
        class="paxig-header-action paxig-header-cart"
        href="<?php echo esc_url( wc_get_cart_url() ); ?>"
        aria-label="Cart"
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
