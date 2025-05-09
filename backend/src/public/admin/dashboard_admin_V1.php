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

    <style>
        .sidebar {
            background-color: #E5E5E5;
        }
        .main-content {
            background-color: #F2F2F2;
        }
    </style>

    <!-- Thêm Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Thêm ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <!-- Thêm SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- JavaScript -->
    <script src="js/libs/jquery-3.7.1.min.js"></script>
    <script src="js/libs/bootstrap.bundle.min.js"></script>
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

    <div class="wrapper">
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
        <main class="main-content" id="mainContent" role="main">
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

            <!-- Charts Section + Recent Activities Row -->
            <!-- Đã xóa biểu đồ ở đây -->
            <!-- End Charts Section + Recent Activities Row -->
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts
    const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(attendanceCtx, {
        type: 'line',
        data: {
            labels: ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'],
            datasets: [{
                label: 'Đi làm đúng giờ',
                data: [65, 59, 80, 81, 56, 55, 40],
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        }
    });

    const distributionCtx = document.getElementById('employeeDistributionChart').getContext('2d');
    new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Kỹ thuật', 'Kinh doanh', 'Hành chính', 'Khác'],
            datasets: [{
                data: [30, 25, 25, 20],
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)'
                ]
            }]
        }
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Dark mode toggle
    const darkModeToggle = document.getElementById('darkModeToggle');
    darkModeToggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
    });
});
</script>
</body>
</html>