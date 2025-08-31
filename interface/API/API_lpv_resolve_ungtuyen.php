<?php
// interface/API/API_lpv_resolve_ungtuyen.php
require 'config.php';
require_method('GET');

$MaNTD     = (int) inparam(['MaNTD','maNTD'], 0);
$MaTin     = (int) inparam(['MaTin','ma_tin'], 0);
$MaUngVien = (int) inparam(['MaUngVien','maUV','ma_ung_vien'], 0);

if (!$MaNTD || !$MaTin || !$MaUngVien) {
  json_out(['ok'=>false,'error'=>'Thiếu MaNTD/MaTin/MaUngVien'], 400);
}

// Tin phải thuộc NTD
$qTin = db()->prepare("SELECT MaTin, MaNTD, ChucDanh, DiaDiemLamViec FROM TinTuyenDung WHERE MaTin=? AND MaNTD=?");
$qTin->bind_param("ii", $MaTin, $MaNTD);
$qTin->execute();
$Tin = $qTin->get_result()->fetch_assoc();
if (!$Tin) json_out(['ok'=>false,'error'=>'Tin không thuộc NTD hoặc không tồn tại'], 400);

// Tìm đơn ứng tuyển
$qUT = db()->prepare("SELECT MaUngTuyen, TrangThai FROM UngTuyen WHERE MaTin=? AND MaUngVien=? LIMIT 1");
$qUT->bind_param("ii", $MaTin, $MaUngVien);
$qUT->execute();
$UT = $qUT->get_result()->fetch_assoc();
if (!$UT) json_out(['ok'=>false,'error'=>'Không tìm thấy đơn ứng tuyển'], 400);

// Thông tin ứng viên
$qUV = db()->prepare("SELECT uv.MaUngVien, tk.Email, uv.HoTen 
                      FROM UngVien uv JOIN TaiKhoan tk ON uv.MaTK=tk.MaTK 
                      WHERE uv.MaUngVien=?");
$qUV->bind_param("i", $MaUngVien);
$qUV->execute();
$UV = $qUV->get_result()->fetch_assoc();

json_out(['ok'=>true, 'MaUngTuyen'=>(int)$UT['MaUngTuyen'], 'TrangThaiUngTuyen'=>$UT['TrangThai'], 'Tin'=>$Tin, 'UngVien'=>$UV]);
