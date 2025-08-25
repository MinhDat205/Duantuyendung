<?php
// interface/API/utils.php
function json_ok($data = [], $status = 200) {
  http_response_code($status);
  echo json_encode(['ok'=>true, 'data'=>$data], JSON_UNESCAPED_UNICODE);
  exit;
}

function json_error($message, $status = 400, $extra = []) {
  http_response_code($status);
  echo json_encode(['ok'=>false, 'error'=>$message, 'extra'=>$extra], JSON_UNESCAPED_UNICODE);
  exit;
}

function require_fields($arr, $fields) {
  foreach ($fields as $f) {
    if (!isset($arr[$f]) || $arr[$f] === '') {
      json_error("Thiếu trường: $f", 422);
    }
  }
}
