<?php
/** PAXIG customer account overview, using live WooCommerce customer and order data. */
defined('ABSPATH') || exit;

$customer = wp_get_current_user();
$customer_id = (int) $customer->ID;
$customer_name = trim((string) $customer->first_name);
if ($customer_name === '') $customer_name = $customer->display_name;

$orders = wc_get_orders([
    'customer_id' => $customer_id,
    'limit' => 3,
    'orderby' => 'date',
    'order' => 'DESC',
]);
$shop_url = wc_get_page_permalink('shop');
$orders_url = wc_get_endpoint_url('orders', '', wc_get_page_permalink('myaccount'));
$addresses_url = wc_get_endpoint_url('edit-address', '', wc_get_page_permalink('myaccount'));
$profile_url = wc_get_endpoint_url('edit-account', '', wc_get_page_permalink('myaccount'));
$shipping_address = wc_get_account_formatted_address('shipping', $customer_id);
$billing_address = wc_get_account_formatted_address('billing', $customer_id);
$phone = (string) get_user_meta($customer_id, 'billing_phone', true);
?>
<div class="paxig-account-dashboard">
	<section class="paxig-account-recent" aria-labelledby="paxig-account-recent-title">
		<header class="paxig-account-section-heading">
			<div>
				<h2 id="paxig-account-recent-title">Recent orders</h2>
				<p>Your latest pairs and their delivery status.</p>
			</div>
			<?php if (!empty($orders)) : ?>
				<a class="paxig-account-text-link" href="<?php echo esc_url($orders_url); ?>">All orders</a>
			<?php endif; ?>
		</header>

		<?php if (!empty($orders)) : ?>
			<ul class="paxig-account-order-list">
				<?php foreach ($orders as $order) :
					$items = $order->get_items();
					$first_item = $items ? reset($items) : false;
					$product = $first_item ? $first_item->get_product() : false;
					$product_name = $first_item ? $first_item->get_name() : 'Order items';
					$product_count = count($items);
					$order_date = $order->get_date_created();
					$status = $order->get_status();
				?>
					<li class="paxig-account-order">
						<a class="paxig-account-order__main" href="<?php echo esc_url($order->get_view_order_url()); ?>">
							<span class="paxig-account-order__image">
								<?php if ($product) : ?>
									<?php echo wp_kses_post($product->get_image('woocommerce_thumbnail', ['alt' => $product_name])); ?>
								<?php else : ?>
									<span aria-hidden="true">P</span>
								<?php endif; ?>
							</span>
							<span class="paxig-account-order__details">
								<strong>Order #<?php echo esc_html($order->get_order_number()); ?></strong>
								<span><?php echo esc_html($order_date ? wc_format_datetime($order_date, get_option('date_format')) : ''); ?></span>
								<span class="paxig-account-order__item-name"><?php echo esc_html($product_name); ?><?php echo $product_count > 1 ? ' +' . esc_html((string) ($product_count - 1)) . ' more' : ''; ?></span>
							</span>
							<span class="paxig-account-order__summary">
								<span class="paxig-account-status paxig-account-status--<?php echo esc_attr(sanitize_html_class($status)); ?>"><?php echo esc_html(wc_get_order_status_name($status)); ?></span>
								<strong><?php echo wp_kses_post($order->get_formatted_order_total()); ?></strong>
							</span>
							<span class="paxig-account-order__action">View order</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<div class="paxig-account-empty">
				<div>
					<h3>You haven’t ordered a pair yet.</h3>
					<p>Once you place an order, you can follow its progress and see every item here.</p>
				</div>
				<a class="button paxig-account-primary" href="<?php echo esc_url($shop_url); ?>">Explore shoes</a>
			</div>
		<?php endif; ?>
	</section>

	<div class="paxig-account-summary-grid">
		<section class="paxig-account-summary" aria-labelledby="paxig-account-details-title">
			<header class="paxig-account-section-heading">
				<div><h2 id="paxig-account-details-title">Your details</h2></div>
				<a class="paxig-account-text-link" href="<?php echo esc_url($profile_url); ?>">Edit</a>
			</header>
			<dl class="paxig-account-definition-list">
				<div><dt>Name</dt><dd><?php echo esc_html(trim($customer->first_name . ' ' . $customer->last_name) ?: $customer->display_name); ?></dd></div>
				<div><dt>Email</dt><dd><?php echo esc_html($customer->user_email); ?></dd></div>
				<?php if ($phone !== '') : ?><div><dt>Phone</dt><dd><?php echo esc_html($phone); ?></dd></div><?php endif; ?>
			</dl>
		</section>

		<section class="paxig-account-summary" aria-labelledby="paxig-account-addresses-title">
			<header class="paxig-account-section-heading">
				<div><h2 id="paxig-account-addresses-title">Address book</h2></div>
				<a class="paxig-account-text-link" href="<?php echo esc_url($addresses_url); ?>">Manage</a>
			</header>
			<div class="paxig-account-address-preview">
				<h3>Shipping address</h3>
				<?php if ($shipping_address) : ?>
					<address><?php echo wp_kses_post($shipping_address); ?></address>
				<?php else : ?>
					<p>No shipping address saved yet.</p>
				<?php endif; ?>
				<?php if ($billing_address && $billing_address !== $shipping_address) : ?>
					<h3>Billing address</h3>
					<address><?php echo wp_kses_post($billing_address); ?></address>
				<?php endif; ?>
			</div>
		</section>
	</div>
</div>
