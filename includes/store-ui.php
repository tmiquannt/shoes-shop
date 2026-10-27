<?php
/** Storefront presentation only. No persisted locale, data or workflow changes. */
defined('ABSPATH') || exit;
function paxig_store_frontend() {
    return PHP_SAPI !== 'cli' && !is_admin() && !(defined('REST_REQUEST') && REST_REQUEST);
}
add_filter('locale',fn($locale)=>paxig_store_frontend()?(function_exists('paxig_filter_locale')?paxig_filter_locale($locale):'en_US'):$locale,999);
add_filter('determine_locale',fn($locale)=>paxig_store_frontend()?(function_exists('paxig_filter_locale')?paxig_filter_locale($locale):'en_US'):$locale,999);
add_filter('language_attributes',fn($attributes)=>paxig_store_frontend()?(function_exists('paxig_get_current_language')&&paxig_get_current_language()==='vi'?'lang="vi"':'lang="en-US"'):$attributes,999);
add_filter('body_class',function($classes){if(paxig_store_frontend())$classes[]='paxig-store';return $classes;});
add_action('wp_enqueue_scripts',function(){
    if(!paxig_store_frontend())return;
    remove_action('wp_head','print_emoji_detection_script',7);
    remove_action('wp_enqueue_scripts','wp_enqueue_emoji_detection_script');
    remove_action('wp_print_styles','print_emoji_styles');
    remove_action('wp_enqueue_scripts','wp_enqueue_emoji_styles');
},0);
add_action('wp_enqueue_scripts',function(){
    if(!paxig_store_frontend())return;
    wp_enqueue_style('paxig-store',get_template_directory_uri().'/assets/css/store-ui.css',['paxig-shoes-style'],filemtime(get_template_directory().'/assets/css/store-ui.css'));
    wp_enqueue_script('paxig-store',get_template_directory_uri().'/assets/js/store-ui.js',[],filemtime(get_template_directory().'/assets/js/store-ui.js'),true);
    wp_localize_script('paxig-store','paxigStoreLabels',paxig_store_labels());
},100);
function paxig_store_labels(){
    return [
      'Chuyển khoản ngân hàng (BACS)'=>'Bank Transfer (BACS)','Thanh toán khi nhận hàng (COD)'=>'Cash on Delivery (COD)','Chờ xác minh'=>'Awaiting Verification','Đã yêu cầu hoàn tiền'=>'Refund Requested','Hư hỏng / không nhập kho'=>'Damaged / Not Restockable',
      'Liên kết in không hợp lệ hoặc đã hết hạn.'=>'The print link is invalid or has expired.','Không thể đọc chứng từ.'=>'We cannot load this document.','Không tìm thấy chứng từ.'=>'Document not found.',
      'Chi tiết đơn đã thay đổi; tải lại trước khi gửi.'=>'Order details have changed. Reload before submitting.','Kho dữ liệu chưa hỗ trợ giao dịch an toàn.'=>'The data store is not ready for this transaction.','Dữ liệu đang được xử lý; hãy tải lại.'=>'This data is being processed. Please reload.','Không thể lưu. Hãy tải lại dữ liệu để kiểm tra trước khi thử lại.'=>'We could not save your request. Reload to check the current data before trying again.',
      'Yêu cầu hủy đơn'=>'Request Cancellation','Yêu cầu trả hàng'=>'Request Return','Trạng thái đơn hàng'=>'PAXIG Fulfillment & Payment',
      'Chờ xác minh thanh toán'=>'Awaiting Payment Verification','Chờ xử lý'=>'Pending','Đã xác nhận'=>'Confirmed','Đang chuẩn bị hàng'=>'Preparing','Đang giao hàng'=>'Shipped','Đã giao hàng'=>'Delivered','Đã hủy'=>'Cancelled',
      'Chưa thanh toán'=>'Unpaid','Đã thanh toán'=>'Paid','Đã đối soát'=>'Reconciled','Đã hoàn tiền'=>'Refunded','Đang yêu cầu hoàn tiền'=>'Refund Requested',
      'Đã yêu cầu trả hàng'=>'Return Requested','Đã chấp nhận trả hàng'=>'Return Approved','Đã nhận hàng trả'=>'Return Received','Từ chối trả hàng'=>'Return Rejected','Hoàn tiền đã xử lý'=>'Refund Processed','Hoàn tiền một phần'=>'Partially Refunded','Hoàn tiền đã ghi nhận'=>'Refund Recorded',
      'Không có'=>'None','Đang cập nhật'=>'Updating','Phiếu bán hàng'=>'Sales Order','Phiếu thu'=>'Payment Receipt','Phiếu trả hàng'=>'Return Record','Chứng từ hoàn tiền'=>'Refund Record','Có thể nhập lại kho'=>'Restockable','Hàng hư hỏng / không nhập kho'=>'Damaged / Not Restockable',
      'Tiền hàng hoàn'=>'Item Refund','Thuế hàng hoàn'=>'Item Tax Refund','Phí vận chuyển hoàn'=>'Shipping Refund','Thuế vận chuyển hoàn'=>'Shipping Tax Refund','Tổng hoàn'=>'Total Refund',
      'Giao hàng'=>'Fulfillment','Thanh toán'=>'Payment','Trả hàng'=>'Return','Hoàn tiền'=>'Refund','Chứng từ'=>'Documents','Chưa có chứng từ.'=>'No documents yet.','Lý do hủy'=>'Cancellation Reason','Lý do trả hàng'=>'Return Reason','Lý do:'=>'Reason:','Lý do'=>'Reason','Sản phẩm trả'=>'Return Items',
      'Yêu cầu hủy đã được chấp nhận.'=>'Your cancellation request was approved.','Yêu cầu hủy đã bị từ chối.'=>'Your cancellation request was declined.','Đã gửi yêu cầu hủy.'=>'Cancellation request submitted.','Tham chiếu:'=>'Reference:','Ngày:'=>'Date:','Xem / In chứng từ'=>'View / Print Document',
      'Đã gửi yêu cầu hủy. Nhân viên sẽ xem xét.'=>'Cancellation request submitted for review.','Đã gửi yêu cầu trả hàng. Nhân viên sẽ xem xét.'=>'Return request submitted for review.','Chọn ít nhất một sản phẩm và số lượng trả.'=>'Select at least one item and a return quantity.',
      'Vui lòng đăng nhập.'=>'Please log in.','Chức năng này chỉ dành cho khách hàng.'=>'This feature is available to customers only.','Không tìm thấy đơn.'=>'Order not found.','Không thể đọc đơn lúc này.'=>'We cannot load this order right now.','Không thể gửi yêu cầu.'=>'We could not send your request.',
      'Cần lý do và mã yêu cầu hợp lệ.'=>'A valid reason and request identifier are required.','Đơn đã có yêu cầu hủy hoặc mã yêu cầu đã được dùng.'=>'This order already has a cancellation request or the request identifier was used.',
      'Đơn đã có yêu cầu hủy.'=>'This order already has a cancellation request.','Chỉ yêu cầu hủy khi đơn đang chờ hoặc đã xác nhận.'=>'Cancellation is available only for pending or confirmed orders.','Trạng thái đơn hiện không cho phép yêu cầu hủy.'=>'The current order status does not allow cancellation.',
      'Chỉ yêu cầu trả hàng sau khi đã giao.'=>'Returns are available only after delivery.','Đơn đã có yêu cầu trả hàng.'=>'This order already has a return request.','Cần chọn sản phẩm trả.'=>'Select the items to return.','Chi tiết hàng trả không hợp lệ.'=>'Return item details are invalid.','Sản phẩm hoặc số lượng trả không hợp lệ.'=>'The return item or quantity is invalid.',
      'Giao hàng tiêu chuẩn'=>'Standard Shipping','Miễn phí vận chuyển'=>'Free Shipping','Chuyển khoản ngân hàng (DEMO)'=>'Bank Transfer (Demo)','Thanh toán khi nhận hàng (DEMO)'=>'Cash on Delivery (Demo)',
      'Mô phỏng BACS: đơn chờ Accountant xác minh. Không chuyển tiền thật.'=>'Demo bank transfer: payment awaits verification. Do not transfer real money.',
      'Mô phỏng COD: không thu tiền thật. Hóa đơn/payment giữ trạng thái đối soát riêng.'=>'Demo cash on delivery: no real payment is collected. Payment reconciliation is tracked separately.',
      'Kích cỡ'=>'Size','Màu sắc'=>'Color','Giới tính'=>'Gender','Môn thể thao'=>'Sport','Nam'=>'Men','Nữ'=>'Women','Trẻ em'=>'Kids',
      'Đỏ / đen'=>'Red / Black','Trắng / xám'=>'White / Grey','Xanh / đen'=>'Lime / Black','Running — Chạy bộ'=>'Running','Basketball — Bóng rổ'=>'Basketball','Football — Bóng đá'=>'Football','Training — Tập luyện'=>'Training',
      'Mới lên kệ'=>'New Arrivals','Đang nổi bật'=>'Trending','Tuyển chọn được yêu thích'=>'Best Sellers','Ưu đãi'=>'Sale',
      'Cửa hàng'=>'All Shoes','Sản phẩm vừa xem'=>'Recently Viewed','Giỏ hàng'=>'Cart','Thanh toán'=>'Payment','Tài khoản'=>'My Account','Hướng dẫn mua sắm'=>'Shopping Guide','Về PAXIG\'s Shoes'=>'About PAXIG\'S SHOES',
      'In / Lưu PDF'=>'Print / Save PDF','Chứng từ khách hàng'=>'Customer Document','Số:'=>'Number:','Ngày lập:'=>'Created:','Đơn hàng'=>'Order','Mã đơn:'=>'Order:','Phương thức thanh toán gốc:'=>'Original Payment Method:','Phương thức:'=>'Method:','Khách hàng'=>'Customer','Họ tên:'=>'Name:','Điện thoại:'=>'Phone:','Địa chỉ:'=>'Address:',
      'Sản phẩm / biến thể'=>'Product / Variation','Sản phẩm hoàn'=>'Refunded Item','Số lượng'=>'Quantity','Đơn giá'=>'Unit Price','Thành tiền'=>'Line Total','Tạm tính'=>'Subtotal','Giảm giá'=>'Discount','Điều chỉnh / vận chuyển'=>'Adjustment / Shipping','Tổng cộng'=>'Total','Số tiền'=>'Amount',
      'Tiền hàng (chưa thuế)'=>'Items (Excluding Tax)','Giảm giá (chưa thuế)'=>'Discount (Excluding Tax)','Phí vận chuyển (chưa thuế)'=>'Shipping (Excluding Tax)','Phụ phí (chưa thuế)'=>'Fees (Excluding Tax)','Thuế (hàng, vận chuyển và phụ phí)'=>'Tax (Items, Shipping and Fees)','Điều chỉnh khác'=>'Other Adjustment','Phương thức vận chuyển'=>'Shipping Method','Tổng thanh toán'=>'Total Payment','Miễn phí'=>'Free',
      'Chứng từ được tạo từ thông tin đơn hàng tại thời điểm lập.'=>'This document uses the order information recorded when it was created.',
      'Hủy đơn đã thanh toán'=>'Paid Order Cancellation','Không có phí vận chuyển cần hoàn'=>'No shipping charge to refund','Không hoàn phí vận chuyển'=>'Shipping is not refunded','Ghi nhận hoàn tiền thủ công theo tham chiếu; phương thức thanh toán gốc không xác nhận kênh chuyển tiền hoàn.'=>'A manual refund is recorded against its reference. The original payment method does not identify the refund transfer channel.',
    ];
}
function paxig_store_text($text){
    if (function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi') {
        return $text;
    }
    $map=paxig_store_labels();uksort($map,fn($a,$b)=>strlen($b)<=>strlen($a));return strtr($text,$map);
}
function paxig_store_visible_text($text){
    if (function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi') {
        return $text;
    }
    $trimmed=trim($text);$map=paxig_store_labels();
    if(isset($map[$trimmed]))return str_replace($trimmed,$map[$trimmed],$text);
    if(str_ends_with($trimmed,':')&&isset($map[rtrim($trimmed,':')]))return str_replace($trimmed,$map[rtrim($trimmed,':')].':',$text);
    if(str_starts_with($trimmed,'Tham chiếu:')){
        $text=str_replace(['Tham chiếu:',' · Ngày:'],['Reference:',' · Date:'],$text);
        return preg_replace_callback('/ · Thanh toán: ([^·]+)/u',fn($m)=>' · Payment: '.paxig_store_visible_text($m[1]),$text);
    }
    foreach(['Có thể nhập lại kho','Hàng hư hỏng / không nhập kho'] as $condition)if(str_ends_with($trimmed,' — '.$condition))return str_replace(' — '.$condition,' — '.$map[$condition],$text);
    if(str_starts_with($trimmed,'Kích cỡ '))return preg_replace('/Kích cỡ /u','Size ',$text,1);
    foreach(['Phiếu bán hàng','Phiếu thu','Phiếu trả hàng','Chứng từ hoàn tiền'] as $label)if(str_starts_with($trimmed,$label.' ')||str_starts_with($trimmed,'— '.$label.' —'))return str_replace($label,$map[$label],$text);
    foreach(['Lý do:','Tham chiếu:','Ngày:','Biến thể #'] as $prefix)if(str_starts_with($trimmed,$prefix))return str_replace($prefix,$map[$prefix]??'Variation #',$text);
    return $text;
}
function paxig_store_html($html){
    // Localize visible text and accessible labels, never form keys/values/URLs/data attributes.
    if($html===strip_tags($html))return paxig_store_visible_text($html);
    $html=preg_replace_callback('/>([^<>]+)</u',fn($m)=>'>'.paxig_store_visible_text($m[1]).'<',$html);
    return preg_replace_callback('/\b(aria-label|title|alt|placeholder)=("|\x27)(.*?)\2/u',fn($m)=>$m[1].'='.$m[2].esc_attr(paxig_store_text(html_entity_decode($m[3],ENT_QUOTES,'UTF-8'))).$m[2],$html);
}
add_filter('gettext',function($translated,$text,$domain){
    if(!paxig_store_frontend())return $translated;
    if(function_exists('paxig_get_current_language')&&paxig_get_current_language()==='vi')return $translated;
    if(in_array($domain,['woocommerce','default'],true))return $text;
    return paxig_store_text($translated);
},999,3);
add_filter('ngettext',function($translated,$single,$plural,$number,$domain){
    if(!paxig_store_frontend())return $translated;
    if(function_exists('paxig_get_current_language')&&paxig_get_current_language()==='vi')return $translated;
    return in_array($domain,['woocommerce','default'],true)?($number==1?$single:$plural):$translated;
},999,5);
add_filter('the_title',function($title,$id){
    if(!paxig_store_frontend()||get_post_type($id)==='product')return $title;
    $slug=get_post_field('post_name',$id);
    $is_vi=function_exists('paxig_get_current_language')&&paxig_get_current_language()==='vi';
    if($is_vi){
        $vi_titles=['cart'=>'Giỏ hàng','checkout'=>'Thanh toán','my-account'=>'Tài khoản của tôi','shop'=>'Cửa hàng','huong-dan'=>'Hướng dẫn mua sắm','ve-paxig'=>"Về PAXIG'S SHOES"];
        return $vi_titles[$slug]??$title;
    }
    return ['cart'=>'Cart','checkout'=>'Checkout','my-account'=>'My Account','huong-dan'=>'Shopping Guide','ve-paxig'=>"About PAXIG'S SHOES"][$slug]??paxig_store_text($title);
},999,2);
function paxig_store_term($term){
    if(!paxig_store_frontend()||!$term instanceof WP_Term||!in_array($term->taxonomy,['product_cat','pa_gender','pa_sport','pa_color','pa_kich-thuoc'],true))return $term;
    if(function_exists('paxig_get_current_language')&&paxig_get_current_language()==='vi')return $term;
    $copy=clone $term;$copy->name=paxig_store_text($copy->name);return $copy;
}
add_filter('get_term','paxig_store_term',999);
add_filter('get_terms',function($terms){return is_array($terms)?array_map('paxig_store_term',$terms):$terms;},999);
add_filter('woocommerce_attribute_label',function($label,$name){
    if(!paxig_store_frontend())return $label;
    if(function_exists('paxig_get_current_language')&&paxig_get_current_language()==='vi') {
        return ['pa_kich-thuoc'=>'Kích cỡ','pa_color'=>'Màu sắc','pa_gender'=>'Giới tính','pa_sport'=>'Môn thể thao'][$name]??$label;
    }
    if($name==='pa_kich-thuoc'&&str_contains($label,'<a'))return $label;
    return ['pa_kich-thuoc'=>'Size','pa_color'=>'Color','pa_gender'=>'Gender','pa_sport'=>'Sport'][$name]??paxig_store_text($label);
},999,2);
add_filter('woocommerce_catalog_orderby',function($labels){
    if(!paxig_store_frontend())return $labels;
    if(function_exists('paxig_get_current_language')&&paxig_get_current_language()==='vi') {
        return [
            'menu_order' => 'Thứ tự mặc định',
            'popularity' => 'Mức độ phổ biến',
            'rating'     => 'Điểm đánh giá',
            'date'       => 'Mới nhất',
            'price'      => 'Giá: Thấp đến Cao',
            'price-desc' => 'Giá: Cao đến Thấp',
        ];
    }
    return ['menu_order'=>'Featured','popularity'=>'Popularity','rating'=>'Rating','date'=>'Newest','price'=>'Price: Low to High','price-desc'=>'Price: High to Low'];
},999);
add_filter('woocommerce_page_title',function($title){
    if(!paxig_store_frontend())return $title;
    if(function_exists('paxig_get_current_language')&&paxig_get_current_language()==='vi') {
        if(is_search())return 'Kết quả tìm kiếm';
        return $title;
    }
    if(is_search())return 'Search Results';
    return paxig_store_text($title)==='Tất cả giày'?'All Shoes':paxig_store_text($title);
},999);
add_filter('woocommerce_get_breadcrumb',function($crumbs){if(paxig_store_frontend())foreach($crumbs as &$crumb)$crumb[0]=paxig_store_text($crumb[0]);return $crumbs;},999);
add_filter('wp_get_nav_menu_objects',function($items){if(paxig_store_frontend())foreach($items as &$item){$item=clone $item;$item->title=paxig_store_text($item->title);}return $items;},999);
add_filter('wp_nav_menu_items',function($html,$args){if(paxig_store_frontend()&&$args->theme_location==='primary')$html.='<li class="paxig-mobile-account"><a href="'.esc_url(wc_get_page_permalink('myaccount')).'">My Account</a></li>';return $html;},999,2);
add_filter('wp_get_attachment_image_attributes',function($attrs){if(paxig_store_frontend())$attrs['alt']=str_replace('Giày minh họa AI cho khóa luận','Illustrative demo footwear',paxig_store_text($attrs['alt']??''));return $attrs;},999);
add_filter('woocommerce_cart_shipping_method_full_label',fn($label)=>paxig_store_frontend()?paxig_store_html('<span>'.$label.'</span>'):$label,999);
add_filter('woocommerce_order_shipping_to_display',fn($label)=>paxig_store_frontend()?paxig_store_text($label):$label,999);
add_filter('widget_title',fn($title)=>paxig_store_frontend()?paxig_store_text($title):$title,999);
foreach(['woocommerce_gateway_title','woocommerce_gateway_description','woocommerce_order_get_payment_method_title'] as $filter)add_filter($filter,fn($text)=>paxig_store_frontend()?paxig_store_text($text):$text,999);
function paxig_store_demo_image_credit_html($product_id){
    $title=sanitize_text_field(get_post_meta($product_id,'_paxig_demo_image_title',true));
    $author=sanitize_text_field(get_post_meta($product_id,'_paxig_demo_image_author',true));
    $license=sanitize_text_field(get_post_meta($product_id,'_paxig_demo_image_license',true));
    $license_url=esc_url(get_post_meta($product_id,'_paxig_demo_image_license_url',true));
    $source_url=esc_url(get_post_meta($product_id,'_paxig_demo_image_source_url',true));
    if(!$title||!$author||!$license||!$source_url)return '';
    $credit='<span>Photo: <a href="'.$source_url.'" rel="noopener noreferrer" target="_blank">'.esc_html($title).'</a> by '.esc_html($author).' (resized for catalog)</span>';
    $credit.=$license_url?' · <a href="'.$license_url.'" rel="license noopener noreferrer" target="_blank">'.esc_html($license).'</a>':' · '.esc_html($license);
    return '<p class="paxig-demo-image-credit">'.$credit.'</p>';
}
add_action('woocommerce_after_shop_loop_item_title',function(){
    if(!paxig_store_frontend())return;
    global $product;
    if($product instanceof WC_Product&&str_contains($product->get_sku(),'PAXIG-DEMO-'))echo paxig_store_demo_image_credit_html($product->get_id());
},11);
add_filter('the_content',function($content){
    if(!paxig_store_frontend())return $content;
    $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
    if(is_product()){
        global $product;if($product instanceof WC_Product&&str_contains($product->get_sku(),'PAXIG-DEMO-')){
            if ($is_vi) {
                return '<p>Khám phá '.esc_html($product->get_name()).' từ bộ sưu tập PAXIG demo.</p><p>Chọn kích cỡ EU của bạn bằng cách tham khảo bảng kích thước. Đây là hình ảnh minh họa cho giày mẫu; không xác nhận kiểu dáng, thông số hay giá thực tế.</p>'.paxig_store_demo_image_credit_html($product->get_id()).'<p>Yêu cầu hủy và trả hàng tuân theo trạng thái đơn hàng hiện tại và quyền hạn tài khoản.</p>';
            }
            return '<p>Explore '.esc_html($product->get_name()).' from the PAXIG demo collection.</p><p>Select your EU size using the size guide. This is an illustrative shoe photo; it does not confirm the exact model, specifications, or price.</p>'.paxig_store_demo_image_credit_html($product->get_id()).'<p>Cancellation and return requests follow the current order status and account permissions.</p>';
        }
    }
    if(is_page('huong-dan')){
        if ($is_vi) {
            return '<h2>Tìm kích cỡ EU của bạn</h2><p>Đo chiều dài bàn chân và đối chiếu với kích cỡ EU bạn thường mang. Bảng size demo không thay thế bảng size chính thức của nhà sản xuất.</p><h2>Vận chuyển</h2><p>'.esc_html(paxig_shipping_summary()).'</p><h2>Hủy đơn &amp; Đổi trả</h2><p>Vào Tài khoản → Đơn hàng → Xem chi tiết để gửi yêu cầu hợp lệ. Đội ngũ chúng tôi sẽ xem xét yêu cầu theo chính sách hiện hành.</p><p>Đây là cửa hàng mô phỏng. Vui lòng không thực hiện thanh toán thật.</p>';
        }
        return '<h2>Find Your EU Size</h2><p>Measure your foot length and compare it with your usual EU size. Demo size values do not replace the manufacturer’s size chart.</p><h2>Shipping</h2><p>'.esc_html(paxig_shipping_summary()).'</p><h2>Cancellation &amp; Returns</h2><p>Open My Account → Orders → View Details to submit an eligible request. Our team reviews requests under the existing order policy.</p><p>This is a demonstration store. Do not make a real payment.</p>';
    }
    if(is_page('ve-paxig')){
        if ($is_vi) {
            return '<p>PAXIG’S SHOES là cửa hàng giày thể thao thử nghiệm. Tên thương hiệu và mẫu mã mô tả danh mục mẫu và không ngụ ý đại lý phân phối chính thức.</p><p>Hình ảnh sản phẩm là hình ảnh minh họa danh mục; thông tin nguồn và giấy phép xuất hiện cùng từng mục. Sản phẩm, giá cả và đơn hàng là dữ liệu thử nghiệm. Không chấp nhận thanh toán hoặc mua hàng thật.</p>';
        }
        return '<p>PAXIG’S SHOES is a demonstration footwear store. Brand and model names describe the demo catalog and do not imply an official dealership.</p><p>Product photos are illustrative catalog images; source and license credits appear with each listing. Products, prices and orders are demonstration data. No real payments or purchases are accepted.</p>';
    }
    return $content;
},999);
add_action('wp',function(){
    if(!paxig_store_frontend()||!is_account_page()||!class_exists('Paxig\\Access\\CustomerAccount'))return;
    $class='Paxig\\Access\\CustomerAccount';
    foreach(['woocommerce_order_details_after_order_table'=>'render','woocommerce_my_account_my_orders_column_paxig_fulfillment'=>'fulfillmentColumn','woocommerce_my_account_my_orders_column_paxig_payment'=>'paymentColumn'] as $hook=>$method){
        remove_action($hook,[$class,$method],10);
        add_action($hook,function($order)use($class,$method){ob_start();call_user_func([$class,$method],$order);echo paxig_store_html(ob_get_clean());},10);
    }
},90);
add_filter('woocommerce_my_account_my_orders_columns',function($columns){if(paxig_store_frontend())foreach($columns as &$label)$label=paxig_store_text($label);return $columns;},999);
add_filter('woocommerce_my_account_my_orders_actions',function($actions){if(paxig_store_frontend())foreach($actions as &$action){$action['name']=paxig_store_text($action['name']);if(isset($action['aria-label']))$action['aria-label']=paxig_store_text($action['aria-label']);}return $actions;},999);
// The original print handler still validates nonce, ownership and document visibility.
// Only its customer HTML is localized; finance and REST responses stay untouched.
add_action('template_redirect',function(){
    if(!paxig_store_frontend()||!isset($_GET['paxig_print_document'])||!in_array('customer',wp_get_current_user()->roles,true))return;
    ob_start(function($html){
        $values=[];
        $html=preg_replace_callback('/(<strong>(?:Họ tên:|Email:|Điện thoại:|Địa chỉ:)<\/strong>)([^<]*)(<\/p>)/u',function($m)use(&$values){$key='PAXIG_SNAPSHOT_VALUE_'.count($values);$values[$key]=$m[2];return $m[1].$key.$m[3];},$html);
        return strtr(str_replace('lang="vi"','lang="en-US"',paxig_store_html($html)),$values);
    });
},-1);
