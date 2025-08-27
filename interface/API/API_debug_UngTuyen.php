<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$raw = file_get_contents('php://input');
$js  = json_decode($raw, true);
$P   = array_merge($_GET, $_POST, is_array($js)?$js:[]);

$maTin = isset($P['MaTin']) ? (int)$P['MaTin'] : null;
$maTK  = isset($P['MaTK']) ? (int)$P['MaTK'] : null;
$maUV  = isset($P['MaUngVien']) ? (int)$P['MaUngVien'] : null;

if (!$maTin) { echo json_encode(['ok'=>false,'error'=>'Thiếu MaTin']); exit; }
if (!$maUV && !$maTK) { echo json_encode(['ok'=>false,'error'=>'Thiếu MaUngVien hoặc MaTK']); exit; }

try {
  if (!$maUV && $maTK) {
    $stmt = $conn->prepare('SELECT MaUngVien FROM UngVien WHERE MaTK=?');
    $stmt->bind_param('i', $maTK);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) $maUV = (int)$row['MaUngVien'];
    $stmt->close();
    if (!$maUV) { echo json_encode(['ok'=>false,'error'=>'Không tìm thấy MaUngVien từ MaTK']); exit; }
  }

  // Kiểm tra trạng thái hiện tại
  $stmt = $conn->prepare('SELECT MaUngTuyen, TrangThai FROM UngTuyen WHERE MaUngVien=? AND MaTin=?');
  $stmt->bind_param('ii', $maUV, $maTin);
  $stmt->execute();
  $res = $stmt->get_result();
  $current = $res->fetch_assoc();
  $stmt->close();

  $currentStatus = $current ? ($current['TrangThai'] ?? '') : 'not_found';
  if ($currentStatus === '') {
    $currentStatus = 'empty';
  }

  echo json_encode([
    'ok' => true,
    'current_status' => $currentStatus,
    'MaUngTuyen' => $current ? $current['MaUngTuyen'] : null,
    'debug_params' => [
      'MaUngVien' => $maUV,
      'MaTin' => $maTin
    ]
  ]);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Lỗi server: '.$e->getMessage()]);
} finally {
  if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
?>
