<?php
// interface/API/API_show_TinTuyenDung.php
require_once __DIR__ . '/config.php';

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Helper function để lấy trường an toàn từ nhiều nguồn
function get_input($keys, $src1, $src2) {
    foreach ((array)$keys as $k) {
        if (isset($src1[$k]) && $src1[$k] !== '') return trim($src1[$k]);
        if (isset($src2[$k]) && $src2[$k] !== '') return trim($src2[$k]);
    }
    return null;
}

// Đọc dữ liệu từ cả JSON và form
$raw = file_get_contents("php://input");
$json = json_decode($raw, true);
$post = $_POST;

try {
    // Lấy MaTin từ query string hoặc body
    $maTin = get_input(['MaTin'], $json, $post);
    
    if ($maTin) {
        // Lấy thông tin tin tuyển dụng cụ thể
        $stmt = $conn->prepare("
            SELECT tt.MaTin, tt.MaNTD, ntd.TenCongTy, tt.MaDanhMuc, dm.TenDanhMuc,
                   tt.ChucDanh, tt.MoTaCongViec, tt.YeuCau, tt.MucLuong, 
                   tt.DiaDiemLamViec, tt.NgayDang, tt.TrangThai
            FROM TinTuyenDung tt
            LEFT JOIN NhaTuyenDung ntd ON tt.MaNTD = ntd.MaNTD
            LEFT JOIN DanhMucNghe dm ON tt.MaDanhMuc = dm.MaDanhMuc
            WHERE tt.MaTin = ? AND tt.TrangThai = 'DaDuyet'
        ");
        $stmt->bind_param('i', $maTin);
        $stmt->execute();
        $result = $stmt->get_result();
        $tinTuyenDung = $result->fetch_assoc();
        $stmt->close();
        
        if ($tinTuyenDung) {
            echo json_encode(['ok' => true, 'data' => $tinTuyenDung], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['ok' => false, 'error' => 'Không tìm thấy tin tuyển dụng'], JSON_UNESCAPED_UNICODE);
        }
    } else {
        // Lấy tất cả tin tuyển dụng đã duyệt
        $stmt = $conn->prepare("
            SELECT tt.MaTin, tt.MaNTD, ntd.TenCongTy, tt.MaDanhMuc, dm.TenDanhMuc,
                   tt.ChucDanh, tt.MoTaCongViec, tt.YeuCau, tt.MucLuong, 
                   tt.DiaDiemLamViec, tt.NgayDang, tt.TrangThai
            FROM TinTuyenDung tt
            LEFT JOIN NhaTuyenDung ntd ON tt.MaNTD = ntd.MaNTD
            LEFT JOIN DanhMucNghe dm ON tt.MaDanhMuc = dm.MaDanhMuc
            WHERE tt.TrangThai = 'DaDuyet'
            ORDER BY tt.NgayDang DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $tinTuyenDungList = [];
        while ($row = $result->fetch_assoc()) {
            $tinTuyenDungList[] = $row;
        }
        $stmt->close();
        
        // Trả về dữ liệu phù hợp với code UI
        echo json_encode($tinTuyenDungList, JSON_UNESCAPED_UNICODE);
    }
    
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Lỗi server: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
    if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
?>
