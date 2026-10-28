<?php
/**
 * Footer của theme PAXG'Shop.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$instagram_url = apply_filters( 'paxig_instagram_url', get_theme_mod( 'paxig_instagram_url', '' ) );
$instagram_url = is_string( $instagram_url ) ? esc_url( $instagram_url, array( 'http', 'https' ) ) : '';
$instagram_host = $instagram_url ? wp_parse_url( $instagram_url, PHP_URL_HOST ) : '';
$guide_url = function_exists( 'paxig_storefront_page_url' ) ? paxig_storefront_page_url( 'guide' ) : '';
$privacy_url = function_exists( 'paxig_storefront_page_url' ) ? paxig_storefront_page_url( 'privacy' ) : '';
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
			<?php if ( $instagram_url && $instagram_host ) : ?>
				<a href="<?php echo esc_url( $instagram_url ); ?>" class="vyre-footer-link" target="_blank" rel="noopener noreferrer">INSTAGRAM</a>
			<?php endif; ?>
			<?php if ( $guide_url ) : ?>
				<a href="<?php echo esc_url( $guide_url ); ?>" class="vyre-footer-link"><?php echo esc_html__( 'SUPPORT', 'paxig-shoes' ); ?></a>
			<?php endif; ?>
			<?php if ( $privacy_url ) : ?>
				<a href="<?php echo esc_url( $privacy_url ); ?>" class="vyre-footer-link"><?php echo esc_html__( 'PRIVACY', 'paxig-shoes' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</footer>

<?php
get_template_part( 'template-parts/mobile-bottom-nav' );
wp_footer();
?>
</body>
</html>
