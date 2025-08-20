<?php
header("Content-Type: application/json");
require "config.php";

$sql = "SELECT NTD.*, TK.Email 
        FROM NhaTuyenDung NTD 
        JOIN TaiKhoan TK ON NTD.MaTK = TK.MaTK";
$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(["status" => "success", "data" => $data]);
?>