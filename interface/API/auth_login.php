<?php
require_once __DIR__ . '/config.php';

// Chỉ cho phép POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
  exit;
}

// Helper lấy input
function get_input($keys, $src1, $src2) {
  foreach ((array)$keys as $k) {
    if (isset($src1[$k]) && $src1[$k] !== '') return trim($src1[$k]);
    if (isset($src2[$k]) && $src2[$k] !== '') return trim($src2[$k]);
  }
  return null;
}

// Đọc dữ liệu JSON + form
$raw  = file_get_contents("php://input");
$json = json_decode($raw, true);
$post = $_POST;

// Nếu body có nội dung nhưng JSON lỗi -> 400
if ($json === null && !empty($raw) && empty($post)) {
  http_response_code(400);
  echo json_encode([
    'ok' => false,
    'error' => 'JSON không hợp lệ: ' . json_last_error_msg(),
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

$password = get_input(['password','matkhau'], $json, $post);
$email    = get_input(['email'], $json, $post);
$email    = $email ? strtolower($email) : null;

if (!$email || !$password) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Thiếu email hoặc mật khẩu'], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  // Tìm tài khoản
  $stmt = $conn->prepare("SELECT MaTK, Email, MatKhau, LoaiTaiKhoan, TrangThai FROM TaiKhoan WHERE Email = ? LIMIT 1");
  if (!$stmt) { throw new Exception('Prepare failed: '.$conn->error); }
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $res  = $stmt->get_result();
  $user = $res->fetch_assoc();
  $stmt->close();

  if (!$user || !password_verify($password, $user['MatKhau'])) {
    http_response_code(401);
    echo json_encode(['ok'=>false,'error'=>'Sai email hoặc mật khẩu'], JSON_UNESCAPED_UNICODE);
    exit;
  }
  if ($user['TrangThai'] !== 'HoatDong') {
    http_response_code(403);
    echo json_encode(['ok'=>false,'error'=>'Tài khoản bị khoá'], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // Tạo session
  session_start();
  $_SESSION['user_id']    = (int)$user['MaTK'];
  $_SESSION['user_type']  = $user['LoaiTaiKhoan'];
  $_SESSION['user_email'] = $user['Email'];

  // Trả thêm thông tin theo loại tài khoản
  if ($user['LoaiTaiKhoan'] === 'UngVien') {
    $stmt = $conn->prepare("SELECT MaUngvien, HoTen FROM UngVien WHERE MaTK = ? LIMIT 1");
    if ($stmt) {
      $stmt->bind_param('i', $user['MaTK']);
      $stmt->execute();
      $res = $stmt->get_result();
      $u   = $res->fetch_assoc();
      $stmt->close();
    }
    echo json_encode([
      'ok'=>true,
      'data'=>[
        // CHUẨN HÓA TÊN KHÓA:
        'MaTK'          => (int)$user['MaTK'],
        'MaTaiKhoan'    => (int)$user['MaTK'], // giữ tạm để không phá chỗ khác
        'LoaiTaiKhoan'  => $user['LoaiTaiKhoan'],
        'Email'         => $user['Email'],
        'MaUngvien'     => isset($u['MaUngvien']) ? (int)$u['MaUngvien'] : null,
        'HoTen'         => $u['HoTen'] ?? null
      ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
  } else {
    $stmt = $conn->prepare("SELECT MaNTD, TenCongTy FROM NhaTuyenDung WHERE MaTK = ? LIMIT 1");
    if ($stmt) {
      $stmt->bind_param('i', $user['MaTK']);
      $stmt->execute();
      $res = $stmt->get_result();
      $c   = $res->fetch_assoc();
      $stmt->close();
    }
    echo json_encode([
      'ok'=>true,
      'data'=>[
        // CHUẨN HÓA TÊN KHÓA:
        'MaTK'            => (int)$user['MaTK'],
        'MaTaiKhoan'      => (int)$user['MaTK'], // giữ tạm
        'LoaiTaiKhoan'    => $user['LoaiTaiKhoan'],
        'Email'           => $user['Email'],
        'MaNhatuyendung'  => isset($c['MaNTD']) ? (int)$c['MaNTD'] : null,
        'TenCongTy'       => $c['TenCongTy'] ?? null
      ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Đăng nhập thất bại','detail'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
  if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
