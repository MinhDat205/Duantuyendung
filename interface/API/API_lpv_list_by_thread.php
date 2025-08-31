<?php
// interface/API/API_lpv_list_by_thread.php
require_once __DIR__ . '/config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
header('Content-Type: application/json; charset=utf-8');

try {
  $MaNTD     = isset($_GET['MaNTD']) ? (int)$_GET['MaNTD'] : 0;
  $MaTin     = isset($_GET['MaTin']) ? (int)$_GET['MaTin'] : 0;
  $MaUngVien = isset($_GET['MaUngVien']) ? (int)$_GET['MaUngVien'] : 0;

  if (!$MaNTD || !$MaTin || !$MaUngVien) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Thiếu MaNTD/MaTin/MaUngVien']); exit;
  }

  // Quyền
  $stmTin = $pdo->prepare("SELECT MaNTD FROM TinTuyenDung WHERE MaTin=?");
  $stmTin->execute([$MaTin]);
  $owner = $stmTin->fetchColumn();
  if (!$owner || (int)$owner !== $MaNTD) {
    http_response_code(403);
    echo json_encode(['ok'=>false,'error'=>'Tin không thuộc NTD']); exit;
  }

  $sql = "SELECT lpv.*, ut.MaTin, ut.MaUngVien
          FROM LichPhongVan lpv
          JOIN UngTuyen ut ON ut.MaUngTuyen = lpv.MaUngTuyen
          WHERE ut.MaTin=? AND ut.MaUngVien=?
          ORDER BY lpv.NgayGioPhongVan DESC, lpv.MaLichPV DESC
          LIMIT 50";
  $stm = $pdo->prepare($sql);
  $stm->execute([$MaTin, $MaUngVien]);
  $rows = $stm->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode(['ok'=>true,'items'=>$rows], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Server error', 'detail'=>$e->getMessage()]);
}
