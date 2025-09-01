<?php
// /Duantuyendung/interface/API/API_login_Admin.php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Chỉ hỗ trợ POST"], JSON_UNESCAPED_UNICODE);
    exit;
}

$Email      = isset($_POST['Email']) ? trim($_POST['Email']) : '';
$MatKhau    = isset($_POST['MatKhau']) ? trim($_POST['MatKhau']) : '';
$redirect   = isset($_POST['redirect']) && $_POST['redirect'] !== ''
              ? trim($_POST['redirect'])
              : '/Duantuyendung/interface/build/pages/UI_AD/UI_Admin_Trangchu.html';
$returnJson = (!empty($_POST['return_json']) && $_POST['return_json'] == '1')
              || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

// 🔒 Chặn open redirect: chỉ cho phép đường dẫn nội bộ
if (strpos($redirect, '/Duantuyendung/') !== 0) {
    $redirect = '/Duantuyendung/interface/build/pages/UI_AD/UI_Admin_Trangchu.html';
}

if ($Email === '' || $MatKhau === '') {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Vui lòng nhập Email và Mật khẩu"], JSON_UNESCAPED_UNICODE);
    exit;
}

function verify_password_flexible(string $input, string $stored): bool {
    $info = password_get_info($stored);
    if (!empty($info['algo'])) return password_verify($input, $stored);
    return hash_equals($stored, $input);
}

try {
    $sql  = "SELECT MaQTV, Email, MatKhau, HoTen, TrangThai FROM QuanTriVien WHERE Email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Lỗi prepare: " . $conn->error);
    $stmt->bind_param("s", $Email);
    $stmt->execute();
    $rs = $stmt->get_result();

    if (!$rs || $rs->num_rows === 0) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Email hoặc mật khẩu không đúng"], JSON_UNESCAPED_UNICODE);
        $stmt->close(); $conn->close(); exit;
    }
    $row = $rs->fetch_assoc();
    $stmt->close();

    if ($row['TrangThai'] !== 'HoatDong') {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Tài khoản quản trị đang bị khóa"], JSON_UNESCAPED_UNICODE);
        $conn->close(); exit;
    }

    if (!verify_password_flexible($MatKhau, $row['MatKhau'])) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Email hoặc mật khẩu không đúng"], JSON_UNESCAPED_UNICODE);
        $conn->close(); exit;
    }

    session_regenerate_id(true);
    $_SESSION['admin'] = [
        'MaQTV'    => (int)$row['MaQTV'],
        'Email'    => $row['Email'],
        'HoTen'    => $row['HoTen'],
        'Role'     => 'QuanTriVien',
        'LoggedAt' => date('Y-m-d H:i:s')
    ];
    $_SESSION['admin_logged_in'] = true;

    $conn->close();

    if ($returnJson) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            "status"   => "success",
            "message"  => "Đăng nhập thành công",
            "data"     => [
                "MaQTV" => (int)$_SESSION['admin']['MaQTV'],
                "Email" => $_SESSION['admin']['Email'],
                "HoTen" => $_SESSION['admin']['HoTen'],
                "Role"  => $_SESSION['admin']['Role']
            ],
            "redirect" => $redirect
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    header("Location: " . $redirect);
    exit;

} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}
