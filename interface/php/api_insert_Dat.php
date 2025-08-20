<?php
header('Content-Type: application/json; charset=utf-8');
include 'config.php';

$tendat       = $_POST['tendat'];
$dientich     = $_POST['dientich'];
$vitri        = $_POST['vitri'];
$manguoitrong = $_POST['manguoitrong'];

$sql = "INSERT INTO Dat (TenDat, DienTich, ViTri, MaNguoiTrong) 
        VALUES ('$tendat', '$dientich', '$vitri', '$manguoitrong')";

if (mysqli_query($conn, $sql)) {
    $response = [
        "status"  => "success",
        "message" => "Thêm vùng đất thành công"
    ];
} else {
    $response = [
        "status"  => "error",
        "message" => mysqli_error($conn)
    ];
}

mysqli_close($conn);

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
