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
				<h2 id="paxig-account-recent-title"><?php echo esc_html__( 'Recent orders', 'paxig-shoes' ); ?></h2>
				<p><?php echo esc_html__( 'Your latest pairs and their delivery status.', 'paxig-shoes' ); ?></p>
			</div>
			<?php if (!empty($orders)) : ?>
				<a class="paxig-account-text-link" href="<?php echo esc_url($orders_url); ?>"><?php echo esc_html__( 'All orders', 'paxig-shoes' ); ?></a>
			<?php endif; ?>
		</header>

		<?php if (!empty($orders)) : ?>
			<ul class="paxig-account-order-list">
				<?php foreach ($orders as $order) :
					$items = $order->get_items();
					$first_item = $items ? reset($items) : false;
					$product = $first_item ? $first_item->get_product() : false;
					$product_name = $first_item ? $first_item->get_name() : __( 'Order items', 'paxig-shoes' );
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
								<strong><?php printf( esc_html__( 'Order #%s', 'paxig-shoes' ), esc_html($order->get_order_number()) ); ?></strong>
								<span><?php echo esc_html($order_date ? wc_format_datetime($order_date, get_option('date_format')) : ''); ?></span>
								<span class="paxig-account-order__item-name"><?php echo esc_html($product_name); ?><?php echo $product_count > 1 ? ' ' . esc_html( sprintf( __( '+%d more', 'paxig-shoes' ), (int)($product_count - 1) ) ) : ''; ?></span>
							</span>
							<span class="paxig-account-order__summary">
								<span class="paxig-account-status paxig-account-status--<?php echo esc_attr(sanitize_html_class($status)); ?>"><?php echo esc_html(wc_get_order_status_name($status)); ?></span>
								<strong><?php echo wp_kses_post($order->get_formatted_order_total()); ?></strong>
							</span>
							<span class="paxig-account-order__action"><?php echo esc_html__( 'View order', 'paxig-shoes' ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<div class="paxig-account-empty">
				<div>
					<h3><?php echo esc_html__( 'You haven’t ordered a pair yet.', 'paxig-shoes' ); ?></h3>
					<p><?php echo esc_html__( 'Once you place an order, you can follow its progress and see every item here.', 'paxig-shoes' ); ?></p>
				</div>
				<a class="button paxig-account-primary" href="<?php echo esc_url($shop_url); ?>"><?php echo esc_html__( 'Explore shoes', 'paxig-shoes' ); ?></a>
			</div>
		<?php endif; ?>
	</section>

	<div class="paxig-account-summary-grid">
		<section class="paxig-account-summary" aria-labelledby="paxig-account-details-title">
			<header class="paxig-account-section-heading">
				<div><h2 id="paxig-account-details-title"><?php echo esc_html__( 'Your details', 'paxig-shoes' ); ?></h2></div>
				<a class="paxig-account-text-link" href="<?php echo esc_url($profile_url); ?>"><?php echo esc_html__( 'Edit', 'paxig-shoes' ); ?></a>
			</header>
			<dl class="paxig-account-definition-list">
				<div><dt><?php echo esc_html__( 'Name', 'paxig-shoes' ); ?></dt><dd><?php echo esc_html(trim($customer->first_name . ' ' . $customer->last_name) ?: $customer->display_name); ?></dd></div>
				<div><dt><?php echo esc_html__( 'Email', 'paxig-shoes' ); ?></dt><dd><?php echo esc_html($customer->user_email); ?></dd></div>
				<?php if ($phone !== '') : ?><div><dt><?php echo esc_html__( 'Phone', 'paxig-shoes' ); ?></dt><dd><?php echo esc_html($phone); ?></dd></div><?php endif; ?>
			</dl>
		</section>

		<section class="paxig-account-summary" aria-labelledby="paxig-account-addresses-title">
			<header class="paxig-account-section-heading">
				<div><h2 id="paxig-account-addresses-title"><?php echo esc_html__( 'Address book', 'paxig-shoes' ); ?></h2></div>
				<a class="paxig-account-text-link" href="<?php echo esc_url($addresses_url); ?>"><?php echo esc_html__( 'Manage', 'paxig-shoes' ); ?></a>
			</header>
			<div class="paxig-account-address-preview">
				<h3><?php echo esc_html__( 'Shipping address', 'paxig-shoes' ); ?></h3>
				<?php if ($shipping_address) : ?>
					<address><?php echo wp_kses_post($shipping_address); ?></address>
				<?php else : ?>
					<p><?php echo esc_html__( 'No shipping address saved yet.', 'paxig-shoes' ); ?></p>
				<?php endif; ?>
				<?php if ($billing_address && $billing_address !== $shipping_address) : ?>
					<h3><?php echo esc_html__( 'Billing address', 'paxig-shoes' ); ?></h3>
					<address><?php echo wp_kses_post($billing_address); ?></address>
				<?php endif; ?>
			</div>
		</section>
	</div>
</div>
