<?php
// interface/API/API_chat_list_threads.php
require 'config.php';
require_method('GET');

$role       = inparam(['role']);
$MaUngVien  = (int) inparam(['MaUngVien', 'ma_ung_vien', 'maUV'], 0);
$MaNTD      = (int) inparam(['MaNTD', 'ma_ntd', 'maNTD'], 0);

// Nếu không truyền role/ids thì dùng session (nếu có)
if (!$role) {
  $u = current_user();
  if ($u && in_array($u['Role'], ['UngVien','NhaTuyenDung'], true)) {
    $role = $u['Role'];
    if ($role === 'UngVien' && !$MaUngVien) {
      $q = db()->prepare("SELECT MaUngVien FROM UngVien WHERE MaTK=? LIMIT 1");
      $q->bind_param("i", $u['MaTK']); $q->execute();
      if ($row = $q->get_result()->fetch_assoc()) $MaUngVien = (int)$row['MaUngVien'];
    }
    if ($role === 'NhaTuyenDung' && !$MaNTD) {
      $q = db()->prepare("SELECT MaNTD FROM NhaTuyenDung WHERE MaTK=? LIMIT 1");
      $q->bind_param("i", $u['MaTK']); $q->execute();
      if ($row = $q->get_result()->fetch_assoc()) $MaNTD = (int)$row['MaNTD'];
    }
  }
}

if (!in_array($role, ['UngVien','NhaTuyenDung'], true)) {
  json_out(['ok'=>false,'error'=>'role không hợp lệ'], 400);
}
if ($role === 'UngVien' && !$MaUngVien)  json_out(['ok'=>false,'error'=>'Thiếu MaUngVien'], 400);
if ($role === 'NhaTuyenDung' && !$MaNTD) json_out(['ok'=>false,'error'=>'Thiếu MaNTD'], 400);

if ($role === 'UngVien') {
  $sql = db()->prepare("
    SELECT th.MaThread, th.MaTin, th.MaUngVien, th.MaNTD, th.IsOpen, th.CreatedAt,
           ttd.ChucDanh, ttd.DiaDiemLamViec, ntd.TenCongTy,
           COALESCE(MAX(msg.CreatedAt), th.CreatedAt) AS LastActivity
    FROM ChatThread th
    JOIN TinTuyenDung ttd ON ttd.MaTin=th.MaTin
    JOIN NhaTuyenDung ntd ON ntd.MaNTD=th.MaNTD
    JOIN UngTuyen ut ON ut.MaTin=th.MaTin AND ut.MaUngVien=th.MaUngVien
    LEFT JOIN ChatMessage msg ON msg.MaThread=th.MaThread
    WHERE th.MaUngVien=? AND ut.TrangThai='MoiPhongVan' AND th.IsOpen=1
    GROUP BY th.MaThread
    ORDER BY LastActivity DESC, th.MaThread DESC
  ");
  $sql->bind_param("i", $MaUngVien);
} else {
  $sql = db()->prepare("
    SELECT th.MaThread, th.MaTin, th.MaUngVien, th.MaNTD, th.IsOpen, th.CreatedAt,
           ttd.ChucDanh, ttd.DiaDiemLamViec, uv.HoTen,
           COALESCE(MAX(msg.CreatedAt), th.CreatedAt) AS LastActivity
    FROM ChatThread th
    JOIN TinTuyenDung ttd ON ttd.MaTin=th.MaTin
    JOIN UngVien uv ON uv.MaUngVien=th.MaUngVien
    JOIN UngTuyen ut ON ut.MaTin=th.MaTin AND ut.MaUngVien=th.MaUngVien
    LEFT JOIN ChatMessage msg ON msg.MaThread=th.MaThread
    WHERE th.MaNTD=? AND ut.TrangThai='MoiPhongVan' AND th.IsOpen=1
    GROUP BY th.MaThread
    ORDER BY LastActivity DESC, th.MaThread DESC
  ");
  $sql->bind_param("i", $MaNTD);
}

$sql->execute();
$res = $sql->get_result();
$items = [];
while ($r = $res->fetch_assoc()) $items[] = $r;

json_out(['ok'=>true,'threads'=>$items]);
