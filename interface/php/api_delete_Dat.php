<?php
header('Content-Type: application/json; charset=utf-8');
include 'config.php';

$id = $_POST['madat'];

$sql = "DELETE FROM Dat WHERE MaDat = $id";

if (mysqli_query($conn, $sql)) {
    echo json_encode([
        'success' => true,
        'message' => '🗑️ Xóa vùng đất thành công'
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'message' => '❌ Lỗi: ' . mysqli_error($conn)
    ], JSON_UNESCAPED_UNICODE);
}

mysqli_close($conn);
?>
