<?php
header('Content-Type: application/json; charset=utf-8');
include 'config.php';

$hoten    = $_POST['hoten'];
$ngaysinh = $_POST['ngaysinh'];
$sdt      = $_POST['sdt'];
$diachi   = $_POST['diachi'];

$sql = "INSERT INTO NguoiTrong (HoTen, NgaySinh, SoDienThoai, DiaChi) 
        VALUES ('$hoten', '$ngaysinh', '$sdt', '$diachi')";

if (mysqli_query($conn, $sql)) {
    $response = [
        "status"  => "success",
        "message" => "Thêm người trồng thành công"
    ];
} else {
    $response = [
        "status"  => "error",
        "message" => mysqli_error($conn)
    ];
}

mysqli_close($conn);

// Trả về JSON
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
