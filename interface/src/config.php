<?php
// Thông tin kết nối
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "TuyenDung"; // Đảm bảo CSDL này đã được tạo trong phpMyAdmin

// Tạo kết nối
$conn = mysqli_connect($servername, $username, $password, $database);

// Kiểm tra kết nối
if (!$conn) {
    die("❌ Kết nối thất bại: " . mysqli_connect_error());
}

// Thiết lập UTF-8
mysqli_set_charset($conn, "utf8");

// Hiển thị nếu thành công
echo "✅ Kết nối thành công!";
?>
