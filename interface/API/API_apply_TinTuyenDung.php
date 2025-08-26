<?php
// interface/API/API_apply_TinTuyenDung.php
require_once __DIR__ . '/config.php';

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Chuẩn hoá input - nhận cả MaUngVien lẫn MaTK
$raw = file_get_contents('php://input');
$js  = json_decode($raw, true);
$P   = array_merge($_POST, is_array($js)?$js:[]);

$maUV = $P['MaUngVien'] ?? $P['maUV'] ?? null;
$maTK = $P['MaTK'] ?? $P['maTK'] ?? null;
$maTin = $P['MaTin'] ?? null;

if (!$maTin) {
    echo json_encode(['ok' => false, 'error' => 'Thiếu thông tin MaTin']);
    exit();
}

if (!$maUV && !$maTK) {
    echo json_encode(['ok' => false, 'error' => 'Thiếu thông tin MaUngVien hoặc MaTK']);
    exit();
}

try {
    // Nếu thiếu MaUngVien nhưng có MaTK → suy ra MaUngVien
    if (!$maUV && $maTK) {
        $stmt = $conn->prepare("SELECT MaUngVien FROM UngVien WHERE MaTK=?");
        $stmt->bind_param('i', $maTK);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $maUV = $row['MaUngVien'];
        } else {
            echo json_encode(['ok' => false, 'error' => 'Không tìm thấy UV theo MaTK={$maTK}']);
            exit();
        }
        $stmt->close();
    }
    
    // Kiểm tra xem đã ứng tuyển chưa
    $stmt = $conn->prepare("SELECT MaUngTuyen FROM UngTuyen WHERE MaUngVien = ? AND MaTin = ?");
    $stmt->bind_param('ii', $maUV, $maTin);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        echo json_encode(['ok' => false, 'error' => 'Bạn đã ứng tuyển tin này rồi'], JSON_UNESCAPED_UNICODE);
        exit();
    }
    $stmt->close();
    
    // Kiểm tra tin tuyển dụng có tồn tại và đã duyệt không
    $stmt = $conn->prepare("SELECT MaTin FROM TinTuyenDung WHERE MaTin = ? AND TrangThai = 'DaDuyet'");
    $stmt->bind_param('i', $maTin);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $stmt->close();
        echo json_encode(['ok' => false, 'error' => 'Tin tuyển dụng không tồn tại hoặc chưa được duyệt'], JSON_UNESCAPED_UNICODE);
        exit();
    }
    $stmt->close();
    
    // Thêm đơn ứng tuyển
    $ngayUngTuyen = date('Y-m-d H:i:s');
    $trangThai = 'DangXet';
    
    $stmt = $conn->prepare("
        INSERT INTO UngTuyen (MaUngVien, MaTin, NgayUngTuyen, TrangThai) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param('iiss', $maUV, $maTin, $ngayUngTuyen, $trangThai);
    
    if ($stmt->execute()) {
        $maUngTuyen = $conn->insert_id;
        $stmt->close();
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Ứng tuyển thành công',
            'data' => [
                'MaUngTuyen' => $maUngTuyen,
                'MaUngVien' => $maUV,
                'MaTin' => $maTin,
                'NgayUngTuyen' => $ngayUngTuyen,
                'TrangThai' => $trangThai
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        $stmt->close();
        echo json_encode(['ok' => false, 'error' => 'Lỗi khi tạo đơn ứng tuyển'], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Lỗi server: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
    if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
?>
