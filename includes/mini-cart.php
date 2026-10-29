<?php
/**
 * Mini-Cart Slide-Out Drawer for PAXG'Shop.
 * Production-ready WooCommerce integration benchmarked against Nike VN UX.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate HTML content for the mini-cart drawer.
 */
function paxig_mini_cart_drawer_content() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '';
	}

	ob_start();
	$cart = WC()->cart;
	$items = $cart->get_cart();
	$count = $cart->get_cart_contents_count();
	$subtotal = $cart->get_cart_subtotal();
	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
	$checkout_url = wc_get_checkout_url();
	$cart_url = wc_get_cart_url();
	?>
	<div class="paxig-cart-drawer-content">
		<?php if ( empty( $items ) ) : ?>
			<div class="paxig-drawer-empty">
				<div class="paxig-drawer-empty__icon" aria-hidden="true">
					<?php echo paxig_icon('shopping-bag', 48); ?>
				</div>
				<h3><?php echo esc_html__( 'Your Shopping Bag is Empty', 'paxig-shoes' ); ?></h3>
				<p><?php echo esc_html__( 'Looks like you haven’t added any sneakers to your bag yet.', 'paxig-shoes' ); ?></p>
				<a class="paxig-drawer-btn paxig-drawer-btn--primary" href="<?php echo esc_url( $shop_url ); ?>">
					<?php echo esc_html__( 'Shop All Shoes', 'paxig-shoes' ); ?>
				</a>
			</div>
		<?php else : ?>
			<div class="paxig-cart-drawer-items" role="list">
				<?php
				foreach ( $items as $cart_item_key => $cart_item ) :
					$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
					if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) {
						continue;
					}

					$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
					$product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
					$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_thumbnail' ), $cart_item, $cart_item_key );
					$product_price = apply_filters( 'woocommerce_cart_item_price', $cart->get_product_price( $_product ), $cart_item, $cart_item_key );
					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					$item_subtotal = apply_filters( 'woocommerce_cart_item_subtotal', $cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
					?>
					<div class="paxig-cart-drawer-item" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>" role="listitem">
						<div class="paxig-cart-drawer-item__media">
							<?php if ( $product_permalink ) : ?>
								<a href="<?php echo esc_url( $product_permalink ); ?>"><?php echo $thumbnail; // phpcs:ignore ?></a>
							<?php else : ?>
								<?php echo $thumbnail; // phpcs:ignore ?>
							<?php endif; ?>
						</div>

						<div class="paxig-cart-drawer-item__info">
							<div class="paxig-cart-drawer-item__header">
								<h4 class="paxig-cart-drawer-item__title">
									<?php if ( $product_permalink ) : ?>
										<a href="<?php echo esc_url( $product_permalink ); ?>"><?php echo esc_html( $product_name ); ?></a>
									<?php else : ?>
										<?php echo esc_html( $product_name ); ?>
									<?php endif; ?>
								</h4>
								<button type="button" class="paxig-cart-item-remove" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Remove %s from bag', 'paxig-shoes' ), $product_name ) ); ?>">
									<?php echo paxig_icon('x', 20); ?>
								</button>
							</div>

							<?php
							// Output variations (e.g., Size, Color)
							if ( ! empty( $cart_item['variation'] ) ) :
								?>
								<div class="paxig-cart-drawer-item__meta">
									<?php
									foreach ( $cart_item['variation'] as $name => $value ) {
										$taxonomy = str_replace( 'attribute_', '', $name );
										$label = wc_attribute_label( $taxonomy );
										$term = get_term_by( 'slug', $value, $taxonomy );
										$val_label = ( $term && ! is_wp_error( $term ) ) ? $term->name : $value;
										echo '<span class="paxig-cart-drawer-item__attr"><strong>' . esc_html( $label ) . ':</strong> ' . esc_html( $val_label ) . '</span> ';
									}
									?>
								</div>
							<?php endif; ?>

							<div class="paxig-cart-drawer-item__footer">
								<div class="paxig-cart-qty-control" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
									<button type="button" class="paxig-qty-btn paxig-qty-btn--minus" aria-label="<?php echo esc_attr__( 'Decrease quantity', 'paxig-shoes' ); ?>" <?php echo $cart_item['quantity'] <= 1 ? 'disabled' : ''; ?>><?php echo paxig_icon('minus', 16); ?></button>
									<span class="paxig-qty-val" aria-live="polite"><?php echo esc_html( $cart_item['quantity'] ); ?></span>
									<button type="button" class="paxig-qty-btn paxig-qty-btn--plus" aria-label="<?php echo esc_attr__( 'Increase quantity', 'paxig-shoes' ); ?>"><?php echo paxig_icon('plus', 16); ?></button>
								</div>
								<div class="paxig-cart-drawer-item__price">
									<?php echo wp_kses_post( $item_subtotal ); ?>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="paxig-cart-drawer-footer">
				<div class="paxig-cart-drawer-subtotal">
					<span><?php echo esc_html__( 'Subtotal', 'paxig-shoes' ); ?></span>
					<strong><?php echo wp_kses_post( $subtotal ); ?></strong>
				</div>
				<p class="paxig-cart-drawer-note">
					<?php echo function_exists( 'paxig_shipping_summary' ) ? esc_html( paxig_shipping_summary() ) : esc_html__( 'Shipping and taxes calculated at checkout.', 'paxig-shoes' ); ?>
				</p>
				<div class="paxig-cart-drawer-actions">
					<a class="paxig-drawer-btn paxig-drawer-btn--primary" href="<?php echo esc_url( $checkout_url ); ?>">
						<?php echo esc_html__( 'Checkout', 'paxig-shoes' ); ?>
					</a>
					<a class="paxig-drawer-btn paxig-drawer-btn--secondary" href="<?php echo esc_url( $cart_url ); ?>">
						<?php echo esc_html__( 'View Shopping Bag', 'paxig-shoes' ); ?>
					</a>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Render the mini-cart drawer in wp_footer.
 */
function paxig_render_cart_drawer() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return;
	}
	$count = WC()->cart->get_cart_contents_count();
	?>
	<dialog id="paxig-cart-drawer" class="paxig-drawer paxig-cart-drawer" aria-labelledby="paxig-cart-drawer-title">
		<div class="paxig-drawer-panel">
			<div class="paxig-drawer-header">
				<h2 id="paxig-cart-drawer-title" class="paxig-drawer-title">
					<?php echo esc_html__( 'Shopping Bag', 'paxig-shoes' ); ?> (<span class="paxig-cart-count"><?php echo esc_html( $count ); ?></span>)
				</h2>
				<button type="button" class="paxig-drawer-close" aria-label="<?php echo esc_attr__( 'Close bag', 'paxig-shoes' ); ?>"><?php echo paxig_icon('x', 22); ?></button>
			</div>
			<div class="paxig-drawer-alert" role="alert" aria-live="assertive" style="display:none;"></div>
			<?php echo paxig_mini_cart_drawer_content(); // phpcs:ignore ?>
		</div>
	</dialog>
	<?php
}
add_action( 'wp_footer', 'paxig_render_cart_drawer', 50 );

