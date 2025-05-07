// Common utilities for the dashboard
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
        document.getElementById('loadingOverlay').classList.remove('d-none');
    },

    hideLoading: () => {
        document.getElementById('loadingOverlay').classList.add('d-none');
    },

    showNotification: (message, type = 'info') => {
        const container = document.getElementById('notificationContainer');
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        container.appendChild(notification);
        setTimeout(() => notification.remove(), 5000);
    },

    validateEmail: (email) => {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },

    validatePhone: (phone) => {
        return /^[0-9]{10,11}$/.test(phone);
    },

    sanitizeInput: (input) => {
        if (typeof input !== 'string') return input;
        return input.replace(/[<>]/g, '');
    }
}; 