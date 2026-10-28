<?php
/** Guest-only presentation; WooCommerce remains the only auth processor. */
defined('ABSPATH') || exit;
function paxig_auth_page() {
    return function_exists('is_account_page') && is_account_page() && !is_user_logged_in();
}
function paxig_auth_mode() {
    if(!paxig_auth_page())return '';
    if((function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('lost-password')) || isset($_GET['lost-password'])) return 'lost-password';
    if(isset($_POST['login']))return 'login';
    $requested=isset($_GET['action']) && is_string($_GET['action']) && wp_unslash($_GET['action'])==='register';
    return get_option('woocommerce_enable_myaccount_registration')==='yes' && ($requested || isset($_POST['register'])) ? 'register' : 'login';
}
add_filter('body_class',function($classes){
    if(paxig_auth_page()){
        $mode = paxig_auth_mode();
        $classes[]='paxig-auth';
        $classes[]='paxig-auth-'.$mode;
        if($mode === 'lost-password') {
            $classes[]='paxig-auth-reset';
        }
    }
    return $classes;
});

function paxig_render_auth_split_start() {
    $mode = paxig_auth_mode();
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    if ($mode === 'register') {
        $step_num = $is_vi ? "02 / GIA NHẬP PAXG'Shop" : "02 / JOIN PAXG'Shop";
        $path_text = $is_vi ? "PAXG'Shop / ĐĂNG KÝ" : "PAXG'Shop / REGISTER";
    } elseif ($mode === 'lost-password') {
        $step_num = $is_vi ? "03 / QUÊN MẬT KHẨU" : "03 / FORGOT PASSWORD";
        $path_text = $is_vi ? "PAXG'Shop / QUÊN MẬT KHẨU" : "PAXG'Shop / FORGOT PASSWORD";
    } else {
        $step_num = $is_vi ? "01 / TÀI KHOẢN" : "01 / ACCOUNT";
        $path_text = $is_vi ? "PAXG'Shop / TÀI KHOẢN" : "PAXG'Shop / ACCOUNT";
    }
    $hero_img = esc_url(get_template_directory_uri() . '/assets/images/vyre-hero-shoe.png');
    $store_url = esc_url(home_url('/'));
    ?>
    <div class="vyre-auth-split-wrapper">
        <div class="vyre-auth-split-left" style="background-image: url('<?php echo $hero_img; ?>');">
            <div class="vyre-auth-split-overlay"></div>
            <div class="vyre-auth-left-brand">
                <a href="<?php echo $store_url; ?>" class="vyre-auth-left-logo" aria-label="<?php echo esc_attr__( "PAXG'Shop", 'paxig-shoes' ); ?>">
                    <img class="paxig-brand__mark" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/paxg-shop-symbol-header.png' ); ?>" width="176" height="166" alt="" aria-hidden="true">
                    <span class="paxig-brand__wordmark"><span class="paxig-brand__wordmark-primary">PAXG</span><span class="paxig-brand__wordmark-shop">'Shop</span></span>
                </a>
            </div>
            <div class="vyre-auth-left-content">
                <span class="vyre-auth-left-step"><?php echo esc_html($step_num); ?></span>
                <h1 class="vyre-auth-left-headline">BUILT<br>DIFFERENT.</h1>
                <p class="vyre-auth-left-desc">
                    <?php if ($is_vi) : ?>
                        GIÀY THỂ THAO CHO MỘT TƯƠNG LAI RỰC RỠ HƠN.<br>
                        VĂN HOÁ THÚC ĐẨY CON NGƯỜI TIẾN BƯỚC.
                    <?php else : ?>
                        SNEAKERS FOR A BRIGHTER TOMORROW.<br>
                        CULTURE MOVES PEOPLE FORWARD.
                    <?php endif; ?>
                </p>
                <div class="vyre-auth-left-dots" aria-hidden="true">
                    <span class="vyre-dot <?php echo $mode === 'login' ? 'is-active' : ''; ?>"></span>
                    <span class="vyre-dot <?php echo $mode === 'register' ? 'is-active' : ''; ?>"></span>
                    <span class="vyre-dot <?php echo $mode === 'lost-password' ? 'is-active' : ''; ?>"></span>
                </div>
            </div>
        </div>
        <div class="vyre-auth-split-right">
            <header class="vyre-auth-right-topbar">
                <span class="vyre-auth-path"><?php echo esc_html($path_text); ?></span>
                <a href="<?php echo $store_url; ?>" class="vyre-auth-back-link"><?php echo $is_vi ? 'QUAY LẠI CỬA HÀNG ↗' : 'BACK TO STORE ↗'; ?></a>
            </header>
            <div class="vyre-auth-form-inner">
    <?php
}

