<?php
// interface/API/API_reset_password.php
require_once __DIR__ . '/config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
if ($_SERVER['REQUEST_METHOD']==='OPTIONS'){ http_response_code(200); exit; }
header('Content-Type: application/json; charset=utf-8');

$Email = trim($_POST['Email'] ?? '');
$OTP = trim($_POST['OTP'] ?? '');
$NewPassword = trim($_POST['NewPassword'] ?? '');

if ($Email==='' || $OTP==='' || $NewPassword===''){
  http_response_code(400);
  echo json_encode(["status"=>"error","message"=>"Thiếu tham số"], JSON_UNESCAPED_UNICODE);
  exit;
}

// Kiểm tra OTP còn hạn
$now = (new DateTime())->format('Y-m-d H:i:s');
$st = $conn->prepare("SELECT 1 FROM PasswordReset WHERE Email=? AND OTP=? AND ExpireAt>=? LIMIT 1");
$st->bind_param("sss", $Email, $OTP, $now);
$st->execute();
if($st->get_result()->num_rows===0){
  http_response_code(400);
  echo json_encode(["status"=>"error","message"=>"OTP không đúng hoặc đã hết hạn"], JSON_UNESCAPED_UNICODE);
  exit;
}

// Cập nhật mật khẩu
$hash = password_hash($NewPassword, PASSWORD_DEFAULT);
$upd = $conn->prepare("UPDATE TaiKhoan SET MatKhau=? WHERE Email=?");
$upd->bind_param("ss", $hash, $Email);
$upd->execute();

// Xoá OTP đã dùng
$del = $conn->prepare("DELETE FROM PasswordReset WHERE Email=?");
$del->bind_param("s", $Email);
$del->execute();

echo json_encode(["status"=>"success","message"=>"Đổi mật khẩu thành công"], JSON_UNESCAPED_UNICODE);
$conn->close();
