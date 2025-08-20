<?php
require 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $MaTK = $_POST['MaTK'] ?? null;
    $TrangThai = $_POST['TrangThai'] ?? null; // HoatDong hoặc BiKhoa

    if (!$MaTK || !$TrangThai) {
        echo json_encode(["status" => "error", "message" => "Thiếu tham số"]);
        exit;
    }

    $sql = "UPDATE TaiKhoan SET TrangThai=? WHERE MaTK=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $TrangThai, $MaTK);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Cập nhật thành công"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Cập nhật thất bại"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Chỉ hỗ trợ POST"]);
}
?>
