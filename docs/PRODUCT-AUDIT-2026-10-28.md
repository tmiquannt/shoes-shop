# Audit sản phẩm PAXG'Shop — 28/10/2026

## Kết luận tại thời điểm audit ban đầu

**Chưa đủ điều kiện phát hành để bán hàng thật.** Dự án có nền tảng storefront khá đầy đủ, nhưng còn lỗi trực tiếp ở đăng ký, thêm giỏ, cập nhật giỏ, thao tác mua trên mobile và xử lý kết quả thanh toán. Cấu hình preview cũng chưa đáp ứng giao hàng cho sản phẩm vật lý.

Audit này kiểm tra code hiện tại, cấu hình WordPress đang chạy và một số hành trình trình duyệt; không sửa chức năng. WooCommerce tiếp tục sở hữu quản trị sản phẩm, đơn hàng, tồn kho và khách hàng. Không cần xây lại admin để hoàn thành sản phẩm.

**Cập nhật sau yêu cầu sửa ngày 28/10:** P1-01 (registration lifecycle), P1-02 (double add) và P2-01 (drawer xóa thông báo lỗi) đã được sửa và kiểm chứng riêng. Giữ xác minh email; cấu hình SMTP và VNPAY do chủ shop thực hiện sau. Xem [kết quả sửa và kiểm thử](/home/tmi_pc_2025_quan/Desktop/shoes-shop/docs/BUGFIX-REGISTRATION-CART-2026-10-28.md). Các phần bên dưới lưu bằng chứng của snapshot audit ban đầu; các phát hiện khác vẫn chưa được xử lý trong lần sửa này.

### Phạm vi và mốc kiểm tra

- Workspace: `/home/tmi_pc_2025_quan/Desktop/shoes-shop`.
- HEAD: `2f47495ead7d1410fb6e2a34217318069c92515a`; branch `main`, ahead origin 4 commit.
- Audit **bao gồm thay đổi chưa commit**, gồm cả shared product card mới. HEAD riêng lẻ không đại diện đầy đủ cho bản được kiểm tra.
- SHA256 manifest 153 file trước khi thêm báo cáo: `423f591eef91c63327b4d1204fad1d0cf9ca7847ad6707564c700065c65b107b`.
- Preview: `http://127.0.0.1:8082`; WordPress `6.8.3`, WooCommerce `10.7.0`; theme bind-mount từ workspace hiện tại.
- Preview chỉ được đọc và duyệt bằng session mới. Không tạo tài khoản, đơn hàng, sửa cấu hình hoặc thêm giỏ ở preview.
- Test có ghi dữ liệu chạy riêng ở `8087`, DB `paxig_qa`, network ứng dụng internal, email và WordPress HTTP bị chặn, chỉ COD. Sản phẩm/địa chỉ/email đều là fixture giả.
- Không kiểm thử hệ thống production, giao dịch ngân hàng, gửi email thật, triển khai hoặc push/commit.

## 1. Cấu hình thực tế đã xác nhận

| Hạng mục | Kết quả hiện tại | Ý nghĩa |
| --- | --- | --- |
| Plugin active | WooCommerce và `paxig-vnpay` | SePay chưa cài trong runtime này |
| Phương thức thanh toán khả dụng | Chỉ `cod` | VNPAY có code/plugin nhưng đang tắt; chưa thể chọn để trả tiền |
| SMTP của theme | `paxig_email_verification_smtp_is_configured() = false` | Chưa có cấu hình gửi thư xác minh; không coi reset password/thư đơn hàng đã hoạt động |
| Checkout | Page 7 dùng WooCommerce Blocks | Test checkout cổ điển không thay thế được acceptance cho Blocks |
| Guest checkout | Bật; tạo account tại checkout tắt | Phù hợp hợp đồng guest purchase trong `docs/tasks.md` |
| Giao hàng | Không có zone cấu hình; zone mặc định 0 cũng không có phương thức | Năm sản phẩm vật lý hiện không có phương án giao hàng |
| Địa điểm/tiền tệ | `US:CA` / `VND` | Cần cấu hình thị trường và địa chỉ cửa hàng thực tế |
| Thuế | Tắt | Cần quyết định theo mô hình kinh doanh; chưa kết luận phải bật hay tính mức nào |
| Sản phẩm | 5 simple, đều physical, không quản lý stock, cùng image ID 10 | Dữ liệu demo; chưa có fixture size/variation đại diện cho bán giày thật |
| Privacy / returns | Hai trang đang draft; URL công khai trả trang không tìm thấy | Nội dung hỗ trợ/chính sách chưa sẵn sàng |
| Terms | WooCommerce chưa gán trang | Chưa có nội dung điều khoản để khách đọc |

