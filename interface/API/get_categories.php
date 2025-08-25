<?php
// API/get_categories.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/utils.php';

try {
  $q = $pdo->query("SELECT MaDanhMuc, TenDanhMuc FROM DanhMucNghe ORDER BY TenDanhMuc ASC");
  $rows = $q->fetchAll();
  json_ok($rows);
} catch (Exception $e) {
  json_error('Lỗi lấy danh mục', 500, ['detail'=>$e->getMessage()]);
}
