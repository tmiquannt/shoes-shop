<?php
/**
 * Lost password confirmation text.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/lost-password-confirmation.php.
 *
 * @package WooCommerce\Templates
 * @version 3.9.0
 */

defined( 'ABSPATH' ) || exit;

paxig_render_auth_split_start();

$is_vi = function_exists( 'paxig_get_current_language' ) && paxig_get_current_language() === 'vi';
?>
<section class="paxig-auth-card vyre-auth-card" aria-labelledby="paxig-auth-title">
	<header class="paxig-auth-card-heading vyre-auth-card-heading">
		<span class="vyre-auth-eyebrow"><?php echo esc_html__( "ENTER THE WORLD OF PAXG'Shop", 'paxig-shoes' ); ?></span>
		<h2 id="paxig-auth-title" class="vyre-auth-title"><?php echo $is_vi ? 'KIỂM TRA HỘP THƯ.' : 'CHECK YOUR INBOX.'; ?></h2>
		<p class="vyre-auth-subtitle"><?php echo $is_vi ? 'Email đặt lại mật khẩu đã được gửi thành công.' : esc_html__( 'Password reset email has been sent.', 'woocommerce' ); ?></p>
	</header>

	<?php do_action( 'woocommerce_before_lost_password_confirmation_message' ); ?>

	<p class="vyre-auth-message">
		<?php echo esc_html( apply_filters( 'woocommerce_lost_password_confirmation_message', esc_html__( 'A password reset email has been sent to the email address on file for your account, but may take several minutes to show up in your inbox. Please wait at least 10 minutes before attempting another reset.', 'woocommerce' ) ) ); ?>
	</p>

	<?php do_action( 'woocommerce_after_lost_password_confirmation_message' ); ?>

	<p class="paxig-auth-switch vyre-auth-switch">
		<?php
		$switch_text = $is_vi
			? __( 'QUAY LẠI <a href="%s">ĐĂNG NHẬP ↗</a>', 'paxig-shoes' )
			: __( 'RETURN TO <a href="%s">SIGN IN ↗</a>', 'paxig-shoes' );
		echo paxig_icon_text(sprintf( $switch_text, esc_url( paxig_auth_switch_url() ) ));
		?>
	</p>
</section>
<?php
paxig_render_auth_split_end();