Nguồn: WordPress/WooCommerce bootstrap chỉ đọc, API lấy options/gateways/products/shipping zones, đối chiếu trang công khai. Không in thông tin bí mật SMTP hoặc merchant.

## 2. Lỗi ưu tiên P1 — sửa trước khi phát hành

### P1-01 — Đăng ký bỏ qua validation và xác minh email

- Nguồn: [email-verification.php:53](/home/tmi_pc_2025_quan/Desktop/shoes-shop/includes/email-verification.php:53), [auth-ui.php:100](/home/tmi_pc_2025_quan/Desktop/shoes-shop/includes/auth-ui.php:100).
- Các gate dùng `is_account_page()`. Woo xử lý registration ở `wp_loaded`, trước main query của WordPress, nên điều kiện này trả false đúng lúc xử lý form.
- Hệ quả: gate SMTP, metadata pending verification, gửi link xác minh, validation tên/điện thoại/password confirmation và lựa chọn dùng password khách nhập bị bỏ qua. Cơ chế Woo có thể đăng nhập khách mới ngay sau tạo account, trái mô tả trong tài liệu.
- Đã xác nhận vòng đời trên runtime bằng probe GET: chỉ đặt cờ `register` trong callback kiểm tra và xóa trước handler tạo account; không gửi đăng ký thật.

```text
wp_loaded19: account_page=false; registration_predicate=false;
             smtp_configured=false; smtp_gate_errors=[]
after_main_query: account_page=true; registration_predicate=true
```

- Sửa: xác định ngữ cảnh registration từ handler/form hợp lệ, không phụ thuộc main query; kiểm thử qua lifecycle thật với account giả, SMTP thiếu, password không khớp và chưa xác minh.

### P1-02 — Một lần bấm thêm sản phẩm có thể thêm hai lần

- Nguồn: [single-product.js:114](/home/tmi_pc_2025_quan/Desktop/shoes-shop/assets/js/single-product.js:114), [mini-cart.php:416](/home/tmi_pc_2025_quan/Desktop/shoes-shop/includes/mini-cart.php:416).
- AJAX vẫn mang field native `add-to-cart`. Woo `WC_Form_Handler` xử lý nó ở `wp_loaded`, rồi custom AJAX handler lại gọi `add_to_cart`.
- **Tái hiện trên trình duyệt QA:** giỏ ban đầu 0; một click sản phẩm fixture ID 12, quantity 1; Store API trả `items_count=2`, một dòng quantity 2.
- Với giới hạn tồn kho hoặc sold-individually, lần native có thể thành công trong khi lần custom báo lỗi.
- Sửa: chuẩn hóa `product_id`, bỏ field kích hoạt native khỏi request AJAX; assert delta số lượng chính xác, cả trường hợp tồn kho sát giới hạn và request thất bại.

### P1-03 — Đổi quantity ở trang giỏ không bật nút cập nhật

- Nguồn: [page-cart.php:80](/home/tmi_pc_2025_quan/Desktop/shoes-shop/page-cart.php:80).
- Row không có class `.cart_item`; Woo `wc-cart` chỉ bật nút khi thay đổi input khớp `.woocommerce-cart-form .cart_item :input`.
- **Tái hiện QA:** quantity `2 → 3`, đã phát cả `input` và `change`; `update_cart.disabled` vẫn `true`.
- Sửa: khôi phục selector/hook tương thích Woo hoặc cung cấp cơ chế cập nhật hoàn chỉnh; xác nhận quantity và totals thực sự đổi ở server.
- Không kết luận tổng tiền bị stale sau manual POST: thiếu selector của native AJAX khiến form hiện dùng nhánh submit thông thường; hai cơ chế cần phân biệt.

### P1-04 — Thanh điều hướng mobile che nút mua cố định

