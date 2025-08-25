<?php
// interface/API/API_insert_Nhatuyendung.php
require_once __DIR__ . '/config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
if ($_SERVER['REQUEST_METHOD']==='OPTIONS'){ http_response_code(200); exit; }
header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$MaTK = $data['MaTK'] ?? null;
$TenCongTy = $data['TenCongTy'] ?? null;
$SoDienThoai = $data['SoDienThoai'] ?? null;
$DiaChi = $data['DiaChi'] ?? null;
$ThongTinCongTy = $data['ThongTinCongTy'] ?? null;
$MaDanhMuc = $data['MaDanhMuc'] ?? null;

if (!$MaTK || !$TenCongTy){
  http_response_code(400);
  echo json_encode(["status"=>"error","message"=>"Thiếu MaTK/TenCongTy"], JSON_UNESCAPED_UNICODE);
  exit;
}

try{
  $ck = $conn->prepare("SELECT MaNTD FROM NhaTuyenDung WHERE MaTK=?");
  $ck->bind_param("i", $MaTK);
  $ck->execute();
  if($ck->get_result()->num_rows>0){
    http_response_code(409);
    echo json_encode(["status"=>"error","message"=>"MaTK đã tồn tại trong NhaTuyenDung"], JSON_UNESCAPED_UNICODE);
    exit;
  }

  $sql = "INSERT INTO NhaTuyenDung (MaTK, TenCongTy, SoDienThoai, DiaChi, ThongTinCongTy, MaDanhMuc)
          VALUES (?, ?, ?, ?, ?, ?)";
  $st = $conn->prepare($sql);
  $st->bind_param("issssi", $MaTK, $TenCongTy, $SoDienThoai, $DiaChi, $ThongTinCongTy, $MaDanhMuc);
  $st->execute();

  echo json_encode(["status"=>"success","message"=>"Thêm NTD thành công","insert_id"=>$conn->insert_id], JSON_UNESCAPED_UNICODE);

}catch(Exception $e){
  http_response_code(500);
  echo json_encode(["status"=>"error","message"=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
$conn->close();
