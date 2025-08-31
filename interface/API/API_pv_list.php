<?php
// interface/API/API_pv_list.php
require 'config.php';
require_method('GET');

$role       = inparam(['role'], '');
$MaNTD      = (int) inparam(['MaNTD'], 0);
$MaTin      = (int) inparam(['MaTin'], 0);
$MaUngVien  = (int) inparam(['MaUngVien','maUV'], 0);

if ($role !== 'NhaTuyenDung') {
  json_out(['ok'=>false,'error'=>'role không hợp lệ'], 404);
}
if (!$MaNTD || !$MaTin || !$MaUngVien) {
  json_out(['ok'=>false,'error'=>'Thiếu tham số'], 404);
}

// reuse logic list_by_thread
$ck = db()->prepare("
  SELECT ut.MaUngTuyen
  FROM UngTuyen ut
  JOIN TinTuyenDung t ON ut.MaTin=t.MaTin
  WHERE ut.MaTin=? AND ut.MaUngVien=? AND t.MaNTD=? LIMIT 1
");
$ck->bind_param("iii", $MaTin, $MaUngVien, $MaNTD);
$ck->execute();
$r = $ck->get_result()->fetch_assoc();
if (!$r) json_out(['ok'=>false,'error'=>'Không hợp lệ (Tin-NTD-UV)', 'items'=>[]], 404);

$sql = db()->prepare("
  SELECT MaLichPV, MaUngTuyen, NgayGioPhongVan, HinhThuc, TrangThai, GhiChu
  FROM LichPhongVan
  WHERE MaUngTuyen=?
  ORDER BY NgayGioPhongVan DESC, MaLichPV DESC
");
$sql->bind_param("i", $r['MaUngTuyen']);
$sql->execute();
$res = $sql->get_result();

$items = [];
while ($row = $res->fetch_assoc()) $items[] = $row;

json_out(['ok'=>true,'items'=>$items]);