/**
 * Update mini-cart fragments via WooCommerce AJAX.
 */
function paxig_mini_cart_fragments( $fragments ) {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return $fragments;
	}

	$count = WC()->cart->get_cart_contents_count();
	$fragments['span.paxig-cart-count'] = '<span class="paxig-cart-count">' . esc_html( $count ) . '</span>';
	$fragments['div.paxig-cart-drawer-content'] = paxig_mini_cart_drawer_content();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'paxig_mini_cart_fragments', 50 );

/**
 * Validate and perform cart quantity updates safely.
 *
 * @param string        $cart_item_key Session cart key.
 * @param mixed         $raw_quantity  Raw quantity input.
 * @param WC_Cart|null  $cart          Cart instance (or null to use WC()->cart).
 * @return array Result array with 'success' (bool) and either 'message' or updated state.
 */
function paxig_validate_and_update_cart_quantity( $cart_item_key, $raw_quantity, $cart = null ) {
	if ( null === $cart ) {
		$cart = function_exists( 'WC' ) && WC()->cart ? WC()->cart : null;
	}

	if ( ! $cart ) {
		return [ 'success' => false, 'message' => __( 'No active shopping bag session found.', 'paxig-shoes' ) ];
	}

	// 1. Strict cart_item_key check
	if ( ! is_string( $cart_item_key ) || '' === trim( $cart_item_key ) ) {
		return [ 'success' => false, 'message' => __( 'Invalid cart item key.', 'paxig-shoes' ) ];
	}

	$cart_item_key = sanitize_text_field( $cart_item_key );
	$cart_contents = $cart->get_cart();

	if ( ! isset( $cart_contents[ $cart_item_key ] ) ) {
		return [ 'success' => false, 'message' => __( 'The selected shoe is no longer in your bag. Please refresh.', 'paxig-shoes' ) ];
	}

	// 2. Strict scalar non-negative integer input validation (no absint on floats/negatives/arrays)
	if ( ! is_scalar( $raw_quantity ) || is_bool( $raw_quantity ) ) {
		return [ 'success' => false, 'message' => __( 'Quantity must be a valid integer.', 'paxig-shoes' ) ];
	}

	$str_qty = trim( (string) $raw_quantity );
	if ( '' === $str_qty || ! preg_match( '/^\d+$/', $str_qty ) ) {
		return [ 'success' => false, 'message' => __( 'Quantity must be a valid non-negative integer.', 'paxig-shoes' ) ];
	}

	if ( strlen( $str_qty ) > 4 ) {
		return [ 'success' => false, 'message' => __( 'Requested quantity exceeds the allowable limit.', 'paxig-shoes' ) ];
	}

	$quantity = (int) $str_qty;

	// 3. Zero quantity removes the item
	if ( 0 === $quantity ) {
		$removed = $cart->remove_cart_item( $cart_item_key );
		if ( $removed ) {
			$cart->calculate_totals();
			return [
				'success' => true,
				'action'  => 'removed',
				'count'   => $cart->get_cart_contents_count(),
			];
		}
		return [ 'success' => false, 'message' => __( 'Could not remove shoe from bag.', 'paxig-shoes' ) ];
	}

	// 4. Product checks (exists, purchasable)
	$cart_item = $cart_contents[ $cart_item_key ];
	$_product  = $cart_item['data'] ?? null;

	if ( ! $_product || ! ( method_exists( $_product, 'exists' ) ? $_product->exists() : true ) ) {
		return [ 'success' => false, 'message' => __( 'This shoe is no longer available in the catalogue.', 'paxig-shoes' ) ];
	}

	if ( method_exists( $_product, 'is_purchasable' ) && ! $_product->is_purchasable() ) {
		return [ 'success' => false, 'message' => __( 'This shoe is currently not available for purchase.', 'paxig-shoes' ) ];
	}

	// 5. Sold individually check
	if ( method_exists( $_product, 'is_sold_individually' ) && $_product->is_sold_individually() && $quantity > 1 ) {
		return [
			'success' => false,
			'message' => sprintf( __( 'You can only have 1 %s in your cart.', 'woocommerce' ), $_product->get_name() ),
		];
	}

	// 6. Stock & Backorders (including shared stock across variations)
	if ( method_exists( $_product, 'is_in_stock' ) && ! $_product->is_in_stock() ) {
		return [
			'success' => false,
			'message' => sprintf( __( 'Sorry, "%s" is out of stock.', 'woocommerce' ), $_product->get_name() ),
		];
	}

	if ( method_exists( $_product, 'managing_stock' ) && $_product->managing_stock() && ! $_product->backorders_allowed() ) {
		$stock_id = method_exists( $_product, 'get_stock_managed_by_id' ) ? $_product->get_stock_managed_by_id() : $_product->get_id();
		$other_cart_qty = 0;

		foreach ( $cart_contents as $other_key => $other_item ) {
			if ( $other_key !== $cart_item_key && isset( $other_item['data'] ) ) {
				$other_product = $other_item['data'];
				$other_stock_id = method_exists( $other_product, 'get_stock_managed_by_id' ) ? $other_product->get_stock_managed_by_id() : $other_product->get_id();
				if ( $other_stock_id === $stock_id ) {
					$other_cart_qty += $other_item['quantity'];
				}
			}
		}

		$total_requested = $other_cart_qty + $quantity;
		$stock_qty = $_product->get_stock_quantity();

		if ( null !== $stock_qty && $total_requested > $stock_qty ) {
			$available_for_this = max( 0, $stock_qty - $other_cart_qty );
			return [
				'success' => false,
				'message' => sprintf( __( 'Sorry, only %d available in stock.', 'woocommerce' ), $available_for_this ),
			];
		}
	}

	// 7. Native woocommerce_update_cart_validation hook
	$passed_validation = apply_filters( 'woocommerce_update_cart_validation', true, $cart_item_key, $cart_item, $quantity );
	if ( ! $passed_validation ) {
		return [
			'success' => false,
			'message' => __( 'Item validation failed. Please check your bag.', 'paxig-shoes' ),
		];
	}

	// 8. Mutation and totals calculation
	$updated = $cart->set_quantity( $cart_item_key, $quantity, false );
	if ( ! $updated ) {
		return [ 'success' => false, 'message' => __( 'Could not update quantity.', 'paxig-shoes' ) ];
	}

	$cart->calculate_totals();

	return [
		'success'  => true,
		'action'   => 'updated',
		'quantity' => $quantity,
		'count'    => $cart->get_cart_contents_count(),
	];
}

