<?php
/**
 * UI_AD/guard.php
 * - Bảo vệ tất cả .html trong thư mục qua .htaccess rewrite.
 * - Cung cấp hàm guard_require() để PHP khác có thể include nếu cần.
 */

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
if (!session_id()) {
  if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
  }
  session_start();
}

/** Kiểm tra đăng nhập admin */
function guard_is_admin_logged_in(): bool {
  if (!empty($_SESSION['admin']['MaQTV'])) return true;
  if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) return true;
  return false;
}

/** Cho phép PHP khác require và chặn truy cập khi chưa login */
function guard_require(string $loginPage = 'UI_DangNhap_Admin.html'): void {
  if (!guard_is_admin_logged_in()) {
    header('Location: ' . $loginPage);
    exit;
  }
}

/** Hợp lệ hoá đường dẫn .html tương đối */
function guard_sanitize_html_path(?string $req): string {
  $req = $req ?? '';
  if (!preg_match('/^[A-Za-z0-9_\-\/]+\.html$/', $req)) {
    http_response_code(400);
    echo 'Bad request';
    exit;
  }
  return $req;
}

/** Entry khi được rewrite từ .htaccess: kiểm tra session + xuất file HTML */
function guard_serve_html_via_rewrite(): void {
  $req = isset($_GET['file']) ? $_GET['file'] : '';
  $req = guard_sanitize_html_path($req);

  $fullPath  = __DIR__ . DIRECTORY_SEPARATOR . $req;
  $loginPage = 'UI_DangNhap_Admin.html';

  // Chưa login và không phải trang login -> về login
  if (!guard_is_admin_logged_in() && basename($fullPath) !== $loginPage) {
    header('Location: ' . $loginPage);
    exit;
  }

  if (!is_file($fullPath)) {
    http_response_code(404);
    echo 'Not found';
    exit;
  }

  header('Content-Type: text/html; charset=utf-8');
  readfile($fullPath);
}

// Nếu có tham số ?file=... → đang được gọi bởi rewrite → phục vụ HTML
if (isset($_GET['file'])) {
  guard_serve_html_via_rewrite();
}
