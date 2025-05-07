// Check authentication first
if (!auth.checkAuth()) {
    window.location.href = "/login.html";
}

class ActivityLogManager {
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
            const dateFrom = document.getElementById("dateFrom").value;
            const dateTo = document.getElementById("dateTo").value;
            const type = document.getElementById("typeFilter").value;

            // Build query parameters
            const params = {
                page: this.currentPage,
                limit: this.itemsPerPage,
                user_id: auth.getCurrentUserId()
            };
            if (searchQuery) params.search = searchQuery;
            if (dateFrom) params.date_from = dateFrom;
            if (dateTo) params.date_to = dateTo;
            if (type) params.type = type;

            const response = await api.activities.getAll(params);
            this.totalItems = response.total;

            // Update table
            const tbody = document.querySelector("#activitiesTable tbody");
            tbody.innerHTML = "";
            
            response.data.forEach(activity => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${this.formatDateTime(activity.created_at)}</td>
                    <td>${this.getActivityTypeLabel(activity.type)}</td>
                    <td>${activity.description}</td>
                    <td>${activity.ip_address || '-'}</td>
                    <td>${activity.user_agent || '-'}</td>
                `;
                tbody.appendChild(tr);
            });

            // Update pagination
            this.updatePagination(response.total);
            
            common.hideLoading();
        } catch (error) {
            common.hideLoading();
            common.showError("Không thể tải nhật ký hoạt động: " + error.message);
        }
    }

    async loadFilters() {
        try {
            // Set default date range to last 30 days
            const today = new Date();
            const thirtyDaysAgo = new Date();
            thirtyDaysAgo.setDate(today.getDate() - 30);

            document.getElementById("dateFrom").value = thirtyDaysAgo.toISOString().split('T')[0];
            document.getElementById("dateTo").value = today.toISOString().split('T')[0];
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

        // Date filters
        document.getElementById("dateFrom").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadActivities();
        });

        document.getElementById("dateTo").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadActivities();
        });

        // Type filter
        document.getElementById("typeFilter").addEventListener("change", () => {
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

        // Back button
        document.getElementById("backBtn").addEventListener("click", () => {
            window.location.href = "dashboard-employee.html";
        });
    }

    getActivityTypeLabel(type) {
        const labels = {
            LOGIN: "Đăng nhập",
            LOGOUT: "Đăng xuất",
            CHECK_IN: "Chấm công vào",
            CHECK_OUT: "Chấm công ra",
            CREATE_LEAVE: "Tạo đơn xin nghỉ",
            UPDATE_LEAVE: "Cập nhật đơn xin nghỉ",
            DELETE_LEAVE: "Xóa đơn xin nghỉ",
            UPDATE_PROFILE: "Cập nhật thông tin cá nhân",
            CHANGE_PASSWORD: "Đổi mật khẩu"
        };
        return labels[type] || type;
    }

    updatePagination(totalItems) {
        const maxPage = Math.ceil(totalItems / this.itemsPerPage);
        document.getElementById("pageInfo").textContent = `Trang ${this.currentPage} / ${maxPage}`;
        document.getElementById("prevPage").disabled = this.currentPage === 1;
        document.getElementById("nextPage").disabled = this.currentPage === maxPage;
    }

    formatDateTime(dateTimeString) {
        const date = new Date(dateTimeString);
        return date.toLocaleString('vi-VN');
    }
}

// Initialize ActivityLogManager
window.activityLogManager = new ActivityLogManager(); 