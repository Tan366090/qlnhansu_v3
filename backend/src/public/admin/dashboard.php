<?php
// Bật hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../backend/src/config/SessionManager.php';
require_once __DIR__ . '/../../backend/src/config/Database.php';

// Initialize SessionManager
$sessionManager = \App\Config\SessionManager::getInstance();
$sessionManager->init();

// Check authentication
if (!$sessionManager->isAuthenticated() || $sessionManager->get('role') !== 'admin') {
    header('Location: /QLNhanSu_version1/public/login_new.html');
    exit;
}

// Get user data
$user = $sessionManager->getCurrentUser();
$username = $user['username'];
$role = $user['role'];

// Kiểm tra session định kỳ
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 86400)) {
    // Nếu session quá 24 giờ, đăng xuất
    session_unset();
    session_destroy();
    header('Location: /QLNhanSu_version1/public/login_new.html');
    exit;
}

// Cập nhật thời gian hoạt động cuối cùng
$_SESSION['last_activity'] = time();

// Test database connection
try {
    $db = new \App\Config\Database();
    $conn = $db->getConnection();
    echo "<p>Database Connection: Success</p>";
} catch(PDOException $e) {
    echo "<p>Database Connection: Failed - " . $e->getMessage() . "</p>";
}
?>
<!DOCTYPE html>
<html lang="vi" spellcheck="false">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta
            http-equiv="Content-Security-Policy"
            content="
            default-src 'self' http://localhost:* http://127.0.0.1:*;
            script-src 'self' 'unsafe-inline' 'unsafe-eval' https://code.jquery.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;
            style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;
            img-src 'self' data: https://unpkg.com;
            font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com;
            connect-src 'self' http://localhost:* http://127.0.0.1:* https://cdn.jsdelivr.net ws://localhost:8080 ws://127.0.0.1:8080;
            worker-src 'self' blob:;
            frame-src 'self';
            object-src 'none';"
        />
        <title>Admin Dashboard - Hệ thống Quản lý Nhân sự</title>

        <!-- External CSS -->
        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        />
        <link
            href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css"
            rel="stylesheet"
        />
        <link
            href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
            rel="stylesheet"
        />
        <link rel="stylesheet" href="./css/admin-dashboard.css" />
        <link rel="stylesheet" href="./css/dashboard-realtime.css" />
        <link rel="stylesheet" href="./assets/css/icons.css" />
        <link rel="stylesheet" href="./css/api_test.css" />
        <script type="module" src="./js/api_test.js"></script>
        <!-- JavaScript -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

        <!-- Main Dashboard Scripts -->
        <script type="module" src="./js/init.js"></script>
        <script type="module" src="/QLNhanSu_version1/public/shared/js/common.js"></script>
        <script type="module" src="/QLNhanSu_version1/public/shared/js/auth_utils.js"></script>
        <script type="module" src="/QLNhanSu_version1/public/shared/js/permission.js"></script>
        <script type="module" src="/QLNhanSu_version1/public/shared/js/notification.js"></script>
        <script type="module" src="/QLNhanSu_version1/public/shared/js/utils.js"></script>
        <script type="module" src="./js/dashboard.js"></script>
        <script type="module" src="./js/tab.js"></script>
        <script src="./js/menu-search.js"></script>
        <script src="./js/recent-menu.js"></script>
        <script src="./js/global-search.js"></script>
        <script src="./js/user-profile.js"></script>
        <script src="./js/export-data.js"></script>
        <script src="./js/ai-analysis.js"></script>
        <script src="./js/gamification.js"></script>
        <script src="./js/mobile-stats.js"></script>
        <script src="./js/activity-filter.js"></script>
        <script src="./js/notification-handler.js"></script>
        <script src="./js/loading-overlay.js"></script>
        <script src="./js/dark-mode.js"></script>
        <script src="./js/error-logger.js"></script>
        <script src="./js/api_test.js"></script>
      
        <!-- Session Check -->
        <script>
            // Kiểm tra session định kỳ
            setInterval(function() {
                fetch('/QLNhanSu_version1/backend/src/api/auth/check.php', {
                    method: 'GET',
                    credentials: 'include'
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.authenticated) {
                        window.location.href = '/QLNhanSu_version1/public/login_new.html';
                    }
                })
                .catch(error => {
                    console.error('Lỗi kiểm tra session:', error);
                });
            }, 300000); // 5 phút kiểm tra một lần

            // Hàm đăng xuất
            async function logout() {
                try {
                    const response = await fetch('/QLNhanSu_version1/backend/src/api/auth/logout.php', {
                        method: 'POST',
                        credentials: 'include'
                    });

                    if (response.ok) {
                        window.location.href = '/QLNhanSu_version1/public/login_new.html';
                    }
                } catch (error) {
                    console.error('Lỗi đăng xuất:', error);
                }
            }

            // Kiểm tra session khi trang được tải
            document.addEventListener('DOMContentLoaded', function() {
                fetch('/QLNhanSu_version1/backend/src/api/auth/check.php', {
                    method: 'GET',
                    credentials: 'include'
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.authenticated) {
                        window.location.href = '/QLNhanSu_version1/public/login_new.html';
                    }
                })
                .catch(error => {
                    console.error('Lỗi kiểm tra session:', error);
                });
            });
        </script>
    </head>
    <body>
        <?php
        // Test PHP code
        echo "<div class='php-test'>";
        echo "<h2>PHP Test Results:</h2>";
        
        // Test PHP version
        echo "<p>PHP Version: " . phpversion() . "</p>";
        
        // Test session
        if(isset($_SESSION['test'])) {
            echo "<p>Session: Active</p>";
        } else {
            $_SESSION['test'] = 'test_value';
            echo "<p>Session: Created</p>";
        }
        
        // Test file permissions
        $file = __FILE__;
        if(is_writable($file)) {
            echo "<p>File Permissions: Writable</p>";
        } else {
            echo "<p>File Permissions: Read-only</p>";
        }
        
        echo "</div>";
        ?>

        <!-- Add notification container -->
        <div class="notification-container" id="notificationContainer"></div>

        <!-- Add loading overlay -->
        <div class="loading-overlay" id="loadingOverlay" style="display: none">
            <div class="text-center">
                <div class="loading-spinner"></div>
                <div class="loading-text">Đang tải...</div>
            </div>
        </div>

        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <div class="dashboard-container">
            <!-- Sidebar -->
            <aside class="sidebar">
                <div class="sidebar-header">
                    <img src="male.png" alt="User Avatar" />
                    <h2>VNPT</h2>
                </div>

                <!-- Menu Search -->
                <div class="menu-search">
                    <input type="text" placeholder="Tìm kiếm menu..." />
                </div>

                <nav>
                    <ul class="nav-menu">
                        <li class="nav-item" data-menu-id="dashboard">
                            <a href="dashboard.html" class="nav-link active">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Dashboard</span>
                                <i class="fas fa-star favorite"></i>
                            </a>
                        </li>
                        <li
                            class="nav-item has-submenu"
                            data-menu-id="employees"
                        >
                            <a href="#" class="nav-link">
                                <i class="fas fa-users"></i>
                                <span>Nhân viên</span>
                                <i class="fas fa-chevron-right"></i>
                                <i class="fas fa-star favorite"></i>
                            </a>
                            <ul class="submenu">
                                <li>
                                    <a
                                        href="employees/list.html"
                                        class="nav-link"
                                    >
                                        <i class="fas fa-list"></i>
                                        <span>Danh sách nhân viên</span>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="employees/add.html"
                                        class="nav-link"
                                    >
                                        <i class="fas fa-plus"></i>
                                        <span>Thêm nhân viên</span>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="employees/edit.html"
                                        class="nav-link"
                                    >
                                        <i class="fas fa-edit"></i>
                                        <span>Chỉnh sửa hồ sơ</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item has-submenu">
                            <a href="#" class="nav-link">
                                <i class="fas fa-clock"></i>
                                <span>Chấm công</span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                            <ul class="submenu">
                                <li>
                                    <a
                                        href="attendance/history.html"
                                        class="nav-link"
                                    >
                                        <i class="fas fa-history"></i>
                                        <span>Lịch sử chấm công</span>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="attendance/check.html"
                                        class="nav-link"
                                    >
                                        <i class="fas fa-check"></i>
                                        <span>Chấm công</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item has-submenu">
                            <a href="#" class="nav-link">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>Lương</span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                            <ul class="submenu">
                                <li>
                                    <a href="salary/list.html" class="nav-link">
                                        <i class="fas fa-list"></i>
                                        <span>Bảng lương</span>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="salary/history.html"
                                        class="nav-link"
                                    >
                                        <i class="fas fa-history"></i>
                                        <span>Lịch sử lương</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item has-submenu">
                            <a href="#" class="nav-link">
                                <i class="fas fa-building"></i>
                                <span>Phòng ban</span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                            <ul class="submenu">
                                <li>
                                    <a
                                        href="departments/list.html"
                                        class="nav-link"
                                    >
                                        <i class="fas fa-list"></i>
                                        <span>Danh sách phòng ban</span>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="positions/list.html"
                                        class="nav-link"
                                    >
                                        <i class="fas fa-briefcase"></i>
                                        <span>Vị trí công việc</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item has-submenu">
                            <a href="#" class="nav-link">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Nghỉ phép</span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                            <ul class="submenu">
                                <li>
                                    <a
                                        href="leave/register.html"
                                        class="nav-link"
                                    >
                                        <i class="fas fa-plus"></i>
                                        <span>Đăng ký nghỉ phép</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="leave/list.html" class="nav-link">
                                        <i class="fas fa-list"></i>
                                        <span>Danh sách nghỉ phép</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item has-submenu">
                            <a href="#" class="nav-link">
                                <i class="fas fa-graduation-cap"></i>
                                <span>Đào tạo</span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                            <ul class="submenu">
                                <li>
                                    <a
                                        href="training/courses.html"
                                        class="nav-link"
                                    >
                                        <i class="fas fa-list"></i>
                                        <span>Khóa đào tạo</span>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="training/register.html"
                                        class="nav-link"
                                    >
                                        <i class="fas fa-plus"></i>
                                        <span>Đăng ký đào tạo</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item has-submenu">
                            <a href="#" class="nav-link">
                                <i class="fas fa-certificate"></i>
                                <span>Bằng cấp</span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                            <ul class="submenu">
                                <li>
                                    <a
                                        href="certificates/list.html"
                                        class="nav-link"
                                    >
                                        <i class="fas fa-list"></i>
                                        <span>Danh sách bằng cấp</span>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="certificates/add.html"
                                        class="nav-link"
                                    >
                                        <i class="fas fa-plus"></i>
                                        <span>Thêm bằng cấp</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item has-submenu">
                            <a href="#" class="nav-link">
                                <i class="fas fa-file-alt"></i>
                                <span>Tài liệu</span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                            <ul class="submenu">
                                <li>
                                    <a
                                        href="documents/list.html"
                                        class="nav-link"
                                    >
                                        <i class="fas fa-list"></i>
                                        <span>Danh sách tài liệu</span>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="documents/upload.html"
                                        class="nav-link"
                                    >
                                        <i class="fas fa-upload"></i>
                                        <span>Upload tài liệu</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item has-submenu">
                            <a href="#" class="nav-link">
                                <i class="fas fa-tools"></i>
                                <span>Thiết bị</span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                            <ul class="submenu">
                                <li>
                                    <a
                                        href="equipment/list.html"
                                        class="nav-link"
                                    >
                                        <i class="fas fa-list"></i>
                                        <span>Danh sách thiết bị</span>
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="equipment/assign.html"
                                        class="nav-link"
                                    >
                                        <i class="fas fa-share"></i>
                                        <span>Cấp phát thiết bị</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a href="report.html" class="nav-link">
                                <i class="fas fa-chart-bar"></i>
                                <span>Báo cáo</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link" id="logoutBtn">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Đăng xuất</span>
                            </a>
                        </li>
                    </ul>
                </nav>

                <!-- Recent Menu -->
                <div class="recent-menu">
                    <h3>Menu gần đây</h3>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Improved Header -->
                <header class="header">
                    <div class="header-left">
                        <h1>Admin Dashboard</h1>
                    </div>
                    <div class="header-right">
                        <div class="header-controls">
                            <select id="languageSwitch" class="form-select">
                                <option value="vi">Tiếng Việt</option>
                                <option value="en">English</option>
                            </select>
                            <button id="darkModeToggle" class="btn">
                                <i class="fas fa-moon"></i>
                            </button>
                        </div>
                    </div>
                </header>
                
                
                
                <!-- Priority Metrics Grid -->
                <section class="metrics-grid">
                    <!-- Priority 1 Metrics -->
                    <div class="metric-card priority-1">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value" id="totalEmployees">0</div>
                            <div class="stat-label">Tổng nhân viên</div>
                        </div>
                    </div>
                    <div class="metric-card priority-1">
                        <div class="stat-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value" id="activeEmployees">0</div>
                            <div class="stat-label">Nhân viên đang hoạt động</div>
                        </div>
                    </div>
                    <div class="metric-card priority-1">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value" id="todayAttendance">0%</div>
                            <div class="stat-label">Tỷ lệ chấm công hôm nay</div>
                        </div>
                    </div>
                    
                    <!-- Priority 2 Metrics -->
                    <div class="metric-card priority-2">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value" id="pendingLeaves">0</div>
                            <div class="stat-label">Đơn xin nghỉ phép chờ duyệt</div>
                        </div>
                    </div>
                    <div class="metric-card priority-2">
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value" id="totalSalary">0</div>
                            <div class="stat-label">Tổng quỹ lương tháng</div>
                        </div>
                    </div>
                    <div class="metric-card priority-2">
                        <div class="stat-icon">
                            <i class="fas fa-user-times"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value" id="inactiveEmployees">0</div>
                            <div class="stat-label">Nhân viên không hoạt động</div>
                        </div>
                    </div>
                </section>
                <section class="data-tabs">
                    <div class="tab-content" id="recentTabsContent">
                        <div class="tab-pane fade show active" id="employees" role="tabpanel">
                            <div class="data-section">
                                <div class="section-header">
                                    <h3 class="section-title">Nhân viên mới</h3>
                                    <div class="action-buttons">
                                        <button class="btn btn-primary" onclick="window.location.href='./employees/list.html'">
                                            Xem tất cả
                                        </button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>Mã NV</th>
                                                <th>Họ tên</th>
                                                <th>Chức vụ</th>
                                                <th>Phòng ban</th>
                                                <th>Ngày vào làm</th>
                                                <th>Ngày sinh</th>
                                                <th>SĐT</th>
                                                <th>Email</th>
                                                <th>Địa chỉ</th>
                                                <th>Trạng thái  </th>
                                                <th>Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody id="recentEmployees">
                                            <!-- Data will be loaded dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="activities" role="tabpanel">
                            <div class="data-section">
                                <div class="section-header">
                                    <h3 class="section-title">Hoạt động gần đây</h3>
                                    <div class="activity-filter">
                                        <select id="activityType" class="form-select">
                                            <option value="all">Tất cả hoạt động</option>
                                            <option value="login">Đăng nhập</option>
                                            <option value="edit">Chỉnh sửa</option>
                                            <option value="view">Xem thông tin</option>
                                            <option value="delete">Xóa</option>
                                        </select>
                                        <input type="date" id="activityDate" class="form-control">
                                        <input type="text" class="form-control" placeholder="Tìm kiếm người dùng...">
                                    </div>
                                </div>
                                <div id="recentActivities">
                                    <!-- Data will be loaded dynamically -->
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- Charts Section -->
                <section class="charts-section">
                    <div class="row">
                        <div class="col-lg-8 mb-4">
                            <div class="data-section main-chart">
                                <div class="section-header">
                                    <h3 class="section-title">Xu hướng chấm công</h3>
                                    <div class="chart-controls">
                                        <select class="form-select" id="attendancePeriod">
                                            <option value="week">Tuần</option>
                                            <option value="month">Tháng</option>
                                            <option value="quarter">Quý</option>
                                        </select>
                                        <button class="btn" onclick="ExportUtils.exportChart('attendanceChart')">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="chart-container">
                                    <canvas id="attendanceChart"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 mb-4">
                            <div class="data-section">
                                <div class="section-header">
                                    <h3 class="section-title">Phân bố nhân viên theo phòng ban</h3>
                                    <div class="chart-controls">
                                        <button class="btn" onclick="ExportUtils.exportChart('departmentChart')">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="chart-container">
                                    <canvas id="departmentChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                
                <!-- Tabs Section -->
                
                
                <!-- Collapsible Sections -->
                <section class="additional-sections">
                    <div class="accordion" id="additionalSectionsAccordion">
                        <!-- AI Predictions Section -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="aiSectionHeader">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#aiSection">
                                    Dự đoán và Phân tích
                                </button>
                            </h2>
                            <div id="aiSection" class="accordion-collapse collapse show">
                                <div class="accordion-body">
                                    <div class="data-section">
                                        <div class="section-header">
                                            <h3 class="section-title">Dự đoán và Phân tích</h3>
                                        </div>
                                        <div class="ai-prediction-card">
                                            <h4>Dự đoán xu hướng nhân sự</h4>
                                            <div id="hrTrends"></div>
                                        </div>
                                        <div class="ai-prediction-card">
                                            <h4>Phân tích tâm lý nhân viên</h4>
                                            <div id="sentimentAnalysis"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Gamification Section -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="gamificationSectionHeader">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#gamificationSection">
                                    Gamification
                                </button>
                            </h2>
                            <div id="gamificationSection" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <div class="data-section">
                                        <div class="section-header">
                                            <h3 class="section-title">Gamification</h3>
                                        </div>
                                        <div class="leaderboard">
                                            <h4>Bảng xếp hạng</h4>
                                            <div id="employeeRankings"></div>
                                        </div>
                                        <div class="achievements">
                                            <h4>Thành tích</h4>
                                            <div id="employeeAchievements"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                
                <footer class="footer">
                    <p>&copy; 2023 VNPT. All rights reserved.</p>
                </footer>
            </main>
        </div>
    </body>
</html>
