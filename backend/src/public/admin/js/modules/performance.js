import { showNotification } from '../utils/notifications.js';
import { handleError } from '../utils/error-handler.js';

// Check authentication first
if (!auth.checkAuth()) {
    window.location.href = "/login.html";
}

class PerformanceManager {
    constructor() {
        this.currentPage = 1;
        this.itemsPerPage = 10;
        this.totalItems = 0;
        this.init();
    }

    async init() {
        await this.loadPerformance();
        await this.loadFilters();
        this.setupEventListeners();
    }

    async loadPerformance() {
        try {
            common.showLoading();
            
            const searchQuery = document.getElementById("searchInput").value;
            const departmentId = document.getElementById("departmentFilter").value;
            const year = document.getElementById("yearFilter").value;
            const quarter = document.getElementById("quarterFilter").value;

            // Build query parameters
            const params = {
                page: this.currentPage,
                limit: this.itemsPerPage
            };
            if (searchQuery) params.search = searchQuery;
            if (departmentId) params.department_id = departmentId;
            if (year) params.year = year;
            if (quarter) params.quarter = quarter;

            const response = await api.performance.getAll(params);
            this.totalItems = response.total;

            // Update table
            const tbody = document.querySelector("#performanceTable tbody");
            tbody.innerHTML = "";
            
            response.data.forEach(record => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${record.employee_code}</td>
                    <td>${record.full_name}</td>
                    <td>${record.department_name}</td>
                    <td>${record.position_name}</td>
                    <td>${record.year}</td>
                    <td>Q${record.quarter}</td>
                    <td>${record.kpi_score}</td>
                    <td>${record.attendance_score}</td>
                    <td>${record.quality_score}</td>
                    <td>${record.total_score}</td>
                    <td>
                        <span class="status-badge ${this.getPerformanceClass(record.total_score)}">
                            ${this.getPerformanceText(record.total_score)}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button onclick="window.performanceManager.viewDetails(${record.performance_id})" class="btn btn-info">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="window.performanceManager.editPerformance(${record.performance_id})" class="btn btn-warning">
                                <i class="fas fa-edit"></i>
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
            common.showError("Không thể tải danh sách đánh giá: " + error.message);
        }
    }

    async loadFilters() {
        try {
            // Load departments
            const deptResponse = await api.departments.getAll();
            const departmentSelect = document.getElementById("departmentFilter");
            departmentSelect.innerHTML = '<option value="">Tất cả</option>';
            deptResponse.data.forEach(dept => {
                const option = document.createElement("option");
                option.value = dept.department_id;
                option.textContent = dept.name;
                departmentSelect.appendChild(option);
            });

            // Set current year and quarter
            const currentDate = new Date();
            const currentYear = currentDate.getFullYear();
            const currentQuarter = Math.floor(currentDate.getMonth() / 3) + 1;

            document.getElementById("yearFilter").value = currentYear;
            document.getElementById("quarterFilter").value = currentQuarter;
        } catch (error) {
            console.error("Error loading filters:", error);
        }
    }

    setupEventListeners() {
        // Search
        document.getElementById("searchBtn").addEventListener("click", () => {
            this.currentPage = 1;
            this.loadPerformance();
        });

        document.getElementById("searchInput").addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                this.currentPage = 1;
                this.loadPerformance();
            }
        });

        // Filters
        document.getElementById("departmentFilter").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadPerformance();
        });

        document.getElementById("yearFilter").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadPerformance();
        });

        document.getElementById("quarterFilter").addEventListener("change", () => {
            this.currentPage = 1;
            this.loadPerformance();
        });

        // Pagination
        document.getElementById("prevPage").addEventListener("click", () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadPerformance();
            }
        });

        document.getElementById("nextPage").addEventListener("click", () => {
            const maxPage = Math.ceil(this.totalItems / this.itemsPerPage);
            if (this.currentPage < maxPage) {
                this.currentPage++;
                this.loadPerformance();
            }
        });

        // Add Performance
        document.getElementById("addPerformanceBtn").addEventListener("click", () => {
            this.showAddPerformanceModal();
        });
    }

    updatePagination(totalItems) {
        const maxPage = Math.ceil(totalItems / this.itemsPerPage);
        document.getElementById("pageInfo").textContent = `Trang ${this.currentPage} / ${maxPage}`;
        document.getElementById("prevPage").disabled = this.currentPage === 1;
        document.getElementById("nextPage").disabled = this.currentPage === maxPage;
    }

    async showAddPerformanceModal() {
        try {
            // Load employees
            const response = await api.users.getAll({ is_active: true });
            
            const modal = document.createElement("div");
            modal.className = "modal";
            modal.innerHTML = `
                <div class="modal-content">
                    <h2>Thêm đánh giá hiệu suất</h2>
                    <form id="addPerformanceForm">
                        <div class="form-group">
                            <label>Nhân viên</label>
                            <select name="user_id" required>
                                ${response.data.map(emp => `
                                    <option value="${emp.user_id}">${emp.employee_code} - ${emp.full_name}</option>
                                `).join('')}
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Năm</label>
                            <input type="number" name="year" value="${new Date().getFullYear()}" required>
                        </div>
                        <div class="form-group">
                            <label>Quý</label>
                            <select name="quarter" required>
                                <option value="1">Quý 1</option>
                                <option value="2">Quý 2</option>
                                <option value="3">Quý 3</option>
                                <option value="4">Quý 4</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Điểm KPI</label>
                            <input type="number" name="kpi_score" min="0" max="100" required>
                        </div>
                        <div class="form-group">
                            <label>Điểm chấm công</label>
                            <input type="number" name="attendance_score" min="0" max="100" required>
                        </div>
                        <div class="form-group">
                            <label>Điểm chất lượng</label>
                            <input type="number" name="quality_score" min="0" max="100" required>
                        </div>
                        <div class="form-group">
                            <label>Nhận xét</label>
                            <textarea name="comment"></textarea>
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
            document.getElementById("addPerformanceForm").addEventListener("submit", async (e) => {
                e.preventDefault();
                await this.addPerformance(new FormData(e.target));
                modal.remove();
            });
        } catch (error) {
            common.showError("Không thể tải danh sách nhân viên: " + error.message);
        }
    }

    async addPerformance(formData) {
        try {
            if (!this.validateForm(formData)) return;

            common.showLoading();

            const data = {
                user_id: formData.get("user_id"),
                year: formData.get("year"),
                quarter: formData.get("quarter"),
                kpi_score: formData.get("kpi_score"),
                attendance_score: formData.get("attendance_score"),
                quality_score: formData.get("quality_score"),
                comment: formData.get("comment")
            };

            await api.performance.create(data);
            common.showSuccess("Thêm đánh giá thành công");
            this.loadPerformance();
        } catch (error) {
            common.showError("Không thể thêm đánh giá: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    async editPerformance(id) {
        try {
            const response = await api.performance.getById(id);
            const performance = response.data;
            
            // Load employees
            const usersResponse = await api.users.getAll({ is_active: true });
            
            const modal = document.createElement("div");
            modal.className = "modal";
            modal.innerHTML = `
                <div class="modal-content">
                    <h2>Sửa đánh giá hiệu suất</h2>
                    <form id="editPerformanceForm">
                        <input type="hidden" name="performance_id" value="${id}">
                        <div class="form-group">
                            <label>Nhân viên</label>
                            <select name="user_id" required>
                                ${usersResponse.data.map(emp => `
                                    <option value="${emp.user_id}" ${emp.user_id === performance.user_id ? 'selected' : ''}>
                                        ${emp.employee_code} - ${emp.full_name}
                                    </option>
                                `).join('')}
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Năm</label>
                            <input type="number" name="year" value="${performance.year}" required>
                        </div>
                        <div class="form-group">
                            <label>Quý</label>
                            <select name="quarter" required>
                                <option value="1" ${performance.quarter === 1 ? 'selected' : ''}>Quý 1</option>
                                <option value="2" ${performance.quarter === 2 ? 'selected' : ''}>Quý 2</option>
                                <option value="3" ${performance.quarter === 3 ? 'selected' : ''}>Quý 3</option>
                                <option value="4" ${performance.quarter === 4 ? 'selected' : ''}>Quý 4</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Điểm KPI</label>
                            <input type="number" name="kpi_score" min="0" max="100" value="${performance.kpi_score}" required>
                        </div>
                        <div class="form-group">
                            <label>Điểm chấm công</label>
                            <input type="number" name="attendance_score" min="0" max="100" value="${performance.attendance_score}" required>
                        </div>
                        <div class="form-group">
                            <label>Điểm chất lượng</label>
                            <input type="number" name="quality_score" min="0" max="100" value="${performance.quality_score}" required>
                        </div>
                        <div class="form-group">
                            <label>Nhận xét</label>
                            <textarea name="comment">${performance.comment || ''}</textarea>
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
            document.getElementById("editPerformanceForm").addEventListener("submit", async (e) => {
                e.preventDefault();
                await this.updatePerformance(new FormData(e.target));
                modal.remove();
            });
        } catch (error) {
            common.showError("Không thể tải thông tin đánh giá: " + error.message);
        }
    }

    async updatePerformance(formData) {
        try {
            if (!this.validateForm(formData)) return;

            common.showLoading();

            const id = formData.get("performance_id");
            const data = {
                user_id: formData.get("user_id"),
                year: formData.get("year"),
                quarter: formData.get("quarter"),
                kpi_score: formData.get("kpi_score"),
                attendance_score: formData.get("attendance_score"),
                quality_score: formData.get("quality_score"),
                comment: formData.get("comment")
            };

            await api.performance.update(id, data);
            common.showSuccess("Cập nhật đánh giá thành công");
            this.loadPerformance();
        } catch (error) {
            common.showError("Không thể cập nhật đánh giá: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    async viewDetails(id) {
        try {
            const response = await api.performance.getById(id);
            const performance = response.data;
            
            const modal = document.createElement("div");
            modal.className = "modal";
            modal.innerHTML = `
                <div class="modal-content">
                    <h2>Chi tiết đánh giá hiệu suất</h2>
                    <div class="performance-details">
                        <div class="detail-row">
                            <label>Nhân viên:</label>
                            <span>${performance.employee_code} - ${performance.full_name}</span>
                        </div>
                        <div class="detail-row">
                            <label>Phòng ban:</label>
                            <span>${performance.department_name}</span>
                        </div>
                        <div class="detail-row">
                            <label>Chức vụ:</label>
                            <span>${performance.position_name}</span>
                        </div>
                        <div class="detail-row">
                            <label>Năm:</label>
                            <span>${performance.year}</span>
                        </div>
                        <div class="detail-row">
                            <label>Quý:</label>
                            <span>Q${performance.quarter}</span>
                        </div>
                        <div class="detail-row">
                            <label>Điểm KPI:</label>
                            <span>${performance.kpi_score}</span>
                        </div>
                        <div class="detail-row">
                            <label>Điểm chấm công:</label>
                            <span>${performance.attendance_score}</span>
                        </div>
                        <div class="detail-row">
                            <label>Điểm chất lượng:</label>
                            <span>${performance.quality_score}</span>
                        </div>
                        <div class="detail-row">
                            <label>Tổng điểm:</label>
                            <span>${performance.total_score}</span>
                        </div>
                        <div class="detail-row">
                            <label>Đánh giá:</label>
                            <span class="status-badge ${this.getPerformanceClass(performance.total_score)}">
                                ${this.getPerformanceText(performance.total_score)}
                            </span>
                        </div>
                        <div class="detail-row">
                            <label>Nhận xét:</label>
                            <span>${performance.comment || 'Không có'}</span>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').remove()">Đóng</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
        } catch (error) {
            common.showError("Không thể tải thông tin đánh giá: " + error.message);
        }
    }

    validateForm(formData) {
        const requiredFields = [
            "user_id",
            "year",
            "quarter",
            "kpi_score",
            "attendance_score",
            "quality_score"
        ];

        for (const field of requiredFields) {
            if (!formData.get(field)) {
                common.showError(`Vui lòng nhập ${this.getFieldLabel(field)}`);
                return false;
            }
        }

        // Validate scores
        const scores = ["kpi_score", "attendance_score", "quality_score"];
        for (const score of scores) {
            const value = parseFloat(formData.get(score));
            if (isNaN(value) || value < 0 || value > 100) {
                common.showError(`Điểm ${this.getFieldLabel(score)} phải từ 0 đến 100`);
                return false;
            }
        }

        return true;
    }

    getFieldLabel(field) {
        const labels = {
            user_id: "nhân viên",
            year: "năm",
            quarter: "quý",
            kpi_score: "KPI",
            attendance_score: "chấm công",
            quality_score: "chất lượng"
        };
        return labels[field] || field;
    }

    getPerformanceClass(score) {
        if (score >= 90) return "excellent";
        if (score >= 80) return "good";
        if (score >= 70) return "average";
        if (score >= 60) return "poor";
        return "very-poor";
    }

    getPerformanceText(score) {
        if (score >= 90) return "Xuất sắc";
        if (score >= 80) return "Tốt";
        if (score >= 70) return "Khá";
        if (score >= 60) return "Trung bình";
        return "Kém";
    }
}

// Initialize PerformanceManager
window.performanceManager = new PerformanceManager();

// Initialize performance optimizer
const optimizer = new PerformanceOptimizer();

// Export optimized functions
export const debounce = optimizer.debounce.bind(optimizer);
export const throttle = optimizer.throttle.bind(optimizer);
export const setCache = optimizer.setCache.bind(optimizer);
export const getCache = optimizer.getCache.bind(optimizer);

// Initialize optimizations
document.addEventListener('DOMContentLoaded', () => {
    // Setup lazy loading for images
    optimizer.setupLazyLoading('img[data-src]', (img) => {
        optimizer.loadImage(img);
    });

    // Optimize existing images
    optimizer.optimizeImages();

    // Preload critical resources
    optimizer.preloadResources();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    optimizer.cleanup();
}); 