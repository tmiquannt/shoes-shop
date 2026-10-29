<?php
/**
 * Registration lifecycle regression tests, without WordPress/DB/network writes.
 * WooCommerce 10.7 processes registration at wp_loaded (before the page query).
 * Its nonce selection is dedicated field when set, otherwise _wpnonce; nonce
 * action is woocommerce-register. Real HTTP/mail-boundary checks are separate.
 */
define('ABSPATH', __DIR__ . '/');
define('DAY_IN_SECONDS', 86400);

class WP_Error {
    private $errors = [];
    function add($code, $message) { $this->errors[$code] = $message; }
    function get_error_codes() { return array_keys($this->errors); }
}
class WP_User {
    public $ID = 21;
    public $user_email = 'customer@example.invalid';
    public $first_name = 'Mai';
    public $last_name = 'Nguyen';
}
class Registration_Test_Redirect extends RuntimeException {
    public $url;
    function __construct($url) { $this->url = $url; }
}
$hooks = [];
function add_filter($tag, $callback, $priority = 10, $args = 1) {
    $GLOBALS['hooks'][$tag][$priority][] = [$callback, $args];
}
function add_action(...$args) { add_filter(...$args); }
function apply_filters($tag, $value, ...$args) {
    $groups = $GLOBALS['hooks'][$tag] ?? [];
    ksort($groups);
    foreach ($groups as $group) foreach ($group as [$callback, $count]) {
        $value = $callback(...array_slice([$value, ...$args], 0, $count));
    }
    return $value;
}
function do_action($tag, ...$args) {
    $groups = $GLOBALS['hooks'][$tag] ?? [];
    ksort($groups);
    foreach ($groups as $group) foreach ($group as [$callback, $count]) $callback(...array_slice($args, 0, $count));
}
function is_admin() { return $GLOBALS['test_admin']; }
function is_account_page() { return $GLOBALS['test_account_page']; }
function wp_verify_nonce($nonce, $action) { return $action === 'woocommerce-register' && $nonce === 'valid-registration-nonce' ? 1 : false; }
function wp_unslash($value) { return is_string($value) ? stripslashes($value) : $value; }
function wc_clean($value) { return trim(strip_tags($value)); }
function wc_sanitize_phone_number($value) { return preg_replace('/[^0-9+]/', '', $value); }
function absint($value) { return abs((int)$value); }
function __($value, ...$args) { return $value; }
function is_email($value) { return filter_var($value, FILTER_VALIDATE_EMAIL); }
function get_bloginfo(...$args) { return 'Test Shop'; }
function wp_specialchars_decode($value, ...$args) { return $value; }
function get_userdata($id) { return $id === 21 ? new WP_User() : false; }
function get_user_meta($id, $key, ...$args) { return $GLOBALS['test_meta'][$id][$key] ?? ''; }
function update_user_meta($id, $key, $value) { $GLOBALS['test_meta'][$id][$key] = $value; return true; }
function delete_user_meta($id, $key) { unset($GLOBALS['test_meta'][$id][$key]); }
function wc_get_page_permalink($page) { return 'https://shop.example/my-account/'; }
function admin_url($path) { return 'https://shop.example/wp-admin/' . $path; }
function wp_validate_redirect($url, $fallback) { return str_starts_with($url, 'https://shop.example/') ? $url : $fallback; }
function add_query_arg($args, $value, $url = null) {
    if (is_array($args)) { $url = $value; }
    else { $args = [$args => $value]; }
    return $url . '?' . http_build_query($args);
}
function wp_mail($recipient, $subject, $message) { $GLOBALS['test_mail'][] = [$recipient, $subject, $message]; return true; }
function wp_safe_redirect($url) { throw new Registration_Test_Redirect($url); }
function wc_set_customer_auth_cookie($id) { $GLOBALS['test_auth_cookies'][] = $id; }
function esc_html($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function wp_salt($scheme = 'auth') { return 'test-salt'; }
function get_transient($key) { return $GLOBALS['test_transients'][$key] ?? false; }
function set_transient($key, $value, $expiration = 0) { $GLOBALS['test_transients'][$key] = $value; return true; }

require __DIR__ . '/../includes/auth-ui.php';
require __DIR__ . '/../includes/email-verification.php';

function reset_context() {
    $_GET = [];
    $_POST = ['register' => 'Register', 'email' => 'customer@example.invalid',
        'woocommerce-register-nonce' => 'valid-registration-nonce',
        'first_name' => 'Mai', 'last_name' => 'Nguyen', 'phone' => '+84 912345678',
        'password' => 'submitted-password', 'password_confirm' => 'submitted-password'];
    $GLOBALS['test_admin'] = false;
    $GLOBALS['test_account_page'] = false;
    $GLOBALS['test_meta'] = [];
    $GLOBALS['test_mail'] = [];
    $GLOBALS['test_auth_cookies'] = [];
    $GLOBALS['test_transients'] = [];
    putenv('PAXIG_SMTP_USERNAME');
    putenv('PAXIG_SMTP_APP_PASSWORD');
}
function configure_test_smtp() {
    putenv('PAXIG_SMTP_USERNAME=sender@example.invalid');
    putenv('PAXIG_SMTP_APP_PASSWORD=test-app-password');
}
function expect($actual, $expected, $message) {
    if ($actual !== $expected) throw new RuntimeException($message . ': ' . json_encode($actual));
}
$passed = 0;
$failed = 0;
function run_test($name, $callback) {
    reset_context();
    try { $callback(); $GLOBALS['passed']++; echo "PASS $name\n"; }
    catch (Throwable $error) { $GLOBALS['failed']++; echo "FAIL $name: {$error->getMessage()}\n"; }
}

run_test('trusted registration recognized before main query', function() {
    expect(is_account_page(), false, 'Fixture must match wp_loaded');
    expect(paxig_email_verification_is_registration_request(), true, 'Registration context');
});
run_test('missing SMTP rejects before customer creation', function() {
    $errors = apply_filters('woocommerce_process_registration_errors', new WP_Error(), '', '', $_POST['email']);
    expect($errors->get_error_codes(), ['paxig_email_verification_mail_unavailable'], 'SMTP error');
    expect($GLOBALS['test_meta'], [], 'No customer writes');
});
run_test('configured SMTP allows registration processing', function() {
    configure_test_smtp();
    $errors = apply_filters('woocommerce_process_registration_errors', new WP_Error(), '', '', $_POST['email']);
    expect($errors->get_error_codes(), [], 'SMTP configured');
});
run_test('submitted password selected before page query', function() {
    expect(apply_filters('pre_option_woocommerce_registration_generate_password', false), 'no', 'Use submitted password');
});
run_test('required fields and password confirmation enforced early', function() {
    $_POST['first_name'] = $_POST['last_name'] = $_POST['phone'] = '';
    $_POST['password_confirm'] = 'different';
    $errors = apply_filters('woocommerce_registration_errors', new WP_Error(), '', $_POST['email']);
    expect($errors->get_error_codes(), ['paxig_registration_first_name', 'paxig_registration_last_name', 'paxig_registration_phone', 'paxig_registration_password_confirm'], 'Required validation');
});
run_test('required password and valid profile mapping', function() {
    $_POST['password'] = '';
    $errors = apply_filters('woocommerce_registration_errors', new WP_Error(), '', $_POST['email']);
    expect($errors->get_error_codes(), ['paxig_registration_password'], 'Missing password');
    $data = apply_filters('woocommerce_new_customer_data', ['role' => 'customer']);
    expect($data, ['role' => 'customer', 'first_name' => 'Mai', 'last_name' => 'Nguyen'], 'Names mapped');
});
run_test('pending registration sends hashed token and prevents automatic auth', function() {
    configure_test_smtp();
    try { do_action('woocommerce_created_customer', 21); }
    catch (Registration_Test_Redirect $redirect) { expect(str_contains($redirect->url, 'paxig_email_verification=sent'), true, 'Sent redirect'); }
    expect(get_user_meta(21, '_paxig_email_verification_required'), 'yes', 'Verification required');
    expect(get_user_meta(21, '_paxig_email_verified'), 'no', 'Pending');
    expect(get_user_meta(21, 'billing_first_name'), 'Mai', 'Billing name');
    expect(get_user_meta(21, 'billing_phone'), '+84912345678', 'Billing phone');
    expect(count($GLOBALS['test_mail']), 1, 'One verification email');
    preg_match('/token=([a-f0-9]{64})/', $GLOBALS['test_mail'][0][2], $matches);
    expect(isset($matches[1]), true, 'Raw token in email');
    expect(get_user_meta(21, '_paxig_email_verification_token_hash'), hash('sha256', $matches[1]), 'Only token hash persisted');
    expect(get_user_meta(21, '_paxig_email_verification_expires_at') > time(), true, 'Token expiry');
    expect(apply_filters('woocommerce_registration_auth_new_customer', true, 21), false, 'No automatic auth');
    $auth_result = apply_filters('wp_authenticate_user', new WP_User());
    expect($auth_result instanceof WP_Error, true, 'Pending login denied');
    expect(count($GLOBALS['test_mail']), 2, 'Auto-resends verification email on unverified login attempt');
    $auth_result2 = apply_filters('wp_authenticate_user', new WP_User());
    expect($auth_result2 instanceof WP_Error, true, 'Pending login still denied');
    expect(count($GLOBALS['test_mail']), 2, 'Rate limited from resending on immediate second attempt');
    expect($GLOBALS['test_auth_cookies'], [], 'No auth cookies');
    expect(apply_filters('woocommerce_email_enabled_customer_new_account', true), false, 'Native new-account email suppressed');
});
run_test('verified and existing accounts retain authentication behavior', function() {
    $user = new WP_User();
    expect(apply_filters('wp_authenticate_user', $user), $user, 'Existing account unaffected');
    update_user_meta(21, '_paxig_email_verification_required', 'yes');
    update_user_meta(21, '_paxig_email_verified', 'yes');
    expect(apply_filters('wp_authenticate_user', $user), $user, 'Verified account allowed');
    expect(apply_filters('woocommerce_registration_auth_new_customer', true, 21), true, 'Verified auth preserved');
});
run_test('WooCommerce fallback nonce accepted', function() {
    $_POST['_wpnonce'] = $_POST['woocommerce-register-nonce'];
    unset($_POST['woocommerce-register-nonce']);
    expect(paxig_email_verification_is_registration_request(), true, 'Fallback nonce');
});
run_test('invalid dedicated nonce cannot use valid fallback', function() {
    $_POST['_wpnonce'] = 'valid-registration-nonce';
    $_POST['woocommerce-register-nonce'] = 'invalid';
    expect(paxig_email_verification_is_registration_request(), false, 'Dedicated nonce wins');
});
run_test('arbitrary or malformed registration POST does not alter customer creation', function() {
    foreach (['missing_nonce', 'invalid_nonce', 'array_nonce', 'array_email', 'no_email', 'no_register'] as $case) {
        reset_context();
        if ($case === 'missing_nonce') unset($_POST['woocommerce-register-nonce']);
        if ($case === 'invalid_nonce') $_POST['woocommerce-register-nonce'] = 'invalid';
        if ($case === 'array_nonce') $_POST['woocommerce-register-nonce'] = ['bad'];
        if ($case === 'array_email') $_POST['email'] = ['bad'];
        if ($case === 'no_email') unset($_POST['email']);
        if ($case === 'no_register') unset($_POST['register']);
        // Even a queried account page must not make an untrusted POST valid.
        $GLOBALS['test_account_page'] = true;
        expect(paxig_email_verification_is_registration_request(), false, $case);
        expect(apply_filters('pre_option_woocommerce_registration_generate_password', false), false, $case . ' password option');
        expect(apply_filters('woocommerce_new_customer_data', ['role' => 'customer']), ['role' => 'customer'], $case . ' data');
        expect(apply_filters('woocommerce_process_registration_errors', new WP_Error(), '', '', '')->get_error_codes(), [], $case . ' SMTP validation');
        expect(apply_filters('woocommerce_registration_errors', new WP_Error(), '', '')->get_error_codes(), [], $case . ' profile validation');
        expect(apply_filters('woocommerce_email_enabled_customer_new_account', true), true, $case . ' native email');
        do_action('woocommerce_created_customer', 21);
        expect($GLOBALS['test_meta'], [], $case . ' metadata');
        expect($GLOBALS['test_mail'], [], $case . ' mail');
    }
});

run_test('register submit value cannot bypass Woo registration protections', function() {
    foreach (['', ['Register']] as $value) {
        reset_context();
        $_POST['register'] = $value;
        expect(paxig_email_verification_is_registration_request(), true, 'Woo checks register presence, not its value');
        $errors = apply_filters('woocommerce_process_registration_errors', new WP_Error(), '', '', $_POST['email']);
        expect(in_array('paxig_email_verification_mail_unavailable', $errors->get_error_codes(), true), true, 'Missing SMTP still blocks creation');
    }
});
run_test('GET registration view and admin-created customers remain unaffected', function() {
    $_POST = [];
    $_GET['action'] = 'register';
    expect(paxig_email_verification_is_registration_request(), false, 'GET is presentation only');
    do_action('woocommerce_created_customer', 21);
    expect($GLOBALS['test_meta'], [], 'GET no metadata');
    reset_context();
    $GLOBALS['test_admin'] = true;
    expect(paxig_email_verification_is_registration_request(), false, 'Admin request');
    do_action('woocommerce_created_customer', 21);
    expect($GLOBALS['test_meta'], [], 'Admin no metadata');
});
run_test('REST customer creation remains unaffected', function() {
    define('REST_REQUEST', true);
    expect(paxig_email_verification_is_registration_request(), false, 'REST request');
    do_action('woocommerce_created_customer', 21);
    expect($GLOBALS['test_meta'], [], 'REST no metadata');
});

echo "$passed passed; $failed failed\n";
exit($failed ? 1 : 0);
