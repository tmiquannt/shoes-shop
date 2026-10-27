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
					<?php echo esc_html__( 'Shoes for every move, every day and every style.', 'paxig-shoes' ); ?>
				</p>
			</div>

			<nav class="paxig-footer__column" aria-label="<?php echo esc_attr__( 'Footer shop navigation', 'paxig-shoes' ); ?>">
				<h2><?php echo esc_html__( 'Shop', 'paxig-shoes' ); ?></h2>
				<a href="<?php echo esc_url( $footer_shop_url ); ?>"><?php echo esc_html__( 'All Shoes', 'paxig-shoes' ); ?></a>
				<a href="<?php echo esc_url( $footer_men_url ); ?>"><?php echo esc_html__( 'Men', 'paxig-shoes' ); ?></a>
				<a href="<?php echo esc_url( $footer_women_url ); ?>"><?php echo esc_html__( 'Women', 'paxig-shoes' ); ?></a>
				<a href="<?php echo esc_url( $footer_sale_url ); ?>"><?php echo esc_html__( 'Sale', 'paxig-shoes' ); ?></a>
			</nav>

			<nav class="paxig-footer__column" aria-label="<?php echo esc_attr__( 'Footer help navigation', 'paxig-shoes' ); ?>">
				<h2><?php echo esc_html__( 'Help', 'paxig-shoes' ); ?></h2>
				<a href="<?php echo esc_url( home_url('/huong-dan/') ); ?>"><?php echo esc_html__( 'Size Guide', 'paxig-shoes' ); ?></a>
				<a href="<?php echo esc_url( home_url('/huong-dan/') ); ?>"><?php echo esc_html__( 'Shipping & Returns', 'paxig-shoes' ); ?></a>
				<a href="<?php echo esc_url( wc_get_page_permalink('myaccount') ); ?>"><?php echo esc_html__( 'My Account', 'paxig-shoes' ); ?></a>
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php echo esc_html__( 'Shopping Bag', 'paxig-shoes' ); ?></a>
			</nav>

			<nav class="paxig-footer__column" aria-label="<?php echo esc_attr__( 'About PAXIG', 'paxig-shoes' ); ?>">
				<h2><?php echo esc_html__( 'About', 'paxig-shoes' ); ?></h2>
				<a href="<?php echo esc_url(home_url('/ve-paxig/')); ?>"><?php echo esc_html__( 'Our Store', 'paxig-shoes' ); ?></a>
				<a href="<?php echo esc_url(home_url('/huong-dan/')); ?>"><?php echo esc_html__( 'Shopping Guide', 'paxig-shoes' ); ?></a>
				<?php
				$policies = [
					'refund_returns' => __( 'Returns & Refunds', 'paxig-shoes' ),
					'privacy-policy' => __( 'Privacy Policy', 'paxig-shoes' ),
				];
				foreach ( $policies as $slug => $label ) :
					$policy = get_page_by_path( $slug );
					if ( $policy && $policy->post_status === 'publish' ) : ?>
					<a href="<?php echo esc_url( get_permalink( $policy ) ); ?>"><?php echo esc_html( $label ); ?></a>
					<?php endif;
				endforeach; ?>
			</nav>
		</div>

		<div class="paxig-footer__bottom">
			<p>
				© <?php echo esc_html( wp_date( 'Y' ) ); ?> PAXIG's Shoes.
				<?php echo esc_html__( 'All rights reserved.', 'paxig-shoes' ); ?>
			</p>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
