<?php
defined('ABSPATH') || exit;
do_action('woocommerce_before_checkout_form', $checkout);
?>
<section class="paxig-checkout-login" aria-labelledby="paxig-checkout-login-title">
    <h2 id="paxig-checkout-login-title">SIGN IN TO CONTINUE</h2>
    <p>You need an account to complete your order and manage order tracking, cancellation and returns.</p>
    <div class="paxig-checkout-login-actions">
        <a class="button" href="<?php echo esc_url(paxig_checkout_auth_url()); ?>">SIGN IN</a>
        <a class="button" href="<?php echo esc_url(paxig_checkout_auth_url(true)); ?>">CREATE ACCOUNT</a>
    </div>
    <p>Your shopping bag will be kept while you sign in or create an account.</p>
</section>
<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
