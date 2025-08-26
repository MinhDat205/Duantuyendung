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
    // Kết nối database
    require_once 'utils.php';
    $pdo = getConnection();
    
    // Lấy thông tin Nhà Tuyển Dụng
    $stmt = $pdo->prepare("
        SELECT 
            tk.MaTaiKhoan,
            tk.Email,
            tk.TrangThai,
            ntd.TenCongTy,
            ntd.SoDienThoai,
            ntd.DiaChi,
            ntd.NgayTao
        FROM Taikhoan tk
        INNER JOIN Nhatuyendung ntd ON tk.MaTaiKhoan = ntd.MaTaiKhoan
        WHERE tk.MaTaiKhoan = ? AND tk.LoaiTaiKhoan = 'NhaTuyenDung'
    ");
    
    $stmt->execute([$_SESSION['user_id']]);
    $ntd = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ntd) {
        echo json_encode([
            'ok' => false,
            'error' => 'Không tìm thấy thông tin Nhà Tuyển Dụng'
        ]);
        exit();
    }
    
    // Trả về thông tin thành công
    echo json_encode([
        'ok' => true,
        'data' => [
            'MaTaiKhoan' => $ntd['MaTaiKhoan'],
            'Email' => $ntd['Email'],
            'TenCongTy' => $ntd['TenCongTy'],
            'SoDienThoai' => $ntd['SoDienThoai'],
            'DiaChi' => $ntd['DiaChi'],
            'TrangThai' => $ntd['TrangThai'],
            'NgayTao' => $ntd['NgayTao']
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'ok' => false,
        'error' => 'Lỗi database: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'ok' => false,
        'error' => 'Lỗi hệ thống: ' . $e->getMessage()
    ]);
}
?>
