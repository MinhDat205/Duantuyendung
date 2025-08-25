<?php
// interface/API/upload_cv.php
require_once __DIR__ . '/config.php'; // chủ yếu để dùng headers/CORS; không cần DB

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok'=>false,'error'=>'Phương thức không hợp lệ'], JSON_UNESCAPED_UNICODE);
  exit;
}

if (!isset($_FILES['cv']) || $_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Không nhận được file hoặc lỗi upload', 'php_error'=>$_FILES['cv']['error'] ?? null], JSON_UNESCAPED_UNICODE);
  exit;
}

$maxSize = 5 * 1024 * 1024; // 5MB
if ($_FILES['cv']['size'] > $maxSize) {
  http_response_code(413);
  echo json_encode(['ok'=>false,'error'=>'File quá lớn (tối đa 5MB)'], JSON_UNESCAPED_UNICODE);
  exit;
}

$allowed = ['image/jpeg','image/png','image/webp','image/gif'];
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($_FILES['cv']['tmp_name']);
if (!in_array($mime, $allowed, true)) {
  http_response_code(415);
  echo json_encode(['ok'=>false,'error'=>'Định dạng không hỗ trợ (jpg, png, webp, gif)'], JSON_UNESCAPED_UNICODE);
  exit;
}

$targetDir = __DIR__ . '/../uploads/cv';
if (!is_dir($targetDir)) {
  if (!mkdir($targetDir, 0775, true)) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Không tạo được thư mục lưu'], JSON_UNESCAPED_UNICODE);
    exit;
  }
}

$ext = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));
if (!$ext) {
  $ext = match($mime) {'image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif', default=>'bin'};
}
$basename = 'cv_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$dest = $targetDir . DIRECTORY_SEPARATOR . $basename;

if (!move_uploaded_file($_FILES['cv']['tmp_name'], $dest)) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Lưu file thất bại'], JSON_UNESCAPED_UNICODE);
  exit;
}

$relPath = 'uploads/cv/' . $basename; // đường dẫn TƯƠNG ĐỐI từ /interface
echo json_encode(['ok'=>true, 'data'=>['fileName'=>$basename,'relPath'=>$relPath]], JSON_UNESCAPED_UNICODE);
