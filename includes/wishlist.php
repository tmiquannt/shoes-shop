<?php
/**
 * Wishlist / Favorites System for PAXG'Shop.
 * Production-ready WooCommerce integration benchmarked against Nike VN Favorites UX.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'PAXIG_WISHLIST_MAX_IDS' ) ) {
	define( 'PAXIG_WISHLIST_MAX_IDS', 100 );
}

/** Validate a bounded list of positive integer product IDs without coercion. */
function paxig_wishlist_normalize_product_ids( $value, &$valid = null ) {
	$valid = false;
	if ( ! is_array( $value ) || count( $value ) > PAXIG_WISHLIST_MAX_IDS ) {
		return [];
	}

	$ids = [];
	foreach ( $value as $candidate ) {
		if ( is_int( $candidate ) ) {
			$id = $candidate;
		} elseif ( is_string( $candidate ) && 1 === preg_match( '/^[1-9][0-9]*$/D', $candidate ) ) {
			$id = filter_var( $candidate, FILTER_VALIDATE_INT, [ 'options' => [ 'min_range' => 1 ] ] );
			if ( false === $id ) {
				return [];
			}
		} else {
			return [];
		}

		if ( $id < 1 ) {
			return [];
		}
		$ids[] = $id;
	}

	$valid = true;
	return array_values( array_unique( $ids ) );
}

/**
 * Render Wishlist button on loop product cards.
 */
function paxig_wishlist_card_button() {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	$product_id = $product->get_id();
	?>
	<button
		type="button"
		class="paxig-wishlist-btn paxig-wishlist-card-btn"
		data-product-id="<?php echo esc_attr( $product_id ); ?>"
		aria-label="<?php echo esc_attr( sprintf( __( 'Favorite %s', 'paxig-shoes' ), $product->get_name() ) ); ?>"
	>
		<?php echo paxig_icon('heart', 20); ?>
	</button>
	<?php
}
add_action( 'woocommerce_before_shop_loop_item_title', 'paxig_wishlist_card_button', 22 );

/**
 * Render Wishlist button on Single Product Page (beside/below Add to Cart).
 */
function paxig_wishlist_pdp_button() {
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	$product_id = $product->get_id();
	?>
	<button
		type="button"
		class="paxig-wishlist-btn paxig-wishlist-pdp-btn"
		data-product-id="<?php echo esc_attr( $product_id ); ?>"
		aria-label="<?php echo esc_attr( sprintf( __( 'Favorite %s', 'paxig-shoes' ), $product->get_name() ) ); ?>"
	>
		<?php echo paxig_icon('heart', 22); ?>
		<span class="paxig-wishlist-btn-text"><?php echo esc_html__( 'Favorite', 'paxig-shoes' ); ?></span>
	</button>
	<?php
}
add_action( 'woocommerce_after_add_to_cart_button', 'paxig_wishlist_pdp_button', 10 );

/**
 * Render the Wishlist drawer in wp_footer.
 */
function paxig_render_wishlist_drawer() {
	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	?>
	<dialog id="paxig-wishlist-drawer" class="paxig-drawer paxig-wishlist-drawer" aria-labelledby="paxig-wishlist-drawer-title">
		<div class="paxig-drawer-panel">
			<div class="paxig-drawer-header">
				<h2 id="paxig-wishlist-drawer-title" class="paxig-drawer-title">
					<?php echo esc_html__( 'Favorites', 'paxig-shoes' ); ?> (<span class="paxig-wishlist-count">0</span>)
				</h2>
				<button type="button" class="paxig-drawer-close" aria-label="<?php echo esc_attr__( 'Close favorites', 'paxig-shoes' ); ?>"><?php echo paxig_icon('x', 22); ?></button>
			</div>

			<div class="paxig-wishlist-drawer-content">
				<div class="paxig-drawer-empty paxig-wishlist-empty">
					<div class="paxig-drawer-empty__icon" aria-hidden="true">
						<?php echo paxig_icon('heart', 48); ?>
					</div>
					<h3><?php echo esc_html__( 'Your Wishlist is Empty', 'paxig-shoes' ); ?></h3>
					<p><?php echo esc_html__( 'Explore our catalogue and tap the heart icon on any pair to save it here.', 'paxig-shoes' ); ?></p>
					<a class="paxig-drawer-btn paxig-drawer-btn--primary" href="<?php echo esc_url( $shop_url ); ?>">
						<?php echo esc_html__( 'Explore Shoes', 'paxig-shoes' ); ?>
					</a>
				</div>

				<div class="paxig-wishlist-drawer-items" role="list"></div>
			</div>
		</div>
	</dialog>
	<?php
}
add_action( 'wp_footer', 'paxig_render_wishlist_drawer', 55 );

/**
 * AJAX endpoint to fetch product details for wishlist IDs.
 */
