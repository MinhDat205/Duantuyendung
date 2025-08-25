<?php
// File: API_update_Taikhoan.php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Chỉ hỗ trợ POST"], JSON_UNESCAPED_UNICODE);
    exit;
}

$MaTK = $_POST['MaTK'] ?? null;
$TenDangNhap = isset($_POST['TenDangNhap']) ? trim($_POST['TenDangNhap']) : null;
$MatKhau = isset($_POST['MatKhau']) ? trim($_POST['MatKhau']) : null;
$LoaiTK = isset($_POST['LoaiTK']) ? trim($_POST['LoaiTK']) : null;

if (!$MaTK) {
    echo json_encode(["status" => "error", "message" => "Thiếu MaTK"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Kiểm tra MaTK tồn tại
$sql_check = "SELECT COUNT(*) AS count FROM TaiKhoan WHERE MaTK = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $MaTK);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$row_check = $result_check->fetch_assoc();
$stmt_check->close();

if ($row_check['count'] == 0) {
    echo json_encode(["status" => "error", "message" => "Không tìm thấy tài khoản với MaTK: $MaTK"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Kiểm tra LoaiTK hợp lệ
$validLoaiTK = ['UngVien', 'NhaTuyenDung'];
if ($LoaiTK !== null && $LoaiTK !== "" && !in_array($LoaiTK, $validLoaiTK)) {
    echo json_encode(["status" => "error", "message" => "LoaiTK không hợp lệ. Chỉ chấp nhận: UngVien, NhaTuyenDung"], JSON_UNESCAPED_UNICODE);
    exit;
}

$setParts = [];
$types = "";
$params = [];

// Map TenDangNhap -> Email
if ($TenDangNhap !== null && $TenDangNhap !== "") {
    // Kiểm tra Email không trùng (ngoại trừ bản ghi hiện tại)
    $sql_email_check = "SELECT COUNT(*) AS count FROM TaiKhoan WHERE Email = ? AND MaTK != ?";
    $stmt_email_check = $conn->prepare($sql_email_check);
    $stmt_email_check->bind_param("si", $TenDangNhap, $MaTK);
    $stmt_email_check->execute();
    $result_email_check = $stmt_email_check->get_result();
    $row_email_check = $result_email_check->fetch_assoc();
    $stmt_email_check->close();

    if ($row_email_check['count'] > 0) {
        echo json_encode(["status" => "error", "message" => "Email đã tồn tại"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $setParts[] = "Email = ?";
    $types .= "s";
    $params[] = $TenDangNhap;
}

// MatKhau (giữ nguyên dạng plain theo hệ thống hiện tại)
if ($MatKhau !== null && $MatKhau !== "") {
    $setParts[] = "MatKhau = ?";
    $types .= "s";
    $params[] = $MatKhau;
}

// Map LoaiTK -> LoaiTaiKhoan
if ($LoaiTK !== null && $LoaiTK !== "") {
    $setParts[] = "LoaiTaiKhoan = ?";
    $types .= "s";
    $params[] = $LoaiTK;
}

if (empty($setParts)) {
    echo json_encode(["status" => "error", "message" => "Không có trường nào để cập nhật"], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "UPDATE TaiKhoan SET " . implode(", ", $setParts) . " WHERE MaTK = ?";
$types .= "i";
$params[] = (int)$MaTK;

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Lỗi prepare: " . $conn->error);

    $stmt->bind_param($types, ...$params);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["status" => "success", "message" => "Cập nhật thành công"], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["status" => "error", "message" => "Không có thay đổi nào được thực hiện"], JSON_UNESCAPED_UNICODE);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Cập nhật thất bại"], JSON_UNESCAPED_UNICODE);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

$conn->close();