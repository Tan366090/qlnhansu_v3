// Check authentication first
if (!auth.checkAuth()) {
    window.location.href = "/login.html";
}

class DashboardAdminManager {
    constructor() {
        this.init();
    }

    async init() {
        await this.loadStatistics();
        await this.loadRecentActivities();
        await this.loadAttendanceSummary();
        await this.loadLeaveRequests();
        this.setupEventListeners();
    }

    async loadStatistics() {
        try {
            common.showLoading();
            
            // Load total employees
            const usersResponse = await api.users.getAll({ limit: 1 });
            document.getElementById("totalEmployees").textContent = usersResponse.total;

            // Load total departments
            const deptResponse = await api.departments.getAll({ limit: 1 });
            document.getElementById("totalDepartments").textContent = deptResponse.total;

            // Load total positions
            const posResponse = await api.positions.getAll({ limit: 1 });
            document.getElementById("totalPositions").textContent = posResponse.total;

            // Load today's attendance
            const today = new Date().toISOString().split('T')[0];
            const attendanceResponse = await api.attendance.getAll({
                date: today,
                limit: 1
            });
            document.getElementById("todayAttendance").textContent = attendanceResponse.total;

            common.hideLoading();
        } catch (error) {
            common.hideLoading();
            console.error("Error loading statistics:", error);
        }
    }

    async loadRecentActivities() {
        try {
            common.showLoading();
            
            const response = await api.activities.getAll({
                limit: 5,
                sort: 'created_at',
                order: 'desc'
            });
            
            const tbody = document.querySelector("#activitiesTable tbody");
            tbody.innerHTML = "";
            
            response.data.forEach(activity => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${this.formatDate(activity.created_at)}</td>
                    <td>${activity.type}</td>
                    <td>${activity.description}</td>
                    <td>${activity.user_name}</td>
                `;
                tbody.appendChild(tr);
            });
            
            common.hideLoading();
        } catch (error) {
            common.hideLoading();
            console.error("Error loading activities:", error);
        }
    }

    async loadAttendanceSummary() {
        try {
            common.showLoading();
            
            const today = new Date().toISOString().split('T')[0];
            const response = await api.attendance.getAll({
                date: today,
                limit: 5,
                sort: 'check_in',
                order: 'desc'
            });
            
            const tbody = document.querySelector("#attendanceTable tbody");
            tbody.innerHTML = "";
            
            response.data.forEach(record => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${record.user_name}</td>
                    <td>${record.department_name}</td>
                    <td>${record.check_in || '-'}</td>
                    <td>${record.check_out || '-'}</td>
                    <td>
                        <span class="status-badge ${record.status.toLowerCase()}">
                            ${record.status}
                        </span>
                    </td>
                `;
                tbody.appendChild(tr);
            });
            
            common.hideLoading();
        } catch (error) {
            common.hideLoading();
            console.error("Error loading attendance:", error);
        }
    }

    async loadLeaveRequests() {
        try {
            common.showLoading();
            
            const response = await api.leaves.getAll({
                status: 'pending',
                limit: 5,
                sort: 'created_at',
                order: 'desc'
            });
            
            const tbody = document.querySelector("#leavesTable tbody");
            tbody.innerHTML = "";
            
            response.data.forEach(leave => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${leave.user_name}</td>
                    <td>${leave.type}</td>
                    <td>${this.formatDate(leave.start_date)}</td>
                    <td>${this.formatDate(leave.end_date)}</td>
                    <td>${leave.days}</td>
                    <td>
                        <div class="action-buttons">
                            <button onclick="window.dashboardAdminManager.approveLeave(${leave.leave_id})" class="btn btn-success">
                                <i class="fas fa-check"></i>
                            </button>
                            <button onclick="window.dashboardAdminManager.rejectLeave(${leave.leave_id})" class="btn btn-danger">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
            
            common.hideLoading();
        } catch (error) {
            common.hideLoading();
            console.error("Error loading leaves:", error);
        }
    }

    setupEventListeners() {
        // View all employees
        document.getElementById("viewAllEmployees").addEventListener("click", () => {
            window.location.href = "employee-list.html";
        });

        // View all departments
        document.getElementById("viewAllDepartments").addEventListener("click", () => {
            window.location.href = "departments.html";
        });

        // View all positions
        document.getElementById("viewAllPositions").addEventListener("click", () => {
            window.location.href = "positions.html";
        });

        // View all attendance
        document.getElementById("viewAllAttendance").addEventListener("click", () => {
            window.location.href = "attendance.html";
        });

        // View all activities
        document.getElementById("viewAllActivities").addEventListener("click", () => {
            window.location.href = "activities.html";
        });

        // View all leaves
        document.getElementById("viewAllLeaves").addEventListener("click", () => {
            window.location.href = "leaves.html";
        });
    }

    async approveLeave(id) {
        if (confirm("Bạn có chắc chắn muốn phê duyệt đơn xin nghỉ này?")) {
            try {
                common.showLoading();
                await api.leaves.update(id, { status: 'approved' });
                common.showSuccess("Phê duyệt đơn xin nghỉ thành công");
                this.loadLeaveRequests();
            } catch (error) {
                common.showError("Không thể phê duyệt đơn xin nghỉ: " + error.message);
            } finally {
                common.hideLoading();
            }
        }
    }

    async rejectLeave(id) {
        if (confirm("Bạn có chắc chắn muốn từ chối đơn xin nghỉ này?")) {
            try {
                common.showLoading();
                await api.leaves.update(id, { status: 'rejected' });
                common.showSuccess("Từ chối đơn xin nghỉ thành công");
                this.loadLeaveRequests();
            } catch (error) {
                common.showError("Không thể từ chối đơn xin nghỉ: " + error.message);
            } finally {
                common.hideLoading();
            }
        }
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN');
    }
}

// Initialize DashboardAdminManager
window.dashboardAdminManager = new DashboardAdminManager(); 
