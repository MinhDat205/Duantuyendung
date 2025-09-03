<?php
require 'config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Chỉ hỗ trợ POST"], JSON_UNESCAPED_UNICODE);
    exit;
}

$raw = file_get_contents('php://input');
$json = json_decode($raw, true) ?: [];
$MaNTD = $json['MaNTD'] ?? null;
$keyword = $json['keyword'] ?? null;
$TrangThai = $json['TrangThai'] ?? null;

if (!$MaNTD) {
    http_response_code(422);
    echo json_encode(["status" => "error", "message" => "Thiếu MaNTD"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Xây dựng câu truy vấn SQL
$sql = "SELECT ut.MaUngTuyen, ut.MaUngVien, ut.MaTin, ut.TrangThai, ut.NgayUngTuyen,
               uv.HoTen, ttd.ChucDanh, ttd.DiaDiemLamViec, ttd.MucLuong
        FROM UngTuyen ut
        JOIN UngVien uv ON ut.MaUngVien = uv.MaUngVien
        JOIN TinTuyenDung ttd ON ut.MaTin = ttd.MaTin
        WHERE ttd.MaNTD = ?";

$params = [$MaNTD];
$types = "i";

if ($TrangThai) {
    $sql .= " AND ut.TrangThai = ?";
    $params[] = $TrangThai;
    $types .= "s";
}

if ($keyword) {
    $sql .= " AND (uv.HoTen LIKE ? OR ttd.ChucDanh LIKE ?)";
    $keywordLike = "%$keyword%";
    $params[] = $keywordLike;
    $params[] = $keywordLike;
    $types .= "ss";
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode(["status" => "success", "data" => $data], JSON_UNESCAPED_UNICODE);
?>