<?php
defined('ABSPATH') || exit;
do_action('woocommerce_before_checkout_form', $checkout);
?>
<section class="paxig-checkout-login" aria-labelledby="paxig-checkout-login-title">
    <h2 id="paxig-checkout-login-title"><?php echo esc_html__( 'SIGN IN TO CONTINUE', 'paxig-shoes' ); ?></h2>
    <p><?php echo esc_html__( 'You need an account to complete your order and manage order tracking, cancellation and returns.', 'paxig-shoes' ); ?></p>
    <div class="paxig-checkout-login-actions">
        <a class="button" href="<?php echo esc_url(paxig_checkout_auth_url()); ?>"><?php echo esc_html__( 'SIGN IN', 'paxig-shoes' ); ?></a>
        <a class="button" href="<?php echo esc_url(paxig_checkout_auth_url(true)); ?>"><?php echo esc_html__( 'CREATE ACCOUNT', 'paxig-shoes' ); ?></a>
    </div>
    <p><?php echo esc_html__( 'Your shopping bag will be kept while you sign in or create an account.', 'paxig-shoes' ); ?></p>
</section>
<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
