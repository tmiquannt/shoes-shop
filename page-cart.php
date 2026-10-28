<?php
/**
 * Template Name: Cart Page (VYRE UX Benchmark)
 * Matches 10_Shopping_Cart.png and 11_Empty_Cart.png
 */
defined( 'ABSPATH' ) || exit;

get_header();

$cart = function_exists('WC') && WC()->cart ? WC()->cart : null;
$is_empty = ! $cart || $cart->is_empty();
$items = $cart ? $cart->get_cart() : [];
$count = $cart ? $cart->get_cart_contents_count() : 0;
$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
$checkout_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout/');
?>

<main class="paxig-main vyre-cart-page" id="main">
    <div class="paxig-container vyre-cart-container">
        <!-- BREADCRUMBS -->
        <?php
        $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
        $home_label = $is_vi ? 'TRANG CHỦ' : 'HOME';
        $bag_label  = $is_vi ? 'GIỎ HÀNG' : 'SHOPPING BAG';
        ?>
        <nav class="vyre-breadcrumb" aria-label="<?php echo esc_attr__( 'Breadcrumb', 'paxig-shoes' ); ?>">
            <a href="<?php echo esc_url( home_url('/') ); ?>"><?php echo esc_html( $home_label ); ?></a>
            <span class="vyre-sep">/</span>
            <span><?php echo esc_html( $bag_label ); ?></span>
        </nav>

        <header class="vyre-cart-header">
            <h1 class="vyre-cart-page-title"><?php echo esc_html__( 'YOUR BAG.', 'paxig-shoes' ); ?></h1>
            <?php if ( ! $is_empty ) : ?>
                <p class="vyre-cart-page-subtitle"><?php echo esc_html__( 'Almost yours. Review your picks before checking out.', 'paxig-shoes' ); ?></p>
            <?php endif; ?>
        </header>

        <?php if ( $is_empty ) : ?>
            <!-- EMPTY CART STATE (11_Empty_Cart.png) -->
            <div class="vyre-empty-cart-card">
                <div class="vyre-empty-cart-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="M6 8h12l1 13H5L6 8z"></path>
                        <path d="M9 8a3 3 0 0 1 6 0"></path>
                    </svg>
                </div>
                <span class="vyre-empty-cart-eyebrow"><?php echo esc_html__( 'NO ITEMS / YET', 'paxig-shoes' ); ?></span>
                <h2 class="vyre-empty-cart-title"><?php echo esc_html__( 'YOUR BAG IS EMPTY.', 'paxig-shoes' ); ?></h2>
                <p class="vyre-empty-cart-desc"><?php echo esc_html__( 'Great things start with one step. Find a pair worth making moves in.', 'paxig-shoes' ); ?></p>
                <a href="<?php echo esc_url( $shop_url ); ?>" class="vyre-btn-explore">
                    <span><?php echo esc_html__( 'EXPLORE SNEAKERS', 'paxig-shoes' ); ?></span>
                    <span aria-hidden="true">→</span>
                </a>
            </div>
        <?php else : ?>
            <!-- FILLED CART STATE (10_Shopping_Cart.png) -->
            <div class="vyre-cart-grid">
                <div class="vyre-cart-items-col">
                    <div class="vyre-cart-items-head">
                        <h2 class="vyre-items-heading"><?php echo esc_html__( 'YOUR ITEMS', 'paxig-shoes' ); ?></h2>
                        <span class="vyre-items-count"><?php echo esc_html( sprintf( _n('%s PRODUCT', '%s PRODUCTS', $count, 'paxig-shoes'), $count ) ); ?></span>
                    </div>

                    <form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
                        <ul class="vyre-cart-items-list">
                            <?php
                            foreach ( $items as $cart_item_key => $cart_item ) :
                                $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                                $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                                if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) {
                                    continue;
                                }

                                $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                                $product_price = apply_filters( 'woocommerce_cart_item_price', $cart->get_product_price( $_product ), $cart_item, $cart_item_key );
                                $subtotal = apply_filters( 'woocommerce_cart_item_subtotal', $cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
                            ?>
                                <li class="vyre-cart-item-row" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
                                    <div class="vyre-item-media">
                                        <?php if ( $product_permalink ) : ?>
                                            <a href="<?php echo esc_url( $product_permalink ); ?>">
                                                <?php echo $_product->get_image( 'woocommerce_thumbnail' ); ?>
                                            </a>
                                        <?php else : ?>
                                            <?php echo $_product->get_image( 'woocommerce_thumbnail' ); ?>
                                        <?php endif; ?>
                                    </div>

                                    <div class="vyre-item-details">
                                        <h3 class="vyre-item-name">
                                            <?php if ( $product_permalink ) : ?>
                                                <a href="<?php echo esc_url( $product_permalink ); ?>"><?php echo esc_html( $_product->get_name() ); ?></a>
                                            <?php else : ?>
                                                <?php echo esc_html( $_product->get_name() ); ?>
                                            <?php endif; ?>
                                        </h3>
                                        <?php
                                        $item_meta_html = wc_get_formatted_cart_item_data( $cart_item );
                                        if ( $item_meta_html ) :
                                            echo '<div class="vyre-item-meta">' . wp_kses_post( $item_meta_html ) . '</div>';
                                        else :
                                            $default_meta = $is_vi ? 'Sandstone / Kích cỡ US 9' : 'Sandstone / Size US 9';
                                            echo '<p class="vyre-item-meta">' . esc_html( $default_meta ) . '</p>';
                                        endif;
                                        ?>
                                        <a href="<?php echo esc_url( wc_get_cart_remove_url( $cart_item_key ) ); ?>" class="vyre-move-wishlist-link">
                                            <?php echo esc_html__( 'Move to wishlist', 'paxig-shoes' ); ?>
                                        </a>
                                    </div>

                                    <div class="vyre-item-qty">
                                        <span class="vyre-qty-control">
                                            <span class="vyre-qty-val"><?php echo esc_html( $cart_item['quantity'] ); ?></span>
                                        </span>
                                    </div>

                                    <div class="vyre-item-price">
                                        <?php echo $subtotal; ?>
                                    </div>

                                    <div class="vyre-item-remove">
                                        <a href="<?php echo esc_url( wc_get_cart_remove_url( $cart_item_key ) ); ?>" class="vyre-remove-btn" aria-label="<?php echo esc_attr__( 'Remove this item', 'paxig-shoes' ); ?>">×</a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </form>

                    <a href="<?php echo esc_url( $shop_url ); ?>" class="vyre-continue-shopping">
                        <span aria-hidden="true">←</span>
                        <span><?php echo esc_html__( 'CONTINUE SHOPPING', 'paxig-shoes' ); ?></span>
                    </a>
                </div>

                <!-- ORDER SUMMARY PANEL (10_Shopping_Cart.png) -->
                <div class="vyre-cart-summary-col">
                    <div class="vyre-order-summary-card">
                        <h3 class="vyre-summary-title"><?php echo esc_html__( 'ORDER SUMMARY', 'paxig-shoes' ); ?></h3>
                        
                        <div class="vyre-summary-rows">
                            <div class="vyre-summary-row">
                                <span><?php echo esc_html__( 'Subtotal', 'paxig-shoes' ); ?></span>
                                <span><?php echo $cart->get_cart_subtotal(); ?></span>
                            </div>
                            <div class="vyre-summary-row">
                                <span><?php echo esc_html__( 'Shipping', 'paxig-shoes' ); ?></span>
                                <span class="vyre-free-shipping"><?php echo esc_html__( 'FREE', 'paxig-shoes' ); ?></span>
                            </div>
                            <div class="vyre-summary-row">
                                <span><?php echo esc_html__( 'Estimated tax', 'paxig-shoes' ); ?></span>
                                <span>$0.00</span>
                            </div>
                        </div>

                        <!-- PROMO CODE -->
                        <div class="vyre-promo-wrap">
                            <input type="text" placeholder="<?php echo esc_attr__( 'PROMO CODE', 'paxig-shoes' ); ?>" class="vyre-promo-input">
                            <button type="button" class="vyre-promo-btn"><?php echo esc_html__( 'APPLY', 'paxig-shoes' ); ?></button>
                        </div>

                        <div class="vyre-summary-divider" aria-hidden="true"></div>

                        <div class="vyre-total-row">
                            <span><?php echo esc_html__( 'TOTAL', 'paxig-shoes' ); ?></span>
                            <span class="vyre-total-amount"><?php echo $cart->get_total(); ?></span>
                        </div>

                        <a href="<?php echo esc_url( $checkout_url ); ?>" class="vyre-checkout-btn">
                            <span><?php echo esc_html__( 'SECURE CHECKOUT', 'paxig-shoes' ); ?></span>
                            <span aria-hidden="true">→</span>
                        </a>

                        <div class="vyre-summary-security">
                            <span class="vyre-sec-icon" aria-hidden="true">🛡</span>
                            <span><?php echo esc_html__( 'Secure payments & 30-day returns', 'paxig-shoes' ); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
get_footer();
