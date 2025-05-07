// Common utilities
const CommonUtils = {
    formatDate: (date) => {
        return new Date(date).toLocaleDateString("vi-VN");
    },
    formatCurrency: (amount) => {
        return new Intl.NumberFormat("vi-VN", {
            style: "currency",
            currency: "VND",
        }).format(amount);
    },
};

// Authentication utilities
const AuthUtils = {
    isAuthenticated: () => {
        return localStorage.getItem("token") !== null;
    },
    logout: () => {
        localStorage.removeItem("token");
        window.location.href = "/login_new.html";
    },
};

// Permission utilities
const PermissionUtils = {
    hasPermission: (permission) => {
        const userPermissions = JSON.parse(
            localStorage.getItem("permissions") || "[]"
        );
        return userPermissions.includes(permission);
    },
};

// Notification utilities
const NotificationUtils = {
    show: (message, type = "info") => {
        const container = document.getElementById("notificationContainer");
        const notification = document.createElement("div");
        notification.className = `notification ${type}`;
        notification.textContent = message;
        container.appendChild(notification);
        setTimeout(() => notification.remove(), 5000);
    },
};

// UI utilities
const UIUtils = {
    // Comment out dark mode implementation
    /*
    toggleDarkMode: () => {
        document.body.classList.toggle("dark-mode");
        localStorage.setItem(
            "darkMode",
            document.body.classList.contains("dark-mode")
        );
    },
    */
    toggleSidebar: () => {
        const sidebar = document.querySelector(".sidebar");
        const overlay = document.getElementById("sidebarOverlay");
        sidebar.classList.toggle("active");
        overlay.classList.toggle("active");
    }
};

// Export utilities
export { CommonUtils, AuthUtils, PermissionUtils, NotificationUtils, UIUtils };

// Dashboard Handler Class
class DashboardHandler {
    constructor() {
        this.apiHandler = new APIHandler();
        this.chartHandler = new ChartHandler();
        this.isLoading = false;
        this.data = {
            employees: [],
            departments: [],
            positions: [],
            performances: [],
            payroll: [],
            leaves: [],
            trainings: [],
            tasks: []
        };
        this.setupEventListeners();
        this.loadData();
    }

