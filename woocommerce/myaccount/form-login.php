<?php
/**
 * Single-view presentation of the native WooCommerce Login Form.
 * Field/hook/nonce contract retained from WooCommerce template9.9.0.
 * @package WooCommerce\Templates
 * @version 9.9.0
 */
defined('ABSPATH') || exit;
$registration_enabled = 'yes' === get_option('woocommerce_enable_myaccount_registration');
$register_view = $registration_enabled && paxig_auth_mode() === 'register';
do_action('woocommerce_before_customer_login_form');
?>
<section class="paxig-auth-card vyre-auth-card" aria-labelledby="paxig-auth-title">
<header class="paxig-auth-card-heading vyre-auth-card-heading">
	<span class="vyre-auth-eyebrow"><?php echo esc_html__( "ENTER THE WORLD OF PAXG'Shop", 'paxig-shoes' ); ?></span>
	<h2 id="paxig-auth-title" class="vyre-auth-title"><?php echo $register_view ? esc_html__( 'JOIN THE MOVEMENT', 'paxig-shoes' ) : esc_html__( 'WELCOME BACK', 'paxig-shoes' ); ?></h2>
	<p class="vyre-auth-subtitle"><?php echo $register_view ? esc_html__( 'Create an account for faster checkout, saved styles and exclusive releases.', 'paxig-shoes' ) : esc_html__( 'Sign in to your account. Your favorites, orders and next moves are waiting.', 'paxig-shoes' ); ?></p>
</header>
<?php if ($register_view) : ?>
		<form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >

			<?php do_action( 'woocommerce_register_form_start' ); ?>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="reg_first_name"><?php echo esc_html__( 'First name', 'paxig-shoes' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="first_name" id="reg_first_name" autocomplete="given-name" value="<?php echo isset( $_POST['first_name'] ) && is_string( $_POST['first_name'] ) ? esc_attr( wp_unslash( $_POST['first_name'] ) ) : ''; ?>" required aria-required="true" />
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="reg_last_name"><?php echo esc_html__( 'Last name', 'paxig-shoes' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="last_name" id="reg_last_name" autocomplete="family-name" value="<?php echo isset( $_POST['last_name'] ) && is_string( $_POST['last_name'] ) ? esc_attr( wp_unslash( $_POST['last_name'] ) ) : ''; ?>" required aria-required="true" />
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
				<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine ?>
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="reg_phone"><?php echo esc_html__( 'Phone', 'paxig-shoes' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
				<input type="tel" class="woocommerce-Input woocommerce-Input--text input-text" name="phone" id="reg_phone" autocomplete="tel" value="<?php echo isset( $_POST['phone'] ) && is_string( $_POST['phone'] ) ? esc_attr( wp_unslash( $_POST['phone'] ) ) : ''; ?>" required aria-required="true" />
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
				<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" required aria-required="true" />
			</p>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="reg_password_confirm"><?php echo esc_html__( 'Confirm password', 'paxig-shoes' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
				<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password_confirm" id="reg_password_confirm" autocomplete="new-password" required aria-required="true" />
			</p>

			<?php do_action( 'woocommerce_register_form' ); ?>

			<p class="woocommerce-form-row form-row">
				<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
				<button type="submit" class="woocommerce-Button woocommerce-button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?> woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>">
					<span><?php echo esc_html__( 'CREATE ACCOUNT', 'paxig-shoes' ); ?></span>
					<?php echo paxig_icon('arrow-right', 18); ?>
				</button>
			</p>

			<?php do_action( 'woocommerce_register_form_end' ); ?>

		</form>
<p class="paxig-auth-switch vyre-auth-switch"><?php echo paxig_icon_text(sprintf( __( 'ALREADY A MEMBER? <a href="%s">SIGN IN ↗</a>', 'paxig-shoes' ), esc_url(paxig_auth_switch_url()) )); ?></p>
<?php else : ?>
		<form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>

			<?php do_action( 'woocommerce_login_form_start' ); ?>

			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="username"><?php esc_html_e( 'Username or email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" /><?php // @codingStandardsIgnoreLine ?>
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
				<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true" />
			</p>

			<?php do_action( 'woocommerce_login_form' ); ?>

			<div class="vyre-auth-options-row">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php esc_html_e( 'Keep me signed in', 'paxig-shoes' ); ?></span>
				</label>
				<p class="woocommerce-LostPassword lost_password">
					<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Forgot password?', 'paxig-shoes' ); ?></a>
				</p>
			</div>
			<p class="form-row form-row-submit">
				<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
				<button type="submit" class="woocommerce-button button woocommerce-form-login__submit<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>">
					<span><?php echo esc_html__( 'SIGN IN', 'paxig-shoes' ); ?></span>
					<?php echo paxig_icon('arrow-right', 18); ?>
				</button>
			</p>

			<?php do_action( 'woocommerce_login_form_end' ); ?>

		</form>
<?php if ($registration_enabled) : ?>
<p class="paxig-auth-switch vyre-auth-switch"><?php echo paxig_icon_text(sprintf( __( "NEW TO PAXG'Shop? <a href=\"%s\">CREATE AN ACCOUNT ↗</a>", 'paxig-shoes' ), esc_url(paxig_auth_switch_url(true)) )); ?></p>
<?php endif; ?>
<?php endif; ?>
</section>
<?php do_action('woocommerce_after_customer_login_form'); ?>
