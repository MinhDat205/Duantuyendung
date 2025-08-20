<?php
include "config.php";

$TenDangNhap = $_POST['TenDangNhap'] ?? null;
$MatKhau = $_POST['MatKhau'] ?? null;

$sql = "SELECT * FROM TaiKhoan WHERE TenDangNhap='$TenDangNhap' AND MatKhau='$MatKhau' LIMIT 1";
$result = $conn->query($sql);

$response = [];
if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $response = ["status" => "success", "message" => "Đăng nhập thành công", "user" => $user];
} else {
    $response = ["status" => "error", "message" => "Sai tài khoản hoặc mật khẩu"];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$conn->close();
?>
