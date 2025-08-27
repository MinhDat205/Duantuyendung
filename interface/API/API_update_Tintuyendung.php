<?php
require_once __DIR__ . '/config.php';

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(); }

try {
  // Nhận dữ liệu
  $raw  = file_get_contents("php://input");
  $json = json_decode($raw, true) ?: [];
  $post = $_POST;
  $get  = $_GET;

  $MaTin           = $json['MaTin']           ?? $post['MaTin']           ?? $get['MaTin']           ?? null;
  $ChucDanh        = $json['ChucDanh']        ?? $post['ChucDanh']        ?? null;
  $MoTaCongViec    = $json['MoTaCongViec']    ?? $post['MoTaCongViec']    ?? null;
  $YeuCau          = $json['YeuCau']          ?? $post['YeuCau']          ?? null;
  $MucLuong        = $json['MucLuong']        ?? $post['MucLuong']        ?? null;
  $DiaDiemLamViec  = $json['DiaDiemLamViec']  ?? $post['DiaDiemLamViec']  ?? null;
  $TrangThai       = $json['TrangThai']       ?? $post['TrangThai']       ?? null;

  if (!$MaTin) throw new Exception("Thiếu MaTin");

  // Xây dựng dynamic update
  $fields = [];
  $params = [];
  $types  = "";

  if ($ChucDanh !== null)       { $fields[] = "ChucDanh=?";        $params[] = $ChucDanh;       $types.="s"; }
  if ($MoTaCongViec !== null)   { $fields[] = "MoTaCongViec=?";    $params[] = $MoTaCongViec;   $types.="s"; }
  if ($YeuCau !== null)         { $fields[] = "YeuCau=?";          $params[] = $YeuCau;         $types.="s"; }
  if ($MucLuong !== null)       { $fields[] = "MucLuong=?";        $params[] = $MucLuong;       $types.="s"; }
  if ($DiaDiemLamViec !== null) { $fields[] = "DiaDiemLamViec=?";  $params[] = $DiaDiemLamViec; $types.="s"; }
  if ($TrangThai !== null)      { $fields[] = "TrangThai=?";       $params[] = $TrangThai;      $types.="s"; }

  if (!$fields) throw new Exception("Không có trường nào để cập nhật");

  $sql = "UPDATE TinTuyenDung SET ".implode(", ", $fields)." WHERE MaTin=?";
  $types .= "i";
  $params[] = (int)$MaTin;

  $stmt = $conn->prepare($sql);
  $stmt->bind_param($types, ...$params);
  if (!$stmt->execute()) throw new Exception("Lỗi cập nhật");
  $stmt->close();

  echo json_encode(['status' => 'success'], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  http_response_code(400);
  echo json_encode(['status'=>'error','message'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
  if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
