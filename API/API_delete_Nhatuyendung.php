<?php
header("Content-Type: application/json");
require "config.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["MaNTD"])) {
    echo json_encode(["status" => "error", "message" => "Thiếu mã NTD"]);
    exit;
}

$MaNTD = (int)$data["MaNTD"];

$sql = "DELETE FROM NhaTuyenDung WHERE MaNTD=$MaNTD";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Xóa thành công"]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}
?>
