<?php
// File: interface/API/API_update_Taikhoan.php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
// Nếu bạn đang gọi từ domain khác (Live Server...), giữ CORS đơn giản:
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

// ===== Input =====
$MaTK        = isset($_POST['MaTK']) ? (int)$_POST['MaTK'] : 0;
$TenDangNhap = isset($_POST['TenDangNhap']) ? trim((string)$_POST['TenDangNhap']) : null; // map -> Email
$MatKhauRaw  = isset($_POST['MatKhau']) ? trim((string)$_POST['MatKhau']) : null;
$LoaiTK      = isset($_POST['LoaiTK']) ? trim((string)$_POST['LoaiTK']) : null;           // map -> LoaiTaiKhoan

if ($MaTK <= 0) {
    echo json_encode(["status" => "error", "message" => "Thiếu hoặc sai MaTK"], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Kiểm tra MaTK tồn tại
    $sql_check = "SELECT Email FROM TaiKhoan WHERE MaTK = ?";
    $stmt_check = $conn->prepare($sql_check);
    if (!$stmt_check) throw new Exception("Lỗi prepare: " . $conn->error);
    $stmt_check->bind_param("i", $MaTK);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    $row_user  = $res_check->fetch_assoc();
    $stmt_check->close();

    if (!$row_user) {
        echo json_encode(["status" => "error", "message" => "Không tìm thấy tài khoản với MaTK: $MaTK"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Validate LoaiTK (nếu có)
    if ($LoaiTK !== null && $LoaiTK !== "") {
        $validLoaiTK = ['UngVien', 'NhaTuyenDung'];
        if (!in_array($LoaiTK, $validLoaiTK, true)) {
            echo json_encode(["status" => "error", "message" => "LoaiTK không hợp lệ. Chỉ chấp nhận: UngVien, NhaTuyenDung"], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    $setParts = [];
    $types = "";
    $params = [];

    // Map TenDangNhap -> Email (nếu muốn đổi email)
    if ($TenDangNhap !== null && $TenDangNhap !== "") {
        // Kiểm tra email không trùng (ngoại trừ chính mình)
        $sql_email_check = "SELECT COUNT(*) AS cnt FROM TaiKhoan WHERE Email = ? AND MaTK != ?";
        $stmt_email = $conn->prepare($sql_email_check);
        if (!$stmt_email) throw new Exception("Lỗi prepare: " . $conn->error);
        $stmt_email->bind_param("si", $TenDangNhap, $MaTK);
        $stmt_email->execute();
        $res_email = $stmt_email->get_result();
        $row_email = $res_email->fetch_assoc();
        $stmt_email->close();

        if (!empty($row_email['cnt'])) {
            echo json_encode(["status" => "error", "message" => "Email đã tồn tại"], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $setParts[] = "Email = ?";
        $types     .= "s";
        $params[]   = $TenDangNhap;
    }

    // Hash mật khẩu nếu có cập nhật
    if ($MatKhauRaw !== null && $MatKhauRaw !== "") {
        // Bạn có thể thêm policy tối thiểu 8 ký tự ở đây nếu muốn
        $hashed = password_hash($MatKhauRaw, PASSWORD_BCRYPT);
        if ($hashed === false) {
            echo json_encode(["status" => "error", "message" => "Không hash được mật khẩu"], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $setParts[] = "MatKhau = ?";
        $types     .= "s";
        $params[]   = $hashed;
    }

    // Map LoaiTK -> LoaiTaiKhoan (nếu có)
    if ($LoaiTK !== null && $LoaiTK !== "") {
        $setParts[] = "LoaiTaiKhoan = ?";
        $types     .= "s";
        $params[]   = $LoaiTK;
    }

    if (empty($setParts)) {
        echo json_encode(["status" => "error", "message" => "Không có trường nào để cập nhật"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $sql_update = "UPDATE TaiKhoan SET " . implode(", ", $setParts) . " WHERE MaTK = ?";
    $types     .= "i";
    $params[]   = $MaTK;

    $stmt = $conn->prepare($sql_update);
    if (!$stmt) throw new Exception("Lỗi prepare: " . $conn->error);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["status" => "success", "message" => "Cập nhật thành công"], JSON_UNESCAPED_UNICODE);
        } else {
            // Không có thay đổi dữ liệu (giá trị gửi lên giống giá trị hiện tại)
            echo json_encode(["status" => "error", "message" => "Không có thay đổi nào được thực hiện"], JSON_UNESCAPED_UNICODE);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Cập nhật thất bại"], JSON_UNESCAPED_UNICODE);
    }
    $stmt->close();

} catch (Throwable $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
    if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
