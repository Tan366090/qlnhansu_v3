// Check authentication first
if (!auth.checkAuth()) {
    window.location.href = "/login.html";
}

class DashboardEmployeeManager {
    constructor() {
        this.init();
    }

    init() {
        this.loadProfile();
        this.loadAttendance();
        this.loadLeaves();
        this.loadActivities();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Check in button
        document.getElementById("checkInBtn").addEventListener("click", () => {
            this.checkIn();
        });

        // Check out button
        document.getElementById("checkOutBtn").addEventListener("click", () => {
            this.checkOut();
        });

        // View all attendance button
        document.getElementById("viewAllAttendanceBtn").addEventListener("click", () => {
            window.location.href = "attendance-employee.html";
        });

        // View all leaves button
        document.getElementById("viewAllLeavesBtn").addEventListener("click", () => {
            window.location.href = "leaves-employee.html";
        });

        // View all activities button
        document.getElementById("viewAllActivitiesBtn").addEventListener("click", () => {
            window.location.href = "activity-log.html";
        });

        // View profile button
        document.getElementById("viewProfileBtn").addEventListener("click", () => {
            window.location.href = "profile.html";
        });

        // Logout button
        document.getElementById("logoutBtn").addEventListener("click", () => {
            auth.logout();
            window.location.href = "/login.html";
        });
    }

    async loadProfile() {
        try {
            common.showLoading();
            const response = await api.users.getProfile();
            this.displayProfile(response.data);
        } catch (error) {
            common.showError("Không thể tải thông tin hồ sơ: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    displayProfile(profile) {
        document.getElementById("employeeCode").textContent = profile.employeeCode;
        document.getElementById("fullName").textContent = profile.fullName;
        document.getElementById("department").textContent = profile.department.name;
        document.getElementById("position").textContent = profile.position.name;
        
        // Set avatar
        const avatar = document.getElementById("avatar");
        if (profile.avatar) {
            avatar.src = profile.avatar;
        } else {
            avatar.src = "assets/images/default-avatar.png";
        }
    }

    async loadAttendance() {
        try {
            common.showLoading();
            const response = await api.attendance.getToday();
            this.displayAttendance(response.data);
        } catch (error) {
            common.showError("Không thể tải thông tin chấm công: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    displayAttendance(attendance) {
        if (attendance) {
            document.getElementById("checkInTime").textContent = this.formatTime(attendance.checkIn);
            document.getElementById("checkOutTime").textContent = this.formatTime(attendance.checkOut);
            document.getElementById("totalHours").textContent = attendance.totalHours || 0;
            document.getElementById("attendanceStatus").textContent = this.getStatusText(attendance.status);
            document.getElementById("attendanceStatus").className = `badge ${this.getStatusBadgeClass(attendance.status)}`;

            // Update button states
            document.getElementById("checkInBtn").disabled = !!attendance.checkIn;
            document.getElementById("checkOutBtn").disabled = !attendance.checkIn || !!attendance.checkOut;
        } else {
            document.getElementById("checkInTime").textContent = "-";
            document.getElementById("checkOutTime").textContent = "-";
            document.getElementById("totalHours").textContent = "0";
            document.getElementById("attendanceStatus").textContent = "Chưa chấm công";
            document.getElementById("attendanceStatus").className = "badge badge-secondary";

            // Update button states
            document.getElementById("checkInBtn").disabled = false;
            document.getElementById("checkOutBtn").disabled = true;
        }
    }

    async checkIn() {
        try {
            common.showLoading();
            await api.attendance.checkIn();
            common.showSuccess("Chấm công vào thành công");
            this.loadAttendance();
        } catch (error) {
            common.showError("Không thể chấm công vào: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    async checkOut() {
        try {
            common.showLoading();
            await api.attendance.checkOut();
            common.showSuccess("Chấm công ra thành công");
            this.loadAttendance();
        } catch (error) {
            common.showError("Không thể chấm công ra: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    async loadLeaves() {
        try {
            common.showLoading();
            const response = await api.leaves.getMyLeaves({
                page: 1,
                limit: 5
            });
            this.displayLeaves(response.data.items);
        } catch (error) {
            common.showError("Không thể tải thông tin nghỉ phép: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    displayLeaves(leaves) {
        const tbody = document.getElementById("leavesTableBody");
        tbody.innerHTML = "";

        if (leaves.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center">Không có đơn nghỉ phép</td>
                </tr>
            `;
            return;
        }

        leaves.forEach(leave => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${this.formatDate(leave.startDate)}</td>
                <td>${this.formatDate(leave.endDate)}</td>
                <td>${leave.totalDays} ngày</td>
                <td>
                    <span class="badge ${this.getStatusBadgeClass(leave.status)}">
                        ${this.getStatusText(leave.status)}
                    </span>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    async loadActivities() {
        try {
            common.showLoading();
            const response = await api.activities.getMyActivities({
                page: 1,
                limit: 5
            });
            this.displayActivities(response.data.items);
        } catch (error) {
            common.showError("Không thể tải thông tin hoạt động: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    displayActivities(activities) {
        const tbody = document.getElementById("activitiesTableBody");
        tbody.innerHTML = "";

        if (activities.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center">Không có hoạt động</td>
                </tr>
            `;
            return;
        }

        activities.forEach(activity => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${this.formatDateTime(activity.createdAt)}</td>
                <td>${activity.action}</td>
                <td>${activity.details}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    getStatusBadgeClass(status) {
        switch (status) {
            case "PRESENT":
                return "badge-success";
            case "LATE":
                return "badge-warning";
            case "ABSENT":
                return "badge-danger";
            case "APPROVED":
                return "badge-success";
            case "PENDING":
                return "badge-warning";
            case "REJECTED":
                return "badge-danger";
            default:
                return "badge-secondary";
        }
    }

    getStatusText(status) {
        switch (status) {
            case "PRESENT":
                return "Đúng giờ";
            case "LATE":
                return "Đi muộn";
            case "ABSENT":
                return "Vắng mặt";
            case "APPROVED":
                return "Đã duyệt";
            case "PENDING":
                return "Chờ duyệt";
            case "REJECTED":
                return "Từ chối";
            default:
                return "Chưa chấm công";
        }
    }

    formatDate(date) {
        return new Date(date).toLocaleDateString("vi-VN");
    }

    formatTime(time) {
        if (!time) return "-";
        return new Date(time).toLocaleTimeString("vi-VN", {
            hour: "2-digit",
            minute: "2-digit"
        });
    }

    formatDateTime(dateTime) {
        return new Date(dateTime).toLocaleString("vi-VN");
    }
}

// Initialize DashboardEmployeeManager
window.dashboardEmployeeManager = new DashboardEmployeeManager(); 