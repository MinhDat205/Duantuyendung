<?php
header("Content-Type: application/json");
require "config.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["MaTK"]) || !isset($data["TenCongTy"]) || !isset($data["DiaChi"])) {
    echo json_encode(["status" => "error", "message" => "Thiếu dữ liệu"]);
    exit;
}

$MaTK = $data["MaTK"];
$TenCongTy = $conn->real_escape_string($data["TenCongTy"]);
$DiaChi = $conn->real_escape_string($data["DiaChi"]);

$sql = "INSERT INTO NhaTuyenDung (MaTK, TenCongTy, DiaChi) 
        VALUES ('$MaTK', '$TenCongTy', '$DiaChi')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Thêm thành công"]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}
?>
