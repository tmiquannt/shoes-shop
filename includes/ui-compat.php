<?php
defined('ABSPATH')||exit;

add_filter('woocommerce_account_menu_items',function($items){
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    $labels = $is_vi
        ? ['dashboard'=>'Tổng quan','orders'=>'Đơn hàng','downloads'=>'Tải xuống','edit-address'=>'Địa chỉ','edit-account'=>'Chi tiết tài khoản','customer-logout'=>'Đăng xuất']
        : ['dashboard'=>'Dashboard','orders'=>'Orders','downloads'=>'Downloads','edit-address'=>'Addresses','edit-account'=>'Account Details','customer-logout'=>'Log Out'];
    foreach($items as $k=>$v)$items[$k]=$labels[$k]??$v;
    return $items;
});

add_filter('gettext',function($translated,$text,$domain){
    if(is_admin()||!in_array($domain,['woocommerce','default'],true))return $translated;
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    if ($is_vi) {
        $vi_map = [
            'Home'=>'Trang chủ','Cart'=>'Giỏ hàng','Checkout'=>'Thanh toán','My account'=>'Tài khoản của tôi','Description'=>'Mô tả','Additional information'=>'Thông tin bổ sung','Reviews'=>'Đánh giá','Choose an option'=>'Chọn kích cỡ','Clear'=>'Xóa lựa chọn','Quantity'=>'Số lượng','Out of stock'=>'Hết hàng','Your cart is currently empty.'=>'Giỏ hàng của bạn đang trống.','Return to shop'=>'Tiếp tục mua sắm','No products were found matching your selection.'=>'Không có sản phẩm nào phù hợp với lựa chọn của bạn. Thử tìm kiếm khác hoặc xóa bộ lọc.','Product'=>'Sản phẩm','Price'=>'Giá','Subtotal'=>'Tạm tính','Total'=>'Tổng cộng','Shipping'=>'Vận chuyển','Cart totals'=>'Tóm tắt đơn hàng','Proceed to checkout'=>'Tiến hành thanh toán','Update cart'=>'Cập nhật giỏ hàng','Coupon code'=>'Mã ưu đãi','Apply coupon'=>'Áp dụng','Billing details'=>'Thông tin thanh toán','Your order'=>'Đơn hàng của bạn','Place order'=>'Đặt hàng','Your account was created successfully. Your login details have been sent to your email address.'=>'Tài khoản của bạn đã được tạo. Vui lòng kiểm tra email để lấy liên kết xác thực trước khi đăng nhập.','First name'=>'Tên','Last name'=>'Họ','Country / Region'=>'Quốc gia / Khu vực','Street address'=>'Địa chỉ đường phố','Town / City'=>'Tỉnh / Thành phố','State / County'=>'Quận / Huyện','Phone'=>'Số điện thoại','Email address'=>'Địa chỉ email','Username or email address'=>'Tên tài khoản hoặc email','Password'=>'Mật khẩu','Log in'=>'Đăng nhập','Login'=>'Đăng nhập','Register'=>'Đăng ký','Remember me'=>'Ghi nhớ đăng nhập','Lost your password?'=>'Quên mật khẩu?','Orders'=>'Đơn hàng','Order'=>'Đơn hàng','Date'=>'Ngày','Status'=>'Trạng thái','Actions'=>'Thao tác','View'=>'Xem chi tiết','Order details'=>'Chi tiết đơn hàng','Billing address'=>'Địa chỉ thanh toán','Shipping address'=>'Địa chỉ nhận hàng','No order has been made yet.'=>'Bạn chưa có đơn hàng nào.','Browse products'=>'Khám phá sản phẩm','Save changes'=>'Lưu thay đổi','Remove this item'=>'Xóa sản phẩm này','Free shipping'=>'Miễn phí vận chuyển','Product Details'=>'Chi tiết sản phẩm','Product Information'=>'Thông tin sản phẩm',
            'Order number:'=>'Mã đơn hàng:','Order number'=>'Mã đơn hàng','Date:'=>'Ngày:','Total:'=>'Tổng cộng:','Payment method:'=>'Phương thức thanh toán:','Thank you. Your order has been received.'=>'Cảm ơn bạn. Đơn hàng của bạn đã được tiếp nhận.','Pay with cash upon delivery.'=>'Thanh toán tiền mặt khi giao hàng.','Make your payment directly into our bank account.'=>'Thanh toán chuyển khoản ngân hàng.',
            'Related products'=>'Sản phẩm liên quan','Required'=>'Bắt buộc','Email'=>'Email','Customer Details'=>'Thông tin khách hàng','Order Summary'=>'Tóm tắt đơn hàng','Proceed to Checkout'=>'Tiến hành thanh toán','Update Cart'=>'Cập nhật giỏ hàng','View Details'=>'Xem chi tiết','Explore products'=>'Khám phá sản phẩm','Your Order'=>'Đơn hàng của bạn','Place Order'=>'Đặt hàng','Billing Address'=>'Địa chỉ thanh toán','Shipping Address'=>'Địa chỉ nhận hàng',
        ];
        return $vi_map[$text] ?? $translated;
    }
    $map=['Home'=>'Home','Cart'=>'Cart','Checkout'=>'Checkout','My account'=>'My Account','Description'=>'Description','Additional information'=>'Additional information','Reviews'=>'Reviews','Choose an option'=>'Select Size','Clear'=>'Clear selection','Quantity'=>'Quantity','Out of stock'=>'Out of Stock','Your cart is currently empty.'=>'Your cart is empty.','Return to shop'=>'Continue Shopping','No products were found matching your selection.'=>'No products match your selection. Try another search or clear your filters.','Product'=>'Product','Price'=>'Price','Subtotal'=>'Subtotal','Total'=>'Total','Shipping'=>'Shipping','Cart totals'=>'Order Summary','Proceed to checkout'=>'Proceed to Checkout','Update cart'=>'Update Cart','Coupon code'=>'Coupon code','Apply coupon'=>'Apply','Billing details'=>'Customer Details','Your order'=>'Your Order','Place order'=>'Place Order','Your account was created successfully. Your login details have been sent to your email address.'=>'Your account was created. Check your email for the verification link before logging in.','First name'=>'First name','Last name'=>'Last name','Country / Region'=>'Country / Region','Street address'=>'Street address','Town / City'=>'Town / City','State / County'=>'State / County','Phone'=>'Phone','Email address'=>'Email','Username or email address'=>'Username or email address','Password'=>'Password','Log in'=>'Log In','Login'=>'Log In','Register'=>'Register','Remember me'=>'Remember me','Lost your password?'=>'Lost your password?','Orders'=>'Orders','Order'=>'Orders','Date'=>'Date','Status'=>'Status','Actions'=>'Actions','View'=>'View Details','Order details'=>'Order Details','Billing address'=>'Billing Address','Shipping address'=>'Shipping Address','No order has been made yet.'=>'You have no orders yet.','Browse products'=>'Explore products','Save changes'=>'Save Changes','Remove this item'=>'Remove this item','Free shipping'=>'Free shipping','Product Details'=>'Product Details','Product Information'=>'Product Information'];
    return $map[$text]??$translated;
},100,3);

