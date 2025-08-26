<?php
require_once 'config.php';
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

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

try {
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

    // Query với tên cột đúng schema
    $sql = "SELECT 
              uv.MaUngVien,
              uv.HoTen,
              uv.SoDienThoai,      -- ĐÚNG TÊN CỘT
              uv.KyNang,
              uv.KinhNghiem,
              uv.AnhCV,            -- ĐÚNG TÊN CỘT
              uv.MaDanhMuc,
              tk.Email,
              tk.LoaiTaiKhoan
            FROM UngVien uv
            JOIN TaiKhoan tk ON tk.MaTK = uv.MaTK
            WHERE uv.MaUngVien = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Prepare failed: ".$conn->error);
    $stmt->bind_param('i', $maUV);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        echo json_encode(["ok"=>true,"data"=>$row]);
    } else {
        echo json_encode(["ok"=>false,"error"=>"Không tìm thấy hồ sơ UV theo MaUngVien={$maUV}"]);
    }
} catch (Throwable $e) {
    echo json_encode(["ok"=>false,"error"=>"Lỗi server: ".$e->getMessage()]);
}
?>
