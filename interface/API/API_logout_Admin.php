<?php
// interface\API\API_logout_Admin.php
// Hủy session admin và đưa về trang đăng nhập (kèm flag logged_out=1)

session_start();

// Xóa toàn bộ biến session
$_SESSION = [];

// Hủy cookie phiên nếu có
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000,
    $params["path"], $params["domain"],
    $params["secure"], $params["httponly"]
  );
}

// Hủy session
session_destroy();

// Điều hướng về trang đăng nhập giao diện (kèm cờ để JS xóa localStorage)
header('Location: /Duantuyendung/interface/build/pages/UI_AD/UI_DangNhap_Admin.html?logged_out=1');
exit;
