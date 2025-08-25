<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "config.php";

// Lấy dữ liệu JSON từ body request
$input = json_decode(file_get_contents("php://input"), true);

$keyword = isset($input['keyword']) ? $input['keyword'] : "";
$TrangThai = isset($input['TrangThai']) ? $input['TrangThai'] : "";
$MaDanhMuc = isset($input['MaDanhMuc']) ? $input['MaDanhMuc'] : "";
$DiaDiemLamViec = isset($input['DiaDiemLamViec']) ? $input['DiaDiemLamViec'] : "";

// Câu SQL cơ bản
$sql = "SELECT * FROM TinTuyenDung WHERE 1=1";
$params = [];
$types = "";

// Nếu có keyword → tìm trong nhiều cột
if (!empty($keyword)) {
    $sql .= " AND (ChucDanh LIKE ? OR MoTaCongViec LIKE ? OR YeuCau LIKE ? OR DiaDiemLamViec LIKE ?)";
    $kw = "%$keyword%";
    $params[] = &$kw;
    $params[] = &$kw;
    $params[] = &$kw;
    $params[] = &$kw;
    $types .= "ssss";
}

// Nếu có TrangThai
if (!empty($TrangThai)) {
    $sql .= " AND TrangThai = ?";
    $params[] = &$TrangThai;
    $types .= "s";
}

// Nếu có MaDanhMuc
if (!empty($MaDanhMuc)) {
    $sql .= " AND MaDanhMuc = ?";
    $params[] = &$MaDanhMuc;
    $types .= "i";
}

// Nếu có DiaDiemLamViec
if (!empty($DiaDiemLamViec)) {
    $sql .= " AND DiaDiemLamViec LIKE ?";
    $dd = "%$DiaDiemLamViec%";
    $params[] = &$dd;
    $types .= "s";
}

// Chuẩn bị câu lệnh
$stmt = $conn->prepare($sql);

// Nếu có tham số thì bind
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $data
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$stmt->close();
$conn->close();
?>
