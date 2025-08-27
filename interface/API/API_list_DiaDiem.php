<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

try {
  $sql = "SELECT DISTINCT TRIM(DiaDiemLamViec) AS DiaDiem FROM TinTuyenDung WHERE TrangThai='DaDuyet' AND DiaDiemLamViec IS NOT NULL AND DiaDiemLamViec<>'' ORDER BY DiaDiemLamViec";
  $res = $conn->query($sql);
  $list = [];
  while ($row = $res->fetch_assoc()) { $list[] = $row['DiaDiem']; }
  echo json_encode(['ok'=>true,'data'=>$list], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Lỗi server: '.$e->getMessage()]);
} finally {
  if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
