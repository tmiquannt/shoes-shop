<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="paxig-header paxig-header--landing">
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

    <?php echo paxig_icon('menu', 24); ?>
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
    $men_args = ['filter_gender'=>'men','query_type_gender'=>'or'];
    $women_args = ['filter_gender'=>'women','query_type_gender'=>'or'];
    $men_url = function_exists('paxig_plp_catalog_url') ? paxig_plp_catalog_url($men_args) : add_query_arg($men_args, $catalog_url);
    $women_url = function_exists('paxig_plp_catalog_url') ? paxig_plp_catalog_url($women_args) : add_query_arg($women_args, $catalog_url);
    $new_url = add_query_arg(['orderby'=>'date'], $catalog_url);
    $releases_url = add_query_arg(['paxig_releases'=>'1'], $catalog_url);
    $story_url = home_url('/#brand-story-title');
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    $category_labels = [
        'lifestyle'  => $is_vi ? 'Phong cách' : 'Lifestyle',
        'running'    => $is_vi ? 'Chạy bộ' : 'Running',
        'basketball' => $is_vi ? 'Bóng rổ' : 'Basketball',
        'football'   => $is_vi ? 'Bóng đá' : 'Football',
        'training'   => $is_vi ? 'Tập luyện' : 'Training',
    ];
    $category_nav_items = [];
    foreach ($category_labels as $category_slug => $category_label) {
        $category_term = get_term_by('slug', $category_slug, 'product_cat');
        $category_url = $category_term && !is_wp_error($category_term)
            ? (function_exists('paxig_plp_term_url') ? paxig_plp_term_url($category_term) : get_term_link($category_term))
            : add_query_arg(['product_cat' => $category_slug, 'post_type' => 'product'], $shop_url);
        if (is_wp_error($category_url)) {
            continue;
        }
        $category_nav_items[$category_slug] = [
            'label' => $category_label,
            'url' => $category_url,
        ];
    }
    $current_category_slug = is_product_category() ? get_queried_object()->slug : '';
    $gender_param = isset( $_GET['filter_gender'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_gender'] ) ) : '';
    $orderby_param = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : '';
    $releases_param = isset( $_GET['paxig_releases'] ) ? sanitize_text_field( wp_unslash( $_GET['paxig_releases'] ) ) : '';

    $active_nav_key = '';
    if ( $releases_param === '1' ) {
        $active_nav_key = 'releases';
    } elseif ( $gender_param === 'men' || ( strpos( $gender_param, 'men' ) !== false && strpos( $gender_param, 'women' ) === false ) ) {
        $active_nav_key = 'men';
    } elseif ( $gender_param === 'women' || strpos( $gender_param, 'women' ) !== false ) {
        $active_nav_key = 'women';
    } elseif ( $orderby_param === 'date' ) {
        $active_nav_key = 'new';
    } elseif ( is_page( 'about' ) || is_page( 'our-story' ) ) {
        $active_nav_key = 'story';
    } elseif ( is_shop() || $current_category_slug !== '' ) {
        $active_nav_key = 'categories';
    }

    $nav_items = [
        'men' => [
            'label' => $is_vi ? 'NAM' : 'MEN',
            'url'   => $men_url,
        ],
        'women' => [
            'label' => $is_vi ? 'NỮ' : 'WOMEN',
            'url'   => $women_url,
        ],
        'categories' => [
            'label' => $is_vi ? 'DANH MỤC' : 'CATEGORIES',
            'url' => $shop_url,
            'children' => $category_nav_items,
        ],
        'new' => [
            'label' => $is_vi ? 'MỚI' : 'NEW',
            'url'   => $new_url,
        ],
        'releases' => [
            'label' => $is_vi ? 'BỘ SƯU TẬP' : 'RELEASES',
            'url'   => $releases_url,
        ],
        'story' => [
            'label' => $is_vi ? 'CÂU CHUYỆN' : 'OUR STORY',
            'url'   => $story_url,
        ],
    ];
    ?>
        <ul class="paxig-nav paxig-nav--poster">
            <?php foreach ( $nav_items as $key => $item ) :
                $has_children = ! empty( $item['children'] );
                $is_active = ( $active_nav_key === $key );
                $is_category_ancestor = $key === 'categories' && $current_category_slug !== '';
                $li_classes = array();
                if ( $is_active ) {
                    $li_classes[] = 'current-menu-item';
                    $li_classes[] = 'is-active';
                }
                if ( $has_children ) {
                    $li_classes[] = 'menu-item-has-children';
                }
                if ( $is_category_ancestor ) {
                    $li_classes[] = 'current-menu-ancestor';
                }
                $li_class_attr = ! empty( $li_classes ) ? ' class="' . esc_attr( implode( ' ', $li_classes ) ) . '"' : '';
            ?>
                <li<?php echo $li_class_attr; ?> data-nav-key="<?php echo esc_attr( $key ); ?>">
                    <a href="<?php echo esc_url( $item['url'] ); ?>"<?php echo $is_active && !$is_category_ancestor ? ' class="is-active" aria-current="page"' : ''; ?>><?php echo esc_html( $item['label'] ); ?></a>
                    <?php if ( $has_children ) : ?>
                        <ul class="sub-menu">
                            <?php foreach ( $item['children'] as $category_slug => $category_item ) :
                                $is_current_category = $current_category_slug === $category_slug;
                            ?>
                                <li<?php echo $is_current_category ? ' class="current-menu-item is-active"' : ''; ?>>
                                    <a href="<?php echo esc_url( $category_item['url'] ); ?>"<?php echo $is_current_category ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $category_item['label'] ); ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
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
        <?php echo paxig_icon('search', 22); ?>
    </button>

    <a
        class="paxig-header-action"
        href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"
        aria-label="<?php echo esc_attr__( 'My Account', 'paxig-shoes' ); ?>"
    >
        <?php echo paxig_icon('user-round', 22); ?>
    </a>

    <button
        class="paxig-header-action paxig-header-wishlist"
        type="button"
        aria-label="<?php echo esc_attr__( 'Wishlist', 'paxig-shoes' ); ?>"
        aria-controls="paxig-wishlist-drawer"
        aria-expanded="false"
    >
        <?php echo paxig_icon('heart', 22); ?>
        <span class="paxig-wishlist-count">0</span>
    </button>

    <a
        class="paxig-header-action paxig-header-cart"
        href="<?php echo esc_url( wc_get_cart_url() ); ?>"
        aria-label="<?php echo esc_attr__( 'Cart', 'paxig-shoes' ); ?>"
        aria-controls="paxig-cart-drawer"
        aria-expanded="false"
    >
        <?php echo paxig_icon('shopping-bag', 22); ?>
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
        <button type="button" class="vyre-search-close" aria-label="<?php echo esc_attr__( 'Close search', 'paxig-shoes' ); ?>"><?php echo paxig_icon('x', 22); ?></button>
        <span id="vyre-search-eyebrow" class="vyre-search-eyebrow"><?php echo esc_html__( 'WHAT ARE YOU LOOKING FOR?', 'paxig-shoes' ); ?></span>
        <form class="vyre-search-form" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
            <div class="vyre-search-input-wrap">
                <?php echo paxig_icon('search', 22); ?>
                <input type="search" name="s" placeholder="<?php echo esc_attr__( 'Search sneakers, collections...', 'paxig-shoes' ); ?>" autocomplete="off">
                <input type="hidden" name="post_type" value="product">
                <button type="submit" class="vyre-search-submit" aria-label="<?php echo esc_attr__( 'Submit search', 'paxig-shoes' ); ?>"><?php echo paxig_icon('arrow-right', 22); ?></button>
            </div>
        </form>
        <div class="vyre-search-cols">
            <div class="vyre-search-col">
                <h4><?php echo esc_html__( 'TRENDING SEARCHES', 'paxig-shoes' ); ?></h4>
                <ul class="vyre-trending-list">
                    <li><a href="<?php echo esc_url( $new_url ); ?>"><?php echo esc_html__( 'New arrivals', 'paxig-shoes' ); ?> <?php echo paxig_icon('arrow-up-right', 18); ?></a></li>
                    <li><a href="<?php echo esc_url( add_query_arg(['product_cat' => 'lifestyle', 'post_type' => 'product'], home_url('/')) ); ?>"><?php echo esc_html__( 'Lifestyle sneakers', 'paxig-shoes' ); ?> <?php echo paxig_icon('arrow-up-right', 18); ?></a></li>
                    <li><a href="<?php echo esc_url( add_query_arg(['s' => 'Court', 'post_type' => 'product'], home_url('/')) ); ?>"><?php echo esc_html__( 'Court Series', 'paxig-shoes' ); ?> <?php echo paxig_icon('arrow-up-right', 18); ?></a></li>
                    <li><a href="<?php echo esc_url( add_query_arg(['paxig_releases' => '1'], $shop_url) ); ?>"><?php echo esc_html__( 'Limited edition', 'paxig-shoes' ); ?> <?php echo paxig_icon('arrow-up-right', 18); ?></a></li>
                </ul>
            </div>
            <div class="vyre-search-col">
                <h4><?php echo esc_html__( 'RECOMMENDED FOR YOU', 'paxig-shoes' ); ?></h4>
                <div class="vyre-search-recs">
                    <?php
                    $recommended_products = function_exists( 'wc_get_products' )
                        ? wc_get_products( array( 'status' => 'publish', 'visibility' => 'visible', 'limit' => 3, 'orderby' => 'popularity', 'order' => 'DESC' ) )
                        : array();
                    foreach ( $recommended_products as $recommended_product ) :
                        if ( ! $recommended_product instanceof WC_Product || ! $recommended_product->is_visible() ) {
                            continue;
                        }
                    ?>
                        <a href="<?php echo esc_url( $recommended_product->get_permalink() ); ?>" class="vyre-rec-card">
                            <div class="vyre-rec-img"><?php echo $recommended_product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ); ?></div>
                            <span><?php echo esc_html( $recommended_product->get_name() ); ?></span>
                            <small><?php echo wp_kses_post( $recommended_product->get_price_html() ); ?></small>
                        </a>
                    <?php endforeach; ?>
                    <?php if ( empty( $recommended_products ) ) : ?>
                        <p class="vyre-search-rec-empty"><?php echo esc_html__( 'Recommended products will appear here when the catalog is ready.', 'paxig-shoes' ); ?></p>
                    <?php endif; ?>
                </div>
                <a href="<?php echo esc_url( $new_url ); ?>" class="vyre-search-explore"><?php echo paxig_icon_text(esc_html__( 'Shop the latest · Discover more ↗', 'paxig-shoes' )); ?></a>
            </div>
        </div>
    </div>
</div>
