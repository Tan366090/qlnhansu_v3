// Check authentication first
if (!auth.checkAuth()) {
    window.location.href = "/login.html";
}

class SalariesManager {
    constructor() {
        this.currentPage = 1;
        this.itemsPerPage = 10;
        this.totalItems = 0;
        this.init();
    }

    async init() {
        await this.loadSalaries();
        await this.loadFilters();
        this.setupEventListeners();
    }

    async loadSalaries() {
        try {
            common.showLoading();
            
            const searchQuery = document.getElementById("searchInput").value;
            const startDate = document.getElementById("startDateFilter").value;
            const endDate = document.getElementById("endDateFilter").value;
            const departmentId = document.getElementById("departmentFilter").value;
            const status = document.getElementById("statusFilter").value;

            // Build query parameters
            const params = {
                page: this.currentPage,
                limit: this.itemsPerPage
            };
            if (searchQuery) params.search = searchQuery;
            if (startDate) params.start_date = startDate;
            if (endDate) params.end_date = endDate;
            if (departmentId) params.department_id = departmentId;
            if (status) params.status = status;

            const response = await api.salaries.getAll(params);
            this.totalItems = response.total;

            // Update table
            const tbody = document.querySelector("#salariesTable tbody");
            tbody.innerHTML = "";
            
            response.data.forEach(salary => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${salary.user_name}</td>
                    <td>${salary.department_name}</td>
                    <td>${salary.position_name}</td>
                    <td>${this.formatCurrency(salary.basic_salary)}</td>
                    <td>${this.formatCurrency(salary.allowance)}</td>
                    <td>${this.formatCurrency(salary.bonus)}</td>
                    <td>${this.formatCurrency(salary.total_salary)}</td>
                    <td>${salary.payment_date}</td>
                    <td>
                        <span class="status-badge ${salary.status.toLowerCase()}">
                            ${salary.status}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button onclick="window.salariesManager.viewSalary(${salary.salary_id})" class="btn btn-info">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="window.salariesManager.editSalary(${salary.salary_id})" class="btn btn-warning" ${salary.status === 'paid' ? 'disabled' : ''}>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="window.salariesManager.deleteSalary(${salary.salary_id})" class="btn btn-danger" ${salary.status === 'paid' ? 'disabled' : ''}>
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
            common.showError("Không thể tải danh sách lương: " + error.message);
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
            this.loadSalaries();
        });

        document.getElementById("searchInput").addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                this.currentPage = 1;
                this.loadSalaries();
            }
        });

        // Filters
        document.getElementById("startDateFilter").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadSalaries();
        });

        document.getElementById("endDateFilter").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadSalaries();
        });

        document.getElementById("departmentFilter").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadSalaries();
        });

        document.getElementById("statusFilter").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadSalaries();
        });

        // Pagination
        document.getElementById("prevPage").addEventListener("click", () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadSalaries();
            }
        });

        document.getElementById("nextPage").addEventListener("click", () => {
            const maxPage = Math.ceil(this.totalItems / this.itemsPerPage);
            if (this.currentPage < maxPage) {
                this.currentPage++;
                this.loadSalaries();
            }
        });

        // Add Salary
        document.getElementById("addSalaryBtn").addEventListener("click", () => {
            this.showAddSalaryModal();
        });
    }

    updatePagination(totalItems) {
        const maxPage = Math.ceil(totalItems / this.itemsPerPage);
        document.getElementById("pageInfo").textContent = `Trang ${this.currentPage} / ${maxPage}`;
        document.getElementById("prevPage").disabled = this.currentPage === 1;
        document.getElementById("nextPage").disabled = this.currentPage === maxPage;
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }

    async showAddSalaryModal() {
        try {
            // Load users for dropdown
            const response = await api.users.getAll();
            
            const modal = document.createElement("div");
            modal.className = "modal";
            modal.innerHTML = `
                <div class="modal-content">
                    <h2>Thêm lương</h2>
                    <form id="addSalaryForm">
                        <div class="form-group">
                            <label>Nhân viên</label>
                            <select name="user_id" required>
                                ${response.data.map(user => `
                                    <option value="${user.user_id}">${user.username} - ${user.department_name}</option>
                                `).join('')}
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Lương cơ bản</label>
                            <input type="number" name="basic_salary" required>
                        </div>
                        <div class="form-group">
                            <label>Phụ cấp</label>
                            <input type="number" name="allowance" value="0">
                        </div>
                        <div class="form-group">
                            <label>Thưởng</label>
                            <input type="number" name="bonus" value="0">
                        </div>
                        <div class="form-group">
                            <label>Ngày thanh toán</label>
                            <input type="date" name="payment_date" required>
                        </div>
                        <div class="form-group">
                            <label>Ghi chú</label>
                            <textarea name="notes"></textarea>
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
            document.getElementById("addSalaryForm").addEventListener("submit", async (e) => {
                e.preventDefault();
                await this.addSalary(new FormData(e.target));
                modal.remove();
            });
        } catch (error) {
            common.showError("Không thể tải danh sách nhân viên: " + error.message);
        }
    }

    async addSalary(formData) {
        try {
            common.showLoading();
            
            const data = {
                user_id: formData.get("user_id"),
                basic_salary: formData.get("basic_salary"),
                allowance: formData.get("allowance"),
                bonus: formData.get("bonus"),
                payment_date: formData.get("payment_date"),
                notes: formData.get("notes")
            };

            await api.salaries.create(data);
            common.showSuccess("Thêm lương thành công");
            this.loadSalaries();
        } catch (error) {
            common.showError("Không thể thêm lương: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    async editSalary(id) {
        try {
            const response = await api.salaries.getById(id);
            const salary = response.data;
            
            const modal = document.createElement("div");
            modal.className = "modal";
            modal.innerHTML = `
                <div class="modal-content">
                    <h2>Sửa lương</h2>
                    <form id="editSalaryForm">
                        <input type="hidden" name="salary_id" value="${id}">
                        <div class="form-group">
                            <label>Nhân viên</label>
                            <input type="text" value="${salary.user_name}" disabled>
                        </div>
                        <div class="form-group">
                            <label>Lương cơ bản</label>
                            <input type="number" name="basic_salary" value="${salary.basic_salary}" required>
                        </div>
                        <div class="form-group">
                            <label>Phụ cấp</label>
                            <input type="number" name="allowance" value="${salary.allowance}">
                        </div>
                        <div class="form-group">
                            <label>Thưởng</label>
                            <input type="number" name="bonus" value="${salary.bonus}">
                        </div>
                        <div class="form-group">
                            <label>Ngày thanh toán</label>
                            <input type="date" name="payment_date" value="${salary.payment_date}" required>
                        </div>
                        <div class="form-group">
                            <label>Ghi chú</label>
                            <textarea name="notes">${salary.notes || ''}</textarea>
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
            document.getElementById("editSalaryForm").addEventListener("submit", async (e) => {
                e.preventDefault();
                await this.updateSalary(new FormData(e.target));
                modal.remove();
            });
        } catch (error) {
            common.showError("Không thể tải thông tin lương: " + error.message);
        }
    }

    async updateSalary(formData) {
        try {
            common.showLoading();
            
            const id = formData.get("salary_id");
            const data = {
                basic_salary: formData.get("basic_salary"),
                allowance: formData.get("allowance"),
                bonus: formData.get("bonus"),
                payment_date: formData.get("payment_date"),
                notes: formData.get("notes")
            };

            await api.salaries.update(id, data);
            common.showSuccess("Cập nhật lương thành công");
            this.loadSalaries();
        } catch (error) {
            common.showError("Không thể cập nhật lương: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    async deleteSalary(id) {
        if (confirm("Bạn có chắc chắn muốn xóa bản ghi lương này?")) {
            try {
                await api.salaries.delete(id);
                common.showSuccess("Xóa lương thành công");
                this.loadSalaries();
            } catch (error) {
                common.showError("Không thể xóa lương: " + error.message);
            }
        }
    }

    async viewSalary(id) {
        try {
            const response = await api.salaries.getById(id);
            const salary = response.data;
            
            const modal = document.createElement("div");
            modal.className = "modal";
            modal.innerHTML = `
                <div class="modal-content">
                    <h2>Chi tiết lương</h2>
                    <div class="salary-details">
                        <div class="detail-row">
                            <label>Nhân viên:</label>
                            <span>${salary.user_name}</span>
                        </div>
                        <div class="detail-row">
                            <label>Phòng ban:</label>
                            <span>${salary.department_name}</span>
                        </div>
                        <div class="detail-row">
                            <label>Chức vụ:</label>
                            <span>${salary.position_name}</span>
                        </div>
                        <div class="detail-row">
                            <label>Lương cơ bản:</label>
                            <span>${this.formatCurrency(salary.basic_salary)}</span>
                        </div>
                        <div class="detail-row">
                            <label>Phụ cấp:</label>
                            <span>${this.formatCurrency(salary.allowance)}</span>
                        </div>
                        <div class="detail-row">
                            <label>Thưởng:</label>
                            <span>${this.formatCurrency(salary.bonus)}</span>
                        </div>
                        <div class="detail-row">
                            <label>Tổng lương:</label>
                            <span>${this.formatCurrency(salary.total_salary)}</span>
                        </div>
                        <div class="detail-row">
                            <label>Ngày thanh toán:</label>
                            <span>${salary.payment_date}</span>
                        </div>
                        <div class="detail-row">
                            <label>Trạng thái:</label>
                            <span class="status-badge ${salary.status.toLowerCase()}">${salary.status}</span>
                        </div>
                        ${salary.notes ? `
                            <div class="detail-row">
                                <label>Ghi chú:</label>
                                <span>${salary.notes}</span>
                            </div>
                        ` : ''}
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').remove()">Đóng</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
        } catch (error) {
            common.showError("Không thể tải thông tin lương: " + error.message);
        }
    }
}

// Initialize SalariesManager
window.salariesManager = new SalariesManager(); 