/**
 * AJAX handler for removing an item from the mini-cart.
 */
function paxig_ajax_remove_cart_item() {
	check_ajax_referer( 'paxig_cart_nonce', 'nonce' );

	if ( ! isset( $_POST['cart_item_key'] ) ) {
		wp_send_json_error( [ 'message' => __( 'Missing cart item key.', 'paxig-shoes' ) ] );
		return;
	}

	$cart_item_key = wp_unslash( $_POST['cart_item_key'] ); // phpcs:ignore
	$result = paxig_validate_and_update_cart_quantity( $cart_item_key, 0 );

	if ( $result['success'] ) {
		$cart = WC()->cart;
		wp_send_json_success( [
			'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', [] ),
			'cart_hash' => $cart ? $cart->get_cart_hash() : '',
			'count'     => $result['count'] ?? 0,
		] );
	} else {
		wp_send_json_error( [ 'message' => $result['message'] ] );
	}
}
add_action( 'wp_ajax_paxig_remove_cart_item', 'paxig_ajax_remove_cart_item' );
add_action( 'wp_ajax_nopriv_paxig_remove_cart_item', 'paxig_ajax_remove_cart_item' );

/**
 * AJAX handler for updating item quantity from the mini-cart.
 */
function paxig_ajax_update_cart_quantity() {
	check_ajax_referer( 'paxig_cart_nonce', 'nonce' );

	if ( ! isset( $_POST['cart_item_key'] ) || ! isset( $_POST['quantity'] ) ) {
		wp_send_json_error( [ 'message' => __( 'Missing required parameters.', 'paxig-shoes' ) ] );
		return;
	}

	$cart_item_key = wp_unslash( $_POST['cart_item_key'] ); // phpcs:ignore
	$raw_quantity  = wp_unslash( $_POST['quantity'] ); // phpcs:ignore

	$result = paxig_validate_and_update_cart_quantity( $cart_item_key, $raw_quantity );

	if ( $result['success'] ) {
		$cart = WC()->cart;
		wp_send_json_success( [
			'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', [] ),
			'cart_hash' => $cart ? $cart->get_cart_hash() : '',
			'count'     => $result['count'] ?? 0,
		] );
	} else {
		wp_send_json_error( [ 'message' => $result['message'] ] );
	}
}
add_action( 'wp_ajax_paxig_update_cart_quantity', 'paxig_ajax_update_cart_quantity' );
add_action( 'wp_ajax_nopriv_paxig_update_cart_quantity', 'paxig_ajax_update_cart_quantity' );

