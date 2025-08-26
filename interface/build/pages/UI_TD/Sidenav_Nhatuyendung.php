<?php
session_start();
// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'NhaTuyenDung') {
    header('Location: ../../UI_SignUp_TD-UV.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar Nhà Tuyển Dụng</title>
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <link href="../assets/css/argon-dashboard-tailwind.css" rel="stylesheet" />
    <style>
        .sidenav {
            position: fixed;`
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            z-index: 999;
            transition: all 0.3s ease;
            overflow-y: auto;
        }
        
        .sidenav-header {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .company-logo {
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
        }
        
        .company-name {
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }
        
        .company-email {
            color: rgba(255,255,255,0.8);
            font-size: 0.9rem;
        }
        
        .sidenav-menu {
            padding: 1rem 0;
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .menu-item:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: rgba(255,255,255,0.5);
        }
        
        .menu-item.active {
            background: rgba(255,255,255,0.15);
            color: white;
            border-left-color: white;
        }
        
        .menu-icon {
            width: 20px;
            margin-right: 0.75rem;
            text-align: center;
        }
        
        .menu-text {
            font-weight: 500;
        }
        
        .logout-section {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        .logout-btn {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 0.75rem;
            background: rgba(255,255,255,0.1);
            border: none;
            border-radius: 0.5rem;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .main-content {
            margin-left: 260px;
            padding: 2rem;
            min-height: 100vh;
            background: #f8f9fa;
        }
        
        .content-header {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .welcome-text {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .status-active {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .status-pending {
            background: #fef5e7;
            color: #744210;
        }
        
        .status-inactive {
            background: #fed7d7;
            color: #742a2a;
        }
        
        @media (max-width: 768px) {
            .sidenav {
                transform: translateX(-100%);
            }
            
            .sidenav.open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-toggle {
                display: block;
                position: fixed;
                top: 1rem;
                left: 1rem;
                z-index: 1000;
                background: #667eea;
                border: none;
                color: white;
                padding: 0.5rem;
                border-radius: 0.5rem;
                cursor: pointer;
            }
        }
        
        .mobile-toggle {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Mobile Toggle Button -->
    <button class="mobile-toggle" onclick="toggleSidenav()">
        <i class="ni ni-bullet-list-67"></i>
    </button>

    <!-- Sidebar -->
    <nav class="sidenav" id="sidenav">
        <div class="sidenav-header">
            <div class="company-logo">
                <i class="ni ni-building"></i>
            </div>
            <div class="company-name" id="companyName">Đang tải...</div>
            <div class="company-email" id="companyEmail">...</div>
        </div>
        
        <div class="sidenav-menu">
            <a href="UI_Nhatuyendung_Trangchu.html" class="menu-item" id="menu-dashboard">
                <div class="menu-icon">
                    <i class="ni ni-chart-bar-32"></i>
                </div>
                <div class="menu-text">Trang chủ</div>
            </a>
            
            <a href="UI_TD_QuanLyTinTuyenDung.html" class="menu-item" id="menu-tintuyendung">
                <div class="menu-icon">
                    <i class="ni ni-briefcase-24"></i>
                </div>
                <div class="menu-text">Quản lý tin tuyển dụng</div>
            </a>
            
            <a href="UI_TD_QuanLyUngVien.html" class="menu-item" id="menu-ungvien">
                <div class="menu-icon">
                    <i class="ni ni-single-02"></i>
                </div>
                <div class="menu-text">Quản lý ứng viên</div>
            </a>
            
            <a href="UI_TD_HoSo.html" class="menu-item" id="menu-hoso">
                <div class="menu-icon">
                    <i class="ni ni-folder-17"></i>
                </div>
                <div class="menu-text">Hồ sơ công ty</div>
            </a>
        </div>
        
        <div class="logout-section">
            <button class="logout-btn" onclick="logout()">
                <div class="menu-icon">
                    <i class="ni ni-button-power"></i>
                </div>
                <div class="menu-text">Đăng xuất</div>
            </button>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="main-content">
        <div class="content-header">
            <h1 class="welcome-text">Chào mừng trở lại!</h1>
            <p>Quản lý hệ thống tuyển dụng của bạn một cách hiệu quả</p>
            <div class="mt-4">
                <span class="status-badge" id="statusBadge">Đang kiểm tra...</span>
            </div>
        </div>
        
        <!-- Content sẽ được load từ các trang khác -->
        <div id="pageContent">
            <div class="text-center text-gray-500">
                <i class="ni ni-chart-bar-32" style="font-size: 3rem;"></i>
                <p class="mt-4">Chọn một mục từ menu để bắt đầu</p>
            </div>
        </div>
    </div>

    <script type="module">
        import { API_BASE, BASE_URL } from '../../assets/js/config.js';
        
        // Lấy thông tin Nhà Tuyển Dụng
        async function loadNTDInfo() {
            try {
                const response = await fetch(API_BASE + 'API_get_NTD_Info.php');
                const data = await response.json();
                
                if (data.ok) {
                    const ntd = data.data;
                    
                    // Cập nhật thông tin công ty
                    document.getElementById('companyName').textContent = ntd.TenCongTy;
                    document.getElementById('companyEmail').textContent = ntd.Email;
                    
                    // Cập nhật trạng thái
                    const statusBadge = document.getElementById('statusBadge');
                    if (ntd.TrangThai === 'HoatDong') {
                        statusBadge.textContent = 'Tài khoản đã kích hoạt';
                        statusBadge.className = 'status-badge status-active';
                    } else if (ntd.TrangThai === 'Pending') {
                        statusBadge.textContent = 'Đang chờ duyệt';
                        statusBadge.className = 'status-badge status-pending';
                    } else {
                        statusBadge.textContent = 'Tài khoản bị khóa';
                        statusBadge.className = 'status-badge status-inactive';
                    }
                    
                    // Lưu thông tin vào localStorage để sử dụng ở các trang khác
                    localStorage.setItem('ntdInfo', JSON.stringify(ntd));
                    
                } else {
                    console.error('Lỗi khi tải thông tin:', data.error);
                    // Nếu có lỗi, chuyển về trang đăng nhập
                    window.location.href = BASE_URL + 'interface/build/pages/UI_SignUp_TD-UV.html';
                }
            } catch (error) {
                console.error('Lỗi kết nối:', error);
                window.location.href = BASE_URL + 'interface/build/pages/UI_SignUp_TD-UV.html';
            }
        }

        // Đăng xuất
        function logout() {
            fetch(API_BASE + 'API_logout_NTD.php')
                .then(() => {
                    localStorage.removeItem('ntdInfo');
                    window.location.href = BASE_URL + 'interface/build/pages/UI_SignUp_TD-UV.html';
                })
                .catch(() => {
                    localStorage.removeItem('ntdInfo');
                    window.location.href = BASE_URL + 'interface/build/pages/UI_SignUp_TD-UV.html';
                });
        }

        // Toggle sidebar trên mobile
        function toggleSidenav() {
            const sidenav = document.getElementById('sidenav');
            sidenav.classList.toggle('open');
        }

        // Đánh dấu menu active dựa trên URL hiện tại
        function setActiveMenu() {
            const currentPath = window.location.pathname;
            const menuItems = document.querySelectorAll('.menu-item');
            
            menuItems.forEach(item => {
                item.classList.remove('active');
                if (currentPath.includes(item.getAttribute('href'))) {
                    item.classList.add('active');
                }
            });
        }

        // Khởi tạo
        document.addEventListener('DOMContentLoaded', function() {
            loadNTDInfo();
            setActiveMenu();
        });

        // Xử lý click menu
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function(e) {
                // Xóa active class từ tất cả menu items
                document.querySelectorAll('.menu-item').forEach(menu => {
                    menu.classList.remove('active');
                });
                
                // Thêm active class cho menu được click
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>
