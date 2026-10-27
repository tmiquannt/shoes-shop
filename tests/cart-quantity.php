<?php
/**
 * Isolated CLI Unit Test Suite for PAXIG Mini-Cart Quantity Validation (CART-1).
 *
 * NOTE: These are in-memory unit proof tests using isolated doubles/stubs.
 * They prove internal validation and mutation contracts without requiring
 * a live WordPress/WooCommerce runtime or database.
 */

// Define test assertions helper
function run_test( $name, callable $test_fn ) {
	global $passed, $failed;
	try {
		$test_fn();
		echo " [PASS] $name\n";
		$passed++;
	} catch ( Throwable $e ) {
		echo " [FAIL] $name: " . $e->getMessage() . "\n";
		$failed++;
	}
}

// In-memory stubs for WordPress / WooCommerce functions if not defined
if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $str ) {
		return is_scalar( $str ) ? trim( strip_tags( (string) $str ) ) : '';
	}
}
if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $val ) {
		return $val;
	}
}
if ( ! function_exists( 'add_action' ) ) {
	function add_action( ...$args ) {}
}
if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( ...$args ) {}
}

$GLOBALS['last_json_response'] = null;
if ( ! function_exists( 'check_ajax_referer' ) ) {
	function check_ajax_referer( $action = '', $query_arg = 'nonce', $die = true ) {
		global $mock_nonce_valid;
		if ( false === $mock_nonce_valid ) {
			if ( $die ) {
				throw new Exception( 'Invalid nonce' );
			}
			return false;
		}
		return true;
	}
}
if ( ! function_exists( 'wp_send_json_success' ) ) {
	function wp_send_json_success( $data = null ) {
		global $last_json_response;
		$last_json_response = [ 'success' => true, 'data' => $data ];
	}
}
if ( ! function_exists( 'wp_send_json_error' ) ) {
	function wp_send_json_error( $data = null ) {
		global $last_json_response;
		$last_json_response = [ 'success' => false, 'data' => $data ];
	}
}
if ( ! function_exists( 'absint' ) ) {
	function absint( $maybeint ) {
		return abs( intval( $maybeint ) );
	}
}
if ( ! function_exists( 'apply_filters' ) ) {
	$GLOBALS['test_filters'] = [];
	function apply_filters( $tag, $value, ...$args ) {
		global $test_filters;
		if ( isset( $test_filters[ $tag ] ) && is_callable( $test_filters[ $tag ] ) ) {
			return call_user_func_array( $test_filters[ $tag ], array_merge( [ $value ], $args ) );
		}
		return $value;
	}
}
if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}
if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

// Stubs for WC_Product and WC_Cart
class Test_WC_Product {
	public $id;
	public $name;
	public $exists = true;
	public $purchasable = true;
	public $sold_individually = false;
	public $in_stock = true;
	public $managing_stock = false;
	public $backorders_allowed = false;
	public $stock_quantity = null;
	public $stock_managed_by_id = null;

	public function __construct( $id, $name = 'Test Shoe' ) {
		$this->id = $id;
		$this->name = $name;
		$this->stock_managed_by_id = $id;
	}
	public function get_id() { return $this->id; }
	public function get_name() { return $this->name; }
	public function exists() { return $this->exists; }
	public function is_purchasable() { return $this->purchasable; }
	public function is_sold_individually() { return $this->sold_individually; }
	public function is_in_stock() { return $this->in_stock; }
	public function managing_stock() { return $this->managing_stock; }
	public function backorders_allowed() { return $this->backorders_allowed; }
	public function get_stock_quantity() { return $this->stock_quantity; }
	public function get_stock_managed_by_id() { return $this->stock_managed_by_id; }
	public function has_enough_stock( $qty ) {
		if ( ! $this->managing_stock || $this->backorders_allowed ) return true;
		return (int) $this->stock_quantity >= (int) $qty;
	}
}

class Test_WC_Cart {
	public $cart_contents = [];
	public $calculated = false;

