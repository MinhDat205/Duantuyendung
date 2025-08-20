<?php
header('Content-Type: application/json; charset=utf-8');
include 'config.php';

$MaCay = $_POST['MaCay'] ?? '';
$GiongCay = $_POST['GiongCay'] ?? '';
$NgayTrong = $_POST['NgayTrong'] ?? '';
$TinhTrang = $_POST['TinhTrang'] ?? '';

if (!$MaCay || !$GiongCay || !$NgayTrong || !$TinhTrang) {
    echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu']);
    exit;
}

$sql = "UPDATE Cay 
        SET GiongCay = '$GiongCay', NgayTrong = '$NgayTrong', TinhTrang = '$TinhTrang'
        WHERE MaCay = '$MaCay'";

if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true, 'message' => 'Cập nhật cây thành công']);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
}

mysqli_close($conn);
?>
