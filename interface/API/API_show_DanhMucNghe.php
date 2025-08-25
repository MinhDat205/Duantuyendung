<?php
// interface/API/API_show_DanhMucNghe.php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

$rs = $conn->query("SELECT MaDanhMuc, TenDanhMuc FROM DanhMucNghe ORDER BY TenDanhMuc ASC");
$data = [];
if ($rs && $rs->num_rows>0){
  while($row = $rs->fetch_assoc()) $data[] = $row;
}
echo json_encode(["status"=>"success","data"=>$data], JSON_UNESCAPED_UNICODE);
$conn->close();