	public function get_cart() {
		return $this->cart_contents;
	}
	public function set_quantity( $cart_item_key, $quantity = 1, $refresh = true ) {
		if ( ! isset( $this->cart_contents[ $cart_item_key ] ) ) {
			return false;
		}
		if ( 0 === $quantity ) {
			return $this->remove_cart_item( $cart_item_key );
		}
		$this->cart_contents[ $cart_item_key ]['quantity'] = $quantity;
		if ( $refresh ) $this->calculate_totals();
		return true;
	}
	public function remove_cart_item( $cart_item_key ) {
		if ( isset( $this->cart_contents[ $cart_item_key ] ) ) {
			unset( $this->cart_contents[ $cart_item_key ] );
			return true;
		}
		return false;
	}
	public function calculate_totals() {
		$this->calculated = true;
	}
	public function get_cart_hash() {
		return md5( serialize( $this->cart_contents ) );
	}
	public function get_cart_contents_count() {
		$count = 0;
		foreach ( $this->cart_contents as $item ) {
			$count += $item['quantity'];
		}
		return $count;
	}
}

class Test_WC_Cart_Fail_Set extends Test_WC_Cart {
	public function set_quantity( $cart_item_key, $quantity = 1, $refresh = true ) {
		return false;
	}
}

class Test_WC_Cart_Fail_Remove extends Test_WC_Cart {
	public function remove_cart_item( $cart_item_key ) {
		return false;
	}
}

if ( ! function_exists( 'WC' ) ) {
	$GLOBALS['mock_wc_cart'] = new Test_WC_Cart();
	function WC() {
		global $mock_wc_cart;
		return (object) [ 'cart' => $mock_wc_cart ];
	}
}

// Load target code
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/../' );
}
require_once __DIR__ . '/../includes/mini-cart.php';

// Test execution
$passed = 0;
$failed = 0;

echo "=== Running CART-1 Expanded Quantity Validation Tests ===\n\n";

// ---------------------------------------------------------
// Section 1: Infrastructure and Contract Existence Check
// ---------------------------------------------------------
echo "-- Section 1: Infrastructure & API Contract Checks --\n";

run_test( 'Core validation helper paxig_validate_and_update_cart_quantity exists', function() {
	if ( ! function_exists( 'paxig_validate_and_update_cart_quantity' ) ) {
		throw new Exception( 'Function paxig_validate_and_update_cart_quantity does not exist' );
	}
});

run_test( 'AJAX handlers paxig_ajax_update_cart_quantity and paxig_ajax_remove_cart_item exist', function() {
	if ( ! function_exists( 'paxig_ajax_update_cart_quantity' ) ) {
		throw new Exception( 'Function paxig_ajax_update_cart_quantity does not exist' );
	}
	if ( ! function_exists( 'paxig_ajax_remove_cart_item' ) ) {
		throw new Exception( 'Function paxig_ajax_remove_cart_item does not exist' );
	}
});

// ---------------------------------------------------------
// Section 2: Input Format and Malformed Key Tests
// ---------------------------------------------------------
echo "\n-- Section 2: Input Format & Malformed Key Tests --\n";

run_test( 'Valid positive quantity updates cart item and recalculates totals', function() {
	$cart = new Test_WC_Cart();
	$product = new Test_WC_Product( 101, 'Runner One' );
	$cart->cart_contents['key_abc'] = [ 'data' => $product, 'quantity' => 2 ];

	$result = paxig_validate_and_update_cart_quantity( 'key_abc', '4', $cart );
	if ( ! $result['success'] ) throw new Exception( "Expected success, got: " . ( $result['message'] ?? '' ) );
	if ( $cart->cart_contents['key_abc']['quantity'] !== 4 ) throw new Exception( "Quantity not updated to 4" );
	if ( ! $cart->calculated ) throw new Exception( "Totals were not calculated" );
});

run_test( 'Strict 0 quantity removes existing item', function() {
	$cart = new Test_WC_Cart();
	$product = new Test_WC_Product( 101, 'Runner One' );
	$cart->cart_contents['key_abc'] = [ 'data' => $product, 'quantity' => 2 ];

	$result = paxig_validate_and_update_cart_quantity( 'key_abc', '0', $cart );
	if ( ! $result['success'] ) throw new Exception( "Expected success, got: " . ( $result['message'] ?? '' ) );
	if ( isset( $cart->cart_contents['key_abc'] ) ) throw new Exception( "Item was not removed on 0 quantity" );
});

run_test( 'Stale or missing cart item key is rejected without mutation', function() {
	$cart = new Test_WC_Cart();
	$product = new Test_WC_Product( 101, 'Runner One' );
	$cart->cart_contents['key_real'] = [ 'data' => $product, 'quantity' => 1 ];

	$result = paxig_validate_and_update_cart_quantity( 'key_stale', '3', $cart );
	if ( $result['success'] ) throw new Exception( "Expected failure on stale key" );
	if ( $cart->cart_contents['key_real']['quantity'] !== 1 ) throw new Exception( "Existing cart was mutated" );
});

