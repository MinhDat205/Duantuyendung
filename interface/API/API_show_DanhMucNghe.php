<?php
// interface/API/API_show_Danhmucnghe.php
require_once __DIR__ . '/config.php';
require_method('GET');

$conn  = db();
$id    = inparam(['id']);
$q     = inparam(['q'], '');
$page  = max(1, (int) inparam(['page'], 1));
$size  = max(1, min(100, (int) inparam(['pageSize'], 20)));
$offset = ($page - 1) * $size;

if (!empty($id)) {
  $stmt = $conn->prepare("SELECT MaDanhMuc, TenDanhMuc FROM DanhMucNghe WHERE MaDanhMuc = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  if (!$row) json_out(['ok'=>false,'error'=>'Không tìm thấy danh mục'], 404);
  json_out(['ok'=>true,'data'=>$row]);
}

if ($q !== '') {
  $like = "%{$q}%";
  $stmt = $conn->prepare("SELECT COUNT(*) total FROM DanhMucNghe WHERE TenDanhMuc LIKE ?");
  $stmt->bind_param("s", $like);
  $stmt->execute();
  $total = (int) $stmt->get_result()->fetch_assoc()['total'];
  $stmt->close();

  $stmt = $conn->prepare("
    SELECT MaDanhMuc, TenDanhMuc
    FROM DanhMucNghe
    WHERE TenDanhMuc LIKE ?
    ORDER BY TenDanhMuc ASC
    LIMIT ? OFFSET ?
  ");
  $stmt->bind_param("sii", $like, $size, $offset);
  $stmt->execute();
  $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
} else {
  $total = (int) $conn->query("SELECT COUNT(*) total FROM DanhMucNghe")->fetch_assoc()['total'];
  $stmt = $conn->prepare("
    SELECT MaDanhMuc, TenDanhMuc
    FROM DanhMucNghe
    ORDER BY TenDanhMuc ASC
    LIMIT ? OFFSET ?
  ");
  $stmt->bind_param("ii", $size, $offset);
  $stmt->execute();
  $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
}

json_out([
  'ok' => true,
  'data' => [
    'items' => $items,
    'paging' => [
      'page' => $page,
      'pageSize' => $size,
      'total' => $total,
      'totalPages' => (int)ceil($total / $size),
      'query' => $q
    ]
  ]
]);
