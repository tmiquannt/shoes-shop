<?php
/**
 * Plugin Name: PAXIG VNPAY for WooCommerce
 * Description: Redirects WooCommerce orders to VNPAY and confirms payment through the signed VNPAY IPN.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * Author: PAXIG
 * License: GPL-2.0-or-later
 * Text Domain: paxig-vnpay
 */

if (!defined('ABSPATH')) {
	exit;
}

define('PAXIG_VNPAY_VERSION', '1.0.0');
define('PAXIG_VNPAY_ID', 'paxig_vnpay');

add_action('plugins_loaded', 'paxig_vnpay_load_gateway', 11);
add_action('rest_api_init', 'paxig_vnpay_register_ipn_route');
add_action('woocommerce_api_paxig_vnpay_return', 'paxig_vnpay_handle_return');
add_action('woocommerce_thankyou_' . PAXIG_VNPAY_ID, 'paxig_vnpay_render_return_message', 5);

/** Load the gateway only after WooCommerce has registered its base class. */
function paxig_vnpay_load_gateway() {
	if (!class_exists('WC_Payment_Gateway')) {
		return;
	}

	class WC_Gateway_Paxig_VNPAY extends WC_Payment_Gateway {
		const SANDBOX_URL = 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';

		public function __construct() {
			$this->id                 = PAXIG_VNPAY_ID;
			$this->method_title       = 'VNPAY';
			$this->method_description = __('Redirect customers to VNPAY to pay for their order.', 'paxig-vnpay');
			$this->has_fields         = false;
			$this->supports           = array('products');

			$this->init_form_fields();
			$this->init_settings();

			$this->enabled     = $this->get_option('enabled', 'no');
			$this->title       = $this->get_option('title', 'VNPAY');
			$this->description = $this->get_option('description', __('Pay online via VNPAY.', 'paxig-vnpay'));

			add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
		}

		public function init_form_fields() {
			$this->form_fields = array(
				'enabled' => array(
					'title'   => __('Enable/Disable', 'paxig-vnpay'),
					'type'    => 'checkbox',
					'label'   => __('Enable VNPAY payments', 'paxig-vnpay'),
					'default' => 'no',
				),
				'title' => array(
					'title'       => __('Checkout title', 'paxig-vnpay'),
					'type'        => 'text',
					'default'     => 'VNPAY',
					'desc_tip'    => true,
					'description' => __('Shown to customers as a payment option.', 'paxig-vnpay'),
				),
				'description' => array(
					'title'       => __('Checkout description', 'paxig-vnpay'),
					'type'        => 'textarea',
					'default'     => __('Pay online via VNPAY.', 'paxig-vnpay'),
					'description' => __('Shown below the payment option during checkout.', 'paxig-vnpay'),
				),
				'environment' => array(
					'title'       => __('Environment', 'paxig-vnpay'),
					'type'        => 'select',
					'default'     => 'sandbox',
					'options'     => array(
						'sandbox'    => __('Sandbox', 'paxig-vnpay'),
						'production' => __('Production', 'paxig-vnpay'),
					),
					'description' => __('Use Sandbox first. Get the production payment URL from VNPAY for your merchant account.', 'paxig-vnpay'),
				),
				'tmn_code' => array(
					'title'       => __('VNPAY Terminal ID (vnp_TmnCode)', 'paxig-vnpay'),
					'type'        => 'text',
					'default'     => '',
					'description' => __('Provided by VNPAY for this merchant website.', 'paxig-vnpay'),
				),
				'hash_secret' => array(
					'title'       => __('VNPAY Secret Key (vnp_HashSecret)', 'paxig-vnpay'),
					'type'        => 'password',
					'default'     => '',
					'description' => __('Used to sign payment requests and verify VNPAY responses. Never put this key in theme code.', 'paxig-vnpay'),
				),
				'production_url' => array(
					'title'       => __('Production payment URL', 'paxig-vnpay'),
					'type'        => 'text',
					'default'     => '',
					'description' => __('Required only in Production. Enter the HTTPS payment URL supplied by VNPAY; only vnpay.vn and vnpayment.vn hosts are accepted.', 'paxig-vnpay'),
				),
				'locale' => array(
					'title'   => __('VNPAY language', 'paxig-vnpay'),
					'type'    => 'select',
					'default' => 'vn',
					'options' => array(
						'vn' => __('Vietnamese', 'paxig-vnpay'),
						'en' => __('English', 'paxig-vnpay'),
					),
				),
			);
		}

		/** Return the API endpoint only when it is an approved HTTPS VNPAY host. */
		public function get_payment_endpoint() {
			$environment = $this->get_option('environment', 'sandbox');
			if ('sandbox' === $environment) {
				return self::SANDBOX_URL;
			}

			if ('production' === $environment) {
				return paxig_vnpay_validate_production_url($this->get_option('production_url', ''));
			}

			return '';
		}

		private function credentials_are_ready() {
			return 'yes' === $this->enabled
				&& '' !== trim((string) $this->get_option('tmn_code', ''))
				&& '' !== trim((string) $this->get_option('hash_secret', ''))
				&& '' !== $this->get_payment_endpoint()
				&& paxig_vnpay_callbacks_are_https();
		}

		public function is_available() {
			if (!parent::is_available()) {
				return false;
			}

			return 'VND' === get_woocommerce_currency() && $this->credentials_are_ready();
		}

		public function process_payment($order_id) {
			$order = wc_get_order($order_id);

			if (!$order) {
				wc_add_notice(__('We could not load this order. Please try again.', 'paxig-vnpay'), 'error');
				return array('result' => 'failure');
			}

			if ($order->is_paid()) {
				return array('result' => 'success', 'redirect' => $this->get_return_url($order));
			}

			if ('VND' !== $order->get_currency()) {
				wc_add_notice(__('VNPAY only supports orders in VND.', 'paxig-vnpay'), 'error');
				return array('result' => 'failure');
			}

			if (!$this->credentials_are_ready()) {
				wc_add_notice(__('VNPAY is not configured yet. Please choose another payment method.', 'paxig-vnpay'), 'error');
				return array('result' => 'failure');
			}

			$amount = paxig_vnpay_amount_for_order($order);
			if (null === $amount) {
				wc_add_notice(__('This order total cannot be sent to VNPAY. Please contact the store.', 'paxig-vnpay'), 'error');
				return array('result' => 'failure');
			}

			try {
				$txn_ref = paxig_vnpay_create_transaction_reference($order->get_id());
			} catch (Throwable $exception) {
				wc_add_notice(__('We could not start VNPAY checkout. Please try again.', 'paxig-vnpay'), 'error');
				return array('result' => 'failure');
			}

			$now = new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
			$ip_address = class_exists('WC_Geolocation') ? WC_Geolocation::get_ip_address() : '';
			if (!filter_var($ip_address, FILTER_VALIDATE_IP)) {
				$ip_address = '127.0.0.1';
			}

			$params = array(
				'vnp_Version'    => '2.1.0',
				'vnp_Command'    => 'pay',
				'vnp_TmnCode'    => trim((string) $this->get_option('tmn_code', '')),
				'vnp_Amount'     => $amount,
				'vnp_CurrCode'   => 'VND',
				'vnp_TxnRef'     => $txn_ref,
				'vnp_OrderInfo'  => 'Thanh toan don hang ' . (string) $order->get_id(),
				'vnp_OrderType'  => 'other',
				'vnp_Locale'     => in_array($this->get_option('locale', 'vn'), array('vn', 'en'), true) ? $this->get_option('locale', 'vn') : 'vn',
				'vnp_IpAddr'     => $ip_address,
				'vnp_CreateDate' => $now->format('YmdHis'),
				'vnp_ExpireDate' => $now->modify('+15 minutes')->format('YmdHis'),
				'vnp_ReturnUrl'  => add_query_arg('wc-api', 'paxig_vnpay_return', home_url('/')),
			);

			ksort($params, SORT_STRING);
			$hash_data = paxig_vnpay_build_hash_data($params);
			$params['vnp_SecureHash'] = hash_hmac('sha512', $hash_data, (string) $this->get_option('hash_secret', ''));

			$payment_url = $this->get_payment_endpoint() . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC1738);
			$references = $order->get_meta('_paxig_vnpay_txn_refs', true);
			$references = is_array($references) ? $references : array();
			$references[] = $txn_ref;
			$references = array_values(array_unique($references));

			$order->update_meta_data('_paxig_vnpay_txn_refs', $references);
			$order->update_meta_data('_paxig_vnpay_active_txn_ref', $txn_ref);
			$order->add_order_note(sprintf(__('Customer redirected to VNPAY. Reference: %s', 'paxig-vnpay'), $txn_ref));
			$order->save();

			if (WC()->cart) {
				WC()->cart->empty_cart();
			}

			return array(
				'result'   => 'success',
				'redirect' => esc_url_raw($payment_url),
			);
		}
	}

	add_filter('woocommerce_payment_gateways', 'paxig_vnpay_add_gateway');
}

