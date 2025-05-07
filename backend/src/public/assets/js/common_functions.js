// Common Functions System
class CommonFunctions {
    constructor() {
        this.userRoles = [];
        this.activityLogs = [];
        this.loadingStates = new Map();
        this.cache = new Map();
        this.rateLimiter = new RateLimiter(100, 60000); // 100 requests per minute
        this.sessionTimeout = 30 * 60 * 1000; // 30 minutes
        this.initializeSession();
        this.loadUserRoles();
        this.loadActivityLogs();
        this.initializeFormHandlers();
    }

    // Initialize session
    initializeSession() {
        this.lastActivity = Date.now();
        document.addEventListener("mousemove", () => this.updateLastActivity());
        document.addEventListener("keypress", () => this.updateLastActivity());
        this.checkSessionTimeout();
    }

    // Update last activity
    updateLastActivity() {
        this.lastActivity = Date.now();
    }

    // Check session timeout
    checkSessionTimeout() {
        setInterval(() => {
            if (Date.now() - this.lastActivity > this.sessionTimeout) {
                this.handleSessionTimeout();
            }
        }, 60000); // Check every minute
    }

    // Handle session timeout
    handleSessionTimeout() {
        notificationSystem.addNotification("Cảnh báo", "Phiên làm việc của bạn đã hết hạn", "warning");
        setTimeout(() => {
            window.location.href = "/login";
        }, 5000);
    }

    // Sanitize input
    sanitizeInput(input) {
        if (typeof input === "string") {
            return input.replace(/[<>]/g, "");
        }
        return input;
    }

