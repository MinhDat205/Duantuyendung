<?php
// File: API_delete_Taikhoan.php
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
if (!$MaTK) {
    echo json_encode(["status" => "error", "message" => "Thiếu MaTK"], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $sql = "DELETE FROM TaiKhoan WHERE MaTK = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Lỗi prepare: " . $conn->error);

    $id = (int)$MaTK;
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["status" => "success", "message" => "Xóa tài khoản thành công"], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["status" => "error", "message" => "Không tìm thấy tài khoản cần xóa"], JSON_UNESCAPED_UNICODE);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Xóa thất bại"], JSON_UNESCAPED_UNICODE);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

$conn->close();
