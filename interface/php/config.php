<?php
// Không để bất kỳ ký tự trắng hoặc dòng trống nào trước <?php
$host = 'localhost';
$username = 'root'; // Thay bằng tên người dùng MySQL của bạn nếu khác
$password = '';     // Thay bằng mật khẩu MySQL nếu có
$database = 'Trongcay';

// Kết nối tới MySQL
$conn = mysqli_connect($host, $username, $password, $database);

// Kiểm tra kết nối
if (!$conn) {
    header('Content-Type: application/json; charset=utf-8');
    die(json_encode([
        'success' => false,
        'message' => 'Kết nối cơ sở dữ liệu thất bại: ' . mysqli_connect_error()
    ], JSON_UNESCAPED_UNICODE));
}

// Không xuất bất kỳ văn bản nào, chỉ thiết lập kết nối
?>