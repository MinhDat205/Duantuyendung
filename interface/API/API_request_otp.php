<?php
// interface/API/API_request_otp.php
require_once __DIR__ . '/config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
if ($_SERVER['REQUEST_METHOD']==='OPTIONS'){ http_response_code(200); exit; }
header('Content-Type: application/json; charset=utf-8');

$Email = trim($_POST['Email'] ?? '');
if ($Email===''){
  http_response_code(400);
  echo json_encode(["status"=>"error","message"=>"Thiếu Email"], JSON_UNESCAPED_UNICODE);
  exit;
}

// Kiểm tra tồn tại tài khoản
$ck = $conn->prepare("SELECT 1 FROM TaiKhoan WHERE Email=? LIMIT 1");
$ck->bind_param("s",$Email);
$ck->execute();
if($ck->get_result()->num_rows===0){
  http_response_code(404);
  echo json_encode(["status"=>"error","message"=>"Email không tồn tại"], JSON_UNESCAPED_UNICODE);
  exit;
}

// Tạo OTP
$otp = str_pad((string)random_int(0,999999), 6, "0", STR_PAD_LEFT);
$expire = (new DateTime('+10 minutes'))->format('Y-m-d H:i:s');

// Upsert PasswordReset
$st = $conn->prepare("INSERT INTO PasswordReset (Email, OTP, ExpireAt) VALUES (?, ?, ?)
                      ON DUPLICATE KEY UPDATE OTP=VALUES(OTP), ExpireAt=VALUES(ExpireAt)");
$st->bind_param("sss", $Email, $otp, $expire);
$st->execute();

// Gửi email (DEV: demo). Cấu hình mail() nếu server hỗ trợ.
// @mail($Email, "OTP khôi phục mật khẩu", "Mã OTP của bạn: $otp (hạn 10 phút)");
file_put_contents(__DIR__."/otp_dev.log", "[".date('c')."] $Email => $otp\n", FILE_APPEND);

echo json_encode(["status"=>"success","message"=>"Đã gửi mã OTP"], JSON_UNESCAPED_UNICODE);
$conn->close();
