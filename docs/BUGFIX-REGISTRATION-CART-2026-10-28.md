# Sửa đăng ký và thêm giỏ — 28/10/2026

## Phạm vi đã chốt

- Giữ xác minh email khi đăng ký. Chủ shop sẽ cấu hình SMTP sau.
- Mỗi thao tác thêm giỏ chỉ cộng đúng số lượng khách chọn một lần.
- Cấu hình và code VNPAY nằm ngoài lần sửa này.
- Giữ guest checkout, native WooCommerce ownership, UI đang chỉnh và các thay đổi chưa commit khác.

## Current / Expected / Unchanged

| Nhóm | Current — lỗi đã tái hiện | Expected | Unchanged |
| --- | --- | --- | --- |
| Đăng ký | Gate dùng `is_account_page()` trước main query nên bỏ qua validation và pending verification | Nhận diện đúng form Woo có nonce hợp lệ ngay lúc xử lý; validate dữ liệu/password; tạo pending account và chặn login trước xác minh | Account cũ, account tạo qua admin, guest purchase, token hết hạn/resend và cấu hình SMTP do merchant quản lý |
| SMTP chưa cấu hình | Gate hiện bị bỏ qua vì lỗi lifecycle | Theo contract hiện có: báo lỗi trước khi tạo account, không âm thầm bỏ xác minh | Không thêm credential hoặc gửi email thật |
| Thêm giỏ | Payload AJAX kích hoạt cả native `add-to-cart` và custom handler, tăng 0→2 khi chọn 1 | Chuẩn hóa product ID rồi dùng duy nhất custom AJAX handler; quantity/variation giữ đúng | Form không JavaScript tiếp tục native POST; không đổi giá/tồn kho/checkout |
| Lỗi kết nối thêm giỏ | Catch tự submit lại form dù request trước có thể đã được server xử lý | Không tự gửi lại thao tác thay đổi giỏ khi kết quả chưa rõ; thông báo lỗi qua UI hiện có và refresh read-only trạng thái giỏ | Không thêm cơ chế retry tự động hoặc đổi backend cart API |

Hướng sửa nhỏ nhất: dùng cùng ngữ cảnh form hợp lệ với Woo trước main query; loại field native khỏi AJAX sau khi lấy product ID; bỏ auto-submit khi kết nối không rõ kết quả. Không tắt xác minh hoặc giảm số lượng trả về để che lỗi xử lý hai lần.

## Kiểm thử và ranh giới

| Yêu cầu | Kiểm tra hồi quy | Kiểm tra thực tế |
| --- | --- | --- |
| REG-1: validation chạy trước main query | PHP test chạy production callbacks với account-page=false và nonce hợp lệ | POST form thật trong QA, thiếu dữ liệu/password khác nhau |
| REG-2: giữ xác minh và chặn pending login | SMTP thiếu/configured, pending metadata, authenticate filters, ngữ cảnh không phải register | QA mail interception, pending account, link xác minh, password login |
| CART-1: mỗi click đúng một lần | Node VM chạy production JS, kiểm tra payload và số fetch | Browser + Store API trên fixture QA: chọn1 tăng1, click tiếp tăng1 |
| CART-2: không tự gửi lại khi lỗi mạng | Promise rejection và response lỗi, kiểm tra không native submit | Fixture QA; không dùng preview/customer cart |
| INV-1: không đổi guest checkout | Suite checkout-registration hiện có | Classic guest COD fixture nếu thay đổi tác động tới auth/checkout |

Test mutation chỉ dùng DB `paxig_qa`, email `@example.invalid`, mail/HTTP interception và network internal; teardown xóa đúng account/cart/order do test tạo. Preview `8082` không dùng cho mutation. Chưa có bằng chứng gửi/nhận email thật cho tới khi merchant cấu hình và kiểm tra SMTP.

## Kết quả

### Thay đổi

