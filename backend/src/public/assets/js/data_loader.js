import ApiService from "./api_service.js";

class DataLoader {
    static async loadEmployeeList() {
        try {
            const employees = await ApiService.getEmployees();
            const tableBody = document.querySelector("#employeeTable tbody");
            if (!tableBody) return;

            tableBody.innerHTML = "";
            employees.forEach(employee => {
                const row = `
                    <tr>
                        <td>${employee.employeeId}</td>
                        <td>${employee.fullName}</td>
                        <td>${employee.department}</td>
                        <td>${employee.position}</td>
                        <td>${employee.email}</td>
                        <td>${employee.phone}</td>
                        <td>${employee.status ? "Đang làm việc" : "Đã nghỉ việc"}</td>
                        <td>
                            <button onclick="viewEmployee('${employee.id}')" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="editEmployee('${employee.id}')" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteEmployee('${employee.id}')" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML("beforeend", row);
            });
        } catch (error) {
            console.error("Error loading employee list:", error);
            showNotification("Lỗi khi tải danh sách nhân viên", "error");
        }
    }

    static async loadDepartments() {
        try {
            const departments = await ApiService.getDepartments();
            const tableBody = document.querySelector("#departmentTable tbody");
            if (!tableBody) return;

            tableBody.innerHTML = "";
            departments.forEach(dept => {
                const row = `
                    <tr>
                        <td>${dept.id}</td>
                        <td>${dept.name}</td>
                        <td>${dept.manager}</td>
                        <td>${dept.employeeCount}</td>
                        <td>
                            <button onclick="editDepartment('${dept.id}')" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteDepartment('${dept.id}')" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML("beforeend", row);
            });
        } catch (error) {
            console.error("Error loading departments:", error);
            showNotification("Lỗi khi tải danh sách phòng ban", "error");
        }
    }

    static async loadAttendance() {
        try {
            const today = new Date().toISOString().split("T")[0];
            const attendance = await ApiService.getAttendance({ date: today });
            const tableBody = document.querySelector("#attendanceTable tbody");
            if (!tableBody) return;

            tableBody.innerHTML = "";
            attendance.forEach(record => {
                const row = `
                    <tr>
                        <td>${record.employeeId}</td>
                        <td>${record.employeeName}</td>
                        <td>${record.checkIn}</td>
                        <td>${record.checkOut || "-"}</td>
                        <td>${record.status}</td>
                        <td>
                            <button onclick="editAttendance('${record.id}')" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML("beforeend", row);
            });
        } catch (error) {
            console.error("Error loading attendance:", error);
            showNotification("Lỗi khi tải dữ liệu chấm công", "error");
        }
    }

    static async loadSalaries() {
        try {
            const salaries = await ApiService.getSalaries();
            const tableBody = document.querySelector("#salaryTable tbody");
            if (!tableBody) return;

            tableBody.innerHTML = "";
            salaries.forEach(salary => {
                const row = `
                    <tr>
                        <td>${salary.employeeId}</td>
                        <td>${salary.employeeName}</td>
                        <td>${salary.baseSalary.toLocaleString("vi-VN")} VNĐ</td>
                        <td>${salary.bonus.toLocaleString("vi-VN")} VNĐ</td>
                        <td>${salary.deductions.toLocaleString("vi-VN")} VNĐ</td>
                        <td>${salary.netSalary.toLocaleString("vi-VN")} VNĐ</td>
                        <td>${salary.paymentDate}</td>
                        <td>
                            <button onclick="viewSalaryDetail('${salary.id}')" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML("beforeend", row);
            });
        } catch (error) {
            console.error("Error loading salaries:", error);
            showNotification("Lỗi khi tải dữ liệu lương", "error");
        }
    }

    static async loadLeaveRequests() {
        try {
            const leaves = await ApiService.getLeaveRequests();
            const tableBody = document.querySelector("#leaveTable tbody");
            if (!tableBody) return;

            tableBody.innerHTML = "";
            leaves.forEach(leave => {
                const row = `
                    <tr>
                        <td>${leave.employeeId}</td>
                        <td>${leave.employeeName}</td>
                        <td>${leave.startDate}</td>
                        <td>${leave.endDate}</td>
                        <td>${leave.reason}</td>
                        <td>${leave.status}</td>
                        <td>
                            <button onclick="approveLeave('${leave.id}')" class="btn btn-success btn-sm" ${leave.status !== "Pending" ? "disabled" : ""}>
                                <i class="fas fa-check"></i>
                            </button>
                            <button onclick="rejectLeave('${leave.id}')" class="btn btn-danger btn-sm" ${leave.status !== "Pending" ? "disabled" : ""}>
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML("beforeend", row);
            });
        } catch (error) {
            console.error("Error loading leave requests:", error);
            showNotification("Lỗi khi tải dữ liệu nghỉ phép", "error");
        }
    }
}

// Utility function to show notifications
function showNotification(message, type = "success") {
    // Implement your notification logic here
    console.log(`${type.toUpperCase()}: ${message}`);
}

export default DataLoader; 