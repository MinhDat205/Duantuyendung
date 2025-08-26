# Hướng dẫn sử dụng API đã được sửa - Duantuyendung

## Tổng quan
Các API đã được sửa để khắc phục lỗi JSON parsing và cải thiện xử lý lỗi.

## Các file đã được sửa

### 1. config.php
- ✅ Thêm headers CORS và JSON
- ✅ Xử lý preflight request
- ✅ Kết nối database với port 3307

### 2. auth_register.php
- ✅ Sửa JSON parsing từ `read_json_body()` sang `file_get_contents("php://input")`
- ✅ Thêm debug logging khi JSON lỗi
- ✅ Cập nhật cấu trúc dữ liệu theo yêu cầu mới
- ✅ Tạm thời bỏ qua phần gán ngành nghề (cần cấu trúc database)

### 3. auth_login.php
- ✅ Sửa JSON parsing tương tự
- ✅ Thêm debug logging
- ✅ Giữ nguyên logic đăng nhập

## Cấu trúc dữ liệu mới

### Đăng ký (auth_register.php)
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

### Đăng nhập (auth_login.php)
```json
{
  "email": "Email",
  "password": "Mật khẩu"
}
```

## Cách test API

### 1. Sử dụng file test_api.html
- Mở file `test_api.html` trong trình duyệt
- Test đăng ký và đăng nhập trực tiếp
- Xem kết quả và debug

### 2. Sử dụng Postman
- Method: POST
- Headers: `Content-Type: application/json`
- Body: Raw JSON với cấu trúc như trên

### 3. Sử dụng Fetch API
```javascript
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

## Debug và xử lý lỗi

### 1. Lỗi JSON không hợp lệ
- Kiểm tra `raw_body` trong response
- Kiểm tra `json_error` để biết loại lỗi
- Đảm bảo gửi đúng `Content-Type: application/json`

### 2. Lỗi database
- Kiểm tra kết nối database
- Kiểm tra cấu trúc bảng
- Xem log lỗi trong response

### 3. Lỗi validation
- Kiểm tra các trường bắt buộc
- Kiểm tra format email
- Kiểm tra loại tài khoản hợp lệ

## Cấu trúc database cần thiết

### Bảng Taikhoan
```sql
CREATE TABLE Taikhoan (
    MaTaiKhoan INT AUTO_INCREMENT PRIMARY KEY,
    Email VARCHAR(255) UNIQUE NOT NULL,
    MatKhau VARCHAR(255) NOT NULL,
    LoaiTaiKhoan ENUM('UngVien', 'NhaTuyenDung') NOT NULL,
    TrangThai ENUM('Active', 'Pending', 'Inactive') DEFAULT 'Active',
    NgayTao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Bảng Nhatuyendung
```sql
CREATE TABLE Nhatuyendung (
    MaNhatuyendung INT AUTO_INCREMENT PRIMARY KEY,
    MaTaiKhoan INT NOT NULL,
    TenCongTy VARCHAR(255) NOT NULL,
    SoDienThoai VARCHAR(20),
    DiaChi TEXT,
    NgayTao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (MaTaiKhoan) REFERENCES Taikhoan(MaTaiKhoan)
);
```

### Bảng Ungvien
```sql
CREATE TABLE Ungvien (
    MaUngvien INT AUTO_INCREMENT PRIMARY KEY,
    MaTaiKhoan INT NOT NULL,
    HoTen VARCHAR(255) NOT NULL,
    SoDienThoai VARCHAR(20),
    AnhCV VARCHAR(255),
    NgayTao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (MaTaiKhoan) REFERENCES Taikhoan(MaTaiKhoan)
);
```

## Lưu ý quan trọng

1. **Port database**: Đảm bảo MySQL chạy trên port 3307
2. **Database name**: `hethongtuyendung`
3. **CORS**: Headers đã được cấu hình cho cross-origin requests
4. **Session**: Đăng nhập sẽ tạo session PHP
5. **Validation**: Kiểm tra dữ liệu đầu vào nghiêm ngặt
6. **Error handling**: Tất cả lỗi đều được log và trả về chi tiết

## Test cases

### Test case 1: Đăng ký Nhà Tuyển Dụng
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

### Test case 2: Đăng ký Ứng viên
```json
{
  "loaiTaiKhoan": "UngVien",
  "ten": "Nguyễn Văn A",
  "sdt": "0987654321",
  "email": "nguyenvana@email.com",
  "matkhau": "password123",
  "nganhNghe": ["CNTT"]
}
```

### Test case 3: Đăng nhập
```json
{
  "email": "abc@company.com",
  "password": "password123"
}
```

## Troubleshooting

### Lỗi thường gặp
1. **"JSON không hợp lệ"**: Kiểm tra Content-Type header
2. **"Lỗi kết nối database"**: Kiểm tra MySQL service và port
3. **"Thiếu trường"**: Kiểm tra tất cả trường bắt buộc
4. **"Email đã tồn tại"**: Sử dụng email khác hoặc xóa tài khoản cũ

### Debug steps
1. Kiểm tra response từ API
2. Xem console.log() trong browser
3. Kiểm tra Network tab trong DevTools
4. Sử dụng file test_api.html để test trực tiếp
