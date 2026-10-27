<?php
require_once __DIR__ . '/includes/i18n.php';
require_once __DIR__ . '/includes/home-ui.php';
require_once __DIR__ . '/includes/style-3.php';
/**
 * Theme functions for PAXIG's Shoes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme setup.
 */
function paxig_shoes_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus(
		array(
			'primary' => 'Primary Menu',
		)
	);
}
add_action( 'after_setup_theme', 'paxig_shoes_setup' );

/**
 * Load theme CSS and JavaScript.
 */
function paxig_shoes_enqueue_assets() {
	wp_enqueue_style(
		'paxig-shoes-style',
		get_stylesheet_uri(),
		array(),
		filemtime( get_stylesheet_directory() . '/style.css' )
	);

	wp_enqueue_script(
		'paxig-shoes-header',
		get_template_directory_uri() . '/assets/js/header.js',
		array(),
		filemtime( get_template_directory() . '/assets/js/header.js' ),
		true
	);

	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_enqueue_style(
			'paxig-shoes-single-product',
			get_template_directory_uri() . '/assets/css/single-product.css',
			array( 'paxig-shoes-style' ),
			filemtime(
				get_template_directory()
				. '/assets/css/single-product.css'
			)
		);

		wp_enqueue_script(
			'paxig-shoes-single-product',
			get_template_directory_uri()
				. '/assets/js/single-product.js',
			array( 'jquery', 'wc-add-to-cart-variation' ),
			filemtime(
				get_template_directory()
				. '/assets/js/single-product.js'
			),
			true
		);

		wp_localize_script(
			'paxig-shoes-single-product',
			'paxigSingleProduct',
			array(
				'i18n' => array(
					'add_to_bag'         => __( 'Add to Bag', 'paxig-shoes' ),
					'unavailable_suffix' => __( ' — Out of stock or unavailable', 'paxig-shoes' ),
					'add_to_cart_error'  => __( 'Could not add item to bag.', 'paxig-shoes' ),
				),
			)
		);
	}
}
add_action(
	'wp_enqueue_scripts',
	'paxig_shoes_enqueue_assets'
);

/**
 * Keep optional Checkout Block labels consistent in Vietnamese.
 */
function paxig_shoes_checkout_optional_labels(
	$translated,
	$text,
	$domain
) {
	if (
		'woocommerce' !== $domain ||
		! function_exists( 'is_checkout' ) ||
		! is_checkout()
	) {
		return $translated;
	}

	$is_vi = function_exists( 'paxig_get_current_language' ) && paxig_get_current_language() === 'vi';
	if ( $is_vi ) {
		$labels = array(
			'Postal code (optional)' => 'Mã bưu điện (tùy chọn)',
			'Phone (optional)'       => 'Số điện thoại (tùy chọn)',
		);
	} else {
		$labels = array(
			'Postal code (optional)' => 'Postcode (optional)',
			'Phone (optional)'       => 'Phone (optional)',
		);
	}

	return isset( $labels[ $text ] )
		? $labels[ $text ]
		: $translated;
}
add_filter(
	'gettext',
	'paxig_shoes_checkout_optional_labels',
	20,
	3
);

/**
 * Mark the variation-selector rendering context.
 */
function paxig_shoes_start_variation_form() {
	$GLOBALS['paxig_rendering_variations'] = true;
}
add_action(
	'woocommerce_before_variations_form',
	'paxig_shoes_start_variation_form',
	0
);

function paxig_shoes_end_variation_form() {
	$GLOBALS['paxig_rendering_variations'] = false;
}
add_action(
	'woocommerce_after_variations_form',
	'paxig_shoes_end_variation_form',
	999
);

/**
 * Add Size Guide beside the product size selector.
 */
function paxig_shoes_add_size_guide_link(
	$label,
	$name,
	$product
) {
	if (
		! in_array( $name, [ 'pa_kich-thuoc', 'pa_size', 'size' ], true ) ||
		! function_exists( 'is_product' ) ||
		! is_product() ||
		empty( $GLOBALS['paxig_rendering_variations'] )
	) {
		return $label;
	}

	return 'Select Size'
		. ' <a class="paxig-size-guide-link" href="'
		. esc_url( home_url('/huong-dan/') )
		. '">Size Guide</a>';
}
add_filter(
	'woocommerce_attribute_label',
	'paxig_shoes_add_size_guide_link',
	10,
	3
);

/**
 * Render size options as buttons.
 */
