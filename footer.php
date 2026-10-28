<?php
/**
 * Footer của theme VYRE / PAXIG Shoes matching FULL_UI_PAXG mockups.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<footer class="paxig-footer vyre-footer">
	<div class="paxig-container vyre-footer-inner">
		<div class="vyre-footer-brand">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<span>VYRE<small class="vyre-tm">™</small></span>
			</a>
		</div>

		<div class="vyre-footer-tagline">
			<?php echo esc_html__( 'BUILT DIFFERENT. MOVE FORWARD.', 'paxig-shoes' ); ?>
		</div>

		<div class="vyre-footer-meta">
			<span class="vyre-footer-copy">© <?php echo esc_html( wp_date( 'Y' ) ); ?> VYRE. <?php echo esc_html__( 'ALL RIGHTS RESERVED.', 'paxig-shoes' ); ?></span>
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
