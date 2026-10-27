<?php
/**
 * Isolated Unit Test Suite for WooCommerce Checkout Registration & Guest Alignment (CHK-2).
 *
 * NOTE: These are in-memory unit proof tests using isolated test doubles and stubs.
 * They prove filter registrations, option overrides, and posted data sanitization
 * against the real WooCommerce WC_Checkout contracts (inspected in class-wc-checkout.php)
 * without requiring WordPress database bootstrap or live HTTP runtime.
 */

$passed = 0;
$failed = 0;

function run_test( $name, callable $fn ) {
	global $passed, $failed;
	try {
		$fn();
		echo " [PASS] $name\n";
		$passed++;
	} catch ( Throwable $e ) {
		echo " [FAIL] $name: " . $e->getMessage() . "\n";
		$failed++;
	}
}

// -------------------------------------------------------------
// In-Memory WordPress & WooCommerce Filter / Hook Infrastructure
// -------------------------------------------------------------
$GLOBALS['wp_filters'] = [];
$GLOBALS['wp_actions'] = [];
$GLOBALS['wp_options'] = [];

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
		global $wp_filters;
		if ( ! isset( $wp_filters[ $tag ] ) ) {
			$wp_filters[ $tag ] = [];
		}
		$wp_filters[ $tag ][] = [
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		];
		// Sort by priority
		usort( $wp_filters[ $tag ], function( $a, $b ) {
			return $a['priority'] <=> $b['priority'];
		});
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $tag, $value, ...$args ) {
		global $wp_filters;
		if ( empty( $wp_filters[ $tag ] ) ) {
			return $value;
		}
		foreach ( $wp_filters[ $tag ] as $filter ) {
			$params = array_merge( [ $value ], array_slice( $args, 0, $filter['accepted_args'] - 1 ) );
			$value  = call_user_func_array( $filter['callback'], $params );
		}
		return $value;
	}
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
		add_filter( $tag, $callback, $priority, $accepted_args );
	}
}

if ( ! function_exists( 'do_action' ) ) {
	function do_action( $tag, ...$args ) {
		global $wp_filters;
		if ( empty( $wp_filters[ $tag ] ) ) {
			return;
		}
		foreach ( $wp_filters[ $tag ] as $filter ) {
			$params = array_slice( $args, 0, $filter['accepted_args'] );
			call_user_func_array( $filter['callback'], $params );
		}
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $option, $default = false ) {
		global $wp_options;
		$val = isset( $wp_options[ $option ] ) ? $wp_options[ $option ] : $default;
		return apply_filters( 'option_' . $option, $val );
	}
}

if ( ! function_exists( '__return_false' ) ) {
	function __return_false() { return false; }
}
if ( ! function_exists( '__return_true' ) ) {
	function __return_true() { return true; }
}
if ( ! function_exists( 'is_user_logged_in' ) ) {
	$GLOBALS['mock_user_logged_in'] = false;
	function is_user_logged_in() {
		global $mock_user_logged_in;
		return (bool) $mock_user_logged_in;
	}
}
if ( ! function_exists( 'get_current_user_id' ) ) {
	$GLOBALS['mock_user_id'] = 0;
	function get_current_user_id() {
		global $mock_user_id;
		return (int) $mock_user_id;
	}
}
if ( ! function_exists( 'wc_get_checkout_url' ) ) {
	function wc_get_checkout_url() { return 'https://example.com/checkout/'; }
}
if ( ! function_exists( 'wc_get_page_permalink' ) ) {
	function wc_get_page_permalink( $page ) { return 'https://example.com/' . $page . '/'; }
}
if ( ! function_exists( 'untrailingslashit' ) ) {
	function untrailingslashit( $url ) { return rtrim( (string) $url, '/' ); }
}
if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $val ) { return $val; }
}
if ( ! function_exists( 'add_query_arg' ) ) {
	function add_query_arg( $args, $url ) {
		return $url . '?' . http_build_query( $args );
	}
}
if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url ) { return $url; }
}
if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $val ) { return htmlspecialchars( (string) $val, ENT_QUOTES ); }
}
if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $val ) { return htmlspecialchars( (string) $val, ENT_QUOTES ); }
}

