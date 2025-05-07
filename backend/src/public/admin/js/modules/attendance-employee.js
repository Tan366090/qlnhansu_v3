// Check authentication first
if (!auth.checkAuth()) {
    window.location.href = "/login.html";
}

class AttendanceEmployeeManager {
    constructor() {
        this.currentPage = 1;
        this.pageSize = 10;
        this.init();
    }

    init() {
        this.loadAttendance();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Search input
        document.getElementById("searchInput").addEventListener("input", (e) => {
            this.currentPage = 1;
            this.loadAttendance();
        });

        // Date filter
        document.getElementById("dateFilter").addEventListener("change", (e) => {
            this.currentPage = 1;
            this.loadAttendance();
        });

        // Pagination
        document.getElementById("prevPage").addEventListener("click", () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadAttendance();
            }
        });

        document.getElementById("nextPage").addEventListener("click", () => {
            this.currentPage++;
            this.loadAttendance();
        });

        // Back to dashboard button
        document.getElementById("backToDashboardBtn").addEventListener("click", () => {
            window.location.href = "dashboard-employee.html";
        });
    }

    async loadAttendance() {
        try {
            common.showLoading();

            const search = document.getElementById("searchInput").value;
            const dateFilter = document.getElementById("dateFilter").value;

            const response = await api.attendance.getMyAttendance({
                page: this.currentPage,
                limit: this.pageSize,
                search,
                date: dateFilter
            });

            this.displayAttendance(response.data.items);
            this.updatePagination(response.data.total);
        } catch (error) {
            common.showError("Không thể tải dữ liệu chấm công: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    displayAttendance(attendance) {
        const tbody = document.getElementById("attendanceTableBody");
        tbody.innerHTML = "";

        if (attendance.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center">Không có dữ liệu chấm công</td>
                </tr>
            `;
            return;
        }

        attendance.forEach(item => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${this.formatDate(item.date)}</td>
                <td>${this.formatTime(item.checkIn)}</td>
                <td>${this.formatTime(item.checkOut)}</td>
                <td>${item.totalHours || 0} giờ</td>
                <td>
                    <span class="badge ${this.getStatusBadgeClass(item.status)}">
                        ${this.getStatusText(item.status)}
                    </span>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    updatePagination(total) {
        const totalPages = Math.ceil(total / this.pageSize);
        document.getElementById("currentPage").textContent = this.currentPage;
        document.getElementById("totalPages").textContent = totalPages;

        document.getElementById("prevPage").disabled = this.currentPage === 1;
        document.getElementById("nextPage").disabled = this.currentPage === totalPages;
    }

    getStatusBadgeClass(status) {
        switch (status) {
            case "PRESENT":
                return "badge-success";
            case "LATE":
                return "badge-warning";
            case "ABSENT":
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
}

// Initialize AttendanceEmployeeManager
window.attendanceEmployeeManager = new AttendanceEmployeeManager(); 