    // Generic API call with rate limiting and error handling
    async apiCall(endpoint, options = {}) {
        if (!this.rateLimiter.canMakeRequest()) {
            throw new Error("Rate limit exceeded");
        }

        const loadingKey = `${endpoint}_${Date.now()}`;
        this.setLoading(loadingKey, true);

        try {
            // Sanitize request data
            if (options.body) {
                options.body = this.sanitizeInput(options.body);
            }

            const defaultOptions = {
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${localStorage.getItem("authToken")}`,
                    "X-Requested-With": "XMLHttpRequest"
                }
            };

            const response = await fetch(endpoint, { ...defaultOptions, ...options });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            this.cacheResponse(endpoint, data);
            return data;
        } catch (error) {
            console.error(`API call failed: ${endpoint}`, error);
            this.logError("API Error", error);
            notificationSystem.addNotification("Lỗi", `Không thể kết nối đến máy chủ: ${error.message}`, "error");
            throw error;
        } finally {
            this.setLoading(loadingKey, false);
        }
    }

    // Cache response
    cacheResponse(endpoint, data) {
        const cacheKey = `${endpoint}_${JSON.stringify(data)}`;
        this.cache.set(cacheKey, {
            data,
            timestamp: Date.now()
        });
    }

    // Get cached response
    getCachedResponse(endpoint) {
        const cacheKey = `${endpoint}_${JSON.stringify({})}`;
        const cached = this.cache.get(cacheKey);
        if (cached && Date.now() - cached.timestamp < 5 * 60 * 1000) { // 5 minutes cache
            return cached.data;
        }
        return null;
    }

    // Log error
    logError(type, error) {
        console.error(`[${type}] ${error.message}`);
        // Send error to server
        fetch("/api/error-log", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                type,
                message: error.message,
                stack: error.stack,
                timestamp: new Date().toISOString()
            })
        });
    }

    // Confirm action
    async confirmAction(message) {
        return new Promise((resolve) => {
            const confirmed = window.confirm(message);
            resolve(confirmed);
        });
    }

    // Set loading state
    setLoading(key, isLoading) {
        this.loadingStates.set(key, isLoading);
        this.updateLoadingUI();
    }

    // Update loading UI
    updateLoadingUI() {
        const isLoading = Array.from(this.loadingStates.values()).some(state => state);
        document.body.classList.toggle("loading", isLoading);
    }

    // Load user roles from API with caching
    async loadUserRoles() {
        const cacheKey = "user_roles";
        if (this.cache.has(cacheKey)) {
            this.userRoles = this.cache.get(cacheKey);
            this.setupRoleBasedAccess();
            return;
        }

        try {
            const roles = await this.apiCall("/api/user-roles");
            this.userRoles = roles;
            this.cache.set(cacheKey, roles);
            this.setupRoleBasedAccess();
        } catch (error) {
            console.error("Error loading user roles:", error);
        }
    }

    // Setup role-based access control
    setupRoleBasedAccess() {
        const currentUserRole = this.getCurrentUserRole();
        const elements = document.querySelectorAll("[data-role]");
        
        elements.forEach(element => {
            const requiredRoles = element.dataset.role.split(",");
            if (!requiredRoles.includes(currentUserRole)) {
                element.style.display = "none";
            }
        });
    }

    // Get current user role
    getCurrentUserRole() {
        return localStorage.getItem("userRole") || "user";
    }

    // Log activity
    async logActivity(action, details) {
        try {
            const response = await fetch("/api/activity-logs", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    action,
                    details,
                    user_id: localStorage.getItem("userId"),
                    timestamp: new Date().toISOString()
                })
            });

            if (response.ok) {
                this.activityLogs.unshift({
                    action,
                    details,
                    timestamp: new Date()
                });
            }
        } catch (error) {
            console.error("Error logging activity:", error);
        }
    }

    // Load activity logs
    async loadActivityLogs() {
        try {
            const response = await fetch("/api/activity-logs");
            this.activityLogs = await response.json();
            this.displayActivityLogs();
        } catch (error) {
            console.error("Error loading activity logs:", error);
        }
    }

    // Display activity logs
    displayActivityLogs() {
        const container = document.getElementById("activityLogs");
        if (!container) return;

        container.innerHTML = "";
        this.activityLogs.forEach(log => {
            const div = document.createElement("div");
            div.className = "activity-log-item";
            div.innerHTML = `
                <div class="activity-log-header">
                    <span class="activity-log-action">${log.action}</span>
                    <span class="activity-log-time">${this.formatDateTime(log.timestamp)}</span>
                </div>
                <div class="activity-log-details">${log.details}</div>
            `;
            container.appendChild(div);
        });
    }

    // Backup data
    async backupData() {
        try {
            const response = await fetch("/api/backup", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                }
            });

            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = url;
                a.download = `backup_${new Date().toISOString().split("T")[0]}.zip`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);

                notificationSystem.addNotification("Thành công", "Đã tạo bản sao lưu", "success");
            }
        } catch (error) {
            console.error("Error backing up data:", error);
            notificationSystem.addNotification("Lỗi", "Không thể tạo bản sao lưu", "error");
        }
    }

    // Advanced search with debounce
    debouncedSearch = this.debounce(async (query, filters) => {
        try {
            const results = await this.apiCall("/api/search", {
                method: "POST",
                body: JSON.stringify({ query, filters })
            });
            this.displaySearchResults(results);
        } catch (error) {
            console.error("Error performing advanced search:", error);
        }
    }, 300);

    // Debounce helper
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

    // Initialize form handlers
    initializeFormHandlers() {
        const searchForm = document.getElementById("searchForm");
        if (searchForm) {
            searchForm.addEventListener("submit", (e) => {
                e.preventDefault();
                const query = document.getElementById("searchQuery").value;
                const type = document.getElementById("searchType").value;
                const date = document.getElementById("searchDate").value;

                if (!query) {
                    notificationSystem.addNotification("Lỗi", "Vui lòng nhập từ khóa tìm kiếm", "error");
                    return;
                }

                this.debouncedSearch(query, { type, date });
            });
        }
    }

    // Display search results with pagination
    displaySearchResults(results) {
        const container = document.getElementById("searchResults");
        if (!container) return;

        if (!results || results.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <p>Không tìm thấy kết quả</p>
                </div>
            `;
            return;
        }

        container.innerHTML = "";
        results.forEach(result => {
            const div = document.createElement("div");
            div.className = "search-result-item";
            div.innerHTML = `
                <h4>${result.title}</h4>
                <p>${result.description}</p>
                <a href="${result.link}" class="btn btn-primary">Xem chi tiết</a>
            `;
            container.appendChild(div);
        });
    }

    // Export report
    async exportReport(format, data) {
        try {
            const response = await fetch(`/api/export/${format}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = url;
                a.download = `report_${new Date().toISOString().split("T")[0]}.${format}`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);

                notificationSystem.addNotification("Thành công", `Đã xuất báo cáo ${format.toUpperCase()}`, "success");
            }
        } catch (error) {
            console.error("Error exporting report:", error);
            notificationSystem.addNotification("Lỗi", "Không thể xuất báo cáo", "error");
        }
    }

    // Generate statistics
    async generateStatistics() {
        try {
            const response = await fetch("/api/statistics");
            const stats = await response.json();
            this.displayStatistics(stats);
        } catch (error) {
            console.error("Error generating statistics:", error);
            notificationSystem.addNotification("Lỗi", "Không thể tạo báo cáo thống kê", "error");
        }
    }

    // Display statistics
    displayStatistics(stats) {
        const container = document.getElementById("statistics");
        if (!container) return;

        container.innerHTML = "";
        Object.entries(stats).forEach(([key, value]) => {
            const div = document.createElement("div");
            div.className = "stat-item";
            div.innerHTML = `
                <h4>${this.formatStatKey(key)}</h4>
                <p>${this.formatStatValue(value)}</p>
            `;
            container.appendChild(div);
        });
    }

    // Format date and time
    formatDateTime(date) {
        return new Date(date).toLocaleString("vi-VN", {
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
            hour: "2-digit",
            minute: "2-digit"
        });
    }

    // Format statistics key
    formatStatKey(key) {
        const keys = {
            "total_employees": "Tổng số nhân viên",
            "active_employees": "Nhân viên đang làm việc",
            "total_departments": "Tổng số phòng ban",
            "total_documents": "Tổng số tài liệu",
            "pending_approvals": "Chờ phê duyệt"
        };
        return keys[key] || key;
    }

    // Format statistics value
    formatStatValue(value) {
        if (typeof value === "number") {
            return value.toLocaleString("vi-VN");
        }
        return value;
    }

    // Integrate with external systems
    async integrateWithExternalSystem(systemName, data) {
        try {
            const response = await fetch(`/api/integration/${systemName}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                notificationSystem.addNotification("Thành công", `Đã tích hợp với ${systemName}`, "success");
            }
        } catch (error) {
            console.error(`Error integrating with ${systemName}:`, error);
            notificationSystem.addNotification("Lỗi", `Không thể tích hợp với ${systemName}`, "error");
        }
    }

    // Restore backup
    async restoreBackup() {
        try {
            const input = document.createElement("input");
            input.type = "file";
            input.accept = ".zip";
            
            input.onchange = async (e) => {
                const file = e.target.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append("backup", file);

                const response = await this.apiCall("/api/restore", {
                    method: "POST",
                    body: formData
                });

                if (response.success) {
                    notificationSystem.addNotification("Thành công", "Đã khôi phục dữ liệu", "success");
                    // Reload page to reflect changes
                    window.location.reload();
                }
            };

            input.click();
        } catch (error) {
            console.error("Error restoring backup:", error);
            notificationSystem.addNotification("Lỗi", "Không thể khôi phục dữ liệu", "error");
        }
    }
}

// Rate Limiter class
class RateLimiter {
    constructor(maxRequests, timeWindow) {
        this.maxRequests = maxRequests;
        this.timeWindow = timeWindow;
        this.requests = [];
    }

    canMakeRequest() {
        const now = Date.now();
        this.requests = this.requests.filter(time => now - time < this.timeWindow);
        
        if (this.requests.length >= this.maxRequests) {
            return false;
        }
        
        this.requests.push(now);
        return true;
    }
}

// Initialize common functions with error handling
document.addEventListener("DOMContentLoaded", () => {
    try {
        // Load user info
        const userName = localStorage.getItem("userName") || "Khách";
        const userRole = localStorage.getItem("userRole") || "user";
        document.getElementById("userName").textContent = userName;
        document.getElementById("userRole").textContent = userRole;

        // Initialize common functions
        window.commonFunctions = new CommonFunctions();
    } catch (error) {
        console.error("Failed to initialize:", error);
        notificationSystem.addNotification("Lỗi", "Không thể khởi tạo hệ thống", "error");
    }
}); 