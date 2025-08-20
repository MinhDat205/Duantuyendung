<?php
// Không để ký tự trắng hoặc dòng trống trước <?php
header('Content-Type: application/json; charset=utf-8');
include 'config.php';

// Kiểm tra dữ liệu đầu vào
$id = isset($_POST['macay']) ? $_POST['macay'] : '';

if (empty($id)) {
    echo json_encode([
        'success' => false,
        'message' => '❌ Vui lòng cung cấp mã cây'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Sử dụng prepared statement để xóa
$stmt = $conn->prepare("DELETE FROM Cay WHERE MaCay = ?");
$stmt->bind_param("s", $id); // "s" vì MaCay là VARCHAR

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => '🗑️ Xóa cây thành công'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'message' => '❌ Không tìm thấy cây với mã: ' . $id
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => '❌ Lỗi: ' . $stmt->error
    ], JSON_UNESCAPED_UNICODE);
}

$stmt->close();
mysqli_close($conn);
?>