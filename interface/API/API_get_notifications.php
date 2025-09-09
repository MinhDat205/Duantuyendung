<?php
require 'config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Lấy MaTK từ session hoặc parameter
    $MaTK = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $MaTK = $_GET['MaTK'] ?? null;
    } else {
        $data = read_json_body();
        $MaTK = $data['MaTK'] ?? null;
    }
    
    if (!$MaTK) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Thiếu MaTK'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Lấy danh sách thông báo của người dùng
    $stmt = $conn->prepare("
        SELECT 
            tb.MaTB,
            tb.LoaiThongBao,
            tb.NoiDung,
            tb.DaXem,
            tb.NgayTao,
            tk.LoaiTaiKhoan
        FROM ThongBao tb
        JOIN TaiKhoan tk ON tb.MaNguoiNhan = tk.MaTK
        WHERE tb.MaNguoiNhan = ?
        ORDER BY tb.NgayTao DESC
        LIMIT 50
    ");
    
    $stmt->bind_param("i", $MaTK);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    // Đếm số thông báo chưa đọc
    $stmt = $conn->prepare("
        SELECT COUNT(*) as unread_count
        FROM ThongBao 
        WHERE MaNguoiNhan = ? AND DaXem = 0
    ");
    
    $stmt->bind_param("i", $MaTK);
    $stmt->execute();
    $result = $stmt->get_result();
    $unreadCount = $result->fetch_assoc()['unread_count'];
    
    echo json_encode([
        'ok' => true,
        'data' => [
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
