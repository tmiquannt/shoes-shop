<?php
/** Teardown only the synthetic checkout fixture, never another database/customer. */
if (wp_get_environment_type() !== 'local' || DB_NAME !== 'paxig_qa') {
    throw new RuntimeException('Refusing cleanup outside QA.');
}
$orders = wc_get_orders(['billing_email' => 'checkout-fixture@example.invalid', 'limit' => -1]);
foreach ($orders as $order) {
    if ((int) $order->get_customer_id() !== 0 || $order->get_payment_method() !== 'cod') {
        throw new RuntimeException('Unexpected fixture ownership or payment method.');
    }
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        if (!$product || $product->get_sku() !== 'PAXIG-QA-SHOE') {
            throw new RuntimeException('Unexpected non-fixture product; cleanup stopped.');
        }
    }
    echo 'Verified guest fixture order ' . $order->get_id() . '; total=' . $order->get_total() . '; restoring stock and deleting fixture.' . PHP_EOL;
    wc_increase_stock_levels($order->get_id());
    $order->delete(true);
}
echo 'Remaining fixture orders: ' . count(wc_get_orders(['billing_email' => 'checkout-fixture@example.invalid', 'limit' => -1])) . PHP_EOL;
