<script>
(function () {
  // ===== CẤU HÌNH =====
  const LOGIN_URL = "/Duantuyendung/interface/build/pages/UI_SignUp_TD-UV.html";
  const API_LOGOUT = "/Duantuyendung/interface/API/API_logout_User.php";

  // Xoá thông tin phía client
  function clearClientAuth() {
    try {
      localStorage.removeItem("auth");
      sessionStorage.removeItem("navInApp");
      sessionStorage.removeItem("skipCloseLogoutOnce");
    } catch {}
  }

  // Phát tín hiệu logout cho các tab khác
  function broadcastForceLogout() {
    try {
      localStorage.setItem("forceLogout", String(Date.now()));
    } catch {}
  }

  // Gọi logout server bằng beacon (phù hợp khi tab sắp đóng)
  function sendServerLogout(reason) {
    try {
      const data = new FormData();
      data.append("reason", reason || "window_close");
      if (navigator.sendBeacon) {
        navigator.sendBeacon(API_LOGOUT, data);
      } else {
        // fallback nhẹ, không chặn đóng tab
        fetch(API_LOGOUT, { method: "POST", body: data, keepalive: true }).catch(() => {});
      }
    } catch {}
  }

  // Thực thi logout đầy đủ
  function hardLogout(reason) {
    clearClientAuth();
    broadcastForceLogout();
    sendServerLogout(reason);
  }

  // Nhận tín hiệu logout từ tab khác
  window.addEventListener("storage", (e) => {
    if (e.key === "forceLogout" && e.newValue) {
      clearClientAuth();
      // Điều hướng về login nếu chưa ở đó
      const atLogin = /\/UI_SignUp_TD-UV\.html$/.test(location.pathname);
      if (!atLogin) location.replace(`${LOGIN_URL}?logged_out=1`);
    }
  });

  // Đánh dấu điều hướng nội bộ để KHÔNG auto-logout
  document.addEventListener("click", (e) => {
    const a = e.target.closest && e.target.closest("a[href]");
    if (a && a.target !== "_blank") {
      sessionStorage.setItem("navInApp", "1");
    }
  }, true);
  document.addEventListener("submit", () => {
    sessionStorage.setItem("navInApp", "1");
  }, true);

  // Nếu là reload (F5), coi như điều hướng nội bộ để không logout
  try {
    const nav = performance.getEntriesByType("navigation")[0];
    if (nav && nav.type === "reload") {
      sessionStorage.setItem("navInApp", "1");
      sessionStorage.setItem("skipCloseLogoutOnce", "1");
    }
  } catch {}

  // pagehide chạy khi đóng tab / chuyển trang
  window.addEventListener("pagehide", function () {
    const byNav = sessionStorage.getItem("navInApp") === "1";
    const skipped = sessionStorage.getItem("skipCloseLogoutOnce") === "1";
    sessionStorage.removeItem("navInApp");
    sessionStorage.removeItem("skipCloseLogoutOnce");
    if (!byNav && !skipped) {
      // Xem như đóng tab / đóng trình duyệt
      hardLogout("pagehide_close");
      // Không redirect tại đây (tránh chặn đóng tab)
    }
  });

  // Nếu quay lại login với ?logged_out=1 → clear sạch
  (function handleLoggedOutQuery() {
    const p = new URLSearchParams(location.search);
    if (p.get("logged_out") === "1") {
      clearClientAuth();
      if (history.replaceState) {
        const clean = location.pathname + location.hash;
        history.replaceState({}, "", clean);
      }
    }
  })();
})();
</script>
