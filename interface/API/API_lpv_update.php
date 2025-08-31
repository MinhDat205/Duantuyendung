<?php
// interface/API/API_lpv_update.php
require 'config.php';
require_method('POST');

$body = json_decode(file_get_contents('php://input'), true) ?: [];
$MaNTD           = (int) ($body['MaNTD'] ?? inparam(['MaNTD'], 0));
$MaLichPV        = (int) ($body['MaLichPV'] ?? inparam(['MaLichPV'], 0));
$NgayGioPhongVan = trim($body['NgayGioPhongVan'] ?? inparam(['NgayGioPhongVan'], ''));

if (!$MaNTD || !$MaLichPV || !$NgayGioPhongVan) {
  json_out(['ok'=>false,'error'=>'Thiếu dữ liệu'], 400);
}

// Verify ownership qua join
$ck = db()->prepare("
  SELECT lpv.MaLichPV
  FROM LichPhongVan lpv
  JOIN UngTuyen ut ON lpv.MaUngTuyen=ut.MaUngTuyen
  JOIN TinTuyenDung t ON ut.MaTin=t.MaTin
  WHERE lpv.MaLichPV=? AND t.MaNTD=?
");
$ck->bind_param("ii", $MaLichPV, $MaNTD);
$ck->execute();
if (!$ck->get_result()->fetch_assoc()) {
  json_out(['ok'=>false,'error'=>'Không có quyền hoặc lịch không tồn tại'], 400);
}

$upd = db()->prepare("UPDATE LichPhongVan SET NgayGioPhongVan=? WHERE MaLichPV=?");
$upd->bind_param("si", $NgayGioPhongVan, $MaLichPV);
$upd->execute();

json_out(['ok'=>true]);
