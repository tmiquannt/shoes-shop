<?php
/**
 * Footer của theme PAXIG's Shoes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php
$footer_shop_url = function_exists( 'wc_get_page_permalink' )
	? wc_get_page_permalink( 'shop' )
	: home_url( '/cua-hang/' );

$footer_men_url = add_query_arg(
	array(
		'filter_gender'     => 'men,unisex',
		'query_type_gender' => 'or',
	),
	$footer_shop_url
);

$footer_women_url = add_query_arg(
	array(
		'filter_gender'     => 'women,unisex',
		'query_type_gender' => 'or',
	),
	$footer_shop_url
);

$footer_sale_url = add_query_arg(
	'paxig_sale',
	'1',
	$footer_shop_url
);
?>

<footer class="paxig-footer">
	<div class="paxig-container">
		<div class="paxig-footer__grid">
			<div class="paxig-footer__brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					PAXIG
				</a>
				<p>
					Shoes for every move, every day and every style.
				</p>
			</div>

			<nav class="paxig-footer__column" aria-label="Footer shop navigation">
				<h2>Shop</h2>
				<a href="<?php echo esc_url( $footer_shop_url ); ?>">All Shoes</a>
				<a href="<?php echo esc_url( $footer_men_url ); ?>">Men</a>
				<a href="<?php echo esc_url( $footer_women_url ); ?>">Women</a>
				<a href="<?php echo esc_url( $footer_sale_url ); ?>">Sale</a>
			</nav>

			<nav class="paxig-footer__column" aria-label="Footer help navigation">
				<h2>Help</h2>
				<a href="<?php echo esc_url( home_url('/huong-dan/') ); ?>">Size Guide</a>
				<a href="<?php echo esc_url( home_url('/huong-dan/') ); ?>">Shipping &amp; Returns</a>
				<a href="<?php echo esc_url( wc_get_page_permalink('myaccount') ); ?>">My Account</a>
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>">Shopping Bag</a>
			</nav>

			<nav class="paxig-footer__column" aria-label="About PAXIG">
				<h2>About</h2>
				<a href="<?php echo esc_url(home_url('/ve-paxig/')); ?>">Our Store</a>
				<a href="<?php echo esc_url(home_url('/huong-dan/')); ?>">Shopping Guide</a>
				<?php foreach (['refund_returns'=>'Returns & Refunds','privacy-policy'=>'Privacy Policy'] as $slug=>$label) : $policy = get_page_by_path($slug); if ($policy && $policy->post_status === 'publish') : ?>
				<a href="<?php echo esc_url(get_permalink($policy)); ?>"><?php echo esc_html($label); ?></a>
				<?php endif; endforeach; ?>
			</nav>
		</div>

		<div class="paxig-footer__bottom">
			<p>
				© <?php echo esc_html( wp_date( 'Y' ) ); ?> PAXIG's Shoes.
				All rights reserved.
			</p>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
