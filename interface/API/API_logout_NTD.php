<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Xử lý preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'NhaTuyenDung') {
    echo json_encode([
        'ok' => false,
        'error' => 'Chưa đăng nhập hoặc không phải Nhà Tuyển Dụng'
    ]);
    exit();
}

try {
    // Xóa session
    session_unset();
    session_destroy();
    
    // Xóa cookie session nếu có
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    echo json_encode([
        'ok' => true,
        'message' => 'Đăng xuất thành công'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'ok' => false,
        'error' => 'Lỗi khi đăng xuất: ' . $e->getMessage()
    ]);
}
?>
