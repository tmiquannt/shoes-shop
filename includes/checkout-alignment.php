<?php
/** Registered storefront checkout. WooCommerce owns auth, cart and order processing. */
defined('ABSPATH') || exit;

function paxig_shipping_summary() {
    foreach (WC_Shipping_Zones::get_zones() as $zone) {
        $vn = false;
        foreach ($zone['zone_locations'] as $location) if ($location->type==='country' && $location->code==='VN') $vn=true;
        if (!$vn) continue;
        $flat=null; $free=null;
        foreach ($zone['shipping_methods'] as $method) {
            if ($method->enabled!=='yes') continue;
            if ($method->id==='flat_rate' && is_numeric($method->get_option('cost'))) $flat=(float)$method->get_option('cost');
            if ($method->id==='free_shipping' && $method->get_option('requires')==='min_amount' && $method->get_option('ignore_discounts')==='no') $free=(float)$method->get_option('min_amount');
        }
        if ($flat!==null && $free!==null) return 'Shipping in Vietnam: standard shipping '.number_format($flat,0,'.',',').' VND per order; free shipping from '.number_format($free,0,'.',',').' VND after discounts. Available rates are confirmed at checkout.';
    }
    return 'Available shipping methods and charges are calculated at checkout for your address.';
}

// Support guest purchase while retaining secure account association for logged-in customers.
add_action('woocommerce_checkout_create_order', function ($order) {
    if (is_user_logged_in() && get_current_user_id() > 0) {
        // Authenticated storefront orders belong to the authenticated actor.
        $order->set_customer_id(get_current_user_id());
    }
}, 1000);

// Enforce guest-capable checkout: registration is never required to place an order.
add_filter('woocommerce_checkout_registration_required', '__return_false', 1000);
add_filter('option_woocommerce_enable_guest_checkout', function () { return 'yes'; }, 1000);

// Disable checkout-level account creation: account creation is deferred post-purchase.
// This preserves existing sign-in and independent My Account registration.
add_filter('woocommerce_checkout_registration_enabled', '__return_false', 1000);
add_filter('option_woocommerce_enable_signup_and_login_from_checkout', function () { return 'no'; }, 1000);

// Defense-in-depth: strip any forged createaccount or account credentials from posted checkout data.
add_filter('woocommerce_checkout_posted_data', function ($data) {
    if (is_array($data)) {
        $data['createaccount'] = 0;
        unset($data['account_password'], $data['account_username']);
    }
    return $data;
}, 1000);

add_action('woocommerce_before_checkout_form', function () {
    if (!is_user_logged_in() && function_exists('wc_get_page_permalink')) {
        echo '<div class="paxig-checkout-guest-prompt"><p>Already have an account? <a href="' . esc_url(paxig_checkout_auth_url()) . '">Sign in</a> for faster checkout and order tracking, or continue as guest below.</p></div>';
    }
}, 10);

function paxig_checkout_return_requested() {
    $value = $_POST['redirect_to'] ?? $_GET['redirect_to'] ?? '';
    return is_string($value) && untrailingslashit(wp_unslash($value)) === untrailingslashit(wc_get_checkout_url());
}
function paxig_checkout_auth_url($register = false) {
    $args = ['redirect_to'=>wc_get_checkout_url()];
    if ($register) $args['action'] = 'register';
    return add_query_arg($args, wc_get_page_permalink('myaccount'));
}
function paxig_auth_switch_url($register = false) {
    if (paxig_checkout_return_requested()) return paxig_checkout_auth_url($register);
    return $register ? add_query_arg('action','register',wc_get_page_permalink('myaccount')) : wc_get_page_permalink('myaccount');
}
foreach (['woocommerce_login_form_start','woocommerce_register_form_start'] as $hook) {
    add_action($hook, function () {
        if (!paxig_checkout_return_requested()) return;
        echo '<input type="hidden" name="redirect_to" value="'.esc_attr(wc_get_checkout_url()).'"><input type="hidden" name="redirect" value="'.esc_attr(wc_get_checkout_url()).'">';
    });
}
foreach (['woocommerce_login_redirect','woocommerce_registration_redirect'] as $hook) {
    add_filter($hook, function ($url) {return paxig_checkout_return_requested() ? wc_get_checkout_url() : $url;}, 1000);
}


// Native Woo status, fulfillment and payment are independent read models.
add_filter('woocommerce_my_account_my_orders_columns', function ($columns) {
    if (isset($columns['order-status'])) $columns['order-status']='Order Status (WooCommerce)';
    return $columns;
}, 1100);
add_action('woocommerce_order_details_after_order_table', function ($order) {
    if (!is_account_page() || !is_user_logged_in() || (int)$order->get_customer_id() !== get_current_user_id()) return;
    echo '<section class="paxig-native-order-status"><h2>Order Status (WooCommerce)</h2><p>'.esc_html(wc_get_order_status_name($order->get_status())).'</p><p>Fulfillment and payment are tracked separately below.</p></section>';
}, 9);

// Do not advertise unsupported demo categories or sales-ranking semantics.
add_filter('wp_nav_menu_objects', function ($items) {
    if (is_admin()) return $items;
    $items = array_filter($items, function ($item) {return strpos($item->url, 'paxig_original_category=') === false;});
    foreach ($items as &$item) {
        $item=clone $item;
        if (strpos($item->url,'paxig_best_sellers=1') !== false) $item->title='Curated Picks';
        if (strpos($item->url,'orderby=popularity') !== false && $item->title==='Trending') $item->title='Popular Products';
    }
    return array_values($items);
}, 1100);
add_filter('gettext', function ($translated,$text,$domain) {
    if (!is_admin() && $domain==='woocommerce' && $text==='Best Seller') return 'Curated Pick';
    return $translated;
}, 1100, 3);
