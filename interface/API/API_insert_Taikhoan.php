<?php
// interface/API/API_insert_Taikhoan.php
require_once __DIR__ . '/config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
if ($_SERVER['REQUEST_METHOD']==='OPTIONS'){ http_response_code(200); exit; }

header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD']!=='POST'){
  http_response_code(405);
  echo json_encode(["status"=>"error","message"=>"Chỉ hỗ trợ POST"], JSON_UNESCAPED_UNICODE);
  exit;
}

$Email = trim($_POST['Email'] ?? '');
$MatKhau = trim($_POST['MatKhau'] ?? '');
$Loai = $_POST['LoaiTaiKhoan'] ?? 'UngVien';

if ($Email==='' || $MatKhau===''){
  http_response_code(400);
  echo json_encode(["status"=>"error","message"=>"Thiếu Email/Mật khẩu"], JSON_UNESCAPED_UNICODE);
  exit;
}
if (!in_array($Loai, ['UngVien','NhaTuyenDung'], true)){
  http_response_code(400);
  echo json_encode(["status"=>"error","message"=>"Loại tài khoản không hợp lệ"], JSON_UNESCAPED_UNICODE);
  exit;
}

try{
  // Kiểm tra trùng email
  $ck=$conn->prepare("SELECT 1 FROM TaiKhoan WHERE Email=? LIMIT 1");
  $ck->bind_param("s",$Email);
  $ck->execute();
  if($ck->get_result()->num_rows>0){
    http_response_code(409);
    echo json_encode(["status"=>"error","message"=>"Email đã tồn tại"], JSON_UNESCAPED_UNICODE);
    exit;
  }

  $hash = password_hash($MatKhau, PASSWORD_DEFAULT);
  $st=$conn->prepare("INSERT INTO TaiKhoan (Email, MatKhau, LoaiTaiKhoan) VALUES (?, ?, ?)");
  $st->bind_param("sss",$Email,$hash,$Loai);
  if(!$st->execute()){
    throw new Exception($st->error);
  }

  echo json_encode([
    "status"=>"success",
    "message"=>"Tạo tài khoản thành công",
    "insert_id"=>$conn->insert_id
  ], JSON_UNESCAPED_UNICODE);

}catch(Exception $e){
  http_response_code(500);
  echo json_encode(["status"=>"error","message"=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
$conn->close();
