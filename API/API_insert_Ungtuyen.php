<?php
require 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $MaTin = $_POST['MaTin'] ?? null;
    $MaUngVien = $_POST['MaUngVien'] ?? null;

    if (!$MaTin || !$MaUngVien) {
        echo json_encode(["status"=>"error","message"=>"Thiếu tham số"]);
        exit;
    }

    $sql = "INSERT INTO UngTuyen (MaTin, MaUngVien) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $MaTin, $MaUngVien);

    if ($stmt->execute()) {
        echo json_encode(["status"=>"success","message"=>"Ứng tuyển thành công"]);
    } else {
        echo json_encode(["status"=>"error","message"=>"Ứng tuyển thất bại hoặc đã ứng tuyển"]);
    }
} else {
    echo json_encode(["status"=>"error","message"=>"Chỉ hỗ trợ POST"]);
}
?>
