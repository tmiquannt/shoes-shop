<?php
/** Guest-only presentation; WooCommerce remains the only auth processor. */
defined('ABSPATH') || exit;
function paxig_auth_page() {
    return function_exists('is_account_page') && is_account_page() && !is_user_logged_in() && !is_wc_endpoint_url('lost-password');
}
function paxig_auth_mode() {
    if(!paxig_auth_page())return '';
    if(isset($_POST['login']))return 'login';
    $requested=isset($_GET['action']) && is_string($_GET['action']) && wp_unslash($_GET['action'])==='register';
    return get_option('woocommerce_enable_myaccount_registration')==='yes' && ($requested || isset($_POST['register'])) ? 'register' : 'login';
}
add_filter('body_class',function($classes){
    if(paxig_auth_page()){$classes[]='paxig-auth';$classes[]='paxig-auth-'.paxig_auth_mode();}
    elseif(function_exists('is_account_page') && is_account_page() && !is_user_logged_in() && is_wc_endpoint_url('lost-password'))$classes[]='paxig-auth-reset';
    return $classes;
});
add_action('wp_enqueue_scripts',function(){
    if(!paxig_auth_page() && !(is_account_page() && !is_user_logged_in() && is_wc_endpoint_url('lost-password')))return;
    wp_enqueue_style('paxig-auth',get_template_directory_uri().'/assets/css/auth-ui.css',['paxig-store'],filemtime(get_template_directory().'/assets/css/auth-ui.css'));
},110);
