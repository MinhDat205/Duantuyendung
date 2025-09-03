<?php
require_once __DIR__ . '/config.php';

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(); }

function get_input($keys, $src1 = [], $src2 = []) {
  foreach ((array)$keys as $k) {
    if (isset($src1[$k]) && $src1[$k] !== '') return trim($src1[$k]);
    if (isset($src2[$k]) && $src2[$k] !== '') return trim($src2[$k]);
    if (isset($_GET[$k]) && $_GET[$k] !== '') return trim($_GET[$k]);
  }
  return null;
}

try {
  $raw  = file_get_contents("php://input");
  $json = json_decode($raw, true) ?: [];
  $post = $_POST;

  $maTin = get_input(['MaTin'], $json, $post);
  $scope = strtolower(get_input(['scope'], $json, $post)) ?: 'public'; // public|admin
  $onlyApproved = ($scope !== 'admin');

  if ($maTin) {
    $sql = "
      SELECT tt.MaTin, tt.MaNTD, ntd.TenCongTy, tt.MaDanhMuc, dm.TenDanhMuc,
             tt.ChucDanh, tt.MoTaCongViec, tt.YeuCau, tt.MucLuong,
             tt.DiaDiemLamViec, tt.NgayDang, tt.TrangThai
      FROM TinTuyenDung tt
      LEFT JOIN NhaTuyenDung ntd ON tt.MaNTD = ntd.MaNTD
      LEFT JOIN DanhMucNghe dm ON tt.MaDanhMuc = dm.MaDanhMuc
      WHERE tt.MaTin = ?
    ";
    if ($onlyApproved) $sql .= " AND tt.TrangThai = 'DaDuyet'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $maTin);
    $stmt->execute();
    $rs = $stmt->get_result();
    $row = $rs->fetch_assoc();
    $stmt->close();

    echo json_encode($row ? ['ok'=>true,'data'=>$row] : ['ok'=>false,'error'=>'Không tìm thấy tin'], JSON_UNESCAPED_UNICODE);
    exit();
  }

  $sql = "
    SELECT tt.MaTin, tt.MaNTD, ntd.TenCongTy, tt.MaDanhMuc, dm.TenDanhMuc,
           tt.ChucDanh, tt.MoTaCongViec, tt.YeuCau, tt.MucLuong,
           tt.DiaDiemLamViec, tt.NgayDang, tt.TrangThai
    FROM TinTuyenDung tt
    LEFT JOIN NhaTuyenDung ntd ON tt.MaNTD = ntd.MaNTD
    LEFT JOIN DanhMucNghe dm ON tt.MaDanhMuc = dm.MaDanhMuc
  ";
  if ($onlyApproved) $sql .= " WHERE tt.TrangThai = 'DaDuyet'";
  $sql .= " ORDER BY tt.NgayDang DESC";

  $rows = [];
  $rs = $conn->query($sql);
  while ($r = $rs->fetch_assoc()) $rows[] = $r;

  echo json_encode($rows, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Lỗi server: '.$e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
  if (isset($conn) && $conn instanceof mysqli) $conn->close();
}