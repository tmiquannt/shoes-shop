<?php
/** Gmail SMTP transport and storefront customer email verification. */
defined('ABSPATH') || exit;

function paxig_email_verification_setting($constant, $environment, $default = '') {
    if (defined($constant)) return trim((string) constant($constant));
    $value = getenv($environment);
    return false === $value ? $default : trim((string) $value);
}

function paxig_email_verification_smtp_config() {
    $username = paxig_email_verification_setting('PAXIG_SMTP_USERNAME', 'PAXIG_SMTP_USERNAME');
    $password = paxig_email_verification_setting('PAXIG_SMTP_APP_PASSWORD', 'PAXIG_SMTP_APP_PASSWORD');
    return [
        'host' => paxig_email_verification_setting('PAXIG_SMTP_HOST', 'PAXIG_SMTP_HOST', 'smtp.gmail.com'),
        'port' => absint(paxig_email_verification_setting('PAXIG_SMTP_PORT', 'PAXIG_SMTP_PORT', '587')),
        'encryption' => strtolower(paxig_email_verification_setting('PAXIG_SMTP_ENCRYPTION', 'PAXIG_SMTP_ENCRYPTION', 'tls')),
        'username' => $username,
        'password' => preg_replace('/\s+/', '', $password),
        'from_email' => paxig_email_verification_setting('PAXIG_SMTP_FROM_EMAIL', 'PAXIG_SMTP_FROM_EMAIL', $username),
        'from_name' => paxig_email_verification_setting('PAXIG_SMTP_FROM_NAME', 'PAXIG_SMTP_FROM_NAME', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES)),
    ];
}

function paxig_email_verification_smtp_is_configured() {
    $config = paxig_email_verification_smtp_config();
    return $config['host'] !== ''
        && $config['port'] > 0
        && in_array($config['encryption'], ['tls', 'ssl'], true)
        && $config['username'] !== ''
        && $config['password'] !== ''
        && is_email($config['from_email']);
}

function paxig_email_verification_configure_smtp($mailer) {
    if (!paxig_email_verification_smtp_is_configured()) return;
    $config = paxig_email_verification_smtp_config();

    $mailer->isSMTP();
    $mailer->Host = $config['host'];
    $mailer->Port = $config['port'];
    $mailer->SMTPAuth = true;
    $mailer->Username = $config['username'];
    $mailer->Password = $config['password'];
    $mailer->SMTPSecure = $config['encryption'];
    $mailer->SMTPAutoTLS = 'tls' === $config['encryption'];
    $mailer->Timeout = 15;
    $mailer->CharSet = 'UTF-8';
    $mailer->setFrom($config['from_email'], $config['from_name'], false);
}
add_action('phpmailer_init', 'paxig_email_verification_configure_smtp');

function paxig_email_verification_is_registration_request() {
    // WooCommerce processes this form at wp_loaded, before is_account_page()
    // can resolve the main query. Match its form fields and nonce selection.
    if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST)
        || !isset($_POST['register'], $_POST['email'])
        || !is_string($_POST['email'])) {
        return false;
    }

    $nonce = isset($_POST['woocommerce-register-nonce'])
        ? $_POST['woocommerce-register-nonce']
        : ($_POST['_wpnonce'] ?? '');

    return is_string($nonce)
        && (bool) wp_verify_nonce(wp_unslash($nonce), 'woocommerce-register');
}

/** Do not create an account that cannot receive its required activation link. */
add_filter('woocommerce_process_registration_errors', function($errors, $username, $password, $email) {
    if (paxig_email_verification_is_registration_request() && !paxig_email_verification_smtp_is_configured()) {
        $errors->add('paxig_email_verification_mail_unavailable', __( 'Email verification is temporarily unavailable. Please try again later.', 'paxig-shoes' ));
    }
    return $errors;
}, 10, 4);

/** The verification email replaces WooCommerce’s normal new-account email for this form. */
add_filter('woocommerce_email_enabled_customer_new_account', function($enabled) {
    return paxig_email_verification_is_registration_request() ? false : $enabled;
});

