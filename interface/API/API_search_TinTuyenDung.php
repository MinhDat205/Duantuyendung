<?php
// interface/API/API_search_TinTuyenDung.php
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
    // Lấy các tham số tìm kiếm
    $keyword = get_input(['keyword', 'tukhoa'], $json, $post);
    $diaDiem = get_input(['DiaDiemLamViec', 'diaDiem'], $json, $post);
    $maDanhMuc = get_input(['MaDanhMuc', 'danhMuc'], $json, $post);
    $mucLuongMin = get_input(['mucLuongMin', 'luongMin'], $json, $post);
    $mucLuongMax = get_input(['mucLuongMax', 'luongMax'], $json, $post);
    
    // Xây dựng câu query
    $sql = "
        SELECT tt.MaTin, tt.MaNTD, ntd.TenCongTy, tt.MaDanhMuc, dm.TenDanhMuc,
               tt.ChucDanh, tt.MoTaCongViec, tt.YeuCau, tt.MucLuong, 
               tt.DiaDiemLamViec, tt.NgayDang, tt.TrangThai
        FROM TinTuyenDung tt
        LEFT JOIN NhaTuyenDung ntd ON tt.MaNTD = ntd.MaNTD
        LEFT JOIN DanhMucNghe dm ON tt.MaDanhMuc = dm.MaDanhMuc
        WHERE tt.TrangThai = 'DaDuyet'
    ";
    
    $params = [];
    $types = '';
    
    // Thêm điều kiện tìm kiếm
    if ($keyword) {
        $sql .= " AND (tt.ChucDanh LIKE ? OR tt.MoTaCongViec LIKE ? OR ntd.TenCongTy LIKE ?)";
        $keywordParam = "%$keyword%";
        $params[] = $keywordParam;
        $params[] = $keywordParam;
        $params[] = $keywordParam;
        $types .= 'sss';
    }
    
    if ($diaDiem) {
        $sql .= " AND tt.DiaDiemLamViec LIKE ?";
        $params[] = "%$diaDiem%";
        $types .= 's';
    }
    
    if ($maDanhMuc) {
        $sql .= " AND tt.MaDanhMuc = ?";
        $params[] = $maDanhMuc;
        $types .= 'i';
    }
    
    if ($mucLuongMin) {
        $sql .= " AND CAST(SUBSTRING_INDEX(tt.MucLuong, '-', 1) AS UNSIGNED) >= ?";
        $params[] = $mucLuongMin;
        $types .= 'i';
    }
    
    if ($mucLuongMax) {
        $sql .= " AND CAST(SUBSTRING_INDEX(tt.MucLuong, '-', -1) AS UNSIGNED) <= ?";
        $params[] = $mucLuongMax;
        $types .= 'i';
    }
    
    $sql .= " ORDER BY tt.NgayDang DESC";
    
    // Thực thi query
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tinTuyenDungList = [];
    while ($row = $result->fetch_assoc()) {
        $tinTuyenDungList[] = $row;
    }
    $stmt->close();
    
    // Trả về dữ liệu phù hợp với code UI
    echo json_encode([
        'status' => 'success',
        'data' => $tinTuyenDungList,
        'total' => count($tinTuyenDungList)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Lỗi server: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
    if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
?>
