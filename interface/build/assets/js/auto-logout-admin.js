(function () {
  const LOGIN_URL =
    "/Duantuyendung/interface/build/pages/UI_AD/UI_DangNhap_Admin.html";
  const API_LOGOUT = "/Duantuyendung/interface/API/API_logout_Admin.php";

  function clearClient() {
    try {
      localStorage.removeItem("admin_logged_in");
      localStorage.removeItem("admin_email");
      sessionStorage.removeItem("navInApp");
      sessionStorage.removeItem("skipCloseLogoutOnce");
    } catch {}
  }
  function broadcast() {
    try {
      localStorage.setItem("forceLogoutAdmin", String(Date.now()));
    } catch {}
  }
  function sendBeacon(reason) {
    try {
      const fd = new FormData();
      fd.append("reason", reason || "window_close");
      if (navigator.sendBeacon) navigator.sendBeacon(API_LOGOUT, fd);
      else
        fetch(API_LOGOUT, { method: "POST", body: fd, keepalive: true }).catch(
          () => {}
        );
    } catch {}
  }
  function hardLogout(reason) {
    clearClient();
    broadcast();
    sendBeacon(reason);
  }

  window.addEventListener("storage", (e) => {
    if (e.key === "forceLogoutAdmin" && e.newValue) {
      clearClient();
      if (!/\/UI_DangNhap_Admin\.html$/.test(location.pathname)) {
        location.replace(`${LOGIN_URL}?logged_out=1`);
      }
    }
  });

  document.addEventListener(
    "click",
    (e) => {
      const a = e.target.closest && e.target.closest("a[href]");
      if (a && a.target !== "_blank") sessionStorage.setItem("navInApp", "1");
    },
    true
  );

  try {
    const nav = performance.getEntriesByType("navigation")[0];
    if (nav && nav.type === "reload") {
      sessionStorage.setItem("navInApp", "1");
      sessionStorage.setItem("skipCloseLogoutOnce", "1");
    }
  } catch {}

  window.addEventListener("pagehide", () => {
    const byNav = sessionStorage.getItem("navInApp") === "1";
    const skipped = sessionStorage.getItem("skipCloseLogoutOnce") === "1";
    sessionStorage.removeItem("navInApp");
    sessionStorage.removeItem("skipCloseLogoutOnce");
    if (!byNav && !skipped) hardLogout("pagehide_close");
  });

  (function () {
    const p = new URLSearchParams(location.search);
    if (p.get("logged_out") === "1") {
      clearClient();
      if (history.replaceState) history.replaceState({}, "", location.pathname);
    }
  })();
})();
