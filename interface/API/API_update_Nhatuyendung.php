<?php
// interface/API/API_update_Nhatuyendung.php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

// Preflight (nếu bạn bật CORS trong config.php)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['status'=>'error','message'=>'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
  exit;
}

// Nhận JSON hoặc form
$raw  = file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body) || empty($body)) $body = $_POST;

// Input
$MaNTD           = isset($body['MaNTD']) ? (int)$body['MaNTD'] : 0;
$TenCongTy       = isset($body['TenCongTy']) ? trim($body['TenCongTy']) : null;
$DiaChi          = isset($body['DiaChi']) ? trim($body['DiaChi']) : null;
$SoDienThoai     = isset($body['SoDienThoai']) ? trim($body['SoDienThoai']) : null;
$ThongTinCongTy  = isset($body['ThongTinCongTy']) ? trim($body['ThongTinCongTy']) : null;

if (!$MaNTD) {
  http_response_code(422);
  echo json_encode(['status'=>'error','message'=>'Thiếu MaNTD'], JSON_UNESCAPED_UNICODE);
  exit;
}

$fields = [];
$types  = '';
$params = [];

// Cho phép cập nhật linh hoạt các trường được gửi lên
if ($TenCongTy !== null)      { $fields[] = 'TenCongTy = ?';      $types .= 's'; $params[] = $TenCongTy; }
if ($SoDienThoai !== null)    { $fields[] = 'SoDienThoai = ?';    $types .= 's'; $params[] = $SoDienThoai; }
if ($DiaChi !== null)         { $fields[] = 'DiaChi = ?';         $types .= 's'; $params[] = $DiaChi; }
if ($ThongTinCongTy !== null) { $fields[] = 'ThongTinCongTy = ?'; $types .= 's'; $params[] = $ThongTinCongTy; }

if (!$fields) {
  http_response_code(422);
  echo json_encode(['status'=>'error','message'=>'Không có trường nào để cập nhật'], JSON_UNESCAPED_UNICODE);
  exit;
}

// Thêm UpdatedAt
$setSql = implode(', ', $fields) . ', UpdatedAt = CURRENT_TIMESTAMP';
$sql    = "UPDATE NhaTuyenDung SET $setSql WHERE MaNTD = ?";

try {
  $stmt = $conn->prepare($sql);
  if (!$stmt) throw new Exception('Prepare failed: '.$conn->error);

  $types  .= 'i';
  $params[] = $MaNTD;

  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $aff = $stmt->affected_rows;
  $stmt->close();

  if ($aff > 0) {
    echo json_encode(['status'=>'success','message'=>'Cập nhật thành công'], JSON_UNESCAPED_UNICODE);
  } else {
    // Không thay đổi gì hoặc MaNTD không tồn tại
    $chk = $conn->prepare("SELECT 1 FROM NhaTuyenDung WHERE MaNTD = ? LIMIT 1");
    $chk->bind_param('i', $MaNTD);
    $chk->execute();
    $exists = $chk->get_result()->fetch_row();
    $chk->close();

    if ($exists) {
      echo json_encode(['status'=>'error','message'=>'Không có thay đổi nào được thực hiện'], JSON_UNESCAPED_UNICODE);
    } else {
      http_response_code(404);
      echo json_encode(['status'=>'error','message'=>'Không tìm thấy NTD'], JSON_UNESCAPED_UNICODE);
    }
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
  if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
