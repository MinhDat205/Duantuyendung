<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

try {
    // Xem dữ liệu trước khi sửa
    $stmt = $conn->prepare('SELECT * FROM UngTuyen ORDER BY MaUngTuyen');
    $stmt->execute();
    $res = $stmt->get_result();
    $before = [];
    while ($row = $res->fetch_assoc()) {
        $before[] = $row;
    }
    $stmt->close();

    // Sửa dữ liệu
    $stmt = $conn->prepare('UPDATE UngTuyen SET TrangThai = "DangXet" WHERE TrangThai IS NULL OR TrangThai = ""');
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    // Xem dữ liệu sau khi sửa
    $stmt = $conn->prepare('SELECT * FROM UngTuyen ORDER BY MaUngTuyen');
    $stmt->execute();
    $res = $stmt->get_result();
    $after = [];
    while ($row = $res->fetch_assoc()) {
        $after[] = $row;
    }
    $stmt->close();

    // Kiểm tra bản ghi cụ thể
    $stmt = $conn->prepare('SELECT * FROM UngTuyen WHERE MaTin = 1 AND MaUngVien = 1');
    $stmt->execute();
    $res = $stmt->get_result();
    $specific = $res->fetch_assoc();
    $stmt->close();

    echo json_encode([
        'ok' => true,
        'affected_rows' => $affected,
        'before_update' => $before,
        'after_update' => $after,
        'specific_record' => $specific
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Lỗi: '.$e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
    if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
?>
