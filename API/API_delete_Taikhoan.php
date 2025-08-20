<?php
include "config.php";

$MaTK = $_POST['MaTK'] ?? null;

$sql = "DELETE FROM TaiKhoan WHERE MaTK='$MaTK'";

$response = [];
if ($conn->query($sql) === TRUE) {
    $response = ["status" => "success", "message" => "Xóa tài khoản thành công"];
} else {
    $response = ["status" => "error", "message" => $conn->error];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$conn->close();
?>
