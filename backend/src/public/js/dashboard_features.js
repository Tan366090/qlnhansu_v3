import { UIUtils, NotificationUtils } from './dashboard.js';

// Dashboard Features
class DashboardFeatures {
    constructor() {
        this.init();
    }

    init() {
        try {
            this.setupEventListeners();
            this.loadData();
        } catch (error) {
            console.error('Initialization error:', error);
            NotificationUtils.show('Không thể khởi tạo dashboard', 'error');
        }
    }

    setupEventListeners() {
        // Dark mode toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', UIUtils.toggleDarkMode);
        }

        // Menu toggle
        const menuToggle = document.getElementById('menuToggle');
        if (menuToggle) {
            menuToggle.addEventListener('click', UIUtils.toggleSidebar);
        }

        // Logout button
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => {
                localStorage.removeItem('token');
                window.location.href = '/login_new.html';
            });
        }
    }

    async loadData() {
        try {
            // Load dashboard data
            const response = await fetch('/api/dashboard/data');
            if (!response.ok) {
                throw new Error('Failed to load dashboard data');
            }
            const data = await response.json();
            this.updateDashboard(data);
        } catch (error) {
            console.error('Error loading dashboard data:', error);
            NotificationUtils.show('Không thể tải dữ liệu dashboard', 'error');
        }
    }

    updateDashboard(data) {
        // Update metrics
        document.getElementById('totalEmployees').textContent = data.totalEmployees || 0;
        document.getElementById('activeEmployees').textContent = data.activeEmployees || 0;
        document.getElementById('kpiCompletion').textContent = `${data.kpiCompletion || 0}%`;
        document.getElementById('todayAttendance').textContent = `${data.todayAttendance || 0}%`;
        document.getElementById('pendingLeaves').textContent = data.pendingLeaves || 0;
        document.getElementById('totalSalary').textContent = data.totalSalary || 0;
    }
}

// Initialize dashboard features
document.addEventListener('DOMContentLoaded', () => {
    new DashboardFeatures();
}); 