/**
 * AJAX handler for adding a product to the cart (PDP and loop).
 */
function paxig_ajax_add_to_cart() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( [ 'message' => __( 'Cart is not initialized.', 'woocommerce' ) ] );
		return;
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	if ( ! $product_id && isset( $_POST['add-to-cart'] ) ) {
		$product_id = absint( $_POST['add-to-cart'] );
	}

	if ( ! $product_id ) {
		wp_send_json_error( [ 'message' => __( 'Invalid product.', 'woocommerce' ) ] );
		return;
	}

	$quantity = isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : 1;
	if ( $quantity <= 0 ) {
		$quantity = 1;
	}

	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		wp_send_json_error( [ 'message' => __( 'Product not found.', 'woocommerce' ) ] );
		return;
	}

	$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
	$variations   = [];

	foreach ( $_POST as $key => $value ) {
		if ( 0 === strpos( $key, 'attribute_' ) ) {
			$variations[ sanitize_text_field( $key ) ] = wp_unslash( $value );
		}
	}

	$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations );

	if ( $passed_validation ) {
		$cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations );
		if ( $cart_item_key ) {
			do_action( 'woocommerce_ajax_added_to_cart', $product_id );

			WC()->cart->calculate_totals();

			wp_send_json_success( [
				'fragments'     => apply_filters( 'woocommerce_add_to_cart_fragments', [] ),
				'cart_hash'     => WC()->cart->get_cart_hash(),
				'cart_item_key' => $cart_item_key,
			] );
			return;
		}
	}

	$notices = wc_get_notices( 'error' );
	wc_clear_notices();
	$error_msg = ! empty( $notices ) ? wp_strip_all_tags( $notices[0]['notice'] ) : __( 'Could not add product to bag.', 'woocommerce' );
	wp_send_json_error( [ 'message' => $error_msg ] );
}
add_action( 'wp_ajax_paxig_ajax_add_to_cart', 'paxig_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_paxig_ajax_add_to_cart', 'paxig_ajax_add_to_cart' );

