<?php
// C:\xampp\htdocs\Duantuyendung\interface\API\API_lpv_set_status.php
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

try {
  $input = json_decode(file_get_contents('php://input'), true) ?: [];
  $MaNTD = (int)($input['MaNTD'] ?? 0);
  $MaLichPV = (int)($input['MaLichPV'] ?? 0);
  $TrangThai = $input['TrangThai'] ?? '';

  $allow = ['DaLenLich','HoanThanh','Huy'];
  if (!in_array($TrangThai, $allow, true)) throw new Exception('TrangThai không hợp lệ');
  if (!$MaNTD || !$MaLichPV) throw new Exception('Thiếu dữ liệu');

  // Verify ownership
  $sql = "SELECT lpv.MaLichPV
          FROM LichPhongVan lpv
          JOIN UngTuyen ut ON lpv.MaUngTuyen = ut.MaUngTuyen
          JOIN TinTuyenDung t ON ut.MaTin = t.MaTin
          WHERE lpv.MaLichPV=? AND t.MaNTD=?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$MaLichPV, $MaNTD]);
  if (!$stmt->fetch()) throw new Exception('Không có quyền hoặc lịch không tồn tại');

  $upd = "UPDATE LichPhongVan SET TrangThai=? WHERE MaLichPV=?";
  $stmt = $pdo->prepare($upd);
  $stmt->execute([$TrangThai, $MaLichPV]);

  echo json_encode(['ok'=>true]);
} catch (Exception $e) {
  http_response_code(400);
  echo json_encode(['ok'=>false, 'error'=>$e->getMessage()]);
}
