<?php
/**
 * Isolated wishlist endpoint tests. No WordPress bootstrap, database, or network.
 */

define( 'ABSPATH', __DIR__ . '/' );

final class Wishlist_Test_Response extends RuntimeException {
	public $success;
	public $data;

	public function __construct( $success, $data ) {
		$this->success = $success;
		$this->data    = $data;
	}
}

final class Wishlist_Test_Product {
	private $id;

	public function __construct( $id ) {
		$this->id = $id;
	}

	public function get_id() { return $this->id; }
	public function is_visible() { return true; }
	public function get_name() { return '<img src=x onerror=alert(1)> Shoe'; }
	public function get_price_html() { return '<span>9.000&nbsp;&#8363;</span>'; }
	public function get_permalink() { return 'https://shop.example/product/' . $this->id . '/'; }
	public function get_image_id() { return 0; }
	public function is_in_stock() { return true; }
}

$GLOBALS['wishlist_test_user_id'] = 1;
$GLOBALS['wishlist_test_logged_in'] = true;
$GLOBALS['wishlist_test_user_meta'] = array(
	1 => array( 'paxig_wishlist' => array( 11, 22 ) ),
	2 => array( 'paxig_wishlist' => array( 33 ) ),
);
$GLOBALS['wishlist_test_meta_writes'] = 0;
$GLOBALS['wishlist_test_meta_update_fails'] = false;
$GLOBALS['wishlist_test_products_fetched'] = 0;

function add_action( ...$args ) {}
function absint( $value ) { return abs( (int) $value ); }
function __( $text, $domain = null ) { return $text; }
function check_ajax_referer( $action, $field ) {
	if ( ! isset( $_POST[ $field ] ) || 'test-nonce' !== $_POST[ $field ] ) {
		throw new RuntimeException( 'Unexpected nonce failure.' );
	}
}
function is_user_logged_in() { return $GLOBALS['wishlist_test_logged_in']; }
function get_current_user_id() { return $GLOBALS['wishlist_test_user_id']; }
function get_user_meta( $user_id, $key, $single = false ) {
	return $GLOBALS['wishlist_test_user_meta'][ $user_id ][ $key ] ?? '';
}
function update_user_meta( $user_id, $key, $value ) {
	$GLOBALS['wishlist_test_meta_writes']++;
	if ( $GLOBALS['wishlist_test_meta_update_fails'] ) {
		return false;
	}
	$GLOBALS['wishlist_test_user_meta'][ $user_id ][ $key ] = $value;
	return true;
}
function wc_get_product( $id ) {
	$GLOBALS['wishlist_test_products_fetched']++;
	return new Wishlist_Test_Product( $id );
}
function wp_get_attachment_image_url( $id, $size ) { return ''; }
function wp_strip_all_tags( $value ) { return strip_tags( $value ); }
function wp_send_json_success( $data = null ) { throw new Wishlist_Test_Response( true, $data ); }
function wp_send_json_error( $data = null, $status_code = null ) { throw new Wishlist_Test_Response( false, $data ); }

require __DIR__ . '/../includes/wishlist.php';

