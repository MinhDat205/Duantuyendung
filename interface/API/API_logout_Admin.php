<?php
// /Duantuyendung/interface/API/API_logout_Admin.php

session_start();
$_SESSION = [];

if (ini_get("session.use_cookies")) {
  $p = session_get_cookie_params();
  setcookie(session_name(), '', time()-42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
}
session_destroy();

// POST (sendBeacon/keepalive) → 204 No Content
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  http_response_code(204);
  exit;
}

// GET → redirect về login kèm flag
header('Location: /Duantuyendung/interface/build/pages/UI_AD/UI_DangNhap_Admin.html?logged_out=1');
exit;
