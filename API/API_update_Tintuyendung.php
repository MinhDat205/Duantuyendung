<?php
include "config.php";

$MaTin = $_POST['MaTin'] ?? null;
$ChucDanh = $_POST['ChucDanh'] ?? null;
$MoTaCongViec = $_POST['MoTaCongViec'] ?? null;
$YeuCau = $_POST['YeuCau'] ?? null;
$MucLuong = $_POST['MucLuong'] ?? null;
$DiaDiemLamViec = $_POST['DiaDiemLamViec'] ?? null;
$TrangThai = $_POST['TrangThai'] ?? null;

$sql = "UPDATE TinTuyenDung 
        SET ChucDanh='$ChucDanh', MoTaCongViec='$MoTaCongViec', YeuCau='$YeuCau', 
            MucLuong='$MucLuong', DiaDiemLamViec='$DiaDiemLamViec', TrangThai='$TrangThai'
        WHERE MaTin='$MaTin'";

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
