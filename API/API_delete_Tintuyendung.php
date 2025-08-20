<?php
include "config.php";

$MaTin = $_POST['MaTin'] ?? null;

$sql = "DELETE FROM TinTuyenDung WHERE MaTin='$MaTin'";

$response = [];
if ($conn->query($sql) === TRUE) {
    $response = ["status" => "success", "message" => "Xóa thành công"];
} else {
    $response = ["status" => "error", "message" => $conn->error];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$conn->close();
?>
