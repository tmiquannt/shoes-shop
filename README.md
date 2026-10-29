# PAXG'Shop (PAXIG Shoes) — Hướng Dẫn Cài Đặt & Khởi Chạy

Dự án website thương mại điện tử chuyên bán giày thể thao cao cấp xây dựng trên nền tảng **WordPress** và **WooCommerce**, tích hợp sẵn:
- **Theme PAXG'Shop:** Giao diện tối ưu chuẩn UX/UI thể thao hiện đại, hỗ trợ đầy đủ tiếng Việt không lỗi font, giỏ hàng Mini-cart slide-in, danh sách yêu thích (Wishlist), bộ lọc phân loại đa năng (Size, Giới tính, Màu sắc, Môn thể thao).
- **Plugin PAXIG VNPAY:** Cổng thanh toán trực tuyến VNPAY chuẩn Sandbox & Production.
- **Xác thực Email khách hàng:** Tích hợp gửi mã OTP xác thực đăng ký qua Gmail SMTP.
- **Seeder sản phẩm tự động:** Tool CLI import từ 100 đến 1.000 sản phẩm giày mẫu kèm ảnh bản quyền chất lượng cao từ Wikimedia Commons.

---

## 📋 Mục lục

1. [Yêu cầu hệ thống](#1-yêu-cầu-hệ-thống)
2. [Cách 1: Cài đặt và chạy bằng Docker (Khuyên dùng)](#2-cách-1-cài-đặt-và-chạy-bằng-docker-khuyên-dùng)
3. [Cách 2: Cài đặt và chạy bằng XAMPP (Windows / macOS / Linux)](#3-cách-2-cài-đặt-và-chạy-bằng-xampp-windows--macos--linux)
4. [Tạo dữ liệu sản phẩm mẫu (Seed Demo Products)](#4-tạo-dữ-liệu-sản-phẩm-mẫu-seed-demo-products)
5. [Cấu hình Cổng thanh toán & Gửi Email](#5-cấu-hình-cổng-thanh-toán--gửi-email)
6. [Xử lý sự cố thường gặp (Troubleshooting)](#6-xử-lý-sự-cố-thường-gặp-troubleshooting)

---

## 1. Yêu cầu hệ thống

- **PHP:** Phiên bản 8.1 trở lên (khuyên dùng PHP 8.2 hoặc 8.3).
- **Cơ sở dữ liệu:** MySQL 8.0+ hoặc MariaDB 10.6+.
- **WordPress:** 6.4 trở lên (tương thích hoàn hảo với WordPress 6.8+).
- **WooCommerce:** Phiên bản 8.x trở lên.
- **Web Server:** Apache (có bật module `mod_rewrite`) hoặc Nginx.

---

## 2. Cách 1: Cài đặt và chạy bằng Docker (Khuyên dùng)

Cách này nhanh nhất, không cần cài đặt PHP hay MySQL trực tiếp vào máy tính, môi trường được cô lập hoàn toàn.

### Bước 1: Chuẩn bị file môi trường `.env`

Sao chép file `.env.example` thành `.env`:

```bash
cp .env.example .env
```

*(Nếu cần đổi cổng truy cập hoặc mật khẩu database, bạn có thể chỉnh sửa trực tiếp trong file `.env` vừa tạo).*

### Bước 2: Khởi động container

Chạy lệnh Docker Compose để tải và khởi động các dịch vụ (WordPress, MySQL 8.0, phpMyAdmin):

```bash
docker compose up -d
```

Sau khi các container khởi động xong:
- **Website WordPress:** [http://localhost:8082](http://localhost:8082)
- **phpMyAdmin (Quản lý Database):** [http://localhost:8083](http://localhost:8083)
  - *Username:* `shoes_shop` (hoặc `root`)
  - *Password:* `local_dev_password` (hoặc `root_dev_password`)

### Bước 3: Cài đặt WordPress ban đầu

1. Mở trình duyệt và truy cập: [http://localhost:8082](http://localhost:8082)
2. Chọn ngôn ngữ: **Tiếng Việt** (hoặc English) rồi bấm **Tiếp tục**.
3. Điền thông tin website:
   - **Tên website:** `PAXG'Shop`
   - **Tên người dùng quản trị:** `admin` (hoặc tên bạn chọn)
   - **Mật khẩu:** Tự tạo mật khẩu quản trị và lưu lại
   - **Email của bạn:** Nhập email quản trị
4. Bấm **Cài đặt WordPress** và đăng nhập vào trang quản trị (`http://localhost:8082/wp-admin`).

### Bước 4: Cài đặt WooCommerce & Kích hoạt Theme

1. **Cài đặt WooCommerce:**
   - Trong trang quản trị WordPress, vào menu **Gói mở rộng (Plugins)** → **Cài mới (Add New Plugin)**.
   - Tìm kiếm từ khóa: `WooCommerce`.
   - Bấm **Cài đặt ngay (Install Now)** rồi bấm **Kích hoạt (Activate)**.
   - Điền nhanh wizard thiết lập cửa hàng của WooCommerce hoặc bấm bỏ qua.
2. **Kích hoạt Theme PAXG'Shop:**
   - Vào menu **Giao diện (Appearance)** → **Giao diện (Themes)**.
   - Bạn sẽ thấy theme **PAXG'Shop** đã có sẵn. Bấm **Kích hoạt (Activate)**.
3. **Kích hoạt Plugin VNPAY:**
   - Vào menu **Gói mở rộng (Plugins)** → **Đã cài đặt (Installed Plugins)**.
   - Tìm plugin **PAXIG VNPAY for WooCommerce** và bấm **Kích hoạt (Activate)**.

### Bước 5: Cấu hình Đường dẫn tĩnh (Permalinks) — BẮT BUỘC

Để các trang Cửa hàng, Giỏ hàng, Thanh toán và Chi tiết sản phẩm hoạt động trơn tru không bị lỗi 404:
1. Vào **Cài đặt (Settings)** → **Đường dẫn tĩnh (Permalinks)**.
2. Tại mục *Cài đặt thông dụng*, chọn **Tiêu đề bài viết (Post name)** (`/%postname%/`).
3. Bấm nút **Lưu thay đổi (Save Changes)** ở cuối trang.

---

## 3. Cách 2: Cài đặt và chạy bằng XAMPP (Windows / macOS / Linux)

Dành cho các bạn muốn chạy môi trường cục bộ truyền thống qua XAMPP.

### Bước 1: Khởi động XAMPP

1. Mở phần mềm **XAMPP Control Panel**.
2. Bấm nút **Start** ở cả 2 dịch vụ **Apache** và **MySQL**.

### Bước 2: Chuẩn bị mã nguồn WordPress

1. Tải bản cài đặt WordPress mới nhất tại: [https://wordpress.org/download/](https://wordpress.org/download/)
2. Giải nén thư mục `wordpress` vào thư mục `htdocs` của XAMPP:
   - Trên Windows: `C:\xampp\htdocs\shoes-shop`
   - Trên Linux: `/opt/lampp/htdocs/shoes-shop`
   - Trên macOS: `/Applications/XAMPP/xamppfiles/htdocs/shoes-shop`

### Bước 3: Đặt Theme và Plugin vào đúng thư mục

Mã nguồn của repository này chính là thư mục của Theme. Hãy đưa vào đúng vị trí của WordPress:

1. **Copy Theme:**
   - Copy toàn bộ nội dung của repository này vào thư mục:
     `C:\xampp\htdocs\shoes-shop\wp-content\themes\paxig-shoes\`
     *(Đảm bảo file `style.css` và `functions.php` nằm trực tiếp trong thư mục `paxig-shoes`).*
2. **Copy Plugin VNPAY:**
   - Copy thư mục `plugins/paxig-vnpay` sang thư mục plugins của WordPress:
     `C:\xampp\htdocs\shoes-shop\wp-content\plugins\paxig-vnpay\`

### Bước 4: Tạo Cơ sở dữ liệu trong phpMyAdmin

1. Mở trình duyệt vào: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Bấm nút **Mới (New)** ở cột bên trái.
3. Nhập tên Database: `shoes_shop`.
4. Chọn bảng mã (Collation): `utf8mb4_unicode_ci`.
5. Bấm **Tạo (Create)**.

### Bước 5: Cài đặt WordPress

1. Truy cập vào đường dẫn: [http://localhost/shoes-shop](http://localhost/shoes-shop)
2. Bấm **Thực hiện ngay (Let's go!)** để kết nối cơ sở dữ liệu:
   - **Tên cơ sở dữ liệu (Database Name):** `shoes_shop`
   - **Tên người dùng (Username):** `root`
   - **Mật khẩu (Password):** *(để trống trên XAMPP mặc định)*
   - **Địa chỉ máy chủ CSDL (Database Host):** `localhost`
   - **Tiền tố bảng (Table Prefix):** `wp_`
3. Bấm **Gửi (Submit)** → Bấm **Bắt đầu cài đặt (Run the installation)**.
4. Điền thông tin website và tài khoản quản trị (Admin), sau đó đăng nhập.

### Bước 6: Cài đặt WooCommerce & Kích hoạt Theme / Plugin

1. Vào **Plugins** → **Thêm mới (Add New Plugin)** → Tìm và cài đặt **WooCommerce** → Bấm **Kích hoạt**.
2. Vào **Giao diện (Appearance)** → **Giao diện (Themes)** → Tìm theme **PAXG'Shop** và bấm **Kích hoạt**.
3. Vào **Plugins** → **Đã cài đặt** → Tìm **PAXIG VNPAY for WooCommerce** và bấm **Kích hoạt**.

### Bước 7: Cập nhật Permalinks

- Vào **Cài đặt (Settings)** → **Đường dẫn tĩnh (Permalinks)** → Chọn **Tiêu đề bài viết (Post name)** → Bấm **Lưu thay đổi**.

---

## 4. Tạo dữ liệu sản phẩm mẫu (Seed Demo Products)

Dự án cung cấp sẵn script `scripts/seed-demo-products.php` giúp bạn tự động tạo catalog giày mẫu chuẩn WooCommerce với đầy đủ danh mục, biến thể, giá bán, size và hình ảnh giày thật có bản quyền CC/Public Domain từ Wikimedia Commons.

### A. Nếu chạy bằng Docker:

Chạy kiểm tra thử (Dry-run, không tải ảnh):
```bash
docker exec -it shoes-shop-wordpress php /var/www/html/wp-content/themes/paxig-shoes/scripts/seed-demo-products.php --dry-run
```

Chạy tạo thật 100 sản phẩm mẫu (kèm tải ảnh):
```bash
docker exec -it shoes-shop-wordpress php /var/www/html/wp-content/themes/paxig-shoes/scripts/seed-demo-products.php --apply --limit=100
```
*(Nếu muốn tạo tối đa 1.000 sản phẩm, bạn đổi `--limit=1000`).*

### B. Nếu chạy bằng XAMPP:

Mở cửa sổ Command Prompt / PowerShell / Terminal tại thư mục theme:

```bash
cd C:\xampp\htdocs\shoes-shop\wp-content\themes\paxig-shoes
```

Chạy lệnh PHP CLI của XAMPP:

```bash
# Trên Windows:
C:\xampp\php\php.exe scripts/seed-demo-products.php --apply --limit=100

# Trên Linux / macOS:
php scripts/seed-demo-products.php --apply --limit=100
```

---

## 5. Cấu hình Cổng thanh toán & Gửi Email

### A. Cấu hình các phương thức thanh toán

Vào **WooCommerce** → **Cài đặt (Settings)** → tab **Thanh toán (Payments)**:

1. **Trả tiền mặt khi nhận hàng (COD):**
   - Bật tùy chọn **Trả tiền mặt khi nhận hàng (Cash on delivery)** để cho phép khách thanh toán sau.
2. **Cổng thanh toán VNPAY (Sandbox / Thử nghiệm):**
   - Bật **VNPAY**.
   - Bấm **Cài đặt (Manage)** và cấu hình:
     - Môi trường: Chọn **Sandbox**.
     - Terminal ID (TMN Code): Nhập mã test do VNPAY cung cấp.
     - Secret Key (Hash Secret): Nhập secret test do VNPAY cung cấp.
     - Đơn vị tiền tệ của cửa hàng phải là **VND** (cấu hình tại tab *Cài đặt chung* của WooCommerce).
3. **Cổng thanh toán SePay (Chuyển khoản ngân hàng tự động quét mã QR):**
   - Cài thêm plugin [SePay Gateway for WooCommerce](https://wordpress.org/plugins/sepay-gateway/) từ kho plugin WordPress và làm theo hướng dẫn tại [PAYMENTS-SETUP.md](PAYMENTS-SETUP.md).

### B. Cấu hình gửi mail xác thực tài khoản (Gmail SMTP)

Theme hỗ trợ tính năng gửi email xác thực tài khoản khách hàng khi đăng ký:
1. Đảm bảo bạn đã có file `.env` ở thư mục theme (hoặc cấu hình trong Docker environment).
2. Lấy **Mật khẩu ứng dụng (Google App Password)**:
   - Vào tài khoản Google của bạn → **Bảo mật** → Bật **Xác minh 2 bước**.
   - Tìm mục **Mật khẩu ứng dụng (App Passwords)** → Tạo mật khẩu mới cho ứng dụng (16 ký tự).
3. Cập nhật vào `.env`:
   ```ini
   PAXIG_SMTP_HOST=smtp.gmail.com
   PAXIG_SMTP_PORT=587
   PAXIG_SMTP_ENCRYPTION=tls
   PAXIG_SMTP_USERNAME=dia-chi-email-cua-ban@gmail.com
   PAXIG_SMTP_APP_PASSWORD=mat-khau-ung-dung-16-ky-tu
   PAXIG_SMTP_FROM_EMAIL=dia-chi-email-cua-ban@gmail.com
   PAXIG_SMTP_FROM_NAME=PAXG'Shop
   ```
*(Xem chi tiết hướng dẫn tại [EMAIL-VERIFICATION-SETUP.md](EMAIL-VERIFICATION-SETUP.md)).*

---

## 6. Xử lý sự cố thường gặp (Troubleshooting)

### 1. Bị lỗi 404 khi bấm vào sản phẩm hoặc trang giỏ hàng / thanh toán
- **Nguyên nhân:** WordPress chưa cập nhật lại file cấu hình `.htaccess` / Permalinks.
- **Khắc phục:** Vào **Cài đặt (Settings)** → **Đường dẫn tĩnh (Permalinks)** → Bấm **Lưu thay đổi (Save Changes)** một lần nữa.

### 2. XAMPP báo lỗi vượt quá kích thước file khi tải ảnh hoặc upload plugin
- Mở file `C:\xampp\php\php.ini`, tìm và điều chỉnh các thông số:
  ```ini
  upload_max_filesize = 64M
  post_max_size = 64M
  memory_limit = 256M
  max_execution_time = 300
  ```
- Sau đó khởi động lại Apache trong XAMPP Control Panel.

### 3. Script Seeder báo lỗi `Could not find wp-load.php`
- Đảm bảo bạn đặt theme đúng vị trí `wp-content/themes/paxig-shoes`.
- Hoặc khai báo đường dẫn trực tiếp bằng biến môi trường trước khi chạy lệnh:
  ```bash
  # Windows CMD
  set PAXIG_WP_ROOT=C:\xampp\htdocs\shoes-shop
  C:\xampp\php\php.exe scripts/seed-demo-products.php --apply --limit=50

  # Linux / macOS
  PAXIG_WP_ROOT=/path/to/wordpress php scripts/seed-demo-products.php --apply --limit=50
  ```

### 4. Lỗi "Cổng (Port) 8082 hoặc 3308 đã bị chiếm dụng" trên Docker
- Mở file `.env` và đổi sang cổng khác còn trống trên máy bạn (ví dụ `WP_PORT=8888`, `DB_PORT=3309`), sau đó chạy lại `docker compose up -d`.

---

## 7. Các lệnh Docker hữu ích

```bash
# Xem trạng thái các container
docker compose ps

# Xem log thời gian thực của WordPress
docker compose logs -f wordpress

# Dừng các container
docker compose stop

# Khởi động lại container
docker compose restart

# Tắt và giải phóng tài nguyên container
docker compose down

# Xóa toàn bộ dữ liệu database và website để làm mới từ đầu (CẨN TRỌNG!)
docker compose down -v
```

---
*Chúc bạn cài đặt và trải nghiệm thành công PAXG'Shop!*
