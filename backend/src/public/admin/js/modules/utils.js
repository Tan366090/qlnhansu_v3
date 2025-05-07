// Utility functions
export const debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

export const throttle = (func, limit) => {
    let inThrottle;
    return function executedFunction(...args) {
        if (!inThrottle) {
            func(...args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
};

export const formatBytes = (bytes, decimals = 2) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
};

export const getRandomColor = () => {
    const letters = '0123456789ABCDEF';
    let color = '#';
    for (let i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
};

export const copyToClipboard = async (text) => {
    try {
        await navigator.clipboard.writeText(text);
        return true;
    } catch (err) {
        console.error('Failed to copy text: ', err);
        return false;
    }
};

// Common utilities
export const CommonUtils = {
    formatDate: (date) => {
        return new Date(date).toLocaleDateString("vi-VN");
    },
    formatCurrency: (amount) => {
        return new Intl.NumberFormat("vi-VN", {
            style: "currency",
            currency: "VND",
        }).format(amount);
    },
    showLoading: () => {
        const overlay = document.getElementById("loadingOverlay");
        if (overlay) overlay.style.display = "flex";
    },
    hideLoading: () => {
        const overlay = document.getElementById("loadingOverlay");
        if (overlay) overlay.style.display = "none";
    }
};

// Authentication utilities
export const AuthUtils = {
    isAuthenticated: () => {
        return localStorage.getItem("token") !== null;
    },
    logout: () => {
        localStorage.removeItem("token");
        window.location.href = "/login_new.html";
    },
    initSessionMonitoring: () => {
        setInterval(() => {
            if (!this.isAuthenticated()) {
                window.location.href = "/login_new.html";
            }
        }, 60000); // Check every minute
    }
};

// Permission utilities
export const PermissionUtils = {
    hasPermission: (permission) => {
        const userPermissions = JSON.parse(
            localStorage.getItem("permissions") || "[]"
        );
        return userPermissions.includes(permission);
    }
};

// Notification utilities
export const NotificationUtils = {
    show: (message, type = "info") => {
        const container = document.getElementById("notificationContainer");
        if (!container) return;
        
        const notification = document.createElement("div");
        notification.className = `notification ${type}`;
        notification.textContent = message;
        container.appendChild(notification);
        setTimeout(() => notification.remove(), 5000);
    }
};

// UI utilities
export const UIUtils = {
    // Comment out dark mode implementation
    /*
    toggleDarkMode: () => {
        document.body.classList.toggle("dark-mode");
        localStorage.setItem(
            "darkMode",
            document.body.classList.contains("dark-mode")
        );
    },
    */
    toggleSidebar: () => {
        document.querySelector('.sidebar').classList.toggle('collapsed');
    }
};

// API utilities
export const APIUtils = {
    baseUrl: '/qlnhansu_V2/backend/src/api',
    
    async fetchWithRetry(endpoint, options = {}, retryCount = 0) {
        try {
            const response = await fetch(`${this.baseUrl}/${endpoint}`, {
                ...options,
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            if (retryCount < 3) {
                await new Promise(resolve => setTimeout(resolve, 1000 * (retryCount + 1)));
                return this.fetchWithRetry(endpoint, options, retryCount + 1);
            }
            throw error;
        }
    }
}; 