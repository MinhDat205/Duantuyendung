<?php
// API/auth_register.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/utils.php';

$body = read_json();
require_fields($body, ['email','password','loaiTaiKhoan']);

$email = trim($body['email']);
$password = (string)$body['password'];
$role = $body['loaiTaiKhoan']; // 'UngVien' | 'NhaTuyenDung'

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  json_error('Email không hợp lệ', 422);
}
if (!in_array($role, ['UngVien','NhaTuyenDung'], true)) {
  json_error('Loại tài khoản không hợp lệ', 422);
}
$hash = password_hash($password, PASSWORD_DEFAULT);

// Thông tin riêng theo role
$hoTen = $body['hoTen'] ?? null;
$tenCongTy = $body['tenCongTy'] ?? null;
$soDienThoai = $body['soDienThoai'] ?? null;
$diaChi = $body['diaChi'] ?? null;
$danhMucIds = $body['danhMucIds'] ?? [];

try {
  $pdo->beginTransaction();

  // 1) Tạo tài khoản
  $stmt = $pdo->prepare("INSERT INTO TaiKhoan (Email, MatKhau, LoaiTaiKhoan) VALUES (?, ?, ?)");
  $stmt->execute([$email, $hash, $role]);
  $maTK = (int)$pdo->lastInsertId();

  if ($role === 'UngVien') {
    if (!$hoTen) json_error('Thiếu họ tên ứng viên', 422);
    // 2) Tạo hồ sơ ứng viên
    $stmt = $pdo->prepare("INSERT INTO UngVien (MaTK, HoTen, SoDienThoai) VALUES (?, ?, ?)");
    $stmt->execute([$maTK, $hoTen, $soDienThoai]);
    $maUngVien = (int)$pdo->lastInsertId();

    // 3) Gán ngành nhiều-nhiều
    if (is_array($danhMucIds) && count($danhMucIds)) {
      $ins = $pdo->prepare("INSERT INTO UngVien_DanhMuc (MaUngVien, MaDanhMuc) VALUES (?, ?)");
      foreach ($danhMucIds as $id) {
        if (is_numeric($id)) $ins->execute([$maUngVien, (int)$id]);
      }
    }

    $pdo->commit();
    json_ok([
      'MaTK' => $maTK,
      'LoaiTaiKhoan' => $role,
      'MaUngVien' => $maUngVien
    ], 201);

  } else {
    if (!$tenCongTy) json_error('Thiếu tên công ty', 422);
    // 2) Tạo hồ sơ NTD
    $stmt = $pdo->prepare("INSERT INTO NhaTuyenDung (MaTK, TenCongTy, SoDienThoai, DiaChi) VALUES (?, ?, ?, ?)");
    $stmt->execute([$maTK, $tenCongTy, $soDienThoai, $diaChi]);
    $maNTD = (int)$pdo->lastInsertId();

    // 3) Gán ngành nhiều-nhiều
    if (is_array($danhMucIds) && count($danhMucIds)) {
      $ins = $pdo->prepare("INSERT INTO NhaTuyenDung_DanhMuc (MaNTD, MaDanhMuc) VALUES (?, ?)");
      foreach ($danhMucIds as $id) {
        if (is_numeric($id)) $ins->execute([$maNTD, (int)$id]);
      }
    }

    $pdo->commit();
    json_ok([
      'MaTK' => $maTK,
      'LoaiTaiKhoan' => $role,
      'MaNTD' => $maNTD
    ], 201);
  }

} catch (PDOException $e) {
  $pdo->rollBack();
  if ($e->errorInfo[1] === 1062) { // duplicate
    json_error('Email đã tồn tại', 409);
  }
  json_error('Đăng ký thất bại', 500, ['detail'=>$e->getMessage()]);
} catch (Exception $e) {
  $pdo->rollBack();
  json_error('Lỗi hệ thống', 500, ['detail'=>$e->getMessage()]);
}
