<?php
// File: API_show_Taikhoan.php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

// Luôn khai báo header JSON sớm
header('Content-Type: application/json; charset=utf-8');

try {
    // Nếu muốn, có thể set timezone
    // date_default_timezone_set('Asia/Ho_Chi_Minh');

    // Truy vấn dữ liệu + alias theo UI (TenDangNhap, LoaiTK)
    $sql = "
        SELECT
            MaTK,
            Email,
            Email AS TenDangNhap,
            MatKhau,
            LoaiTaiKhoan AS LoaiTK,
            TrangThai,
            NgayTao
        FROM TaiKhoan
        ORDER BY MaTK DESC
    ";

    $result = $conn->query($sql);
    if ($result === false) {
        throw new Exception('Lỗi truy vấn: ' . $conn->error);
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        // Chuẩn hoá trạng thái về HoatDong | BiKhoa (phòng dữ liệu không chuẩn)
        $tt = isset($row['TrangThai']) ? trim((string)$row['TrangThai']) : '';
        $ttLower = mb_strtolower($tt, 'UTF-8');

        if ($ttLower === 'hoatdong' || $ttLower === 'hoạt động' || $ttLower === 'active' || $ttLower === '1' || $ttLower === 'true') {
            $row['TrangThai'] = 'HoatDong';
        } elseif ($ttLower === 'bikhoa' || $ttLower === 'bị khóa' || $ttLower === 'locked' || $ttLower === '0' || $ttLower === 'false') {
            $row['TrangThai'] = 'BiKhoa';
        } else {
            // fallback an toàn
            $row['TrangThai'] = ($ttLower === 'hoatdong') ? 'HoatDong' : 'BiKhoa';
        }

        $data[] = $row;
    }

    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} finally {
    if (isset($result) && $result instanceof mysqli_result) {
        $result->free();
    }
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
