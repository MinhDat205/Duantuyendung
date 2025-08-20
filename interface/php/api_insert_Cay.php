<?php
// Không để bất kỳ ký tự trắng hoặc dòng trống nào trước <?php
header('Content-Type: application/json; charset=utf-8');
include 'config.php';

// Lấy dữ liệu từ POST
$macay = $_POST['MaCay'] ?? '';
$giongcay = $_POST['GiongCay'] ?? '';
$ngaytrong = $_POST['NgayTrong'] ?? '';
$tinhtrang = $_POST['TinhTrang'] ?? '';

// Kiểm tra dữ liệu bắt buộc
if (empty($macay) || empty($giongcay) || empty($ngaytrong) || empty($tinhtrang)) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng điền đầy đủ thông tin'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Sử dụng prepared statement để chèn dữ liệu
$stmt = $conn->prepare("INSERT INTO Cay (MaCay, GiongCay, NgayTrong, TinhTrang) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $macay, $giongcay, $ngaytrong, $tinhtrang);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Thêm cây thành công'
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi: ' . $stmt->error
    ], JSON_UNESCAPED_UNICODE);
}

$stmt->close();
mysqli_close($conn);
?>