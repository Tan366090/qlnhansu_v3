class NetworkErrorHandler {
    static #isOffline = false;
    static #lastOnlineCheck = null;
    static #checkInterval = 30000; // 30 seconds
    static #retryQueue = [];
    static #maxRetries = 3;

    static init() {
        this.#setupEventListeners();
        this.#startOnlineCheck();
    }

    static #setupEventListeners() {
        window.addEventListener('online', () => this.#handleOnline());
        window.addEventListener('offline', () => this.#handleOffline());
    }

    static #startOnlineCheck() {
        setInterval(() => {
            this.#checkConnection();
        }, this.#checkInterval);
    }

    static #checkConnection() {
        const now = Date.now();
        if (this.#lastOnlineCheck && (now - this.#lastOnlineCheck) < this.#checkInterval) {
            return;
        }

        this.#lastOnlineCheck = now;
        fetch('/api/health-check', {
            method: 'HEAD',
            cache: 'no-cache'
        })
        .then(() => {
            if (this.#isOffline) {
                this.#handleOnline();
            }
        })
        .catch(() => {
            if (!this.#isOffline) {
                this.#handleOffline();
            }
        });
    }

    static #handleOnline() {
        this.#isOffline = false;
        NotificationService.success('Đã khôi phục kết nối');
        this.#processRetryQueue();
    }

    static #handleOffline() {
        this.#isOffline = true;
        NotificationService.error('Mất kết nối mạng. Đang thử kết nối lại...');
    }

    static handle(error) {
        if (error.response) {
            // Server responded with error
            this.#handleServerError(error);
        } else if (error.request) {
            // Request made but no response
            this.#handleNetworkError(error);
        } else {
            // Something else happened
            this.#handleUnknownError(error);
        }
    }

    static #handleServerError(error) {
        const { status, data } = error.response;
        
        switch (status) {
            case 400:
                NotificationService.error(data.message || 'Yêu cầu không hợp lệ');
                break;
            case 401:
                NotificationService.error('Phiên đăng nhập đã hết hạn');
                // Redirect to login
                window.location.href = '/login';
                break;
            case 403:
                NotificationService.error('Bạn không có quyền thực hiện thao tác này');
                break;
            case 404:
                NotificationService.error('Không tìm thấy tài nguyên');
                break;
            case 500:
                NotificationService.error('Lỗi máy chủ. Vui lòng thử lại sau');
                break;
            default:
                NotificationService.error(data.message || 'Đã xảy ra lỗi không xác định');
        }
    }

    static #handleNetworkError(error) {
        if (this.#isOffline) {
            NotificationService.error('Không thể kết nối đến máy chủ. Vui lòng kiểm tra kết nối mạng');
        } else {
            NotificationService.error('Mất kết nối đến máy chủ. Đang thử kết nối lại...');
            this.#addToRetryQueue(error.config);
        }
    }

    static #handleUnknownError(error) {
        console.error('Unknown error:', error);
        NotificationService.error('Đã xảy ra lỗi không xác định');
    }

    static #addToRetryQueue(config) {
        if (config.retryCount === undefined) {
            config.retryCount = 0;
        }

        if (config.retryCount < this.#maxRetries) {
            config.retryCount++;
            this.#retryQueue.push({
                config,
                timestamp: Date.now()
            });
        }
    }

    static #processRetryQueue() {
        if (this.#retryQueue.length === 0) return;

        const now = Date.now();
        const retryable = this.#retryQueue.filter(item => {
            return (now - item.timestamp) < 300000; // 5 minutes
        });

        this.#retryQueue = this.#retryQueue.filter(item => {
            return (now - item.timestamp) >= 300000;
        });

        retryable.forEach(item => {
            axios(item.config)
                .then(response => {
                    if (typeof item.config.onSuccess === 'function') {
                        item.config.onSuccess(response);
                    }
                })
                .catch(error => {
                    if (typeof item.config.onError === 'function') {
                        item.config.onError(error);
                    }
                });
        });
    }

    static isOffline() {
        return this.#isOffline;
    }
}

// Initialize the service when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    NetworkErrorHandler.init();
}); 