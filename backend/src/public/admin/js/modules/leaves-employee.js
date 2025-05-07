// Check authentication first
if (!auth.checkAuth()) {
    window.location.href = "/login.html";
}

class LeavesEmployeeManager {
    constructor() {
        this.currentPage = 1;
        this.pageSize = 10;
        this.init();
    }

    init() {
        this.loadLeaves();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Search input
        document.getElementById("searchInput").addEventListener("input", (e) => {
            this.currentPage = 1;
            this.loadLeaves();
        });

        // Date filter
        document.getElementById("dateFilter").addEventListener("change", (e) => {
            this.currentPage = 1;
            this.loadLeaves();
        });

        // Status filter
        document.getElementById("statusFilter").addEventListener("change", (e) => {
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
            this.currentPage++;
            this.loadLeaves();
        });

        // Add leave button
        document.getElementById("addLeaveBtn").addEventListener("click", () => {
            this.showAddLeaveModal();
        });

        // Back to dashboard button
        document.getElementById("backToDashboardBtn").addEventListener("click", () => {
            window.location.href = "dashboard-employee.html";
        });
    }

    async loadLeaves() {
        try {
            common.showLoading();

            const search = document.getElementById("searchInput").value;
            const dateFilter = document.getElementById("dateFilter").value;
            const statusFilter = document.getElementById("statusFilter").value;

            const response = await api.leaves.getMyLeaves({
                page: this.currentPage,
                limit: this.pageSize,
                search,
                date: dateFilter,
                status: statusFilter
            });

            this.displayLeaves(response.data.items);
            this.updatePagination(response.data.total);
        } catch (error) {
            common.showError("Không thể tải dữ liệu nghỉ phép: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    displayLeaves(leaves) {
        const tbody = document.getElementById("leavesTableBody");
        tbody.innerHTML = "";

        if (leaves.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center">Không có dữ liệu nghỉ phép</td>
                </tr>
            `;
            return;
        }

        leaves.forEach(item => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${this.formatDate(item.startDate)}</td>
                <td>${this.formatDate(item.endDate)}</td>
                <td>${item.reason}</td>
                <td>${item.totalDays} ngày</td>
                <td>
                    <span class="badge ${this.getStatusBadgeClass(item.status)}">
                        ${this.getStatusText(item.status)}
                    </span>
                </td>
                <td>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-info" onclick="leavesEmployeeManager.viewLeave(${item.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${item.status === "PENDING" ? `
                            <button type="button" class="btn btn-sm btn-primary" onclick="leavesEmployeeManager.editLeave(${item.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="leavesEmployeeManager.deleteLeave(${item.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    updatePagination(total) {
        const totalPages = Math.ceil(total / this.pageSize);
        document.getElementById("currentPage").textContent = this.currentPage;
        document.getElementById("totalPages").textContent = totalPages;

        document.getElementById("prevPage").disabled = this.currentPage === 1;
        document.getElementById("nextPage").disabled = this.currentPage === totalPages;
    }

    showAddLeaveModal() {
        const modal = document.getElementById("addLeaveModal");
        modal.style.display = "block";

        // Close modal when clicking outside
        window.onclick = (event) => {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        };

        // Handle form submission
        document.getElementById("addLeaveForm").addEventListener("submit", async (e) => {
            e.preventDefault();
            await this.addLeave(new FormData(e.target));
        });
    }

    async addLeave(formData) {
        try {
            if (!this.validateForm(formData)) return;

            common.showLoading();

            const data = {
                startDate: formData.get("startDate"),
                endDate: formData.get("endDate"),
                reason: formData.get("reason")
            };

            await api.leaves.create(data);
            common.showSuccess("Tạo đơn nghỉ phép thành công");
            
            // Close modal
            document.getElementById("addLeaveModal").style.display = "none";
            
            // Reload leaves
            this.loadLeaves();
        } catch (error) {
            common.showError("Không thể tạo đơn nghỉ phép: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    async editLeave(id) {
        try {
            common.showLoading();
            const response = await api.leaves.getById(id);
            const leave = response.data;

            const modal = document.getElementById("editLeaveModal");
            modal.style.display = "block";

            // Set form values
            document.getElementById("editStartDate").value = leave.startDate.split('T')[0];
            document.getElementById("editEndDate").value = leave.endDate.split('T')[0];
            document.getElementById("editReason").value = leave.reason;

            // Close modal when clicking outside
            window.onclick = (event) => {
                if (event.target === modal) {
                    modal.style.display = "none";
                }
            };

            // Handle form submission
            document.getElementById("editLeaveForm").addEventListener("submit", async (e) => {
                e.preventDefault();
                await this.updateLeave(id, new FormData(e.target));
            });
        } catch (error) {
            common.showError("Không thể tải thông tin đơn nghỉ phép: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    async updateLeave(id, formData) {
        try {
            if (!this.validateForm(formData)) return;

            common.showLoading();

            const data = {
                startDate: formData.get("startDate"),
                endDate: formData.get("endDate"),
                reason: formData.get("reason")
            };

            await api.leaves.update(id, data);
            common.showSuccess("Cập nhật đơn nghỉ phép thành công");
            
            // Close modal
            document.getElementById("editLeaveModal").style.display = "none";
            
            // Reload leaves
            this.loadLeaves();
        } catch (error) {
            common.showError("Không thể cập nhật đơn nghỉ phép: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    async viewLeave(id) {
        try {
            common.showLoading();
            const response = await api.leaves.getById(id);
            const leave = response.data;

            const modal = document.getElementById("viewLeaveModal");
            modal.style.display = "block";

            // Set modal content
            document.getElementById("viewStartDate").textContent = this.formatDate(leave.startDate);
            document.getElementById("viewEndDate").textContent = this.formatDate(leave.endDate);
            document.getElementById("viewReason").textContent = leave.reason;
            document.getElementById("viewTotalDays").textContent = leave.totalDays;
            document.getElementById("viewStatus").textContent = this.getStatusText(leave.status);
            document.getElementById("viewCreatedAt").textContent = this.formatDateTime(leave.createdAt);
            
            if (leave.approver) {
                document.getElementById("viewApprover").textContent = leave.approver.fullName;
                document.getElementById("viewApprovedAt").textContent = this.formatDateTime(leave.approvedAt);
            } else {
                document.getElementById("viewApprover").textContent = "Chưa duyệt";
                document.getElementById("viewApprovedAt").textContent = "Chưa duyệt";
            }

            if (leave.rejectionReason) {
                document.getElementById("viewRejectionReason").textContent = leave.rejectionReason;
                document.getElementById("rejectionReasonContainer").style.display = "block";
            } else {
                document.getElementById("rejectionReasonContainer").style.display = "none";
            }

            // Close modal when clicking outside
            window.onclick = (event) => {
                if (event.target === modal) {
                    modal.style.display = "none";
                }
            };
        } catch (error) {
            common.showError("Không thể tải thông tin đơn nghỉ phép: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    async deleteLeave(id) {
        try {
            if (!confirm("Bạn có chắc chắn muốn xóa đơn nghỉ phép này?")) {
                return;
            }

            common.showLoading();
            await api.leaves.delete(id);
            common.showSuccess("Xóa đơn nghỉ phép thành công");
            this.loadLeaves();
        } catch (error) {
            common.showError("Không thể xóa đơn nghỉ phép: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    validateForm(formData) {
        const startDate = formData.get("startDate");
        const endDate = formData.get("endDate");
        const reason = formData.get("reason");

        if (!startDate) {
            common.showError("Vui lòng chọn ngày bắt đầu");
            return false;
        }

        if (!endDate) {
            common.showError("Vui lòng chọn ngày kết thúc");
            return false;
        }

        if (new Date(endDate) < new Date(startDate)) {
            common.showError("Ngày kết thúc phải sau ngày bắt đầu");
            return false;
        }

        if (!reason) {
            common.showError("Vui lòng nhập lý do nghỉ phép");
            return false;
        }

        return true;
    }

    getStatusBadgeClass(status) {
        switch (status) {
            case "APPROVED":
                return "badge-success";
            case "PENDING":
                return "badge-warning";
            case "REJECTED":
                return "badge-danger";
            default:
                return "badge-secondary";
        }
    }

    getStatusText(status) {
        switch (status) {
            case "APPROVED":
                return "Đã duyệt";
            case "PENDING":
                return "Chờ duyệt";
            case "REJECTED":
                return "Từ chối";
            default:
                return "Không xác định";
        }
    }

    formatDate(date) {
        return new Date(date).toLocaleDateString("vi-VN");
    }

    formatDateTime(dateTime) {
        return new Date(dateTime).toLocaleString("vi-VN");
    }
}

// Initialize LeavesEmployeeManager
window.leavesEmployeeManager = new LeavesEmployeeManager(); 