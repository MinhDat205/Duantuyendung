<?php
require 'config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $data = read_json_body();
    
    $MaTB = $data['MaTB'] ?? null;
    $MaTK = $data['MaTK'] ?? null;
    
    if (!$MaTB || !$MaTK) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Thiếu MaTB hoặc MaTK'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Đánh dấu thông báo đã đọc
    $stmt = $conn->prepare("
        UPDATE ThongBao 
        SET DaXem = 1 
        WHERE MaTB = ? AND MaNguoiNhan = ?
    ");
    
    $stmt->bind_param("ii", $MaTB, $MaTK);
    $result = $stmt->execute();
    
    if ($result && $stmt->affected_rows > 0) {
        echo json_encode(['ok' => true, 'message' => 'Đã đánh dấu thông báo đã đọc'], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Không tìm thấy thông báo'], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
