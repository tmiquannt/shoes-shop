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
    if(is_admin()||!in_array($domain,['woocommerce','default','paxig-shoes'],true))return $translated;
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    if ($is_vi) {
        $vi_map = [
            'Home'=>'Trang chủ','Cart'=>'Giỏ hàng','Checkout'=>'Thanh toán','My account'=>'Tài khoản của tôi','Description'=>'Mô tả','Additional information'=>'Thông tin bổ sung','Reviews'=>'Đánh giá','Choose an option'=>'Chọn kích cỡ','Clear'=>'Xóa lựa chọn','Quantity'=>'Số lượng','Out of stock'=>'Hết hàng','Your cart is currently empty.'=>'Giỏ hàng của bạn đang trống.','Return to shop'=>'Tiếp tục mua sắm','No products were found matching your selection.'=>'Không có sản phẩm nào phù hợp với lựa chọn của bạn. Thử tìm kiếm khác hoặc xóa bộ lọc.','Product'=>'Sản phẩm','Price'=>'Giá','Subtotal'=>'Tạm tính','Subtotal:'=>'Tạm tính:','Total'=>'Tổng cộng','Total:'=>'Tổng cộng:','Shipping'=>'Vận chuyển','Shipping:'=>'Vận chuyển:','Cart totals'=>'Tóm tắt đơn hàng','Proceed to checkout'=>'Tiến hành thanh toán','Update cart'=>'Cập nhật giỏ hàng','Coupon code'=>'Mã ưu đãi','Apply coupon'=>'Áp dụng','Billing details'=>'Thông tin thanh toán','Your order'=>'Đơn hàng của bạn','Place order'=>'Đặt hàng','Your account was created successfully. Your login details have been sent to your email address.'=>'Tài khoản của bạn đã được tạo. Vui lòng kiểm tra email để lấy liên kết xác thực trước khi đăng nhập.','First name'=>'Tên','Last name'=>'Họ','Country / Region'=>'Quốc gia / Khu vực','Street address'=>'Địa chỉ đường phố','Town / City'=>'Tỉnh / Thành phố','State / County'=>'Quận / Huyện','Phone'=>'Số điện thoại','Email address'=>'Địa chỉ email','Username or email address'=>'Tên tài khoản hoặc email','Password'=>'Mật khẩu','Log in'=>'Đăng nhập','Login'=>'Đăng nhập','Register'=>'Đăng ký','Remember me'=>'Ghi nhớ đăng nhập','Lost your password?'=>'Quên mật khẩu?','Forgot your password?'=>'Quên mật khẩu?','Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.'=>'Quên mật khẩu? Vui lòng nhập tên tài khoản hoặc địa chỉ email. Bạn sẽ nhận được liên kết qua email để tạo mật khẩu mới.','Username or email'=>'Tên tài khoản hoặc email','Reset password'=>'Đặt lại mật khẩu','Password reset email has been sent.'=>'Email đặt lại mật khẩu đã được gửi.','The following addresses will be used on the checkout page by default.'=>'Các địa chỉ sau sẽ được dùng mặc định tại trang thanh toán.','You have not set up this type of address yet.'=>'Bạn chưa thiết lập loại địa chỉ này.','Add billing address'=>'Thêm địa chỉ thanh toán','Add shipping address'=>'Thêm địa chỉ nhận hàng','Edit billing address'=>'Sửa địa chỉ thanh toán','Edit shipping address'=>'Sửa địa chỉ nhận hàng','Display name'=>'Tên hiển thị','This will be how your name will be displayed in the account section and in reviews'=>'Tên này sẽ hiển thị trong mục tài khoản và phần đánh giá sản phẩm','Password change'=>'Thay đổi mật khẩu','Current password (leave blank to leave unchanged)'=>'Mật khẩu hiện tại (bỏ trống nếu không đổi)','New password (leave blank to leave unchanged)'=>'Mật khẩu mới (bỏ trống nếu không đổi)','Confirm new password'=>'Xác nhận mật khẩu mới','Order #%1$s was placed on %2$s and is currently %3$s.'=>'Đơn hàng #%1$s được đặt vào ngày %2$s và hiện đang %3$s.','Pending payment'=>'Chờ thanh toán','Processing'=>'Đang xử lý','On hold'=>'Tạm giữ','Completed'=>'Đã hoàn thành','Cancelled'=>'Đã hủy','Refunded'=>'Đã hoàn tiền','Failed'=>'Thất bại','Order again'=>'Đặt lại đơn hàng','Order updates'=>'Cập nhật đơn hàng','Invalid order.'=>'Đơn hàng không hợp lệ.','Orders'=>'Đơn hàng','Order'=>'Đơn hàng','Date'=>'Ngày','Status'=>'Trạng thái','Actions'=>'Thao tác','View'=>'Xem chi tiết','Order details'=>'Chi tiết đơn hàng','Billing address'=>'Địa chỉ thanh toán','Shipping address'=>'Địa chỉ nhận hàng','No order has been made yet.'=>'Bạn chưa có đơn hàng nào.','Browse products'=>'Khám phá sản phẩm','Save changes'=>'Lưu thay đổi','Remove this item'=>'Xóa sản phẩm này','Free shipping'=>'Miễn phí vận chuyển','Product Details'=>'Chi tiết sản phẩm','Product Information'=>'Thông tin sản phẩm',
            'Order number:'=>'Mã đơn hàng:','Order number'=>'Mã đơn hàng','Date:'=>'Ngày:','Payment method:'=>'Phương thức thanh toán:','Thank you. Your order has been received.'=>'Cảm ơn bạn. Đơn hàng của bạn đã được tiếp nhận.','Pay with cash upon delivery.'=>'Thanh toán tiền mặt khi giao hàng.','Make your payment directly into our bank account.'=>'Thanh toán chuyển khoản ngân hàng.',
            'Related products'=>'Sản phẩm liên quan','Required'=>'Bắt buộc','Email'=>'Email','Customer Details'=>'Thông tin khách hàng','Order Summary'=>'Tóm tắt đơn hàng','Proceed to Checkout'=>'Tiến hành thanh toán','Update Cart'=>'Cập nhật giỏ hàng','View Details'=>'Xem chi tiết','Explore products'=>'Khám phá sản phẩm','Your Order'=>'Đơn hàng của bạn','Place Order'=>'Đặt hàng','Billing Address'=>'Địa chỉ thanh toán','Shipping Address'=>'Địa chỉ nhận hàng',
            'Relevance'=>'Phù hợp nhất','Shop order'=>'Thứ tự sắp xếp','Min price'=>'Giá tối thiểu','Max price'=>'Giá tối đa','Price:'=>'Giá:','Filter'=>'Lọc','Apply'=>'Áp dụng','View cart'=>'Xem giỏ hàng','Show password'=>'Hiện mật khẩu','Hide password'=>'Ẩn mật khẩu',
            'Save my name, email, and website in this browser for the next time I comment.'=>'Lưu tên, email và trang web của tôi trong trình duyệt này cho lần bình luận tiếp theo.',
            'Your email address will not be published.'=>'Địa chỉ email của bạn sẽ không được hiển thị công khai.',
            'Required fields are marked %s'=>'Các trường bắt buộc được đánh dấu %s',
            'Required fields are marked *'=>'Các trường bắt buộc được đánh dấu *',
            'Cancel reply'=>'Hủy trả lời','Rate…'=>'Xếp hạng…','Perfect'=>'Rất tốt','Good'=>'Tốt','Average'=>'Trung bình','Not that bad'=>'Không tệ','Very poor'=>'Rất tệ',
            'Contact information'=>'Thông tin liên hệ','Shipping methods'=>'Phương thức vận chuyển','Order note'=>'Ghi chú đơn hàng','Notes about your order, e.g. special notes for delivery.'=>'Ghi chú về đơn hàng, ví dụ: lưu ý khi giao hàng.','Add a note to your order'=>'Thêm ghi chú cho đơn hàng','Enter a coupon'=>'Nhập mã ưu đãi','Taxes'=>'Thuế','Discount'=>'Giảm giá','Fee'=>'Phụ phí',
            'BUILT DIFFERENT. MOVE FORWARD.'=>'KHÁC BIỆT TỪ BẢN LĨNH. TIẾN BƯỚC KHÔNG NGỪNG.','ALL RIGHTS RESERVED.'=>'BẢN QUYỀN ĐÃ ĐƯỢC BẢO LƯU.','SUPPORT'=>'HỖ TRỢ','PRIVACY'=>'CHÍNH SÁCH BẢO MẬT','TERMS'=>'ĐIỀU KHOẢN','HELP'=>'TRỢ GIÚP',
            'HOME'=>'TRANG CHỦ','SHOP'=>'CỬA HÀNG','SAVED'=>'ĐÃ LƯU','BAG'=>'GIỎ HÀNG','ME'=>'TÀI KHOẢN','SHOPPING BAG'=>'GIỎ HÀNG','YOUR BAG.'=>'GIỎ HÀNG CỦA BẠN.',
            'DRAG TO EXPLORE'=>'KÉO ĐỂ KHÁM PHÁ','VYRE / WORLDWIDE'=>'VYRE / TOÀN CẦU','BETTER MATERIALS. BOLDER IDEAS.'=>'CHẤT LIỆU CAO CẤP HƠN. Ý TƯỞNG TÁO BẠO HƠN.','THE NEXT MOVE STARTS HERE'=>'BƯỚC TIẾP THEO BẮT ĐẦU TẠI ĐÂY',
            'CURATED / 01'=>'TUYỂN CHỌN / 01','NEW ARRIVALS.'=>'SẢN PHẨM MỚI.','WHO WE ARE'=>'CHÚNG TÔI LÀ AI','OUR STORY'=>'CÂU CHUYỆN CỦA CHÚNG TÔI',
            'FOOT LENGTH (CM)'=>'CHIỀU DÀI BÀN CHÂN (CM)','YOUR PERFECT FIT'=>'KÍCH CỠ VỪA VẶN','SIZE GUIDE.'=>'BẢNG KÍCH CỠ.',"Measure your foot from heel to toe. If you're between sizes, we recommend sizing up for all-day comfort."=>'Đo chiều dài bàn chân từ gót đến ngón chân. Nếu bạn ở giữa hai cỡ, chúng tôi khuyên bạn nên chọn cỡ lớn hơn để thoải mái cả ngày.','GOT IT ✓'=>'ĐÃ HIỂU ✓','YOU MIGHT ALSO LIKE.'=>'CÓ THỂ BẠN CŨNG THÍCH.',
            'ALREADY A MEMBER? <a href="%s">SIGN IN ↗</a>'=>'ĐÃ CÓ TÀI KHOẢN? <a href="%s">ĐĂNG NHẬP ↗</a>','NEW TO VYRE? <a href="%s">CREATE AN ACCOUNT ↗</a>'=>'CHƯA CÓ TÀI KHOẢN? <a href="%s">TẠO TÀI KHOẢN ↗</a>',
            'Email verification is temporarily unavailable. Please try again later.'=>'Tính năng xác thực email tạm thời không khả dụng. Vui lòng thử lại sau.',
            'Please verify your email before logging in. Use the resend form on this page if you need another link.'=>'Vui lòng xác thực email trước khi đăng nhập. Sử dụng biểu mẫu gửi lại trên trang này nếu bạn cần liên kết mới.',
            'Sandstone / Everyday sneaker'=>'Sandstone / Giày sneaker hàng ngày','LIFESTYLE / NEW SEASON'=>'LIFESTYLE / MÙA MỚI','COLOR: SANDSTONE'=>'MÀU: SANDSTONE','4 AVAILABLE COLORS'=>'4 MÀU KHẢ DỤNG','SELECT SIZE'=>'CHỌN KÍCH CỠ','SIZE GUIDE ↗'=>'BẢNG KÍCH CỠ ↗',
            'FREE SHIPPING OVER $150'=>'MIỄN PHÍ VẬN CHUYỂN TỪ 150.000₫','30-DAY RETURNS'=>'ĐỔI TRẢ TRONG 30 NGÀY','SECURE CHECKOUT'=>'THANH TOÁN BẢO MẬT','Read the shopping, cancellation and returns guide'=>'Xem hướng dẫn mua sắm, hủy đơn và đổi trả','Delivery & Returns'=>'Giao hàng & Đổi trả',
        ];
        return $vi_map[$text] ?? $translated;
    }
    $map=['Home'=>'Home','Cart'=>'Cart','Checkout'=>'Checkout','My account'=>'My Account','Description'=>'Description','Additional information'=>'Additional information','Reviews'=>'Reviews','Choose an option'=>'Select Size','Clear'=>'Clear selection','Quantity'=>'Quantity','Out of stock'=>'Out of Stock','Your cart is currently empty.'=>'Your cart is empty.','Return to shop'=>'Continue Shopping','No products were found matching your selection.'=>'No products match your selection. Try another search or clear your filters.','Product'=>'Product','Price'=>'Price','Subtotal'=>'Subtotal','Subtotal:'=>'Subtotal:','Total'=>'Total','Total:'=>'Total:','Shipping'=>'Shipping','Shipping:'=>'Shipping:','Cart totals'=>'Order Summary','Proceed to checkout'=>'Proceed to Checkout','Update cart'=>'Update Cart','Coupon code'=>'Coupon code','Apply coupon'=>'Apply','Billing details'=>'Customer Details','Your order'=>'Your Order','Place order'=>'Place Order','Your account was created successfully. Your login details have been sent to your email address.'=>'Your account was created. Check your email for the verification link before logging in.','First name'=>'First name','Last name'=>'Last name','Country / Region'=>'Country / Region','Street address'=>'Street address','Town / City'=>'Town / City','State / County'=>'State / County','Phone'=>'Phone','Email address'=>'Email','Username or email address'=>'Username or email address','Password'=>'Password','Log in'=>'Log In','Login'=>'Log In','Register'=>'Register','Remember me'=>'Remember me','Lost your password?'=>'Lost your password?','Forgot your password?'=>'Forgot your password?','Orders'=>'Orders','Order'=>'Orders','Date'=>'Date','Status'=>'Status','Actions'=>'Actions','View'=>'View Details','Order details'=>'Order Details','Billing address'=>'Billing Address','Shipping address'=>'Shipping Address','No order has been made yet.'=>'You have no orders yet.','Browse products'=>'Explore products','Save changes'=>'Save Changes','Remove this item'=>'Remove this item','Free shipping'=>'Free shipping','Product Details'=>'Product Details','Product Information'=>'Product Information',
        'BUILT DIFFERENT. MOVE FORWARD.'=>'BUILT DIFFERENT. MOVE FORWARD.','ALL RIGHTS RESERVED.'=>'ALL RIGHTS RESERVED.','SUPPORT'=>'SUPPORT','PRIVACY'=>'PRIVACY','TERMS'=>'TERMS','HELP'=>'HELP',
        'HOME'=>'HOME','SHOP'=>'SHOP','SAVED'=>'SAVED','BAG'=>'BAG','ME'=>'ME','SHOPPING BAG'=>'SHOPPING BAG','YOUR BAG.'=>'YOUR BAG.',
        'DRAG TO EXPLORE'=>'DRAG TO EXPLORE','VYRE / WORLDWIDE'=>'VYRE / WORLDWIDE','BETTER MATERIALS. BOLDER IDEAS.'=>'BETTER MATERIALS. BOLDER IDEAS.','THE NEXT MOVE STARTS HERE'=>'THE NEXT MOVE STARTS HERE',
        'CURATED / 01'=>'CURATED / 01','NEW ARRIVALS.'=>'NEW ARRIVALS.','WHO WE ARE'=>'WHO WE ARE','OUR STORY'=>'OUR STORY',
        'FOOT LENGTH (CM)'=>'FOOT LENGTH (CM)','YOUR PERFECT FIT'=>'YOUR PERFECT FIT','SIZE GUIDE.'=>'SIZE GUIDE.',"Measure your foot from heel to toe. If you're between sizes, we recommend sizing up for all-day comfort."=>"Measure your foot from heel to toe. If you're between sizes, we recommend sizing up for all-day comfort.",'GOT IT ✓'=>'GOT IT ✓','YOU MIGHT ALSO LIKE.'=>'YOU MIGHT ALSO LIKE.',
        'ALREADY A MEMBER? <a href="%s">SIGN IN ↗</a>'=>'ALREADY A MEMBER? <a href="%s">SIGN IN ↗</a>','NEW TO VYRE? <a href="%s">CREATE AN ACCOUNT ↗</a>'=>'NEW TO VYRE? <a href="%s">CREATE AN ACCOUNT ↗</a>',
        'Email verification is temporarily unavailable. Please try again later.'=>'Email verification is temporarily unavailable. Please try again later.',
        'Please verify your email before logging in. Use the resend form on this page if you need another link.'=>'Please verify your email before logging in. Use the resend form on this page if you need another link.',
        'Sandstone / Everyday sneaker'=>'Sandstone / Everyday sneaker','LIFESTYLE / NEW SEASON'=>'LIFESTYLE / NEW SEASON','COLOR: SANDSTONE'=>'COLOR: SANDSTONE','4 AVAILABLE COLORS'=>'4 AVAILABLE COLORS','SELECT SIZE'=>'SELECT SIZE','SIZE GUIDE ↗'=>'SIZE GUIDE ↗',
        'FREE SHIPPING OVER $150'=>'FREE SHIPPING OVER $150','30-DAY RETURNS'=>'30-DAY RETURNS','SECURE CHECKOUT'=>'SECURE CHECKOUT','Read the shopping, cancellation and returns guide'=>'Read the shopping, cancellation and returns guide','Delivery & Returns'=>'Delivery & Returns',
    ];
    return $map[$text]??$translated;
},100,3);

