<?php
require 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $MaUngTuyen = $_POST['MaUngTuyen'] ?? null;

    if (!$MaUngTuyen) {
        echo json_encode(["status"=>"error","message"=>"Thiếu MaUngTuyen"]);
        exit;
    }

    $sql = "DELETE FROM UngTuyen WHERE MaUngTuyen=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $MaUngTuyen);

    if ($stmt->execute()) {
        echo json_encode(["status"=>"success","message"=>"Xóa thành công"]);
    } else {
        echo json_encode(["status"=>"error","message"=>"Xóa thất bại"]);
    }
} else {
    echo json_encode(["status"=>"error","message"=>"Chỉ hỗ trợ POST"]);
}
?>
