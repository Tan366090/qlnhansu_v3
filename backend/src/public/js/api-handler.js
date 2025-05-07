// API Handler with Error Handling and Loading States
class APIHandler {
    constructor() {
        this.baseUrl = 'http://localhost/qlnhansu_V3/backend/src/public/api';
        this.loadingStates = new Map();
    }

    // Show loading state
    showLoading(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.add('btn-loading');
            this.loadingStates.set(elementId, true);
        }
    }

    // Hide loading state
    hideLoading(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.remove('btn-loading');
            this.loadingStates.delete(elementId);
        }
    }

    // Show error message
    showError(message, type = 'error') {
        const container = document.getElementById('notificationContainer');
        if (!container) return;

        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        container.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    // Handle API errors
    handleError(error, endpoint) {
        console.error(`API Error in ${endpoint}:`, error);
        
        let errorMessage = 'Đã xảy ra lỗi khi kết nối với máy chủ';
        if (error.response) {
            switch (error.response.status) {
                case 401:
                    errorMessage = 'Phiên đăng nhập đã hết hạn';
                    // Redirect to login
                    window.location.href = '/login_new.html';
                    break;
                case 403:
                    errorMessage = 'Bạn không có quyền truy cập';
                    break;
                case 404:
                    errorMessage = 'Không tìm thấy tài nguyên';
                    break;
                case 500:
                    errorMessage = 'Lỗi máy chủ nội bộ';
                    break;
            }
        } else if (error.request) {
            errorMessage = 'Không thể kết nối với máy chủ';
        }

        this.showError(errorMessage);
        return null;
    }

    // Generic API request method
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}/${endpoint}`;
        const loadingId = options.loadingId;

        try {
            if (loadingId) this.showLoading(loadingId);

            const response = await fetch(url, {
                ...options,
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'application/json',
                    ...options.headers
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            return data;

        } catch (error) {
            return this.handleError(error, endpoint);
        } finally {
            if (loadingId) this.hideLoading(loadingId);
        }
    }

    // GET request
    async get(endpoint, options = {}) {
        return this.request(endpoint, {
            method: 'GET',
            ...options
        });
    }

    // POST request
    async post(endpoint, data, options = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data),
            ...options
        });
    }

    // PUT request
    async put(endpoint, data, options = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data),
            ...options
        });
    }

    // DELETE request
    async delete(endpoint, options = {}) {
        return this.request(endpoint, {
            method: 'DELETE',
            ...options
        });
    }

    // Handle empty data states
    handleEmptyData(containerId, message = 'Không có dữ liệu') {
        const container = document.getElementById(containerId);
        if (!container) return;

        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-database"></i>
                <p>${message}</p>
            </div>
        `;
    }

    // Handle chart errors
    handleChartError(chartId, error) {
        const canvas = document.getElementById(chartId);
        if (!canvas) return;

        const container = canvas.parentElement;
        container.innerHTML = `
            <div class="chart-error">
                <i class="fas fa-exclamation-circle"></i>
                <p>Không thể tải dữ liệu biểu đồ</p>
                <small>${error.message}</small>
            </div>
        `;
    }
}

// Export API handler instance
const apiHandler = new APIHandler(); 