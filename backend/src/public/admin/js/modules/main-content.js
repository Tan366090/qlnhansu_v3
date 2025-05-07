// Main Content Handler
class MainContent {
    constructor() {
        this.currentPage = 1;
        this.pageSize = 10;
        this.totalItems = 0;
        this.searchTerm = "";
        this.filters = {};
        this.data = [];
        this.initialize();
    }

    initialize() {
        this.setupEventListeners();
        this.loadData();
    }

    setupEventListeners() {
        // Search
        const searchInput = document.getElementById("searchInput");
        if (searchInput) {
            searchInput.addEventListener("input", this.debounce(() => {
                this.searchTerm = searchInput.value;
                this.loadData();
            }, 300));
        }

        // Filters
        const filterElements = document.querySelectorAll(".filter-box select");
        filterElements.forEach(select => {
            select.addEventListener("change", () => {
                this.filters[select.id] = select.value;
                this.loadData();
            });
        });

        // Pagination
        const pagination = document.querySelector(".pagination");
        if (pagination) {
            pagination.addEventListener("click", (e) => {
                if (e.target.tagName === "BUTTON") {
                    this.currentPage = parseInt(e.target.dataset.page);
                    this.loadData();
                }
            });
        }
    }

    async loadData() {
        try {
            this.showLoading();
            
            // Build query parameters
            const params = new URLSearchParams({
                page: this.currentPage,
                pageSize: this.pageSize,
                search: this.searchTerm,
                ...this.filters
            });

            // Get data from API
            const response = await fetch(`/api/data?${params}`);
            const result = await response.json();

            this.data = result.data;
            this.totalItems = result.total;
            
            this.renderData();
            this.updatePagination();
        } catch (error) {
            this.showError("Không thể tải dữ liệu");
            console.error("Error loading data:", error);
        } finally {
            this.hideLoading();
        }
    }

    renderData() {
        const container = document.querySelector(".data-table tbody");
        if (!container) return;

        if (this.data.length === 0) {
            this.showEmptyState();
            return;
        }

        container.innerHTML = this.data.map(item => this.renderRow(item)).join("");
    }

    renderRow(item) {
        // Override this method in child classes
        return "";
    }

    updatePagination() {
        const pagination = document.querySelector(".pagination");
        if (!pagination) return;

        const totalPages = Math.ceil(this.totalItems / this.pageSize);
        let html = "";

        // Previous button
        html += `
            <button class="btn ${this.currentPage === 1 ? "disabled" : ""}" 
                    data-page="${this.currentPage - 1}">
                <i class="fas fa-chevron-left"></i>
            </button>
        `;

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (
                i === 1 || 
                i === totalPages || 
                (i >= this.currentPage - 2 && i <= this.currentPage + 2)
            ) {
                html += `
                    <button class="btn ${i === this.currentPage ? "active" : ""}" 
                            data-page="${i}">
                        ${i}
                    </button>
                `;
            } else if (
                i === this.currentPage - 3 || 
                i === this.currentPage + 3
            ) {
                html += "<span>...</span>";
            }
        }

        // Next button
        html += `
            <button class="btn ${this.currentPage === totalPages ? "disabled" : ""}" 
                    data-page="${this.currentPage + 1}">
                <i class="fas fa-chevron-right"></i>
            </button>
        `;

        pagination.innerHTML = html;
    }

    showLoading() {
        const container = document.querySelector(".content-body");
        if (container) {
            container.classList.add("loading");
        }
    }

    hideLoading() {
        const container = document.querySelector(".content-body");
        if (container) {
            container.classList.remove("loading");
        }
    }

    showEmptyState() {
        const container = document.querySelector(".content-body");
        if (container) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>Không có dữ liệu</h3>
                    <p>Không tìm thấy kết quả phù hợp với tiêu chí tìm kiếm</p>
                </div>
            `;
        }
    }

    showError(message) {
        const toast = document.createElement("div");
        toast.className = "toast error";
        toast.innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add("show");
        }, 100);

        setTimeout(() => {
            toast.classList.remove("show");
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }

    showSuccess(message) {
        const toast = document.createElement("div");
        toast.className = "toast success";
        toast.innerHTML = `
            <i class="fas fa-check-circle"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add("show");
        }, 100);

        setTimeout(() => {
            toast.classList.remove("show");
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Example usage for a specific page
class EmployeeList extends MainContent {
    renderRow(employee) {
        return `
            <tr>
                <td>${employee.id}</td>
                <td>${employee.name}</td>
                <td>${employee.department}</td>
                <td>${employee.position}</td>
                <td>${employee.status}</td>
                <td>
                    <button class="btn btn-primary btn-sm" onclick="viewEmployee(${employee.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="editEmployee(${employee.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="deleteEmployee(${employee.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }
}

// Initialize when document is ready
document.addEventListener("DOMContentLoaded", () => {
    // Check if we're on a page that needs the main content handler
    if (document.querySelector(".main-content")) {
        new MainContent();
    }
}); 