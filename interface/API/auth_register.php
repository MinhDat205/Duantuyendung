<?php
// interface/API/auth_register.php
require_once __DIR__ . '/config.php';

$body = read_json_body();
$required = ['email','password','loaiTaiKhoan'];
foreach ($required as $f) {
  if (!isset($body[$f]) || $body[$f]==='') {
    http_response_code(422);
    echo json_encode(['ok'=>false,'error'=>"Thiếu trường: $f"], JSON_UNESCAPED_UNICODE);
    exit;
  }
}

$email = trim($body['email']);
$password = (string)$body['password'];
$role = $body['loaiTaiKhoan']; // 'UngVien' | 'NhaTuyenDung'
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Email không hợp lệ'], JSON_UNESCAPED_UNICODE); exit;
}
if (!in_array($role, ['UngVien','NhaTuyenDung'], true)) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Loại tài khoản không hợp lệ'], JSON_UNESCAPED_UNICODE); exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$hoTen = $body['hoTen'] ?? null;
$tenCongTy = $body['tenCongTy'] ?? null;
$soDienThoai = $body['soDienThoai'] ?? null;
$diaChi = $body['diaChi'] ?? null;
$danhMucIds = $body['danhMucIds'] ?? [];
$anhCV = $body['anhCV'] ?? null; // uploads/cv/xxx.png

try {
  $conn->begin_transaction();

  // 1) tạo tài khoản
  $stmt = $conn->prepare("INSERT INTO TaiKhoan (Email, MatKhau, LoaiTaiKhoan) VALUES (?, ?, ?)");
  $stmt->bind_param('sss', $email, $hash, $role);
  if (!$stmt->execute()) {
    if ($conn->errno === 1062) {
      $conn->rollback();
      http_response_code(409);
      echo json_encode(['ok'=>false,'error'=>'Email đã tồn tại'], JSON_UNESCAPED_UNICODE); exit;
    }
    throw new Exception($conn->error);
  }
  $maTK = $conn->insert_id;
  $stmt->close();

  if ($role === 'UngVien') {
    if (!$hoTen) { throw new Exception('Thiếu họ tên ứng viên'); }
    // 2) hồ sơ Ứng viên (có AnhCV)
    $stmt = $conn->prepare("INSERT INTO UngVien (MaTK, HoTen, SoDienThoai, AnhCV) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isss', $maTK, $hoTen, $soDienThoai, $anhCV);
    if (!$stmt->execute()) throw new Exception($conn->error);
    $maUngVien = $conn->insert_id;
    $stmt->close();

    // 3) gán ngành
    if (is_array($danhMucIds) && $danhMucIds) {
      $stmt = $conn->prepare("INSERT INTO UngVien_DanhMuc (MaUngVien, MaDanhMuc) VALUES (?, ?)");
      foreach ($danhMucIds as $id) {
        if (is_numeric($id)) { $id = (int)$id; $stmt->bind_param('ii', $maUngVien, $id); $stmt->execute(); }
      }
      $stmt->close();
    }

    $conn->commit();
    echo json_encode(['ok'=>true, 'data'=>['MaTK'=>$maTK,'LoaiTaiKhoan'=>$role,'MaUngVien'=>$maUngVien]], JSON_UNESCAPED_UNICODE);
    exit;

  } else {
    if (!$tenCongTy) { throw new Exception('Thiếu tên công ty'); }
    // 2) hồ sơ NTD
    $stmt = $conn->prepare("INSERT INTO NhaTuyenDung (MaTK, TenCongTy, SoDienThoai, DiaChi) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isss', $maTK, $tenCongTy, $soDienThoai, $diaChi);
    if (!$stmt->execute()) throw new Exception($conn->error);
    $maNTD = $conn->insert_id;
    $stmt->close();

    // 3) gán ngành
    if (is_array($danhMucIds) && $danhMucIds) {
      $stmt = $conn->prepare("INSERT INTO NhaTuyenDung_DanhMuc (MaNTD, MaDanhMuc) VALUES (?, ?)");
      foreach ($danhMucIds as $id) {
        if (is_numeric($id)) { $id = (int)$id; $stmt->bind_param('ii', $maNTD, $id); $stmt->execute(); }
      }
      $stmt->close();
    }

    $conn->commit();
    echo json_encode(['ok'=>true, 'data'=>['MaTK'=>$maTK,'LoaiTaiKhoan'=>$role,'MaNTD'=>$maNTD]], JSON_UNESCAPED_UNICODE);
    exit;
  }

} catch (Throwable $e) {
  $conn->rollback();
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Đăng ký thất bại','detail'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
  if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
