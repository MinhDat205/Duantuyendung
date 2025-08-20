<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "HeThongTuyenDung";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(
        ["status" => "error", "message" => "Kết nối thất bại: " . $conn->connect_error],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    ));
}

$conn->set_charset("utf8mb4");
?>
