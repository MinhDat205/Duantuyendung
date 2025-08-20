<?php
include "config.php";

$MaTK = $_POST['MaTK'] ?? null;
$TenDangNhap = $_POST['TenDangNhap'] ?? null;
$MatKhau = $_POST['MatKhau'] ?? null;
$LoaiTK = $_POST['LoaiTK'] ?? null;

$sql = "UPDATE TaiKhoan SET TenDangNhap='$TenDangNhap', MatKhau='$MatKhau', LoaiTK='$LoaiTK' WHERE MaTK='$MaTK'";

$response = [];
if ($conn->query($sql) === TRUE) {
    $response = ["status" => "success", "message" => "Cập nhật thành công"];
} else {
    $response = ["status" => "error", "message" => $conn->error];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$conn->close();
?>
