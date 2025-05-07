const adminDashboard = {
    menu: [
        {
            title: "Quản lý người dùng",
            items: [
                { name: "Danh sách người dùng", path: "/admin/users", permission: "view_users" },
                { name: "Thêm người dùng", path: "/admin/users/add", permission: "create_users" },
                { name: "Phân quyền", path: "/admin/permissions", permission: "manage_permissions" }
            ]
        },
        {
            title: "Quản lý lương",
            items: [
                { name: "Danh sách lương", path: "/admin/salaries", permission: "view_salaries" },
                { name: "Tăng lương", path: "/admin/salaries/increase", permission: "manage_salaries" },
                { name: "Xuất bảng lương", path: "/admin/salaries/export", permission: "export_salaries" }
            ]
        },
        {
            title: "Quản lý chấm công",
            items: [
                { name: "Nhập chấm công", path: "/admin/attendance/input", permission: "manage_attendance" },
                { name: "Xuất bảng chấm công", path: "/admin/attendance/export", permission: "export_attendance" }
            ]
        },
        {
            title: "Quản lý thưởng",
            items: [
                { name: "Thêm thưởng", path: "/admin/bonuses/add", permission: "manage_bonuses" },
                { name: "Danh sách thưởng", path: "/admin/bonuses", permission: "view_bonuses" }
            ]
        },
        {
            title: "Báo cáo",
            items: [
                { name: "Lịch sử thay đổi", path: "/admin/history", permission: "view_history" },
                { name: "Lịch sử thao tác", path: "/admin/audit", permission: "view_audit" }
            ]
        }
    ]
};

export default adminDashboard; 
// Khởi tạo theo dõi phiên đăng nhập
AuthUtils.initSessionMonitoring();

// Enhanced Dashboard Initialization
class EnhancedDashboard {
    constructor() {
        this.initUI();
        this.initEventListeners();
        this.loadData();
    }
    
    initUI() {
        // Initialize tooltips
        $("[data-toggle=\"tooltip\"]").tooltip();
        
        // Initialize charts with better defaults
        this.initCharts();
        
        // Initialize accordion
        this.initAccordion();
    }
    
    initCharts() {
        // Improved chart initialization with better options
        this.charts = {
            attendance: new Chart(document.getElementById("attendanceChart"), {
                type: "line",
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "bottom"
                        },
                        tooltip: {
                            mode: "index",
                            intersect: false
                        }
                    },
                    interaction: {
                        mode: "nearest",
                        axis: "x",
                        intersect: false
                    }
                }
            }),
            department: new Chart(document.getElementById("departmentChart"), {
                type: "doughnut",
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "right"
                        }
                    }
                }
            })
        };
    }
    
    initAccordion() {
        // Initialize Bootstrap accordion
        const accordion = new bootstrap.Collapse(document.getElementById("additionalSectionsAccordion"), {
            toggle: false
        });
    }
    
    async loadData() {
        try {
            this.showLoading();
            
            const [stats, recentEmployees, activities] = await Promise.all([
                this.fetchWithCache(this.config.endpoints.getStats),
                this.fetchWithCache(this.config.endpoints.getRecentEmployees),
                this.fetchWithCache(this.config.endpoints.getActivities)
            ]);
            
            this.updateStats(stats);
            this.updateRecentEmployees(recentEmployees);
            this.updateActivities(activities);
            this.updateCharts(stats);
        } catch (error) {
            ErrorTracker.log(error, "Loading dashboard data");
            this.showError("Không thể tải dữ liệu dashboard");
        } finally {
            this.hideLoading();
        }
    }
    
    updateStats(stats) {
        // Update quick stats
        document.getElementById("totalEmployees").textContent = stats.totalEmployees;
        document.getElementById("activeEmployees").textContent = stats.activeEmployees;
        document.getElementById("todayAttendance").textContent = `${stats.todayAttendance}%`;
        
        // Update metric cards
        document.querySelectorAll(".metric-card .stat-value").forEach(card => {
            const id = card.id;
            if (stats[id] !== undefined) {
                card.textContent = stats[id];
            }
        });
    }
    
    updateCharts(stats) {
        // Update attendance chart
        this.charts.attendance.data = {
            labels: stats.attendance.labels,
            datasets: [{
                label: "Tỷ lệ chấm công",
                data: stats.attendance.data,
                borderColor: "#4e73df",
                backgroundColor: "rgba(78, 115, 223, 0.1)",
                tension: 0.4
            }]
        };
        this.charts.attendance.update();
        
        // Update department chart
        this.charts.department.data = {
            labels: stats.departments.labels,
            datasets: [{
                data: stats.departments.data,
                backgroundColor: [
                    "#4e73df",
                    "#1cc88a",
                    "#36b9cc",
                    "#f6c23e",
                    "#e74a3b"
                ]
            }]
        };
        this.charts.department.update();
    }
    
    initEventListeners() {
        // Chart period change
        document.getElementById("attendancePeriod").addEventListener("change", (e) => {
            this.loadChartData(e.target.value);
        });
        
        // Activity filter changes
        document.getElementById("activityType").addEventListener("change", () => {
            this.filterActivities();
        });
        
        document.getElementById("activityDate").addEventListener("change", () => {
            this.filterActivities();
        });
        
        // Dark mode toggle
        document.getElementById("darkModeToggle").addEventListener("click", () => {
            ThemeManager.toggleDarkMode();
            this.updateChartsTheme();
        });
    }
    
    updateChartsTheme() {
        const isDarkMode = document.body.classList.contains("dark-mode");
        const textColor = isDarkMode ? "#fff" : "#666";
        
        Object.values(this.charts).forEach(chart => {
            chart.options.plugins.legend.labels.color = textColor;
            chart.update();
        });
    }
    
    filterActivities() {
        const type = document.getElementById("activityType").value;
        const date = document.getElementById("activityDate").value;
        
        const activities = document.querySelectorAll(".activity-item");
        activities.forEach(activity => {
            const activityType = activity.dataset.type;
            const activityDate = activity.dataset.date;
            
            const typeMatch = type === "all" || activityType === type;
            const dateMatch = !date || activityDate === date;
            
            activity.style.display = typeMatch && dateMatch ? "block" : "none";
        });
    }
    
    showLoading() {
        document.getElementById("loadingOverlay").style.display = "flex";
    }
    
    hideLoading() {
        document.getElementById("loadingOverlay").style.display = "none";
    }
    
    showError(message) {
        const notification = document.createElement("div");
        notification.className = "notification error show";
        notification.innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            <div class="notification-content">${message}</div>
            <div class="notification-progress"></div>
        `;
        document.getElementById("notificationContainer").appendChild(notification);
        
        setTimeout(() => {
            notification.classList.remove("show");
            notification.classList.add("hide");
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }
}

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
    new EnhancedDashboard();
});

class MenuManager {
    constructor() {
        this.config = MenuConfig;
        this.init();
    }

    init() {
        this.setupMenu();
        this.setupSearch();
        this.setupFavorites();
        if (this.config.notifications && this.config.notifications.enabled) {
            this.setupNotifications();
        }
    }

    setupNotifications() {
        if (!this.config.notifications) return;
        
        const { sound, desktop } = this.config.notifications;
        
        // Setup notification sound if enabled
        if (sound) {
            this.setupNotificationSound();
        }
        
        // Setup desktop notifications if enabled
        if (desktop) {
            this.setupDesktopNotifications();
        }
    }

    setupNotificationSound() {
        // Implementation for notification sound
    }

    setupDesktopNotifications() {
        // Implementation for desktop notifications
    }

    // ... rest of the MenuManager class implementation
}