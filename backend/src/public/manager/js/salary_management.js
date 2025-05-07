// Salary Management System
class SalaryManagement {
    constructor() {
        this.salaries = [];
        this.loadSalaries();
        this.calculateSalaries();
    }

    // Load salaries from API
    async loadSalaries() {
        try {
            const response = await fetch("/api/salaries");
            this.salaries = await response.json();
            this.displaySalaries();
        } catch (error) {
            console.error("Error loading salaries:", error);
            notificationSystem.addNotification("Lỗi", "Không thể tải danh sách lương", "error");
        }
    }

    // Display salaries in table
    displaySalaries() {
        const tbody = document.getElementById("salaryList");
        if (!tbody) return;

        tbody.innerHTML = "";
        this.salaries.forEach(salary => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${salary.employee_name}</td>
                <td>${this.formatCurrency(salary.basic_salary)}</td>
                <td>${this.formatCurrency(salary.allowance)}</td>
                <td>${this.formatCurrency(salary.bonus)}</td>
                <td>${this.formatCurrency(salary.total)}</td>
                <td>${this.getStatusBadge(salary.status)}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="salaryManagement.viewSalary(${salary.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${salary.status === "pending" ? `
                        <button class="btn btn-sm btn-success" onclick="salaryManagement.approveSalary(${salary.id})">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="salaryManagement.rejectSalary(${salary.id})">
                            <i class="fas fa-times"></i>
                        </button>
                    ` : ""}
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Calculate salaries
    async calculateSalaries() {
        try {
            const response = await fetch("/api/salaries/calculate", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                }
            });

            if (response.ok) {
                notificationSystem.addNotification("Thành công", "Đã tính toán lương tự động", "success");
                this.loadSalaries();
            } else {
                throw new Error("Failed to calculate salaries");
            }
        } catch (error) {
            console.error("Error calculating salaries:", error);
            notificationSystem.addNotification("Lỗi", "Không thể tính toán lương tự động", "error");
        }
    }

    // View salary details
    async viewSalary(id) {
        try {
            const response = await fetch(`/api/salaries/${id}`);
            const salary = await response.json();
            
            // Show salary details in modal
            this.showSalaryModal(salary);
        } catch (error) {
            console.error("Error viewing salary:", error);
            notificationSystem.addNotification("Lỗi", "Không thể xem chi tiết lương", "error");
        }
    }

    // Approve salary
    async approveSalary(id) {
        try {
            const response = await fetch(`/api/salaries/${id}/approve`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                }
            });

            if (response.ok) {
                notificationSystem.addNotification("Thành công", "Lương đã được phê duyệt", "success");
                this.loadSalaries();
            } else {
                throw new Error("Failed to approve salary");
            }
        } catch (error) {
            console.error("Error approving salary:", error);
            notificationSystem.addNotification("Lỗi", "Không thể phê duyệt lương", "error");
        }
    }

    // Reject salary
    async rejectSalary(id) {
        try {
            const response = await fetch(`/api/salaries/${id}/reject`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                }
            });

            if (response.ok) {
                notificationSystem.addNotification("Thành công", "Lương đã bị từ chối", "success");
                this.loadSalaries();
            } else {
                throw new Error("Failed to reject salary");
            }
        } catch (error) {
            console.error("Error rejecting salary:", error);
            notificationSystem.addNotification("Lỗi", "Không thể từ chối lương", "error");
        }
    }

    // Format currency
    formatCurrency(amount) {
        return new Intl.NumberFormat("vi-VN", {
            style: "currency",
            currency: "VND"
        }).format(amount);
    }

    // Get status badge
    getStatusBadge(status) {
        const badges = {
            "pending": "<span class=\"badge badge-warning\">Chờ phê duyệt</span>",
            "approved": "<span class=\"badge badge-success\">Đã phê duyệt</span>",
            "rejected": "<span class=\"badge badge-danger\">Đã từ chối</span>"
        };
        return badges[status] || status;
    }
}

// Initialize salary management system
const salaryManagement = new SalaryManagement();
window.salaryManagement = salaryManagement; 