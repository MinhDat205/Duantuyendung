<?php
// interface/API/config.php

// Headers CORS và JSON
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Xử lý preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Cấu hình database
$db_host = 'localhost';
$db_name = 'hethongtuyendung';
$db_user = 'root';
$db_pass = '';
$port = 3307;

// Kết nối database sử dụng mysqli
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $port);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Lỗi kết nối database'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Đặt charset UTF-8
$conn->set_charset("utf8mb4");

// Hàm đọc JSON body từ request
function read_json_body() {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['ok'=>false,'error'=>'JSON không hợp lệ'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    return $data ?: [];
}

// Hàm tạo kết nối PDO (cho các API mới)
function getConnection() {
    global $db_host, $db_name, $db_user, $db_pass, $port;
    
    try {
        $pdo = new PDO(
            "mysql:host=$db_host;port=$port;dbname=$db_name;charset=utf8mb4",
            $db_user,
            $db_pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Lỗi kết nối database: ' . $e->getMessage());
    }
}
?>