    // Setup event listeners
    setupEventListeners() {
        // Comment out dark mode toggle event listener
        /*
        const darkModeToggle = document.getElementById('darkModeToggle');
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', () => {
                document.body.classList.toggle('dark-mode');
                localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
            });
        }
        */

        // Logout button
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => {
                localStorage.removeItem('token');
                window.location.href = '/login_new.html';
            });
        }

        // Attendance period change
        const attendancePeriod = document.getElementById('attendancePeriod');
        if (attendancePeriod) {
            attendancePeriod.addEventListener('change', (e) => {
                this.updateAttendanceChart(e.target.value);
            });
        }
    }

    // Load all dashboard data
    async loadData() {
        if (this.isLoading) return;

        this.isLoading = true;
        document.getElementById('loadingOverlay').style.display = 'flex';

        try {
            const [
                employees,
                departments,
                positions,
                performances,
                payroll,
                leaves,
                trainings,
                tasks
            ] = await Promise.all([
                this.apiHandler.get('employees'),
                this.apiHandler.get('departments'),
                this.apiHandler.get('positions'),
                this.apiHandler.get('performances'),
                this.apiHandler.get('payroll'),
                this.apiHandler.get('leaves'),
                this.apiHandler.get('trainings'),
                this.apiHandler.get('tasks')
            ]);

            this.data = {
                employees: employees || [],
                departments: departments || [],
                positions: positions || [],
                performances: performances || [],
                payroll: payroll || [],
                leaves: leaves || [],
                trainings: trainings || [],
                tasks: tasks || []
            };

            await this.updateMetrics();
            await this.updateCharts();
            await this.updateRecentEmployees();

        } catch (error) {
            console.error('Error loading dashboard data:', error);
            this.apiHandler.showError('Lỗi khi tải dữ liệu dashboard', 'error');
        } finally {
            this.isLoading = false;
            document.getElementById('loadingOverlay').style.display = 'none';
        }
    }

    // Update metrics
    async updateMetrics() {
        try {
            const { employees = [], performances = [], payroll = [], leaves = [] } = this.data;

            // Update employee metrics
            const totalEmployeesEl = document.getElementById('totalEmployees');
            const activeEmployeesEl = document.getElementById('activeEmployees');
            const inactiveEmployeesEl = document.getElementById('inactiveEmployees');
            
            if (totalEmployeesEl) totalEmployeesEl.textContent = employees.length || 0;
            if (activeEmployeesEl) activeEmployeesEl.textContent = 
                employees.filter(emp => emp?.status === 'active').length || 0;
            if (inactiveEmployeesEl) inactiveEmployeesEl.textContent = 
                employees.filter(emp => emp?.status === 'inactive').length || 0;

            // Update performance metrics
            const kpiCompletionEl = document.getElementById('kpiCompletion');
            if (kpiCompletionEl) {
                const avgPerformance = performances.length > 0 
                    ? performances.reduce((sum, perf) => sum + (perf?.rating || 0), 0) / performances.length 
                    : 0;
                kpiCompletionEl.textContent = `${Math.round(avgPerformance)}%`;
            }

            // Update payroll metrics
            const totalSalaryEl = document.getElementById('totalSalary');
            if (totalSalaryEl) {
                const totalSalary = payroll.length > 0
                    ? payroll.reduce((sum, pay) => sum + (pay?.amount || 0), 0)
                    : 0;
                totalSalaryEl.textContent = this.formatCurrency(totalSalary);
            }

            // Update leaves metrics
            const pendingLeavesEl = document.getElementById('pendingLeaves');
            if (pendingLeavesEl) {
                const pendingLeaves = leaves.length > 0
                    ? leaves.filter(leave => leave?.status === 'pending').length
                    : 0;
                pendingLeavesEl.textContent = pendingLeaves;
            }

            // Update attendance metrics
            const todayAttendanceEl = document.getElementById('todayAttendance');
            if (todayAttendanceEl) {
                const today = new Date().toISOString().split('T')[0];
                const todayAttendance = employees.length > 0
                    ? employees.filter(emp => emp?.last_attendance_date === today).length
                    : 0;
                const attendanceRate = employees.length > 0 
                    ? (todayAttendance / employees.length) * 100 
                    : 0;
                todayAttendanceEl.textContent = `${Math.round(attendanceRate)}%`;
            }

        } catch (error) {
            console.error('Error updating metrics:', error);
            this.apiHandler.showError('Lỗi khi cập nhật số liệu', 'error');
        }
    }

    // Update charts
    async updateCharts() {
        try {
            const { employees, departments } = this.data;

            // Update attendance chart
            const attendanceData = this.processAttendanceData(employees);
            this.chartHandler.initChart('attendanceChart', 'line', attendanceData);

            // Update department chart
            const departmentData = this.processDepartmentData(departments);
            this.chartHandler.initChart('departmentChart', 'pie', departmentData);

        } catch (error) {
            console.error('Error updating charts:', error);
            this.apiHandler.showError('Lỗi khi cập nhật biểu đồ', 'error');
        }
    }

    // Process attendance data
    processAttendanceData(employees) {
        if (!employees || !Array.isArray(employees)) {
            return { labels: [], datasets: [] };
        }

        // Group attendance by date and calculate rates
        const attendanceByDate = {};
        employees.forEach((emp) => {
            if (emp && emp.last_attendance_date) {
                if (!attendanceByDate[emp.last_attendance_date]) {
                    attendanceByDate[emp.last_attendance_date] = { present: 0, total: 0 };
                }
                attendanceByDate[emp.last_attendance_date].total++;
                if (emp.status === 'present') {
                    attendanceByDate[emp.last_attendance_date].present++;
                }
            }
        });

        const labels = Object.keys(attendanceByDate).sort();
        const values = labels.map((date) => {
            const { present, total } = attendanceByDate[date];
            return (present / total) * 100;
        });

        return this.chartHandler.processLineData(
            labels,
            [{
                label: 'Tỷ lệ chấm công',
                data: values,
                color: '#4CAF50'
            }]
        );
    }

    // Process department data
    processDepartmentData(departments) {
        if (!departments || !Array.isArray(departments)) {
            return { labels: [], datasets: [] };
        }

        return this.chartHandler.processPieData(
            departments.map(d => d.name),
            departments.map(d => d.employee_count)
        );
    }

    // Update recent employees table
    async updateRecentEmployees() {
        try {
            const { employees } = this.data;
            const recentEmployees = employees
                .sort((a, b) => new Date(b.join_date) - new Date(a.join_date))
                .slice(0, 10);

            const tbody = document.getElementById('recentEmployees');
            if (!tbody) return;

            if (recentEmployees.length === 0) {
                this.apiHandler.handleEmptyData('recentEmployees');
                return;
            }

            tbody.innerHTML = recentEmployees
                .map(emp => `
                    <tr>
                        <td>${emp.employee_id}</td>
                        <td>${emp.full_name}</td>
                        <td>${emp.position}</td>
                        <td>${emp.department}</td>
                        <td>${this.formatDate(emp.join_date)}</td>
                        <td>${this.formatDate(emp.birth_date)}</td>
                        <td>${emp.phone}</td>
                        <td>${emp.email}</td>
                        <td>${emp.address}</td>
                        <td>${emp.status}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="window.location.href='employees/edit.html?id=${emp.id}'">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                `)
                .join('');

        } catch (error) {
            console.error('Error updating recent employees:', error);
            this.apiHandler.showError('Lỗi khi cập nhật danh sách nhân viên', 'error');
        }
    }

    // Update attendance chart based on period
    async updateAttendanceChart(period) {
        try {
            const data = await this.apiHandler.get(`attendance/trend?period=${period}`);
            if (!data) return;

            this.chartHandler.updateChart('attendanceChart', data);

        } catch (error) {
            console.error('Error updating attendance chart:', error);
            this.apiHandler.showError('Lỗi khi cập nhật biểu đồ chấm công', 'error');
        }
    }

    // Format currency
    formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }

    // Format date
    formatDate(date) {
        return new Date(date).toLocaleDateString('vi-VN');
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const dashboard = new DashboardHandler();
}); 