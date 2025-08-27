<?php
// interface/API/API_update_Ungvien.php
require_once __DIR__ . '/config.php';

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Chuẩn hoá input - nhận cả MaUngVien lẫn MaTK
$raw = file_get_contents('php://input');
$js  = json_decode($raw, true);
$P   = array_merge($_POST, is_array($js)?$js:[]);

$maUV = $P['MaUngVien'] ?? $P['maUV'] ?? null;
$maTK = $P['MaTK'] ?? $P['maTK'] ?? null;

if (!$maUV && !$maTK) { 
    echo json_encode(["ok"=>false,"error"=>"Thiếu trường: MaUngVien hoặc MaTK"]); 
    exit; 
}

// Nếu thiếu MaUngVien nhưng có MaTK → suy ra MaUngVien
if (!$maUV && $maTK) {
    $stmt = $conn->prepare("SELECT MaUngVien FROM UngVien WHERE MaTK=?");
    $stmt->bind_param('i', $maTK);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $maUV = $row['MaUngVien'];
    } else {
        echo json_encode(["ok"=>false,"error"=>"Không tìm thấy UV theo MaTK={$maTK}"]);
        exit;
    }
    $stmt->close();
}

// Lấy các trường cần cập nhật
$HoTen = $P['HoTen'] ?? null;
$SoDienThoai = $P['SoDienThoai'] ?? null;
$AnhCV = $P['AnhCV'] ?? null;
$KyNang = $P['KyNang'] ?? null;
$KinhNghiem = $P['KinhNghiem'] ?? null;
$MaDanhMuc = $P['MaDanhMuc'] ?? null;

try {
    $response = [];

    if (!$maUV) {
    $response = [
        "status" => "error", 
        "message" => "Thiếu tham số bắt buộc: MaUngVien"
    ];
} else {
    $fields = [];
    $types = "";
    $values = [];
    
    if ($HoTen !== null) {
        $fields[] = "HoTen = ?";
        $types .= "s";
        $values[] = $HoTen;
    }
    if ($SoDienThoai !== null) {
        $fields[] = "SoDienThoai = ?";
        $types .= "s";
        $values[] = $SoDienThoai;
    }
    if ($AnhCV !== null) {
        $fields[] = "AnhCV = ?";
        $types .= "s";
        $values[] = $AnhCV;
    }
    if ($KyNang !== null) {
        $fields[] = "KyNang = ?";
        $types .= "s";
        $values[] = $KyNang;
    }
    if ($KinhNghiem !== null) {
        $fields[] = "KinhNghiem = ?";
        $types .= "s";
        $values[] = $KinhNghiem;
    }
    if ($MaDanhMuc !== null) {
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
        $values[] = $maUV;
        
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

echo json_encode(['ok' => $response['status'] === 'success', 'message' => $response['message']], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Lỗi server: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
    if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
?>
