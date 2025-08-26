# Hướng dẫn sử dụng API đã được sửa hoàn toàn - Duantuyendung

## Tổng quan
Các API đã được sửa để khắc phục lỗi "Thiếu trường: password" và hỗ trợ cả JSON và form data với các alias key.

## Các file đã được sửa

### 1. config.php
- ✅ Headers CORS và JSON đầy đủ
- ✅ Xử lý preflight request
- ✅ Kết nối database với port 3307
- ✅ Hỗ trợ Authorization header

### 2. auth_register.php
- ✅ Hỗ trợ cả JSON và form data
- ✅ Các alias key cho tất cả trường
- ✅ Helper function `get_input()` để lấy dữ liệu an toàn
- ✅ Cập nhật cấu trúc database theo file mẫu
- ✅ Xử lý ngành nghề từ string và array

### 3. auth_login.php
- ✅ Hỗ trợ cả JSON và form data
- ✅ Các alias key cho password
- ✅ Helper function `get_input()` để lấy dữ liệu an toàn
- ✅ Cập nhật cấu trúc database theo file mẫu

## Cấu trúc dữ liệu mới

### Đăng ký (auth_register.php)
**JSON:**
```json
{
  "loaiTaiKhoan": "NhaTuyenDung",
  "ten": "Tên công ty hoặc họ tên",
  "sdt": "Số điện thoại",
  "diachi": "Địa chỉ (chỉ cho NhaTuyenDung)",
  "email": "Email",
  "matkhau": "Mật khẩu",
  "nganhNghe": ["CNTT", "Marketing"]
}
```

**Form Data:**
```
loaiTaiKhoan=NhaTuyenDung&ten=Công ty Test&sdt=0123456789&diachi=Hồ Chí Minh&email=test@example.com&matkhau=123456&nganhNghe=CNTT,Marketing
```

### Đăng nhập (auth_login.php)
**JSON:**
```json
{
  "email": "Email",
  "password": "Mật khẩu"
}
```

**Form Data:**
```
email=test@example.com&password=123456
```

## Các alias key được hỗ trợ

### 1. Mật khẩu
- `password` | `matkhau`

### 2. Loại tài khoản
- `loaiTaiKhoan` | `loai_tai_khoan`

### 3. Tên
- `ten` | `hoTen` | `tenCongTy`

### 4. Số điện thoại
- `sdt` | `soDienThoai`

### 5. Địa chỉ
- `diachi` | `diaChi`

### 6. Ngành nghề
- `nganhNghe`: nhận mảng JSON hoặc string "CNTT,Marketing" -> chuyển thành mảng

## Helper function

```php
function get_input($keys, $src1, $src2) {
  foreach ((array)$keys as $k) {
    if (isset($src1[$k]) && $src1[$k] !== '') return trim($src1[$k]);
    if (isset($src2[$k]) && $src2[$k] !== '') return trim($src2[$k]);
  }
  return null;
}

// Sử dụng:
$raw = file_get_contents("php://input");
$json = json_decode($raw, true);
$post = $_POST;

$password = get_input(['password','matkhau'], $json, $post);
$email = get_input(['email'], $json, $post);
$loaiTK = get_input(['loaiTaiKhoan','loai_tai_khoan'], $json, $post);
$ten = get_input(['ten','hoTen','tenCongTy'], $json, $post);
$sdt = get_input(['sdt','soDienThoai'], $json, $post);
$diachi = get_input(['diachi','diaChi'], $json, $post);
$nganhNghe = $json['nganhNghe'] ?? ($post['nganhNghe'] ?? []);

// Xử lý ngành nghề
if (is_string($nganhNghe)) { 
    $nganhNghe = array_filter(array_map('trim', explode(',', $nganhNghe))); 
}
```

## Cách test API

### 1. Sử dụng file test_api.html
- Mở file `test_api.html` trong trình duyệt
- Test đăng ký và đăng nhập trực tiếp
- Test raw API với JSON, form-urlencoded, multipart/form-data
- Xem kết quả và debug

### 2. Sử dụng Postman
- Method: POST
- Headers: `Content-Type: application/json` hoặc `application/x-www-form-urlencoded`
- Body: Raw JSON hoặc form data với cấu trúc như trên

### 3. Sử dụng Fetch API
```javascript
// Test JSON
const response = await fetch('auth_register.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        loaiTaiKhoan: "NhaTuyenDung",
        ten: "Công ty Test",
        sdt: "0123456789",
        diachi: "Hồ Chí Minh",
        email: "test@example.com",
        matkhau: "123456",
        nganhNghe: ["CNTT"]
    })
});

// Test Form Data
const formData = new FormData();
formData.append('loaiTaiKhoan', 'NhaTuyenDung');
formData.append('ten', 'Công ty Test');
formData.append('sdt', '0123456789');
formData.append('diachi', 'Hồ Chí Minh');
formData.append('email', 'test@example.com');
formData.append('matkhau', '123456');
formData.append('nganhNghe', 'CNTT,Marketing');

const response = await fetch('auth_register.php', {
    method: 'POST',
    body: formData
});

const result = await response.json();
console.log(result);
```

## Response format

