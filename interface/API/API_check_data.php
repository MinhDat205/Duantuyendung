<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

try {
    // Kiểm tra dữ liệu trong bảng UngTuyen
    $stmt = $conn->prepare('SELECT * FROM UngTuyen ORDER BY MaUngTuyen');
    $stmt->execute();
    $res = $stmt->get_result();
    $data = [];
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();

    // Kiểm tra trạng thái cụ thể cho MaTin=1, MaUngVien=1
    $stmt = $conn->prepare('SELECT * FROM UngTuyen WHERE MaTin = 1 AND MaUngVien = 1');
    $stmt->execute();
    $res = $stmt->get_result();
    $specific = $res->fetch_assoc();
    $stmt->close();

    echo json_encode([
        'ok' => true,
        'all_data' => $data,
        'specific_record' => $specific,
        'total_records' => count($data)
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Lỗi: '.$e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
    if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
?>