add_filter('gettext_with_context',function($translated,$text,$context,$domain){
    if(is_admin()||!in_array($domain,['woocommerce','default'],true))return $translated;
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    if ($is_vi) {
        if ($context === 'breadcrumb' && $text === 'Home') return 'Trang chủ';
    } else {
        if ($context === 'breadcrumb' && $text === 'Home') return 'Home';
    }
    return $translated;
},100,4);

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

// Breadcrumb translations
add_filter('woocommerce_breadcrumb_defaults', function($defaults) {
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    if ($is_vi) {
        $defaults['home'] = 'Trang chủ';
        $defaults['wrap_before'] = '<nav class="woocommerce-breadcrumb" aria-label="Đường dẫn điều hướng">';
    } else {
        $defaults['home'] = 'Home';
        $defaults['wrap_before'] = '<nav class="woocommerce-breadcrumb" aria-label="Breadcrumb">';
    }
    return $defaults;
}, 20);

add_filter('woocommerce_get_breadcrumb', function($crumbs) {
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    if ($is_vi) {
        $crumb_map = [
            'Home'         => 'Trang chủ',
            'New Arrivals' => 'Mới lên kệ',
            'Best Sellers' => 'Bán chạy',
            'Sale'         => 'Ưu đãi',
            'Trending'     => 'Xu hướng',
            'Lifestyle'    => 'Lifestyle',
            'Running'      => 'Chạy bộ',
            'Basketball'   => 'Bóng rổ',
            'Football'     => 'Bóng đá',
            'Training'     => 'Tập luyện',
            'Shop'         => 'Cửa hàng',
            'All Shoes'    => 'Tất cả giày',
        ];
        foreach ($crumbs as &$crumb) {
            if (isset($crumb_map[$crumb[0]])) {
                $crumb[0] = $crumb_map[$crumb[0]];
            } elseif (is_string($crumb[0]) && str_starts_with($crumb[0], 'Search results for')) {
                $crumb[0] = preg_replace('/Search results for &ldquo;(.*)&rdquo;/u', 'Kết quả tìm kiếm cho “$1”', $crumb[0]);
                $crumb[0] = preg_replace('/Search results for “(.*)”/u', 'Kết quả tìm kiếm cho “$1”', $crumb[0]);
            }
        }
    }
    return $crumbs;
}, 20);

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
        'Contact information' => 'Thông tin liên hệ',
        'Shipping address' => 'Địa chỉ nhận hàng',
        'Billing address' => 'Địa chỉ thanh toán',
        'Shipping methods' => 'Phương thức vận chuyển',
        'Payment options' => 'Tùy chọn thanh toán',
        'Order summary' => 'Tóm tắt đơn hàng',
        'Order note' => 'Ghi chú đơn hàng',
    ];
    return strtr($block_content, $block_map);
}, 20, 2);

if(get_option('paxig_ui_demo')){
 remove_action('pre_get_posts','paxig_shoes_filter_best_sellers');
 add_action('pre_get_posts',function($q){if(is_admin()||!$q->is_main_query()||!$q->is_post_type_archive('product'))return;if(($_GET['paxig_best_sellers']??'')==='1'){$tax=$q->get('tax_query');if(!is_array($tax))$tax=[];$tax[]=['taxonomy'=>'product_cat','field'=>'slug','terms'=>['best-sellers']];$q->set('tax_query',$tax);}if(isset($_GET['paxig_original_category'])&&is_string($_GET['paxig_original_category']))$q->set('post__in',[0]);});
}