- `includes/email-verification.php`: nhận diện form registration có field/nonce hợp lệ theo Woo trước main query; giữ gate SMTP và pending verification.
- `includes/auth-ui.php`: dùng cùng ngữ cảnh đã xác thực để chọn password khách nhập, validate tên/điện thoại/password confirmation và lưu thông tin.
- `assets/js/single-product.js`: bỏ `add-to-cart` khỏi payload AJAX sau khi chuẩn hóa product ID; bỏ native resubmit khi lỗi mạng/JSON, refresh Woo fragments chỉ đọc để đồng bộ lại giỏ.
- `assets/js/mini-cart.js`: mở drawer trước rồi hiển thị lỗi, tránh lỗi bị xóa khi mở.
- `functions.php`: thông báo VI/EN khi chưa xác nhận được kết quả thêm giỏ.
- Thêm hai regression suites `tests/email-verification.php` và `tests/single-product-cart.test.js`; không đổi markup, CSS, gateway settings hoặc code VNPAY.

### RED / GREEN

- Auth: 8 lỗi tái hiện trước sửa → 13/13 test pass.
- Kiểm tra bổ sung khi rà UI: nhận diện field submit theo đúng semantics `isset` của Woo, kể cả `register[]`; regression mới RED → GREEN, auth suite hiện 14/14. POST malformed này qua HTTP QA vẫn bị gate SMTP chặn và không tạo account.
- Cart: 4/6 test ban đầu fail; thêm regression drawer error và read-only reconciliation đều RED trước sửa → 7/7 pass.
- Toàn bộ Node tests: 26/26 pass.
- PHP cart 23/23, checkout-registration 8/8, wishlist pass; syntax các file đã sửa và `git diff --check` pass.

### Demo thực tế trên QA cách ly

**Registration qua form HTTP thật trong trình duyệt:** SMTP thiếu thì trả lỗi và chưa tạo account; field bắt buộc/password confirmation chạy trước tạo account. Cấu hình gửi thư giả chỉ trong QA cùng mail interception cho phép tạo account pending, lưu đúng tên/phone/password khách nhập, không auto-login. Login trước xác minh bị chặn; link lấy từ thư bị chặn xác minh thành công và đăng nhập; logout/login lại bằng password đã nhập thành công; link dùng lại không đăng nhập lại cho khách đã logout. Tám kiểm tra pass. Account giả đã xóa.

**Cart qua trình duyệt và Store API:** số lượng `0 → 1 → 2 → 5` khi lần lượt chọn `1, 1, 3`. Thêm vượt stock 5 bị từ chối, quantity giữ 5 và lỗi hiện trong drawer. Khi giả lập mất response sau khi server đã thêm 1, không có resubmit; server và drawer đều hiển thị quantity 1, kèm thông báo tiếng Việt. Cart fixture cuối được dọn trống.

**Guest checkout:** chạy lại actual classic COD form trên fixture sau sửa; invalid nonce/required errors/receipt 250000 VND/COD/cart trống/logged-out đều pass. Đơn giả #16 được xóa bằng Woo CRUD và tồn kho khôi phục 5.

Bằng chứng phiên kiểm tra: [registration](/tmp/shoes-audit-20261028/registration-smoke-results.json), [cart](/tmp/shoes-audit-20261028/cart-add-once-smoke-results.json), [drawer sau lỗi kết nối](/tmp/shoes-audit-20261028/cart-add-once-error.jpg). Các file `/tmp` có thể bị hệ thống dọn.

### Giới hạn

SMTP/VNPAY để merchant cấu hình sau theo yêu cầu. Test xác minh dùng thư bị chặn trong QA, chưa chứng minh gửi/nhận thư qua SMTP thật. Khi SMTP chưa cấu hình, đăng ký báo tạm thời chưa khả dụng theo contract hiện có. Code VNPAY và các vấn đề còn lại trong audit nằm ngoài bản sửa này. Không tạo account/đơn/cart ở preview `8082`, không commit hoặc push.
