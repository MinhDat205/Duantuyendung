<?php
// interface/API/API_chat_init.php
require 'config.php';
require_method('POST');

$body = json_decode(file_get_contents('php://input'), true) ?: [];
$MaTin     = (int) ($body['MaTin']     ?? inparam(['MaTin'], 0));
$MaUngVien = (int) ($body['MaUngVien'] ?? inparam(['MaUngVien','maUV'], 0));
$MaNTD     = (int) ($body['MaNTD']     ?? inparam(['MaNTD','maNTD'], 0));

if (!$MaTin || !$MaUngVien || !$MaNTD) {
  json_out(['ok'=>false,'error'=>'Thiếu MaTin/MaUngVien/MaNTD'], 400);
}

// Kiểm tra ứng tuyển + tin thuộc NTD + trạng thái MoiPhongVan
$sql = db()->prepare("
  SELECT ut.MaUngTuyen, ut.TrangThai
  FROM UngTuyen ut
  JOIN TinTuyenDung t ON ut.MaTin = t.MaTin
  WHERE ut.MaTin=? AND ut.MaUngVien=? AND t.MaNTD=?
  LIMIT 1
");
$sql->bind_param("iii", $MaTin, $MaUngVien, $MaNTD);
$sql->execute();
$row = $sql->get_result()->fetch_assoc();
if (!$row) json_out(['ok'=>false,'error'=>'Không hợp lệ (Tin-NTD-UV)'], 400);
if ($row['TrangThai'] !== 'MoiPhongVan') {
  json_out(['ok'=>false,'error'=>'Chỉ khởi tạo chat khi đơn ở trạng thái MoiPhongVan'], 400);
}

// Đã tồn tại thread?
$ck = db()->prepare("
  SELECT MaThread FROM ChatThread
  WHERE MaTin=? AND MaUngVien=? AND MaNTD=? LIMIT 1
");
$ck->bind_param("iii", $MaTin, $MaUngVien, $MaNTD);
$ck->execute();
$existed = $ck->get_result()->fetch_assoc();

if ($existed) {
  json_out(['ok'=>true, 'MaThread'=>(int)$existed['MaThread'], 'existed'=>true]);
}

// Tạo mới
$ins = db()->prepare("
  INSERT INTO ChatThread (MaTin, MaUngVien, MaNTD, IsOpen)
  VALUES (?,?,?,1)
");
$ins->bind_param("iii", $MaTin, $MaUngVien, $MaNTD);
$ins->execute();
$MaThread = db()->insert_id;

json_out(['ok'=>true, 'MaThread'=>(int)$MaThread, 'existed'=>false]);