add_filter('woocommerce_get_related_product_cat_terms',function($ids){$allowed=['lifestyle','running','basketball','football','training','air-force-1','jordan','air-max','dunk','cortez','pegasus','vomero','revolution','metcon','mercurial'];return array_values(array_filter($ids,function($id)use($allowed){$t=get_term($id,'product_cat');return $t&&!is_wp_error($t)&&in_array($t->slug,$allowed,true);}));});
add_filter('woocommerce_get_related_product_tag_terms',fn()=>[]);

add_filter('gettext',function($translated,$text,$domain){
    if(!is_admin()&&$domain==='woocommerce'&&$text==='Shipment'){
        $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
        return $is_vi ? 'Vận chuyển' : 'Shipping';
    }
    return $translated;
},110,3);

add_filter('woocommerce_get_privacy_policy_text',function($text,$type){
    if(!get_option('paxig_ui_demo'))return $text;
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    if ($is_vi) {
        return $type==='checkout'?'Đây là đơn hàng thử nghiệm. Vui lòng không chuyển tiền thật hoặc nhập thông tin cá nhân thực tế.':'Chỉ sử dụng tài khoản thử nghiệm. Vui lòng không nhập thông tin cá nhân thực tế.';
    }
    return $type==='checkout'?'This is a demonstration order. Do not make a real payment or enter real personal details.':'Use a demonstration account only. Do not enter real personal details.';
},100,2);

add_filter('woocommerce_shipping_package_name',function($name){
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    return $is_vi ? 'Vận chuyển' : 'Shipping';
});

// Single & Loop Add-to-cart labels
add_filter('woocommerce_product_single_add_to_cart_text',function(){
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    return $is_vi ? 'Thêm vào giỏ hàng' : 'Add to Bag';
},100);

add_filter('woocommerce_product_add_to_cart_text',function($text,$product){
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    if ($product && $product->is_type('variable')) {
        return $is_vi ? 'Chọn kích cỡ' : 'Select Size';
    }
    return $is_vi ? 'Thêm vào giỏ hàng' : 'Add to Bag';
},100,2);

add_filter('woocommerce_page_title',function($title){
    if(is_shop()){
        $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
        return $is_vi ? 'Tất cả giày' : 'All Shoes';
    }
    return $title;
},100);