/**
 * Enqueue mini-cart drawer scripts and styles.
 */
function paxig_enqueue_mini_cart_assets() {
	if ( is_admin() ) {
		return;
	}

	$css_path = get_template_directory() . '/assets/css/mini-cart.css';
	$js_path  = get_template_directory() . '/assets/js/mini-cart.js';

	wp_enqueue_style(
		'paxig-mini-cart',
		get_template_directory_uri() . '/assets/css/mini-cart.css',
		[ 'paxig-style-3' ],
		file_exists( $css_path ) ? filemtime( $css_path ) : '1.0.0'
	);

	wp_enqueue_script(
		'paxig-mini-cart',
		get_template_directory_uri() . '/assets/js/mini-cart.js',
		[ 'jquery', 'wc-cart-fragments' ],
		file_exists( $js_path ) ? filemtime( $js_path ) : '1.0.0',
		true
	);

	wp_localize_script( 'paxig-mini-cart', 'paxigMiniCart', [
		'ajax_url'     => admin_url( 'admin-ajax.php' ),
		'nonce'        => wp_create_nonce( 'paxig_cart_nonce' ),
		'is_cart'      => function_exists( 'is_cart' ) && is_cart(),
		'is_checkout'  => function_exists( 'is_checkout' ) && is_checkout(),
		'i18n'         => [
			'remove_error' => __( 'Could not remove item from bag.', 'paxig-shoes' ),
			'update_error' => __( 'Could not update quantity.', 'paxig-shoes' ),
			'comm_error'   => __( 'Communication error. Please check your connection and try again.', 'paxig-shoes' ),
			'add_error'    => __( 'Could not add product to bag.', 'paxig-shoes' ),
		],
	] );
}
add_action( 'wp_enqueue_scripts', 'paxig_enqueue_mini_cart_assets', 105 );
