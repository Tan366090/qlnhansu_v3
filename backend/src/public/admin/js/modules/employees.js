// Check authentication first
if (!auth.checkAuth()) {
    window.location.href = "/login.html";
}

class EmployeeManager {
    constructor() {
        this.currentPage = 1;
        this.itemsPerPage = 10;
        this.totalItems = 0;
        this.init();
    }

    async init() {
        await this.loadEmployees();
        await this.loadFilters();
        this.setupEventListeners();
    }

    async loadEmployees() {
        try {
            common.showLoading();
            
            const searchQuery = document.getElementById("searchInput").value;
            const departmentId = document.getElementById("departmentFilter").value;
            const positionId = document.getElementById("positionFilter").value;

            // Build query parameters
            const params = {};
            if (searchQuery) params.search = searchQuery;
            if (departmentId) params.department_id = departmentId;
            if (positionId) params.position_id = positionId;

            const response = await api.users.getAll(params);
            this.totalItems = response.length;

            // Update table
            const tbody = document.querySelector("#employeeTable tbody");
            tbody.innerHTML = "";
            
            response.forEach(emp => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${emp.employee_code}</td>
                    <td>${emp.username}</td>
                    <td>${emp.department_name}</td>
                    <td>${emp.position_name}</td>
                    <td>${emp.email}</td>
                    <td>${emp.phone || '-'}</td>
                    <td>
                        <span class="status-badge ${emp.is_active ? "active" : "inactive"}">
                            ${emp.is_active ? "Đang làm việc" : "Đã nghỉ việc"}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button onclick="window.employeeManager.viewEmployee(${emp.user_id})" class="btn btn-info">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="window.employeeManager.editEmployee(${emp.user_id})" class="btn btn-warning">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="window.employeeManager.deleteEmployee(${emp.user_id})" class="btn btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Update pagination
            this.updatePagination(response.length);
            
            common.hideLoading();
        } catch (error) {
            common.hideLoading();
            common.showError("Không thể tải danh sách nhân viên: " + error.message);
        }
    }

    async loadFilters() {
        try {
            // Load departments
            const departments = await api.departments.getAll();
            const departmentSelect = document.getElementById("departmentFilter");
            departments.forEach(dept => {
                const option = document.createElement("option");
                option.value = dept.id;
                option.textContent = dept.name;
                departmentSelect.appendChild(option);
            });

            // Load positions
            const positions = await api.positions.getAll();
            const positionSelect = document.getElementById("positionFilter");
            positions.forEach(pos => {
                const option = document.createElement("option");
                option.value = pos.id;
                option.textContent = pos.name;
                positionSelect.appendChild(option);
            });
        } catch (error) {
            console.error("Error loading filters:", error);
        }
    }

    setupEventListeners() {
        // Search
        document.getElementById("searchBtn").addEventListener("click", () => {
            this.currentPage = 1;
            this.loadEmployees();
        });

        document.getElementById("searchInput").addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                this.currentPage = 1;
                this.loadEmployees();
            }
        });

        // Filters
        document.getElementById("departmentFilter").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadEmployees();
        });

        document.getElementById("positionFilter").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadEmployees();
        });

        // Pagination
        document.getElementById("prevPage").addEventListener("click", () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadEmployees();
            }
        });

        document.getElementById("nextPage").addEventListener("click", () => {
            const maxPage = Math.ceil(this.totalItems / this.itemsPerPage);
            if (this.currentPage < maxPage) {
                this.currentPage++;
                this.loadEmployees();
            }
        });

        // Add Employee
        document.getElementById("addEmployeeBtn").addEventListener("click", () => {
            window.location.href = "add.html";
        });
    }

    updatePagination(totalItems) {
        const maxPage = Math.ceil(totalItems / this.itemsPerPage);
        document.getElementById("pageInfo").textContent = `Trang ${this.currentPage} / ${maxPage}`;
        document.getElementById("prevPage").disabled = this.currentPage === 1;
        document.getElementById("nextPage").disabled = this.currentPage === maxPage;
    }

    async updateStatistics() {
        try {
            const [users, departments, positions] = await Promise.all([
                api.users.getAll(),
                api.departments.getAll(),
                api.positions.getAll()
            ]);

            document.getElementById("totalEmployees").textContent = users.length;
            document.getElementById("totalDepartments").textContent = departments.length;
            document.getElementById("totalPositions").textContent = positions.length;
            document.getElementById("activeEmployees").textContent = 
                users.filter(user => user.is_active).length;
        } catch (error) {
            common.showError("Không thể cập nhật thống kê: " + error.message);
        }
    }

    viewEmployee(id) {
        window.location.href = `view.html?id=${id}`;
    }

    async editEmployee(id) {
        try {
            const user = await api.users.getById(id);
            // Redirect to edit page with user data
            window.location.href = `edit.html?id=${id}`;
        } catch (error) {
            common.showError("Không thể tải thông tin nhân viên: " + error.message);
        }
    }

    async deleteEmployee(id) {
        if (confirm("Bạn có chắc chắn muốn xóa nhân viên này?")) {
            try {
                await api.users.delete(id);
                common.showSuccess("Xóa nhân viên thành công");
                this.loadEmployees();
            } catch (error) {
                common.showError("Không thể xóa nhân viên: " + error.message);
            }
        }
    }
}

// Initialize EmployeeManager
window.employeeManager = new EmployeeManager(); 