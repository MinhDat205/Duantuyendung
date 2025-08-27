<?php
// interface\API\API_logout_User.php
// Hủy phiên người dùng (Ứng viên / Nhà tuyển dụng) và đưa về UI_SignUp_TD-UV.html (kèm flag logged_out=1)

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

// Điều hướng về trang đăng ký/đăng nhập (kèm cờ để JS phía UI xóa localStorage/sessionStorage)
header('Location: /Duantuyendung/interface/build/pages/UI_SignUp_TD-UV.html?logged_out=1');
exit;
