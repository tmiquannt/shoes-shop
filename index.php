<?php
get_header();
?>

<main class="paxig-main">
	<div class="paxig-container">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : ?>
				<?php the_post(); ?>

				<article>
					<h1><?php if ( function_exists('paxig_auth_mode') && paxig_auth_mode() === 'register' ) echo esc_html__( 'Create Account', 'paxig-shoes' ); else the_title(); ?></h1>
					<?php the_content(); ?>
				</article>

			<?php endwhile; ?>
		<?php else : ?>
			<div class="paxig-notice">
				<h1><?php echo is_404() ? esc_html__( 'Page Not Found', 'paxig-shoes' ) : esc_html__( "PAXIG's Shoes", 'paxig-shoes' ); ?></h1>
				<p><?php echo is_404() ? esc_html__( "The page you are looking for doesn't exist or has been moved.", 'paxig-shoes' ) : esc_html__( 'Explore our footwear collections.', 'paxig-shoes' ); ?></p>
				<p><a class="button" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ); ?>"><?php echo esc_html__( 'Shop All Shoes', 'paxig-shoes' ); ?></a></p>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
