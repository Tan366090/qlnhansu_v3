// Check authentication first
if (!auth.checkAuth()) {
    window.location.href = "/login.html";
}

class EmployeeListManager {
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
            const status = document.getElementById("statusFilter").value;

            // Build query parameters
            const params = {
                page: this.currentPage,
                limit: this.itemsPerPage
            };
            if (searchQuery) params.search = searchQuery;
            if (departmentId) params.department_id = departmentId;
            if (positionId) params.position_id = positionId;
            if (status) params.status = status;

            const response = await api.users.getAll(params);
            this.totalItems = response.total;

            // Update table
            const tbody = document.querySelector("#employeesTable tbody");
            tbody.innerHTML = "";
            
            response.data.forEach(employee => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${employee.employee_code}</td>
                    <td>${employee.username}</td>
                    <td>${employee.full_name}</td>
                    <td>${employee.department_name}</td>
                    <td>${employee.position_name}</td>
                    <td>${employee.email}</td>
                    <td>${employee.phone}</td>
                    <td>
                        <span class="status-badge ${employee.is_active ? 'active' : 'inactive'}">
                            ${employee.is_active ? 'Đang làm việc' : 'Đã nghỉ việc'}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button onclick="window.employeeListManager.viewEmployee(${employee.user_id})" class="btn btn-info">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="window.employeeListManager.editEmployee(${employee.user_id})" class="btn btn-warning" ${!employee.is_active ? 'disabled' : ''}>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="window.employeeListManager.deleteEmployee(${employee.user_id})" class="btn btn-danger" ${!employee.is_active ? 'disabled' : ''}>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Update pagination
            this.updatePagination(response.total);
            
            common.hideLoading();
        } catch (error) {
            common.hideLoading();
            common.showError("Không thể tải danh sách nhân viên: " + error.message);
        }
    }

    async loadFilters() {
        try {
            // Load departments for dropdown
            const deptResponse = await api.departments.getAll();
            const departmentSelect = document.getElementById("departmentFilter");
            departmentSelect.innerHTML = '<option value="">Tất cả</option>';
            deptResponse.data.forEach(dept => {
                const option = document.createElement("option");
                option.value = dept.department_id;
                option.textContent = dept.name;
                departmentSelect.appendChild(option);
            });

            // Load positions for dropdown
            const posResponse = await api.positions.getAll();
            const positionSelect = document.getElementById("positionFilter");
            positionSelect.innerHTML = '<option value="">Tất cả</option>';
            posResponse.data.forEach(pos => {
                const option = document.createElement("option");
                option.value = pos.position_id;
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

        document.getElementById("statusFilter").addEventListener("change", () => {
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
            window.location.href = "add-employee.html";
        });
    }

    updatePagination(totalItems) {
        const maxPage = Math.ceil(totalItems / this.itemsPerPage);
        document.getElementById("pageInfo").textContent = `Trang ${this.currentPage} / ${maxPage}`;
        document.getElementById("prevPage").disabled = this.currentPage === 1;
        document.getElementById("nextPage").disabled = this.currentPage === maxPage;
    }

    async viewEmployee(id) {
        try {
            const response = await api.users.getById(id);
            const employee = response.data;
            
            const modal = document.createElement("div");
            modal.className = "modal";
            modal.innerHTML = `
                <div class="modal-content">
                    <h2>Chi tiết nhân viên</h2>
                    <div class="employee-details">
                        <div class="detail-row">
                            <label>Mã nhân viên:</label>
                            <span>${employee.employee_code}</span>
                        </div>
                        <div class="detail-row">
                            <label>Tên đăng nhập:</label>
                            <span>${employee.username}</span>
                        </div>
                        <div class="detail-row">
                            <label>Họ và tên:</label>
                            <span>${employee.full_name}</span>
                        </div>
                        <div class="detail-row">
                            <label>Email:</label>
                            <span>${employee.email}</span>
                        </div>
                        <div class="detail-row">
                            <label>Số điện thoại:</label>
                            <span>${employee.phone}</span>
                        </div>
                        <div class="detail-row">
                            <label>Phòng ban:</label>
                            <span>${employee.department_name}</span>
                        </div>
                        <div class="detail-row">
                            <label>Chức vụ:</label>
                            <span>${employee.position_name}</span>
                        </div>
                        <div class="detail-row">
                            <label>Trạng thái:</label>
                            <span class="status-badge ${employee.is_active ? 'active' : 'inactive'}">
                                ${employee.is_active ? 'Đang làm việc' : 'Đã nghỉ việc'}
                            </span>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').remove()">Đóng</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
        } catch (error) {
            common.showError("Không thể tải thông tin nhân viên: " + error.message);
        }
    }

    editEmployee(id) {
        window.location.href = `edit-employee.html?id=${id}`;
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

// Initialize EmployeeListManager
window.employeeListManager = new EmployeeListManager(); 