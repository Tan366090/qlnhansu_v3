/**
 * @module activities
 * @description Handles activities functionality based on the activities table
 */

// Check authentication first
if (!auth.checkAuth()) {
    window.location.href = "/login.html";
}

class ActivitiesManager {
    constructor() {
        this.currentPage = 1;
        this.itemsPerPage = 10;
        this.totalItems = 0;
        this.init();
    }

    async init() {
        await this.loadActivities();
        await this.loadFilters();
        this.setupEventListeners();
    }

    async loadActivities() {
        try {
            common.showLoading();
            
            const searchQuery = document.getElementById("searchInput").value;
            const startDate = document.getElementById("startDateFilter").value;
            const endDate = document.getElementById("endDateFilter").value;
            const activityType = document.getElementById("activityTypeFilter").value;

            // Build query parameters
            const params = {
                page: this.currentPage,
                limit: this.itemsPerPage
            };
            if (searchQuery) params.search = searchQuery;
            if (startDate) params.start_date = startDate;
            if (endDate) params.end_date = endDate;
            if (activityType) params.activity_type = activityType;

            const response = await api.activities.getAll(params);
            this.totalItems = response.total;

            // Update table
            const tbody = document.querySelector("#activitiesTable tbody");
            tbody.innerHTML = "";
            
            response.data.forEach(activity => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${activity.activity_name}</td>
                    <td>${activity.activity_type}</td>
                    <td>${activity.start_date}</td>
                    <td>${activity.end_date}</td>
                    <td>${activity.description || '-'}</td>
                    <td>${activity.status}</td>
                    <td>
                        <div class="action-buttons">
                            <button onclick="window.activitiesManager.editActivity(${activity.activity_id})" class="btn btn-warning">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="window.activitiesManager.deleteActivity(${activity.activity_id})" class="btn btn-danger">
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
            common.showError("Không thể tải danh sách hoạt động: " + error.message);
        }
    }

    async loadFilters() {
        try {
            // Load activity types for dropdown
            const response = await api.activities.getTypes();
            const typeSelect = document.getElementById("activityTypeFilter");
            typeSelect.innerHTML = '<option value="">Tất cả</option>';
            response.data.forEach(type => {
                const option = document.createElement("option");
                option.value = type;
                option.textContent = type;
                typeSelect.appendChild(option);
            });
        } catch (error) {
            console.error("Error loading filters:", error);
        }
    }

    setupEventListeners() {
        // Search
        document.getElementById("searchBtn").addEventListener("click", () => {
            this.currentPage = 1;
            this.loadActivities();
        });

        document.getElementById("searchInput").addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                this.currentPage = 1;
                this.loadActivities();
            }
        });

        // Filters
        document.getElementById("startDateFilter").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadActivities();
        });

        document.getElementById("endDateFilter").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadActivities();
        });

        document.getElementById("activityTypeFilter").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadActivities();
        });

        // Pagination
        document.getElementById("prevPage").addEventListener("click", () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadActivities();
            }
        });

        document.getElementById("nextPage").addEventListener("click", () => {
            const maxPage = Math.ceil(this.totalItems / this.itemsPerPage);
            if (this.currentPage < maxPage) {
                this.currentPage++;
                this.loadActivities();
            }
        });

        // Add Activity
        document.getElementById("addActivityBtn").addEventListener("click", () => {
            this.showAddActivityModal();
        });
    }

    updatePagination(totalItems) {
        const maxPage = Math.ceil(totalItems / this.itemsPerPage);
        document.getElementById("pageInfo").textContent = `Trang ${this.currentPage} / ${maxPage}`;
        document.getElementById("prevPage").disabled = this.currentPage === 1;
        document.getElementById("nextPage").disabled = this.currentPage === maxPage;
    }

    async showAddActivityModal() {
        try {
            const response = await api.activities.getTypes();
            
            const modal = document.createElement("div");
            modal.className = "modal";
            modal.innerHTML = `
                <div class="modal-content">
                    <h2>Thêm hoạt động</h2>
                    <form id="addActivityForm">
                        <div class="form-group">
                            <label>Tên hoạt động</label>
                            <input type="text" name="activity_name" required>
                        </div>
                        <div class="form-group">
                            <label>Loại hoạt động</label>
                            <select name="activity_type" required>
                                ${response.data.map(type => `
                                    <option value="${type}">${type}</option>
                                `).join('')}
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Ngày bắt đầu</label>
                            <input type="date" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label>Ngày kết thúc</label>
                            <input type="date" name="end_date" required>
                        </div>
                        <div class="form-group">
                            <label>Mô tả</label>
                            <textarea name="description"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Trạng thái</label>
                            <select name="status" required>
                                <option value="pending">Đang chờ</option>
                                <option value="in_progress">Đang thực hiện</option>
                                <option value="completed">Hoàn thành</option>
                                <option value="cancelled">Đã hủy</option>
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
            document.getElementById("addActivityForm").addEventListener("submit", async (e) => {
                e.preventDefault();
                await this.addActivity(new FormData(e.target));
                modal.remove();
            });
        } catch (error) {
            common.showError("Không thể tải danh sách loại hoạt động: " + error.message);
        }
    }

    async addActivity(formData) {
        try {
            common.showLoading();
            
            const data = {
                activity_name: formData.get("activity_name"),
                activity_type: formData.get("activity_type"),
                start_date: formData.get("start_date"),
                end_date: formData.get("end_date"),
                description: formData.get("description"),
                status: formData.get("status")
            };

            await api.activities.create(data);
            common.showSuccess("Thêm hoạt động thành công");
            this.loadActivities();
        } catch (error) {
            common.showError("Không thể thêm hoạt động: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    async editActivity(id) {
        try {
            const response = await api.activities.getById(id);
            const activity = response.data;
            const typesResponse = await api.activities.getTypes();
            
            const modal = document.createElement("div");
            modal.className = "modal";
            modal.innerHTML = `
                <div class="modal-content">
                    <h2>Sửa hoạt động</h2>
                    <form id="editActivityForm">
                        <input type="hidden" name="activity_id" value="${id}">
                        <div class="form-group">
                            <label>Tên hoạt động</label>
                            <input type="text" name="activity_name" value="${activity.activity_name}" required>
                        </div>
                        <div class="form-group">
                            <label>Loại hoạt động</label>
                            <select name="activity_type" required>
                                ${typesResponse.data.map(type => `
                                    <option value="${type}" ${activity.activity_type === type ? 'selected' : ''}>${type}</option>
                                `).join('')}
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Ngày bắt đầu</label>
                            <input type="date" name="start_date" value="${activity.start_date}" required>
                        </div>
                        <div class="form-group">
                            <label>Ngày kết thúc</label>
                            <input type="date" name="end_date" value="${activity.end_date}" required>
                        </div>
                        <div class="form-group">
                            <label>Mô tả</label>
                            <textarea name="description">${activity.description || ''}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Trạng thái</label>
                            <select name="status" required>
                                <option value="pending" ${activity.status === 'pending' ? 'selected' : ''}>Đang chờ</option>
                                <option value="in_progress" ${activity.status === 'in_progress' ? 'selected' : ''}>Đang thực hiện</option>
                                <option value="completed" ${activity.status === 'completed' ? 'selected' : ''}>Hoàn thành</option>
                                <option value="cancelled" ${activity.status === 'cancelled' ? 'selected' : ''}>Đã hủy</option>
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
            document.getElementById("editActivityForm").addEventListener("submit", async (e) => {
                e.preventDefault();
                await this.updateActivity(new FormData(e.target));
                modal.remove();
            });
        } catch (error) {
            common.showError("Không thể tải thông tin hoạt động: " + error.message);
        }
    }

    async updateActivity(formData) {
        try {
            common.showLoading();
            
            const id = formData.get("activity_id");
            const data = {
                activity_name: formData.get("activity_name"),
                activity_type: formData.get("activity_type"),
                start_date: formData.get("start_date"),
                end_date: formData.get("end_date"),
                description: formData.get("description"),
                status: formData.get("status")
            };

            await api.activities.update(id, data);
            common.showSuccess("Cập nhật hoạt động thành công");
            this.loadActivities();
        } catch (error) {
            common.showError("Không thể cập nhật hoạt động: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    async deleteActivity(id) {
        if (confirm("Bạn có chắc chắn muốn xóa hoạt động này?")) {
            try {
                await api.activities.delete(id);
                common.showSuccess("Xóa hoạt động thành công");
                this.loadActivities();
            } catch (error) {
                common.showError("Không thể xóa hoạt động: " + error.message);
            }
        }
    }
}

// Initialize ActivitiesManager
window.activitiesManager = new ActivitiesManager(); 