// -------------------------------------------------------------
// Real WooCommerce WC_Checkout Contract Double
// (Modeled strictly on woocommerce/includes/class-wc-checkout.php)
// -------------------------------------------------------------
class Test_WC_Checkout_Double {
	public function is_registration_required() {
		// Matches class-wc-checkout.php line 207
		return apply_filters(
			'woocommerce_checkout_registration_required',
			'yes' !== get_option( 'woocommerce_enable_guest_checkout' )
		);
	}

	public function is_registration_enabled() {
		// Matches class-wc-checkout.php line 224
		return apply_filters(
			'woocommerce_checkout_registration_enabled',
			'yes' === get_option( 'woocommerce_enable_signup_and_login_from_checkout' )
		);
	}

	public function get_posted_data() {
		// Matches class-wc-checkout.php line 764
		$data = [
			'terms'         => (int) isset( $_POST['terms'] ),
			'createaccount' => (int) ( $this->is_registration_enabled() ? ! empty( $_POST['createaccount'] ) : false ),
			'billing_email' => $_POST['billing_email'] ?? '',
		];

		// Matches class-wc-checkout.php line 827
		return apply_filters( 'woocommerce_checkout_posted_data', $data );
	}

	public function maybe_skip_fieldset( $fieldset_key, $data ) {
		// Matches class-wc-checkout.php line 746
		if ( 'account' === $fieldset_key && ( is_user_logged_in() || ( ! $this->is_registration_required() && empty( $data['createaccount'] ) ) ) ) {
			return true;
		}
		return false;
	}

	public function process_customer_simulation( $data ) {
		// Matches class-wc-checkout.php line 1163
		// Checks whether account creation would be triggered
		if ( ! is_user_logged_in() && ( $this->is_registration_required() || ! empty( $data['createaccount'] ) ) ) {
			return 'ACCOUNT_CREATED';
		}
		return 'GUEST_PURCHASE';
	}
}

// -------------------------------------------------------------
// Load Target File
// -------------------------------------------------------------
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../' );
}
require_once __DIR__ . '/../includes/checkout-alignment.php';

echo "=== Running CHK-2 Checkout Registration & Guest Tests ===\n\n";

// -------------------------------------------------------------
// Test 1: Checkout registration is disabled despite 'yes' option
// -------------------------------------------------------------
run_test( 'Checkout registration is disabled via filter despite woocommerce_enable_signup_and_login_from_checkout = yes', function() {
	global $wp_options;
	$wp_options['woocommerce_enable_signup_and_login_from_checkout'] = 'yes';

	$checkout = new Test_WC_Checkout_Double();
	$enabled = $checkout->is_registration_enabled();

	if ( true === $enabled ) {
		throw new Exception( 'is_registration_enabled() returned true despite CHK-2 mandate to disable checkout registration' );
	}
});

// -------------------------------------------------------------
// Test 2: Guest checkout is enabled despite 'no' guest option
// -------------------------------------------------------------
run_test( 'Registration is NOT required via filter despite woocommerce_enable_guest_checkout = no', function() {
	global $wp_options;
	$wp_options['woocommerce_enable_guest_checkout'] = 'no';

	$checkout = new Test_WC_Checkout_Double();
	$required = $checkout->is_registration_required();

	if ( true === $required ) {
		throw new Exception( 'is_registration_required() returned true despite CHK-2 mandate for guest-capable checkout' );
	}
});

// -------------------------------------------------------------
// Test 3: Forged createaccount in $_POST is stripped to 0 in get_posted_data
// -------------------------------------------------------------
run_test( 'Forged $_POST[createaccount] evaluates to 0 in get_posted_data() due to registration disabled', function() {
	global $wp_options;
	$wp_options['woocommerce_enable_signup_and_login_from_checkout'] = 'yes';
	$_POST['createaccount'] = '1';

	$checkout = new Test_WC_Checkout_Double();
	$posted = $checkout->get_posted_data();

	if ( 0 !== $posted['createaccount'] ) {
		throw new Exception( 'get_posted_data() returned createaccount = ' . var_export( $posted['createaccount'], true ) );
	}
});

