<?php
/**
 * Internationalization (i18n) & Bicultural Support (EN / VI) for PAXG'Shop.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load theme textdomain for gettext translations.
 */
function paxig_shoes_load_textdomain() {
	load_theme_textdomain( 'paxig-shoes', get_template_directory() . '/languages' );
	$lang = paxig_get_current_language();
	if ( 'vi' === $lang ) {
		load_default_textdomain( 'vi' );
		if ( defined( 'WP_LANG_DIR' ) ) {
			load_plugin_textdomain( 'woocommerce', false, 'plugins' );
		}
	}
}
add_action( 'after_setup_theme', 'paxig_shoes_load_textdomain' );

/**
 * Register client-side i18n locale data fallback for WooCommerce Blocks.
 */
function paxig_shoes_enqueue_block_i18n_fallback() {
	$is_vi = function_exists( 'paxig_get_current_language' ) && paxig_get_current_language() === 'vi';
	if ( ! $is_vi ) {
		return;
	}

	$vi_wc_strings = array(
		''                              => array( 'domain' => 'woocommerce', 'lang' => 'vi' ),
		'Proceed to Checkout'           => array( 'Tiến hành thanh toán' ),
		'Order summary'                 => array( 'Tóm tắt đơn hàng' ),
		'Subtotal'                      => array( 'Tạm tính' ),
		'Shipping'                      => array( 'Vận chuyển' ),
		'Total'                         => array( 'Tổng cộng' ),
		'Taxes'                         => array( 'Thuế' ),
		'Discount'                      => array( 'Giảm giá' ),
		'Fee'                           => array( 'Phụ phí' ),
		'Coupon code'                   => array( 'Mã ưu đãi' ),
		'Apply'                         => array( 'Áp dụng' ),
		'Remove item'                   => array( 'Xóa sản phẩm' ),
		'Reduce quantity'               => array( 'Giảm số lượng' ),
		'Increase quantity'             => array( 'Tăng số lượng' ),
		'Contact information'           => array( 'Thông tin liên hệ' ),
		'Shipping address'              => array( 'Địa chỉ nhận hàng' ),
		'Billing address'               => array( 'Địa chỉ thanh toán' ),
		'Shipping methods'              => array( 'Phương thức vận chuyển' ),
		'Payment options'               => array( 'Tùy chọn thanh toán' ),
		'Place Order'                   => array( 'Đặt hàng' ),
		'Your cart is currently empty!' => array( 'Giỏ hàng của bạn đang trống!' ),
		'Browse store'                  => array( 'Khám phá cửa hàng' ),
		'Return to Cart'                => array( 'Quay lại giỏ hàng' ),
		'Order notes'                   => array( 'Ghi chú đơn hàng' ),
		'Add a note to your order'      => array( 'Thêm ghi chú cho đơn hàng' ),
	);

	$script = 'if ( window.wp && window.wp.i18n ) { window.wp.i18n.setLocaleData( ' . wp_json_encode( $vi_wc_strings ) . ', "woocommerce" ); }';
	wp_add_inline_script( 'wp-i18n', $script, 'after' );
}
add_action( 'wp_enqueue_scripts', 'paxig_shoes_enqueue_block_i18n_fallback', 50 );

/**
 * Detect current active language ('vi' or 'en').
 *
 * Precedence:
 * 1. Query parameter ?lang=vi or ?lang=en
 * 2. Cookie 'paxig_lang'
 * 3. Default: 'en'
 *
 * @return string 'vi' or 'en'
 */
function paxig_get_current_language() {
	if ( isset( $_GET['lang'] ) && is_string( $_GET['lang'] ) ) {
		$candidate = strtolower( trim( $_GET['lang'] ) );
		if ( in_array( $candidate, [ 'vi', 'en' ], true ) ) {
			return $candidate;
		}
	}

	if ( isset( $_COOKIE['paxig_lang'] ) && is_string( $_COOKIE['paxig_lang'] ) ) {
		$candidate = strtolower( trim( $_COOKIE['paxig_lang'] ) );
		if ( in_array( $candidate, [ 'vi', 'en' ], true ) ) {
			return $candidate;
		}
	}

	return 'en';
}

/**
 * Persist language selection in cookie when switching.
 */
function paxig_persist_language_cookie() {
	if ( isset( $_GET['lang'] ) && is_string( $_GET['lang'] ) ) {
		$candidate = strtolower( trim( $_GET['lang'] ) );
		if ( in_array( $candidate, [ 'vi', 'en' ], true ) ) {
			if ( ! headers_sent() ) {
				$path = defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/';
				$domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';
				setcookie( 'paxig_lang', $candidate, time() + ( 86400 * 365 ), $path, $domain, is_ssl(), false );
			}
			$_COOKIE['paxig_lang'] = $candidate;
		}
	}
}
add_action( 'init', 'paxig_persist_language_cookie', 1 );

/**
 * Filter WordPress locale to match active language.
 */
function paxig_filter_locale( $locale ) {
	if ( is_admin() ) {
		return $locale;
	}

	$lang = paxig_get_current_language();
	return 'vi' === $lang ? 'vi' : 'en_US';
}
add_filter( 'locale', 'paxig_filter_locale', 20 );
add_filter( 'determine_locale', 'paxig_filter_locale', 20 );

/**
 * Filter HTML language attribute.
 */
function paxig_filter_language_attributes( $attributes ) {
	if ( is_admin() ) {
		return $attributes;
	}

	$lang = paxig_get_current_language();
	return 'vi' === $lang ? 'lang="vi"' : 'lang="en-US"';
}
add_filter( 'language_attributes', 'paxig_filter_language_attributes', 20 );

/**
 * Render sleek language switcher dropdown selector.
 *
 * @param string $class Additional CSS classes.
 */
function paxig_render_language_switcher( $class = '' ) {
	static $switcher_index = 0;
	$switcher_index++;
	$select_id = 'paxig-lang-select-' . $switcher_index;

	$current_lang = paxig_get_current_language();

	// Preserve current URL parameters while updating lang
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : home_url( '/' );
	$vi_url      = add_query_arg( 'lang', 'vi', $request_uri );
	$en_url      = add_query_arg( 'lang', 'en', $request_uri );
	?>
	<div class="paxig-lang-dropdown paxig-lang-switch <?php echo esc_attr( $class ); ?>">
		<label for="<?php echo esc_attr( $select_id ); ?>" class="screen-reader-text">
			<?php echo esc_html__( 'Language Switcher', 'paxig-shoes' ); ?>
		</label>
		<div class="paxig-lang-control">
			<span class="paxig-lang-icon" aria-hidden="true">
				<?php echo paxig_icon('globe', 14); ?>
			</span>
			<select
				id="<?php echo esc_attr( $select_id ); ?>"
				class="paxig-lang-select"
				aria-label="<?php echo esc_attr__( 'Language Switcher', 'paxig-shoes' ); ?>"
				onchange="if (this.value) window.location.href = this.value;"
			>
				<option value="<?php echo esc_url( $vi_url ); ?>" <?php selected( $current_lang, 'vi' ); ?>>
					Tiếng Việt (VI)
				</option>
				<option value="<?php echo esc_url( $en_url ); ?>" <?php selected( $current_lang, 'en' ); ?>>
					English (EN)
				</option>
			</select>
			<span class="paxig-lang-chevron" aria-hidden="true">
				<?php echo paxig_icon('chevron-down', 10); ?>
			</span>
		</div>
	</div>
	<?php
}
