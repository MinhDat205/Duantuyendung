<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Chỉ hỗ trợ POST"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true) ?: [];
    $MaUngTuyen = $json['MaUngTuyen'] ?? $_POST['MaUngTuyen'] ?? null;
    $TrangThai = $json['TrangThai'] ?? $_POST['TrangThai'] ?? null;

    if (!$MaUngTuyen || !$TrangThai) {
        http_response_code(422);
        echo json_encode(["status" => "error", "message" => "Thiếu tham số MaUngTuyen hoặc TrangThai"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Validate trạng thái
    $allow = ['DangXet', 'MoiPhongVan', 'TuChoi'];
    if (!in_array($TrangThai, $allow, true)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "TrangThai không hợp lệ"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Lấy trạng thái hiện tại
    $stmt = $conn->prepare("SELECT MaTin, MaUngVien, TrangThai FROM UngTuyen WHERE MaUngTuyen = ?");
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
    $id = (int)$MaUngTuyen;
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Ứng tuyển không tồn tại"], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Cập nhật trạng thái
    $stmt = $conn->prepare("UPDATE UngTuyen SET TrangThai = ? WHERE MaUngTuyen = ?");
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
    $stmt->bind_param("si", $TrangThai, $id);
    if (!$stmt->execute()) throw new Exception("Cập nhật thất bại");
    $affected = $stmt->affected_rows;
    $stmt->close();

    // Tạo ChatThread nếu trạng thái mới là MoiPhongVan và khác trạng thái cũ
    if ($TrangThai === 'MoiPhongVan' && $row['TrangThai'] !== 'MoiPhongVan') {
        $MaTin = (int)$row['MaTin'];
        $MaUngVien = (int)$row['MaUngVien'];
        
        // Lấy MaNTD từ TinTuyenDung
        $stmt = $conn->prepare("SELECT MaNTD FROM TinTuyenDung WHERE MaTin = ?");
        $stmt->bind_param("i", $MaTin);
        $stmt->execute();
        $result = $stmt->get_result();
        $ntd = $result->fetch_assoc();
        $stmt->close();

        if (!$ntd) {
            throw new Exception("Không tìm thấy tin tuyển dụng");
        }
        $MaNTD = (int)$ntd['MaNTD'];

        // Kiểm tra thread tồn tại
        $stmt = $conn->prepare("
            SELECT MaThread FROM ChatThread 
            WHERE MaTin = ? AND MaUngVien = ? AND MaNTD = ? LIMIT 1
        ");
        $stmt->bind_param("iii", $MaTin, $MaUngVien, $MaNTD);
        $stmt->execute();
        $result = $stmt->get_result();
        $existed = $result->fetch_assoc();
        $stmt->close();

        if (!$existed) {
            // Tạo ChatThread mới
            $stmt = $conn->prepare("
                INSERT INTO ChatThread (MaTin, MaUngVien, MaNTD, IsOpen, CreatedAt)
                VALUES (?, ?, ?, 1, NOW())
            ");
            $stmt->bind_param("iii", $MaTin, $MaUngVien, $MaNTD);
            if (!$stmt->execute()) {
                throw new Exception("Tạo ChatThread thất bại");
            }
            $MaThread = $conn->insert_id;
            $stmt->close();
        }
    }

    if ($affected > 0) {
        echo json_encode(["status" => "success", "message" => "Cập nhật thành công"], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["status" => "success", "message" => "Không có thay đổi"], JSON_UNESCAPED_UNICODE);
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
    if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
?>