function paxig_shoes_render_size_buttons( $html, $args ) {
	$attribute = isset( $args['attribute'] )
		? $args['attribute']
		: '';

	if (
		! function_exists( 'is_product' ) ||
		! is_product() ||
		! in_array( $attribute, [ 'pa_kich-thuoc', 'pa_size', 'size' ], true ) ||
		empty( $args['options'] )
	) {
		return $html;
	}

	$buttons = '';

	foreach ( $args['options'] as $option ) {
		$size_label = $option;

		if ( taxonomy_exists( $attribute ) ) {
			$term = get_term_by(
				'slug',
				$option,
				$attribute
			);

			if ( $term && ! is_wp_error( $term ) ) {
				$size_label = $term->name;
			}
		}

		$buttons .= sprintf(
			'<button type="button" class="paxig-size-button" data-value="%1$s">%2$s</button>',
			esc_attr( $option ),
			esc_html( $size_label )
		);
	}

	return '<div class="paxig-size-picker">'
		. '<div class="paxig-size-picker__native">'
		. $html
		. '</div>'
		. '<div class="paxig-size-picker__buttons"'
		. ' role="group" aria-label="' . esc_attr__( 'Select Size', 'paxig-shoes' ) . '">'
		. $buttons
		. '</div>'
		. '</div>';
}
add_filter(
	'woocommerce_dropdown_variation_attribute_options_html',
	'paxig_shoes_render_size_buttons',
	20,
	2
);

/**
 * Single-product Add to Bag text.
 */
function paxig_shoes_add_to_cart_text() {
	return __( 'Add to Bag', 'paxig-shoes' );
}
add_filter(
	'woocommerce_product_single_add_to_cart_text',
	'paxig_shoes_add_to_cart_text'
);

/**
 * Display a Nike-style subtitle below the product title.
 */
function paxig_shoes_product_subtitle() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$gender = $product->get_attribute( 'pa_gender' );
	$is_vi  = function_exists( 'paxig_get_current_language' ) && paxig_get_current_language() === 'vi';

	if ( $is_vi ) {
		$subtitle_map = array(
			'Men'    => 'Giày Nam',
			'Nam'    => 'Giày Nam',
			'Women'  => 'Giày Nữ',
			'Nữ'     => 'Giày Nữ',
			'Unisex' => 'Giày Unisex',
		);
		$default_subtitle = 'Giày';
	} else {
		$subtitle_map = array(
			'Men'    => "Men's Shoes",
			'Nam'    => "Men's Shoes",
			'Women'  => "Women's Shoes",
			'Nữ'     => "Women's Shoes",
			'Unisex' => 'Unisex Shoes',
		);
		$default_subtitle = 'Shoes';
	}

	$subtitle = isset( $subtitle_map[ $gender ] )
		? $subtitle_map[ $gender ]
		: $default_subtitle;

	echo '<p class="paxig-product-subtitle">'
		. esc_html( $subtitle )
		. '</p>';
}
add_action(
	'woocommerce_single_product_summary',
	'paxig_shoes_product_subtitle',
	6
);

/**
 * Display the product description in the product summary.
 */
function paxig_shoes_product_details_in_summary() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$description = get_post_field(
		'post_content',
		$product->get_id()
	);

	if (
		empty(
			trim(
				wp_strip_all_tags( $description )
			)
		)
	) {
		return;
	}

	echo '<section class="paxig-product-details">';
	echo '<h2>' . esc_html__( 'Product Details', 'paxig-shoes' ) . '</h2>';
	echo apply_filters( 'the_content', $description );
	echo '</section>';
}
add_action(
	'woocommerce_single_product_summary',
	'paxig_shoes_product_details_in_summary',
	35
);

/**
 * Configure product tabs.
 */
function paxig_shoes_product_tabs( $tabs ) {
	global $product;

	unset( $tabs['description'] );

	if ( isset( $tabs['additional_information'] ) ) {
		$tabs['additional_information']['title'] =
			__( 'Product Information', 'paxig-shoes' );
	}

	if (
		isset( $tabs['reviews'] ) &&
		$product instanceof WC_Product
	) {
		$tabs['reviews']['title'] = sprintf(
			__( 'Reviews (%d)', 'paxig-shoes' ),
			$product->get_review_count()
		);
	}

	return $tabs;
}
add_filter(
	'woocommerce_product_tabs',
	'paxig_shoes_product_tabs',
	98
);

/**
 * English product-information heading.
 */
function paxig_shoes_product_information_heading() {
	return 'Product Information';
}
add_filter(
	'woocommerce_product_additional_information_heading',
	'paxig_shoes_product_information_heading'
);

