<?php
require 'config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$sql = "SELECT ut.*, uv.HoTen, ttd.ChucDanh 
        FROM UngTuyen ut
        JOIN UngVien uv ON ut.MaUngVien = uv.MaUngVien
        JOIN TinTuyenDung ttd ON ut.MaTin = ttd.MaTin";

$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(["status"=>"success","data"=>$data]);
?>