function paxig_email_verification_send($customer_id) {
    $user = get_userdata($customer_id);
    if (!$user || !paxig_email_verification_smtp_is_configured()) return false;

    try {
        $token = bin2hex(random_bytes(32));
    } catch (Throwable $error) {
        return false;
    }

    $expires = time() + DAY_IN_SECONDS;
    update_user_meta($customer_id, '_paxig_email_verification_token_hash', hash('sha256', $token));
    update_user_meta($customer_id, '_paxig_email_verification_expires_at', $expires);
    delete_user_meta($customer_id, '_paxig_email_verification_sent_at');

    $verification_url = add_query_arg([
        'action' => 'paxig_verify_email',
        'user_id' => $customer_id,
        'token' => $token,
    ], admin_url('admin-post.php'));
    $name = trim($user->first_name . ' ' . $user->last_name);
    $greeting = $name !== '' ? 'Hello ' . $name . ',' : 'Hello,';
    $subject = sprintf('Verify your email address for %s', wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES));
    $message = $greeting . "\n\n"
        . "Thanks for creating an account. Verify your email address using this link:\n\n"
        . $verification_url . "\n\n"
        . "This link can be used once and expires in 24 hours. If you did not create this account, you can ignore this email.\n";

    if (!wp_mail($user->user_email, $subject, $message)) return false;
    update_user_meta($customer_id, '_paxig_email_verification_sent_at', time());
    return true;
}

/** Create a pending account and send the one-time activation link. */
add_action('woocommerce_created_customer', function($customer_id) {
    if (!paxig_email_verification_is_registration_request()) return;

    update_user_meta($customer_id, '_paxig_email_verification_required', 'yes');
    update_user_meta($customer_id, '_paxig_email_verified', 'no');

    $fallback = wc_get_page_permalink('myaccount');
    $redirect = isset($_POST['redirect_to']) && is_string($_POST['redirect_to'])
        ? wp_unslash($_POST['redirect_to'])
        : $fallback;
    update_user_meta($customer_id, '_paxig_email_verification_redirect', wp_validate_redirect($redirect, $fallback));

    paxig_email_verification_send($customer_id);
}, 30, 1);

/** Keep WooCommerce from authenticating a newly registered account before verification. */
add_filter('woocommerce_registration_auth_new_customer', function($authenticate, $customer_id) {
    if (get_user_meta($customer_id, '_paxig_email_verification_required', true) === 'yes'
        && get_user_meta($customer_id, '_paxig_email_verified', true) !== 'yes') {
        return false;
    }
    return $authenticate;
}, 10, 2);

/** Redirect after account creation so refreshing the page does not submit the form twice. */
add_action('woocommerce_created_customer', function($customer_id) {
    if (!paxig_email_verification_is_registration_request()) return;
    $status = get_user_meta($customer_id, '_paxig_email_verification_sent_at', true) ? 'sent' : 'delivery_failed';
    $url = add_query_arg('paxig_email_verification', $status, wc_get_page_permalink('myaccount'));
    wp_safe_redirect($url);
    exit;
}, 9999, 1);

/** Block password login until the customer follows their verification link. */
add_filter('wp_authenticate_user', function($user) {
    if (!($user instanceof WP_User)
        || get_user_meta($user->ID, '_paxig_email_verification_required', true) !== 'yes'
        || get_user_meta($user->ID, '_paxig_email_verified', true) === 'yes') {
        return $user;
    }
    return new WP_Error('paxig_email_not_verified', __( 'Please verify your email before logging in. Use the resend form on this page if you need another link.', 'paxig-shoes' ));
}, 20, 1);

function paxig_email_verification_redirect($status, $url = '') {
    $fallback = wc_get_page_permalink('myaccount');
    $url = wp_validate_redirect($url, $fallback);
    wp_safe_redirect(add_query_arg('paxig_email_verification', sanitize_key($status), $url));
    exit;
}

