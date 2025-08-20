<?php
include "config.php";

$TenDangNhap = $_POST['TenDangNhap'] ?? null;
$MatKhau = $_POST['MatKhau'] ?? null;
$LoaiTK = $_POST['LoaiTK'] ?? "UngVien";

$sql = "INSERT INTO TaiKhoan (TenDangNhap, MatKhau, LoaiTK) VALUES ('$TenDangNhap', '$MatKhau', '$LoaiTK')";

$response = [];
if ($conn->query($sql) === TRUE) {
    $response = ["status" => "success", "message" => "Tạo tài khoản thành công"];
} else {
    $response = ["status" => "error", "message" => $conn->error];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$conn->close();
?>
