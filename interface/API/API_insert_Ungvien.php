<?php
// interface/API/API_insert_Ungvien.php
require_once __DIR__ . '/config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
if ($_SERVER['REQUEST_METHOD']==='OPTIONS'){ http_response_code(200); exit; }
header('Content-Type: application/json; charset=utf-8');

$MaTK = $_POST['MaTK'] ?? null;
$HoTen = $_POST['HoTen'] ?? null;
$SoDienThoai = $_POST['SoDienThoai'] ?? null;
$AnhCV = $_POST['AnhCV'] ?? null;
$KyNang = $_POST['KyNang'] ?? null;
$KinhNghiem = $_POST['KinhNghiem'] ?? null;
$MaDanhMuc = $_POST['MaDanhMuc'] ?? null;

if (!$MaTK || !$HoTen){
  http_response_code(400);
  echo json_encode(["status"=>"error","message"=>"Thiếu MaTK/HoTen"], JSON_UNESCAPED_UNICODE);
  exit;
}

try{
  // Kiểm tra tồn tại
  $ck = $conn->prepare("SELECT MaUngVien FROM UngVien WHERE MaTK=?");
  $ck->bind_param("i", $MaTK);
  $ck->execute();
  if($ck->get_result()->num_rows>0){
    http_response_code(409);
    echo json_encode(["status"=>"error","message"=>"MaTK đã tồn tại trong UngVien"], JSON_UNESCAPED_UNICODE);
    exit;
  }

  $sql = "INSERT INTO UngVien (MaTK, HoTen, SoDienThoai, AnhCV, KyNang, KinhNghiem, MaDanhMuc)
          VALUES (?, ?, ?, ?, ?, ?, ?)";
  $st = $conn->prepare($sql);
  $st->bind_param("isssssi", $MaTK, $HoTen, $SoDienThoai, $AnhCV, $KyNang, $KinhNghiem, $MaDanhMuc);
  $st->execute();

  echo json_encode(["status"=>"success","message"=>"Thêm ứng viên thành công","insert_id"=>$conn->insert_id], JSON_UNESCAPED_UNICODE);

}catch(Exception $e){
  http_response_code(500);
  echo json_encode(["status"=>"error","message"=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
$conn->close();
