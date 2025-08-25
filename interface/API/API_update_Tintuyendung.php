<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(["status"=>"error","message"=>"Chỉ hỗ trợ POST"]); exit; }

$MaTin = $_POST['MaTin'] ?? null;
$ChucDanh = $_POST['ChucDanh'] ?? null;
$MoTaCongViec = $_POST['MoTaCongViec'] ?? null;
$YeuCau = $_POST['YeuCau'] ?? null;
$MucLuong = $_POST['MucLuong'] ?? null;
$DiaDiemLamViec = $_POST['DiaDiemLamViec'] ?? null;
$TrangThai = $_POST['TrangThai'] ?? null;
$MaDanhMuc = $_POST['MaDanhMuc'] ?? null;

if (!$MaTin) { echo json_encode(["status"=>"error","message"=>"Thiếu MaTin"]); exit; }

$fields=[]; $types=""; $params=[];

if($ChucDanh!==null){ $fields[]="ChucDanh=?"; $types.="s"; $params[]=$ChucDanh; }
if($MoTaCongViec!==null){ $fields[]="MoTaCongViec=?"; $types.="s"; $params[]=$MoTaCongViec; }
if($YeuCau!==null){ $fields[]="YeuCau=?"; $types.="s"; $params[]=$YeuCau; }
if($MucLuong!==null){ $fields[]="MucLuong=?"; $types.="s"; $params[]=$MucLuong; }
if($DiaDiemLamViec!==null){ $fields[]="DiaDiemLamViec=?"; $types.="s"; $params[]=$DiaDiemLamViec; }
if($TrangThai!==null){ $fields[]="TrangThai=?"; $types.="s"; $params[]=$TrangThai; }
if($MaDanhMuc!==null && $MaDanhMuc!==""){ $fields[]="MaDanhMuc=?"; $types.="i"; $params[]=(int)$MaDanhMuc; }

if(!$fields){ echo json_encode(["status"=>"error","message"=>"Không có trường để cập nhật"]); exit; }

$sql = "UPDATE TinTuyenDung SET ".implode(", ", $fields)." WHERE MaTin=?";
$types.="i"; $params[]=(int)$MaTin;

try{
  $stmt = $conn->prepare($sql);
  if(!$stmt) throw new Exception("Lỗi prepare: ".$conn->error);
  $stmt->bind_param($types, ...$params);
  if($stmt->execute()){
    echo json_encode(["status"=>"success","message"=>"Cập nhật thành công"], JSON_UNESCAPED_UNICODE);
  } else {
    echo json_encode(["status"=>"error","message"=>"Cập nhật thất bại"], JSON_UNESCAPED_UNICODE);
  }
  $stmt->close();
}catch(Exception $e){
  echo json_encode(["status"=>"error","message"=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
$conn->close();
