<?php
// interface/API/API_lpv_list_by_ntd.php
require 'config.php';
require_method('GET');

$MaNTD     = (int) inparam(['MaNTD'], 0);
$MaTin     = (int) inparam(['MaTin'], 0);
$MaUngVien = (int) inparam(['MaUngVien','maUV'], 0);
$onlyUpcoming = (int) inparam(['upcoming'], 0);

if (!$MaNTD) json_out(['ok'=>false,'error'=>'Thiếu MaNTD'], 400);

$cond = " t.MaNTD=? ";
$types = "i";
$args  = [$MaNTD];

if ($MaTin)     { $cond .= " AND ut.MaTin=? ";      $types.="i"; $args[]=$MaTin; }
if ($MaUngVien) { $cond .= " AND ut.MaUngVien=? ";  $types.="i"; $args[]=$MaUngVien; }
if ($onlyUpcoming) { $cond .= " AND lpv.NgayGioPhongVan >= NOW() "; }

$sql = db()->prepare("
  SELECT lpv.MaLichPV, lpv.MaUngTuyen, lpv.NgayGioPhongVan, lpv.HinhThuc, lpv.TrangThai, lpv.GhiChu,
         ut.MaTin, ut.MaUngVien, uv.HoTen, t.ChucDanh, t.DiaDiemLamViec
  FROM LichPhongVan lpv
  JOIN UngTuyen ut ON lpv.MaUngTuyen=ut.MaUngTuyen
  JOIN TinTuyenDung t ON ut.MaTin=t.MaTin
  JOIN UngVien uv ON ut.MaUngVien=uv.MaUngVien
  WHERE $cond
  ORDER BY lpv.NgayGioPhongVan DESC, lpv.MaLichPV DESC
");
$sql->bind_param($types, ...$args);
$sql->execute();
$res = $sql->get_result();

$items = [];
while ($r = $res->fetch_assoc()) $items[] = $r;

json_out(['ok'=>true,'items'=>$items]);
