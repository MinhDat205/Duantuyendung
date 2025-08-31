<?php
// interface/API/API_lpv_list_by_ntd.php
require_once __DIR__ . '/config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
header('Content-Type: application/json; charset=utf-8');

try {
  $MaNTD    = isset($_GET['MaNTD']) ? (int)$_GET['MaNTD'] : 0;
  $status   = isset($_GET['TrangThai']) ? $_GET['TrangThai'] : null; // DaLenLich | HoanThanh | Huy
  $upcoming = isset($_GET['upcoming']) ? (int)$_GET['upcoming'] : 0; // 1: chỉ tương lai
  $from     = isset($_GET['from']) ? $_GET['from'] : null; // YYYY-MM-DD
  $to       = isset($_GET['to']) ? $_GET['to'] : null;     // YYYY-MM-DD

  if (!$MaNTD) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Thiếu MaNTD']); exit;
  }

  $params = [$MaNTD];
  $cond = [];
  $cond[] = "ttd.MaNTD = ?";

  if ($status && in_array($status, ['DaLenLich','HoanThanh','Huy'])) {
    $cond[] = "lpv.TrangThai = ?";
    $params[] = $status;
  }
  if ($upcoming === 1) {
    $cond[] = "lpv.NgayGioPhongVan >= NOW()";
  }
  if ($from) {
    $cond[] = "lpv.NgayGioPhongVan >= ?";
    $params[] = $from . " 00:00:00";
  }
  if ($to) {
    $cond[] = "lpv.NgayGioPhongVan <= ?";
    $params[] = $to . " 23:59:59";
  }

  $where = $cond ? ("WHERE " . implode(" AND ", $cond)) : "";
  $sql = "
    SELECT lpv.MaLichPV, lpv.MaUngTuyen, lpv.NgayGioPhongVan, lpv.HinhThuc, lpv.TrangThai, lpv.GhiChu,
           ut.MaTin, ut.MaUngVien, uv.HoTen AS TenUngVien,
           ttd.ChucDanh, ttd.DiaDiemLamViec
    FROM LichPhongVan lpv
    JOIN UngTuyen ut ON ut.MaUngTuyen = lpv.MaUngTuyen
    JOIN TinTuyenDung ttd ON ttd.MaTin = ut.MaTin
    JOIN UngVien uv ON uv.MaUngVien = ut.MaUngVien
    $where
    ORDER BY lpv.NgayGioPhongVan ASC, lpv.MaLichPV DESC
    LIMIT 500
  ";
  $stm = $pdo->prepare($sql);
  $stm->execute($params);
  $rows = $stm->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode(['ok'=>true, 'items'=>$rows], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false, 'error'=>'Server error', 'detail'=>$e->getMessage()]);
}
