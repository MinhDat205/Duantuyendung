<?php
// interface/API/API_debug_whoami.php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

try {
  // 1) Kết nối PDO dùng hàm trong config.php (đã có port)
  $pdo = getConnection();

  // 2) Đọc JSON body (dùng helper trong config.php)
  $req   = read_json_body();
  $email = isset($req['email']) ? trim($req['email']) : '';

  if ($email === '') {
    echo json_encode(['ok'=>false,'error'=>'Thiếu email']); 
    exit;
  }

  // 3) Tìm tài khoản theo Email -> lấy MaTK
  $sql = "SELECT MaTK, Email, LoaiTaiKhoan 
          FROM TaiKhoan 
          WHERE Email = ? 
          LIMIT 1";
  $st  = $pdo->prepare($sql);
  $st->execute([$email]);
  $tk = $st->fetch();

  if (!$tk) {
    echo json_encode(['ok'=>false,'error'=>'Không tìm thấy tài khoản theo email']); 
    exit;
  }
  $maTK = (int)$tk['MaTK'];

  // 4) Tìm MaUngVien (thử nhiều khả năng tên cột trong bảng UngVien)
  $maUV = 0;

  // TH1: bảng UngVien có cột MaUV + MaTK
  $st = $pdo->prepare("SELECT MaUV AS MaUngVien FROM UngVien WHERE MaTK = ? LIMIT 1");
  $st->execute([$maTK]);
  if ($row = $st->fetch()) { $maUV = (int)$row['MaUngVien']; }

  // TH2: bảng UngVien có cột MaUngVien + MaTK
  if ($maUV === 0) {
    $st = $pdo->prepare("SELECT MaUngVien FROM UngVien WHERE MaTK = ? LIMIT 1");
    $st->execute([$maTK]);
    if ($row = $st->fetch()) { $maUV = (int)$row['MaUngVien']; }
  }

  // TH3: fallback theo Email nếu bảng UngVien có cột Email
  if ($maUV === 0) {
    $st = $pdo->prepare("SELECT MaUV AS MaUngVien FROM UngVien WHERE Email = ? LIMIT 1");
    $st->execute([$email]);
    if ($row = $st->fetch()) { $maUV = (int)$row['MaUngVien']; }
  }

  echo json_encode([
    'ok'   => true,
    'data' => [
      'Email'     => $email,
      'MaTK'      => $maTK,
      'MaUngVien' => $maUV,        // có thể = 0 nếu chưa mapping trong DB
      'role'      => 'UngVien'
    ]
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