function paxig_email_verification_handle_link() {
    nocache_headers();
    $customer_id = isset($_GET['user_id']) ? absint($_GET['user_id']) : 0;
    $token = isset($_GET['token']) && is_string($_GET['token']) ? wp_unslash($_GET['token']) : '';
    $user = $customer_id ? get_userdata($customer_id) : false;

    if (!$user || get_user_meta($customer_id, '_paxig_email_verification_required', true) !== 'yes') {
        paxig_email_verification_redirect('invalid');
    }
    if (get_user_meta($customer_id, '_paxig_email_verified', true) === 'yes') {
        paxig_email_verification_redirect('already_verified');
    }

    $stored_hash = (string) get_user_meta($customer_id, '_paxig_email_verification_token_hash', true);
    $expires = (int) get_user_meta($customer_id, '_paxig_email_verification_expires_at', true);
    if ($expires < time()) {
        delete_user_meta($customer_id, '_paxig_email_verification_token_hash');
        delete_user_meta($customer_id, '_paxig_email_verification_expires_at');
        paxig_email_verification_redirect('expired');
    }
    if (preg_match('/^[a-f0-9]{64}$/', $token) !== 1 || $stored_hash === '' || !hash_equals($stored_hash, hash('sha256', $token))) {
        paxig_email_verification_redirect('invalid');
    }

    update_user_meta($customer_id, '_paxig_email_verified', 'yes');
    delete_user_meta($customer_id, '_paxig_email_verification_token_hash');
    delete_user_meta($customer_id, '_paxig_email_verification_expires_at');
    delete_user_meta($customer_id, '_paxig_email_verification_sent_at');
    $redirect = (string) get_user_meta($customer_id, '_paxig_email_verification_redirect', true);
    delete_user_meta($customer_id, '_paxig_email_verification_redirect');

    wc_set_customer_auth_cookie($customer_id);
    paxig_email_verification_redirect('verified', $redirect);
}
add_action('admin_post_nopriv_paxig_verify_email', 'paxig_email_verification_handle_link');
add_action('admin_post_paxig_verify_email', 'paxig_email_verification_handle_link');

function paxig_email_verification_handle_resend() {
    check_admin_referer('paxig_resend_email_verification', 'paxig_resend_email_verification_nonce');
    if (!paxig_email_verification_smtp_is_configured()) {
        paxig_email_verification_redirect('smtp_unconfigured');
    }

    $email = isset($_POST['email']) && is_string($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    if (is_email($email)) {
        $user = get_user_by('email', $email);
        if ($user
            && get_user_meta($user->ID, '_paxig_email_verification_required', true) === 'yes'
            && get_user_meta($user->ID, '_paxig_email_verified', true) !== 'yes') {
            $rate_key = 'paxig_email_resend_' . substr(hash_hmac('sha256', strtolower($email), wp_salt('auth')), 0, 40);
            if (!get_transient($rate_key)) {
                set_transient($rate_key, 1, MINUTE_IN_SECONDS);
                paxig_email_verification_send($user->ID);
            }
        }
    }

    // Keep the response identical whether the address exists or has a pending account.
    paxig_email_verification_redirect('resend_requested');
}
add_action('admin_post_nopriv_paxig_resend_email_verification', 'paxig_email_verification_handle_resend');
add_action('admin_post_paxig_resend_email_verification', 'paxig_email_verification_handle_resend');

/** Turn one-time redirect flags into WooCommerce notices. */
add_action('wp', function() {
    if (is_admin() || !function_exists('wc_add_notice') || !isset($_GET['paxig_email_verification']) || !is_string($_GET['paxig_email_verification'])) return;
    $status = sanitize_key(wp_unslash($_GET['paxig_email_verification']));
    $messages = [
        'sent' => ['success', __( 'Account created. Check your inbox or spam folder for the verification link. It expires in 24 hours.', 'paxig-shoes' )],
        'delivery_failed' => ['error', __( 'Your account was created, but the verification email could not be sent. Check the SMTP settings and use the resend form.', 'paxig-shoes' )],
        'verified' => ['success', __( 'Your email is verified. Your account is ready.', 'paxig-shoes' )],
        'already_verified' => ['notice', __( 'This email address has already been verified.', 'paxig-shoes' )],
        'expired' => ['error', __( 'That verification link has expired. Use the resend form to get a new one.', 'paxig-shoes' )],
        'invalid' => ['error', __( 'That verification link is invalid or has already been used.', 'paxig-shoes' )],
        'resend_requested' => ['notice', __( 'If an unverified account exists for that email, a new verification link has been sent.', 'paxig-shoes' )],
        'smtp_unconfigured' => ['error', __( 'Google SMTP is not configured yet, so the verification email could not be sent.', 'paxig-shoes' )],
    ];
    if (isset($messages[$status])) wc_add_notice($messages[$status][1], $messages[$status][0]);
}, 20);
