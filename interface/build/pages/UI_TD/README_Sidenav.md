# Hướng dẫn sử dụng Sidenav_Nhatuyendung.php

## Tổng quan
File `Sidenav_Nhatuyendung.php` là sidebar (thanh công cụ) chính cho Nhà Tuyển Dụng trong hệ thống Duantuyendung.

## Tính năng chính

### 1. Sidebar Navigation
- **Trang chủ**: Dashboard chính của Nhà Tuyển Dụng
- **Quản lý tin tuyển dụng**: Quản lý các tin tuyển dụng đã đăng
- **Quản lý ứng viên**: Xem và quản lý ứng viên đã ứng tuyển
- **Hồ sơ công ty**: Cập nhật thông tin công ty
- **Đăng xuất**: Thoát khỏi hệ thống

### 2. Hiển thị thông tin
- Tên công ty
- Email đăng nhập
- Trạng thái tài khoản (Active/Pending/Inactive)

### 3. Responsive Design
- Hỗ trợ mobile với toggle button
- Sidebar có thể ẩn/hiện trên thiết bị nhỏ

## Cách sử dụng

### Đăng nhập
1. Truy cập trang đăng nhập: `UI_TD_DangNhap.html`
2. Nhập email và mật khẩu
3. Sau khi đăng nhập thành công, sẽ được chuyển đến `Sidenav_Nhatuyendung.php`

### Điều hướng
- Click vào các mục menu để chuyển trang
- Menu active sẽ được highlight
- Sử dụng nút "Đăng xuất" để thoát khỏi hệ thống

## Cấu trúc file

### API cần thiết
- `API_get_NTD_Info.php`: Lấy thông tin Nhà Tuyển Dụng
- `API_logout_NTD.php`: Đăng xuất
- `auth_login.php`: Đăng nhập (đã được cập nhật)

### Database
- Bảng `Taikhoan`: Thông tin tài khoản
- Bảng `Nhatuyendung`: Thông tin công ty
- Các bảng liên quan khác

## Bảo mật
- Kiểm tra session đăng nhập
- Chỉ cho phép Nhà Tuyển Dụng truy cập
- Tự động chuyển về trang đăng nhập nếu chưa đăng nhập

## Tùy chỉnh
- Có thể thay đổi màu sắc trong CSS
- Thêm/bớt menu items theo nhu cầu
- Cập nhật icon và text

## Lưu ý
- Đảm bảo database có cấu trúc đúng
- Kiểm tra quyền truy cập file
- Test trên các trình duyệt khác nhau