/** Register this gateway with WooCommerce. */
function paxig_vnpay_add_gateway($gateways) {
	$gateways[] = 'WC_Gateway_Paxig_VNPAY';
	return $gateways;
}

/** Validate the configured production endpoint against official VNPAY domains. */
function paxig_vnpay_validate_production_url($url) {
	$url = trim((string) $url);
	$parts = wp_parse_url($url);

	if (!is_array($parts) || empty($parts['scheme']) || 'https' !== strtolower($parts['scheme']) || empty($parts['host'])) {
		return '';
	}

	if (isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment']) || (isset($parts['port']) && 443 !== (int) $parts['port'])) {
		return '';
	}

	$host = strtolower(rtrim($parts['host'], '.'));
	$approved = false;
	foreach (array('vnpay.vn', 'vnpayment.vn') as $domain) {
		if ($host === $domain || substr($host, -strlen('.' . $domain)) === '.' . $domain) {
			$approved = true;
			break;
		}
	}

	if (!$approved) {
		return '';
	}

	return esc_url_raw($url, array('https'));
}

/** Convert a VND order total to the integer amount format required by VNPAY. */
function paxig_vnpay_amount_for_order($order) {
	if (!$order || 'VND' !== $order->get_currency()) {
		return null;
	}

	$total = (float) $order->get_total();
	$amount = (int) round($total * 100);
	if ($total <= 0 || $amount <= 0 || $amount > 999999999999) {
		return null;
	}

	return (string) $amount;
}

