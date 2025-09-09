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
    
    $MaTK = $data['MaTK'] ?? null;
    
    if (!$MaTK) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Thiếu MaTK'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Đánh dấu tất cả thông báo đã đọc
    $stmt = $conn->prepare("
        UPDATE ThongBao 
        SET DaXem = 1 
        WHERE MaNguoiNhan = ? AND DaXem = 0
    ");
    
    $stmt->bind_param("i", $MaTK);
    $result = $stmt->execute();
    $affectedRows = $stmt->affected_rows;
    
    echo json_encode([
        'ok' => true, 
        'message' => "Đã đánh dấu {$affectedRows} thông báo đã đọc"
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
