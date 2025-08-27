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

  // Bộ lọc tuỳ chọn
  $MaTin     = get_input(['MaTin'],     $json, $post);    // ưu tiên lọc theo tin
  $MaNTD     = get_input(['MaNTD'],     $json, $post);    // hoặc lọc theo chủ NTD
  $TrangThai = get_input(['TrangThai'], $json, $post);    // DangXet | MoiPhongVan | TuChoi
  $q         = get_input(['q', 'query', 'search'], $json, $post); // tìm kiếm nhanh

  // Base query: lấy đủ thông tin để hiển thị
  $sql = "
    SELECT 
      ut.MaUngTuyen, ut.MaTin, ut.MaUngVien, ut.NgayUngTuyen, ut.TrangThai,
      uv.HoTen, uv.SoDienThoai,
      tk.Email,
      ttd.ChucDanh, ttd.MaNTD
    FROM UngTuyen ut
    JOIN UngVien uv       ON uv.MaUngVien = ut.MaUngVien
    JOIN TaiKhoan tk      ON tk.MaTK      = uv.MaTK
    JOIN TinTuyenDung ttd ON ttd.MaTin    = ut.MaTin
    WHERE 1=1
  ";

  $types = "";
  $binds = [];
  if ($MaTin !== null && $MaTin !== "") {
    $sql .= " AND ut.MaTin = ? ";
    $types .= "i"; $binds[] = (int)$MaTin;
  }
  if ($MaNTD !== null && $MaNTD !== "") {
    $sql .= " AND ttd.MaNTD = ? ";
    $types .= "i"; $binds[] = (int)$MaNTD;
  }
  if ($TrangThai !== null && $TrangThai !== "") {
    $sql .= " AND ut.TrangThai = ? ";
    $types .= "s"; $binds[] = $TrangThai;
  }
  if ($q !== null && $q !== "") {
    // Tìm theo họ tên / email / sđt (LIKE)
    $like = "%".$conn->real_escape_string($q)."%";
    $sql .= " AND (uv.HoTen LIKE ? OR tk.Email LIKE ? OR uv.SoDienThoai LIKE ?) ";
    $types .= "sss"; array_push($binds, $like, $like, $like);
  }

  $sql .= " ORDER BY ut.NgayUngTuyen DESC ";

  if ($types) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Prepare failed: ".$conn->error);
    $stmt->bind_param($types, ...$binds);
    $stmt->execute();
    $rs = $stmt->get_result();
  } else {
    $rs = $conn->query($sql);
  }

  if (!$rs) throw new Exception("Query failed: ".$conn->error);

  $data = [];
  while ($row = $rs->fetch_assoc()) $data[] = $row;

  if (isset($stmt) && $stmt instanceof mysqli_stmt) $stmt->close();

  echo json_encode(["status"=>"success","data"=>$data], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["status"=>"error","message"=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
  if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