run_test( 'Malformed cart item keys (empty, whitespace, array, non-string) are rejected', function() {
	$cart = new Test_WC_Cart();
	$product = new Test_WC_Product( 101, 'Runner One' );
	$cart->cart_contents['key_real'] = [ 'data' => $product, 'quantity' => 1 ];

	foreach ( [ '', '   ', [ 'key' ], 123, null ] as $bad_key ) {
		$result = paxig_validate_and_update_cart_quantity( $bad_key, '2', $cart );
		if ( $result['success'] ) throw new Exception( "Expected failure for malformed key: " . var_export( $bad_key, true ) );
	}
	if ( $cart->cart_contents['key_real']['quantity'] !== 1 ) throw new Exception( "Existing cart was mutated" );
});

run_test( 'Negative quantity is rejected without absint mutation', function() {
	$cart = new Test_WC_Cart();
	$product = new Test_WC_Product( 101, 'Runner One' );
	$cart->cart_contents['key_abc'] = [ 'data' => $product, 'quantity' => 2 ];

	$result = paxig_validate_and_update_cart_quantity( 'key_abc', '-1', $cart );
	if ( $result['success'] ) throw new Exception( "Expected failure on negative input" );
	if ( $cart->cart_contents['key_abc']['quantity'] !== 2 ) throw new Exception( "Cart was mutated on negative input" );
});

run_test( 'Fraction / float quantity is rejected without mutation', function() {
	$cart = new Test_WC_Cart();
	$product = new Test_WC_Product( 101, 'Runner One' );
	$cart->cart_contents['key_abc'] = [ 'data' => $product, 'quantity' => 2 ];

	$result = paxig_validate_and_update_cart_quantity( 'key_abc', '2.5', $cart );
	if ( $result['success'] ) throw new Exception( "Expected failure on fraction input" );
	if ( $cart->cart_contents['key_abc']['quantity'] !== 2 ) throw new Exception( "Cart was mutated on fraction input" );
});

run_test( 'Array quantity input is rejected without mutation', function() {
	$cart = new Test_WC_Cart();
	$product = new Test_WC_Product( 101, 'Runner One' );
	$cart->cart_contents['key_abc'] = [ 'data' => $product, 'quantity' => 2 ];

	$result = paxig_validate_and_update_cart_quantity( 'key_abc', [ 5 ], $cart );
	if ( $result['success'] ) throw new Exception( "Expected failure on array input" );
	if ( $cart->cart_contents['key_abc']['quantity'] !== 2 ) throw new Exception( "Cart was mutated on array input" );
});

run_test( 'Non-numeric string quantity is rejected', function() {
	$cart = new Test_WC_Cart();
	$product = new Test_WC_Product( 101, 'Runner One' );
	$cart->cart_contents['key_abc'] = [ 'data' => $product, 'quantity' => 2 ];

	$result = paxig_validate_and_update_cart_quantity( 'key_abc', 'five', $cart );
	if ( $result['success'] ) throw new Exception( "Expected failure on string input" );
	if ( $cart->cart_contents['key_abc']['quantity'] !== 2 ) throw new Exception( "Cart was mutated on string input" );
});

run_test( 'Excessive / huge quantity input is rejected', function() {
	$cart = new Test_WC_Cart();
	$product = new Test_WC_Product( 101, 'Runner One' );
	$cart->cart_contents['key_abc'] = [ 'data' => $product, 'quantity' => 2 ];

	$result = paxig_validate_and_update_cart_quantity( 'key_abc', '9999999', $cart );
	if ( $result['success'] ) throw new Exception( "Expected failure on excessive quantity" );
});

// ---------------------------------------------------------
// Section 3: Product Integrity & Stock Tests
// ---------------------------------------------------------
echo "\n-- Section 3: Product Integrity & Inventory Tests --\n";

run_test( 'Missing product in cart item data is rejected', function() {
	$cart = new Test_WC_Cart();
	$cart->cart_contents['key_missing'] = [ 'data' => null, 'quantity' => 1 ];

	$result = paxig_validate_and_update_cart_quantity( 'key_missing', '2', $cart );
	if ( $result['success'] ) throw new Exception( "Expected failure when product data is null" );

	$dead_product = new Test_WC_Product( 999 );
	$dead_product->exists = false;
	$cart->cart_contents['key_dead'] = [ 'data' => $dead_product, 'quantity' => 1 ];

	$result2 = paxig_validate_and_update_cart_quantity( 'key_dead', '2', $cart );
	if ( $result2['success'] ) throw new Exception( "Expected failure when product does not exist" );
});

