// Check authentication first
if (!auth.checkAuth()) {
    window.location.href = "/login.html";
}

class LeavesManager {
    constructor() {
        this.currentPage = 1;
        this.itemsPerPage = 10;
        this.totalItems = 0;
        this.init();
    }

    async init() {
        await this.loadLeaves();
        await this.loadFilters();
        this.setupEventListeners();
    }

    async loadLeaves() {
        try {
            common.showLoading();
            
            const searchQuery = document.getElementById("searchInput").value;
            const startDate = document.getElementById("startDateFilter").value;
            const endDate = document.getElementById("endDateFilter").value;
            const leaveType = document.getElementById("leaveTypeFilter").value;
            const status = document.getElementById("statusFilter").value;

            // Build query parameters
            const params = {
                page: this.currentPage,
                limit: this.itemsPerPage
            };
            if (searchQuery) params.search = searchQuery;
            if (startDate) params.start_date = startDate;
            if (endDate) params.end_date = endDate;
            if (leaveType) params.leave_type = leaveType;
            if (status) params.status = status;

            const response = await api.leaves.getAll(params);
            this.totalItems = response.total;

            // Update table
            const tbody = document.querySelector("#leavesTable tbody");
            tbody.innerHTML = "";
            
            response.data.forEach(leave => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${leave.user_name}</td>
                    <td>${leave.department_name}</td>
                    <td>${leave.leave_type}</td>
                    <td>${leave.start_date}</td>
                    <td>${leave.end_date}</td>
                    <td>${leave.reason}</td>
                    <td>
                        <span class="status-badge ${leave.status.toLowerCase()}">
                            ${leave.status}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button onclick="window.leavesManager.approveLeave(${leave.leave_id})" class="btn btn-success" ${leave.status !== 'pending' ? 'disabled' : ''}>
                                <i class="fas fa-check"></i>
                            </button>
                            <button onclick="window.leavesManager.rejectLeave(${leave.leave_id})" class="btn btn-danger" ${leave.status !== 'pending' ? 'disabled' : ''}>
                                <i class="fas fa-times"></i>
                            </button>
                            <button onclick="window.leavesManager.viewLeave(${leave.leave_id})" class="btn btn-info">
                                <i class="fas fa-eye"></i>
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
            common.showError("Không thể tải danh sách đơn xin nghỉ: " + error.message);
        }
    }

    async loadFilters() {
        try {
            // Load leave types for dropdown
            const response = await api.leaves.getTypes();
            const typeSelect = document.getElementById("leaveTypeFilter");
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
            this.loadLeaves();
        });

        document.getElementById("searchInput").addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                this.currentPage = 1;
                this.loadLeaves();
            }
        });

        // Filters
        document.getElementById("startDateFilter").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadLeaves();
        });

        document.getElementById("endDateFilter").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadLeaves();
        });

        document.getElementById("leaveTypeFilter").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadLeaves();
        });

        document.getElementById("statusFilter").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadLeaves();
        });

        // Pagination
        document.getElementById("prevPage").addEventListener("click", () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadLeaves();
            }
        });

        document.getElementById("nextPage").addEventListener("click", () => {
            const maxPage = Math.ceil(this.totalItems / this.itemsPerPage);
            if (this.currentPage < maxPage) {
                this.currentPage++;
                this.loadLeaves();
            }
        });
    }

    updatePagination(totalItems) {
        const maxPage = Math.ceil(totalItems / this.itemsPerPage);
        document.getElementById("pageInfo").textContent = `Trang ${this.currentPage} / ${maxPage}`;
        document.getElementById("prevPage").disabled = this.currentPage === 1;
        document.getElementById("nextPage").disabled = this.currentPage === maxPage;
    }

    async approveLeave(id) {
        if (confirm("Bạn có chắc chắn muốn phê duyệt đơn xin nghỉ này?")) {
            try {
                common.showLoading();
                await api.leaves.approve(id);
                common.showSuccess("Phê duyệt đơn xin nghỉ thành công");
                this.loadLeaves();
            } catch (error) {
                common.showError("Không thể phê duyệt đơn xin nghỉ: " + error.message);
            } finally {
                common.hideLoading();
            }
        }
    }

    async rejectLeave(id) {
        if (confirm("Bạn có chắc chắn muốn từ chối đơn xin nghỉ này?")) {
            try {
                common.showLoading();
                await api.leaves.reject(id);
                common.showSuccess("Từ chối đơn xin nghỉ thành công");
                this.loadLeaves();
            } catch (error) {
                common.showError("Không thể từ chối đơn xin nghỉ: " + error.message);
            } finally {
                common.hideLoading();
            }
        }
    }

    async viewLeave(id) {
        try {
            const response = await api.leaves.getById(id);
            const leave = response.data;
            
            const modal = document.createElement("div");
            modal.className = "modal";
            modal.innerHTML = `
                <div class="modal-content">
                    <h2>Chi tiết đơn xin nghỉ</h2>
                    <div class="leave-details">
                        <div class="detail-row">
                            <label>Nhân viên:</label>
                            <span>${leave.user_name}</span>
                        </div>
                        <div class="detail-row">
                            <label>Phòng ban:</label>
                            <span>${leave.department_name}</span>
                        </div>
                        <div class="detail-row">
                            <label>Loại nghỉ:</label>
                            <span>${leave.leave_type}</span>
                        </div>
                        <div class="detail-row">
                            <label>Ngày bắt đầu:</label>
                            <span>${leave.start_date}</span>
                        </div>
                        <div class="detail-row">
                            <label>Ngày kết thúc:</label>
                            <span>${leave.end_date}</span>
                        </div>
                        <div class="detail-row">
                            <label>Lý do:</label>
                            <span>${leave.reason}</span>
                        </div>
                        <div class="detail-row">
                            <label>Trạng thái:</label>
                            <span class="status-badge ${leave.status.toLowerCase()}">${leave.status}</span>
                        </div>
                        ${leave.notes ? `
                            <div class="detail-row">
                                <label>Ghi chú:</label>
                                <span>${leave.notes}</span>
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
            common.showError("Không thể tải thông tin đơn xin nghỉ: " + error.message);
        }
    }
}

// Initialize LeavesManager
window.leavesManager = new LeavesManager(); 