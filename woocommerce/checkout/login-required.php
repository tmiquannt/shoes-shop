<?php
defined('ABSPATH') || exit;
do_action('woocommerce_before_checkout_form', $checkout);
?>
<section class="paxig-checkout-login" aria-labelledby="paxig-checkout-login-title">
    <h2 id="paxig-checkout-login-title"><?php echo esc_html__( 'CHECK OUT YOUR WAY', 'paxig-shoes' ); ?></h2>
    <p><?php echo esc_html__( 'Sign in to view orders linked to your account, or continue as a guest.', 'paxig-shoes' ); ?></p>
    <div class="paxig-checkout-login-actions">
        <a class="button" href="<?php echo esc_url(paxig_checkout_auth_url()); ?>"><?php echo esc_html__( 'SIGN IN', 'paxig-shoes' ); ?></a>
        <a class="button" href="<?php echo esc_url(wc_get_checkout_url()); ?>"><?php echo esc_html__( 'CONTINUE AS GUEST', 'paxig-shoes' ); ?></a>
    </div>
</section>
<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
