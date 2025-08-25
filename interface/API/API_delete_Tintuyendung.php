<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(["status"=>"error","message"=>"Chỉ hỗ trợ POST"]); exit; }

$MaTin = $_POST['MaTin'] ?? null;
if(!$MaTin){ echo json_encode(["status"=>"error","message"=>"Thiếu MaTin"]); exit; }

try{
  $stmt = $conn->prepare("DELETE FROM TinTuyenDung WHERE MaTin=?");
  if(!$stmt) throw new Exception("Lỗi prepare: ".$conn->error);
  $id=(int)$MaTin;
  $stmt->bind_param("i", $id);
  if($stmt->execute()){
    if($stmt->affected_rows>0){
      echo json_encode(["status"=>"success","message"=>"Xóa thành công"], JSON_UNESCAPED_UNICODE);
    }else{
      echo json_encode(["status"=>"error","message"=>"Không tìm thấy bản ghi"], JSON_UNESCAPED_UNICODE);
    }
  } else {
    echo json_encode(["status"=>"error","message"=>"Xóa thất bại"], JSON_UNESCAPED_UNICODE);
  }
  $stmt->close();
}catch(Exception $e){
  echo json_encode(["status"=>"error","message"=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
$conn->close();
