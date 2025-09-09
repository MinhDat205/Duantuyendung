<?php
// /Duantuyendung/interface/API/API_update_Danhmucnghe.php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Access-Control-Allow-Origin: http://localhost:8888');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function require_admin() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        http_response_code(401);
        echo json_encode(["ok" => false, "error" => "Unauthorized"]);
        exit;
    }
}

require_admin();

header('Content-Type: application/json; charset=utf-8');

try {
    $conn = db();
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$conn) {
        throw new Exception("Không thể kết nối cơ sở dữ liệu");
    }

    switch ($method) {
        case 'POST':
            // Thêm danh mục mới
            if (!isset($input['TenDanhMuc']) || empty(trim($input['TenDanhMuc']))) {
                http_response_code(400);
                echo json_encode(["ok" => false, "error" => "Tên danh mục không được để trống"]);
                exit;
            }

            $TenDanhMuc = trim($input['TenDanhMuc']);
            $sql = "INSERT INTO DanhMucNghe (TenDanhMuc) VALUES (?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) throw new Exception("Lỗi prepare: " . $conn->error);

            $stmt->bind_param("s", $TenDanhMuc);
            if ($stmt->execute()) {
                $lastId = $conn->insert_id;
                echo json_encode(["ok" => true, "message" => "Thêm danh mục thành công", "id" => $lastId]);
            } else {
                throw new Exception("Thêm danh mục thất bại");
            }
            $stmt->close();
            break;

        case 'PUT':
            // Cập nhật danh mục
            if (!isset($input['MaDanhMuc']) || !isset($input['TenDanhMuc']) || empty(trim($input['TenDanhMuc']))) {
                http_response_code(400);
                echo json_encode(["ok" => false, "error" => "Mã danh mục và tên danh mục là bắt buộc"]);
                exit;
            }

            $MaDanhMuc = (int)$input['MaDanhMuc'];
            $TenDanhMuc = trim($input['TenDanhMuc']);
            $sql = "UPDATE DanhMucNghe SET TenDanhMuc = ? WHERE MaDanhMuc = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) throw new Exception("Lỗi prepare: " . $conn->error);

            $stmt->bind_param("si", $TenDanhMuc, $MaDanhMuc);
            if ($stmt->execute()) {
                echo json_encode(["ok" => true, "message" => "Cập nhật danh mục thành công"]);
            } else {
                throw new Exception("Cập nhật danh mục thất bại");
            }
            $stmt->close();
            break;

        case 'DELETE':
            // Xóa danh mục
            if (!isset($input['MaDanhMuc']) && !isset($input['ids'])) {
                http_response_code(400);
                echo json_encode(["ok" => false, "error" => "Mã danh mục hoặc danh sách mã là bắt buộc"]);
                exit;
            }

            if (isset($input['ids']) && is_array($input['ids'])) {
                $ids = array_map('intval', $input['ids']);
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $sql = "DELETE FROM DanhMucNghe WHERE MaDanhMuc IN ($placeholders)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) throw new Exception("Lỗi prepare: " . $conn->error);

                $types = str_repeat('i', count($ids));
                $stmt->bind_param($types, ...$ids);
            } else {
                $MaDanhMuc = (int)$input['MaDanhMuc'];
                $sql = "DELETE FROM DanhMucNghe WHERE MaDanhMuc = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) throw new Exception("Lỗi prepare: " . $conn->error);

                $stmt->bind_param("i", $MaDanhMuc);
            }

            if ($stmt->execute()) {
                echo json_encode(["ok" => true, "message" => "Xóa danh mục thành công"]);
            } else {
                throw new Exception("Xóa danh mục thất bại");
            }
            $stmt->close();
            break;

        default:
            http_response_code(405);
            echo json_encode(["ok" => false, "error" => "Phương thức không được hỗ trợ"]);
            exit;
    }

    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["ok" => false, "error" => $e->getMessage()]);
    if (isset($conn)) $conn->close();
}