<?php
// interface/API/API_lpv_create.php
require 'config.php';
require_method('POST');

$body = json_decode(file_get_contents('php://input'), true) ?: [];

$MaNTD            = (int) ($body['MaNTD'] ?? inparam(['MaNTD'], 0));
$MaTin            = (int) ($body['MaTin'] ?? inparam(['MaTin'], 0));
$MaUngVien        = (int) ($body['MaUngVien'] ?? inparam(['MaUngVien'], 0));
$NgayGioPhongVan  = trim($body['NgayGioPhongVan'] ?? inparam(['NgayGioPhongVan'], ''));
$HinhThuc         = trim($body['HinhThuc'] ?? inparam(['HinhThuc'], 'Online'));
$GhiChu           = trim($body['GhiChu'] ?? inparam(['GhiChu'], ''));

if (!$MaNTD || !$MaTin || !$MaUngVien || !$NgayGioPhongVan) {
  json_out(['ok'=>false,'error'=>'Thiếu dữ liệu bắt buộc'], 400);
}

// Validate hình thức
$allow_ht = ['Online','Offline'];
if (!in_array($HinhThuc, $allow_ht, true)) {
  json_out(['ok'=>false,'error'=>'Hình thức không hợp lệ'], 400);
}

// Validate định dạng & không ở quá khứ
$dt = DateTime::createFromFormat('Y-m-d H:i:s', $NgayGioPhongVan);
$dt_errors = DateTime::getLastErrors();
if (!$dt || $dt_errors['warning_count'] || $dt_errors['error_count']) {
  json_out(['ok'=>false,'error'=>'Định dạng NgayGioPhongVan phải là YYYY-MM-DD HH:MM:SS'], 400);
}
$now = new DateTime('now');
if ($dt <= $now) {
  json_out(['ok'=>false,'error'=>'Thời điểm phỏng vấn phải ở tương lai'], 400);
}

// 1) Xác thực NTD sở hữu tin
$sqlTin = db()->prepare("SELECT MaTin FROM TinTuyenDung WHERE MaTin=? AND MaNTD=?");
$sqlTin->bind_param("ii", $MaTin, $MaNTD);
$sqlTin->execute();
if (!$sqlTin->get_result()->fetch_assoc()) {
  json_out(['ok'=>false,'error'=>'Không có quyền với tin tuyển dụng này'], 403);
}

// 2) Lấy MaUngTuyen từ cặp (MaTin, MaUngVien)
$sqlUT = db()->prepare("
  SELECT MaUngTuyen
  FROM UngTuyen
  WHERE MaTin=? AND MaUngVien=?
  LIMIT 1
");
$sqlUT->bind_param("ii", $MaTin, $MaUngVien);
$sqlUT->execute();
$ut = $sqlUT->get_result()->fetch_assoc();
if (!$ut) {
  json_out(['ok'=>false,'error'=>'Ứng viên chưa ứng tuyển tin này'], 400);
}
$MaUngTuyen = (int)$ut['MaUngTuyen'];

// (Tuỳ chọn) chặn trùng giờ trong cùng thread (nếu muốn chặt chẽ hơn)
// $checkDup = db()->prepare("SELECT MaLichPV FROM LichPhongVan WHERE MaUngTuyen=? AND NgayGioPhongVan=? LIMIT 1");
// $checkDup->bind_param("is", $MaUngTuyen, $NgayGioPhongVan);
// $checkDup->execute();
// if ($checkDup->get_result()->fetch_assoc()) {
//   json_out(['ok'=>false,'error'=>'Đã có lịch ở thời điểm này cho ứng viên này'], 409);
// }

// 3) Tạo lịch
$TrangThai = 'DaLenLich';
$ins = db()->prepare("
  INSERT INTO LichPhongVan (MaUngTuyen, NgayGioPhongVan, HinhThuc, TrangThai, GhiChu)
  VALUES (?, ?, ?, ?, ?)
");
$ins->bind_param("issss", $MaUngTuyen, $NgayGioPhongVan, $HinhThuc, $TrangThai, $GhiChu);

if (!$ins->execute()) {
  json_out(['ok'=>false,'error'=>'Không thể tạo lịch: '.$ins->error], 500);
}

$MaLichPV = db()->insert_id;
json_out(['ok'=>true,'MaLichPV'=>$MaLichPV]);
