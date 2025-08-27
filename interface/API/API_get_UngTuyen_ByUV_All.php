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

$maUV = isset($P['MaUngVien']) ? (int)$P['MaUngVien'] : null;
$maTK = isset($P['MaTK']) ? (int)$P['MaTK'] : null;

try {
  if (!$maUV && $maTK) {
    $stmt = $conn->prepare('SELECT MaUngVien FROM UngVien WHERE MaTK=?');
    $stmt->bind_param('i', $maTK);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) $maUV = (int)$row['MaUngVien'];
    $stmt->close();
  }

  if (!$maUV) { echo json_encode(['ok'=>true,'data'=>[]]); exit; }

  $sql = "SELECT t.MaTin, t.ChucDanh AS TieuDe, ntd.TenCongTy, ut.NgayUngTuyen AS NgayNop, ut.TrangThai
          FROM UngTuyen ut
          JOIN TinTuyenDung t ON t.MaTin = ut.MaTin
          JOIN NhaTuyenDung ntd ON ntd.MaNTD = t.MaNTD
          WHERE ut.MaUngVien = ?
          ORDER BY ut.NgayUngTuyen DESC";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('i', $maUV);
  $stmt->execute();
  $res = $stmt->get_result();
  $data = [];
  while ($row = $res->fetch_assoc()) { $data[] = $row; }
  $stmt->close();

  echo json_encode(['ok'=>true,'data'=>$data], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Lỗi server: '.$e->getMessage()]);
} finally {
  if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
?>
