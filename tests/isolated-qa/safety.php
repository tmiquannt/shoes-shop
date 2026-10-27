<?php
/** Test installation only: no delivery, external HTTP or real payment methods. */
defined('ABSPATH') || exit;
if (wp_get_environment_type() !== 'local' || DB_NAME !== 'paxig_qa') {
    throw new RuntimeException('QA safety plugin refuses a non-fixture database.');
}
add_action('send_headers', function () {
    header('X-Paxig-Isolated-QA: synthetic-only');
});
add_filter('pre_wp_mail', function () {
    update_option('paxig_qa_mail_interceptions', 1 + (int) get_option('paxig_qa_mail_interceptions', 0), false);
    return true; // Test interception, NOT delivery proof.
}, PHP_INT_MAX);
add_filter('pre_http_request', function () {
    return new WP_Error('paxig_qa_network_blocked', 'Outbound HTTP is disabled in isolated QA.');
}, PHP_INT_MAX);
add_filter('woocommerce_available_payment_gateways', function ($gateways) {
    return array_intersect_key($gateways, ['cod' => true]);
}, PHP_INT_MAX);
