<?php
// interface/API/API_lpv_update.php
require_once __DIR__ . '/config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
header('Content-Type: application/json; charset=utf-8');

try {
  $data = json_decode(file_get_contents("php://input"), true);
  if (!is_array($data)) { throw new Exception("Body phải là JSON"); }

  $MaNTD   = (int)($data['MaNTD'] ?? 0);
  $MaLichPV= (int)($data['MaLichPV'] ?? 0);
  $NgayGio = isset($data['NgayGioPhongVan']) ? trim($data['NgayGioPhongVan']) : null;
  $HinhThuc= isset($data['HinhThuc']) ? $data['HinhThuc'] : null;
  $GhiChu  = isset($data['GhiChu']) ? trim($data['GhiChu']) : null;

  if (!$MaNTD || !$MaLichPV) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Thiếu MaNTD/MaLichPV']); exit;
  }

  // Kiểm tra quyền sở hữu: truy ngược qua UT -> TTD -> MaNTD
  $q = "SELECT ttd.MaNTD
        FROM LichPhongVan lpv
        JOIN UngTuyen ut ON ut.MaUngTuyen = lpv.MaUngTuyen
        JOIN TinTuyenDung ttd ON ttd.MaTin = ut.MaTin
        WHERE lpv.MaLichPV=?";
  $stm = $pdo->prepare($q);
  $stm->execute([$MaLichPV]);
  $owner = $stm->fetchColumn();
  if (!$owner || (int)$owner !== $MaNTD) {
    http_response_code(403);
    echo json_encode(['ok'=>false,'error'=>'Không có quyền']); exit;
  }

  $fields = [];
  $params = [];

  if ($NgayGio !== null) {
    $ts = strtotime($NgayGio);
    if ($ts === false) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'NgayGioPhongVan không hợp lệ']); exit; }
    $fields[] = "NgayGioPhongVan=?";
    $params[] = date('Y-m-d H:i:s', $ts);
  }
  if ($HinhThuc !== null) {
    if (!in_array($HinhThuc, ['Online','Offline'])) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'HinhThuc không hợp lệ']); exit; }
    $fields[] = "HinhThuc=?";
    $params[] = $HinhThuc;
  }
  if ($GhiChu !== null) {
    $fields[] = "GhiChu=?";
    $params[] = $GhiChu;
  }

  if (!$fields) { echo json_encode(['ok'=>true,'updated'=>0]); exit; }

  $sql = "UPDATE LichPhongVan SET ".implode(",", $fields)." WHERE MaLichPV=?";
  $params[] = $MaLichPV;
  $u = $pdo->prepare($sql);
  $u->execute($params);

  echo json_encode(['ok'=>true, 'updated'=>$u->rowCount()]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false, 'error'=>'Server error', 'detail'=>$e->getMessage()]);
}
