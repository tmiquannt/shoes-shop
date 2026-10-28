<?php
/**
 * Footer của theme PAXG'Shop.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<footer class="paxig-footer vyre-footer">
	<div class="paxig-container vyre-footer-inner">
		<div class="vyre-footer-brand">
			<a class="paxig-brand paxig-brand--footer" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr__( "PAXG'Shop", 'paxig-shoes' ); ?>">
				<img class="paxig-brand__mark" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/paxg-shop-symbol-header.png' ); ?>" width="176" height="166" alt="" aria-hidden="true">
				<span class="paxig-brand__wordmark"><span class="paxig-brand__wordmark-primary">PAXG</span><span class="paxig-brand__wordmark-shop">'Shop</span></span>
			</a>
		</div>

		<div class="vyre-footer-tagline">
			<?php echo esc_html__( 'BUILT DIFFERENT. MOVE FORWARD.', 'paxig-shoes' ); ?>
		</div>

		<div class="vyre-footer-meta">
			<span class="vyre-footer-copy">© <?php echo esc_html( wp_date( 'Y' ) ); ?> PAXG'Shop. <?php echo esc_html__( 'ALL RIGHTS RESERVED.', 'paxig-shoes' ); ?></span>
			<a href="#" class="vyre-footer-link">INSTAGRAM</a>
			<a href="<?php echo esc_url( home_url('/huong-dan/') ); ?>" class="vyre-footer-link"><?php echo esc_html__( 'SUPPORT', 'paxig-shoes' ); ?></a>
			<a href="<?php echo esc_url( home_url('/chinh-sach-bao-mat/') ); ?>" class="vyre-footer-link"><?php echo esc_html__( 'PRIVACY', 'paxig-shoes' ); ?></a>
		</div>
	</div>
</footer>

<?php
get_template_part( 'template-parts/mobile-bottom-nav' );
wp_footer();
?>
</body>
</html>
