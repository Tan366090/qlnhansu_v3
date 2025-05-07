<?php include 'headers.php'; ?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' https:; img-src 'self' data: https:; font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com https://fonts.googleapis.com https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/; style-src 'self' 'unsafe-inline' https:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https: https://cdn.jsdelivr.net https://code.jquery.com; connect-src 'self' https:;">
    <title>Quản lý nhân sự - Dashboard</title>

    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/font-awesome/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/notifications.css">
    <link rel="stylesheet" href="../assets/css/loading.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="css/admin-dashboard.css">
    <link rel="stylesheet" href="css/dark-mode.css">

    <!-- System Loading -->
    <!-- <div class="system-loading">
        <div class="system-loader"></div>
    </div> -->

    <!-- Thêm CSS cho hệ thống thông báo -->
    <style>
        .notification-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 25px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 450px;
            max-width: 650px;
            z-index: 9999;
            animation: slideIn 0.3s ease-in-out;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .notification-content {
            display: flex;
            align-items: center;
            gap: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex: 1;
        }

        .notification i {
            font-size: 18px;
            flex-shrink: 0;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .notification-header h4 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
        }

        .notification-time {
            font-size: 12px;
            color: #666;
        }

        .notification p {
            margin: 0;
            font-size: 14px;
            color: #333;
            line-height: 1.4;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .notification-link {
            display: inline-block;
            margin-top: 8px;
            color: #1a73e8;
            text-decoration: none;
            font-size: 12px;
        }

        .notification-link:hover {
            text-decoration: underline;
        }

        .notification.success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .notification.error {
            background-color: rgb(231, 136, 144);
            border-color: rgb(116, 7, 18);
            color: rgb(12, 7, 8);
        }

        .notification.warning {
            background-color: #fff3cd;
            border-color: #ffeeba;
            color: #856404;
        }

        .notification.info {
            background-color: #cce5ff;
            border-color: #b8daff;
            color: #004085;
        }

        .close-btn {
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            padding: 0;
            margin-left: 15px;
        }

        .close-btn:hover {
            opacity: 0.7;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    </style>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="theme-color" content="#ffffff" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <!-- Content Security Policy -->
    <meta http-equiv="Content-Security-Policy" content="default-src * 'unsafe-inline' 'unsafe-eval' data: blob:; style-src * 'unsafe-inline' https://cdn.jsdelivr.net; script-src * 'unsafe-inline' 'unsafe-eval' https://code.jquery.com; connect-src * 'unsafe-inline'; img-src * data: blob: 'unsafe-inline'; frame-src *; font-src * data: 'unsafe-inline';" />

    <title>Admin Dashboard - Quản trị hệ thống</title>

    <link rel="stylesheet" href="css/libs/bootstrap.min.css">
    <link rel="stylesheet" href="css/libs/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/libs/font-awesome.min.css">
    <link rel="stylesheet" href="css/libs/roboto.css">
    <link rel="stylesheet" href="dashboard_admin.css">
    <link rel="stylesheet" href="css/dark-mode.css">

    <script src="js/libs/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="js/libs/jquery-3.7.1.min.js"></script>

    <!-- Configuration -->
    <script src="js/config.js"></script>
    <script type="module">
        import {
            Dashboard
        } from './js/modules/dashboard.js';
        import {
            DarkMode
        } from './js/modules/dark-mode.js';

        document.addEventListener('DOMContentLoaded', () => {
            const dashboard = new Dashboard();
            dashboard.init();

            const darkMode = new DarkMode();
            darkMode.init();
        });
    </script>

    <!-- Main Dashboard Scripts -->
    <script type="module">
        import {
            WidgetManager
        } from './js/modules/widget-manager.js';
        import {
            CommonUtils,
            AuthUtils,
            PermissionUtils,
            NotificationUtils,
            UIUtils
        } from './js/modules/utils.js';
        import {
            APIUtils
        } from './js/modules/api.js';
        import {
            Dashboard
        } from './js/modules/dashboard.js';
        import {
            APITest
        } from './js/modules/test.js';
        import {
            ChartManager
        } from './js/modules/chart-manager.js';
        import {
            ThemeManager
        } from './js/modules/theme-manager.js';
        import {
            ErrorHandler
        } from './js/modules/error-handler.js';
        import {
            API
        } from './js/modules/api.js';
        import {
            GlobalSearch
        } from './js/modules/global-search.js';
        import {
            MenuSearch
        } from './js/modules/menu-search.js';
        import {
            RecentMenu
        } from './js/modules/recent-menu.js';
        import {
            UserProfile
        } from './js/modules/user-profile.js';
        import {
            ExportData
        } from './js/modules/export-data.js';
        import {
            AIAnalysis
        } from './js/modules/ai-analysis.js';
        import {
            Gamification
        } from './js/modules/gamification.js';
        import {
            MobileStats
        } from './js/modules/mobile-stats.js';
        import {
            ActivityFilter
        } from './js/modules/activity-filter.js';
        import {
            NotificationHandler
        } from './js/modules/notification-handler.js';
        import {
            LoadingOverlay
        } from './js/modules/loading-overlay.js';
        import {
            DarkMode
        } from './js/modules/dark-mode.js';
        import {
            ContentLoader
        } from './js/modules/content-loader.js';
        import {
            MenuHandler
        } from './js/modules/menu-handler.js';

        // Initialize error handler
        const errorHandler = new ErrorHandler();
        errorHandler.initialize();

        // Initialize dashboard
        const dashboard = new Dashboard();
        dashboard.init();

        // Initialize menu handler
        const menuHandler = new MenuHandler();
        menuHandler.initialize();

        // Initialize widget manager
        const widgetManager = new WidgetManager();
        widgetManager.initialize();

        // Initialize chart manager
        const chartManager = new ChartManager();
        chartManager.initialize();

        // Initialize theme manager
        const themeManager = new ThemeManager();
        themeManager.initialize();

        // Initialize global search
        const globalSearch = new GlobalSearch();
        globalSearch.initialize();

        // Xử lý sự kiện khi trang được tải
        document.addEventListener('DOMContentLoaded', async () => {
            // Kiểm tra xác thực
            if (!AuthUtils.isAuthenticated()) {
                window.location.href = '/login_new.html';
                return;
            }

            // Khởi tạo các module
            await initializeModules();

            // Thêm các event listeners
            addEventListeners();
        });

        // Khởi tạo các module
        async function initializeModules() {
            try {
                loadingOverlay.show();

                // Khởi tạo dashboard
                await dashboard.loadData();

                // Khởi tạo tìm kiếm
                globalSearch.initialize();
                menuSearch.initialize();

                // Khởi tạo menu gần đây
                recentMenu.initialize();

                // Khởi tạo thông tin người dùng
                await userProfile.loadProfile();

                // Khởi tạo phân tích AI
                await aiAnalysis.initialize();

                // Khởi tạo gamification
                // await gamification.initialize();

                // Khởi tạo thống kê mobile
                await mobileStats.initialize();

                // Khởi tạo bộ lọc hoạt động
                activityFilter.initialize();

                // Khởi tạo thông báo
                await notificationHandler.initialize();

                // Khởi tạo chế độ tối
                darkMode.initialize();

                loadingOverlay.hide();
            } catch (error) {
                console.error('Lỗi khởi tạo:', error);
                loadingOverlay.hide();
            }
        }

        // Thêm các event listeners
        function addEventListeners() {
            // Xử lý tìm kiếm toàn cục
            document.getElementById('globalSearch').addEventListener('input', (e) => {
                globalSearch.search(e.target.value);
            });

            // Xử lý tìm kiếm menu
            document.querySelector('.menu-search input').addEventListener('input', (e) => {
                menuSearch.search(e.target.value);
            });

            // Xử lý chuyển đổi chế độ tối
            document.getElementById('themeToggle').addEventListener('click', () => {
                darkMode.toggle();
            });

            // Xử lý làm mới hoạt động
            document.getElementById('refreshActivities').addEventListener('click', async () => {
                await activityFilter.refresh();
            });

            // Xử lý làm mới thông báo
            document.getElementById('refreshNotifications').addEventListener('click', async () => {
                await notificationHandler.refresh();
            });

            // Xử lý xuất dữ liệu
            document.querySelectorAll('.export-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    exportData.export(btn.dataset.type);
                });
            });

            // Xử lý cập nhật phân tích AI
            document.getElementById('updateAnalysis').addEventListener('click', async () => {
                await aiAnalysis.update();
            });

            // Xử lý cập nhật gamification
            // document.getElementById('updateGamification').addEventListener('click', async () => {
            //     await gamification.update();
            // });

            // Xử lý cập nhật thống kê mobile
            document.getElementById('updateMobileStats').addEventListener('click', async () => {
                await mobileStats.update();
            });

            // Xử lý đăng xuất
            document.getElementById('logoutBtn').addEventListener('click', () => {
                AuthUtils.logout();
            });

            // Xử lý các nút quick action
            document.querySelectorAll('.quick-action-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const action = btn.dataset.action;
                    handleQuickAction(action);
                });
            });

            // Xử lý các selector
            document.querySelectorAll('select').forEach(select => {
                select.addEventListener('change', (e) => {
                    const type = select.id;
                    handleSelectorChange(type, e.target.value);
                });
            });
        }

        // Xử lý quick action
        function handleQuickAction(action) {
            switch (action) {
                case 'add-employee':
                    window.location.href = 'employees/add.html';
                    break;
                case 'check-attendance':
                    window.location.href = 'attendance/check.html';
                    break;
                case 'register-leave':
                    window.location.href = 'leave/register.html';
                    break;
                case 'calculate-salary':
                    window.location.href = 'salary/calculate.html';
                    break;
            }
        }

        // Xử lý thay đổi selector
        function handleSelectorChange(type, value) {
            switch (type) {
                case 'performanceTimeRange':
                    dashboard.updatePerformanceChart(value);
                    break;
                case 'attendancePeriod':
                    dashboard.updateAttendanceChart(value);
                    break;
                case 'activityType':
                    activityFilter.filterByType(value);
                    break;
            }
        }

        // Xử lý sự kiện khi trang bị đóng
        window.addEventListener('beforeunload', () => {
            dashboard.cleanup();
            globalSearch.cleanup();
            menuSearch.cleanup();
            recentMenu.cleanup();
            userProfile.cleanup();
            aiAnalysis.cleanup();
            // gamification.cleanup();
            mobileStats.cleanup();
            activityFilter.cleanup();
            notificationHandler.cleanup();
            loadingOverlay.cleanup();
            darkMode.cleanup();
        });
    </script>
    <style>
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: calc(-1 * var(--sidebar-width));
                transition: left 0.3s ease;
                z-index: 1030;
                max-height: 100vh;
                overflow-y: auto;
                overflow-x: hidden;
                width: var(--sidebar-width);
                background-color: #F2F2F2;
            }

            .sidebar.active {
                left: 0;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1029;
            }

            .sidebar-overlay.active {
                display: block;
            }

            /* Mobile sidebar improvements */
            .sidebar-header {
                padding: 1rem;
                position: sticky;
                top: 0;
                background-color: #F2F2F2;
                z-index: 1;
            }

            .user-info {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .user-avatar {
                margin-bottom: 0.5rem;
            }

            .nav-list {
                padding: 0.5rem;
                margin: 0;
                list-style: none;
            }

            .nav-item {
                margin-bottom: 0.25rem;
                position: relative;
            }

            .nav-link {
                padding: 0.75rem 1rem;
                display: flex;
                align-items: center;
                color: #333;
                text-decoration: none;
                transition: background-color 0.3s;
            }

            .nav-link i {
                font-size: 1.1rem;
                margin-right: 10px;
                width: 20px;
                text-align: center;
            }

            .nav-link span {
                font-size: 0.9rem;
                flex: 1;
            }

            .submenu {
                list-style: none;
                padding-left: 2rem;
                margin: 0;
                display: none;
            }

            .nav-item.has-submenu.open .submenu {
                display: block;
            }

            .submenu .nav-link {
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
            }

            .submenu-toggle {
                display: inline-flex;
                margin-left: auto;
                transition: transform 0.3s ease;
            }

            .nav-item.has-submenu.open .submenu-toggle {
                transform: rotate(90deg);
            }
        }

        /* Header Styles */
        .header {
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            height: 60px;
            background: #fff;
            border-bottom: 1px solid #eee;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
            transition: left 0.3s ease;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 300px;
        }

        /* Header Controls Styles */
        .header-controls {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-controls .btn,
        .header-controls .form-select {
            flex-shrink: 0;
        }

        .header-controls .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 8px 12px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .header-controls .btn-warning:hover {
            background-color: #e0a800;
            border-color: #d39e00;
        }

        .header-controls .form-select {
            width: auto;
            min-width: 120px;
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #ddd;
            background-color: #fff;
            cursor: pointer;
        }

        .header-controls .btn {
            padding: 8px 12px;
            border-radius: 4px;
            border: none;
            background: transparent;
            color: #333;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .header-controls .btn:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .header-title {
                max-width: 200px;
            }

            .header-controls {
                gap: 10px;
            }
        }

        @media (max-width: 992px) {
            .header-title {
                max-width: 150px;
            }

            .header-controls .btn-warning span {
                display: none;
            }

            .header-controls .form-select {
                min-width: 100px;
            }
        }

        @media (max-width: 768px) {
            .header {
                left: 0;
                padding: 0 15px;
            }

            .header-title {
                max-width: 120px;
                font-size: 1.2rem;
            }

            .header-controls {
                gap: 8px;
            }

            .header-controls .btn-warning {
                padding: 8px;
            }

            .header-controls .form-select {
                min-width: 80px;
                padding: 8px;
            }

            .header-controls .btn {
                padding: 8px;
            }
        }

        @media (max-width: 576px) {
            .header {
                height: 50px;
                padding: 0 10px;
            }

            .header-title {
                max-width: 100px;
                font-size: 1rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .header-controls {
                gap: 5px;
                flex-wrap: nowrap;
            }

            .header-controls .btn-warning,
            .header-controls .btn {
                padding: 6px;
                font-size: 0.8rem;
            }

            .header-controls .form-select {
                min-width: 60px;
                padding: 6px;
                font-size: 0.8rem;
            }

            .header-controls .btn i {
                font-size: 0.9rem;
            }

            .header-controls .btn span {
                display: none;
            }
        }

        @media (max-width: 400px) {
            .header {
                height: 45px;
                padding: 0 5px;
            }

            .header-title {
                max-width: 80px;
                font-size: 0.9rem;
            }

            .header-controls .btn {
                padding: 4px 6px;
            }

            .header-controls .form-select {
                min-width: 50px;
                padding: 4px;
            }
        }

        /* Search Styles */
        .search-container {
            margin-top: 15px;
            position: relative;
            width: 300px;
        }

        .search-input {
            width: 100%;
            height: 38px;
            padding: 8px 35px 8px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: #2196F3;
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
            outline: none;
        }

        .search-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-size: 14px;
        }

        /* Theme Toggle Button */
        .theme-toggle {
            background: none;
            border: 1px solid #e0e0e0;
            width: 38px;
            height: 38px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #666;
            transition: all 0.3s ease;
        }

        .theme-toggle:hover {
            background: #f5f5f5;
            color: #333;
        }

        .theme-toggle i {
            font-size: 16px;
        }

        .theme-toggle .fa-moon {
            display: none;
        }

        [data-theme="dark"] .theme-toggle .fa-sun {
            display: none;
        }

        [data-theme="dark"] .theme-toggle .fa-moon {
            display: block;
        }

        /* User Menu */
        .user-menu {
            position: relative;
        }

        .user-menu-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            background: none;
            border: 1px solid #e0e0e0;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-menu-btn:hover {
            background: #f5f5f5;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-name {
            font-weight: 500;
            color: #333;
        }

        /* Dropdown Menu */
        .dropdown-menu {
            position: absolute;
            top: calc(100% + 5px);
            right: 0;
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            min-width: 180px;
            padding: 8px 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background: #f5f5f5;
        }

        .dropdown-item i {
            font-size: 14px;
            width: 16px;
            text-align: center;
        }

        .dropdown-divider {
            height: 1px;
            background: #e0e0e0;
            margin: 8px 0;
        }

        /* Dark Mode Styles */
        [data-theme="dark"] .header {
            background: #1a1a1a;
            border-bottom-color: #2d2d2d;
        }

        [data-theme="dark"] .header-title {
            color: #fff;
        }

        [data-theme="dark"] .search-input {
            background: #2d2d2d;
            border-color: #3d3d3d;
            color: #fff;
        }

        [data-theme="dark"] .search-input:focus {
            border-color: #2196F3;
        }

        [data-theme="dark"] .search-icon {
            color: #888;
        }

        [data-theme="dark"] .theme-toggle {
            border-color: #3d3d3d;
            color: #888;
        }

        [data-theme="dark"] .theme-toggle:hover {
            background: #2d2d2d;
            color: #fff;
        }

        [data-theme="dark"] .user-menu-btn {
            border-color: #3d3d3d;
            color: #fff;
        }

        [data-theme="dark"] .user-menu-btn:hover {
            background: #2d2d2d;
        }

        [data-theme="dark"] .user-name {
            color: #fff;
        }

        [data-theme="dark"] .dropdown-menu {
            background: #1a1a1a;
            border-color: #3d3d3d;
        }

        [data-theme="dark"] .dropdown-item {
            color: #fff;
        }

        [data-theme="dark"] .dropdown-item:hover {
            background: #2d2d2d;
        }

        [data-theme="dark"] .dropdown-divider {
            background: #3d3d3d;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .header {
                left: 0;
            }

            .search-container {
                width: 200px;
            }
        }

        @media (max-width: 768px) {
            .header {
                padding: 0 15px;
            }

            .header-title {
                font-size: 1.2rem;
            }

            .search-container {
                display: none;
            }

            .user-name {
                display: none;
            }

            .user-menu-btn {
                padding: 6px;
            }
        }

        @media (max-width: 576px) {
            .header {
                height: 50px;
            }

            .theme-toggle,
            .user-menu-btn {
                width: 32px;
                height: 32px;
                padding: 4px;
            }

            .user-avatar {
                width: 24px;
                height: 24px;
            }
        }

        /* Icon styles */
        .nav-icon,
        .nav-link i:not(.fa-chevron-right),
        .submenu .nav-link i {
            display: none !important;
            /* Hide all menu icons */
        }

        /* Show only Dashboard icon */
        .nav-item[data-menu-id="dashboard"] .nav-icon,
        .nav-item[data-menu-id="dashboard"] .nav-link i {
            display: inline-flex !important;
        }

        /* Show only the chevron-right icon for submenus */
        .nav-item.has-submenu .fa-chevron-right {
            display: inline-flex !important;
            margin-left: auto;
            transition: transform 0.3s ease;
        }

        /* Rotate chevron when submenu is open */
        .nav-item.has-submenu.open .fa-chevron-right {
            transform: rotate(90deg);
        }

        /* Menu styles */
        .nav-list {
            list-style: none;
            /* Remove bullet points from menu */
            overflow-y: auto;
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE and Edge */
        }

        .nav-list::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari, Opera */
        }

        .submenu {
            list-style: none;
            /* Remove bullet points from submenu */
            overflow-y: auto;
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE and Edge */
        }

        .submenu::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari, Opera */
        }

        /* Remove underline on menu hover */
        .nav-link {
            text-decoration: none;
        }

        .nav-link:hover {
            text-decoration: none;
        }

        /* Statistics Cards Styles */
        .statistics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: #fff;
            border-radius: 8px;
            transition: all 0.3s ease;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
            height: 90px;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .stat-content {
            display: flex;
            align-items: center;
            padding: 1rem;
            gap: 1rem;
            height: 100%;
        }

        .stat-icon-wrapper {
            width: 45px;
            height: 45px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
        }

        .stat-card:nth-child(1) .stat-icon-wrapper {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        }

        .stat-card:nth-child(2) .stat-icon-wrapper {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        }

        .stat-card:nth-child(3) .stat-icon-wrapper {
            background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
        }

        .stat-card:nth-child(4) .stat-icon-wrapper {
            background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
        }

        .stat-info {
            flex: 1;
            min-width: 0;
        }

        .stat-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: #5a5c69;
            margin: 0;
            margin-bottom: 0.25rem;
            letter-spacing: 0.5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2e3a59;
            margin: 0;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .stat-trend {
            font-size: 0.7rem;
            margin-top: 0.15rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .stat-trend.positive {
            color: #1cc88a;
        }

        .stat-trend.negative {
            color: #e74a3b;
        }

        /* Dark Mode Support */
        [data-theme="dark"] .stat-card {
            background: #2d2d2d;
            border-color: rgba(255, 255, 255, 0.1);
        }

        [data-theme="dark"] .stat-title {
            color: #adb5bd;
        }

        [data-theme="dark"] .stat-value {
            color: #fff;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .statistics-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .statistics-grid {
                grid-template-columns: 1fr;
            }

            .stat-content {
                padding: 0.75rem;
            }

            .stat-icon-wrapper {
                width: 40px;
                height: 40px;
                font-size: 1.1rem;
            }

            .stat-title {
                font-size: 0.75rem;
            }

            .stat-value {
                font-size: 1.25rem;
            }
        }

        /* Sidebar Header Styles */
        .sidebar-header {
            padding: 20px;
            background: #F2F2F2;
            border-bottom: 1px solid #F2F2F2;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-details {
            flex: 1;
        }

        .user-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #212529;
            margin: 0 0 5px 0;
        }

        .user-role {
            font-size: 0.85rem;
            color: #6c757d;
            display: block;
        }

        /* Dark mode support */
        [data-theme="dark"] .sidebar-header {
            background: #2d2d2d;
            border-bottom-color: #3d3d3d;
        }

        [data-theme="dark"] .user-name {
            color: #fff;
        }

        [data-theme="dark"] .user-role {
            color: #adb5bd;
        }

        [data-theme="dark"] .user-avatar {
            border-color: #3d3d3d;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .sidebar-header {
                padding: 15px;
            }

            .user-avatar {
                width: 40px;
                height: 40px;
            }

            .user-name {
                font-size: 1rem;
            }

            .user-role {
                font-size: 0.8rem;
            }
        }

        /* Show only the chevron-right icon for submenus */
        .submenu-toggle {
            display: inline-flex !important;
            margin-left: auto;
            transition: transform 0.3s ease;
        }

        /* Rotate chevron when submenu is open */
        .nav-item.has-submenu.open .submenu-toggle {
            transform: rotate(90deg);
        }

        /* Menu styles */
        .nav-list {
            list-style: none;
            /* Remove bullet points from menu */
            overflow-y: auto;
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE and Edge */
        }

        .nav-list::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari, Opera */
        }

        .submenu {
            list-style: none;
            /* Remove bullet points from submenu */
            overflow-y: auto;
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE and Edge */
        }

        .submenu::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari, Opera */
        }

        /* Icon styles */
        .nav-icon {
            display: none;
            /* Hide all menu icons */
        }

        /* Show only Dashboard icon */
        .nav-item[data-menu-id="dashboard"] .nav-icon {
            display: inline-flex !important;
        }

        /* Show only the chevron-right icon for submenus */
        .submenu-toggle {
            display: inline-flex !important;
            margin-left: auto;
            transition: transform 0.3s ease;
        }

        /* Rotate chevron when submenu is open */
        .nav-item.has-submenu.open .submenu-toggle {
            transform: rotate(90deg);
        }

        /* Menu styles */
        .nav-list {
            list-style: none;
            /* Remove bullet points from menu */
            overflow-y: auto;
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE and Edge */
        }

        .nav-list::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari, Opera */
        }

        .submenu {
            list-style: none;
            /* Remove bullet points from submenu */
            overflow-y: auto;
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE and Edge */
        }

        .submenu::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari, Opera */
        }

        /* Remove underline on menu hover */
        .nav-link {
            text-decoration: none;
        }

        .nav-link:hover {
            text-decoration: none;
        }

        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin: 0;
            text-decoration: none !important;
        }

        .section-title {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            text-decoration: none !important;
        }

        .dashboard-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .dashboard-card .card-header {
            background: #f8f9fa;
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            flex-shrink: 0;
        }

        .dashboard-card .card-body {
            padding: 15px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .trend-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .department-stats,
        .sentiment-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin: 10px 0;
            flex-shrink: 0;
        }

        .sentiment-chart-container {
            position: relative;
            flex: 1;
            min-height: 150px;
            margin: 10px 0;
        }

        .trend-items-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .view-more {
            text-align: center;
            margin-top: 10px;
            flex-shrink: 0;
        }

        .trend-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e3f2fd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            color: #1976d2;
            font-size: 0.8rem;
        }

        .trend-content {
            flex: 1;
        }

        .trend-title {
            font-weight: 500;
            color: #333;
            margin-bottom: 2px;
        }

        .trend-value {
            color: #666;
            font-size: 0.85rem;
        }

        .trend-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .trend-badge.positive {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .department-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin: 10px 0;
        }

        .department-item {
            background: #f5f5f5;
            padding: 8px;
            border-radius: 6px;
            text-align: center;
        }

        .department-name {
            font-weight: 500;
            color: #333;
            font-size: 0.85rem;
            margin-bottom: 2px;
        }

        .department-count {
            font-size: 1rem;
            font-weight: 600;
            color: #1976d2;
        }

        .department-percentage {
            font-size: 0.8rem;
            color: #666;
        }

        .sentiment-chart-container {
            position: relative;
            height: 200px;
            margin-top: 10px;
        }

        .view-more {
            text-align: center;
            margin-top: 10px;
        }

        .view-more a {
            color: #1976d2;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .sentiment-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin: 10px 0;
        }

        .sentiment-item {
            background: #f5f5f5;
            padding: 8px;
            border-radius: 6px;
            text-align: center;
        }

        .sentiment-label {
            font-weight: 500;
            color: #333;
            font-size: 0.85rem;
            margin-bottom: 2px;
        }

        .sentiment-value {
            font-size: 1rem;
            font-weight: 600;
        }

        .sentiment-value.positive {
            color: #2e7d32;
        }

        .sentiment-value.neutral {
            color: #f57c00;
        }

        .sentiment-value.negative {
            color: #c62828;
        }

        .sentiment-chart-container {
            position: relative;
            height: 150px;
            margin: 10px 0;
        }

        .activity-section {
            margin-top: 2rem;
        }

        .activity-section .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .activity-section .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding: 1rem 1.5rem;
        }

        .activity-section .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .activity-section .card-body {
            padding: 1.5rem;
            max-height: 500px;
            overflow-y: auto;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 0.8rem;
            margin-bottom: 0.8rem;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .activity-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .activity-item .icon-wrapper {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            margin-right: 0.8rem;
            background: rgba(0, 0, 0, 0.03);
        }

        .activity-item .icon-wrapper i {
            font-size: 1.2rem;
        }

        .activity-item .content {
            flex: 1;
        }

        .activity-item .title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.2rem;
        }

        .activity-item .value {
            font-size: 1.4rem;
            font-weight: 700;
            color: #34495e;
            margin: 0;
        }

        .activity-item[data-status="success"] {
            border-left-color: #2ecc71;
        }

        .activity-item[data-status="success"] .icon-wrapper {
            background: rgba(46, 204, 113, 0.1);
        }

        .activity-item[data-status="success"] .icon-wrapper i {
            color: #2ecc71;
        }

        .activity-item[data-status="warning"] {
            border-left-color: #f1c40f;
        }

        .activity-item[data-status="warning"] .icon-wrapper {
            background: rgba(241, 196, 15, 0.1);
        }

        .activity-item[data-status="warning"] .icon-wrapper i {
            color: #f1c40f;
        }

        .activity-item[data-status="error"] {
            border-left-color: #e74c3c;
        }

        .activity-item[data-status="error"] .icon-wrapper {
            background: rgba(231, 76, 60, 0.1);
        }

        .activity-item[data-status="error"] .icon-wrapper i {
            color: #e74c3c;
        }

        .activity-item[data-status="info"] {
            border-left-color: #3498db;
        }

        .activity-item[data-status="info"] .icon-wrapper {
            background: rgba(52, 152, 219, 0.1);
        }

        .activity-item[data-status="info"] .icon-wrapper i {
            color: #3498db;
        }

        /* Scrollbar styling */
        .activity-section .card-body::-webkit-scrollbar {
            width: 6px;
        }

        .activity-section .card-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .activity-section .card-body::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .activity-section .card-body::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Status colors */
        .activity-item[data-status="success"] {
            border-left-color: #28a745;
        }

        .activity-item[data-status="success"] .icon-wrapper i {
            color: #28a745;
        }

        .activity-item[data-status="warning"] {
            border-left-color: #ffc107;
        }

        .activity-item[data-status="warning"] .icon-wrapper i {
            color: #ffc107;
        }

        .activity-item[data-status="error"] {
            border-left-color: #dc3545;
        }

        .activity-item[data-status="error"] .icon-wrapper i {
            color: #dc3545;
        }

        .activity-item[data-status="info"] {
            border-left-color: #17a2b8;
        }

        .activity-item[data-status="info"] .icon-wrapper i {
            color: #17a2b8;
        }

        /* Dashboard Statistics Styles */
        .dashboard-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .dashboard-stat-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 1.25rem;
            transition: all 0.3s ease;
        }

        .dashboard-stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .dashboard-stat-content {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .dashboard-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: #ffffff;
        }

        .dashboard-stat-card:nth-child(1) .dashboard-stat-icon {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        }

        .dashboard-stat-card:nth-child(2) .dashboard-stat-icon {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        }

        .dashboard-stat-card:nth-child(3) .dashboard-stat-icon {
            background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
        }

        .dashboard-stat-card:nth-child(4) .dashboard-stat-icon {
            background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
        }

        .dashboard-stat-info {
            flex: 1;
        }

        .dashboard-stat-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #6c757d;
            margin: 0;
            margin-bottom: 0.25rem;
        }

        .dashboard-stat-number {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2e3a59;
            margin: 0;
            line-height: 1.2;
        }

        /* Dark Mode Support */
        [data-theme="dark"] .dashboard-stat-card {
            background: #2d2d2d;
        }

        [data-theme="dark"] .dashboard-stat-label {
            color: #adb5bd;
        }

        [data-theme="dark"] .dashboard-stat-number {
            color: #ffffff;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .dashboard-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .dashboard-stats-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-stat-content {
                padding: 1rem;
            }

            .dashboard-stat-icon {
                width: 40px;
                height: 40px;
                font-size: 1.1rem;
            }

            .dashboard-stat-label {
                font-size: 0.8rem;
            }

            .dashboard-stat-number {
                font-size: 1.25rem;
            }
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .card-body {
            padding: 1rem;
        }

        .dashboard-stats-grid {
            margin-bottom: 0.5rem !important;
        }

        .charts-row {
            margin-top: 0 !important;
        }

        /* Scroll Driven Animations for Activities */
        @media (prefers-reduced-motion: no-preference) {
            .activity-timeline {
                &>.activity-item {
                    animation: slide-in-from-left linear both;
                    animation-timeline: view();
                    animation-range: entry 0% entry 100%;
                }
            }
        }

        @keyframes slide-in-from-left {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .activity-item {
            padding: 15px;
            margin-bottom: 10px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .activity-item:hover {
            transform: translateY(-2px);
        }

        /* Main content styles */
        .main-content {
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }

        /* Card styles */
        .card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        .card:hover {
            box-shadow: var(--shadow-md);
        }

        /* Table styles */
        .table {
            background-color: var(--bg-card);
            color: var(--text-primary);
        }

        .table th {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        .table td {
            border-color: var(--border-color);
        }

        .table tr:nth-child(even) {
            background-color: var(--bg-secondary);
        }

        .table tr:hover {
            background-color: var(--bg-hover);
        }

        /* Chart styles */
        .chart-container {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: var(--spacing-md);
        }

        /* Statistics card styles */
        .statistics-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: var(--spacing-md);
        }

        /* Form styles */
        .form-control {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }

        /* Button styles */
        .btn {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn:hover {
            background-color: var(--bg-hover);
            border-color: var(--border-hover);
        }

        /* Dropdown styles */
        .dropdown-menu {
            background-color: var(--bg-dropdown);
            border: 1px solid var(--border-color);
        }

        .dropdown-item {
            color: var(--text-primary);
        }

        .dropdown-item:hover {
            background-color: var(--bg-hover);
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: #F2F2F2;
            color: #333;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: left;
        }

        .nav-item {
            margin: 0;
            padding: 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            transition: background-color 0.3s;
            text-align: left;
        }

        .nav-link:hover {
            background-color: #E0E0E0;
        }

        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: left;
            color: #333;
        }

        .nav-link span {
            text-align: left;
        }

        /* Menu Toggle Button Styles */
        .menu-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1050;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px 12px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .menu-toggle i {
            font-size: 1.2rem;
            color: #333;
        }

        .menu-toggle:hover {
            background: #f8f9fa;
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }

            .sidebar {
                position: fixed;
                left: -280px;
                top: 0;
                bottom: 0;
                width: 280px;
                z-index: 1040;
                transition: left 0.3s ease;
                background: #fff;
                box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
            }

            .sidebar.active {
                left: 0;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1030;
            }

            .sidebar-overlay.active {
                display: block;
            }

            body.sidebar-open {
                overflow: hidden;
            }
        }
    </style>
</head>

<body class="theme-transition">
    <div class="dashboard-container">
        <!-- Menu Toggle Button -->
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="sidebar-overlay"></div>
        <aside class="sidebar" style="background-color: #F2F2F2;" role="complementary">
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
                    <li class="nav-item" data-menu-id="dashboard">
                        <a href="dashboard_admin_V1.php" class="nav-link">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item" data-menu-id="employees">
                        <a href="employees/NhanVien_List.html" class="nav-link">
                            <i class="fas fa-users"></i>
                            <span>Nhân viên</span>
                        </a>
                    </li>
                    <li class="nav-item" data-menu-id="attendance">
                        <a href="pages/attendance.php" class="nav-link">
                            <i class="fas fa-clock"></i>
                            <span>Chấm công</span>
                        </a>
                    </li>
                    
                    <li class="nav-item" data-menu-id="payroll">
                        <a href="payroll/payroll_List.html" class="nav-link">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Lương thưởng</span>
                        </a>
                    </li>
                    
                    <li class="nav-item" data-menu-id="departments">
                        <a href="departments/departments.html" class="nav-link">
                            <i class="fas fa-building"></i>
                            <span>Phòng ban</span>
                        </a>
                    </li>
                    
                    <li class="nav-item" data-menu-id="certificates">
                        <a href="degrees/degrees_list.html" class="nav-link">
                            <i class="fas fa-certificate"></i>
                            <span>Bằng cấp</span>
                        </a>
                    </li>
                    
                    <li class="nav-item" data-menu-id="leave">
                        <a href="leave/leave_list.php" class="nav-link">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Nghỉ phép</span>
                        </a>
                    </li>
                    
                    <!-- <li class="nav-item" data-menu-id="training">
                        <a href="pages/training.php" class="nav-link">
                            <i class="fas fa-graduation-cap"></i>
                            <span>Đào tạo</span>
                        </a>
                    </li>
                    <li class="nav-item" data-menu-id="equipment">
                        <a href="pages/equipment.php" class="nav-link">
                            <i class="fas fa-laptop"></i>
                            <span>Thiết bị</span>
                        </a>
                    </li>
                    <li class="nav-item" data-menu-id="documents">
                        <a href="pages/documents.php" class="nav-link">
                            <i class="fas fa-file-alt"></i>
                            <span>Tài liệu</span>
                        </a>
                    </li> -->
                    <li class="nav-item" data-menu-id="logout">
                        <a href="logout.php" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Đăng xuất</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <style>
            /* Menu Toggle Button Styles */
            .menu-toggle {
                display: none;
                position: fixed;
                top: 15px;
                left: 15px;
                z-index: 1050;
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 8px 12px;
                cursor: pointer;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }

            .menu-toggle i {
                font-size: 1.2rem;
                color: #333;
            }

            .menu-toggle:hover {
                background: #f8f9fa;
            }

            /* Sidebar Styles */
            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                width: 280px;
                background: #fff;
                box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
                z-index: 1040;
                transition: left 0.3s ease;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1030;
            }

            /* Mobile Styles */
            @media (max-width: 768px) {
                .menu-toggle {
                    display: block;
                }

                .sidebar {
                    left: -280px;
                }

                .sidebar.active {
                    left: 0;
                }

                .sidebar-overlay.active {
                    display: block;
                }

                body.sidebar-open {
                    overflow: hidden;
                }

                .nav-list {
                    padding: 0;
                    margin: 0;
                }

                .nav-item {
                    margin: 0;
                    padding: 0;
                }

                .nav-link {
                    padding: 12px 20px;
                    display: flex;
                    align-items: center;
                }

                .nav-link i {
                    width: 24px;
                    margin-right: 10px;
                }

                .nav-link span {
                    flex: 1;
                }
            }
        </style>

        <!-- Main Content -->
        <main class="main-content" id="mainContent" role="main">
            <header style="margin: 10px 60px 0px 60px; padding: 20px" class="header">
                <div class="header-left">
                    <button class="menu-toggle d-md-none">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="header-title">Dashboard</h1>
                </div>
                <div class="header-right">
                    <div class="header-controls">
                        <button id="viewDataBtn" class="btn btn-warning me-2">
                            <i class="fas fa-database"></i> View Data
                        </button>
                        <select id="languageSwitch" class="form-select">
                            <option value="vi">Tiếng Việt</option>
                            <option value="en">English</option>
                        </select>
                        <button id="darkModeToggle" class="btn" aria-label="Toggle Dark Mode">
                            <i class="fas fa-moon"></i>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Notification Container -->
            <div class="notification-container" id="notificationContainer"></div>

            <div class="dashboard-content">
                <!-- Statistics Cards -->
                <div class="dashboard-stats-grid d-grid gap-3">
                    <div class="dashboard-stat-card card shadow-sm hover-shadow">
                        <div class="dashboard-stat-content">
                            <div class="dashboard-stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="dashboard-stat-info">
                                <h3 class="dashboard-stat-label">Tổng số nhân viên</h3>
                                <p class="dashboard-stat-number" id="totalEmployees">
                                    <span class="loader"></span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-stat-card card shadow-sm hover-shadow">
                        <div class="dashboard-stat-content">
                            <div class="dashboard-stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="dashboard-stat-info">
                                <h3 class="dashboard-stat-label">Đi làm đúng giờ</h3>
                                <p class="dashboard-stat-number" id="onTimePercentage">
                                    <span class="loader"></span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-stat-card card shadow-sm hover-shadow">
                        <div class="dashboard-stat-content">
                            <div class="dashboard-stat-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="dashboard-stat-info">
                                <h3 class="dashboard-stat-label">Có mặt hôm nay</h3>
                                <p class="dashboard-stat-number" id="presentToday">
                                    <span class="loader"></span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-stat-card card shadow-sm hover-shadow">
                        <div class="dashboard-stat-content">
                            <div class="dashboard-stat-icon">
                                <i class="fas fa-calendar-times"></i>
                            </div>
                            <div class="dashboard-stat-info">
                                <h3 class="dashboard-stat-label">Vắng mặt hôm nay</h3>
                                <p class="dashboard-stat-number" id="absentToday">
                                    <span class="loader"></span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="dashboard-stat-card card shadow-sm hover-shadow">
                        <div class="dashboard-stat-content">
                            <div class="dashboard-stat-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="dashboard-stat-info">
                                <h3 class="dashboard-stat-label">Đi muộn hôm nay</h3>
                                <p class="dashboard-stat-number" id="lateToday">
                                    <span class="loader"></span>
                                </p>
                            </div>
                        </div>
                    </div> -->
                </div>

                <!-- New Charts Section -->
                <div class="row charts-row">
                    <!-- Comment out HR Status Chart
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Biểu đồ trạng thái nhân sự</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="hrChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    -->

                    <!-- Department Distribution Chart -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Biểu đồ phân bố nhân sự theo phòng ban</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="hrDepartmentChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Finance Chart -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Biểu đồ tài chính</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="financeChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Training Chart -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Biểu đồ đào tạo</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="trainingChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Recruitment Chart -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Biểu đồ tuyển dụng</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="recruitmentChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Timeline -->
                <div class="activity-section mt-4">
                    <div class="row">
                        <!-- Statistics Cards -->
                        <!-- Comment out statistics section
                        <div class="col-md-12 mb-4">
                            <div class="statistics-grid">
                                <div class="stat-card">
                                    <div class="stat-content">
                                        <div class="stat-icon-wrapper">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <div class="stat-info">
                                            <div class="stat-title">Hoạt động trong tháng</div>
                                            <div class="stat-value" id="total-activities">0</div>
                                            <div class="stat-trend positive">
                                                <i class="fas fa-arrow-up"></i>
                                                <span>12%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-content">
                                        <div class="stat-icon-wrapper">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div class="stat-info">
                                            <?php /* Tổng số phòng ban hiện có trong hệ thống */ ?>
                                            <div class="stat-title">Tổng số phòng ban</div>
                                            <div class="stat-value" id="total-users">0</div>
                                            <?php /* Tỷ lệ tăng trưởng so với tháng trước */ ?>
                                            <div class="stat-trend positive">
                                                <i class="fas fa-arrow-up"></i>
                                                <span>5%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-content">
                                        <div class="stat-icon-wrapper">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                        <div class="stat-info">
                                            <?php /* Số lượng đơn xin nghỉ phép đang chờ xử lý */ ?>
                                            <div class="stat-title">Đơn xin nghỉ phép</div>
                                            <div class="stat-value" id="total-leaves">0</div>
                                            <?php /* Tỷ lệ giảm so với tháng trước */ ?>
                                            <div class="stat-trend negative">
                                                <i class="fas fa-arrow-down"></i>
                                                <span>3%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-content">
                                        <div class="stat-icon-wrapper">
                                            <i class="fas fa-tasks"></i>
                                        </div>
                                        <div class="stat-info">
                                            <div class="stat-title">Công việc đang thực hiện</div>
                                            <div class="stat-value" id="total-tasks">0</div>
                                            <div class="stat-trend positive">
                                                <i class="fas fa-arrow-up"></i>
                                                <span>8%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        -->

                        <!-- Activity Timeline -->
                        <div class="col-md-12">
                            <div class="card shadow-sm">
                                <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center">
                                    <h3 class="section-title text-primary mb-0">Hoạt động gần đây</h3>
                                    <div class="activity-filters">
                                        <select id="activity-filter" class="form-select form-select-sm">
                                            <option value="all">Tất cả</option>
                                            <option value="LOGIN">Đăng nhập</option>
                                            <option value="LOGOUT">Đăng xuất</option>
                                            <option value="UPDATE_PROFILE">Cập nhật hồ sơ</option>
                                            <option value="CREATE_LEAVE">Nghỉ phép</option>
                                            <option value="APPROVE_LEAVE">Duyệt nghỉ phép</option>
                                            <option value="UPLOAD_DOCUMENT">Tài liệu</option>
                                            <option value="ASSIGN_ASSET">Tài sản</option>
                                            <option value="GENERATE_REPORT">Báo cáo</option>
                                            <option value="CREATE_PROJECT">Dự án</option>
                                            <option value="COMPLETE_TASK">Công việc</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <div class="activity-timeline" id="recent-activities">
                                        <!-- Activities will be loaded here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    /* Statistics Cards */
                    .stat-card {
                        border-radius: 10px;
                        padding: 20px;
                        height: 100%;
                        transition: transform 0.3s ease, box-shadow 0.3s ease;
                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                    }

                    .stat-card:hover {
                        transform: translateY(-5px);
                        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
                    }

                    .stat-icon {
                        font-size: 2.5rem;
                        margin-bottom: 15px;
                        opacity: 0.8;
                    }

                    .stat-content h3 {
                        font-size: 2rem;
                        font-weight: 600;
                        margin-bottom: 5px;
                    }

                    .stat-content p {
                        font-size: 1rem;
                        opacity: 0.9;
                        margin-bottom: 0;
                    }

                    .bg-primary {
                        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
                    }

                    .bg-success {
                        background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
                    }

                    .bg-warning {
                        background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
                    }

                    .bg-info {
                        background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
                    }

                    /* Activity Filters */
                    .activity-filters {
                        width: 200px;
                    }

                    /* Activity Items */
                    .activity-item {
                        display: flex;
                        align-items: center;
                        padding: 0.8rem;
                        margin-bottom: 0.8rem;
                        background: #ffffff;
                        border-radius: 8px;
                        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                        transition: all 0.3s ease;
                        border-left: 3px solid transparent;
                    }

                    .activity-item:hover {
                        transform: translateY(-1px);
                        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                    }

                    .activity-item .icon-wrapper {
                        width: 36px;
                        height: 36px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        border-radius: 8px;
                        margin-right: 0.8rem;
                        background: rgba(0, 0, 0, 0.03);
                    }

                    .activity-item .icon-wrapper i {
                        font-size: 1.2rem;
                    }

                    .activity-item .content {
                        flex: 1;
                    }

                    .activity-item .title {
                        font-size: 0.95rem;
                        font-weight: 600;
                        color: #2c3e50;
                        margin-bottom: 0.2rem;
                    }

                    .activity-item .value {
                        font-size: 1.4rem;
                        font-weight: 700;
                        color: #34495e;
                        margin: 0;
                    }

                    .activity-item[data-status="success"] {
                        border-left-color: #2ecc71;
                    }

                    .activity-item[data-status="success"] .icon-wrapper {
                        background: rgba(46, 204, 113, 0.1);
                    }

                    .activity-item[data-status="success"] .icon-wrapper i {
                        color: #2ecc71;
                    }

                    .activity-item[data-status="warning"] {
                        border-left-color: #f1c40f;
                    }

                    .activity-item[data-status="warning"] .icon-wrapper {
                        background: rgba(241, 196, 15, 0.1);
                    }

                    .activity-item[data-status="warning"] .icon-wrapper i {
                        color: #f1c40f;
                    }

                    .activity-item[data-status="error"] {
                        border-left-color: #e74c3c;
                    }

                    .activity-item[data-status="error"] .icon-wrapper {
                        background: rgba(231, 76, 60, 0.1);
                    }

                    .activity-item[data-status="error"] .icon-wrapper i {
                        color: #e74c3c;
                    }

                    .activity-item[data-status="info"] {
                        border-left-color: #3498db;
                    }

                    .activity-item[data-status="info"] .icon-wrapper {
                        background: rgba(52, 152, 219, 0.1);
                    }

                    .activity-item[data-status="info"] .icon-wrapper i {
                        color: #3498db;
                    }

                    /* Status colors */
                    .activity-item[data-status="success"] {
                        border-left-color: #28a745;
                    }

                    .activity-item[data-status="success"] .icon-wrapper i {
                        color: #28a745;
                    }

                    .activity-item[data-status="warning"] {
                        border-left-color: #ffc107;
                    }

                    .activity-item[data-status="warning"] .icon-wrapper i {
                        color: #ffc107;
                    }

                    .activity-item[data-status="error"] {
                        border-left-color: #dc3545;
                    }

                    .activity-item[data-status="error"] .icon-wrapper i {
                        color: #dc3545;
                    }

                    .activity-item[data-status="info"] {
                        border-left-color: #17a2b8;
                    }

                    .activity-item[data-status="info"] .icon-wrapper i {
                        border-left-color: #17a2b8;
                    }

                    .activity-item[data-status="info"] .icon-wrapper i {
                        color: #17a2b8;
                    }
                </style>

                <script>
                    async function loadRecentActivities() {
                        try {
                            const response = await fetch('../../api/activities/recent.php');

                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }

                            const data = await response.json();

                            if (!data.success) {
                                throw new Error(data.message || 'API returned unsuccessful response');
                            }

                            if (!data.data || !Array.isArray(data.data)) {
                                throw new Error('Invalid data format received from API');
                            }

                            const activityTimeline = document.getElementById('recent-activities');
                            activityTimeline.innerHTML = data.data.map(activity => `
                            <div class="activity-item mb-3" data-type="${activity.type}">
                                <div class="d-flex align-items-center">
                                    <div class="activity-icon me-3">
                                        <i class="fas ${getActivityIcon(activity.type)}"></i>
                                    </div>
                                    <div class="activity-content flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">${activity.type_name || activity.type}</h6>
                                            <small class="text-muted">${activity.created_at}</small>
                                        </div>
                                        <p class="mb-0">${activity.description || 'Không có mô tả'}</p>
                                        <small class="text-muted">${activity.user_name || 'Không xác định'} (${activity.user_email || 'Không có email'})</small>
                                    </div>
                                </div>
                            </div>
                        `).join('');
                        } catch (error) {
                            console.error('Error loading activities:', error);
                            const activityTimeline = document.getElementById('recent-activities');
                            activityTimeline.innerHTML = `
                            <div class="alert alert-danger">
                                <h6 class="alert-heading">Có lỗi xảy ra khi tải dữ liệu</h6>
                                <p class="mb-0">${error.message}</p>
                                <hr>
                                <p class="mb-0 small">Vui lòng thử lại sau hoặc liên hệ quản trị viên nếu lỗi vẫn tiếp diễn.</p>
                            </div>
                        `;
                        }
                    }

                    function getActivityIcon(type) {
                        const icons = {
                            'LOGIN': 'fa-sign-in-alt',
                            'LOGOUT': 'fa-sign-out-alt',
                            'UPDATE_PROFILE': 'fa-user-edit',
                            'CREATE_LEAVE': 'fa-calendar-plus',
                            'APPROVE_LEAVE': 'fa-check-circle',
                            'UPLOAD_DOCUMENT': 'fa-file-upload',
                            'ASSIGN_ASSET': 'fa-box',
                            'GENERATE_REPORT': 'fa-chart-bar',
                            'CREATE_PROJECT': 'fa-project-diagram',
                            'COMPLETE_TASK': 'fa-tasks'
                        };
                        return icons[type] || 'fa-info-circle';
                    }

                    // Load activities when page loads
                    document.addEventListener('DOMContentLoaded', loadRecentActivities);

                    // Add event listener for filter change
                    document.getElementById('activity-filter').addEventListener('change', function(e) {
                        const filter = e.target.value;
                        const activities = document.querySelectorAll('.activity-item');

                        activities.forEach(activity => {
                            if (filter === 'all' || activity.dataset.type === filter) {
                                activity.style.display = '';
                            } else {
                                activity.style.display = 'none';
                            }
                        });
                    });
                </script>

                <!-- Quick Actions -->
                <div class="quick-actions mt-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-transparent border-bottom-0">
                            <h3 class="section-title text-primary mb-0">Thao tác nhanh</h3>
                        </div>
                        <div class="card-body p-3">
                            <div class="quick-actions-grid">
                                <button class="quick-action-btn" onclick="showAddEmployeeModal()">
                                    <i class="fas fa-user-plus"></i>
                                    <span>Thêm nhân viên</span>
                                </button>
                                <button class="quick-action-btn" onclick="showAttendanceModal()">
                                    <i class="fas fa-clock"></i>
                                    <span>Chấm công</span>
                                </button>
                                <button class="quick-action-btn" onclick="showLeaveModal()">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Đăng ký nghỉ phép</span>
                                </button>
                                <button class="quick-action-btn" onclick="showSalaryModal()">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <span>Tính lương</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="row">
                    <div class="col-md-6">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <h5>HR Trends</h5>
                            </div>
                            <div class="card-body">
                                <div class="trend-items-container">
                                    <div class="trend-item">
                                        <div class="trend-icon">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <div class="trend-content">
                                            <div class="trend-title">Tăng trưởng nhân sự</div>
                                            <div class="trend-value">
                                                <span class="trend-badge positive">3 → 120 nhân viên (+117)</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="department-stats">
                                        <div class="department-item">
                                            <div class="department-name">IT</div>
                                            <div class="department-count">25</div>
                                            <div class="department-percentage">20.8%</div>
                                        </div>
                                        <div class="department-item">
                                            <div class="department-name">Kinh doanh</div>
                                            <div class="department-count">35</div>
                                            <div class="department-percentage">29.2%</div>
                                        </div>
                                        <div class="department-item">
                                            <div class="department-name">Hành chính</div>
                                            <div class="department-count">20</div>
                                            <div class="department-percentage">16.7%</div>
                                        </div>
                                        <div class="department-item">
                                            <div class="department-name">Kế toán</div>
                                            <div class="department-count">15</div>
                                            <div class="department-percentage">12.5%</div>
                                        </div>
                                        <div class="department-item">
                                            <div class="department-name">Kỹ thuật</div>
                                            <div class="department-count">25</div>
                                            <div class="department-percentage">20.8%</div>
                                        </div>
                                    </div>

                                    <div class="trend-item">
                                        <div class="trend-icon">
                                            <i class="fas fa-chart-bar"></i>
                                        </div>
                                        <div class="trend-content">
                                            <div class="trend-title">Dự đoán tăng trưởng</div>
                                            <div class="trend-value">15-20 nhân viên mới</div>
                                        </div>
                                    </div>

                                    <div class="trend-item">
                                        <div class="trend-icon">
                                            <i class="fas fa-user-check"></i>
                                        </div>
                                        <div class="trend-content">
                                            <div class="trend-title">Tỷ lệ hoàn thành thử việc</div>
                                            <div class="trend-value">
                                                <span class="trend-badge positive">95%</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="trend-item">
                                        <div class="trend-icon">
                                            <i class="fas fa-venus"></i>
                                        </div>
                                        <div class="trend-content">
                                            <div class="trend-title">Tỷ lệ nhân viên nữ</div>
                                            <div class="trend-value">45%</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="view-more">
                                    <a href="#">Xem thêm <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <h5>Employee Sentiment</h5>
                            </div>
                            <div class="card-body">
                                <div class="sentiment-stats">
                                    <div class="sentiment-item">
                                        <div class="sentiment-label">Tích cực</div>
                                        <div class="sentiment-value positive">65%</div>
                                    </div>
                                    <div class="sentiment-item">
                                        <div class="sentiment-label">Trung lập</div>
                                        <div class="sentiment-value neutral">25%</div>
                                    </div>
                                    <div class="sentiment-item">
                                        <div class="sentiment-label">Tiêu cực</div>
                                        <div class="sentiment-value negative">10%</div>
                                    </div>
                                </div>

                                <div class="sentiment-chart-container">
                                    <canvas id="sentimentChart"></canvas>
                                </div>

                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const ctx = document.getElementById('sentimentChart').getContext('2d');
                                        const sentimentChart = new Chart(ctx, {
                                            type: 'pie',
                                            data: {
                                                labels: ['Tích cực', 'Trung lập', 'Tiêu cực'],
                                                datasets: [{
                                                    data: [65, 25, 10],
                                                    backgroundColor: [
                                                        '#2ecc71', // Xanh lá cho tích cực
                                                        '#f1c40f', // Vàng cho trung lập
                                                        '#e74c3c' // Đỏ cho tiêu cực
                                                    ]
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                plugins: {
                                                    legend: {
                                                        position: 'bottom'
                                                    }
                                                }
                                            }
                                        });
                                    });
                                </script>

                                <div class="trend-items-container">
                                    <div class="trend-item">
                                        <div class="trend-icon">
                                            <i class="fas fa-comment-alt"></i>
                                        </div>
                                        <div class="trend-content">
                                            <div class="trend-title">Phản hồi gần đây</div>
                                            <div class="trend-value">120 phản hồi trong 30 ngày</div>
                                        </div>
                                    </div>

                                    <div class="trend-item">
                                        <div class="trend-icon">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <div class="trend-content">
                                            <div class="trend-title">Xu hướng</div>
                                            <div class="trend-value">
                                                <span class="trend-badge positive">+5% tích cực</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="view-more">
                                    <a href="#">Xem chi tiết <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="footer">
                <p>&copy; 2023 VNPT. All rights reserved.</p>
            </footer>
        </main>
    </div>

    <!-- Form thêm nhân viên -->
    <div id="addEmployeeModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Thêm nhân viên mới</h3>
                <button style="color: brown;" type="button" class="modal-close-btn" onclick="closeAddEmployeeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addEmployeeForm" class="employee-form" enctype="multipart/form-data">
                    <div class="form-grid">
                        <!-- Thông tin cá nhân -->
                        <div class="form-group">
                            <label class="form-label required">Họ</label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Tên</label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Số điện thoại</label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Phòng ban</label>
                            <select class="form-select" name="department_id" required>
                                <option value="">Chọn phòng ban</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Vị trí</label>
                            <select class="form-select" name="position_id" required>
                                <option value="">Chọn vị trí</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Ngày bắt đầu</label>
                            <input type="date" class="form-control" name="hire_date" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Mức lương</label>
                            <input type="number" class="form-control" name="salary" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Trạng thái</label>
                            <select class="form-select" name="status">
                                <option value="active">Đang làm việc</option>
                                <option value="inactive">Nghỉ việc</option>
                                <option value="probation">Thử việc</option>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Địa chỉ</label>
                            <textarea class="form-control" name="address" rows="3"></textarea>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Ảnh đại diện</label>
                            <div class="avatar-upload" onclick="document.getElementById('avatar').click()">
                                <input type="file" id="avatar" name="avatar" accept="image/*" style="display: none">
                                <div id="avatarPreview" class="avatar-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                                <span>Nhấn để tải ảnh lên</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="closeAddEmployeeModal()">Hủy</button>
                        <button type="submit" class="btn-submit">Thêm nhân viên</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal chấm công -->
    <div id="attendanceModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-clock"></i>
                    Chấm công nhân viên
                </h3>
                <button type="button" class="modal-close-btn" onclick="closeAttendanceModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="attendanceForm" class="attendance-form">
                    <!-- Phần tìm kiếm nhân viên -->
                    <div class="form-section employee-search-section">
                        <h4 class="section-title">Tìm kiếm nhân viên</h4>
                        <div class="search-container">
                            <div class="search-input-group">
                                <input type="text"
                                    id="employeeSearch"
                                    name="employeeSearch"
                                    class="form-control search-input"
                                    placeholder="Nhập tên, mã nhân viên hoặc email..."
                                    autocomplete="off"
                                    style="z-index: 1;">
                                <button type="button" class="search-btn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <div id="searchResults" class="search-results" style="z-index: 2;"></div>
                        </div>
                    </div>

                    <style>
                        .search-container {
                            position: relative;
                            width: 100%;
                            margin-bottom: 15px;
                        }

                        .search-input-group {
                            display: flex;
                            gap: 10px;
                            width: 100%;
                            position: relative;
                        }

                        .search-input {
                            flex: 1;
                            height: 38px;
                            padding: 8px 12px;
                            border: 1px solid #ddd;
                            border-radius: 4px;
                            font-size: 14px;
                            background: #fff;
                            width: 100%;
                            pointer-events: auto;
                        }

                        .search-input:focus {
                            border-color: #007bff;
                            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
                            outline: none;
                        }

                        .search-btn {
                            padding: 8px 15px;
                            background: #007bff;
                            color: white;
                            border: none;
                            border-radius: 4px;
                            cursor: pointer;
                            transition: background 0.3s;
                        }

                        .search-btn:hover {
                            background: #0056b3;
                        }

                        .search-results {
                            position: absolute;
                            top: 100%;
                            left: 0;
                            right: 0;
                            background: white;
                            border: 1px solid #ddd;
                            border-radius: 4px;
                            max-height: 200px;
                            overflow-y: auto;
                            margin-top: 5px;
                            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                            display: none;
                        }

                        .search-result-item {
                            padding: 10px 15px;
                            cursor: pointer;
                            border-bottom: 1px solid #eee;
                        }

                        .search-result-item:hover {
                            background-color: #f8f9fa;
                        }

                        .search-result-item:last-child {
                            border-bottom: none;
                        }
                    </style>

                    <!-- Thông tin nhân viên -->
                    <div class="form-section employee-info-section">
                        <h4 class="section-title">Thông tin nhân viên</h4>
                        <div class="employee-info-grid">
                            <div class="info-item">
                                <label>Mã nhân viên</label>
                                <input type="text" id="employeeId" class="form-control" readonly>
                            </div>
                            <div class="info-item">
                                <label>Tên nhân viên</label>
                                <input type="text" id="employeeName" class="form-control" readonly>
                            </div>
                            <div class="info-item">
                                <label>Phòng ban</label>
                                <input type="text" id="employeeDepartment" class="form-control" readonly>
                            </div>
                            <div class="info-item">
                                <label>Vị trí</label>
                                <input type="text" id="employeePosition" class="form-control" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin chấm công -->
                    <div class="form-section attendance-info-section">
                        <h4 class="section-title">Thông tin chấm công</h4>
                        <div class="attendance-info-grid">
                            <div class="info-item">
                                <label>Ngày chấm công</label>
                                <input type="date" id="attendanceDate" class="form-control" required>
                            </div>
                            <div class="info-item">
                                <label>Thời gian</label>
                                <input type="time" id="recordedTime" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <!-- Ký hiệu chấm công -->
                    <div class="form-section attendance-symbols-section">
                        <!-- Ký hiệu chấm công -->
                        <div class="form-section attendance-symbols-section">
                            <h4 class="section-title">Ký hiệu chấm công</h4>
                            <div class="symbols-grid">
                                <button type="button" class="symbol-btn" data-symbol="P" data-color="success">
                                    <i class="fas fa-check-circle"></i>
                                    <span>P - Có mặt</span>
                                </button>
                                <button type="button" class="symbol-btn" data-symbol="Ô" data-color="warning">
                                    <i class="fas fa-procedures"></i>
                                    <span>Ô - Nghỉ ốm</span>
                                </button>
                                <button type="button" class="symbol-btn" data-symbol="Cô" data-color="info">
                                    <i class="fas fa-baby"></i>
                                    <span>Cô - Chăm sóc con ốm</span>
                                </button>
                                <button type="button" class="symbol-btn" data-symbol="TS" data-color="primary">
                                    <i class="fas fa-female"></i>
                                    <span>TS - Nghỉ thai sản</span>
                                </button>
                                <button type="button" class="symbol-btn" data-symbol="T" data-color="danger">
                                    <i class="fas fa-ambulance"></i>
                                    <span>T - Tai nạn lao động</span>
                                </button>
                                <button type="button" class="symbol-btn" data-symbol="CN" data-color="secondary">
                                    <i class="fas fa-calendar-week"></i>
                                    <span>CN - Chủ nhật</span>
                                </button>
                                <button type="button" class="symbol-btn" data-symbol="NL" data-color="secondary">
                                    <i class="fas fa-calendar-day"></i>
                                    <span>NL - Nghỉ lễ</span>
                                </button>
                                <button type="button" class="symbol-btn" data-symbol="NB" data-color="info">
                                    <i class="fas fa-exchange-alt"></i>
                                    <span>NB - Nghỉ bù</span>
                                </button>
                                <button type="button" class="symbol-btn" data-symbol="1/2K" data-color="warning">
                                    <i class="fas fa-clock"></i>
                                    <span>1/2K - Nghỉ nửa ngày không lương</span>
                                </button>
                                <button type="button" class="symbol-btn" data-symbol="K" data-color="danger">
                                    <i class="fas fa-calendar-times"></i>
                                    <span>K - Nghỉ nguyên ngày không lương</span>
                                </button>
                                <button type="button" class="symbol-btn" data-symbol="N" data-color="danger">
                                    <i class="fas fa-ban"></i>
                                    <span>N - Ngừng làm việc</span>
                                </button>
                                <button type="button" class="symbol-btn" data-symbol="P" data-color="success">
                                    <i class="fas fa-calendar-check"></i>
                                    <span>P - Nghỉ phép</span>
                                </button>
                                <button type="button" class="symbol-btn" data-symbol="1/2P" data-color="success">
                                    <i class="fas fa-clock"></i>
                                    <span>1/2P - Nghỉ nửa ngày phép</span>
                                </button>
                                <button type="button" class="symbol-btn" data-symbol="NN" data-color="info">
                                    <i class="fas fa-clock"></i>
                                    <span>NN - Làm nửa ngày</span>
                                </button>
                            </div>
                            <input type="hidden" id="attendanceSymbol" required>
                        </div>

                        <!-- Ghi chú -->
                        <div class="form-section notes-section">
                            <h4 class="section-title">Ghi chú</h4>
                            <textarea id="notes" class="form-control" rows="3"
                                placeholder="Nhập ghi chú (nếu có)..."></textarea>
                        </div>

                        <!-- Nút thao tác -->
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="closeAttendanceModal()">
                                <i class="fas fa-times"></i> Hủy
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Lưu chấm công
                            </button>
                        </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* Style cho modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .modal-overlay.active {
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.3s ease;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8f9fa;
            border-radius: 12px 12px 0 0;
        }

        .modal-title {
            margin: 0;
            font-size: 1.5rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-close-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 5px;
            color: #666;
            transition: color 0.3s;
        }

        .modal-close-btn:hover {
            color: #333;
        }

        .modal-body {
            padding: 20px;
        }

        /* Style cho form sections */
        .form-section {
            margin-bottom: 25px;
            padding: 15px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            text-decoration: none;
        }

        /* Style cho tìm kiếm nhân viên */
        .search-container {
            position: relative;
            width: 100%;
        }

        .search-input-group {
            display: flex;
            gap: 10px;
            width: 100%;
        }

        .search-input-group input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
        }

        .search-input-group input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }

        .search-btn {
            padding: 8px 15px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .search-btn:hover {
            background: #0056b3;
        }

        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .search-result-item {
            padding: 10px 15px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .search-result-item:hover {
            background: #f8f9fa;
        }

        /* Style cho thông tin nhân viên */
        .employee-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .info-item label {
            font-size: 0.9rem;
            color: #666;
        }

        /* Style cho ký hiệu chấm công */
        .symbols-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
        }

        .symbol-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
            text-align: left;
        }

        .symbol-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .symbol-btn.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .symbol-btn[data-color="success"]:hover {
            background: #28a745;
            color: white;
            border-color: #28a745;
        }

        .symbol-btn[data-color="warning"]:hover {
            background: #ffc107;
            color: white;
            border-color: #ffc107;
        }

        .symbol-btn[data-color="danger"]:hover {
            background: #dc3545;
            color: white;
            border-color: #dc3545;
        }

        .symbol-btn[data-color="info"]:hover {
            background: #17a2b8;
            color: white;
            border-color: #17a2b8;
        }

        .symbol-btn[data-color="primary"]:hover {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .symbol-btn[data-color="secondary"]:hover {
            background: #6c757d;
            color: white;
            border-color: #6c757d;
        }

        /* Style cho nút thao tác */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 10px;
            }

            .employee-info-grid,
            .symbols-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        // Initialize theme
        document.addEventListener('DOMContentLoaded', function() {
            // Check for saved theme preference
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                document.body.setAttribute('data-theme', savedTheme);
            }

            // Initialize charts
            initCharts();
        });

        // Theme toggle functionality
        function toggleTheme() {
            const currentTheme = document.body.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        }

        // Initialize all charts
        function initCharts() {
            // Performance Chart
            const performanceCtx = document.getElementById('performanceChart').getContext('2d');
            new Chart(performanceCtx, {
                type: 'bar',
                data: {
                    labels: ['Q1', 'Q2', 'Q3', 'Q4'],
                    datasets: [{
                        label: 'Hiệu suất trung bình',
                        data: [75, 82, 78, 85],
                        backgroundColor: '#2196F3',
                        borderColor: '#1976D2',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });

            // Salary Chart
            const salaryCtx = document.getElementById('salaryChart').getContext('2d');
            new Chart(salaryCtx, {
                type: 'line',
                data: {
                    labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
                    datasets: [{
                        label: 'Tổng chi phí lương',
                        data: [50000000, 52000000, 51000000, 53000000, 54000000, 55000000],
                        borderColor: '#FFC107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('vi-VN') + 'đ';
                                }
                            }
                        }
                    }
                }
            });

            // Leave Chart
            const leaveCtx = document.getElementById('leaveChart').getContext('2d');
            new Chart(leaveCtx, {
                type: 'bar',
                data: {
                    labels: ['Nghỉ phép', 'Nghỉ ốm', 'Nghỉ không lương'],
                    datasets: [{
                        data: [120, 45, 30],
                        backgroundColor: [
                            '#4CAF50',
                            '#2196F3',
                            '#FFC107'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Recruitment Chart
            const recruitmentCtx = document.getElementById('recruitmentChart').getContext('2d');
            new Chart(recruitmentCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Đã tuyển', 'Đang phỏng vấn', 'Đã từ chối', 'Đang chờ'],
                    datasets: [{
                        data: [15, 8, 12, 5],
                        backgroundColor: [
                            '#4CAF50',
                            '#2196F3',
                            '#F44336',
                            '#FFC107'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Training Chart
            const trainingCtx = document.getElementById('trainingChart').getContext('2d');
            new Chart(trainingCtx, {
                type: 'bar',
                data: {
                    labels: ['Kỹ năng mềm', 'Kỹ thuật', 'Quản lý', 'An toàn'],
                    datasets: [{
                        label: 'Số người tham gia',
                        data: [45, 30, 25, 40],
                        backgroundColor: '#9C27B0'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Assets Chart
            const assetsCtx = document.getElementById('assetsChart').getContext('2d');
            new Chart(assetsCtx, {
                type: 'pie',
                data: {
                    labels: ['Đang sử dụng', 'Đang bảo trì', 'Đã thanh lý', 'Chưa cấp phát'],
                    datasets: [{
                        data: [60, 15, 10, 15],
                        backgroundColor: [
                            '#4CAF50',
                            '#2196F3',
                            '#F44336',
                            '#FFC107'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        // Search functionality
        function handleSearch(event) {
            event.preventDefault();
            const searchTerm = document.querySelector('.search-input').value;
            // Implement search logic here
            console.log('Searching for:', searchTerm);
        }

        // User menu toggle
        function toggleUserMenu() {
            const userMenu = document.querySelector('.user-menu');
            userMenu.classList.toggle('show');
        }

        // Close user menu when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.querySelector('.user-menu');
            const userButton = document.querySelector('.user-button');
            if (userMenu && userButton) {
                if (!userButton.contains(event.target) && !userMenu.contains(event.target)) {
                    userMenu.classList.remove('show');
                }
            }
        });

        // Hàm hiển thị modal thêm nhân viên
        function showAddEmployeeModal() {
            const modal = document.getElementById('addEmployeeModal');
            if (!modal) {
                console.error('Modal element not found');
                return;
            }

            modal.classList.add('active');

            // Reset form
            const form = document.getElementById('addEmployeeForm');
            if (form) {
                form.reset();
            }

            // Reset avatar preview
            const avatarPreview = document.getElementById('avatarPreview');
            if (avatarPreview) {
                avatarPreview.innerHTML = '<i class="fas fa-user"></i>';
            }
        }

        // Hàm đóng modal thêm nhân viên
        function closeAddEmployeeModal() {
            const modal = document.getElementById('addEmployeeModal');
            if (modal) {
                modal.classList.remove('active');
            }
        }

        // Hàm hiển thị modal chấm công
        function showAttendanceModal() {
            const modal = document.getElementById('attendanceModal');
            if (!modal) {
                console.error('Modal element not found');
                return;
            }

            modal.classList.add('active');

            // Set default date to today
            const today = new Date();
            const dateInput = document.getElementById('attendanceDate');
            const timeInput = document.getElementById('recordedTime');

            if (dateInput && timeInput) {
                dateInput.valueAsDate = today;
                timeInput.value = today.toTimeString().slice(0, 5);
            }

            // Reset form
            const form = document.getElementById('attendanceForm');
            if (form) {
                form.reset();
            }

            // Reset employee fields
            const employeeFields = ['employeeId', 'employeeName', 'employeeDepartment', 'employeePosition'];
            employeeFields.forEach(field => {
                const element = document.getElementById(field);
                if (element) element.value = '';
            });

            // Reset attendance symbol
            const symbolInput = document.getElementById('attendanceSymbol');
            if (symbolInput) {
                symbolInput.value = '';
            }

            // Remove active class from all symbol buttons
            document.querySelectorAll('.symbol-btn').forEach(btn => {
                btn.classList.remove('active');
            });
        }

        // Hàm đóng modal chấm công
        function closeAttendanceModal() {
            const modal = document.getElementById('attendanceModal');
            if (modal) {
                modal.classList.remove('active');
            }
        }

        // Xử lý sự kiện khi trang được tải
        document.addEventListener('DOMContentLoaded', function() {
            // Xử lý modal thêm nhân viên
            const addEmployeeBtn = document.querySelector('.add-employee-btn');
            if (addEmployeeBtn) {
                addEmployeeBtn.addEventListener('click', showAddEmployeeModal);
            }

            // Xử lý sự kiện khi click ra ngoài modal thêm nhân viên
            const employeeModal = document.getElementById('addEmployeeModal');
            if (employeeModal) {
                employeeModal.addEventListener('click', function(e) {
                    if (e.target === employeeModal) {
                        closeAddEmployeeModal();
                    }
                });
            }

            // Xử lý sự kiện khi click ra ngoài modal chấm công
            const attendanceModal = document.getElementById('attendanceModal');
            if (attendanceModal) {
                attendanceModal.addEventListener('click', function(e) {
                    if (e.target === attendanceModal) {
                        closeAttendanceModal();
                    }
                });
            }

            // Xử lý sự kiện khi tải ảnh đại diện
            const avatarInput = document.getElementById('avatar');
            if (avatarInput) {
                avatarInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const avatarPreview = document.getElementById('avatarPreview');
                            if (avatarPreview) {
                                avatarPreview.innerHTML = `<img src="${e.target.result}" alt="Avatar">`;
                            }
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Xử lý form thêm nhân viên
            const employeeForm = document.getElementById('addEmployeeForm');
            if (employeeForm) {
                employeeForm.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const submitButton = employeeForm.querySelector('button[type="submit"]');
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

                    try {
                        const formData = new FormData(employeeForm);

                        const response = await fetch('/api/employees/add.php', {
                            method: 'POST',
                            body: formData
                        });

                        const result = await response.json();

                        if (result.success) {
                            showNotification('success', 'Thành công', 'Thêm nhân viên thành công');
                            closeAddEmployeeModal();
                            // Reload danh sách nhân viên nếu cần
                        } else {
                            throw new Error(result.message || 'Có lỗi xảy ra khi thêm nhân viên');
                        }
                    } catch (error) {
                        showNotification('error', 'Lỗi', error.message);
                    } finally {
                        submitButton.disabled = false;
                        submitButton.innerHTML = 'Thêm nhân viên';
                    }
                });
            }

            // Xử lý form chấm công
            const attendanceForm = document.getElementById('attendanceForm');
            if (attendanceForm) {
                attendanceForm.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const submitButton = attendanceForm.querySelector('button[type="submit"]');
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

                    try {
                        const formData = new FormData(attendanceForm);

                        const response = await fetch('/api/attendance/save.php', {
                            method: 'POST',
                            body: formData
                        });

                        const result = await response.json();

                        if (result.success) {
                            showNotification('success', 'Thành công', 'Chấm công thành công');
                            closeAttendanceModal();
                        } else {
                            throw new Error(result.message || 'Có lỗi xảy ra khi chấm công');
                        }
                    } catch (error) {
                        showNotification('error', 'Lỗi', error.message);
                    } finally {
                        submitButton.disabled = false;
                        submitButton.innerHTML = 'Lưu chấm công';
                    }
                });
            }

            // Xử lý tìm kiếm nhân viên trong modal chấm công
            let searchTimeout;
            const employeeSearch = document.getElementById('employeeSearch');
            const searchResults = document.getElementById('searchResults');

            if (employeeSearch) {
                // Xử lý khi click ra ngoài để đóng kết quả tìm kiếm
                document.addEventListener('click', (e) => {
                    if (!employeeSearch.contains(e.target) && !searchResults.contains(e.target)) {
                        searchResults.style.display = 'none';
                    }
                });

                // Xử lý khi nhấn ESC để đóng kết quả tìm kiếm
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        searchResults.style.display = 'none';
                    }
                });

                employeeSearch.addEventListener('input', (e) => {
                    clearTimeout(searchTimeout);
                    const searchTerm = e.target.value.trim();

                    if (searchTerm.length < 2) {
                        searchResults.style.display = 'none';
                        return;
                    }

                    searchTimeout = setTimeout(async () => {
                        try {
                            const response = await fetch(`/api/employees/search.php?q=${encodeURIComponent(searchTerm)}`);
                            const data = await response.json();

                            searchResults.innerHTML = '';

                            if (data.length > 0) {
                                data.forEach(employee => {
                                    const div = document.createElement('div');
                                    div.className = 'search-result-item';
                                    div.innerHTML = `
                                <div class="employee-id">${employee.employee_id}</div>
                                <div class="employee-name">${employee.full_name}</div>
                                <div class="employee-department">${employee.department_name}</div>
                            `;
                                    div.onclick = () => {
                                        selectEmployee(employee);
                                        searchResults.style.display = 'none';
                                    };
                                    searchResults.appendChild(div);
                                });
                                searchResults.style.display = 'block';
                            } else {
                                searchResults.style.display = 'none';
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            searchResults.style.display = 'none';
                        }
                    }, 300);
                });
            }

            // Xử lý chọn ký hiệu chấm công
            document.querySelectorAll('.symbol-btn').forEach(button => {
                button.addEventListener('click', () => {
                    document.querySelectorAll('.symbol-btn').forEach(btn => {
                        btn.classList.remove('active');
                        btn.style.backgroundColor = '';
                        btn.style.color = '';
                        btn.style.borderColor = '';
                    });

                    button.classList.add('active');
                    const color = button.dataset.color;
                    button.style.backgroundColor = `var(--${color})`;
                    button.style.color = 'white';
                    button.style.borderColor = `var(--${color})`;

                    document.getElementById('attendanceSymbol').value = button.dataset.symbol;
                });
            });
        });

        // Hàm chọn nhân viên từ kết quả tìm kiếm
        function selectEmployee(employee) {
            document.getElementById('employeeId').value = employee.employee_id;
            document.getElementById('employeeName').value = employee.full_name;
            document.getElementById('employeeDepartment').value = employee.department_name;
            document.getElementById('employeePosition').value = employee.position_name;

            document.getElementById('employeeSearch').value = '';
            document.getElementById('searchResults').style.display = 'none';
        }

        // Hàm hiển thị modal nghỉ phép
        function showLeaveModal() {
            // TODO: Thêm modal nghỉ phép
            console.log('Show leave modal');
        }

        // Hàm hiển thị modal tính lương
        function showSalaryModal() {
            // TODO: Thêm modal tính lương
            console.log('Show salary modal');
        }

        // Function to update dashboard statistics
        async function updateDashboardStats() {
            try {
                const response = await fetch('/qlnhansu_V2/backend/src/api/dashboard/stats.php?type=departments');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();

                if (data.success) {
                    // Update total employees
                    const totalEmployees = document.getElementById('totalEmployees');
                    if (totalEmployees) {
                        totalEmployees.textContent = data.data.totalEmployees.toLocaleString();
                    }

                    // Update on-time percentage
                    const onTimePercentage = document.getElementById('onTimePercentage');
                    if (onTimePercentage) {
                        onTimePercentage.textContent = data.data.onTimePercentage + '%';
                    }

                    // Update present today
                    const presentToday = document.getElementById('presentToday');
                    if (presentToday) {
                        presentToday.textContent = data.data.presentToday.toLocaleString();
                    }

                    // Update absent today
                    const absentToday = document.getElementById('absentToday');
                    if (absentToday) {
                        absentToday.textContent = data.data.absentToday.toLocaleString();
                    }
                } else {
                    console.error('Error fetching dashboard stats:', data.message);
                    // Show error message to user
                    showNotification('error', 'Lỗi', 'Không thể tải dữ liệu thống kê: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                // Show error message to user
                showNotification('error', 'Lỗi', 'Có lỗi xảy ra khi tải dữ liệu thống kê');
            }
        }

        // Function to show notification
        function showNotification(type, title, message, duration = 5000) {
            const container = document.getElementById('notificationContainer');
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;

            // Set icon based on type
            let icon = '';
            switch (type) {
                case 'success':
                    icon = '<i class="fas fa-check-circle"></i>';
                    break;
                case 'error':
                    icon = '<i class="fas fa-exclamation-circle"></i>';
                    break;
                case 'warning':
                    icon = '<i class="fas fa-exclamation-triangle"></i>';
                    break;
                case 'info':
                    icon = '<i class="fas fa-info-circle"></i>';
                    break;
            }

            notification.innerHTML = `
        <div class="notification-icon">${icon}</div>
        <div class="notification-content">
            <div class="notification-title">${title}</div>
            <div class="notification-message">${message}</div>
        </div>
        <div class="notification-close">
            <i class="fas fa-times"></i>
        </div>
        <div class="notification-progress">
            <div class="notification-progress-bar"></div>
        </div>
    `;

            // Add to container
            container.appendChild(notification);

            // Start progress bar
            const progressBar = notification.querySelector('.notification-progress-bar');
            let width = 100;
            const interval = setInterval(() => {
                width -= 0.1;
                progressBar.style.width = width + '%';
                if (width <= 0) {
                    clearInterval(interval);
                    removeNotification(notification);
                }
            }, duration / 1000);

            // Close button click
            const closeBtn = notification.querySelector('.notification-close');
            closeBtn.addEventListener('click', () => {
                clearInterval(interval);
                removeNotification(notification);
            });

            // Auto remove after duration
            setTimeout(() => {
                if (notification.parentNode) {
                    removeNotification(notification);
                }
            }, duration);
        }

        function removeNotification(notification) {
            notification.style.animation = 'slideOut 0.3s ease-out';
            notification.addEventListener('animationend', () => {
                notification.remove();
            });
        }

        // Add slideOut animation
        const style = document.createElement('style');
        style.textContent = `
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
        document.head.appendChild(style);

        // Example usage:
        // showNotification('success', 'Thành công', 'Thao tác đã được thực hiện thành công');
        // showNotification('error', 'Lỗi', 'Có lỗi xảy ra khi thực hiện thao tác');
        // showNotification('warning', 'Cảnh báo', 'Vui lòng kiểm tra lại thông tin');
        // showNotification('info', 'Thông tin', 'Có thông báo mới từ hệ thống');
        // ... existing code ...

        // Update stats when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Initial update
            updateDashboardStats();

            // Update stats every 5 minutes
            setInterval(updateDashboardStats, 300000);
        });

        // Function to fetch and update dashboard statistics
        function updateDashboardStats() {
            fetch('../api/dashboard_stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update total employees
                        document.getElementById('totalEmployees').textContent = data.data.totalEmployees;

                        // Update present today
                        document.getElementById('presentToday').textContent = data.data.presentToday;

                        // Update absent today
                        document.getElementById('absentToday').textContent = data.data.absentToday;

                        // Update on-time percentage with % symbol
                        document.getElementById('onTimePercentage').textContent = data.data.onTimePercentage + '%';
                    } else {
                        console.error('Failed to fetch dashboard stats:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error fetching dashboard stats:', error);
                });
        }

        // Update stats when page loads
        document.addEventListener('DOMContentLoaded', updateDashboardStats);

        // Update stats every 5 minutes
        setInterval(updateDashboardStats, 300000);

        // Function to fetch chart data
        async function fetchChartData() {
            try {
                const response = await fetch('/qlnhansu_V3/backend/src/public/admin/api/dashboard_charts.php');
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const data = await response.json();
                return data.data;
            } catch (error) {
                console.error('Error fetching chart data:', error);
                return null;
            }
        }

        // Initialize all charts with real data
        async function initCharts() {
            const chartData = await fetchChartData();
            if (!chartData) return;

            // 1. Performance Chart
            const performanceCtx = document.getElementById('performanceChart').getContext('2d');
            const performanceLabels = chartData.performance.map(item => `Q${item.quarter}`);
            const performanceData = chartData.performance.map(item => item.avg_score);

            new Chart(performanceCtx, {
                type: 'bar',
                data: {
                    labels: performanceLabels,
                    datasets: [{
                        label: 'Hiệu suất trung bình',
                        data: performanceData,
                        backgroundColor: '#2196F3',
                        borderColor: '#1976D2',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });

            // 2. Salary Chart
            const salaryCtx = document.getElementById('salaryChart').getContext('2d');
            const salaryLabels = chartData.salary.map(item => item.month);
            const salaryData = chartData.salary.map(item => item.total_salary);

            new Chart(salaryCtx, {
                type: 'line',
                data: {
                    labels: salaryLabels,
                    datasets: [{
                        label: 'Tổng chi phí lương',
                        data: salaryData,
                        borderColor: '#FFC107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('vi-VN') + 'đ';
                                }
                            }
                        }
                    }
                }
            });

            // 3. Leave Chart
            const leaveCtx = document.getElementById('leaveChart').getContext('2d');
            const leaveLabels = chartData.leaves.map(item => item.leave_type);
            const leaveData = chartData.leaves.map(item => item.count);

            new Chart(leaveCtx, {
                type: 'bar',
                data: {
                    labels: leaveLabels,
                    datasets: [{
                        data: leaveData,
                        backgroundColor: [
                            '#4CAF50',
                            '#2196F3',
                            '#FFC107'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // 4. Recruitment Chart
            const recruitmentCtx = document.getElementById('recruitmentChart').getContext('2d');
            const recruitmentLabels = chartData.recruitment.map(item => item.status);
            const recruitmentData = chartData.recruitment.map(item => item.count);

            new Chart(recruitmentCtx, {
                type: 'doughnut',
                data: {
                    labels: recruitmentLabels,
                    datasets: [{
                        data: recruitmentData,
                        backgroundColor: [
                            '#4CAF50',
                            '#2196F3',
                            '#F44336',
                            '#FFC107'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // 5. Training Chart
            const trainingCtx = document.getElementById('trainingChart').getContext('2d');
            const trainingLabels = chartData.training.map(item => item.category);
            const trainingData = chartData.training.map(item => item.participant_count);

            new Chart(trainingCtx, {
                type: 'bar',
                data: {
                    labels: trainingLabels,
                    datasets: [{
                        label: 'Số người tham gia',
                        data: trainingData,
                        backgroundColor: '#9C27B0'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // 6. Assets Chart
            const assetsCtx = document.getElementById('assetsChart').getContext('2d');
            const assetsLabels = chartData.assets.map(item => item.status);
            const assetsData = chartData.assets.map(item => item.count);

            new Chart(assetsCtx, {
                type: 'pie',
                data: {
                    labels: assetsLabels,
                    datasets: [{
                        data: assetsData,
                        backgroundColor: [
                            '#4CAF50',
                            '#2196F3',
                            '#F44336',
                            '#FFC107'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        // Initialize charts when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initCharts();

            // Refresh charts every 5 minutes
            setInterval(initCharts, 300000);
        });

        // Function to fetch new chart data
        function fetchNewChartData() {
            fetch('/qlnhansu_V2/backend/src/public/admin/api/new_charts.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        initNewCharts(data.data);
                    }
                })
                .catch(error => console.error('Error fetching chart data:', error));
        }

        // Function to initialize new charts
        function initNewCharts(chartData) {
            /* Comment out HR Status Chart initialization
            // HR Chart (Pie)
            const hrCtx = document.getElementById('hrChart').getContext('2d');
            new Chart(hrCtx, {
                type: 'pie',
                data: {
                    labels: ['Đang làm việc', 'Nghỉ việc', 'Thử việc'],
                    datasets: [{
                        data: [
                            chartData.hr.active,
                            chartData.hr.inactive,
                            chartData.hr.probation
                        ],
                        backgroundColor: ['#4e73df', '#e74a3b', '#f6c23e']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
            */

            // Finance Chart (Bar)
            const financeCtx = document.getElementById('financeChart').getContext('2d');
            new Chart(financeCtx, {
                type: 'bar',
                data: {
                    labels: chartData.finance.map(item => `Tháng ${item.month}`),
                    datasets: [{
                        label: 'Tổng lương (VND)',
                        data: chartData.finance.map(item => item.total_salary),
                        backgroundColor: '#1cc88a'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('vi-VN') + ' VND';
                                }
                            }
                        }
                    }
                }
            });

            // Training Chart (Line)
            const trainingCtx = document.getElementById('trainingChart').getContext('2d');
            new Chart(trainingCtx, {
                type: 'line',
                data: {
                    labels: chartData.training.map(item => `Tháng ${item.month}`),
                    datasets: [{
                            label: 'Số khóa học',
                            data: chartData.training.map(item => item.total_courses),
                            borderColor: '#36b9cc',
                            fill: false
                        },
                        {
                            label: 'Số người tham gia',
                            data: chartData.training.map(item => item.total_participants),
                            borderColor: '#f6c23e',
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Recruitment Chart (Bar)
            const recruitmentCtx = document.getElementById('recruitmentChart').getContext('2d');
            new Chart(recruitmentCtx, {
                type: 'bar',
                data: {
                    labels: chartData.recruitment.map(item => `Tháng ${item.month}`),
                    datasets: [{
                            label: 'Tổng đơn ứng tuyển',
                            data: chartData.recruitment.map(item => item.total_applications),
                            backgroundColor: '#4e73df'
                        },
                        {
                            label: 'Đã phỏng vấn',
                            data: chartData.recruitment.map(item => item.interviewed),
                            backgroundColor: '#1cc88a'
                        },
                        {
                            label: 'Đã tuyển dụng',
                            data: chartData.recruitment.map(item => item.hired),
                            backgroundColor: '#36b9cc'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Fetch chart data on page load
        document.addEventListener('DOMContentLoaded', function() {
            fetchNewChartData();
            // Refresh chart data every 5 minutes
            setInterval(fetchNewChartData, 300000);
        });

        // Function to fetch and update HR statistics
        async function updateHRStats() {
            try {
                const response = await fetch('../api/hr_stats.php');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();

                if (data.success) {
                    // Update HR stats
                    const hrData = data.data.hr;
                    document.getElementById('totalEmployees').textContent = hrData.total_employees;
                    document.getElementById('activeEmployees').textContent = hrData.active_employees;
                    document.getElementById('inactiveEmployees').textContent = hrData.inactive_employees;
                    document.getElementById('probationEmployees').textContent = hrData.probation_employees;

                    // Update finance chart
                    updateFinanceChart(data.data.finance);

                    // Update training chart
                    updateTrainingChart(data.data.training);

                    // Update recruitment chart
                    updateRecruitmentChart(data.data.recruitment);
                } else {
                    console.error('Error fetching HR stats:', data.message);
                    showNotification('error', 'Lỗi', 'Không thể tải dữ liệu nhân sự: ' + data.message);
                }
            } catch (error) {
                // console.error('Error:', error);
                // showNotification('error', 'Lỗi', 'Có lỗi xảy ra khi tải dữ liệu nhân sự');
            }
        }

        // Function to update finance chart
        function updateFinanceChart(financeData) {
            const ctx = document.getElementById('financeChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: financeData.map(item => `Tháng ${item.month}`),
                    datasets: [{
                        label: 'Tổng lương (VND)',
                        data: financeData.map(item => item.total_salary),
                        backgroundColor: '#1cc88a'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('vi-VN') + ' VND';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Function to update training chart
        function updateTrainingChart(trainingData) {
            const ctx = document.getElementById('trainingChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trainingData.map(item => `Tháng ${item.month}`),
                    datasets: [{
                            label: 'Số khóa học',
                            data: trainingData.map(item => item.total_courses),
                            borderColor: '#36b9cc',
                            fill: false
                        },
                        {
                            label: 'Số người tham gia',
                            data: trainingData.map(item => item.total_participants),
                            borderColor: '#f6c23e',
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Function to update recruitment chart
        function updateRecruitmentChart(recruitmentData) {
            const ctx = document.getElementById('recruitmentChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: recruitmentData.map(item => `Tháng ${item.month}`),
                    datasets: [{
                            label: 'Tổng đơn ứng tuyển',
                            data: recruitmentData.map(item => item.total_applications),
                            backgroundColor: '#4e73df'
                        },
                        {
                            label: 'Đã phỏng vấn',
                            data: recruitmentData.map(item => item.interviewed),
                            backgroundColor: '#1cc88a'
                        },
                        {
                            label: 'Đã tuyển dụng',
                            data: recruitmentData.map(item => item.hired),
                            backgroundColor: '#36b9cc'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Update stats when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Initial update
            updateHRStats();

            // Update stats every 5 minutes
            setInterval(updateHRStats, 300000);
        });

        // Function to load department data
        async function loadDepartmentData() {
            try {
                const response = await fetch('../api/departments.php');
                const data = await response.json();

                if (data.success) {
                    // Update total departments count
                    document.getElementById('total-users').textContent = data.data.length;

                    // Update department list if needed
                    const departmentList = document.getElementById('departmentList');
                    if (departmentList) {
                        departmentList.innerHTML = data.data.map(dept => `
                    <div class="department-item">
                        <span class="department-name">${dept.name}</span>
                        <span class="department-count">${dept.employee_count} nhân viên</span>
                    </div>
                `).join('');
                    }
                } else {
                    console.error('Error loading department data:', data.message);
                }
            } catch (error) {
                console.error('Error loading department data:', error);
            }
        }

        // Load department data when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadDepartmentData();
            // Refresh department data every 5 minutes
            setInterval(loadDepartmentData, 300000);
        });

        // Function to load database tables
        async function loadDatabaseTables() {
            try {
                const response = await fetch('/api/database/tables');
                const data = await response.json();

                if (data.success) {
                    const tableList = document.getElementById('tableList');
                    tableList.innerHTML = data.tables.map(table => `
                <a href="#" class="list-group-item list-group-item-action" data-table="${table}">
                    <i class="fas fa-table me-2"></i>${table}
                </a>
            `).join('');

                    // Add click event to table items
                    tableList.querySelectorAll('.list-group-item').forEach(item => {
                        item.addEventListener('click', async (e) => {
                            e.preventDefault();
                            const tableName = e.currentTarget.dataset.table;
                            await loadTableData(tableName);

                            // Update active state
                            tableList.querySelectorAll('.list-group-item').forEach(i => i.classList.remove('active'));
                            e.currentTarget.classList.add('active');
                        });
                    });

                    // Load first table by default
                    if (data.tables.length > 0) {
                        await loadTableData(data.tables[0]);
                        tableList.querySelector('.list-group-item').classList.add('active');
                    }
                } else {
                    console.error('Error loading tables:', data.message);
                }
            } catch (error) {
                console.error('Error loading tables:', error);
            }
        }

        // Function to load table data
        async function loadTableData(tableName) {
            try {
                console.log('Loading data for table:', tableName);
                const response = await fetch(`/api/database/table/${tableName}`);
                console.log('API Response:', response);
                const data = await response.json();
                console.log('Parsed Data:', data);

                if (data.success) {
                    const dataTable = document.getElementById('dataTable');
                    const thead = dataTable.querySelector('thead tr');
                    const tbody = dataTable.querySelector('tbody');

                    // Clear existing data
                    thead.innerHTML = '';
                    tbody.innerHTML = '';

                    // Add headers
                    data.data.headers.forEach(header => {
                        thead.innerHTML += `<th>${header}</th>`;
                    });

                    // Add rows
                    data.data.rows.forEach(row => {
                        const tr = document.createElement('tr');
                        Object.values(row).forEach(cell => {
                            tr.innerHTML += `<td>${cell}</td>`;
                        });
                        tbody.appendChild(tr);
                    });
                } else {
                    console.error('Error loading table data:', data.message);
                    showError('Không thể tải dữ liệu: ' + data.message);
                }
            } catch (error) {
                console.error('Error loading table data:', error);
                showError('Lỗi khi tải dữ liệu: ' + error.message);
            }
        }

        // Initialize when document is ready
        // document.addEventListener('DOMContentLoaded', function() {
        //     // Initialize View Data button
        //     const viewDataBtn = document.getElementById('viewDataBtn');
        //     const databaseViewModal = document.getElementById('databaseViewModal');
        //     const modal = new bootstrap.Modal(databaseViewModal);

        //     viewDataBtn.addEventListener('click', async (e) => {
        //         e.preventDefault();
        //         modal.show();
        //         await loadDatabaseTables();
        //     });

        //     // Prevent modal from closing when clicking outside
        //     databaseViewModal.addEventListener('click', (e) => {
        //         if (e.target === databaseViewModal) {
        //             e.stopPropagation();
        //         }
        //     });
        // });

        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger alert-dismissible fade show';
            errorDiv.role = 'alert';
            errorDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

            const modalBody = document.querySelector('#databaseViewModal .modal-body');
            modalBody.insertBefore(errorDiv, modalBody.firstChild);

            // Auto remove after 5 seconds
            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        }

        // Function to initialize HR department chart
        async function initHRDepartmentChart() {
            const chartContainer = document.getElementById('hrDepartmentChart');
            if (!chartContainer) {
                console.error('Chart container not found');
                return;
            }

            try {
                // Hiển thị trạng thái đang tải
                chartContainer.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

                const response = await fetch('/qlnhansu_V2/backend/src/public/admin/api/hr_department_chart.php');

                // Kiểm tra lỗi HTTP khi gọi API
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                // Kiểm tra nếu API trả về lỗi
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load HR department data');
                }

                // Kiểm tra nếu không có dữ liệu phòng ban
                if (!data.data || data.data.length === 0) {
                    throw new Error('No department data available');
                }

                const departments = data.data;

                // Xóa trạng thái đang tải và tạo canvas mới
                chartContainer.innerHTML = '<canvas></canvas>';
                const ctx = chartContainer.querySelector('canvas').getContext('2d');

                // Hủy biểu đồ cũ nếu tồn tại
                if (window.hrDepartmentChart) {
                    window.hrDepartmentChart.destroy();
                }

                // Create new chart instance
                window.hrDepartmentChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: departments.map(dept => dept.name),
                        datasets: [{
                            data: departments.map(dept => dept.count),
                            backgroundColor: departments.map(dept => dept.color)
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    generateLabels: function(chart) {
                                        const data = chart.data;
                                        if (data.labels.length && data.datasets.length) {
                                            return data.labels.map((label, i) => {
                                                const value = data.datasets[0].data[i];
                                                const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                                const percentage = ((value / total) * 100).toFixed(1);
                                                return {
                                                    text: `${label} (${value} - ${percentage}%)`,
                                                    fillStyle: data.datasets[0].backgroundColor[i],
                                                    hidden: false,
                                                    lineCap: 'butt',
                                                    lineDash: [],
                                                    lineDashOffset: 0,
                                                    lineJoin: 'miter',
                                                    lineWidth: 1,
                                                    strokeStyle: data.datasets[0].backgroundColor[i],
                                                    pointStyle: 'circle',
                                                    rotation: 0
                                                };
                                            });
                                        }
                                        return [];
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error initializing HR department chart:', error);
                chartContainer.innerHTML = `
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading">Lỗi khi tải dữ liệu nhân sự</h4>
                <p>${error.message}</p>
                <hr>
                <p class="mb-0">Vui lòng thử lại sau hoặc liên hệ quản trị viên.</p>
            </div>
        `;
            }
        }

        // Initialize chart after all resources are loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Wait for all resources to load
            window.addEventListener('load', function() {
                // Small delay to ensure other charts are initialized
                setTimeout(initHRDepartmentChart, 100);
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize department chart
            initDepartmentChart();
        });

        async function initDepartmentChart() {
            try {
                const response = await fetch('/qlnhansu_V2/backend/src/public/admin/api/hr_department_chart.php');
                const data = await response.json();

                if (!data.success) {
                    console.error('Failed to load department data:', data.message);
                    return;
                }

                const departments = data.data;
                const chartCtx = document.getElementById('hrDepartmentChart');

                if (!chartCtx) {
                    console.error('HR Department Chart container not found');
                    return;
                }

                // Destroy existing chart if it exists
                if (window.departmentChart) {
                    window.departmentChart.destroy();
                }

                // Create new chart
                window.departmentChart = new Chart(chartCtx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: departments.map(dept => dept.name),
                        datasets: [{
                            label: 'Số lượng nhân viên',
                            data: departments.map(dept => dept.count),
                            backgroundColor: departments.map(dept => dept.color),
                            borderColor: departments.map(dept => dept.color),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `${context.parsed.y} nhân viên`;
                                    }
                                }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error initializing department chart:', error);
            }
        }

        // ... existing code ...
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize View Data button
            const viewDataBtn = document.getElementById('viewDataBtn');
            if (viewDataBtn) {
                viewDataBtn.addEventListener('click', function() {
                    window.location.href = 'check_data.php';
                });
            }
        });
        // ... existing code ...
    </script>

    </div>

    <!-- Database View Modal -->
    <div class="modal fade" id="databaseViewModal" tabindex="-1" aria-labelledby="databaseViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="databaseViewModalLabel">
                        <i class="fas fa-database text-warning me-2"></i>Database Tables
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="list-group" id="tableList">
                                <!-- Tables will be loaded here -->
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="dataTable">
                                    <thead>
                                        <tr>
                                            <!-- Table headers will be loaded here -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Table data will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Files -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/modules/menu-handler.js"></script>
    <script src="js/modules/recent-menu.js"></script>
    <script type="module" src="./js/modules/tab.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            const sidebarOverlay = document.querySelector('.sidebar-overlay');
            const mainContent = document.querySelector('.main-content');

            if (menuToggle && sidebar && sidebarOverlay) {
                menuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    sidebarOverlay.classList.toggle('active');
                    document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
                });

                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                });

                // Close menu on window resize if in desktop mode
                window.addEventListener('resize', function() {
                    if (window.innerWidth > 768) {
                        sidebar.classList.remove('active');
                        sidebarOverlay.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.createElement('button');
            menuToggle.className = 'menu-toggle';
            menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
            document.body.appendChild(menuToggle);

            const sidebar = document.querySelector('.sidebar');
            const overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);

            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            });

            overlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });

            // Close menu when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
                        sidebar.classList.remove('active');
                        overlay.classList.remove('active');
                    }
                }
            });
        });
    </script>
    <!-- Add this before closing body tag -->
    <script src="js/modules/menu-handler.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize menu handler
            new MenuHandler();
        });
    </script>
</body>

</html>