<?php
// UI_AD/auth_guard.php
session_start();

$passed = false;
if (!empty($_SESSION['admin']) && !empty($_SESSION['admin']['MaQTV'])) {
  $passed = true;
}
if (!$passed && !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
  $passed = true;
}

if (!$passed) {
  // Chưa đăng nhập -> quay lại trang login
  header('Location: UI_DangNhap_Admin.html');
  exit;
}
