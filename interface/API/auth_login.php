<?php
// API/auth_login.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/utils.php';

$body = read_json();
require_fields($body, ['email','password']);

$email = trim($body['email']);
$password = (string)$body['password'];

try {
  $stmt = $pdo->prepare("SELECT MaTK, Email, MatKhau, LoaiTaiKhoan, TrangThai FROM TaiKhoan WHERE Email = ? LIMIT 1");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if (!$user) json_error('Sai email hoặc mật khẩu', 401);
  if ($user['TrangThai'] !== 'HoatDong') json_error('Tài khoản bị khoá', 403);
  if (!password_verify($password, $user['MatKhau'])) json_error('Sai email hoặc mật khẩu', 401);

  // Lấy info theo role
  if ($user['LoaiTaiKhoan'] === 'UngVien') {
    $q = $pdo->prepare("SELECT MaUngVien, HoTen FROM UngVien WHERE MaTK = ?");
    $q->execute([$user['MaTK']]);
    $u = $q->fetch();
    json_ok([
      'MaTK' => (int)$user['MaTK'],
      'LoaiTaiKhoan' => $user['LoaiTaiKhoan'],
      'Email' => $user['Email'],
      'MaUngVien' => $u['MaUngVien'] ?? null,
      'HoTen' => $u['HoTen'] ?? null
    ]);
  } else {
    $q = $pdo->prepare("SELECT MaNTD, TenCongTy FROM NhaTuyenDung WHERE MaTK = ?");
    $q->execute([$user['MaTK']]);
    $c = $q->fetch();
    json_ok([
      'MaTK' => (int)$user['MaTK'],
      'LoaiTaiKhoan' => $user['LoaiTaiKhoan'],
      'Email' => $user['Email'],
      'MaNTD' => $c['MaNTD'] ?? null,
      'TenCongTy' => $c['TenCongTy'] ?? null
    ]);
  }

} catch (Exception $e) {
  json_error('Đăng nhập thất bại', 500, ['detail'=>$e->getMessage()]);
}
