<?php
require_once __DIR__ . '/config.php';

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(); }

try {
  $raw  = file_get_contents("php://input");
  $json = json_decode($raw, true) ?: [];
  $MaTin = $json['MaTin'] ?? $_POST['MaTin'] ?? $_GET['MaTin'] ?? null;

  if (!$MaTin) throw new Exception("Thiếu MaTin");

  $stmt = $conn->prepare("DELETE FROM TinTuyenDung WHERE MaTin=?");
  $id = (int)$MaTin;
  $stmt->bind_param("i", $id);
  if (!$stmt->execute()) throw new Exception("Xóa thất bại");
  $stmt->close();

  echo json_encode(['status'=>'success'], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(400);
  echo json_encode(['status'=>'error','message'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
  if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
