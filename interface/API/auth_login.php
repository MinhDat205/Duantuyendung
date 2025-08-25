<?php
// interface/API/auth_login.php
require_once __DIR__ . '/config.php';

$body = read_json_body();
if (!isset($body['email'],$body['password'])) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Thiếu email hoặc mật khẩu'], JSON_UNESCAPED_UNICODE); exit;
}

$email = trim($body['email']);
$password = (string)$body['password'];

try {
  $stmt = $conn->prepare("SELECT MaTK, Email, MatKhau, LoaiTaiKhoan, TrangThai FROM TaiKhoan WHERE Email = ? LIMIT 1");
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $res = $stmt->get_result();
  $user = $res->fetch_assoc();
  $stmt->close();

  if (!$user) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'Sai email hoặc mật khẩu'], JSON_UNESCAPED_UNICODE); exit; }
  if ($user['TrangThai'] !== 'HoatDong') { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Tài khoản bị khoá'], JSON_UNESCAPED_UNICODE); exit; }
  if (!password_verify($password, $user['MatKhau'])) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'Sai email hoặc mật khẩu'], JSON_UNESCAPED_UNICODE); exit; }

  if ($user['LoaiTaiKhoan'] === 'UngVien') {
    $stmt = $conn->prepare("SELECT MaUngVien, HoTen FROM UngVien WHERE MaTK = ?");
    $stmt->bind_param('i', $user['MaTK']);
    $stmt->execute();
    $res = $stmt->get_result();
    $u = $res->fetch_assoc();
    $stmt->close();

    echo json_encode(['ok'=>true,'data'=>[
      'MaTK'=>(int)$user['MaTK'],
      'LoaiTaiKhoan'=>$user['LoaiTaiKhoan'],
      'Email'=>$user['Email'],
      'MaUngVien'=>$u['MaUngVien'] ?? null,
      'HoTen'=>$u['HoTen'] ?? null
    ]], JSON_UNESCAPED_UNICODE);

  } else {
    $stmt = $conn->prepare("SELECT MaNTD, TenCongTy FROM NhaTuyenDung WHERE MaTK = ?");
    $stmt->bind_param('i', $user['MaTK']);
    $stmt->execute();
    $res = $stmt->get_result();
    $c = $res->fetch_assoc();
    $stmt->close();

    echo json_encode(['ok'=>true,'data'=>[
      'MaTK'=>(int)$user['MaTK'],
      'LoaiTaiKhoan'=>$user['LoaiTaiKhoan'],
      'Email'=>$user['Email'],
      'MaNTD'=>$c['MaNTD'] ?? null,
      'TenCongTy'=>$c['TenCongTy'] ?? null
    ]], JSON_UNESCAPED_UNICODE);
  }

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Đăng nhập thất bại','detail'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
  if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
