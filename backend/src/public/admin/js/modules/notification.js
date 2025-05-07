// Notification utilities
export const showNotification = (type, message, duration = 5000) => {
    const notification = document.createElement('div');
    notification.className = `notification ${type} show`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <div class="notification-content">${message}</div>
        <div class="notification-progress"></div>
    `;
    document.getElementById('notificationContainer').appendChild(notification);
    
    setTimeout(() => {
        notification.classList.remove('show');
        notification.classList.add('hide');
        setTimeout(() => notification.remove(), 300);
    }, duration);
};

export const showError = (message, duration = 5000) => {
    showNotification('error', message, duration);
};

export const showSuccess = (message, duration = 5000) => {
    showNotification('success', message, duration);
};

export const showWarning = (message, duration = 5000) => {
    showNotification('warning', message, duration);
};

export const showInfo = (message, duration = 5000) => {
    showNotification('info', message, duration);
}; 