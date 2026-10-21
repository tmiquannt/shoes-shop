<?php
get_header();
?>

<main class="paxig-main">
	<div class="paxig-container">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : ?>
				<?php the_post(); ?>

				<article>
					<h1><?php if ( function_exists('paxig_auth_mode') && paxig_auth_mode() === 'register' ) echo 'Create Account'; else the_title(); ?></h1>
					<?php the_content(); ?>
				</article>

			<?php endwhile; ?>
		<?php else : ?>
			<div class="paxig-notice">
				<h1>PAXIG's Shoes</h1>
				<p>Explore our footwear collections.</p>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();