function paxig_ajax_get_wishlist_details() {
	check_ajax_referer( 'paxig_wishlist_nonce', 'nonce' );

	$submitted_ids = isset( $_POST['product_ids'] ) ? $_POST['product_ids'] : [];
	$ids           = paxig_wishlist_normalize_product_ids( $submitted_ids, $valid );
	if ( ! $valid ) {
		wp_send_json_error( [ 'message' => __( 'Invalid favorites list.', 'woocommerce' ) ], 400 );
	}

	if ( empty( $ids ) ) {
		wp_send_json_success( [ 'items' => [] ] );
	}

	$items = [];
	foreach ( $ids as $id ) {
		$product = wc_get_product( $id );
		if ( ! $product || ! $product->is_visible() ) {
			continue;
		}

		$items[] = [
			'id'        => $product->get_id(),
			'name'      => $product->get_name(),
			'price'     => html_entity_decode( wp_strip_all_tags( $product->get_price_html() ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
			'permalink' => $product->get_permalink(),
			'image_url' => $product->get_image_id() ? wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_thumbnail' ) : '',
			'in_stock'  => $product->is_in_stock(),
		];
	}

	wp_send_json_success( [ 'items' => $items ] );
}
add_action( 'wp_ajax_paxig_get_wishlist_details', 'paxig_ajax_get_wishlist_details' );
add_action( 'wp_ajax_nopriv_paxig_get_wishlist_details', 'paxig_ajax_get_wishlist_details' );

/**
 * AJAX endpoint to sync wishlist with logged-in user meta.
 */
function paxig_ajax_sync_user_wishlist() {
	check_ajax_referer( 'paxig_wishlist_nonce', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( [ 'message' => __( 'Sign in to sync favorites.', 'woocommerce' ) ], 401 );
	}

	$user_id       = get_current_user_id();
	$submitted_ids = isset( $_POST['product_ids'] ) ? $_POST['product_ids'] : [];
	$saved_ids     = paxig_wishlist_normalize_product_ids( $submitted_ids, $valid );
	if ( ! $valid ) {
		wp_send_json_error( [ 'message' => __( 'Invalid favorites list.', 'woocommerce' ) ], 400 );
	}

	$stored_ids = get_user_meta( $user_id, 'paxig_wishlist', true );
	if ( ! ( is_array( $stored_ids ) && $stored_ids === $saved_ids ) ) {
		if ( false === update_user_meta( $user_id, 'paxig_wishlist', $saved_ids ) ) {
			wp_send_json_error( [ 'message' => __( 'Could not save favorites. Please try again.', 'woocommerce' ) ], 500 );
		}
	}

	wp_send_json_success( [ 'saved_ids' => $saved_ids ] );
}
add_action( 'wp_ajax_paxig_sync_user_wishlist', 'paxig_ajax_sync_user_wishlist' );

/**
 * Enqueue wishlist scripts and styles.
 */
function paxig_enqueue_wishlist_assets() {
	if ( is_admin() ) {
		return;
	}

	$css_path = get_template_directory() . '/assets/css/wishlist.css';
	$js_path  = get_template_directory() . '/assets/js/wishlist.js';

	wp_enqueue_style(
		'paxig-wishlist',
		get_template_directory_uri() . '/assets/css/wishlist.css',
		[ 'paxig-style-3' ],
		file_exists( $css_path ) ? filemtime( $css_path ) : '1.0.0'
	);

	wp_enqueue_script(
		'paxig-wishlist',
		get_template_directory_uri() . '/assets/js/wishlist.js',
		[],
		file_exists( $js_path ) ? filemtime( $js_path ) : '1.0.0',
		true
	);

	$initial_user_ids = [];
	if ( is_user_logged_in() ) {
		$stored_user_ids = get_user_meta( get_current_user_id(), 'paxig_wishlist', true );
		$initial_user_ids = paxig_wishlist_normalize_product_ids( is_array( $stored_user_ids ) ? $stored_user_ids : [], $valid );
		if ( ! $valid ) {
			$initial_user_ids = [];
		}
	}

	wp_localize_script( 'paxig-wishlist', 'paxigWishlist', [
		'ajax_url'     => admin_url( 'admin-ajax.php' ),
		'nonce'        => wp_create_nonce( 'paxig_wishlist_nonce' ),
		'is_logged_in' => is_user_logged_in(),
		'user_ids'     => array_values( array_filter( $initial_user_ids ) ),
		'i18n'         => [
			'read_error'   => __( 'Could not read saved favorites on this device.', 'paxig-shoes' ),
			'save_error'   => __( 'Could not save favorites on this device.', 'paxig-shoes' ),
			'update_error' => __( 'Could not update favorites. Please try again.', 'paxig-shoes' ),
			'sync_error'   => __( 'Could not sync favorites to your account. Please try again.', 'paxig-shoes' ),
			'max_limit'    => __( 'You can save up to 100 favorites.', 'paxig-shoes' ),
			'remove'       => __( 'Remove from favorites', 'paxig-shoes' ),
			'select_size'  => __( 'Select Size / View →', 'paxig-shoes' ),
			'out_of_stock' => __( 'Out of stock', 'paxig-shoes' ),
			'loading'      => __( 'Loading saved shoes…', 'paxig-shoes' ),
			'detail_error' => __( 'Could not load favorites. Please try again.', 'paxig-shoes' ),
			'favorite'     => __( 'Favorite', 'paxig-shoes' ),
			'favorited'    => __( 'Favorited', 'paxig-shoes' ),
			'add_favorite' => __( 'Add to favorites', 'paxig-shoes' ),
			'remove_favorite' => __( 'Remove from favorites', 'paxig-shoes' ),
		],
	] );
}
add_action( 'wp_enqueue_scripts', 'paxig_enqueue_wishlist_assets', 106 );