/** Create a unique, alphanumeric VNPAY reference that carries the Woo order ID. */
function paxig_vnpay_create_transaction_reference($order_id) {
	$order_part = strtoupper(base_convert((string) absint($order_id), 10, 36));
	if ('' === $order_part || strlen($order_part) > 10) {
		throw new RuntimeException('Order ID cannot be encoded as a VNPAY reference.');
	}

	return str_pad($order_part, 10, '0', STR_PAD_LEFT) . strtoupper(bin2hex(random_bytes(6)));
}

/** Build the VNPAY checksum string using its documented sorted URL-encoded parameters. */
function paxig_vnpay_build_hash_data($params) {
	ksort($params, SORT_STRING);
	$parts = array();
	foreach ($params as $key => $value) {
		if (is_scalar($value)) {
			$parts[] = urlencode((string) $key) . '=' . urlencode((string) $value);
		}
	}

	return implode('&', $parts);
}

/** Collect only scalar VNPAY request fields. */
function paxig_vnpay_collect_params($source) {
	$params = array();
	foreach ((array) $source as $key => $value) {
		if (is_string($key) && 1 === preg_match('/^vnp_[A-Za-z0-9]+$/D', $key) && is_scalar($value)) {
			$params[$key] = sanitize_text_field((string) $value);
		}
	}

	return $params;
}