function paxig_render_auth_split_end() {
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    ?>
            </div>
            <footer class="vyre-auth-right-footer">
                <span>© PAXG'Shop <?php echo esc_html( wp_date('Y') ); ?></span>
                <div class="vyre-auth-footer-links">
                    <a href="<?php echo esc_url(home_url('/chinh-sach-bao-mat/')); ?>"><?php echo $is_vi ? 'CHÍNH SÁCH BẢO MẬT' : 'PRIVACY'; ?></a>
                    <a href="<?php echo esc_url(home_url('/dieu-khoan/')); ?>"><?php echo $is_vi ? 'ĐIỀU KHOẢN' : 'TERMS'; ?></a>
                    <a href="<?php echo esc_url(home_url('/huong-dan/')); ?>"><?php echo $is_vi ? 'TRỢ GIÚP' : 'HELP'; ?></a>
                </div>
            </footer>
        </div>
    </div>
    <?php
}

add_action('woocommerce_before_customer_login_form', 'paxig_render_auth_split_start', 1);
add_action('woocommerce_after_customer_login_form', 'paxig_render_auth_split_end', 999);

add_action('woocommerce_before_lost_password_form', 'paxig_render_auth_split_start', 1);
add_action('woocommerce_after_lost_password_form', 'paxig_render_auth_split_end', 999);

add_action('woocommerce_before_reset_password_form', 'paxig_render_auth_split_start', 1);
add_action('woocommerce_after_reset_password_form', 'paxig_render_auth_split_end', 999);

add_action('wp_enqueue_scripts',function(){
    if(!paxig_auth_page())return;
    wp_enqueue_style('paxig-auth',get_template_directory_uri().'/assets/css/auth-ui.css',['paxig-store', 'paxig-style-3'],filemtime(get_template_directory().'/assets/css/auth-ui.css'));
},999);

/** Use the password entered on the storefront registration form. */
add_filter('pre_option_woocommerce_registration_generate_password',function($value){
    if(is_admin() || !function_exists('is_account_page') || !is_account_page())return $value;
    $register_requested=(isset($_GET['action']) && is_string($_GET['action']) && wp_unslash($_GET['action'])==='register') || isset($_POST['register']);
    return $register_requested ? 'no' : $value;
});

/** Validate required customer details before WooCommerce creates the account. */
add_filter('woocommerce_registration_errors',function($errors,$username,$email){
    if(!is_account_page() || !isset($_POST['register']))return $errors;

    $first_name=isset($_POST['first_name']) && is_string($_POST['first_name']) ? trim(wc_clean(wp_unslash($_POST['first_name']))) : '';
    $last_name=isset($_POST['last_name']) && is_string($_POST['last_name']) ? trim(wc_clean(wp_unslash($_POST['last_name']))) : '';
    $phone=isset($_POST['phone']) && is_string($_POST['phone']) ? wc_sanitize_phone_number(wp_unslash($_POST['phone'])) : '';
    $password=isset($_POST['password']) && is_string($_POST['password']) ? wp_unslash($_POST['password']) : '';
    $password_confirm=isset($_POST['password_confirm']) && is_string($_POST['password_confirm']) ? wp_unslash($_POST['password_confirm']) : '';

    if($first_name==='')$errors->add('paxig_registration_first_name', __( 'Please enter your first name.', 'paxig-shoes' ));
    if($last_name==='')$errors->add('paxig_registration_last_name', __( 'Please enter your last name.', 'paxig-shoes' ));
    if($phone==='')$errors->add('paxig_registration_phone', __( 'Please enter your phone number.', 'paxig-shoes' ));
    if($password==='')$errors->add('paxig_registration_password', __( 'Please create a password.', 'paxig-shoes' ));
    elseif($password!==$password_confirm)$errors->add('paxig_registration_password_confirm', __( 'Your passwords do not match.', 'paxig-shoes' ));

    return $errors;
},10,3);

/** Include the submitted name before WooCommerce sends the new-account email. */
add_filter('woocommerce_new_customer_data',function($customer_data){
    if(!is_account_page() || !isset($_POST['register']))return $customer_data;
    $customer_data['first_name']=isset($_POST['first_name']) && is_string($_POST['first_name']) ? wc_clean(wp_unslash($_POST['first_name'])) : '';
    $customer_data['last_name']=isset($_POST['last_name']) && is_string($_POST['last_name']) ? wc_clean(wp_unslash($_POST['last_name'])) : '';
    return $customer_data;
});

/** Save the customer details WooCommerce uses to prefill checkout. */
add_action('woocommerce_created_customer',function($customer_id){
    if(!is_account_page() || !isset($_POST['register']))return;
    $first_name=isset($_POST['first_name']) && is_string($_POST['first_name']) ? wc_clean(wp_unslash($_POST['first_name'])) : '';
    $last_name=isset($_POST['last_name']) && is_string($_POST['last_name']) ? wc_clean(wp_unslash($_POST['last_name'])) : '';
    $phone=isset($_POST['phone']) && is_string($_POST['phone']) ? wc_sanitize_phone_number(wp_unslash($_POST['phone'])) : '';
    update_user_meta($customer_id,'billing_first_name',$first_name);
    update_user_meta($customer_id,'billing_last_name',$last_name);
    update_user_meta($customer_id,'billing_phone',$phone);
},20,1);
