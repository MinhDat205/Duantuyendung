<?php
require 'config.php';
header('Content-Type: application/json');

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
