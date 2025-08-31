<?php
// interface/API/API_lpv_set_status.php
require 'config.php';

// Chuẩn REST: chỉ cho phép POST (hoặc PUT/PATCH nếu bạn muốn chuẩn hơn)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Methods: POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  json_out(['ok'=>false,'error'=>'Method not allowed'], 405);
}

// Đọc input JSON
$body = json_decode(file_get_contents('php://input'), true) ?: [];
$MaNTD     = (int)($body['MaNTD'] ?? 0);
$MaLichPV  = (int)($body['MaLichPV'] ?? 0);
$TrangThai = trim($body['TrangThai'] ?? '');

if (!$MaNTD || !$MaLichPV || !$TrangThai) {
  json_out(['ok'=>false,'error'=>'Thiếu MaNTD/MaLichPV/TrangThai'], 400);
}

// Chỉ cho phép HoanThanh hoặc Huy
$allow = ['HoanThanh','Huy'];
if (!in_array($TrangThai, $allow, true)) {
  json_out(['ok'=>false,'error'=>'TrangThai chỉ cho phép HoanThanh hoặc Huy'], 400);
}

// Verify lịch thuộc về tin của NTD
$ck = db()->prepare("
  SELECT lpv.MaLichPV
  FROM LichPhongVan lpv
  JOIN UngTuyen ut ON lpv.MaUngTuyen=ut.MaUngTuyen
  JOIN TinTuyenDung t ON ut.MaTin=t.MaTin
  WHERE lpv.MaLichPV=? AND t.MaNTD=? LIMIT 1
");
$ck->bind_param("ii", $MaLichPV, $MaNTD);
$ck->execute();
if (!$ck->get_result()->fetch_assoc()) {
  json_out(['ok'=>false,'error'=>'Không có quyền hoặc lịch không tồn tại'], 400);
}

// Cập nhật trạng thái
$upd = db()->prepare("UPDATE LichPhongVan SET TrangThai=? WHERE MaLichPV=?");
$upd->bind_param("si", $TrangThai, $MaLichPV);
if (!$upd->execute()) {
  json_out(['ok'=>false,'error'=>'Lỗi DB: '.$upd->error], 500);
}

json_out(['ok'=>true,'MaLichPV'=>$MaLichPV,'TrangThai'=>$TrangThai]);
