<?php
// interface/API/API_lpv_list_for_ntd.php
require 'config.php';
require_method('GET');

try {
  // ---- Input ----
  $MaNTD = (int) inparam(['MaNTD'], 0);
  if (!$MaNTD) {
    json_out(['ok'=>false,'error'=>'Thiếu MaNTD'], 400);
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

  // Giới hạn theo NTD đang đăng nhập (qua TinTuyenDung)
  $conds[] = 't.MaNTD = ?';
  $types  .= 'i';
  $args[]  = $MaNTD;

  // Trạng thái
  if ($status !== '' && strcasecmp($status, 'All') !== 0) {
    // Chỉ nhận các giá trị hợp lệ
    $allowed = ['DaLenLich','HoanThanh','Huy'];
    if (!in_array($status, $allowed, true)) {
      json_out(['ok'=>false,'error'=>'status không hợp lệ'], 400);
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
    $conds[] = '(uv.HoTen LIKE ? OR t.ChucDanh LIKE ? OR t.DiaDiemLamViec LIKE ? OR lpv.GhiChu LIKE ?)';
    $types  .= 'ssss';
    $like = '%'.$q.'%';
    array_push($args, $like, $like, $like, $like);
  }

  $where = $conds ? ('WHERE '.implode(' AND ', $conds)) : '';

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
      uv.HoTen,
      t.ChucDanh,
      t.DiaDiemLamViec
    FROM LichPhongVan lpv
    JOIN UngTuyen ut     ON lpv.MaUngTuyen = ut.MaUngTuyen
    JOIN TinTuyenDung t  ON ut.MaTin = t.MaTin
    JOIN UngVien uv      ON ut.MaUngVien = uv.MaUngVien
    $where
    ORDER BY lpv.NgayGioPhongVan DESC, lpv.MaLichPV DESC
    LIMIT ?
  ";

  // Thêm LIMIT vào bind
  $types .= 'i';
  $args[] = $limit;

  $stmt = db()->prepare($sql);
  if (!$stmt) {
    json_out(['ok'=>false,'error'=>'prepare failed: '.db()->error], 500);
  }

  $stmt->bind_param($types, ...$args);
  if (!$stmt->execute()) {
    json_out(['ok'=>false,'error'=>'execute failed: '.$stmt->error], 500);
  }
  $res = $stmt->get_result();
  $items = [];
  while ($row = $res->fetch_assoc()) $items[] = $row;

  json_out(['ok'=>true, 'items'=>$items]);

} catch (Throwable $e) {
  json_out(['ok'=>false,'error'=>'server: '.$e->getMessage()], 500);
}
