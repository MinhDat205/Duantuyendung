<?php
header("Content-Type: application/json");
require "config.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["MaNTD"]) || !isset($data["TenCongTy"]) || !isset($data["DiaChi"])) {
    echo json_encode(["status" => "error", "message" => "Thiếu dữ liệu"]);
    exit;
}

$MaNTD = (int)$data["MaNTD"];
$TenCongTy = $conn->real_escape_string($data["TenCongTy"]);
$DiaChi = $conn->real_escape_string($data["DiaChi"]);

$sql = "UPDATE NhaTuyenDung 
        SET TenCongTy='$TenCongTy', DiaChi='$DiaChi' 
        WHERE MaNTD=$MaNTD";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Cập nhật thành công"]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}
?>
