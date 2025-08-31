<?php
// interface/API/API_lpv_list_by_thread.php
require 'config.php';
require_method('GET');

$MaNTD     = (int) inparam(['MaNTD'], 0);
$MaTin     = (int) inparam(['MaTin'], 0);
$MaUngVien = (int) inparam(['MaUngVien','maUV'], 0);
$onlyUpcoming = (int) inparam(['upcoming'], 0);

if (!$MaNTD || !$MaTin || !$MaUngVien) {
  json_out(['ok'=>false,'error'=>'Thiếu tham số'], 400);
}

// Xác thực mối quan hệ Tin-NTD-UV
$ck = db()->prepare("
  SELECT ut.MaUngTuyen
  FROM UngTuyen ut
  JOIN TinTuyenDung t ON ut.MaTin=t.MaTin
  WHERE ut.MaTin=? AND ut.MaUngVien=? AND t.MaNTD=? 
  LIMIT 1
");
$ck->bind_param("iii", $MaTin, $MaUngVien, $MaNTD);
$ck->execute();
$r = $ck->get_result()->fetch_assoc();
if (!$r) json_out(['ok'=>false,'error'=>'Không hợp lệ (Tin-NTD-UV)'], 400);

$where = " MaUngTuyen=? ";
if ($onlyUpcoming) $where .= " AND NgayGioPhongVan >= NOW() ";

$sql = db()->prepare("
  SELECT MaLichPV, MaUngTuyen, NgayGioPhongVan, HinhThuc, TrangThai, GhiChu
  FROM LichPhongVan
  WHERE $where
  ORDER BY NgayGioPhongVan DESC, MaLichPV DESC
");
$sql->bind_param("i", $r['MaUngTuyen']);
$sql->execute();
$res = $sql->get_result();

$items = [];
while ($row = $res->fetch_assoc()) $items[] = $row;

json_out(['ok'=>true,'items'=>$items]);
