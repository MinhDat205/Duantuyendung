<?php
// Tắt hiển thị lỗi HTML
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

try {
  // Test kết nối mysqli
  $connection_info = [
    'mysqli_host' => $conn->host_info ?? 'unknown',
    'mysqli_port' => $conn->connect_port ?? 'unknown',
    'mysqli_database' => $conn->database ?? 'unknown',
    'mysqli_charset' => $conn->character_set_name() ?? 'unknown'
  ];

  // Test query dữ liệu
  $stmt = $conn->prepare('SELECT COUNT(*) as total FROM UngTuyen');
  $stmt->execute();
  $res = $stmt->get_result();
  $count = $res->fetch_assoc();
  $stmt->close();

  // Test query cụ thể
  $stmt = $conn->prepare('SELECT MaUngTuyen, MaTin, MaUngVien, TrangThai FROM UngTuyen LIMIT 5');
  $stmt->execute();
  $res = $stmt->get_result();
  $data = [];
  while ($row = $res->fetch_assoc()) {
    $data[] = $row;
  }
  $stmt->close();

  // Test PDO connection
  $pdo = getConnection();
  $pdo_info = [
    'pdo_dsn' => $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS),
    'pdo_database' => $pdo->query('SELECT DATABASE()')->fetchColumn()
  ];

  echo json_encode([
    'ok' => true,
    'connection_info' => $connection_info,
    'pdo_info' => $pdo_info,
    'total_ungtuyen' => $count['total'],
    'sample_data' => $data
  ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Lỗi: '.$e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
  if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
?>
