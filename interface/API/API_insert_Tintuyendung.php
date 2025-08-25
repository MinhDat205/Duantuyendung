<?php
include "config.php";

$MaNTD = $_POST['MaNTD'] ?? null;
$ChucDanh = $_POST['ChucDanh'] ?? null;
$MoTaCongViec = $_POST['MoTaCongViec'] ?? null;
$YeuCau = $_POST['YeuCau'] ?? null;
$MucLuong = $_POST['MucLuong'] ?? null;
$DiaDiemLamViec = $_POST['DiaDiemLamViec'] ?? null;
$TrangThai = $_POST['TrangThai'] ?? 'ChoDuyet';

$sql = "INSERT INTO TinTuyenDung (MaNTD, ChucDanh, MoTaCongViec, YeuCau, MucLuong, DiaDiemLamViec, NgayDang, TrangThai)
        VALUES ('$MaNTD','$ChucDanh','$MoTaCongViec','$YeuCau','$MucLuong','$DiaDiemLamViec', NOW(), '$TrangThai')";

$response = [];
if ($conn->query($sql) === TRUE) {
    $response = ["status" => "success", "message" => "Thêm tin tuyển dụng thành công"];
} else {
    $response = ["status" => "error", "message" => $conn->error];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$conn->close();
?>
