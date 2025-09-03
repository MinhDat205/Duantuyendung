<?php
// interface/API/config.php

/* ================================================
 * 1) Headers CORS + JSON
 * ================================================ */
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit;
}

/* ================================================
 * 2) Timezone + Session
 * ================================================ */
date_default_timezone_set('Asia/Ho_Chi_Minh');
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

/* ================================================
 * 3) DB Config (chỉnh đúng tên DB của bạn)
 * ================================================ */
$db_host = 'localhost';
$db_name = 'HeThongTuyenDung'; // Sửa đúng tên DB
$db_user = 'root';
$db_pass = '';
$db_port = 3306;

/* ================================================
 * 4) Kết nối mysqli (toàn cục)
 * ================================================ */
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'error' => 'Lỗi kết nối database',
    'detail' => $conn->connect_error
  ], JSON_UNESCAPED_UNICODE);
  exit;
}
$conn->set_charset('utf8mb4');

// Lưu vào biến global cho các hàm tiện ích
$GLOBALS['__mysqli'] = $conn;

/* ================================================
 * 5) Helpers chung
 * ================================================ */

// Lấy instance mysqli
function db(): mysqli {
  return $GLOBALS['__mysqli'];
}

// Trả JSON + status code
function json_out(array $data, int $status = 200): void {
  http_response_code($status);
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

// Bắt buộc method
function require_method(string $method): void {
  if (strcasecmp($_SERVER['REQUEST_METHOD'] ?? '', $method) !== 0) {
    json_out(['ok'=>false,'error'=>"Method phải là $method"], 405);
  }
}

// Đọc param từ GET/POST
function inparam(array $keys, $default = null) {
  foreach ($keys as $k) {
    if (isset($_GET[$k]))   return trim((string)$_GET[$k]);
    if (isset($_POST[$k]))  return trim((string)$_POST[$k]);
  }
  return $default;
}

// Đọc JSON body
function read_json_body(): array {
  $raw = file_get_contents('php://input');
  if ($raw === '' || $raw === false) return [];
  $data = json_decode($raw, true);
  if (json_last_error() !== JSON_ERROR_NONE) {
    json_out(['ok'=>false,'error'=>'JSON không hợp lệ'], 400);
  }
  return is_array($data) ? $data : [];
}

// Lấy user hiện tại từ session
function current_user(): ?array {
  return (!empty($_SESSION['auth']) && is_array($_SESSION['auth']))
    ? $_SESSION['auth']
    : null;
}

// Yêu cầu login
function require_login(): array {
  $u = current_user();
  if (!$u) json_out(['ok'=>false,'error'=>'Chưa đăng nhập'], 401);
  return $u;
}