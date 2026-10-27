<?php
/** Run before synthetic checkout. Fails closed outside this fixture database. */
if (wp_get_environment_type() !== 'local' || DB_NAME !== 'paxig_qa') {
    throw new RuntimeException('Wrong QA environment.');
}
$before = (int) get_option('paxig_qa_mail_interceptions', 0);
if (!wp_mail('qa@example.invalid', 'Synthetic safety check', 'No delivery allowed.')
    || (int) get_option('paxig_qa_mail_interceptions', 0) !== $before + 1) {
    throw new RuntimeException('Mail interception not proven.');
}
$http = wp_remote_get('https://example.invalid/', ['timeout' => 1]);
if (!is_wp_error($http) || $http->get_error_code() !== 'paxig_qa_network_blocked') {
    throw new RuntimeException('HTTP blocking not proven.');
}
$gateways = WC()->payment_gateways()->get_available_payment_gateways();
if (array_keys($gateways) !== ['cod']) {
    throw new RuntimeException('Expected only synthetic COD gateway.');
}
echo 'PASS local DB guard, intercepted mail, blocked HTTP, COD-only gateways.' . PHP_EOL;
