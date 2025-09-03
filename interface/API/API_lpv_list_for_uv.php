<?php
require 'config.php';
require_method('GET');

try {
  // ---- Input ----
  $MaUngVien = (int) inparam(['MaUngVien'], 0);
  if (!$MaUngVien) {
    json_out(['ok' => false, 'error' => 'Thiếu MaUngVien'], 400);
  }

  $status       = trim((string) inparam(['status'], ''));  // DaLenLich | HoanThanh | Huy | All/blank
  $onlyUpcoming = (int) inparam(['upcoming'], 0);          // 1 -> chỉ lịch tương lai
  $q            = trim((string) inparam(['q'], ''));       // tìm kiếm tự do
  $limit        = (int) inparam(['limit'], 200);
  if ($limit < 1)   $limit = 1;
  if ($limit > 1000) $limit = 1000;

  // ---- Build conditions ----
  $conds = [];
  $types = '';
  $args  = [];

  // Giới hạn theo ứng viên
  $conds[] = 'ut.MaUngVien = ?';
  $types  .= 'i';
  $args[]  = $MaUngVien;

  // Trạng thái
  if ($status !== '' && strcasecmp($status, 'All') !== 0) {
    $allowed = ['DaLenLich', 'HoanThanh', 'Huy'];
    if (!in_array($status, $allowed, true)) {
      json_out(['ok' => false, 'error' => 'status không hợp lệ'], 400);
    }
    $conds[] = 'lpv.TrangThai = ?';
    $types  .= 's';
    $args[]  = $status;
  }

  // Chỉ lịch tương lai
  if ($onlyUpcoming) {
    $conds[] = 'lpv.NgayGioPhongVan >= NOW()';
  }

  // Tìm kiếm free-text
  if ($q !== '') {
    $conds[] = '(ntd.TenCongTy LIKE ? OR t.ChucDanh LIKE ? OR t.DiaDiemLamViec LIKE ? OR lpv.GhiChu LIKE ?)';
    $types  .= 'ssss';
    $like = '%' . $q . '%';
    array_push($args, $like, $like, $like, $like);
  }

  $where = $conds ? ('WHERE ' . implode(' AND ', $conds)) : '';

  // ---- Query ----
  $sql = "
    SELECT
      lpv.MaLichPV,
      lpv.MaUngTuyen,
      lpv.NgayGioPhongVan,
      lpv.HinhThuc,
      lpv.TrangThai,
      lpv.GhiChu,
      ut.MaTin,
      ut.MaUngVien,
      ntd.TenCongTy,
      t.ChucDanh,
      t.DiaDiemLamViec
    FROM LichPhongVan lpv
    JOIN UngTuyen ut ON lpv.MaUngTuyen = ut.MaUngTuyen
    JOIN TinTuyenDung t ON ut.MaTin = t.MaTin
    JOIN NhaTuyenDung ntd ON t.MaNTD = ntd.MaNTD
    $where
    ORDER BY lpv.NgayGioPhongVan DESC, lpv.MaLichPV DESC
    LIMIT ?
  ";

  // Thêm LIMIT vào bind
  $types .= 'i';
  $args[] = $limit;

  // Debug: Ghi câu lệnh SQL và tham số
  error_log("SQL: $sql");
  error_log("Params: " . json_encode($args));
  error_log("Types: $types");

  $stmt = db()->prepare($sql);
  if (!$stmt) {
    json_out(['ok' => false, 'error' => 'prepare failed: ' . db()->error], 500);
  }

  $stmt->bind_param($types, ...$args);
  if (!$stmt->execute()) {
    json_out(['ok' => false, 'error' => 'execute failed: ' . $stmt->error], 500);
  }
  $res = $stmt->get_result();
  $items = [];
  while ($row = $res->fetch_assoc()) $items[] = $row;

  json_out(['ok' => true, 'items' => $items]);

} catch (Throwable $e) {
  json_out(['ok' => false, 'error' => 'server: ' . $e->getMessage()], 500);
}
?>