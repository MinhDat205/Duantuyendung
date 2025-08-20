<?php
header('Content-Type: application/json; charset=utf-8');
include 'config.php';

$id = $_POST['manguoitrong'];

$sql = "DELETE FROM NguoiTrong WHERE MaNguoiTrong = $id";

if (mysqli_query($conn, $sql)) {
    echo json_encode([
        'success' => true,
        'message' => 'Xóa người trồng thành công 🗑️'
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi: ' . mysqli_error($conn)
    ], JSON_UNESCAPED_UNICODE);
}

mysqli_close($conn);
?>
