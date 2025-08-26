<?php
// interface/API/auth_register.php
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
$raw = file_get_contents("php://input");
$json = json_decode($raw, true);
$post = $_POST;

// Lấy dữ liệu với các alias key
$password   = get_input(['password','matkhau'], $json, $post);
$email      = get_input(['email'], $json, $post);
$loaiTK     = get_input(['loaiTaiKhoan','loai_tai_khoan'], $json, $post);
$ten        = get_input(['ten','hoTen','tenCongTy'], $json, $post);
$sdt        = get_input(['sdt','soDienThoai'], $json, $post);
$diachi     = get_input(['diachi','diaChi'], $json, $post);
$nganhNghe  = $json['nganhNghe'] ?? ($post['nganhNghe'] ?? []);

// Xử lý ngành nghề
if (is_string($nganhNghe)) { 
    $nganhNghe = array_filter(array_map('trim', explode(',', $nganhNghe))); 
}

// Debug: Log raw body nếu có lỗi
if ($json === null && !empty($raw) && empty($post)) {
    $error = json_last_error_msg();
    echo json_encode([
        'ok' => false, 
        'error' => 'JSON không hợp lệ: ' . $error,
        'raw_body' => $raw,
        'json_error' => json_last_error()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Kiểm tra các trường bắt buộc
$required = ['email','password','loaiTaiKhoan'];
foreach ($required as $f) {
  $value = null;
  if ($f === 'email') $value = $email;
  elseif ($f === 'password') $value = $password;
  elseif ($f === 'loaiTaiKhoan') $value = $loaiTK;
  
  if (!$value || $value === '') {
    http_response_code(422);
    echo json_encode(['ok'=>false,'error'=>"Thiếu trường: $f"], JSON_UNESCAPED_UNICODE);
    exit;
  }
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Email không hợp lệ'], JSON_UNESCAPED_UNICODE); exit;
}
if (!in_array($loaiTK, ['UngVien','NhaTuyenDung'], true)) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Loại tài khoản không hợp lệ'], JSON_UNESCAPED_UNICODE); exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

// Xử lý dữ liệu theo loại tài khoản
if ($loaiTK === 'UngVien') {
    $hoTen = $ten;
    $soDienThoai = $sdt;
    $anhCV = $json['anhCV'] ?? $post['anhCV'] ?? null;
    $danhMucIds = $nganhNghe;
} else {
    $tenCongTy = $ten;
    $soDienThoai = $sdt;
    $diaChi = $diachi;
    $danhMucIds = $nganhNghe;
}

try {
  $conn->begin_transaction();

  // 1) tạo tài khoản
  $stmt = $conn->prepare("INSERT INTO TaiKhoan (Email, MatKhau, LoaiTaiKhoan, TrangThai) VALUES (?, ?, ?, 'HoatDong')");
  $stmt->bind_param('sss', $email, $hash, $loaiTK);
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

  if ($loaiTK === 'UngVien') {
    if (!$hoTen) { throw new Exception('Thiếu họ tên ứng viên'); }
    // 2) hồ sơ Ứng viên (có AnhCV)
    $stmt = $conn->prepare("INSERT INTO Ungvien (MaTK, HoTen, SoDienThoai, AnhCV) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isss', $maTK, $hoTen, $soDienThoai, $anhCV);
    if (!$stmt->execute()) throw new Exception($conn->error);
    $maUngVien = $conn->insert_id;
    $stmt->close();

    // 3) gán ngành nghề (tạm thời bỏ qua vì cần cấu trúc database phù hợp)
    // TODO: Cần tạo bảng DanhMuc và bảng quan hệ Ungvien_DanhMuc
    // if (is_array($danhMucIds) && $danhMucIds) {
    //   $stmt = $conn->prepare("INSERT INTO Ungvien_DanhMuc (MaUngvien, MaDanhMuc) VALUES (?, ?)");
    //   foreach ($danhMucIds as $id) {
    //     if (is_numeric($id)) { $id = (int)$id; $stmt->bind_param('ii', $maUngVien, $id); $stmt->execute(); }
    //   }
    //   $stmt->close();
    // }

    $conn->commit();
    echo json_encode(['ok'=>true, 'data'=>['MaTaiKhoan'=>$maTK,'LoaiTaiKhoan'=>$loaiTK,'MaUngvien'=>$maUngVien]], JSON_UNESCAPED_UNICODE);
    exit;

  } else {
    if (!$tenCongTy) { throw new Exception('Thiếu tên công ty'); }
    // 2) hồ sơ NTD
    $stmt = $conn->prepare("INSERT INTO NhaTuyenDung (MaTK, TenCongTy, SoDienThoai, DiaChi) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isss', $maTK, $tenCongTy, $soDienThoai, $diaChi);
    if (!$stmt->execute()) throw new Exception($conn->error);
    $maNTD = $conn->insert_id;
    $stmt->close();

    // 3) gán ngành nghề (tạm thời bỏ qua vì cần cấu trúc database phù hợp)
    // TODO: Cần tạo bảng DanhMuc và bảng quan hệ Nhatuyendung_DanhMuc
    // if (is_array($danhMucIds) && $danhMucIds) {
    //   $stmt = $conn->prepare("INSERT INTO Nhatuyendung_DanhMuc (MaNhatuyendung, MaDanhMuc) VALUES (?, ?)");
    //   foreach ($danhMucIds as $id) {
    //     if (is_numeric($id)) { $id = (int)$id; $stmt->bind_param('ii', $maNTD, $id); $stmt->execute(); }
    //   }
    //   $stmt->close();
    // }

    $conn->commit();
    echo json_encode(['ok'=>true, 'data'=>['MaTaiKhoan'=>$maTK,'LoaiTaiKhoan'=>$loaiTK,'MaNhatuyendung'=>$maNTD]], JSON_UNESCAPED_UNICODE);
    exit;
  }

} catch (Throwable $e) {
  $conn->rollback();
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Đăng ký thất bại','detail'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
  if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