add_filter('woocommerce_catalog_orderby',function(){
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    if($is_vi){
        return ['menu_order'=>'Nổi bật','popularity'=>'Phổ biến nhất','rating'=>'Đánh giá cao','date'=>'Mới nhất','price'=>'Giá: Thấp đến Cao','price-desc'=>'Giá: Cao đến Thấp'];
    }
    return ['menu_order'=>'Featured','popularity'=>'Popularity','rating'=>'Reviews','date'=>'Newest','price'=>'Price: Low to High','price-desc'=>'Price: High to Low'];
},100);

add_filter('ngettext',function($translation,$single,$plural,$number,$domain){
    if($domain==='woocommerce'&&str_starts_with($single,'Showing')){
        $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
        if ($is_vi) {
            return str_contains($plural,'%3$d') ? 'Hiển thị %1$d–%2$d trong %3$d sản phẩm' : 'Hiển thị %1$d sản phẩm';
        }
        return str_contains($plural,'%3$d')?'Showing %1$d–%2$d of %3$d products':'Showing %1$d products';
    }
    return $translation;
},100,5);

add_filter('gettext',function($translated,$text,$domain){
    if(!is_admin()&&$domain==='woocommerce'){
        $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
        if ($is_vi) {
            $labels=['Add a review'=>'Thêm đánh giá','There are no reviews yet.'=>'Chưa có đánh giá nào.','Your rating'=>'Đánh giá của bạn','Your review'=>'Nhận xét của bạn','Submit'=>'Gửi đánh giá','Showing the single result'=>'Hiển thị kết quả duy nhất'];
            return $labels[$text]??$translated;
        }
        $labels=['Add a review'=>'Add a Review','There are no reviews yet.'=>'There are no reviews yet.','Your rating'=>'Your Rating','Your review'=>'Your Review','Submit'=>'Submit','Showing the single result'=>'Showing the single result'];
        return $labels[$text]??$translated;
    }
    return $translated;
},120,3);

add_action('widgets_init',function(){register_sidebar(['name'=>'Recently Viewed Products','id'=>'paxig-recent','before_widget'=>'<section class="paxig-recent-native">','after_widget'=>'</section>','before_title'=>'<h2>','after_title'=>'</h2>']);});
add_action('woocommerce_after_single_product_summary',function(){if(is_active_sidebar('paxig-recent'))dynamic_sidebar('paxig-recent');},25);

function paxig_original_demo_notice(){
    if(!get_option('paxig_ui_demo')) return;
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    $title = $is_vi ? 'Cửa hàng thử nghiệm' : 'Demo Store';
    $body  = $is_vi ? 'Sản phẩm, giá cả và đơn hàng là dữ liệu thử nghiệm. Vui lòng không chuyển tiền thật hoặc thanh toán thực tế.' : 'Products, prices and orders are demonstration data. Do not transfer money or make a real payment.';
    echo '<p class="paxig-notice demo-notice"><strong>' . esc_html( $title ) . '</strong><span>' . esc_html( $body ) . '</span></p>';
}
add_action('woocommerce_before_checkout_form','paxig_original_demo_notice',5);
add_action('woocommerce_thankyou','paxig_original_demo_notice',5);

add_action('woocommerce_no_products_found',function(){
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    $label = $is_vi ? 'Xem tất cả giày' : 'Shop All Shoes';
    echo '<p class="paxig-empty-action"><a class="button" href="'.esc_url(wc_get_page_permalink('shop')).'">' . esc_html( $label ) . '</a></p>';
},20);

// Translate WooCommerce Gutenberg Blocks content (Cart, Checkout, Empty Cart)
add_filter('render_block', function($block_content, $block) {
    if (is_admin()) return $block_content;
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    if (!$is_vi) return $block_content;

    $block_map = [
        'Your cart is currently empty!' => 'Giỏ hàng của bạn đang trống!',
        'New in store' => 'Sản phẩm mới lên kệ',
        'You may be interested in&hellip;' => 'Có thể bạn sẽ thích&hellip;',
        'You may be interested in…' => 'Có thể bạn sẽ thích…',
        'Browse store' => 'Khám phá cửa hàng',
        'Return to shop' => 'Tiếp tục mua sắm',
    ];
    return strtr($block_content, $block_map);
}, 20, 2);

if(get_option('paxig_ui_demo')){
 remove_action('pre_get_posts','paxig_shoes_filter_best_sellers');
 add_action('pre_get_posts',function($q){if(is_admin()||!$q->is_main_query()||!$q->is_post_type_archive('product'))return;if(($_GET['paxig_best_sellers']??'')==='1'){$tax=$q->get('tax_query');if(!is_array($tax))$tax=[];$tax[]=['taxonomy'=>'product_cat','field'=>'slug','terms'=>['best-sellers']];$q->set('tax_query',$tax);}if(isset($_GET['paxig_original_category'])&&is_string($_GET['paxig_original_category']))$q->set('post__in',[0]);});
}
