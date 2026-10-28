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