/** Verify the VNPAY HMAC using constant-time comparison. */
function paxig_vnpay_verify_signature($params, $secret) {
	if (empty($params['vnp_SecureHash']) || '' === (string) $secret) {
		return false;
	}

	$provided_hash = strtolower((string) $params['vnp_SecureHash']);
	unset($params['vnp_SecureHash'], $params['vnp_SecureHashType']);
	$expected_hash = hash_hmac('sha512', paxig_vnpay_build_hash_data($params), (string) $secret);

	return hash_equals($expected_hash, $provided_hash);
}

/** Resolve a transaction reference to an order and confirm it belongs to that order. */
function paxig_vnpay_find_order_for_reference($txn_ref) {
	if (!is_string($txn_ref) || 1 !== preg_match('/^[0-9A-Z]{10}[0-9A-F]{12}$/D', $txn_ref)) {
		return false;
	}

	$order_part = ltrim(substr($txn_ref, 0, 10), '0');
	if ('' === $order_part) {
		return false;
	}

	$order_id = (int) base_convert($order_part, 36, 10);
	$order = wc_get_order($order_id);
	if (!$order) {
		return false;
	}

	$references = $order->get_meta('_paxig_vnpay_txn_refs', true);
	if (!is_array($references) || !in_array($txn_ref, $references, true)) {
		return false;
	}

	return $order;
}

/** Return a VNPAY-compatible acknowledgement payload for the IPN callback. */
function paxig_vnpay_ipn_response($code, $message) {
	return new WP_REST_Response(array('RspCode' => (string) $code, 'Message' => (string) $message), 200);
}

/** Register the public, signature-protected VNPAY server callback. */
function paxig_vnpay_register_ipn_route() {
	register_rest_route('paxig/v1', '/vnpay/ipn', array(
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'paxig_vnpay_handle_ipn',
		'permission_callback' => '__return_true',
	));
}

/** Verify the signed IPN, exact order amount, merchant ID and payment state. */
function paxig_vnpay_handle_ipn($request) {
	$settings = get_option('woocommerce_' . PAXIG_VNPAY_ID . '_settings', array());
	$settings = is_array($settings) ? $settings : array();
	$params = paxig_vnpay_collect_params($request->get_query_params());

	if (!paxig_vnpay_verify_signature($params, isset($settings['hash_secret']) ? $settings['hash_secret'] : '')) {
		return paxig_vnpay_ipn_response('97', 'Invalid signature');
	}

	if (empty($settings['tmn_code']) || empty($params['vnp_TmnCode']) || !hash_equals((string) $settings['tmn_code'], (string) $params['vnp_TmnCode'])) {
		return paxig_vnpay_ipn_response('97', 'Invalid merchant');
	}

	$order = paxig_vnpay_find_order_for_reference(isset($params['vnp_TxnRef']) ? $params['vnp_TxnRef'] : '');
	if (!$order) {
		return paxig_vnpay_ipn_response('01', 'Order not found');
	}

	$expected_amount = paxig_vnpay_amount_for_order($order);
	if (null === $expected_amount || empty($params['vnp_Amount']) || !ctype_digit((string) $params['vnp_Amount']) || !hash_equals($expected_amount, (string) $params['vnp_Amount'])) {
		return paxig_vnpay_ipn_response('04', 'Invalid amount');
	}
	if (isset($params['vnp_CurrCode']) && 'VND' !== (string) $params['vnp_CurrCode']) {
		return paxig_vnpay_ipn_response('04', 'Invalid currency');
	}

	if ($order->is_paid()) {
		return paxig_vnpay_ipn_response('02', 'Order already confirmed');
	}

	$response_code = isset($params['vnp_ResponseCode']) ? (string) $params['vnp_ResponseCode'] : '';
	$transaction_status = isset($params['vnp_TransactionStatus']) ? (string) $params['vnp_TransactionStatus'] : '';
	$txn_ref = (string) $params['vnp_TxnRef'];
	if (1 !== preg_match('/^\d{2}$/D', $response_code) || 1 !== preg_match('/^\d{2}$/D', $transaction_status)) {
		return paxig_vnpay_ipn_response('99', 'Invalid transaction response');
	}

	if ('00' === $response_code && '00' === $transaction_status) {
		$transaction_id = isset($params['vnp_TransactionNo']) ? (string) $params['vnp_TransactionNo'] : '';
		if (1 !== preg_match('/^\d{1,15}$/D', $transaction_id)) {
			return paxig_vnpay_ipn_response('99', 'Invalid VNPAY transaction number');
		}

		$order->payment_complete($transaction_id);
		$order->add_order_note(sprintf(__('VNPAY confirmed payment. Reference: %1$s; transaction: %2$s.', 'paxig-vnpay'), $txn_ref, $transaction_id));
		return paxig_vnpay_ipn_response('00', 'Confirm Success');
	}

	$active_reference = (string) $order->get_meta('_paxig_vnpay_active_txn_ref', true);
	if ($active_reference === $txn_ref && in_array($order->get_status(), array('pending', 'on-hold', 'failed'), true)) {
		$order->update_status('failed', sprintf(__('VNPAY payment failed. Reference: %1$s; response: %2$s; transaction status: %3$s.', 'paxig-vnpay'), $txn_ref, $response_code, $transaction_status));
	}

	return paxig_vnpay_ipn_response('00', 'Confirm Success');
}

