<?php
// interface/API/API_show_DanhMucNghe.php
require_once __DIR__ . '/config.php';

// CORS & JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

try {
  $sql = "SELECT MaDanhMuc, TenDanhMuc FROM DanhMucNghe ORDER BY TenDanhMuc ASC";
  $rs  = $conn->query($sql);

  if (!$rs) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Query failed','detail'=>$conn->error], JSON_UNESCAPED_UNICODE);
    exit;
  }

  $data = [];
  while ($row = $rs->fetch_assoc()) {
    $data[] = $row;
  }

  echo json_encode(['ok'=>true, 'data'=>$data], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Lỗi lấy danh mục','detail'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
  if (isset($rs) && $rs instanceof mysqli_result) { $rs->free(); }
  if (isset($conn) && $conn instanceof mysqli) { $conn->close(); }
}