/**
 * English review text.
 */
function paxig_shoes_english_review_text(
	$translated,
	$text,
	$domain
) {
	if (
		'woocommerce' !== $domain ||
		! function_exists( 'is_product' ) ||
		! is_product()
	) {
		return $translated;
	}

	$review_text = array(
		'Reviews' =>
			'Reviews',
		'Add a review' =>
			'Add a Review',
		'There are no reviews yet.' =>
			'There are no reviews yet.',
		'Be the first to review “%s”' =>
			'Be the first to review “%s”',
		'Be the first to review &ldquo;%s&rdquo;' =>
			'Be the first to review “%s”',
		'Your rating' =>
			'Your Rating',
		'Your review' =>
			'Your Review',
		'Submit' =>
			'Submit',
	);

	return isset( $review_text[ $text ] )
		? $review_text[ $text ]
		: $translated;
}
add_filter(
	'gettext',
	'paxig_shoes_english_review_text',
	20,
	3
);

/**
 * Remove WooCommerce sidebar and technical product meta.
 */
function paxig_shoes_adjust_single_product() {
	remove_action(
		'woocommerce_sidebar',
		'woocommerce_get_sidebar',
		10
	);

	if (
		function_exists( 'is_product' ) &&
		is_product()
	) {
		remove_action(
			'woocommerce_single_product_summary',
			'woocommerce_template_single_meta',
			40
		);
	}
}
add_action(
	'wp',
	'paxig_shoes_adjust_single_product'
);

/**
 * Filter sale products when paxig_sale=1.
 */
function paxig_shoes_filter_sale_products( $query ) {
	if (
		is_admin() ||
		! $query->is_main_query() ||
		! $query->is_post_type_archive( 'product' )
	) {
		return;
	}

	$sale_filter = isset( $_GET['paxig_sale'] )
		? sanitize_text_field(
			wp_unslash( $_GET['paxig_sale'] )
		)
		: '';

	if ( '1' !== $sale_filter ) {
		return;
	}

	$sale_product_ids = wc_get_product_ids_on_sale();

	$query->set(
		'post__in',
		! empty( $sale_product_ids )
			? $sale_product_ids
			: array( 0 )
	);
}
add_action(
	'pre_get_posts',
	'paxig_shoes_filter_sale_products'
);

/**
 * Filter and sort Best Sellers.
 */
function paxig_shoes_filter_best_sellers( $query ) {
	if (
		is_admin() ||
		! $query->is_main_query() ||
		! $query->is_post_type_archive( 'product' )
	) {
		return;
	}

	$best_sellers_filter =
		isset( $_GET['paxig_best_sellers'] )
			? sanitize_text_field(
				wp_unslash(
					$_GET['paxig_best_sellers']
				)
			)
			: '';

	if ( '1' !== $best_sellers_filter ) {
		return;
	}

	$meta_query = $query->get( 'meta_query' );

	if ( ! is_array( $meta_query ) ) {
		$meta_query = array();
	}

	$meta_query['paxig_total_sales'] = array(
		'key'     => 'total_sales',
		'value'   => 0,
		'compare' => '>',
		'type'    => 'NUMERIC',
	);

	$query->set( 'meta_query', $meta_query );

	$query->set(
		'orderby',
		array(
			'paxig_total_sales' => 'DESC',
			'date'              => 'DESC',
		)
	);
}
add_action(
	'pre_get_posts',
	'paxig_shoes_filter_best_sellers'
);

/**
 * English archive title.
 */
function paxig_shoes_archive_title( $title ) {
	if (
		function_exists( 'is_shop' ) &&
		is_shop()
	) {
		return 'All Shoes';
	}

	return $title;
}
add_filter(
	'woocommerce_page_title',
	'paxig_shoes_archive_title'
);

/**
 * English catalog sorting labels.
 */
function paxig_shoes_catalog_orderby() {
	return array(
		'menu_order' => 'Featured',
		'popularity' => 'Popularity',
		'rating'     => 'Average Rating',
		'date'       => 'Newest',
		'price'      => 'Price: Low to High',
		'price-desc' => 'Price: High to Low',
	);
}
add_filter(
	'woocommerce_catalog_orderby',
	'paxig_shoes_catalog_orderby'
);

/**
 * Product-card button text.
 */
function paxig_shoes_loop_button_text(
	$text,
	$product
) {
	if (
		$product instanceof WC_Product &&
		$product->is_type( 'variable' )
	) {
		return 'Choose Options';
	}

	return 'Add to Bag';
}
add_filter(
	'woocommerce_product_add_to_cart_text',
	'paxig_shoes_loop_button_text',
	10,
	2
);

