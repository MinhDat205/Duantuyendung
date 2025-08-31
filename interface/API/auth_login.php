<?php
// interface/API/auth_login.php
require_once __DIR__ . '/config.php';

// Helper function để lấy trường an toàn từ nhiều nguồn
function get_input($keys, $src1, $src2) {
  foreach ((array)$keys as $k) {
    if (isset($src1[$k]) && $src1[$k] !== '') return trim($src1[$k]);
    if (isset($src2[$k]) && $src2[$k] !== '') return trim($src2[$k]);
  }
  return null;
}

// Đọc dữ liệu từ cả JSON và form
$raw  = file_get_contents("php://input");
$json = json_decode($raw, true);
$post = $_POST;

// Lấy dữ liệu với các alias key
$password = get_input(['password','matkhau'], $json, $post);
$email    = get_input(['email'], $json, $post);

// Debug JSON hỏng
if ($json === null && $raw !== '' && empty($post)) {
  http_response_code(400);
  echo json_encode([
    'ok' => false,
    'error' => 'JSON không hợp lệ: ' . json_last_error_msg(),
    'raw_body' => mb_substr($raw, 0, 200),
    'json_error' => json_last_error()
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

if (!$email || !$password) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Thiếu email hoặc mật khẩu'], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  $stmt = $conn->prepare("SELECT MaTK, Email, MatKhau, LoaiTaiKhoan, TrangThai FROM TaiKhoan WHERE Email = ? LIMIT 1");
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $res  = $stmt->get_result();
  $user = $res->fetch_assoc();
  $stmt->close();

  if (!$user) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'Sai email hoặc mật khẩu'], JSON_UNESCAPED_UNICODE); exit; }
  if ($user['TrangThai'] !== 'HoatDong') { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Tài khoản bị khoá'], JSON_UNESCAPED_UNICODE); exit; }
  if (!password_verify($password, $user['MatKhau'])) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'Sai email hoặc mật khẩu'], JSON_UNESCAPED_UNICODE); exit; }

  // KHÔNG gọi session_start() lần 2 (config.php đã start nếu cần)
  if (session_status() === PHP_SESSION_NONE) { @session_start(); }
  $_SESSION['user_id']    = (int)$user['MaTK'];
  $_SESSION['user_type']  = $user['LoaiTaiKhoan'];
  $_SESSION['user_email'] = $user['Email'];

  if ($user['LoaiTaiKhoan'] === 'UngVien') {
    $stmt = $conn->prepare("SELECT MaUngVien, HoTen FROM UngVien WHERE MaTK = ?");
    $stmt->bind_param('i', $user['MaTK']);
    $stmt->execute();
    $res = $stmt->get_result();
    $u   = $res->fetch_assoc();
    $stmt->close();

    http_response_code(200);
    echo json_encode(['ok'=>true,'data'=>[
      'MaTK'          => (int)$user['MaTK'],
      'MaTaiKhoan'    => (int)$user['MaTK'],
      'LoaiTaiKhoan'  => $user['LoaiTaiKhoan'],
      'Email'         => $user['Email'],
      'MaUngVien'     => $u['MaUngVien'] ?? null,
      'MaUngvien'     => $u['MaUngVien'] ?? null, // giữ alias bạn đang dùng ở FE
      'HoTen'         => $u['HoTen'] ?? null
    ]], JSON_UNESCAPED_UNICODE);
  } else {
    $stmt = $conn->prepare("SELECT MaNTD, TenCongTy FROM NhaTuyenDung WHERE MaTK = ?");
    $stmt->bind_param('i', $user['MaTK']);
    $stmt->execute();
    $res = $stmt->get_result();
    $c   = $res->fetch_assoc();
    $stmt->close();

    http_response_code(200);
    echo json_encode(['ok'=>true,'data'=>[
      'MaTK'            => (int)$user['MaTK'],
      'MaTaiKhoan'      => (int)$user['MaTK'],
      'LoaiTaiKhoan'    => $user['LoaiTaiKhoan'],
      'Email'           => $user['Email'],
      'MaNTD'           => $c['MaNTD'] ?? null,
      'MaNhatuyendung'  => $c['MaNTD'] ?? null, // giữ alias bạn đang dùng ở FE
      'TenCongTy'       => $c['TenCongTy'] ?? null
    ]], JSON_UNESCAPED_UNICODE);
  }

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Đăng nhập thất bại','detail'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
  if (isset($conn) && $conn instanceof mysqli) { $conn->close(); }
}
