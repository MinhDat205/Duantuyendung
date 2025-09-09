// interface/build/assets/js/config.js
// Cấu hình chung cho toàn bộ frontend

export const BASE_URL = "http://localhost:8888/Duantuyendung/";
export const API_BASE = "http://localhost:8888/Duantuyendung/interface/API/";

// Các endpoint API chuẩn
export const API_ENDPOINTS = {
  // Auth
  LOGIN: "auth_login.php",
  REGISTER: "auth_register.php",

  // Ứng viên
  SHOW_UNGVIEN: "API_show_UngVien.php",

  // Tin tuyển dụng
  SHOW_TINTUYENDUNG: "API_show_TinTuyenDung.php",

  // Nhà tuyển dụng
  SHOW_NHATUYENDUNG: "API_show_Nhatuyendung.php",

  // Quản lý
  SHOW_TAIKHOAN: "API_show_Taikhoan.php",
  SHOW_UNGVIEN: "API_show_Ungvien.php",
  SHOW_UNGVIEN_BY_UV: "API_show_Ungtuyen_byUV.php",
  SHOW_UNGVIEN_ALL: "API_show_Ungtuyen.php",
};

// Helper function để tạo URL API
export function getApiUrl(endpoint) {
  return API_BASE + endpoint;
}

// Helper function để tạo URL trang
export function getPageUrl(path) {
  return BASE_URL + path;
}