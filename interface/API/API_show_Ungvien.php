<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

function get_input($keys, $src1 = [], $src2 = []) {
  foreach ((array)$keys as $k) {
    if (isset($src1[$k]) && $src1[$k] !== '') return trim($src1[$k]);
    if (isset($src2[$k]) && $src2[$k] !== '') return trim($src2[$k]);
    if (isset($_GET[$k])  && $_GET[$k]  !== '') return trim($_GET[$k]);
  }
  return null;
}

try {
  $raw  = file_get_contents('php://input');
  $json = json_decode($raw, true) ?: [];
  $post = $_POST;

  // Lấy thông tin ứng viên theo MaTK hoặc MaUngVien
  $MaTK = get_input(['MaTK'], $json, $post);
  $MaUngVien = get_input(['MaUngVien'], $json, $post);

  if (!$MaTK && !$MaUngVien) {
    throw new Exception("Thiếu tham số: MaTK hoặc MaUngVien");
  }

  // Base query: lấy thông tin ứng viên
  $sql = "
    SELECT 
      uv.MaUngVien,
      uv.HoTen,
      uv.SoDienThoai,
      uv.KyNang,
      uv.KinhNghiem,
      uv.AnhCV,
      uv.MaDanhMuc,
      tk.Email,
      tk.MaTK
    FROM UngVien uv
    JOIN TaiKhoan tk ON tk.MaTK = uv.MaTK
    WHERE 1=1
  ";

  $types = "";
  $binds = [];
  
  // Xử lý logic tìm kiếm
  if ($MaUngVien && $MaTK) {
    // Nếu có cả hai, tìm theo MaUngVien trước
    $sql .= " AND uv.MaUngVien = ? ";
    $types .= "i"; 
    $binds[] = (int)$MaUngVien;
  } elseif ($MaUngVien) {
    // Chỉ có MaUngVien
    $sql .= " AND uv.MaUngVien = ? ";
    $types .= "i"; 
    $binds[] = (int)$MaUngVien;
  } elseif ($MaTK) {
    // Chỉ có MaTK
    $sql .= " AND uv.MaTK = ? ";
    $types .= "i"; 
    $binds[] = (int)$MaTK;
  }

  $sql .= " LIMIT 1 ";

  $stmt = $conn->prepare($sql);
  if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
  
  if ($types) {
    $stmt->bind_param($types, ...$binds);
  }
  
  $stmt->execute();
  $rs = $stmt->get_result();

  if (!$rs) throw new Exception("Query failed: " . $conn->error);

  $data = $rs->fetch_assoc();
  
  if (!$data) {
    throw new Exception("Không tìm thấy thông tin ứng viên");
  }

  $stmt->close();

  echo json_encode([
    "ok" => true,
    "data" => $data
  ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    "ok" => false,
    "error" => $e->getMessage()
  ], JSON_UNESCAPED_UNICODE);
} finally {
  if (isset($conn) && $conn instanceof mysqli) $conn->close();
}