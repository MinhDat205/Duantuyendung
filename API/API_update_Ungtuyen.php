<?php
require 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $MaUngTuyen = $_POST['MaUngTuyen'] ?? null;
    $TrangThai = $_POST['TrangThai'] ?? null;

    if (!$MaUngTuyen || !$TrangThai) {
        echo json_encode(["status"=>"error","message"=>"Thiếu tham số"]);
        exit;
    }

    $sql = "UPDATE UngTuyen SET TrangThai=? WHERE MaUngTuyen=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $TrangThai, $MaUngTuyen);

    if ($stmt->execute()) {
        echo json_encode(["status"=>"success","message"=>"Cập nhật thành công"]);
    } else {
        echo json_encode(["status"=>"error","message"=>"Cập nhật thất bại"]);
    }
} else {
    echo json_encode(["status"=>"error","message"=>"Chỉ hỗ trợ POST"]);
}
?>
