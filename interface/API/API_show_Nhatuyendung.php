<?php
// interface/API/API_show_Nhatuyendung.php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

try {
  $maTK = isset($_GET['MaTK']) ? (int)$_GET['MaTK'] : 0;
  $data = [];

  if ($maTK > 0) {
    $stmt = $conn->prepare("
      SELECT NTD.*, TK.Email
      FROM NhaTuyenDung NTD
      JOIN TaiKhoan TK ON NTD.MaTK = TK.MaTK
      WHERE NTD.MaTK = ?
      LIMIT 1
    ");
    if (!$stmt) throw new Exception('Prepare failed: '.$conn->error);
    $stmt->bind_param('i', $maTK);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) $data[] = $row;
    $stmt->close();
  } else {
    $sql = "
      SELECT NTD.*, TK.Email
      FROM NhaTuyenDung NTD
      JOIN TaiKhoan TK ON NTD.MaTK = TK.MaTK
      ORDER BY NTD.MaNTD DESC
    ";
    $rs = $conn->query($sql);
    if ($rs === false) throw new Exception('Query lỗi: '.$conn->error);
    while ($row = $rs->fetch_assoc()) $data[] = $row;
    $rs->free();
  }

  echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} finally {
  if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