/**
 * Remaining English archive strings.
 */
function paxig_shoes_english_archive_text(
	$translated,
	$text,
	$domain
) {
	if (
		'woocommerce' !== $domain ||
		! function_exists( 'is_shop' ) ||
		! (
			is_shop() ||
			is_product_taxonomy()
		)
	) {
		return $translated;
	}

	$archive_text = array(
		'Home' =>
			'Home',
		'Showing the single result' =>
			'Showing 1 product',
		'Select options' =>
			'Choose Options',
	);

	return isset( $archive_text[ $text ] )
		? $archive_text[ $text ]
		: $translated;
}
add_filter(
	'gettext',
	'paxig_shoes_english_archive_text',
	20,
	3
);

/**
 * English plural archive result count.
 */
function paxig_shoes_archive_plural_text(
	$translation,
	$single,
	$plural,
	$number,
	$domain
) {
	if (
		'woocommerce' === $domain &&
		function_exists( 'is_shop' ) &&
		(
			is_shop() ||
			is_product_taxonomy()
		) &&
		'Showing all %1$d results' === $plural
	) {
		return 'Showing all %1$d products';
	}

	return $translation;
}
add_filter(
	'ngettext',
	'paxig_shoes_archive_plural_text',
	20,
	5
);

/**
 * Centered shell around shop archives.
 */
function paxig_shoes_shop_shell_open() {
	if (
		function_exists( 'is_shop' ) &&
		(
			is_shop() ||
			is_product_taxonomy()
		)
	) {
		echo '<div class="paxig-shop-shell">';
	}
}
add_action(
	'woocommerce_before_main_content',
	'paxig_shoes_shop_shell_open',
	1
);

function paxig_shoes_shop_shell_close() {
	if (
		function_exists( 'is_shop' ) &&
		(
			is_shop() ||
			is_product_taxonomy()
		)
	) {
		echo '</div>';
	}
}
add_action(
	'woocommerce_after_main_content',
	'paxig_shoes_shop_shell_close',
	99
);

/**
 * Open shop layout and output category sidebar.
 */
function paxig_shoes_shop_layout_open() {
	if (
		! function_exists( 'is_shop' ) ||
		! (
			is_shop() ||
			is_product_taxonomy()
		)
	) {
		return;
	}

	echo '<div class="paxig-shop-layout">';
	echo '<aside class="paxig-shop-sidebar">';
	echo '<h2>' . esc_html__( 'Category', 'paxig-shoes' ) . '</h2>';
	echo '<ul>';

	$category_terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( ! is_wp_error( $category_terms ) ) {
		foreach ( $category_terms as $category_term ) {
			if (
				in_array(
					$category_term->name,
					array(
						'Shoes',
						'Uncategorized',
						'Chưa phân loại',
					),
					true
				)
			) {
				continue;
			}

			$category_url =
				get_term_link( $category_term );

			if ( is_wp_error( $category_url ) ) {
				continue;
			}

			printf(
				'<li class="cat-item cat-item-%1$d"><a href="%2$s">%3$s</a></li>',
				(int) $category_term->term_id,
				esc_url( $category_url ),
				esc_html( $category_term->name )
			);
		}
	}

	echo '</ul>';
	echo '</aside>';
	echo '<div class="paxig-shop-products">';
}
add_action(
	'woocommerce_before_shop_loop',
	'paxig_shoes_shop_layout_open',
	5
);

/**
 * Close shop layout.
 */
function paxig_shoes_shop_layout_close() {
	if (
		! function_exists( 'is_shop' ) ||
		! (
			is_shop() ||
			is_product_taxonomy()
		)
	) {
		return;
	}

	echo '</div>';
	echo '</div>';
}
add_action(
	'woocommerce_after_shop_loop',
	'paxig_shoes_shop_layout_close',
	99
);

require_once get_template_directory().'/includes/ui-compat.php';
require_once get_template_directory().'/includes/reference-ui.php';
require_once get_template_directory().'/includes/plp-ui.php';

require_once get_template_directory().'/includes/store-ui.php';

require_once get_template_directory() . '/includes/auth-ui.php';
require_once get_template_directory() . '/includes/email-verification.php';
require_once get_template_directory() . '/includes/customer-account.php';

require_once get_template_directory() . '/includes/checkout-alignment.php';
require_once get_template_directory() . '/includes/mini-cart.php';
require_once get_template_directory() . '/includes/wishlist.php';
