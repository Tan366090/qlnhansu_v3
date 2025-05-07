// Import configurations
import { CONFIG, API_ENDPOINTS, handleApiError, fetchWithTimeout } from '../../js/config.js';

// Service layer for API calls
const ApiService = {
    async fetchEmployees() {
        try {
            return await fetchWithTimeout(API_ENDPOINTS.EMPLOYEES);
        } catch (error) {
            return handleApiError(error);
        }
    },

    async fetchAttendance() {
        try {
            return await fetchWithTimeout(API_ENDPOINTS.ATTENDANCE);
        } catch (error) {
            return handleApiError(error);
        }
    },

    async fetchDepartments() {
        try {
            return await fetchWithTimeout(API_ENDPOINTS.DEPARTMENTS);
        } catch (error) {
            return handleApiError(error);
        }
    },

    async fetchSalaries() {
        try {
            return await fetchWithTimeout(API_ENDPOINTS.SALARIES);
        } catch (error) {
            return handleApiError(error);
        }
    }
};

// Common utilities
export const CommonUtils = {
    formatDate: (date) => {
        return new Date(date).toLocaleDateString('vi-VN');
    },
    formatCurrency: (amount) => {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    },
    formatPercent: (value) => {
        return (value * 100).toFixed(2) + '%';
    }
};

// Notification utilities
export const NotificationUtils = {
    show: (message, type = "info") => {
        const container = document.getElementById("notificationContainer");
        if (!container) return;
        
        const notification = document.createElement("div");
        notification.className = `notification ${type}`;
        notification.textContent = message;
        container.appendChild(notification);
        setTimeout(() => notification.remove(), 5000);
    }
};

// UI utilities
export const UIUtils = {
    toggleDarkMode: () => {
        document.body.classList.toggle("dark-mode");
        localStorage.setItem("darkMode", document.body.classList.contains("dark-mode"));
    },
    toggleSidebar: () => {
        const sidebar = document.querySelector(".sidebar");
        if (sidebar) {
            sidebar.classList.toggle("collapsed");
        }
    },
    showLoading: () => {
        const loadingOverlay = document.getElementById("loadingOverlay");
        if (loadingOverlay) {
            loadingOverlay.style.display = "flex";
        }
    },
    hideLoading: () => {
        const loadingOverlay = document.getElementById("loadingOverlay");
        if (loadingOverlay) {
            loadingOverlay.style.display = "none";
        }
    },
    showNotification(message, type = 'info') {
        const container = document.getElementById('notificationContainer');
        if (!container) return;

        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        container.appendChild(notification);

        setTimeout(() => notification.remove(), 5000);
    },

    updateElement(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    },

    initializeCharts() {
        // Initialize attendance chart
        const attendanceCtx = document.getElementById('attendanceChart')?.getContext('2d');
        if (attendanceCtx) {
            return new Chart(attendanceCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Tỷ lệ chấm công',
                        data: [],
                        borderColor: '#4CAF50',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
        return null;
    }
};

// Event Handlers
const EventHandlers = {
    setupEventListeners() {
        // Dark mode toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', () => {
                document.body.classList.toggle('dark-mode');
                localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
            });
        }

        // Logout button
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => {
                localStorage.removeItem('token');
                window.location.href = '/login.html';
            });
        }

        // Menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.querySelector('.sidebar');
        if (menuToggle && sidebar) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });
        }

        // Window resize handler
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768 && sidebar) {
                sidebar.classList.remove('active');
            }
        });
    }
};

// Dashboard Class
class Dashboard {
    constructor() {
        this.charts = {};
        this.data = {
            employees: [],
            attendance: [],
            departments: [],
            salaries: []
        };
        
        this.initialize();
    }

    async initialize() {
        try {
            UIUtils.showLoading();
            EventHandlers.setupEventListeners();
            this.charts.attendance = UIUtils.initializeCharts();
            await this.loadData();
        } catch (error) {
            console.error('Dashboard initialization error:', error);
            UIUtils.showNotification('Error initializing dashboard', 'error');
        } finally {
            UIUtils.hideLoading();
        }
    }

    async loadData() {
        try {
            const [employees, attendance, departments, salaries] = await Promise.all([
                ApiService.fetchEmployees(),
                ApiService.fetchAttendance(),
                ApiService.fetchDepartments(),
                ApiService.fetchSalaries()
            ]);

            if (employees.error || attendance.error || departments.error || salaries.error) {
                throw new Error('Error fetching data');
            }

            this.data = { employees, attendance, departments, salaries };
            await this.updateDashboard();
        } catch (error) {
            console.error('Error loading data:', error);
            UIUtils.showNotification('Error loading dashboard data', 'error');
        }
    }

    async updateDashboard() {
        this.updateMetrics();
        this.updateCharts();
        this.updateTables();
    }

    updateMetrics() {
        const { employees, attendance, salaries } = this.data;
        
        // Update employee metrics
        UIUtils.updateElement('totalEmployees', employees.length);
        UIUtils.updateElement('activeEmployees', 
            employees.filter(emp => emp.status === 'active').length);
        UIUtils.updateElement('inactiveEmployees',
            employees.filter(emp => emp.status === 'inactive').length);

        // Update other metrics...
    }

    updateCharts() {
        if (this.charts.attendance) {
            // Update attendance chart
            const attendanceData = this.processAttendanceData();
            this.charts.attendance.data.labels = attendanceData.labels;
            this.charts.attendance.data.datasets[0].data = attendanceData.values;
            this.charts.attendance.update();
        }
    }

    updateTables() {
        // Update recent employees table
        const recentEmployees = this.data.employees
            .sort((a, b) => new Date(b.join_date) - new Date(a.join_date))
            .slice(0, 10);

        const tbody = document.getElementById('recentEmployees');
        if (tbody) {
            tbody.innerHTML = recentEmployees.map(emp => `
                <tr>
                    <td>${emp.employee_id}</td>
                    <td>${emp.full_name}</td>
                    <td>${emp.position}</td>
                    <td>${emp.department}</td>
                    <td>${new Date(emp.join_date).toLocaleDateString('vi-VN')}</td>
                    <td>${emp.status}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="window.location.href='employees/edit.html?id=${emp.id}'">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }
    }

    processAttendanceData() {
        const { attendance } = this.data;
        // Process attendance data for chart
        // Return processed data
        return {
            labels: [],
            values: []
        };
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new Dashboard();
});

export { ApiService }; 