### Thành công
```json
{
  "ok": true,
  "data": {
    "MaTaiKhoan": 123,
    "LoaiTaiKhoan": "NhaTuyenDung",
    "MaNhatuyendung": 456
  }
}
```

### Lỗi
```json
{
  "ok": false,
  "error": "Chi tiết lỗi",
  "raw_body": "Raw body nếu có lỗi JSON",
  "json_error": "Mã lỗi JSON nếu có"
}
```

## Cấu trúc database theo file mẫu

### Bảng TaiKhoan
```sql
CREATE TABLE TaiKhoan (
    MaTK INT AUTO_INCREMENT PRIMARY KEY,
    Email VARCHAR(100) NOT NULL UNIQUE,
    MatKhau VARCHAR(255) NOT NULL,
    LoaiTaiKhoan ENUM('UngVien', 'NhaTuyenDung') NOT NULL,
    TrangThai ENUM('HoatDong', 'BiKhoa') NOT NULL DEFAULT 'HoatDong',
    NgayTao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

### Bảng NhaTuyenDung
```sql
CREATE TABLE NhaTuyenDung (
    MaNTD INT AUTO_INCREMENT PRIMARY KEY,
    MaTK INT NOT NULL UNIQUE,
    TenCongTy VARCHAR(150) NOT NULL,
    SoDienThoai VARCHAR(15),
    DiaChi VARCHAR(255),
    ThongTinCongTy TEXT,
    MaDanhMuc INT NULL,
    CreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_NTD_TK FOREIGN KEY (MaTK) REFERENCES TaiKhoan(MaTK)
);
```

### Bảng UngVien
```sql
CREATE TABLE UngVien (
    MaUngVien INT AUTO_INCREMENT PRIMARY KEY,
    MaTK INT NOT NULL UNIQUE,
    HoTen VARCHAR(100) NOT NULL,
    SoDienThoai VARCHAR(15),
    AnhCV VARCHAR(255),
    KyNang TEXT,
    KinhNghiem TEXT,
    MaDanhMuc INT NULL,
    CreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_UV_TK FOREIGN KEY (MaTK) REFERENCES TaiKhoan(MaTK)
);
```

## Lưu ý quan trọng

1. **Database**: Cần chạy MySQL trên port 3307 với database `hethongtuyendung`
2. **Headers**: API tự động xử lý cả JSON và form data
3. **CORS**: Headers đã được cấu hình cho cross-origin requests
4. **Session**: Đăng nhập sẽ tạo session PHP
5. **Validation**: Kiểm tra dữ liệu đầu vào nghiêm ngặt
6. **Error handling**: Tất cả lỗi đều được log và trả về chi tiết
7. **Alias keys**: Hỗ trợ nhiều tên trường khác nhau
8. **Fallback**: Tự động fallback từ JSON sang form data nếu cần

## Test cases

### Test case 1: Đăng ký Nhà Tuyển Dụng (JSON)
```json
{
  "loaiTaiKhoan": "NhaTuyenDung",
  "ten": "Công ty ABC",
  "sdt": "0123456789",
  "diachi": "123 Đường ABC, Quận 1, TP.HCM",
  "email": "abc@company.com",
  "matkhau": "password123",
  "nganhNghe": ["CNTT", "Marketing"]
}
```

### Test case 2: Đăng ký Ứng viên (Form Data)
```
loaiTaiKhoan=UngVien&ten=Nguyễn Văn A&sdt=0987654321&email=nguyenvana@email.com&matkhau=password123&nganhNghe=CNTT
```

### Test case 3: Đăng nhập (JSON)
```json
{
  "email": "abc@company.com",
  "password": "password123"
}
```

### Test case 4: Đăng nhập (Form Data)
```
email=abc@company.com&password=password123
```

## Troubleshooting

### Lỗi thường gặp
1. **"Thiếu trường: password"**: Đã được sửa, hỗ trợ cả `password` và `matkhau`
2. **"JSON không hợp lệ"**: Kiểm tra Content-Type header hoặc sử dụng form data
3. **"Lỗi kết nối database"**: Kiểm tra MySQL service và port 3307
4. **"Thiếu trường"**: Kiểm tra tất cả trường bắt buộc
5. **"Email đã tồn tại"**: Sử dụng email khác hoặc xóa tài khoản cũ

### Debug steps
1. Kiểm tra response từ API
2. Xem console.log() trong browser
3. Kiểm tra Network tab trong DevTools
4. Sử dụng file test_api.html để test trực tiếp
5. Test với cả JSON và form data
6. Kiểm tra các alias key khác nhau

## Tính năng mới

1. **Hỗ trợ đa dạng input**: JSON, form-urlencoded, multipart/form-data
2. **Alias keys**: Nhiều tên trường cho cùng một dữ liệu
3. **Fallback tự động**: Từ JSON sang form data
4. **Xử lý ngành nghề linh hoạt**: Array hoặc string
5. **Debug chi tiết**: Raw body và JSON error codes
6. **Headers CORS đầy đủ**: Hỗ trợ Authorization
7. **Cấu trúc database chuẩn**: Theo file mẫu Database_va_Dulieukiemthu.txt
