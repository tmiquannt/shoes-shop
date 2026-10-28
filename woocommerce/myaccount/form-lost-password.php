<?php
/**
 * Lost password form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-lost-password.php.
 *
 * @package WooCommerce\Templates
 * @version 9.2.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_lost_password_form' );

$is_vi = function_exists( 'paxig_get_current_language' ) && paxig_get_current_language() === 'vi';
?>
<section class="paxig-auth-card vyre-auth-card" aria-labelledby="paxig-auth-title">
	<header class="paxig-auth-card-heading vyre-auth-card-heading">
		<span class="vyre-auth-eyebrow"><?php echo esc_html__( "ENTER THE WORLD OF PAXG'Shop", 'paxig-shoes' ); ?></span>
		<h2 id="paxig-auth-title" class="vyre-auth-title"><?php echo $is_vi ? 'KHÔI PHỤC MẬT KHẨU.' : 'RESET PASSWORD.'; ?></h2>
		<p class="vyre-auth-subtitle"><?php echo $is_vi ? 'Nhập tên đăng nhập hoặc email của bạn. Bạn sẽ nhận được liên kết tạo mật khẩu mới qua email.' : esc_html( apply_filters( 'woocommerce_lost_password_message', __( 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'woocommerce' ) ) ); ?></p>
	</header>

	<form method="post" class="woocommerce-ResetPassword lost_reset_password">

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="user_login"><?php esc_html_e( 'Username or email', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
			<input class="woocommerce-Input woocommerce-Input--text input-text" type="text" name="user_login" id="user_login" autocomplete="username" required aria-required="true" />
		</p>

		<div class="clear"></div>

		<?php do_action( 'woocommerce_lostpassword_form' ); ?>

		<p class="woocommerce-form-row form-row form-row-submit">
			<input type="hidden" name="wc_reset_password" value="true" />
			<button type="submit" class="woocommerce-Button button woocommerce-form-login__submit woocommerce-form-lost-password__submit<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" value="<?php esc_attr_e( 'Reset password', 'woocommerce' ); ?>">
				<span><?php echo $is_vi ? 'GỬI LIÊN KẾT ĐẶT LẠI' : esc_html__( 'Reset password', 'woocommerce' ); ?></span>
				<?php echo paxig_icon('arrow-right', 18); ?>
			</button>
		</p>

		<?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>

	</form>

	<p class="paxig-auth-switch vyre-auth-switch">
		<?php
		$switch_text = $is_vi
			? __( 'BẠN ĐÃ NHỚ MẬT KHẨU? <a href="%s">ĐĂNG NHẬP ↗</a>', 'paxig-shoes' )
			: __( 'REMEMBER YOUR PASSWORD? <a href="%s">SIGN IN ↗</a>', 'paxig-shoes' );
		echo paxig_icon_text(sprintf( $switch_text, esc_url( paxig_auth_switch_url() ) ));
		?>
	</p>
</section>
<?php
do_action( 'woocommerce_after_lost_password_form' );
