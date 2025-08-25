<?php
include "config.php";

$MaUngVien = $_POST['MaUngVien'] ?? null;
$HoTen = $_POST['HoTen'] ?? null;
$SoDienThoai = $_POST['SoDienThoai'] ?? null;
$AnhCV = $_POST['AnhCV'] ?? null;
$KyNang = $_POST['KyNang'] ?? null;
$KinhNghiem = $_POST['KinhNghiem'] ?? null;
$MaDanhMuc = $_POST['MaDanhMuc'] ?? null;

$response = [];

if (!$MaUngVien) {
    $response = [
        "status" => "error", 
        "message" => "Thiếu tham số bắt buộc: MaUngVien"
    ];
} else {
    $fields = [];
    $types = "";
    $values = [];
    
    if (array_key_exists("HoTen", $_POST)) {
        $fields[] = "HoTen = ?";
        $types .= "s";
        $values[] = $HoTen;
    }
    if (array_key_exists("SoDienThoai", $_POST)) {
        $fields[] = "SoDienThoai = ?";
        $types .= "s";
        $values[] = $SoDienThoai;
    }
    if (array_key_exists("AnhCV", $_POST)) {
        $fields[] = "AnhCV = ?";
        $types .= "s";
        $values[] = $AnhCV;
    }
    if (array_key_exists("KyNang", $_POST)) {
        $fields[] = "KyNang = ?";
        $types .= "s";
        $values[] = $KyNang;
    }
    if (array_key_exists("KinhNghiem", $_POST)) {
        $fields[] = "KinhNghiem = ?";
        $types .= "s";
        $values[] = $KinhNghiem;
    }
    if (array_key_exists("MaDanhMuc", $_POST)) {
        $fields[] = "MaDanhMuc = ?";
        $types .= "i";
        $values[] = $MaDanhMuc;
    }

    if (empty($fields)) {
        $response = [
            "status" => "error", 
            "message" => "Không có trường nào để cập nhật"
        ];
    } else {
        $sql = "UPDATE UngVien SET " . implode(", ", $fields) . " WHERE MaUngVien = ?";
        $types .= "i";
        $values[] = $MaUngVien;
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
        
        if ($stmt->execute()) {
            $response = [
                "status" => "success", 
                "message" => "Cập nhật ứng viên thành công"
            ];
        } else {
            $response = [
                "status" => "error", 
                "message" => "Lỗi: " . $stmt->error
            ];
        }
        $stmt->close();
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$conn->close();
?>
