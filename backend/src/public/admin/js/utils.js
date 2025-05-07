// Common Utilities
const CommonUtils = {
    formatDate: (date) => {
        return new Date(date).toLocaleDateString(APP_CONFIG.DEFAULT_LANGUAGE);
    },
    formatCurrency: (amount) => {
        return new Intl.NumberFormat(APP_CONFIG.DEFAULT_LANGUAGE, {
            style: "currency",
            currency: APP_CONFIG.CURRENCY,
        }).format(amount);
    },
    formatTime: (date) => {
        return new Date(date).toLocaleTimeString(APP_CONFIG.DEFAULT_LANGUAGE);
    }
};

// Notification Utilities
const NotificationUtils = {
    show: (message, type = "info") => {
        const container = document.getElementById("notificationContainer");
        if (!container) {
            console.warn("Notification container not found");
            return;
        }

        const notification = document.createElement("div");
        notification.className = `notification ${type}`;
        notification.textContent = message;
        container.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => notification.remove(), 5000);
    }
};

// UI Utilities
const UIUtils = {
    toggleDarkMode: () => {
        document.body.classList.toggle("dark-mode");
        localStorage.setItem(
            "darkMode",
            document.body.classList.contains("dark-mode")
        );
    },
    showLoading: () => {
        const overlay = document.getElementById("loadingOverlay");
        if (overlay) {
            overlay.classList.remove("d-none");
        }
    },
    hideLoading: () => {
        const overlay = document.getElementById("loadingOverlay");
        if (overlay) {
            overlay.classList.add("d-none");
        }
    }
};

// Export utilities
window.CommonUtils = CommonUtils;
window.NotificationUtils = NotificationUtils;
window.UIUtils = UIUtils; 