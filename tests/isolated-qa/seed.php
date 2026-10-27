<?php
/** Synthetic fixture creation. Run ONLY through isolated compose CLI. */
if (wp_get_environment_type() !== 'local' || DB_NAME !== 'paxig_qa') {
    throw new RuntimeException('Refusing to seed outside the isolated QA database.');
}
update_option('woocommerce_currency', 'VND');
update_option('woocommerce_coming_soon', 'no'); // Expose synthetic PDPs to anonymous QA sessions only.
update_option('woocommerce_default_country', 'VN');
update_option('woocommerce_enable_guest_checkout', 'yes');
update_option('woocommerce_enable_signup_and_login_from_checkout', 'no');
update_option('woocommerce_enable_myaccount_registration', 'yes');
update_option('woocommerce_calc_taxes', 'no');
update_option('woocommerce_cod_settings', ['enabled' => 'yes', 'title' => 'QA cash on delivery']);
update_option('woocommerce_store_address', 'Synthetic QA address');
update_option('woocommerce_store_city', 'Ho Chi Minh City');
update_option('woocommerce_store_postcode', '700000');
update_option('woocommerce_store_pages', []);
WC_Install::create_pages();
foreach (['cart' => '[woocommerce_cart]', 'checkout' => '[woocommerce_checkout]'] as $page => $content) {
    wp_update_post(['ID' => wc_get_page_id($page), 'post_content' => $content]);
}
if (!wc_get_product_id_by_sku('PAXIG-QA-SHOE')) {
    $product = new WC_Product_Simple();
    $product->set_name('PAXIG QA Shoe');
    $product->set_sku('PAXIG-QA-SHOE');
    $product->set_regular_price('250000');
    $product->set_status('publish');
    $product->set_manage_stock(true);
    $product->set_stock_quantity(5);
    $product->set_virtual(true); // Shipping integration is a separate test.
    $product->save();
}
echo 'Synthetic product ID: ' . wc_get_product_id_by_sku('PAXIG-QA-SHOE') . PHP_EOL;
echo 'Checkout type: classic; shipping, taxes, provider delivery NOT tested.' . PHP_EOL;
