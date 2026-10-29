# Shoes-Shop (PAXG'Shop) Project Guidelines & UI Rules

BẮT BUỘC ĐỌC VÀ TUÂN THỦ TRƯỚC KHI THỰC HIỆN BẤT KỲ TÁC VỤ NÀO TRONG DỰ ÁN NÀY:

## 1. Quy tắc UI Thẻ sản phẩm & Trình bày ảnh (Product Card Presentation)
- **Tuyệt đối chống anti-pattern 2 viền lồng nhau (Double-border anti-pattern):**
  - Khi ảnh sản phẩm đã có background riêng (ví dụ ảnh chụp giày có nền xám studio), khung container bao ngoài (`.vyre-card-media`, `.vyre-card-img-link`, `.paxig-reference-image`) **TUYỆT ĐỐI KHÔNG** được đặt padding dày hoặc background màu tương phản tạo thành viền khung giả bao quanh ảnh.
  - Luôn đảm bảo: `padding: 0; background: transparent;`.
  - Thẻ `<img>` phải hiển thị tràn viền sạch sẽ: `width: 100%; height: 100%; object-fit: cover; display: block;`. Chỉ hiển thị ảnh trực tiếp, không viền ngoài.

## 2. Quy tắc Nút yêu thích / Icon trái tim (Wishlist Button)
- **Vị trí cố định:** Luôn nằm ở góc trên bên phải của ảnh sản phẩm (`top: 12px; right: 12px;` hoặc `top: 10px; right: 10px;`), nằm trực tiếp trên nền ảnh.
- **Bắt buộc bo tròn hoàn hảo:** Luôn khai báo `border-radius: 50% !important;` cho các class wishlist button (`.vyre-card-wishlist`, `.paxig-wishlist-card-btn`). Tuyệt đối không để reset button toàn cục (`button { border-radius: 0; }`) ghi đè biến nút tròn thành nút vuông.
- **Style chuẩn:** Nền mờ kính (`background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(4px);`), viền mảnh tinh tế (`1px solid var(--paxig-color-border, #d6d0c7)`), hover phóng nhẹ `transform: scale(1.1);`.

## 3. Quy trình xác minh trực quan (Visual Verification)
- Sau khi sửa bất kỳ mã CSS/UI nào, bắt buộc chụp ảnh màn hình bằng Google Chrome headless (`google-chrome --headless --screenshot=...`) để kiểm chứng mắt thấy trước khi báo hoàn thành cho người dùng.

## 4. Quy tắc Typography Tiêu đề lớn & Giãn cách Dấu tiếng Việt (Headings & Diacritics Spacing)
- **Tuyệt đối chống lỗi chữ/dấu đè lên nhau (Diacritics collision / overlapping text):**
  - Font display thể thao/poster (`PAXIG Display` / `Oswald`) khi viết hoa tiếng Việt (`Ộ`, `Ậ`, `Ạ`, `Ỹ`...) có dấu nặng (dot below) hoặc dấu phụ rơi sâu xuống dưới baseline.
  - **TUYỆT ĐỐI KHÔNG** đặt `line-height` quá thấp (`<= 1.06`) kết hợp với `margin: 0` hoặc margin-bottom quá hẹp (`< 12px`) trên các tiêu đề lớn (`.vyre-plp-title`, `.woocommerce-products-header__title`, heading hero/banner).
  - **Quy chuẩn bắt buộc:**
    - `line-height >= 1.15` cho các tiêu đề display để bao trọn bounding box của dấu phụ.
    - Đảm bảo khoảng cách giữa tiêu đề và phụ đề liền kề (`.vyre-plp-subtitle`) tối thiểu `14px - 16px` (`margin-bottom: 16px;`).
    - Kiểm soát độ ưu tiên selector (specificity), tránh để selector toàn cục hoặc modifier ghi đè `margin: 0` làm biến mất khoảng cách giữa tiêu đề và phụ đề.

## 5. Quy tắc Giãn cách Form Mua hàng & Ô số lượng PDP (PDP Cart Form & Quantity Spacing)
- **Tuyệt đối chống lỗi dính sát / đè chồng giữa ô số lượng và nút Thêm vào giỏ hàng:**
  - Trong trang chi tiết sản phẩm (PDP), WooCommerce mặc định có thể áp dụng `float: left` cho `.quantity` và `.button`, đồng thời một số style vô tình ghi đè `margin-bottom: 0 !important;` lên toàn bộ `form.cart .quantity`.
  - Với sản phẩm đơn (simple product), form không sử dụng flex wrapper `.woocommerce-variation-add-to-cart`, dẫn đến ô số lượng (`.quantity`) và nút thêm giỏ hàng (`.single_add_to_cart_button`) dính chặt mép viền (khoảng cách 0px) hoặc gây lỗi trôi dòng (float bug khiến nút yêu thích nhảy lên cùng hàng với ô số lượng).
- **Quy chuẩn bắt buộc:**
  - Luôn triệt tiêu float: `float: none !important;` trên cả `.quantity` và `.single_add_to_cart_button`.
  - Luôn duy trì khoảng cách chuẩn giữa ô số lượng và nút thêm vào giỏ hàng: `margin-bottom: 16px !important;` trên `form.cart .quantity`.
  - Riêng với sản phẩm biến thể (variable product), chỉ đặt `margin-bottom: 0 !important;` khi nằm bên trong `.woocommerce-variation-add-to-cart` để tận dụng thuộc tính flex `gap` mà không bị cộng dồn khoảng cách thừa.