/** Validate the customer return for display, then return to the WooCommerce order page. */
function paxig_vnpay_handle_return() {
	$settings = get_option('woocommerce_' . PAXIG_VNPAY_ID . '_settings', array());
	$settings = is_array($settings) ? $settings : array();
	$params = paxig_vnpay_collect_params(wp_unslash($_GET));

	$order = false;
	$is_valid = paxig_vnpay_verify_signature($params, isset($settings['hash_secret']) ? $settings['hash_secret'] : '')
		&& !empty($settings['tmn_code'])
		&& !empty($params['vnp_TmnCode'])
		&& hash_equals((string) $settings['tmn_code'], (string) $params['vnp_TmnCode']);

	if ($is_valid && isset($params['vnp_TxnRef'])) {
		$order = paxig_vnpay_find_order_for_reference($params['vnp_TxnRef']);
		$is_valid = (bool) $order
			&& null !== paxig_vnpay_amount_for_order($order)
			&& isset($params['vnp_Amount'])
			&& hash_equals(paxig_vnpay_amount_for_order($order), (string) $params['vnp_Amount'])
			&& (!isset($params['vnp_CurrCode']) || 'VND' === (string) $params['vnp_CurrCode']);
	}

	if (!$is_valid || !$order) {
		if (function_exists('wc_add_notice')) {
			wc_add_notice(__('We could not verify the VNPAY return. Payment status will be updated only after VNPAY confirms it.', 'paxig-vnpay'), 'error');
		}
		wp_safe_redirect(function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/'));
		exit;
	}

	$result = isset($params['vnp_ResponseCode'], $params['vnp_TransactionStatus'])
		&& '00' === (string) $params['vnp_ResponseCode']
		&& '00' === (string) $params['vnp_TransactionStatus']
		? 'success'
		: 'failed';

	wp_safe_redirect(add_query_arg('paxig_vnpay_result', $result, $order->get_checkout_order_received_url()));
	exit;
}

/** Explain that the IPN, not the browser redirect, controls the payment state. */
function paxig_vnpay_render_return_message($order_id) {
	$order = wc_get_order($order_id);
	if (!$order || $order->is_paid()) {
		return;
	}

	$result = isset($_GET['paxig_vnpay_result']) ? sanitize_key(wp_unslash($_GET['paxig_vnpay_result'])) : '';
	if ('success' === $result) {
		echo '<p class="woocommerce-info">' . esc_html__('VNPAY returned a successful result. We are waiting for VNPAY server confirmation before marking this order as paid.', 'paxig-vnpay') . '</p>';
	} elseif ('failed' === $result) {
		echo '<p class="woocommerce-error">' . esc_html__('VNPAY did not report a successful payment. You can retry payment from your order details.', 'paxig-vnpay') . '</p>';
	}
}

/** Add Checkout Blocks support while reusing the server-side gateway process_payment flow. */
add_action('woocommerce_blocks_loaded', 'paxig_vnpay_register_blocks_support');
function paxig_vnpay_register_blocks_support() {
	$abstract_class = 'Automattic\\WooCommerce\\Blocks\\Payments\\Integrations\\AbstractPaymentMethodType';
	if (!class_exists($abstract_class)) {
		return;
	}

	class Paxig_VNPAY_Blocks_Integration extends Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType {
		protected $name = PAXIG_VNPAY_ID;

		public function initialize() {
			$settings = get_option('woocommerce_' . PAXIG_VNPAY_ID . '_settings', array());
			$this->settings = is_array($settings) ? $settings : array();
		}

		public function is_active() {
			return paxig_vnpay_settings_are_ready($this->settings);
		}

		public function get_payment_method_script_handles() {
			wp_register_script(
				'paxig-vnpay-blocks',
				plugins_url('assets/js/vnpay-blocks.js', __FILE__),
				array('wc-blocks-registry', 'wc-settings', 'wp-element'),
				PAXIG_VNPAY_VERSION,
				true
			);

			return array('paxig-vnpay-blocks');
		}

		public function get_payment_method_script_handles_for_admin() {
			return $this->get_payment_method_script_handles();
		}

		public function get_payment_method_data() {
			return array(
				'title'       => isset($this->settings['title']) ? $this->settings['title'] : 'VNPAY',
				'description' => isset($this->settings['description']) ? $this->settings['description'] : __('Pay online via VNPAY.', 'paxig-vnpay'),
				'supports'    => array('products'),
			);
		}
	}

	add_action('woocommerce_blocks_payment_method_type_registration', function ($registry) {
		$registry->register(new Paxig_VNPAY_Blocks_Integration());
	});
}

/** Determine whether credentials, endpoint and store currency allow the gateway to appear. */
function paxig_vnpay_settings_are_ready($settings) {
	if (!is_array($settings) || 'yes' !== (isset($settings['enabled']) ? $settings['enabled'] : 'no')) {
		return false;
	}

	if (empty($settings['tmn_code']) || empty($settings['hash_secret']) || !function_exists('get_woocommerce_currency') || 'VND' !== get_woocommerce_currency()) {
		return false;
	}

	$environment = isset($settings['environment']) ? $settings['environment'] : 'sandbox';
	if ('sandbox' === $environment) {
		return paxig_vnpay_callbacks_are_https();
	}

	return 'production' === $environment
		&& '' !== paxig_vnpay_validate_production_url(isset($settings['production_url']) ? $settings['production_url'] : '')
		&& paxig_vnpay_callbacks_are_https();
}

/** VNPAY callbacks must use the public HTTPS URLs registered with the merchant account. */
function paxig_vnpay_callbacks_are_https() {
	$ipn = wp_parse_url(rest_url('paxig/v1/vnpay/ipn'));
	$return = wp_parse_url(add_query_arg('wc-api', 'paxig_vnpay_return', home_url('/')));

	return is_array($ipn) && isset($ipn['scheme']) && 'https' === strtolower($ipn['scheme'])
		&& is_array($return) && isset($return['scheme']) && 'https' === strtolower($return['scheme']);
}
