// Check authentication first
if (!auth.checkAuth()) {
    window.location.href = "/login.html";
}

class AttendanceManager {
    constructor() {
        this.currentPage = 1;
        this.itemsPerPage = 10;
        this.totalItems = 0;
        this.init();
    }

    async init() {
        await this.loadAttendance();
        await this.loadFilters();
        this.setupEventListeners();
    }

    async loadAttendance() {
        try {
            common.showLoading();
            
            const searchQuery = document.getElementById("searchInput").value;
            const startDate = document.getElementById("startDateFilter").value;
            const endDate = document.getElementById("endDateFilter").value;
            const departmentId = document.getElementById("departmentFilter").value;
            const attendanceSymbol = document.getElementById("symbolFilter").value;

            // Build query parameters
            const params = {
                page: this.currentPage,
                limit: this.itemsPerPage
            };
            if (searchQuery) params.search = searchQuery;
            if (startDate) params.start_date = startDate;
            if (endDate) params.end_date = endDate;
            if (departmentId) params.department_id = departmentId;
            if (attendanceSymbol) params.attendance_symbol = attendanceSymbol;

            const response = await api.attendance.getAll(params);
            this.totalItems = response.total;

            // Update table
            const tbody = document.querySelector("#attendanceTable tbody");
            tbody.innerHTML = "";
            
            response.data.forEach(record => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${record.user_name}</td>
                    <td>${record.department_name}</td>
                    <td>${record.position_name}</td>
                    <td>${record.attendance_date}</td>
                    <td>${record.attendance_symbol}</td>
                    <td>${record.notes || '-'}</td>
                    <td>
                        <div class="action-buttons">
                            <button onclick="window.attendanceManager.editAttendance(${record.attendance_id})" class="btn btn-warning">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="window.attendanceManager.deleteAttendance(${record.attendance_id})" class="btn btn-danger">
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
            common.showError("Không thể tải danh sách chấm công: " + error.message);
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
            this.loadAttendance();
        });

        document.getElementById("searchInput").addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                this.currentPage = 1;
                this.loadAttendance();
            }
        });

        // Filters
        document.getElementById("startDateFilter").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadAttendance();
        });

        document.getElementById("endDateFilter").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadAttendance();
        });

        document.getElementById("departmentFilter").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadAttendance();
        });

        document.getElementById("symbolFilter").addEventListener("change", () => {
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
            const maxPage = Math.ceil(this.totalItems / this.itemsPerPage);
            if (this.currentPage < maxPage) {
                this.currentPage++;
                this.loadAttendance();
            }
        });

        // Add Attendance
        document.getElementById("addAttendanceBtn").addEventListener("click", () => {
            this.showAddAttendanceModal();
        });
    }

    updatePagination(totalItems) {
        const maxPage = Math.ceil(totalItems / this.itemsPerPage);
        document.getElementById("pageInfo").textContent = `Trang ${this.currentPage} / ${maxPage}`;
        document.getElementById("prevPage").disabled = this.currentPage === 1;
        document.getElementById("nextPage").disabled = this.currentPage === maxPage;
    }

    async showAddAttendanceModal() {
        try {
            // Load users for dropdown
            const response = await api.users.getAll();
            
            const modal = document.createElement("div");
            modal.className = "modal";
            modal.innerHTML = `
                <div class="modal-content">
                    <h2>Thêm chấm công</h2>
                    <form id="addAttendanceForm">
                        <div class="form-group">
                            <label>Nhân viên</label>
                            <select name="user_id" required>
                                ${response.data.map(user => `
                                    <option value="${user.user_id}">${user.username} - ${user.department_name}</option>
                                `).join('')}
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Ngày chấm công</label>
                            <input type="date" name="attendance_date" required>
                        </div>
                        <div class="form-group">
                            <label>Ký hiệu</label>
                            <select name="attendance_symbol" required>
                                <option value="P">Có mặt (P)</option>
                                <option value="A">Vắng mặt (A)</option>
                                <option value="L">Nghỉ phép (L)</option>
                                <option value="H">Nghỉ lễ (H)</option>
                                <option value="E">Đi muộn (E)</option>
                                <option value="L">Về sớm (L)</option>
                            </select>
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
            document.getElementById("addAttendanceForm").addEventListener("submit", async (e) => {
                e.preventDefault();
                await this.addAttendance(new FormData(e.target));
                modal.remove();
            });
        } catch (error) {
            common.showError("Không thể tải danh sách nhân viên: " + error.message);
        }
    }

    async addAttendance(formData) {
        try {
            common.showLoading();
            
            const data = {
                user_id: formData.get("user_id"),
                attendance_date: formData.get("attendance_date"),
                attendance_symbol: formData.get("attendance_symbol"),
                notes: formData.get("notes")
            };

            await api.attendance.create(data);
            common.showSuccess("Thêm chấm công thành công");
            this.loadAttendance();
        } catch (error) {
            common.showError("Không thể thêm chấm công: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    async editAttendance(id) {
        try {
            const response = await api.attendance.getById(id);
            const record = response.data;
            
            const modal = document.createElement("div");
            modal.className = "modal";
            modal.innerHTML = `
                <div class="modal-content">
                    <h2>Sửa chấm công</h2>
                    <form id="editAttendanceForm">
                        <input type="hidden" name="attendance_id" value="${id}">
                        <div class="form-group">
                            <label>Nhân viên</label>
                            <input type="text" value="${record.user_name}" disabled>
                        </div>
                        <div class="form-group">
                            <label>Ngày chấm công</label>
                            <input type="date" name="attendance_date" value="${record.attendance_date}" required>
                        </div>
                        <div class="form-group">
                            <label>Ký hiệu</label>
                            <select name="attendance_symbol" required>
                                <option value="P" ${record.attendance_symbol === 'P' ? 'selected' : ''}>Có mặt (P)</option>
                                <option value="A" ${record.attendance_symbol === 'A' ? 'selected' : ''}>Vắng mặt (A)</option>
                                <option value="L" ${record.attendance_symbol === 'L' ? 'selected' : ''}>Nghỉ phép (L)</option>
                                <option value="H" ${record.attendance_symbol === 'H' ? 'selected' : ''}>Nghỉ lễ (H)</option>
                                <option value="E" ${record.attendance_symbol === 'E' ? 'selected' : ''}>Đi muộn (E)</option>
                                <option value="L" ${record.attendance_symbol === 'L' ? 'selected' : ''}>Về sớm (L)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Ghi chú</label>
                            <textarea name="notes">${record.notes || ''}</textarea>
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
            document.getElementById("editAttendanceForm").addEventListener("submit", async (e) => {
                e.preventDefault();
                await this.updateAttendance(new FormData(e.target));
                modal.remove();
            });
        } catch (error) {
            common.showError("Không thể tải thông tin chấm công: " + error.message);
        }
    }

    async updateAttendance(formData) {
        try {
            common.showLoading();
            
            const id = formData.get("attendance_id");
            const data = {
                attendance_date: formData.get("attendance_date"),
                attendance_symbol: formData.get("attendance_symbol"),
                notes: formData.get("notes")
            };

            await api.attendance.update(id, data);
            common.showSuccess("Cập nhật chấm công thành công");
            this.loadAttendance();
        } catch (error) {
            common.showError("Không thể cập nhật chấm công: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    async deleteAttendance(id) {
        if (confirm("Bạn có chắc chắn muốn xóa bản ghi chấm công này?")) {
            try {
                await api.attendance.delete(id);
                common.showSuccess("Xóa chấm công thành công");
                this.loadAttendance();
            } catch (error) {
                common.showError("Không thể xóa chấm công: " + error.message);
            }
        }
    }
}

// Initialize AttendanceManager
window.attendanceManager = new AttendanceManager(); 