// -------------------------------------------------------------
// Test 4: Forged createaccount is stripped via woocommerce_checkout_posted_data filter defense-in-depth
// -------------------------------------------------------------
run_test( 'woocommerce_checkout_posted_data sanitizes createaccount and unsets account credentials', function() {
	$dirty_posted = [
		'createaccount'    => 1,
		'account_password' => 'secret123',
		'account_username' => 'forged_user',
		'billing_email'    => 'shopper@example.com',
	];

	$cleaned = apply_filters( 'woocommerce_checkout_posted_data', $dirty_posted );

	if ( ! empty( $cleaned['createaccount'] ) ) {
		throw new Exception( 'createaccount was not stripped to 0 in woocommerce_checkout_posted_data filter' );
	}
	if ( isset( $cleaned['account_password'] ) ) {
		throw new Exception( 'account_password was not stripped in woocommerce_checkout_posted_data filter' );
	}
	if ( isset( $cleaned['account_username'] ) ) {
		throw new Exception( 'account_username was not stripped in woocommerce_checkout_posted_data filter' );
	}
	if ( ( $cleaned['billing_email'] ?? '' ) !== 'shopper@example.com' ) {
		throw new Exception( 'billing_email was unintentionally corrupted' );
	}
});

// -------------------------------------------------------------
// Test 5: maybe_skip_fieldset skips account fieldset during checkout
// -------------------------------------------------------------
run_test( 'maybe_skip_fieldset skips account fieldset for guest checkout', function() {
	global $wp_options, $mock_user_logged_in;
	$mock_user_logged_in = false;
	$wp_options['woocommerce_enable_signup_and_login_from_checkout'] = 'yes';
	$wp_options['woocommerce_enable_guest_checkout'] = 'no';
	$_POST['createaccount'] = '1';

	$checkout = new Test_WC_Checkout_Double();
	$posted = $checkout->get_posted_data();
	$skipped = $checkout->maybe_skip_fieldset( 'account', $posted );

	if ( true !== $skipped ) {
		throw new Exception( 'maybe_skip_fieldset did not skip account fieldset' );
	}
});

// -------------------------------------------------------------
// Test 6: process_customer does NOT create account during guest checkout
// -------------------------------------------------------------
run_test( 'process_customer simulation completes as GUEST_PURCHASE without creating customer', function() {
	global $wp_options, $mock_user_logged_in;
	$mock_user_logged_in = false;
	$wp_options['woocommerce_enable_signup_and_login_from_checkout'] = 'yes';
	$wp_options['woocommerce_enable_guest_checkout'] = 'no';
	$_POST['createaccount'] = '1';

	$checkout = new Test_WC_Checkout_Double();
	$posted = $checkout->get_posted_data();
	$action = $checkout->process_customer_simulation( $posted );

	if ( 'GUEST_PURCHASE' !== $action ) {
		throw new Exception( 'process_customer simulation triggered ' . $action . ' instead of GUEST_PURCHASE' );
	}
});

// -------------------------------------------------------------
// Test 7: My Account registration option is untouched and independent
// -------------------------------------------------------------
run_test( 'My Account registration option woocommerce_enable_myaccount_registration is untouched', function() {
	global $wp_options;
	$wp_options['woocommerce_enable_myaccount_registration'] = 'yes';

	$myaccount_reg = get_option( 'woocommerce_enable_myaccount_registration' );
	if ( 'yes' !== $myaccount_reg ) {
		throw new Exception( 'woocommerce_enable_myaccount_registration was improperly altered' );
	}
});

// -------------------------------------------------------------
// Test 8: Storefront authenticated orders still associate customer_id
// -------------------------------------------------------------
run_test( 'Storefront authenticated order retains customer_id when logged in', function() {
	global $mock_user_logged_in, $mock_user_id;
	$mock_user_logged_in = true;
	$mock_user_id = 42;

	$order_double = new class {
		public $customer_id = 0;
		public function set_customer_id( $id ) { $this->customer_id = $id; }
	};

	do_action( 'woocommerce_checkout_create_order', $order_double );

	if ( 42 !== $order_double->customer_id ) {
		throw new Exception( 'Authenticated order customer_id was not set to current user id' );
	}
});

echo "\n======================================================\n";
echo "Total Tests Run: " . ( $passed + $failed ) . " | Passed: $passed | Failed: $failed\n";
echo "======================================================\n";

if ( $failed > 0 ) {
	exit( 1 );
}
exit( 0 );
