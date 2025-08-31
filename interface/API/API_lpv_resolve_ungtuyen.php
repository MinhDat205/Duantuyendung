<?php
// interface/API/API_lpv_resolve_ungtuyen.php
require_once __DIR__ . '/config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
header('Content-Type: application/json; charset=utf-8');

try {
  $MaTin      = isset($_GET['MaTin']) ? (int)$_GET['MaTin'] : 0;
  $MaUngVien  = isset($_GET['MaUngVien']) ? (int)$_GET['MaUngVien'] : 0;
  $MaNTD      = isset($_GET['MaNTD']) ? (int)$_GET['MaNTD'] : 0;

  if (!$MaTin || !$MaUngVien || !$MaNTD) {
    http_response_code(400);
    echo json_encode(['ok'=>false, 'error'=>'Thiếu MaTin/MaUngVien/MaNTD']);
    exit;
  }

  // Kiểm tra Tin thuộc NTD này
  $sqlTin = "SELECT MaTin, MaNTD, ChucDanh, DiaDiemLamViec FROM TinTuyenDung WHERE MaTin = ?";
  $stmTin = $pdo->prepare($sqlTin);
  $stmTin->execute([$MaTin]);
  $tin = $stmTin->fetch(PDO::FETCH_ASSOC);
  if (!$tin || (int)$tin['MaNTD'] !== $MaNTD) {
    http_response_code(403);
    echo json_encode(['ok'=>false, 'error'=>'Tin không thuộc NTD']);
    exit;
  }

  // Tìm bản ghi Ứng tuyển + kiểm tra trạng thái
  $sqlUT = "SELECT ut.MaUngTuyen, ut.TrangThai, ut.NgayUngTuyen
            FROM UngTuyen ut
            WHERE ut.MaTin=? AND ut.MaUngVien=?";
  $stmUT = $pdo->prepare($sqlUT);
  $stmUT->execute([$MaTin, $MaUngVien]);
  $ut = $stmUT->fetch(PDO::FETCH_ASSOC);
  if (!$ut) {
    http_response_code(404);
    echo json_encode(['ok'=>false, 'error'=>'Ứng viên chưa ứng tuyển tin này']);
    exit;
  }
  if ($ut['TrangThai'] !== 'MoiPhongVan') {
    http_response_code(409);
    echo json_encode(['ok'=>false, 'error'=>'Trạng thái không cho phép tạo lịch (yêu cầu MoiPhongVan)', 'TrangThai'=>$ut['TrangThai']]);
    exit;
  }

  // Trả thêm thông tin UV
  $sqlUV = "SELECT MaUngVien, HoTen, SoDienThoai FROM UngVien WHERE MaUngVien=?";
  $stmUV = $pdo->prepare($sqlUV);
  $stmUV->execute([$MaUngVien]);
  $uv = $stmUV->fetch(PDO::FETCH_ASSOC) ?: null;

  echo json_encode([
    'ok'=>true,
    'MaUngTuyen'=>(int)$ut['MaUngTuyen'],
    'UngTuyen'=>$ut,
    'Tin'=>$tin,
    'UngVien'=>$uv
  ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false, 'error'=>'Server error', 'detail'=>$e->getMessage()]);
}
