// Check authentication first
if (!auth.checkAuth()) {
    window.location.href = "/login.html";
}

class DepartmentsManager {
    constructor() {
        this.currentPage = 1;
        this.itemsPerPage = 10;
        this.totalItems = 0;
        this.init();
    }

    async init() {
        await this.loadDepartments();
        this.setupEventListeners();
    }

    async loadDepartments() {
        try {
            common.showLoading();
            
            const searchQuery = document.getElementById("searchInput").value;
            const status = document.getElementById("statusFilter").value;

            // Build query parameters
            const params = {
                page: this.currentPage,
                limit: this.itemsPerPage
            };
            if (searchQuery) params.search = searchQuery;
            if (status) params.status = status;

            const response = await api.departments.getAll(params);
            this.totalItems = response.total;

            // Update table
            const tbody = document.querySelector("#departmentsTable tbody");
            tbody.innerHTML = "";
            
            response.data.forEach(dept => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${dept.name}</td>
                    <td>${dept.code}</td>
                    <td>${dept.description || ''}</td>
                    <td>${dept.manager_name || 'Chưa có'}</td>
                    <td>${dept.employee_count}</td>
                    <td>
                        <span class="status-badge ${dept.status.toLowerCase()}">
                            ${dept.status}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button onclick="window.departmentsManager.viewDepartment(${dept.department_id})" class="btn btn-info">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="window.departmentsManager.editDepartment(${dept.department_id})" class="btn btn-warning" ${dept.status === 'inactive' ? 'disabled' : ''}>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="window.departmentsManager.deleteDepartment(${dept.department_id})" class="btn btn-danger" ${dept.status === 'inactive' ? 'disabled' : ''}>
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
            common.showError("Không thể tải danh sách phòng ban: " + error.message);
        }
    }

    setupEventListeners() {
        // Search
        document.getElementById("searchBtn").addEventListener("click", () => {
            this.currentPage = 1;
            this.loadDepartments();
        });

        document.getElementById("searchInput").addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                this.currentPage = 1;
                this.loadDepartments();
            }
        });

        // Filters
        document.getElementById("statusFilter").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadDepartments();
        });

        // Pagination
        document.getElementById("prevPage").addEventListener("click", () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadDepartments();
            }
        });

        document.getElementById("nextPage").addEventListener("click", () => {
            const maxPage = Math.ceil(this.totalItems / this.itemsPerPage);
            if (this.currentPage < maxPage) {
                this.currentPage++;
                this.loadDepartments();
            }
        });

        // Add Department
        document.getElementById("addDepartmentBtn").addEventListener("click", () => {
            this.showAddDepartmentModal();
        });
    }

    updatePagination(totalItems) {
        const maxPage = Math.ceil(totalItems / this.itemsPerPage);
        document.getElementById("pageInfo").textContent = `Trang ${this.currentPage} / ${maxPage}`;
        document.getElementById("prevPage").disabled = this.currentPage === 1;
        document.getElementById("nextPage").disabled = this.currentPage === maxPage;
    }

    async showAddDepartmentModal() {
        try {
            // Load managers for dropdown
            const response = await api.users.getAll({ role: 'manager' });
            
            const modal = document.createElement("div");
            modal.className = "modal";
            modal.innerHTML = `
                <div class="modal-content">
                    <h2>Thêm phòng ban</h2>
                    <form id="addDepartmentForm">
                        <div class="form-group">
                            <label>Tên phòng ban</label>
                            <input type="text" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Mã phòng ban</label>
                            <input type="text" name="code" required>
                        </div>
                        <div class="form-group">
                            <label>Mô tả</label>
                            <textarea name="description"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Trưởng phòng</label>
                            <select name="manager_id">
                                <option value="">Chọn trưởng phòng</option>
                                ${response.data.map(user => `
                                    <option value="${user.user_id}">${user.username}</option>
                                `).join('')}
                            </select>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Lưu</button>
                            <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').remove()">Hủy</button>
                        </div>
                    </form>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Handle form submission
            document.getElementById("addDepartmentForm").addEventListener("submit", async (e) => {
                e.preventDefault();
                await this.addDepartment(new FormData(e.target));
                modal.remove();
            });
        } catch (error) {
            common.showError("Không thể tải danh sách trưởng phòng: " + error.message);
        }
    }

    async addDepartment(formData) {
        try {
            common.showLoading();
            
            const data = {
                name: formData.get("name"),
                code: formData.get("code"),
                description: formData.get("description"),
                manager_id: formData.get("manager_id") || null
            };

            await api.departments.create(data);
            common.showSuccess("Thêm phòng ban thành công");
            this.loadDepartments();
        } catch (error) {
            common.showError("Không thể thêm phòng ban: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    async editDepartment(id) {
        try {
            const response = await api.departments.getById(id);
            const dept = response.data;
            
            // Load managers for dropdown
            const managersResponse = await api.users.getAll({ role: 'manager' });
            
            const modal = document.createElement("div");
            modal.className = "modal";
            modal.innerHTML = `
                <div class="modal-content">
                    <h2>Sửa phòng ban</h2>
                    <form id="editDepartmentForm">
                        <input type="hidden" name="department_id" value="${id}">
                        <div class="form-group">
                            <label>Tên phòng ban</label>
                            <input type="text" name="name" value="${dept.name}" required>
                        </div>
                        <div class="form-group">
                            <label>Mã phòng ban</label>
                            <input type="text" name="code" value="${dept.code}" required>
                        </div>
                        <div class="form-group">
                            <label>Mô tả</label>
                            <textarea name="description">${dept.description || ''}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Trưởng phòng</label>
                            <select name="manager_id">
                                <option value="">Chọn trưởng phòng</option>
                                ${managersResponse.data.map(user => `
                                    <option value="${user.user_id}" ${user.user_id === dept.manager_id ? 'selected' : ''}>
                                        ${user.username}
                                    </option>
                                `).join('')}
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Trạng thái</label>
                            <select name="status">
                                <option value="active" ${dept.status === 'active' ? 'selected' : ''}>Hoạt động</option>
                                <option value="inactive" ${dept.status === 'inactive' ? 'selected' : ''}>Không hoạt động</option>
                            </select>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Lưu</button>
                            <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').remove()">Hủy</button>
                        </div>
                    </form>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Handle form submission
            document.getElementById("editDepartmentForm").addEventListener("submit", async (e) => {
                e.preventDefault();
                await this.updateDepartment(new FormData(e.target));
                modal.remove();
            });
        } catch (error) {
            common.showError("Không thể tải thông tin phòng ban: " + error.message);
        }
    }

    async updateDepartment(formData) {
        try {
            common.showLoading();
            
            const id = formData.get("department_id");
            const data = {
                name: formData.get("name"),
                code: formData.get("code"),
                description: formData.get("description"),
                manager_id: formData.get("manager_id") || null,
                status: formData.get("status")
            };

            await api.departments.update(id, data);
            common.showSuccess("Cập nhật phòng ban thành công");
            this.loadDepartments();
        } catch (error) {
            common.showError("Không thể cập nhật phòng ban: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    async deleteDepartment(id) {
        if (confirm("Bạn có chắc chắn muốn xóa phòng ban này?")) {
            try {
                await api.departments.delete(id);
                common.showSuccess("Xóa phòng ban thành công");
                this.loadDepartments();
            } catch (error) {
                common.showError("Không thể xóa phòng ban: " + error.message);
            }
        }
    }

    async viewDepartment(id) {
        try {
            const response = await api.departments.getById(id);
            const dept = response.data;
            
            const modal = document.createElement("div");
            modal.className = "modal";
            modal.innerHTML = `
                <div class="modal-content">
                    <h2>Chi tiết phòng ban</h2>
                    <div class="department-details">
                        <div class="detail-row">
                            <label>Tên phòng ban:</label>
                            <span>${dept.name}</span>
                        </div>
                        <div class="detail-row">
                            <label>Mã phòng ban:</label>
                            <span>${dept.code}</span>
                        </div>
                        <div class="detail-row">
                            <label>Mô tả:</label>
                            <span>${dept.description || 'Không có'}</span>
                        </div>
                        <div class="detail-row">
                            <label>Trưởng phòng:</label>
                            <span>${dept.manager_name || 'Chưa có'}</span>
                        </div>
                        <div class="detail-row">
                            <label>Số nhân viên:</label>
                            <span>${dept.employee_count}</span>
                        </div>
                        <div class="detail-row">
                            <label>Trạng thái:</label>
                            <span class="status-badge ${dept.status.toLowerCase()}">${dept.status}</span>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').remove()">Đóng</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
        } catch (error) {
            common.showError("Không thể tải thông tin phòng ban: " + error.message);
        }
    }
}

// Initialize DepartmentsManager
window.departmentsManager = new DepartmentsManager(); 