- Nguồn: [single-product.css:577](/home/tmi_pc_2025_quan/Desktop/shoes-shop/assets/css/single-product.css:577), [style-3.css:1473](/home/tmi_pc_2025_quan/Desktop/shoes-shop/assets/css/style-3.css:1473).
- Fresh navigation ở 390×844: nút sticky có tọa độ y≈787–831; nav chiếm y=780–844. Hit-test tâm nút trả link My Account của nav.
- Sticky dùng `bottom:0; z-index:999`, nav dùng `bottom:0; z-index:9999`.
- Sửa: bố trí hai thanh không đè nhau, tính safe area; xác nhận click thật đúng nút ở trạng thái sticky đang hiển thị.

### P1-05 — VNPAY xác nhận thành công dù lưu thanh toán thất bại

- Nguồn: [paxig-vnpay.php:410](/home/tmi_pc_2025_quan/Desktop/shoes-shop/plugins/paxig-vnpay/paxig-vnpay.php:410).
- IPN bỏ qua kết quả `payment_complete()`, vẫn thêm ghi chú đã thanh toán và trả `RspCode=00`.
- Probe in-memory chạy callback thật với chữ ký fixture hợp lệ và `payment_complete=false`: response `00`, trạng thái vẫn chưa paid, note vẫn báo xác nhận.
- WooCommerce cài đặt hiện tại có thể trả false khi completion gặp exception. [Tài liệu VNPAY](https://sandbox.vnpayment.vn/apis/docs/thanh-toan-pay/pay.html) quy định `00/02` kết thúc retry, nên có thể nhận tiền nhưng đơn chưa được ghi nhận đúng.
- Sửa: chỉ acknowledge thành công sau khi kiểm tra kết quả và trạng thái persisted phù hợp; lỗi xử lý phải được ghi log và phản hồi theo cơ chế retry. Cần thêm policy cho callback đến muộn, order cancelled/đã đổi payment method và xử lý song song.
- Đây là lỗi tiềm ẩn phải sửa **trước khi bật VNPAY**; không có giao dịch ngân hàng thật nào được thực hiện trong audit.

### P1-06 — Chưa cấu hình phương thức giao hàng cho hàng vật lý

- Nguồn runtime: cả năm sản phẩm `virtual=false`; shipping zones trống, fallback zone 0 methods trống.
- Không có rate để khách chọn cho những đôi giày đang bán. Dòng “calculated at checkout” không thay thế được cấu hình giao hàng.
- Sửa bằng cấu hình WooCommerce theo khu vực kinh doanh: địa chỉ shop, quốc gia bán/giao, ít nhất một phương thức hợp lệ, phí và ngưỡng miễn phí nếu có. Không bắt buộc phải viết tích hợp hãng vận chuyển nếu vận hành thủ công đã đáp ứng nhu cầu.
- Acceptance phải dùng sản phẩm physical và địa chỉ trong/ngoài vùng phục vụ; fixture virtual không chứng minh giao hàng.

## 3. P2 và các khoảng trống hoàn thiện

| ID | Phát hiện / bằng chứng | Việc cần làm |
| --- | --- | --- |
| P2-01 | [mini-cart.js:180](/home/tmi_pc_2025_quan/Desktop/shoes-shop/assets/js/mini-cart.js:180) gọi `showAlert` rồi `openDrawer`; mở drawer lại xóa alert. Chạy production JS trong Node VM cho kết quả drawer mở nhưng error trống/ẩn. | Mở drawer trước rồi hiển thị lỗi; kiểm tra lỗi stock, variation và lỗi kết nối. |
| P2-02 | [page-cart.php:18](/home/tmi_pc_2025_quan/Desktop/shoes-shop/page-cart.php:18) không gọi hook/notices của Woo hoặc outlet tương đương; QA populated cart cũng không có notices wrapper. | Hiển thị lỗi/thành công khi áp coupon, cập nhật stock, xóa/undo; test cả lỗi và thành công. |
| P2-03 | [page-cart.php:151](/home/tmi_pc_2025_quan/Desktop/shoes-shop/page-cart.php:151) có tổng tiền nhưng thiếu dòng coupon đã áp, mức giảm, nút gỡ coupon và fee rows khi có. | Giải thích đầy đủ các thành phần tổng tiền; gỡ được mã đang dùng. Coupon lifecycle chưa được chạy trong audit này. |
| P2-04 | [paxig-vnpay.php:393](/home/tmi_pc_2025_quan/Desktop/shoes-shop/plugins/paxig-vnpay/paxig-vnpay.php:393) trả `02` ngay khi order paid, trước ghi nhận transaction thứ hai. Probe hai reference/transaction thành công: chỉ transaction đầu được ghi. | Phân biệt callback lặp của cùng transaction với khoản trả tiền khác; lưu/surfacing khoản trả thêm để đối soát/hoàn tiền. Chưa kiểm thử race đồng thời. |
| P2-05 | [single-product.css:996](/home/tmi_pc_2025_quan/Desktop/shoes-shop/assets/css/single-product.css:996) ép related grid 4 cột bằng `!important`, thắng rule mobile 2 cột. Fresh mobile: card rộng 71.5px; giá cần 170px và bị `overflow:hidden` cắt. | Khôi phục breakpoint của related products; test đầy đủ giá/tên trên card ở 390px. |
| P2-06 | [store-ui.php:208](/home/tmi_pc_2025_quan/Desktop/shoes-shop/includes/store-ui.php:208) thay nội dung trang `huong-dan`/`ve-paxig` và mô tả SKU demo bằng nội dung demonstration. Các override này không kiểm tra cờ demo. | Bỏ/gate override khi lên shop thật và giữ nội dung merchant. Cờ `paxig_ui_demo` **có** được đọc trong `ui-compat.php` cho notice/privacy text, nhưng không điều khiển các override ở đây. |
| P2-07 | Catalog hiện dùng chung một ảnh cho cả 5 sản phẩm, mô tả sample; không có variable product đại diện. | Dữ liệu thật theo SKU/size/màu, ảnh đúng sản phẩm và quyền sử dụng, giá/tồn kho đúng. Không coi script seed tồn tại là dữ liệu đã seed. |
| P2-08 | Privacy/returns draft, terms chưa gán; footer ẩn link không có trang published. | Hoàn thiện thông tin shop, kênh liên hệ, giao hàng, đổi trả/bảo mật/điều khoản rồi publish và liên kết. Không bịa nội dung vận hành hoặc thông tin pháp lý của merchant. |
| P2-09 | [reference-ui.php:154](/home/tmi_pc_2025_quan/Desktop/shoes-shop/includes/reference-ui.php:154) size-guide modal chỉ toggle class/aria-hidden; [header.js:225](/home/tmi_pc_2025_quan/Desktop/shoes-shop/assets/js/header.js:225) search modal thiếu trap/restore focus. | Hoàn thiện focus vào/ra, Tab, Escape; label rõ cho search; kiểm tra keyboard thực tế. |
| P2-10 | [reference-ui.js:13](/home/tmi_pc_2025_quan/Desktop/shoes-shop/assets/js/reference-ui.js:13) và [header.js:58](/home/tmi_pc_2025_quan/Desktop/shoes-shop/assets/js/header.js:58) có nhãn English hardcode. | Localize cả aria labels/JS theo VI/EN; test cả checkout và account, không chỉ tiêu đề trang. |
| P2-11 | Ba script `test-pdp-ajax-cart.mjs`, `test-mobile-pdp-ajax-cart.mjs`, `verify-journey.mjs` trong `tests/isolated-qa` thực tế hardcode `8082`, có ghi cart, không guard QA. | Chuyển sang target synthetic có safety header; không chạy nhầm preview. Audit không chạy ba script này. |
| P2-12 | [verify-journey.mjs:30](/home/tmi_pc_2025_quan/Desktop/shoes-shop/tests/isolated-qa/verify-journey.mjs:30) log size/CTA/cart mà không assert. Test PDP chỉ đòi số lượng tăng nên vẫn pass khi thêm gấp đôi. Một số unit test copy implementation thay vì chạy production script. | Assertion exact delta/state và test production code; không kế thừa nhãn PASS cũ cho source hiện tại. |
| P2-13 | Không thấy CI/runner tổng, dependency manifest/lock cho bộ QA, quy trình package/deploy/backup/restore/rollback trong repo. Browser scripts phụ thuộc đường dẫn skill bên ngoài repo. | Một lệnh kiểm tra có thể tái lập, chốt version/hash plugin, runtime và browser runner portable; diễn tập restore/rollback ở staging. |

## 4. Ma trận chức năng sản phẩm

| Nhóm | Hiện trạng | Điều kiện hoàn thiện |
| --- | --- | --- |
| Home/navigation/catalog | Có giao diện; trang home/shop/PDP/search/no-results mở được | Test category/gender/price/sort/pagination với dữ liệu đa dạng, menu mobile và trạng thái rỗng |
| PDP/gallery/reviews/size | Có code, có trang thực tế | Sửa mobile CTA/related; variation nhiều thuộc tính, hết size/hết stock, review theo policy merchant |
| Wishlist | Có guest/account storage và test tập trung | Kiểm thử đăng nhập/đăng xuất, hai account, reload, move-from-cart trên source cuối cùng |
| Cart/drawer | Có đủ bề mặt nhưng lỗi P1/P2 ở trên | Một click đúng quantity, cập nhật/xóa, lỗi stock, coupon, totals rõ và đúng |
| Guest checkout/COD | Classic synthetic flow pass trong audit | Kiểm thử **Blocks đang triển khai**, physical shipping, lỗi địa chỉ, retry/double-submit và receipt |
| Online payment | VNPAY có plugin/Blocks adapter; SePay cần plugin ngoài | Sửa callback, cấu hình merchant, callback HTTPS, sandbox/đối soát và kiểm thử tích hợp phù hợp provider |
| Đăng ký/login/reset | Có form theo Woo; đăng ký hiện có lỗi lifecycle | Pending verification, resend/expiry, password khách nhập, login/reset và mail delivery thật ở staging |
| Profile/address/orders | Có native Woo account surfaces | Kiểm thử account fixture, ownership và các trạng thái đơn; không suy từ UI sang backend đã hoạt động |
| Sau mua hàng | Woo có order status; post-purchase signup/order claim trong hợp đồng cũ chưa được triển khai | Bổ sung hành động tùy chọn sau mua và claim an toàn nếu giữ yêu cầu `docs/tasks.md`; không tự gắn đơn chỉ vì trùng email |
| Đổi trả/tracking/hỗ trợ | Chưa có bằng chứng workflow hoàn chỉnh | Xác định kênh vận hành đủ dùng: hướng dẫn/contact/thủ công hoặc plugin. Không mặc định cần xây một ERP mới |
| Nội dung/VI–EN/a11y | Có nền tảng, còn demo và nhãn hardcode/modal thiếu keyboard behavior | Nội dung shop thật, hình đúng SKU, chính sách published, kiểm tra cả ngôn ngữ và keyboard |
| SEO/performance | Có title/canonical một số trang; search/cart/account có noindex phù hợp ở mẫu đã xem | Home/shop chưa có meta description/canonical trong mẫu; chốt URL/ngôn ngữ/sitemap/schema theo môi trường thật, đo tải và cache/session ở staging |
| Vận hành/release | Có môi trường preview và bộ QA cách ly | Chốt config, đóng gói theme/plugin, log/monitoring, mail, cron, backup/restore/rollback và smoke sau deploy |

## 5. Đã kiểm chứng và giới hạn

### Kiểm tra đã chạy

- PHP syntax: 38 file pass; JavaScript syntax: 10 file production pass.
- Node unit suite: 19/19 pass; PHP cart: 23/23; checkout-registration: 8/8; PHP wishlist pass.
- `git diff --check`: pass trước khi thêm báo cáo.
- Browser GET: home, shop, PDP, search có/không kết quả, giỏ rỗng, checkout giỏ rỗng, login, register, forgot password, policy URLs.
- Home inspector ở 1440/768/390; screenshot home/shop/PDP/account/cart tại desktop và mobile. Mẫu đã xem không có body horizontal overflow, broken image hoặc JS exception. Related-card clipping bên trong vẫn tồn tại dù body không overflow.
- Đã nhìn screenshot thật và hit-test mobile. Inspector còn báo các house-style warnings/failures; không dùng khẩu vị thiết kế như yêu cầu bắt buộc để bác giao diện hiện có. Các nghi vấn tự động về covered/reduced-hidden text có thể liên quan menu đóng và cần kiểm tra riêng trước khi coi là bug.
- QA an toàn xác nhận: DB local synthetic, mail interception, HTTP blocking, internal network, COD-only.
- QA PDP/cart: một click tăng 0→2; đổi quantity vẫn disabled; đã xóa item, cart cuối 0.
- QA classic guest checkout: invalid nonce bị từ chối; lỗi required hiển thị; submit form thật không tạo account; receipt đúng sản phẩm/250000 VND/COD; giỏ về 0, vẫn logged-out.
- Đơn giả #15 đã được xóa bằng Woo CRUD sau khi xác nhận đúng fixture; tồn kho phục hồi 5; số đơn fixture cuối 0. Ba container QA đã dừng trở lại.

### Chưa đủ bằng chứng để kết luận pass

- Checkout Blocks với giỏ hàng physical, shipping/tax/coupon thực tế.
- Registration end-to-end, nhận mail, verify/resend/expire, password reset và authenticated account mutations.
- Giao dịch VNPAY/SePay thật hoặc sandbox round trip, webhook qua Internet, callback đồng thời, đối soát/refund.
- Variant/shared stock, hai khách tranh tồn, cache/CDN/session và plugin coexistence trên staging/production.
- Guest order claim, trả hàng/tracking đầy đủ, quyền xem đơn giữa nhiều account.
- Full WCAG, Core Web Vitals/load test, vulnerability/version database scan và hạ tầng production. Không coi syntax pass là các mục này đã pass.

### Bằng chứng cục bộ

- [Manifest source](/tmp/shoes-audit-20261028/tree-manifest.json).
- [Browser measurements và nội dung trang](/tmp/shoes-audit-20261028/browser-results.json).
- [Inspector home](/tmp/shoes-audit-20261028/home/report.json).
- [Desktop home](/tmp/shoes-audit-20261028/home/desktop-fold.jpg), [mobile home](/tmp/shoes-audit-20261028/home/mobile-fold.jpg).
- [Mobile PDP fresh](/tmp/shoes-audit-20261028/pdp-mobile-fresh-related.jpg).
- [Kết quả giỏ QA](/tmp/shoes-audit-20261028/isolated-cart-results.json), [ảnh giỏ QA](/tmp/shoes-audit-20261028/isolated-cart.jpg).
- [Receipt COD giả](/tmp/paxig-qa-classic-receipt.jpg).

Evidence dưới `/tmp` là artifact phiên audit, có thể bị hệ thống dọn; các kết quả chính đã được ghi trong báo cáo này. QA cũ tham chiếu plugin trong `/tmp` đã mất; audit phục hồi runtime QA bằng bản sao code WooCommerce 10.7.0 từ preview, không sao chép DB/customer data. Đây cũng là lý do cần dependency pin và setup tái lập.

## 6. Lộ trình hoàn thiện theo thứ tự

1. **Khóa lỗi cốt lõi:** sửa registration lifecycle, double add, cart update và mobile CTA; regression test chạy qua code thật và handler lifecycle thật.
2. **Hoàn chỉnh shopping:** thông báo lỗi, coupon/fees/totals, responsive related cards, dữ liệu variation/stock/ảnh đúng SKU; test guest/account ở mobile/desktop.
3. **Cấu hình để bán được:** địa chỉ/vùng bán/giao/phí, nội dung merchant, SMTP, policies; quyết định rõ thuế và cách hỗ trợ/đổi trả. Đây là phần cần dữ liệu kinh doanh thật, không tự điền thông tin giả.
4. **Nghiệm thu checkout đang dùng:** synthetic Blocks + physical shipping, validation/recovery/duplicate submit/receipt; giữ guest checkout và Woo là nguồn dữ liệu chính.
5. **Hoàn thiện payment và sau mua:** sửa IPN/đối soát trước khi bật online gateway; nghiệm thu mail/account/order claim; chọn kênh tracking/returns đủ cho vận hành.
6. **Chuẩn bị phát hành:** keyboard/VI–EN/SEO/performance/cache checks; CI và artifacts; staging acceptance, backup/restore/rollback, smoke sau deploy theo đúng source cuối cùng.

**Gate hoàn thành:** không còn P1 mở; luồng mua hàng trên checkout thực tế pass bằng dữ liệu đại diện; email/payment xác nhận bằng bằng chứng tích hợp; nội dung/config shop thật đầy đủ; có gói triển khai và cách khôi phục. Những tính năng mở rộng như loyalty, social login hay tự động hóa kho không tự động là yêu cầu của lần audit này.
