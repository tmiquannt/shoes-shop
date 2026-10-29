<?php
/**
 * Reusable Product Card Component for PAXG'Shop.
 * Used across Homepage, Shop PLP, Category Archives, and Search.
 *
 * @package PaxigShoes
 */

defined( 'ABSPATH' ) || exit;

$product = isset( $args['product'] ) && $args['product'] instanceof WC_Product
	? $args['product']
	: ( isset( $GLOBALS['product'] ) && $GLOBALS['product'] instanceof WC_Product ? $GLOBALS['product'] : null );

if ( ! $product instanceof WC_Product ) {
	return;
}

$id         = $product->get_id();
$name       = $product->get_name();
$permalink  = $product->get_permalink();
$price_html = $product->get_price_html();
$is_vi      = function_exists( 'paxig_get_current_language' ) && paxig_get_current_language() === 'vi';

$badge = '';
$badge_class = '';
if ( $product->is_on_sale() ) {
	$regular_price = (float) $product->get_regular_price();
	$sale_price    = (float) $product->get_sale_price();
	$percent       = 0;
	if ( $regular_price > 0 && $sale_price > 0 && $regular_price > $sale_price ) {
		$percent = (int) round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
	}
	$badge       = $percent > 0 ? '-' . $percent . '%' : ( $is_vi ? 'ƯU ĐÃI' : __( 'SALE', 'paxig-shoes' ) );
	$badge_class = 'vyre-card-badge--sale';
} elseif ( has_term( 'new-arrivals', 'product_cat', $id ) || has_term( 'limited-edition', 'product_cat', $id ) ) {
	$badge = $is_vi ? 'MỚI' : __( 'NEW', 'paxig-shoes' );
} elseif ( has_term( 'best-sellers', 'product_cat', $id ) ) {
	$badge = $is_vi ? 'BÁN CHẠY' : __( 'BESTSELLER', 'paxig-shoes' );
}

$subtitle = trim( wp_trim_words( wp_strip_all_tags( $product->get_short_description() ), 12, '' ) );
$color    = trim( wp_strip_all_tags( $product->get_attribute( 'pa_color' ) ) );

if ( ! $subtitle ) {
	$category_term = function_exists( 'paxig_reference_category_term' ) ? paxig_reference_category_term() : null;
	if ( $category_term ) {
		$subtitle = $category_term->name;
	} elseif ( $color ) {
		$subtitle = $color;
	}
}

$classes = function_exists( 'wc_get_product_class' )
	? wc_get_product_class( [ 'vyre-product-card' ], $product )
	: [ 'vyre-product-card', 'product' ];
?>
<li class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-product-id="<?php echo esc_attr( $id ); ?>">
	<div class="vyre-card-media paxig-reference-image">
		<?php if ( $badge ) : ?>
			<span class="vyre-card-badge <?php echo esc_attr( $badge_class ); ?> paxig-reference-badge"><?php echo esc_html( $badge ); ?></span>
		<?php endif; ?>
		<button
			type="button"
			class="vyre-card-wishlist paxig-wishlist-btn paxig-wishlist-card-btn"
			data-product-id="<?php echo esc_attr( $id ); ?>"
			aria-pressed="false"
			aria-label="<?php echo esc_attr( sprintf( __( 'Favorite %s', 'paxig-shoes' ), $name ) ); ?>"
		>
			<?php echo paxig_icon('heart', 20); ?>
		</button>
		<a href="<?php echo esc_url( $permalink ); ?>" class="vyre-card-img-link" tabindex="-1">
			<?php echo $product->get_image( 'woocommerce_thumbnail', [ 'loading' => 'lazy' ] ); ?>
		</a>
	</div>
	<div class="vyre-card-info">
		<div class="vyre-card-row vyre-loop-title-row">
			<h3 class="vyre-card-title woocommerce-loop-product__title">
				<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $name ); ?></a>
			</h3>
			<?php if ( $color ) : ?>
				<span class="vyre-card-color"><?php echo esc_html( $color ); ?></span>
			<?php endif; ?>
		</div>
		<?php if ( $subtitle ) : ?>
			<div class="vyre-card-subtitle paxig-reference-category"><?php echo esc_html( $subtitle ); ?></div>
		<?php endif; ?>
		<div class="vyre-card-footer">
			<div class="vyre-card-price price"><?php echo wp_kses_post( $price_html ); ?></div>
			<?php if ( $product->is_type( 'variable' ) ) : ?>
				<a
					href="<?php echo esc_url( $permalink ); ?>"
					class="vyre-card-add-to-cart paxig-add-to-cart-btn paxig-add-to-cart-btn--variable"
					aria-label="<?php echo esc_attr( sprintf( __( 'Select options for %s', 'paxig-shoes' ), $name ) ); ?>"
					title="<?php echo esc_attr( __( 'Select options', 'paxig-shoes' ) ); ?>"
				>
					<?php echo paxig_icon( 'shopping-bag', 18 ); ?>
				</a>
			<?php elseif ( $product->is_purchasable() && $product->is_in_stock() ) : ?>
				<button
					type="button"
					class="vyre-card-add-to-cart paxig-add-to-cart-btn"
					data-product-id="<?php echo esc_attr( $id ); ?>"
					aria-label="<?php echo esc_attr( sprintf( __( 'Add %s to cart', 'paxig-shoes' ), $name ) ); ?>"
					title="<?php echo esc_attr( __( 'Add to cart', 'paxig-shoes' ) ); ?>"
				>
					<?php echo paxig_icon( 'shopping-bag', 18 ); ?>
				</button>
			<?php endif; ?>
		</div>
	</div>
</li>
