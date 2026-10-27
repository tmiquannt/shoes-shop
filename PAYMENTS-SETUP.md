# Checkout payment setup

WooCommerce owns the customer checkout and payment settings. The checkout offers the payment methods that are enabled and configured in WooCommerce:

- **Cash on delivery (COD):** WooCommerce's built-in gateway.
- **SePay:** the official SePay Gateway for WooCommerce plugin.
- **VNPAY:** the `plugins/paxig-vnpay` plugin in this repository.

Customers can choose COD or an online gateway at checkout. SePay and VNPAY appear only after their plugins are installed, enabled, and configured.

To present only these requested choices, enable COD, SePay, and VNPAY in **WooCommerce → Settings → Payments**, and disable any other methods such as Bank transfer (BACS) or cheque.

## Install the VNPAY gateway

1. Zip the `plugins/paxig-vnpay` folder, keeping `paxig-vnpay.php` at the root of that folder.
2. In WordPress, open **Plugins → Add New Plugin → Upload Plugin**, upload the zip, and activate **PAXIG VNPAY for WooCommerce**.
3. Open **WooCommerce → Settings → Payments → VNPAY**.
4. Leave the gateway disabled until the merchant's VNPAY credentials are ready. Configure the Sandbox terminal ID and secret key first, choose Sandbox, save, and enable the gateway.
5. Confirm the store currency is **VND**. The gateway hides itself for other currencies.
6. Register this IPN URL with VNPAY for the merchant account:

   `https://YOUR-STORE-DOMAIN/wp-json/paxig/v1/vnpay/ipn`

   The store must be publicly reachable over HTTPS so VNPAY can call the IPN endpoint. The customer return URL is generated automatically by the gateway.
7. Use the VNPAY-provided production payment URL when switching the environment to Production. The plugin accepts only HTTPS hosts under `vnpay.vn` or `vnpayment.vn`; it does not guess or silently substitute a production URL.

The customer return page only displays the signed result. An order is marked paid only after the signed IPN has passed signature, merchant ID, order reference, currency, and amount checks. Repeated IPNs are idempotent. The plugin does not implement VNPAY refunds.

## Enable COD

Open **WooCommerce → Settings → Payments → Cash on delivery**, enable COD, then configure its title, instructions, and shipping availability in WooCommerce.

## Install and configure SePay

Install [SePay Gateway for WooCommerce](https://wordpress.org/plugins/sepay-gateway/) from the WordPress plugin directory, then connect it to the merchant's SePay account and linked bank account. Configure the transfer prefix and webhook through the plugin settings. SePay's plugin handles displaying its payment instructions and notifying WooCommerce when a matching transfer arrives. Follow [SePay's WooCommerce integration guide](https://docs.sepay.vn/woocommerce.html).

SePay's documentation says its Test Mode does not support WooCommerce integration. Use the provider's documented merchant setup and follow its live-account requirements before accepting real transfers.

## Provider and WooCommerce references

- [VNPAY payment API v2.1.0](https://sandbox.vnpayment.vn/apis/docs/thanh-toan-pay/pay.html)
- [WooCommerce Payment Gateway API](https://developer.woocommerce.com/docs/features/payments/payment-gateway-api/)
- [WooCommerce Checkout Blocks payment method integration](https://developer.woocommerce.com/docs/block-development/extensible-blocks/cart-and-checkout-blocks/checkout-payment-methods/payment-method-integration)

## Go-live notes

- Do not enable a gateway with demo credentials or without registering and verifying its server callback.
- The current theme contains demo-store text and a demo notice controlled by the site's `paxig_ui_demo` option. Review the store content and demo setting before accepting real orders.
- This repository has no connected WordPress runtime or merchant credentials, so gateway activation, provider onboarding, and an end-to-end payment were not performed here.
