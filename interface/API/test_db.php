<?php
// test_db.php - kiểm tra kết nối database MySQL

$db_host = 'localhost';
$db_name = 'HeThongTuyenDung'; // Đặt đúng tên DB bạn đã tạo
$db_user = 'root';
$db_pass = '';
$db_port = 3306;

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

if ($conn->connect_error) {
    die("❌ Kết nối thất bại: " . $conn->connect_error);
} else {
    echo "✅ Kết nối thành công tới database: " . $db_name . "<br>";

    // Thử query nhỏ để chắc chắn
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        echo "📂 Danh sách bảng trong DB:<br>";
        while ($row = $result->fetch_array()) {
            echo "- " . $row[0] . "<br>";
        }
    } else {
        echo "⚠️ Không thể lấy danh sách bảng: " . $conn->error;
    }
}

$conn->close();
?>
