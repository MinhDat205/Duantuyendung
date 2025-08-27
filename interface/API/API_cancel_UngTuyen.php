<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { 
    http_response_code(200); 
    exit; 
}

// Tắt hiển thị lỗi HTML
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    // Đọc input
    $raw = file_get_contents('php://input');
    $js = json_decode($raw, true);
    $P = array_merge($_GET, $_POST, is_array($js) ? $js : []);

    $maTin = isset($P['MaTin']) ? (int)$P['MaTin'] : null;
    $maTK = isset($P['MaTK']) ? (int)$P['MaTK'] : null;
    $maUV = isset($P['MaUngVien']) ? (int)$P['MaUngVien'] : null;

    // Validate input
    if (!$maTin) {
        echo json_encode(['ok' => false, 'error' => 'Thiếu MaTin']);
        exit;
    }
    if (!$maUV && !$maTK) {
        echo json_encode(['ok' => false, 'error' => 'Thiếu MaUngVien hoặc MaTK']);
        exit;
    }

    // Lấy MaUngVien từ MaTK nếu cần
    if (!$maUV && $maTK) {
        $stmt = $conn->prepare('SELECT MaUngVien FROM UngVien WHERE MaTK = ?');
        $stmt->bind_param('i', $maTK);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $maUV = (int)$row['MaUngVien'];
        }
        $stmt->close();
        
        if (!$maUV) {
            echo json_encode(['ok' => false, 'error' => 'Không tìm thấy MaUngVien từ MaTK']);
            exit;
        }
    }

    // Kiểm tra xem có đơn ứng tuyển không
    $stmt = $conn->prepare('SELECT MaUngTuyen, TrangThai FROM UngTuyen WHERE MaUngVien = ? AND MaTin = ?');
    $stmt->bind_param('ii', $maUV, $maTin);
    $stmt->execute();
    $res = $stmt->get_result();
    $current = $res->fetch_assoc();
    $stmt->close();

    if (!$current) {
        echo json_encode(['ok' => false, 'error' => 'Không tìm thấy đơn ứng tuyển']);
        exit;
    }

    // Xử lý trạng thái
    $currentStatus = $current['TrangThai'] ?? '';
    if (empty($currentStatus) || $currentStatus === '') {
        // Nếu trạng thái rỗng, cập nhật thành DangXet trước
        $stmt = $conn->prepare('UPDATE UngTuyen SET TrangThai = "DangXet" WHERE MaUngVien = ? AND MaTin = ?');
        $stmt->bind_param('ii', $maUV, $maTin);
        $stmt->execute();
        $stmt->close();
        $currentStatus = 'DangXet';
    }

    // Kiểm tra nếu đã hủy rồi
    if ($currentStatus === 'Huy') {
        echo json_encode(['ok' => true, 'status' => 'already_cancelled']);
        exit;
    }

    // Thực hiện hủy
    $stmt = $conn->prepare('UPDATE UngTuyen SET TrangThai = "Huy" WHERE MaUngVien = ? AND MaTin = ?');
    $stmt->bind_param('ii', $maUV, $maTin);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        echo json_encode([
            'ok' => true, 
            'status' => 'cancelled',
            'previous_status' => $currentStatus,
            'debug' => [
                'MaUngVien' => $maUV,
                'MaTin' => $maTin,
                'affected_rows' => $affected
            ]
        ]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Không thể cập nhật trạng thái']);
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Lỗi server: ' . $e->getMessage()]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