run_test( 'Unpurchasable product is rejected', function() {
	$cart = new Test_WC_Cart();
	$product = new Test_WC_Product( 101, 'Discontinued Shoe' );
	$product->purchasable = false;
	$cart->cart_contents['key_abc'] = [ 'data' => $product, 'quantity' => 1 ];

	$result = paxig_validate_and_update_cart_quantity( 'key_abc', '2', $cart );
	if ( $result['success'] ) throw new Exception( "Expected failure when product is unpurchasable" );
	if ( $cart->cart_contents['key_abc']['quantity'] !== 1 ) throw new Exception( "Cart was mutated" );
});

run_test( 'Out of stock product is rejected', function() {
	$cart = new Test_WC_Cart();
	$product = new Test_WC_Product( 101, 'Out of Stock Shoe' );
	$product->in_stock = false;
	$cart->cart_contents['key_abc'] = [ 'data' => $product, 'quantity' => 1 ];

	$result = paxig_validate_and_update_cart_quantity( 'key_abc', '2', $cart );
	if ( $result['success'] ) throw new Exception( "Expected failure when product is out of stock" );
	if ( $cart->cart_contents['key_abc']['quantity'] !== 1 ) throw new Exception( "Cart was mutated" );
});

run_test( 'Sold individually product cannot have quantity > 1', function() {
	$cart = new Test_WC_Cart();
	$product = new Test_WC_Product( 101, 'Limited Edition One' );
	$product->sold_individually = true;
	$cart->cart_contents['key_abc'] = [ 'data' => $product, 'quantity' => 1 ];

	$result = paxig_validate_and_update_cart_quantity( 'key_abc', '2', $cart );
	if ( $result['success'] ) throw new Exception( "Expected failure on sold individually > 1" );
	if ( $cart->cart_contents['key_abc']['quantity'] !== 1 ) throw new Exception( "Cart was mutated on sold individually violation" );
});

run_test( 'Stock limit prevents quantity exceeding available inventory when backorders disallowed', function() {
	$cart = new Test_WC_Cart();
	$product = new Test_WC_Product( 101, 'In Stock Shoe' );
	$product->managing_stock = true;
	$product->stock_quantity = 3;
	$product->backorders_allowed = false;
	$cart->cart_contents['key_abc'] = [ 'data' => $product, 'quantity' => 1 ];

	$result = paxig_validate_and_update_cart_quantity( 'key_abc', '4', $cart );
	if ( $result['success'] ) throw new Exception( "Expected failure when quantity exceeds stock" );
	if ( $cart->cart_contents['key_abc']['quantity'] !== 1 ) throw new Exception( "Cart was mutated when stock exceeded" );
});

run_test( 'Shared stock across multiple cart items of same managed stock ID is enforced', function() {
	$cart = new Test_WC_Cart();
	$product_a = new Test_WC_Product( 101, 'Shoe Red' );
	$product_a->managing_stock = true;
	$product_a->stock_quantity = 5;
	$product_a->stock_managed_by_id = 500;

	$product_b = new Test_WC_Product( 102, 'Shoe Blue' );
	$product_b->managing_stock = true;
	$product_b->stock_quantity = 5;
	$product_b->stock_managed_by_id = 500;

	$cart->cart_contents['item_a'] = [ 'data' => $product_a, 'quantity' => 3 ];
	$cart->cart_contents['item_b'] = [ 'data' => $product_b, 'quantity' => 1 ];

	$result = paxig_validate_and_update_cart_quantity( 'item_b', '3', $cart );
	if ( $result['success'] ) throw new Exception( "Expected failure on shared aggregate stock violation" );
	if ( $cart->cart_contents['item_b']['quantity'] !== 1 ) throw new Exception( "Cart was mutated on shared stock violation" );
});

run_test( 'Backorders allowed permits updating quantity beyond current stock', function() {
	$cart = new Test_WC_Cart();
	$product = new Test_WC_Product( 101, 'Backorder Shoe' );
	$product->managing_stock = true;
	$product->stock_quantity = 2;
	$product->backorders_allowed = true;
	$cart->cart_contents['key_abc'] = [ 'data' => $product, 'quantity' => 1 ];

	$result = paxig_validate_and_update_cart_quantity( 'key_abc', '5', $cart );
	if ( ! $result['success'] ) throw new Exception( "Expected success when backorders allowed, got: " . ( $result['message'] ?? '' ) );
	if ( $cart->cart_contents['key_abc']['quantity'] !== 5 ) throw new Exception( "Quantity was not updated for backorder item" );
});

