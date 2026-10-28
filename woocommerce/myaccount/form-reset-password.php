<?php
/**
 * Lost password reset form.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-reset-password.php.
 *
 * @package WooCommerce\Templates
 * @version 9.2.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_reset_password_form' );

$is_vi = function_exists( 'paxig_get_current_language' ) && paxig_get_current_language() === 'vi';
?>
<section class="paxig-auth-card vyre-auth-card" aria-labelledby="paxig-auth-title">
	<header class="paxig-auth-card-heading vyre-auth-card-heading">
		<span class="vyre-auth-eyebrow"><?php echo esc_html__( "ENTER THE WORLD OF PAXG'Shop", 'paxig-shoes' ); ?></span>
		<h2 id="paxig-auth-title" class="vyre-auth-title"><?php echo $is_vi ? 'MẬT KHẨU MỚI.' : 'NEW PASSWORD.'; ?></h2>
		<p class="vyre-auth-subtitle"><?php echo $is_vi ? 'Vui lòng nhập mật khẩu mới cho tài khoản của bạn bên dưới.' : esc_html( apply_filters( 'woocommerce_reset_password_message', __( 'Enter a new password below.', 'woocommerce' ) ) ); ?></p>
	</header>

	<form method="post" class="woocommerce-ResetPassword lost_reset_password">

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="password_1"><?php esc_html_e( 'New password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
			<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password_1" id="password_1" autocomplete="new-password" required aria-required="true" />
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="password_2"><?php esc_html_e( 'Re-enter new password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
			<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password_2" id="password_2" autocomplete="new-password" required aria-required="true" />
		</p>

		<input type="hidden" name="reset_key" value="<?php echo esc_attr( $args['key'] ); ?>" />
		<input type="hidden" name="reset_login" value="<?php echo esc_attr( $args['login'] ); ?>" />

		<div class="clear"></div>

		<?php do_action( 'woocommerce_resetpassword_form' ); ?>

		<p class="woocommerce-form-row form-row form-row-submit">
			<input type="hidden" name="wc_reset_password" value="true" />
			<button type="submit" class="woocommerce-Button button woocommerce-form-login__submit woocommerce-form-lost-password__submit<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" value="<?php esc_attr_e( 'Save', 'woocommerce' ); ?>">
				<span><?php echo $is_vi ? 'LƯU MẬT KHẨU' : esc_html__( 'Save', 'woocommerce' ); ?></span>
				<span aria-hidden="true">→</span>
			</button>
		</p>

		<?php wp_nonce_field( 'reset_password', 'woocommerce-reset-password-nonce' ); ?>

	</form>

	<p class="paxig-auth-switch vyre-auth-switch">
		<?php
		$switch_text = $is_vi
			? __( 'QUAY LẠI <a href="%s">ĐĂNG NHẬP ↗</a>', 'paxig-shoes' )
			: __( 'BACK TO <a href="%s">SIGN IN ↗</a>', 'paxig-shoes' );
		printf( $switch_text, esc_url( paxig_auth_switch_url() ) );
		?>
	</p>
</section>
<?php
do_action( 'woocommerce_after_reset_password_form' );
