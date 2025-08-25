<?php
// interface/API/API_insert_Ungvien.php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

$MaTK        = $_POST['MaTK']        ?? null;
$HoTen       = $_POST['HoTen']       ?? '';
$SoDienThoai = $_POST['SoDienThoai'] ?? '';
$AnhCV       = $_POST['AnhCV']       ?? '';
$KyNang      = $_POST['KyNang']      ?? '';
$KinhNghiem  = $_POST['KinhNghiem']  ?? '';
$MaDanhMuc   = $_POST['MaDanhMuc']   ?? null;

if (!$MaTK || $HoTen===''){
  http_response_code(400);
  echo json_encode(["status"=>"error","message"=>"Thiếu MaTK/HoTen"], JSON_UNESCAPED_UNICODE);
  exit;
}

try{
  // Check unique MaTK
  $c = $conn->prepare("SELECT MaUngVien FROM UngVien WHERE MaTK=? LIMIT 1");
  $c->bind_param("i",$MaTK);
  $c->execute();
  $r = $c->get_result();
  if ($r && $r->num_rows>0){
    $e = $r->fetch_assoc();
    echo json_encode(["status"=>"error","message"=>"MaTK đã tồn tại trong UngVien","existing_MaUngVien"=>(int)$e['MaUngVien']], JSON_UNESCAPED_UNICODE);
    $c->close(); $conn->close(); exit;
  }
  $c->close();

  $sql = "INSERT INTO UngVien (MaTK, HoTen, SoDienThoai, AnhCV, KyNang, KinhNghiem, MaDanhMuc) VALUES (?,?,?,?,?,?,?)";
  $stmt = $conn->prepare($sql);
  if (!$stmt) throw new Exception("Lỗi prepare: ".$conn->error);
  $stmt->bind_param("isssssi", $MaTK, $HoTen, $SoDienThoai, $AnhCV, $KyNang, $KinhNghiem, $MaDanhMuc);
  $ok = $stmt->execute();
  if (!$ok) throw new Exception("Lỗi execute: ".$stmt->error);

  echo json_encode(["status"=>"success","message"=>"Thêm ứng viên thành công","insert_id"=>$conn->insert_id], JSON_UNESCAPED_UNICODE);
  $stmt->close();
  $conn->close();
} catch (Exception $e){
  http_response_code(500);
  echo json_encode(["status"=>"error","message"=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
