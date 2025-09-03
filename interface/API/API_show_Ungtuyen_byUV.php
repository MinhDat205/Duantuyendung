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

$MaUngVien = $_GET['MaUngVien'] ?? null;

if (!$MaUngVien) {
    echo json_encode(["status"=>"error","message"=>"Thiếu MaUngVien"]);
    exit;
}

$sql = "SELECT ut.*, ttd.ChucDanh, ttd.DiaDiemLamViec, ttd.MucLuong 
        FROM UngTuyen ut
        JOIN TinTuyenDung ttd ON ut.MaTin = ttd.MaTin
        WHERE ut.MaUngVien=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $MaUngVien);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(["status"=>"success","data"=>$data]);
?>