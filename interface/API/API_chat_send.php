<?php
// interface/API/API_chat_send.php
require 'config.php';
require_method('POST');

$body = json_decode(file_get_contents('php://input'), true) ?: [];

$MaThread   = (int) ($body['MaThread']   ?? inparam(['MaThread'], 0));
$SenderType = trim($body['SenderType']   ?? inparam(['SenderType'], ''));
$NoiDung    = trim($body['NoiDung']      ?? inparam(['NoiDung'], ''));
$MaTK       = isset($body['MaTK']) ? (int)$body['MaTK'] : (int) inparam(['MaTK'], 0);
if ($MaTK === 0) $MaTK = null;

if (!$MaThread || !$SenderType || $NoiDung==='') {
  json_out(['ok'=>false,'error'=>'Thiếu dữ liệu bắt buộc'], 400);
}
if (!in_array($SenderType, ['UngVien','NhaTuyenDung'], true)) {
  json_out(['ok'=>false,'error'=>'SenderType không hợp lệ'], 400);
}

// Cắt nội dung để an toàn
if (mb_strlen($NoiDung, 'UTF-8') > 5000) {
  $NoiDung = mb_substr($NoiDung, 0, 5000, 'UTF-8');
}

// Resolve thread & validate
$q = db()->prepare("
  SELECT th.MaThread, th.IsOpen, ut.TrangThai
  FROM ChatThread th
  JOIN UngTuyen ut ON ut.MaTin=th.MaTin AND ut.MaUngVien=th.MaUngVien
  WHERE th.MaThread=? LIMIT 1
");
$q->bind_param("i", $MaThread);
$q->execute();
$th = $q->get_result()->fetch_assoc();
if (!$th) json_out(['ok'=>false,'error'=>'Thread không tồn tại'], 400);
if ((int)$th['IsOpen'] !== 1) json_out(['ok'=>false,'error'=>'Thread đã đóng'], 400);
if ($th['TrangThai'] !== 'MoiPhongVan') {
  json_out(['ok'=>false,'error'=>'Chỉ được chat khi đơn ở trạng thái MoiPhongVan'], 400);
}

// (Tùy chọn) kiểm tra quyền bằng session/current_user nếu cần

// Insert
$ins = db()->prepare("
  INSERT INTO ChatMessage (MaThread, SenderType, MaTK, NoiDung)
  VALUES (?,?,?,?)
");
if ($MaTK === null) {
  // bind_param không nhận null trực tiếp -> set type phù hợp và dùng NULL qua set_null?
  // Cách đơn giản: ép 0 và để FK SET NULL không áp dụng, nên ta dùng NULL bằng cách dynamic SQL:
  $ins = db()->prepare("
    INSERT INTO ChatMessage (MaThread, SenderType, MaTK, NoiDung)
    VALUES (?,?,NULL,?)
  ");
  $ins->bind_param("iss", $MaThread, $SenderType, $NoiDung);
  $ok = $ins->execute();
} else {
  $ins->bind_param("isis", $MaThread, $SenderType, $MaTK, $NoiDung);
  $ok = $ins->execute();
}

if (!$ok) json_out(['ok'=>false,'error'=>'Gửi tin nhắn thất bại'], 400);
$MaMsg = db()->insert_id;

json_out(['ok'=>true, 'MaMsg'=>(int)$MaMsg]);
