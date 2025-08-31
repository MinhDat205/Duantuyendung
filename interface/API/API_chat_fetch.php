<?php
// interface/API/API_chat_fetch.php
require 'config.php';
require_method('GET');

$MaThread = (int) inparam(['MaThread','ma_thread'], 0);
$limit    = (int) inparam(['limit'], 20);
$since    = trim(inparam(['since'], ''));

if (!$MaThread) json_out(['ok'=>false,'error'=>'Thiếu MaThread'], 400);
if ($limit <= 0) $limit = 20;

// Kiểm tra thread tồn tại & open
$ck = db()->prepare("SELECT MaThread FROM ChatThread WHERE MaThread=? AND IsOpen=1 LIMIT 1");
$ck->bind_param("i", $MaThread);
$ck->execute();
if (!$ck->get_result()->fetch_assoc()) {
  json_out(['ok'=>false,'error'=>'Thread không tồn tại hoặc đã đóng'], 400);
}

$messages = [];
if ($since !== '') {
  $sql = db()->prepare("
    SELECT MaMsg, MaThread, SenderType, MaTK, NoiDung, CreatedAt, IsRead
    FROM ChatMessage
    WHERE MaThread=? AND CreatedAt > ?
    ORDER BY CreatedAt ASC, MaMsg ASC
    LIMIT ?
  ");
  $sql->bind_param("isi", $MaThread, $since, $limit);
  $sql->execute();
  $res = $sql->get_result();
  while ($r = $res->fetch_assoc()) $messages[] = $r;
} else {
  // Lấy newest limit rồi đảo thành oldest->newest
  $sql = db()->prepare("
    SELECT MaMsg, MaThread, SenderType, MaTK, NoiDung, CreatedAt, IsRead
    FROM ChatMessage
    WHERE MaThread=?
    ORDER BY CreatedAt DESC, MaMsg DESC
    LIMIT ?
  ");
  $sql->bind_param("ii", $MaThread, $limit);
  $sql->execute();
  $res = $sql->get_result();
  while ($r = $res->fetch_assoc()) $messages[] = $r;
  $messages = array_reverse($messages);
}

json_out(['ok'=>true,'messages'=>$messages]);
