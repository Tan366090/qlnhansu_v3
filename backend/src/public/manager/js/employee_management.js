// Employee Management System
class EmployeeManagement {
    constructor() {
        this.employees = [];
        this.loadEmployees();
        this.checkBirthdays();
    }

    // Load employees from API
    async loadEmployees() {
        try {
            const response = await fetch("/api/employees");
            this.employees = await response.json();
            this.displayEmployees();
        } catch (error) {
            console.error("Error loading employees:", error);
            notificationSystem.addNotification("Lỗi", "Không thể tải danh sách nhân viên", "error");
        }
    }

    // Display employees in table
    displayEmployees() {
        const tbody = document.getElementById("employeeList");
        if (!tbody) return;

        tbody.innerHTML = "";
        this.employees.forEach(employee => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${employee.name}</td>
                <td>${employee.position}</td>
                <td>${employee.department}</td>
                <td>${this.formatDate(employee.join_date)}</td>
                <td>${this.getPerformanceBadge(employee.performance)}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="employeeManagement.viewEmployee(${employee.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-info" onclick="employeeManagement.evaluateEmployee(${employee.id})">
                        <i class="fas fa-star"></i>
                    </button>
                    <button class="btn btn-sm btn-success" onclick="employeeManagement.manageSkills(${employee.id})">
                        <i class="fas fa-tools"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Check for birthdays
    checkBirthdays() {
        const today = new Date();
        const todayMonth = today.getMonth() + 1;
        const todayDay = today.getDate();

        this.employees.forEach(employee => {
            const birthday = new Date(employee.birthday);
            const birthdayMonth = birthday.getMonth() + 1;
            const birthdayDay = birthday.getDate();

            if (birthdayMonth === todayMonth && birthdayDay === todayDay) {
                notificationSystem.addNotification(
                    "Chúc mừng sinh nhật",
                    `Chúc mừng sinh nhật ${employee.name}!`,
                    "info",
                    `/employee.html?id=${employee.id}`
                );
            }
        });
    }

    // View employee details
    async viewEmployee(id) {
        try {
            const response = await fetch(`/api/employees/${id}`);
            const employee = await response.json();
            
            // Show employee details in modal
            this.showEmployeeModal(employee);
        } catch (error) {
            console.error("Error viewing employee:", error);
            notificationSystem.addNotification("Lỗi", "Không thể xem chi tiết nhân viên", "error");
        }
    }

    // Evaluate employee
    async evaluateEmployee(id) {
        try {
            const evaluation = {
                employee_id: id,
                performance: document.getElementById("performance").value,
                skills: document.getElementById("skills").value,
                comments: document.getElementById("comments").value
            };

            const response = await fetch("/api/employees/evaluate", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(evaluation)
            });

            if (response.ok) {
                notificationSystem.addNotification("Thành công", "Đã đánh giá nhân viên", "success");
                this.loadEmployees();
            } else {
                throw new Error("Failed to evaluate employee");
            }
        } catch (error) {
            console.error("Error evaluating employee:", error);
            notificationSystem.addNotification("Lỗi", "Không thể đánh giá nhân viên", "error");
        }
    }

    // Manage employee skills
    async manageSkills(id) {
        try {
            const skills = {
                employee_id: id,
                skills: Array.from(document.querySelectorAll("input[name=\"skill\"]:checked")).map(input => input.value)
            };

            const response = await fetch("/api/employees/skills", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(skills)
            });

            if (response.ok) {
                notificationSystem.addNotification("Thành công", "Đã cập nhật kỹ năng nhân viên", "success");
                this.loadEmployees();
            } else {
                throw new Error("Failed to update skills");
            }
        } catch (error) {
            console.error("Error managing skills:", error);
            notificationSystem.addNotification("Lỗi", "Không thể cập nhật kỹ năng nhân viên", "error");
        }
    }

    // Get performance badge
    getPerformanceBadge(performance) {
        const badges = {
            "excellent": "<span class=\"badge badge-success\">Xuất sắc</span>",
            "good": "<span class=\"badge badge-info\">Tốt</span>",
            "average": "<span class=\"badge badge-warning\">Trung bình</span>",
            "poor": "<span class=\"badge badge-danger\">Kém</span>"
        };
        return badges[performance] || performance;
    }

    // Format date
    formatDate(date) {
        return new Date(date).toLocaleDateString("vi-VN");
    }
}

// Initialize employee management system
const employeeManagement = new EmployeeManagement();
window.employeeManagement = employeeManagement; 