function wishlist_test_assert( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

function wishlist_test_call( $callback, $post ) {
	$_POST = array_merge( array( 'nonce' => 'test-nonce' ), $post );
	try {
		$callback();
	} catch ( Wishlist_Test_Response $response ) {
		return $response;
	}
	throw new RuntimeException( 'Expected a JSON response.' );
}

// Full snapshot replaces stored state, including an empty snapshot (remove all).
$response = wishlist_test_call( 'paxig_ajax_sync_user_wishlist', array( 'product_ids' => array( '22', 44 ) ) );
wishlist_test_assert( $response->success, 'A valid integer-ID snapshot should be accepted.' );
wishlist_test_assert( array( 22, 44 ) === $response->data['saved_ids'], 'Sync must return the exact submitted snapshot.' );
wishlist_test_assert( array( 22, 44 ) === $GLOBALS['wishlist_test_user_meta'][1]['paxig_wishlist'], 'Sync must replace rather than union stored IDs.' );

$response = wishlist_test_call( 'paxig_ajax_sync_user_wishlist', array( 'product_ids' => array() ) );
wishlist_test_assert( $response->success && array() === $response->data['saved_ids'], 'An empty snapshot must remove all saved favorites.' );
wishlist_test_assert( array() === $GLOBALS['wishlist_test_user_meta'][1]['paxig_wishlist'], 'Removal must persist as an empty server snapshot.' );

// Reject malformed or oversized input atomically; no user-meta writes occur.
$before_writes = $GLOBALS['wishlist_test_meta_writes'];
foreach ( array(
	array( 7, array( 8 ) ),
	array( 7.5 ),
	array( '7x' ),
	array( '-7' ),
	array( '0' ),
	array( str_repeat( '9', 40 ) ),
	array_fill( 0, 101, 7 ),
) as $invalid_ids ) {
	$response = wishlist_test_call( 'paxig_ajax_sync_user_wishlist', array( 'product_ids' => $invalid_ids ) );
	wishlist_test_assert( ! $response->success, 'Malformed or over-limit IDs must be rejected.' );
	wishlist_test_assert( array() === $GLOBALS['wishlist_test_user_meta'][1]['paxig_wishlist'], 'Rejected input must leave metadata unchanged.' );
}
wishlist_test_assert( $before_writes === $GLOBALS['wishlist_test_meta_writes'], 'Rejected requests must not write metadata.' );

// Current account reads/writes its own metadata only.
$GLOBALS['wishlist_test_user_meta'][1]['paxig_wishlist'] = array( 44 );
$GLOBALS['wishlist_test_user_id'] = 2;
$response = wishlist_test_call( 'paxig_ajax_sync_user_wishlist', array( 'product_ids' => array( 55 ) ) );
wishlist_test_assert( array( 55 ) === $response->data['saved_ids'], 'The active account should receive its own exact snapshot.' );
wishlist_test_assert( array( 44 ) === $GLOBALS['wishlist_test_user_meta'][1]['paxig_wishlist'], 'Another account metadata must not be overwritten.' );

// Product-detail fetch accepts a bounded list and returns no more than that bound.
$GLOBALS['wishlist_test_products_fetched'] = 0;
$response = wishlist_test_call( 'paxig_ajax_get_wishlist_details', array( 'product_ids' => array( 11, 12 ) ) );
wishlist_test_assert( $response->success && 2 === count( $response->data['items'] ), 'Valid bounded detail requests should return the requested visible products.' );
wishlist_test_assert( 2 === $GLOBALS['wishlist_test_products_fetched'], 'Detail lookup must fetch only submitted IDs.' );
wishlist_test_assert( "9.000\u{00A0}₫" === $response->data['items'][0]['price'], 'Formatted VND entities must become displayable Unicode text.' );

$response = wishlist_test_call( 'paxig_ajax_get_wishlist_details', array( 'product_ids' => range( 1, 100 ) ) );
wishlist_test_assert( $response->success && 100 === count( $response->data['items'] ), 'The maximum allowed detail batch should be accepted.' );

$before_fetches = $GLOBALS['wishlist_test_products_fetched'];
$response = wishlist_test_call( 'paxig_ajax_get_wishlist_details', array( 'product_ids' => array_fill( 0, 101, 11 ) ) );
wishlist_test_assert( ! $response->success, 'Detail requests above the bound must be rejected.' );
wishlist_test_assert( $before_fetches === $GLOBALS['wishlist_test_products_fetched'], 'Rejected detail requests must not fetch products.' );

$response = wishlist_test_call( 'paxig_ajax_get_wishlist_details', array( 'product_ids' => array( '11oops' ) ) );
wishlist_test_assert( ! $response->success, 'Malformed detail IDs must be rejected rather than coerced.' );
wishlist_test_assert( $before_fetches === $GLOBALS['wishlist_test_products_fetched'], 'Malformed detail requests must not fetch products.' );

// An unchanged snapshot is successful without an update call; a changed snapshot reports storage failure.
$update_calls_before = $GLOBALS['wishlist_test_meta_writes'];
$GLOBALS['wishlist_test_meta_update_fails'] = true;
$response = wishlist_test_call( 'paxig_ajax_sync_user_wishlist', array( 'product_ids' => array( 55 ) ) );
wishlist_test_assert( $response->success, 'An unchanged snapshot must remain successful when WordPress reports no update.' );
wishlist_test_assert( $update_calls_before === $GLOBALS['wishlist_test_meta_writes'], 'An unchanged snapshot must not call update_user_meta.' );

$response = wishlist_test_call( 'paxig_ajax_sync_user_wishlist', array( 'product_ids' => array( 66 ) ) );
wishlist_test_assert( ! $response->success, 'A failed write of a changed snapshot must return an error.' );
wishlist_test_assert( array( 55 ) === $GLOBALS['wishlist_test_user_meta'][2]['paxig_wishlist'], 'A failed write must preserve the prior stored snapshot.' );
$GLOBALS['wishlist_test_meta_update_fails'] = false;

echo "wishlist PHP tests passed\n";
