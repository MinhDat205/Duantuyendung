<?php
include "config.php";

$MaTK = $_POST['MaTK'] ?? null;
$HoTen = $_POST['HoTen'] ?? null;
$SoDienThoai = $_POST['SoDienThoai'] ?? null;
$AnhCV = $_POST['AnhCV'] ?? null;
$KyNang = $_POST['KyNang'] ?? null;
$KinhNghiem = $_POST['KinhNghiem'] ?? null;
$MaDanhMuc = $_POST['MaDanhMuc'] ?? null;

$response = [];

if ($MaTK && $HoTen) {
    // Kiểm tra MaTK đã tồn tại trong UngVien chưa
    $checkSql = "SELECT MaUngVien FROM UngVien WHERE MaTK = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $MaTK);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $existing = $checkResult->fetch_assoc();
        $response = [
            "status" => "error", 
            "message" => "MaTK đã tồn tại trong UngVien. Vui lòng dùng MaTK khác hoặc dùng API update.",
            "existing_MaUngVien" => intval($existing["MaUngVien"])
        ];
    } else {
        $sql = "INSERT INTO UngVien (MaTK, HoTen, SoDienThoai, AnhCV, KyNang, KinhNghiem, MaDanhMuc) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssssi", $MaTK, $HoTen, $SoDienThoai, $AnhCV, $KyNang, $KinhNghiem, $MaDanhMuc);
        
        if ($stmt->execute()) {
            $response = [
                "status" => "success", 
                "message" => "Thêm ứng viên thành công",
                "insert_id" => $conn->insert_id
            ];
        } else {
            $response = [
                "status" => "error", 
                "message" => "Lỗi: " . $stmt->error
            ];
        }
        $stmt->close();
    }
    $checkStmt->close();
} else {
    $response = [
        "status" => "error", 
        "message" => "Thiếu tham số bắt buộc: MaTK, HoTen"
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$conn->close();
?>
