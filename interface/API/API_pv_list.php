<?php
// C:\xampp\htdocs\Duantuyendung\interface\API\API_pv_list.php
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

try {
  $role = $_GET['role'] ?? '';
  $MaNTD = isset($_GET['MaNTD']) ? (int)$_GET['MaNTD'] : 0;
  $MaTin = isset($_GET['MaTin']) ? (int)$_GET['MaTin'] : 0;
  $MaUngVien = isset($_GET['MaUngVien']) ? (int)$_GET['MaUngVien'] : 0;

  if ($role !== 'NhaTuyenDung') throw new Exception('role không hợp lệ');
  if (!$MaNTD || !$MaTin || !$MaUngVien) throw new Exception('Thiếu tham số');

  // Tái sử dụng logic: xác thực & lấy MaUngTuyen
  $sql = "SELECT ut.MaUngTuyen
          FROM UngTuyen ut
          JOIN TinTuyenDung t ON ut.MaTin = t.MaTin
          WHERE ut.MaTin=? AND ut.MaUngVien=? AND t.MaNTD=?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$MaTin, $MaUngVien, $MaNTD]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) throw new Exception('Không hợp lệ (Tin-NTD-UV)');

  $sqlList = "SELECT MaLichPV, MaUngTuyen, NgayGioPhongVan, HinhThuc, TrangThai, GhiChu
              FROM LichPhongVan
              WHERE MaUngTuyen=?
              ORDER BY NgayGioPhongVan DESC, MaLichPV DESC";
  $stmt = $pdo->prepare($sqlList);
  $stmt->execute([$row['MaUngTuyen']]);
  $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode(['ok'=>true, 'items'=>$items]);
} catch (Exception $e) {
  http_response_code(404);
  echo json_encode(['ok'=>false, 'error'=>$e->getMessage(), 'items'=>[]]);
}
