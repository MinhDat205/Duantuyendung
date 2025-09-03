<?php
require 'config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$raw = file_get_contents('php://input');
$json = json_decode($raw, true) ?: [];
$MaNguoiNhan = $json['MaNguoiNhan'] ?? null;
$LoaiThongBao = $json['LoaiThongBao'] ?? null;
$NoiDung = $json['NoiDung'] ?? null;

if (!$MaNguoiNhan || !$LoaiThongBao || !$NoiDung) {
    http_response_code(422);
    echo json_encode(["status" => "error", "message" => "Thiếu tham số bắt buộc"], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO ThongBao (MaNguoiNhan, LoaiThongBao, NoiDung, NgayTao)
     VALUES (?, ?, ?, NOW())"
);
$stmt->bind_param("iss", $MaNguoiNhan, $LoaiThongBao, $NoiDung);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(["status" => "success", "message" => "Thông báo đã được tạo"], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Lỗi tạo thông báo"], JSON_UNESCAPED_UNICODE);
}

$stmt->close();
$conn->close();
?>