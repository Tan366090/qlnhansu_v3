<?php include 'headers.php'; ?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' https:; img-src 'self' data: https:; font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com https://fonts.googleapis.com https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/; style-src 'self' 'unsafe-inline' https:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https: https://cdn.jsdelivr.net https://code.jquery.com; connect-src 'self' https:;">
    <meta name="theme-color" content="#ffffff" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    
    <title>Admin Dashboard - Quản trị hệ thống</title>

    <!-- CSS -->
    <!-- <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css"> -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/notifications.css">
    <link rel="stylesheet" href="../assets/css/loading.css">
    <link rel="stylesheet" href="css/admin-dashboard.css">
    <link rel="stylesheet" href="css/libs/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/libs/roboto.css">


    <!-- Thêm Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Thêm ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <!-- Thêm SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- JavaScript -->
    <script src="js/libs/jquery-3.7.1.min.js"></script>
    <script src="js/libs/bootstrap.bundle.min.js"></script>
    <style>
        .stat-info h3 {
    color: #222 !important;
    font-weight: bold;
    font-family: 'Roboto', Arial, sans-serif;
    letter-spacing: -0.5px;
}
    </style>
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar" role="complementary">
        <div class="sidebar-header">
            <div class="user-info">
                <div class="user-avatar">
                    <img src="male.png" alt="User Avatar" class="rounded-circle" width="40" height="40">
                </div>
                <div class="user-details">
                    <h2 class="user-name">VNPT</h2>
                    <span class="user-role">Administrator</span>
                </div>
            </div>
        </div>

        <nav role="navigation">
            <ul class="nav-list">
                <li class="nav-item active" data-menu-id="dashboard">
                    <a href="dashboard_admin_V1.php" class="nav-link">
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item" data-menu-id="employees">
                    <a href="employees/NhanVien_List.html" class="nav-link">
                        <span>Nhân viên</span>
                    </a>
                </li>
                <li class="nav-item" data-menu-id="attendance">
                    <a href="pages/attendance.php" class="nav-link">
                        <span>Chấm công</span>
                    </a>
                </li>
                <li class="nav-item" data-menu-id="payroll">
                    <a href="payroll/payroll_List.html" class="nav-link">
                        <span>Lương thưởng</span>
                    </a>
                </li>
                <li class="nav-item" data-menu-id="departments">
                    <a href="departments/departments.html" class="nav-link">
                        <span>Phòng ban</span>
                    </a>
                </li>
                <li class="nav-item" data-menu-id="certificates">
                    <a href="degrees/degrees_list.html" class="nav-link">
                        <span>Bằng cấp</span>
                    </a>
                </li>
                <li class="nav-item" data-menu-id="leave">
                    <a href="leave/leave_list.php" class="nav-link">
                        <span>Nghỉ phép</span>
                    </a>
                </li>
                <li class="nav-item" data-menu-id="logout">
                    <a href="logout.php" class="nav-link">
                        <span>Đăng xuất</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>
 
   <div style ="background-color: #f8f6f4;"class="wrapper">
        <!-- Header -->
        <header class="header">
            <div class="header-center">
                <input type="text" class="form-control search-box" placeholder="Tìm kiếm...">
            </div>
            <div class="header-right">
                <button class="btn btn-link notification-bell" type="button" id="notificationsDropdown" data-bs-toggle="dropdown">
                    <i class="fas fa-bell"></i>
                    <span class="badge">3</span>
                </button>
                <button class="btn btn-link chat-bell" type="button" id="chatButton">
                    <img src="chat.png" alt="Chat" style="width:28px;height:28px;object-fit:cover;vertical-align:middle;">
                </button>
                <div class="dropdown d-inline-block">
                    <button class="btn btn-link p-0" type="button" id="userDropdown" data-bs-toggle="dropdown">
                        <img src="male.png" alt="User" class="rounded-circle" width="32" height="32">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user"></i> Hồ sơ</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog"></i> Cài đặt</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                    </ul>
                </div>
                <select id="languageSwitch" class="form-select form-select-sm ms-2">
                    <option value="vi">Tiếng Việt</option>
                    <option value="en">English</option>
                </select>
                <button id="darkModeToggle" class="btn ms-2" aria-label="Toggle Dark Mode">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
           
        </header>

        <!-- Main Content -->
        <main style ="background-color: #f8f6f4;" class="main-content" id="mainContent" role="main">
            <!-- Statistics Cards -->
            <div class="dashboard-stats-grid">
                <div class="dashboard-stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Tổng số nhân viên</h3>
                        <p class="stat-number" id="totalEmployees">0</p>
                        <p class="stat-change positive">+5% so với tháng trước</p>
                    </div>
                </div>
                <div class="dashboard-stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-business-time"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Đi làm đúng giờ</h3>
                        <p class="stat-number" id="onTimePercentage">0%</p>
                        <p class="stat-change positive">+2% so với tuần trước</p>
                    </div>
                </div>
                <div class="dashboard-stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Có mặt hôm nay</h3>
                        <p class="stat-number" id="presentToday">0</p>
                        <p class="stat-change">So với hôm qua</p>
                    </div>
                </div>
                <div class="dashboard-stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-user-times"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Vắng mặt hôm nay</h3>
                        <p class="stat-number" id="absentToday">0</p>
                        <p class="stat-change negative">+3% so với hôm qua</p>
                    </div>
                </div>
            </div>

           
        </main>
    </div>
</div>

<!-- Chat Modal -->
<div class="modal fade" id="chatModal" tabindex="-1" aria-labelledby="chatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 1200px; margin: 1.75rem auto; max-height: 98vh;">
        <div class="modal-content" style="background: transparent; border: none; box-shadow: none;">
            <div class="modal-body p-0" style="background: transparent;">
                <iframe src="chat_widget.php" style="width: 100%; height: 800px; max-height: 90vh; border: none; border-radius: 16px; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Overlay for chat modal -->
<div id="chatOverlay" style="display:none;"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu functionality
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (menuToggle && sidebar && overlay) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.classList.toggle('sidebar-open');
        });
        
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        });
    }

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    if (tooltipTriggerList.length > 0) {
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Chat functionality
    const chatButton = document.getElementById('chatButton');
    const chatModal = new bootstrap.Modal(document.getElementById('chatModal'));
    const chatOverlay = document.getElementById('chatOverlay');
    
    chatButton.addEventListener('click', function() {
        chatModal.show();
        chatOverlay.style.display = 'block';
    });

    // Khi modal đóng (bằng nút X hoặc sự kiện khác)
    document.getElementById('chatModal').addEventListener('hidden.bs.modal', function () {
        chatOverlay.style.display = 'none';
    });

    // Listen for close message from iframe
    window.addEventListener('message', function(event) {
        if(event.data === 'closeChatModal') {
            chatModal.hide();
            chatOverlay.style.display = 'none';
        }
    });
});
</script>

</body>
</html>