<?php
// interface/API/API_logout_User.php
// Hủy phiên người dùng (Ứng viên / Nhà tuyển dụng) và hỗ trợ sendBeacon.

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

// Nếu là POST (sendBeacon / keepalive fetch) → trả 204 No Content
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  http_response_code(204);
  exit;
}

// Còn lại (GET) → điều hướng về trang đăng nhập
header('Location: /Duantuyendung/interface/build/pages/UI_SignUp_TD-UV.html?logged_out=1');
exit;
