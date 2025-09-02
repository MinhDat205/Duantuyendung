<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok'=>false,'error'=>'Chỉ hỗ trợ POST'], JSON_UNESCAPED_UNICODE); exit;
  }

  $raw  = file_get_contents('php://input');
  $json = json_decode($raw, true) ?: [];
  $MaTin = $json['MaTin'] ?? $_POST['MaTin'] ?? null;
  $MaTK = $json['MaTK'] ?? $_POST['MaTK'] ?? null;
  $MaUngVien = $json['MaUngVien'] ?? $_POST['MaUngVien'] ?? null;

  if (!$MaTin || (!$MaUngVien && !$MaTK)) {
    http_response_code(422);
    echo json_encode(['ok'=>false,'error'=>'Thiếu tham số: MaTin và (MaUngVien hoặc MaTK)'], JSON_UNESCAPED_UNICODE); exit;
  }

  // Suy ra MaUngVien nếu chỉ có MaTK
  if (!$MaUngVien && $MaTK) {
    $stmt = $conn->prepare('SELECT MaUngVien FROM UngVien WHERE MaTK=? LIMIT 1');
    $stmt->bind_param('i', $MaTK);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    if (!$row || empty($row['MaUngVien'])) {
      http_response_code(404);
      echo json_encode(['ok'=>false,'error'=>'Không tìm thấy ứng viên theo MaTK'], JSON_UNESCAPED_UNICODE); exit;
    }
    $MaUngVien = (int)$row['MaUngVien'];
  }

  $MaTin = (int)$MaTin;
  $MaUngVien = (int)$MaUngVien;

  // Cập nhật trạng thái đơn ứng tuyển về Huy
  $stmt = $conn->prepare('UPDATE UngTuyen SET TrangThai = "Huy" WHERE MaTin = ? AND MaUngVien = ?');
  if (!$stmt) { throw new Exception('Prepare failed: ' . $conn->error); }
  $stmt->bind_param('ii', $MaTin, $MaUngVien);
  $ok = $stmt->execute();
  $affected = $stmt->affected_rows;
  $stmt->close();

  if (!$ok) {
    throw new Exception('Hủy ứng tuyển thất bại');
  }

  echo json_encode(['ok'=>true,'data'=>['affected'=>$affected]], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
  if (isset($conn) && $conn instanceof mysqli) { $conn->close(); }
}
?>