run_test( 'Failed cart set_quantity or remove_cart_item reports error cleanly', function() {
	$cart_fail_set = new Test_WC_Cart_Fail_Set();
	$product = new Test_WC_Product( 101, 'Sample Shoe' );
	$cart_fail_set->cart_contents['key_abc'] = [ 'data' => $product, 'quantity' => 1 ];

	$result1 = paxig_validate_and_update_cart_quantity( 'key_abc', '2', $cart_fail_set );
	if ( $result1['success'] ) throw new Exception( "Expected failure when set_quantity returns false" );

	$cart_fail_remove = new Test_WC_Cart_Fail_Remove();
	$cart_fail_remove->cart_contents['key_abc'] = [ 'data' => $product, 'quantity' => 1 ];

	$result2 = paxig_validate_and_update_cart_quantity( 'key_abc', '0', $cart_fail_remove );
	if ( $result2['success'] ) throw new Exception( "Expected failure when remove_cart_item returns false" );
});

run_test( 'woocommerce_update_cart_validation veto blocks update without cart mutation', function() {
	global $test_filters;
	$cart = new Test_WC_Cart();
	$product = new Test_WC_Product( 101, 'Veto Shoe' );
	$cart->cart_contents['key_abc'] = [ 'data' => $product, 'quantity' => 2 ];

	$test_filters['woocommerce_update_cart_validation'] = function( $passed, $key, $item, $qty ) {
		return false; // Veto
	};

	$result = paxig_validate_and_update_cart_quantity( 'key_abc', '3', $cart );
	unset( $test_filters['woocommerce_update_cart_validation'] );

	if ( $result['success'] ) throw new Exception( "Expected failure when validation filter vetoes" );
	if ( $cart->cart_contents['key_abc']['quantity'] !== 2 ) throw new Exception( "Cart was mutated despite validation filter veto" );
});

// ---------------------------------------------------------
// Section 4: AJAX Handlers & Nonce Wiring Simulation
// ---------------------------------------------------------
echo "\n-- Section 4: AJAX Handler & Nonce Wiring Simulation --\n";

run_test( 'AJAX update handler rejects missing POST parameters with JSON error', function() {
	global $last_json_response, $mock_nonce_valid;
	$mock_nonce_valid = true;
	$last_json_response = null;
	$_POST = []; // Empty

	paxig_ajax_update_cart_quantity();
	if ( ! $last_json_response || $last_json_response['success'] ) {
		throw new Exception( "Expected JSON error for missing parameters" );
	}
});

run_test( 'AJAX remove handler rejects missing cart_item_key with JSON error', function() {
	global $last_json_response, $mock_nonce_valid;
	$mock_nonce_valid = true;
	$last_json_response = null;
	$_POST = [];

	paxig_ajax_remove_cart_item();
	if ( ! $last_json_response || $last_json_response['success'] ) {
		throw new Exception( "Expected JSON error for missing cart_item_key" );
	}
});

run_test( 'AJAX update handler with valid inputs executes and returns success fragments', function() {
	global $last_json_response, $mock_wc_cart, $mock_nonce_valid;
	$mock_nonce_valid = true;
	$product = new Test_WC_Product( 201, 'Air Max Pulse' );
	$mock_wc_cart->cart_contents['item_xyz'] = [ 'data' => $product, 'quantity' => 1 ];

	$_POST = [
		'nonce'         => 'valid_token',
		'cart_item_key' => 'item_xyz',
		'quantity'      => '3',
	];

	paxig_ajax_update_cart_quantity();
	if ( ! $last_json_response || ! $last_json_response['success'] ) {
		throw new Exception( "Expected JSON success for valid update" );
	}
	if ( $mock_wc_cart->cart_contents['item_xyz']['quantity'] !== 3 ) {
		throw new Exception( "Cart quantity was not updated to 3" );
	}
});

echo "\n======================================================\n";
echo "Total Tests Run: " . ( $passed + $failed ) . " | Passed: $passed | Failed: $failed\n";
echo "======================================================\n";

if ( $failed > 0 ) {
	exit( 1 );
}
exit( 0 );
