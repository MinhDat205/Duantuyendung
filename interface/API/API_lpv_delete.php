<?php
// interface/API/API_lpv_delete.php
require 'config.php';

// Chấp nhận cả POST lẫn DELETE, nhưng thực tế đa số gọi bằng POST AJAX
$method = $_SERVER['REQUEST_METHOD'];
if (!in_array($method, ['POST','DELETE'])) {
  json_out(['ok'=>false,'error'=>'Method not allowed'], 405);
}

// Gom dữ liệu từ nhiều nguồn: JSON body -> form -> query
$raw = json_decode(file_get_contents('php://input'), true) ?: [];
$MaNTD    = (int)($raw['MaNTD']    ?? $_POST['MaNTD']    ?? $_GET['MaNTD']    ?? 0);
$MaLichPV = (int)($raw['MaLichPV'] ?? $_POST['MaLichPV'] ?? $_GET['MaLichPV'] ?? 0);

if (!$MaNTD || !$MaLichPV) {
  json_out(['ok'=>false,'error'=>'Thiếu MaNTD hoặc MaLichPV','debug'=>compact('MaNTD','MaLichPV')], 400);
}

// Kiểm tra quyền sở hữu (NTD chỉ được xóa lịch thuộc tin của mình)
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

// Xóa
$del = db()->prepare("DELETE FROM LichPhongVan WHERE MaLichPV=?");
$del->bind_param("i", $MaLichPV);
if (!$del->execute()) {
  json_out(['ok'=>false,'error'=>'Xoá thất bại: '.$del->error], 500);
}

json_out(['ok'=>true,'MaLichPV'=>$MaLichPV,'affected'=>$del->affected_rows]);
