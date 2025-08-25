<?php
// File: API_duyet_Taikhoan.php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Chỉ hỗ trợ POST"], JSON_UNESCAPED_UNICODE);
    exit;
}

$MaTK = $_POST['MaTK'] ?? null;
$TrangThai = $_POST['TrangThai'] ?? null; // HoatDong | BiKhoa

if (!$MaTK || !$TrangThai) {
    echo json_encode(["status" => "error", "message" => "Thiếu tham số"], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $sql = "UPDATE TaiKhoan SET TrangThai = ? WHERE MaTK = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Lỗi prepare: " . $conn->error);

    $id = (int)$MaTK;
    $stmt->bind_param("si", $TrangThai, $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Cập nhật thành công"], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["status" => "error", "message" => "Cập nhật thất bại"], JSON_UNESCAPED_UNICODE);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
$conn->close();
