// Check authentication first
if (!auth.checkAuth()) {
    window.location.href = "/login.html";
}

class PositionsManager {
    constructor() {
        this.currentPage = 1;
        this.itemsPerPage = 10;
        this.totalItems = 0;
        this.init();
    }

    async init() {
        await this.loadPositions();
        await this.loadFilters();
        this.setupEventListeners();
    }

    async loadPositions() {
        try {
            common.showLoading();
            
            const searchQuery = document.getElementById("searchInput").value;
            const departmentId = document.getElementById("departmentFilter").value;
            const status = document.getElementById("statusFilter").value;

            // Build query parameters
            const params = {
                page: this.currentPage,
                limit: this.itemsPerPage
            };
            if (searchQuery) params.search = searchQuery;
            if (departmentId) params.department_id = departmentId;
            if (status) params.status = status;

            const response = await api.positions.getAll(params);
            this.totalItems = response.total;

            // Update table
            const tbody = document.querySelector("#positionsTable tbody");
            tbody.innerHTML = "";
            
            response.data.forEach(position => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${position.name}</td>
                    <td>${position.code}</td>
                    <td>${position.department_name}</td>
                    <td>${position.description || ''}</td>
                    <td>${position.employee_count}</td>
                    <td>
                        <span class="status-badge ${position.status.toLowerCase()}">
                            ${position.status}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button onclick="window.positionsManager.viewPosition(${position.position_id})" class="btn btn-info">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="window.positionsManager.editPosition(${position.position_id})" class="btn btn-warning" ${position.status === 'inactive' ? 'disabled' : ''}>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="window.positionsManager.deletePosition(${position.position_id})" class="btn btn-danger" ${position.status === 'inactive' ? 'disabled' : ''}>
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
            common.showError("Không thể tải danh sách chức vụ: " + error.message);
        }
    }

    async loadFilters() {
        try {
            // Load departments for dropdown
            const response = await api.departments.getAll();
            const departmentSelect = document.getElementById("departmentFilter");
            departmentSelect.innerHTML = '<option value="">Tất cả</option>';
            response.data.forEach(dept => {
                const option = document.createElement("option");
                option.value = dept.department_id;
                option.textContent = dept.name;
                departmentSelect.appendChild(option);
            });
        } catch (error) {
            console.error("Error loading filters:", error);
        }
    }

    setupEventListeners() {
        // Search
        document.getElementById("searchBtn").addEventListener("click", () => {
            this.currentPage = 1;
            this.loadPositions();
        });

        document.getElementById("searchInput").addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                this.currentPage = 1;
                this.loadPositions();
            }
        });

        // Filters
        document.getElementById("departmentFilter").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadPositions();
        });

        document.getElementById("statusFilter").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadPositions();
        });

        // Pagination
        document.getElementById("prevPage").addEventListener("click", () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadPositions();
            }
        });

        document.getElementById("nextPage").addEventListener("click", () => {
            const maxPage = Math.ceil(this.totalItems / this.itemsPerPage);
            if (this.currentPage < maxPage) {
                this.currentPage++;
                this.loadPositions();
            }
        });

        // Add Position
        document.getElementById("addPositionBtn").addEventListener("click", () => {
            this.showAddPositionModal();
        });
    }

    updatePagination(totalItems) {
        const maxPage = Math.ceil(totalItems / this.itemsPerPage);
        document.getElementById("pageInfo").textContent = `Trang ${this.currentPage} / ${maxPage}`;
        document.getElementById("prevPage").disabled = this.currentPage === 1;
        document.getElementById("nextPage").disabled = this.currentPage === maxPage;
    }

    async showAddPositionModal() {
        try {
            // Load departments for dropdown
            const response = await api.departments.getAll();
            
            const modal = document.createElement("div");
            modal.className = "modal";
            modal.innerHTML = `
                <div class="modal-content">
                    <h2>Thêm chức vụ</h2>
                    <form id="addPositionForm">
                        <div class="form-group">
                            <label>Tên chức vụ</label>
                            <input type="text" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Mã chức vụ</label>
                            <input type="text" name="code" required>
                        </div>
                        <div class="form-group">
                            <label>Phòng ban</label>
                            <select name="department_id" required>
                                ${response.data.map(dept => `
                                    <option value="${dept.department_id}">${dept.name}</option>
                                `).join('')}
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Mô tả</label>
                            <textarea name="description"></textarea>
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
            document.getElementById("addPositionForm").addEventListener("submit", async (e) => {
                e.preventDefault();
                await this.addPosition(new FormData(e.target));
                modal.remove();
            });
        } catch (error) {
            common.showError("Không thể tải danh sách phòng ban: " + error.message);
        }
    }

    async addPosition(formData) {
        try {
            common.showLoading();
            
            const data = {
                name: formData.get("name"),
                code: formData.get("code"),
                department_id: formData.get("department_id"),
                description: formData.get("description")
            };

            await api.positions.create(data);
            common.showSuccess("Thêm chức vụ thành công");
            this.loadPositions();
        } catch (error) {
            common.showError("Không thể thêm chức vụ: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    async editPosition(id) {
        try {
            const response = await api.positions.getById(id);
            const position = response.data;
            
            // Load departments for dropdown
            const departmentsResponse = await api.departments.getAll();
            
            const modal = document.createElement("div");
            modal.className = "modal";
            modal.innerHTML = `
                <div class="modal-content">
                    <h2>Sửa chức vụ</h2>
                    <form id="editPositionForm">
                        <input type="hidden" name="position_id" value="${id}">
                        <div class="form-group">
                            <label>Tên chức vụ</label>
                            <input type="text" name="name" value="${position.name}" required>
                        </div>
                        <div class="form-group">
                            <label>Mã chức vụ</label>
                            <input type="text" name="code" value="${position.code}" required>
                        </div>
                        <div class="form-group">
                            <label>Phòng ban</label>
                            <select name="department_id" required>
                                ${departmentsResponse.data.map(dept => `
                                    <option value="${dept.department_id}" ${dept.department_id === position.department_id ? 'selected' : ''}>
                                        ${dept.name}
                                    </option>
                                `).join('')}
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Mô tả</label>
                            <textarea name="description">${position.description || ''}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Trạng thái</label>
                            <select name="status">
                                <option value="active" ${position.status === 'active' ? 'selected' : ''}>Hoạt động</option>
                                <option value="inactive" ${position.status === 'inactive' ? 'selected' : ''}>Không hoạt động</option>
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
            document.getElementById("editPositionForm").addEventListener("submit", async (e) => {
                e.preventDefault();
                await this.updatePosition(new FormData(e.target));
                modal.remove();
            });
        } catch (error) {
            common.showError("Không thể tải thông tin chức vụ: " + error.message);
        }
    }

    async updatePosition(formData) {
        try {
            common.showLoading();
            
            const id = formData.get("position_id");
            const data = {
                name: formData.get("name"),
                code: formData.get("code"),
                department_id: formData.get("department_id"),
                description: formData.get("description"),
                status: formData.get("status")
            };

            await api.positions.update(id, data);
            common.showSuccess("Cập nhật chức vụ thành công");
            this.loadPositions();
        } catch (error) {
            common.showError("Không thể cập nhật chức vụ: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    async deletePosition(id) {
        if (confirm("Bạn có chắc chắn muốn xóa chức vụ này?")) {
            try {
                await api.positions.delete(id);
                common.showSuccess("Xóa chức vụ thành công");
                this.loadPositions();
            } catch (error) {
                common.showError("Không thể xóa chức vụ: " + error.message);
            }
        }
    }

    async viewPosition(id) {
        try {
            const response = await api.positions.getById(id);
            const position = response.data;
            
            const modal = document.createElement("div");
            modal.className = "modal";
            modal.innerHTML = `
                <div class="modal-content">
                    <h2>Chi tiết chức vụ</h2>
                    <div class="position-details">
                        <div class="detail-row">
                            <label>Tên chức vụ:</label>
                            <span>${position.name}</span>
                        </div>
                        <div class="detail-row">
                            <label>Mã chức vụ:</label>
                            <span>${position.code}</span>
                        </div>
                        <div class="detail-row">
                            <label>Phòng ban:</label>
                            <span>${position.department_name}</span>
                        </div>
                        <div class="detail-row">
                            <label>Mô tả:</label>
                            <span>${position.description || 'Không có'}</span>
                        </div>
                        <div class="detail-row">
                            <label>Số nhân viên:</label>
                            <span>${position.employee_count}</span>
                        </div>
                        <div class="detail-row">
                            <label>Trạng thái:</label>
                            <span class="status-badge ${position.status.toLowerCase()}">${position.status}</span>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').remove()">Đóng</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
        } catch (error) {
            common.showError("Không thể tải thông tin chức vụ: " + error.message);
        }
    }
}

// Initialize PositionsManager
window.positionsManager = new PositionsManager(); 