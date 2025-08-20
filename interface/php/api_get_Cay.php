<?php
header('Content-Type: application/json; charset=utf-8');
include 'config.php';

$sql = "SELECT MaCay, GiongCay, NgayTrong, TinhTrang FROM Cay";
$result = mysqli_query($conn, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

if (!empty($data)) {
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'message' => 'Không có dữ liệu']);
}

mysqli_close($conn);
?>
