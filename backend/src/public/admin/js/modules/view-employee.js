// Check authentication first
if (!auth.checkAuth()) {
    window.location.href = "/login.html";
}

class ViewEmployeeManager {
    constructor() {
        this.employeeId = new URLSearchParams(window.location.search).get('id');
        if (!this.employeeId) {
            window.location.href = "employee-list.html";
            return;
        }
        this.init();
    }

    async init() {
        await this.loadEmployee();
        await this.loadAttendance();
        await this.loadLeaves();
        this.setupEventListeners();
    }

    async loadEmployee() {
        try {
            common.showLoading();
            
            const response = await api.users.getById(this.employeeId);
            const employee = response.data;
            
            // Update employee details
            document.getElementById("employeeCode").textContent = employee.employee_code;
            document.getElementById("username").textContent = employee.username;
            document.getElementById("fullName").textContent = employee.full_name;
            document.getElementById("email").textContent = employee.email;
            document.getElementById("phone").textContent = employee.phone;
            document.getElementById("department").textContent = employee.department_name;
            document.getElementById("position").textContent = employee.position_name;
            document.getElementById("gender").textContent = employee.gender === 'male' ? 'Nam' : employee.gender === 'female' ? 'Nữ' : 'Khác';
            document.getElementById("birthDate").textContent = this.formatDate(employee.birth_date);
            document.getElementById("address").textContent = employee.address || 'Không có';
            document.getElementById("status").textContent = employee.is_active ? 'Đang làm việc' : 'Đã nghỉ việc';
            document.getElementById("status").className = `status-badge ${employee.is_active ? 'active' : 'inactive'}`;
            
            // Update avatar if exists
            if (employee.avatar) {
                document.getElementById("avatar").src = employee.avatar;
            }
            
            common.hideLoading();
        } catch (error) {
            common.hideLoading();
            common.showError("Không thể tải thông tin nhân viên: " + error.message);
            window.location.href = "employee-list.html";
        }
    }

    async loadAttendance() {
        try {
            common.showLoading();
            
            const response = await api.attendance.getAll({
                user_id: this.employeeId,
                limit: 5,
                sort: 'date',
                order: 'desc'
            });
            
            const tbody = document.querySelector("#attendanceTable tbody");
            tbody.innerHTML = "";
            
            response.data.forEach(record => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${this.formatDate(record.date)}</td>
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

    async loadLeaves() {
        try {
            common.showLoading();
            
            const response = await api.leaves.getAll({
                user_id: this.employeeId,
                limit: 5,
                sort: 'start_date',
                order: 'desc'
            });
            
            const tbody = document.querySelector("#leavesTable tbody");
            tbody.innerHTML = "";
            
            response.data.forEach(leave => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${this.formatDate(leave.start_date)}</td>
                    <td>${this.formatDate(leave.end_date)}</td>
                    <td>${leave.days}</td>
                    <td>${leave.type}</td>
                    <td>
                        <span class="status-badge ${leave.status.toLowerCase()}">
                            ${leave.status}
                        </span>
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
        // Back button
        document.getElementById("backBtn").addEventListener("click", () => {
            window.location.href = "employee-list.html";
        });

        // Edit button
        document.getElementById("editBtn").addEventListener("click", () => {
            window.location.href = `edit-employee.html?id=${this.employeeId}`;
        });

        // View all attendance
        document.getElementById("viewAllAttendance").addEventListener("click", () => {
            window.location.href = `attendance.html?user_id=${this.employeeId}`;
        });

        // View all leaves
        document.getElementById("viewAllLeaves").addEventListener("click", () => {
            window.location.href = `leaves.html?user_id=${this.employeeId}`;
        });
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN');
    }
}

// Initialize ViewEmployeeManager
window.viewEmployeeManager = new ViewEmployeeManager(); 