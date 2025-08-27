<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status"=>"error","message"=>"Chỉ hỗ trợ POST"], JSON_UNESCAPED_UNICODE);
    exit;
  }

  $raw  = file_get_contents('php://input');
  $json = json_decode($raw, true) ?: [];
  $MaUngTuyen = $json['MaUngTuyen'] ?? $_POST['MaUngTuyen'] ?? null;
  $TrangThai  = $json['TrangThai']  ?? $_POST['TrangThai']  ?? null;

  if (!$MaUngTuyen || !$TrangThai) {
    http_response_code(422);
    echo json_encode(["status"=>"error","message"=>"Thiếu tham số MaUngTuyen hoặc TrangThai"], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // Validate trạng thái
  $allow = ['DangXet','MoiPhongVan','TuChoi'];
  if (!in_array($TrangThai, $allow, true)) {
    http_response_code(400);
    echo json_encode(["status"=>"error","message"=>"TrangThai không hợp lệ"], JSON_UNESCAPED_UNICODE);
    exit;
  }

  $stmt = $conn->prepare("UPDATE UngTuyen SET TrangThai=? WHERE MaUngTuyen=?");
  if (!$stmt) throw new Exception("Prepare failed: ".$conn->error);
  $id = (int)$MaUngTuyen;
  $stmt->bind_param("si", $TrangThai, $id);
  if (!$stmt->execute()) throw new Exception("Cập nhật thất bại");
  $affected = $stmt->affected_rows;
  $stmt->close();

  if ($affected > 0) {
    echo json_encode(["status"=>"success","message"=>"Cập nhật thành công"], JSON_UNESCAPED_UNICODE);
  } else {
    // Không đổi (ví dụ set đúng trạng thái hiện tại)
    echo json_encode(["status"=>"success","message"=>"Không có thay đổi"], JSON_UNESCAPED_UNICODE);
  }

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["status"=>"error","message"=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
  if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
