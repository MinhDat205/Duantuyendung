<?php
include "config.php";

$MaUngVien = $_POST['MaUngVien'] ?? null;

$response = [];

if ($MaUngVien) {
    $sql = "DELETE FROM UngVien WHERE MaUngVien = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $MaUngVien);
    
    if ($stmt->execute()) {
        $response = [
            "status" => "success", 
            "message" => "Xóa ứng viên thành công"
        ];
    } else {
        $response = [
            "status" => "error", 
            "message" => "Lỗi: " . $stmt->error
        ];
    }
    $stmt->close();
} else {
    $response = [
        "status" => "error", 
        "message" => "Thiếu tham số MaUngVien"
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$conn->close();
?>
