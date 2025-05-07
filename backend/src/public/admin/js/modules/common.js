// Utility functions
export const formatNumber = (number) => {
    return new Intl.NumberFormat('vi-VN').format(number);
};

export const formatCurrency = (amount) => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
};

export const formatDate = (date) => {
    return new Intl.DateTimeFormat('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    }).format(new Date(date));
};

export const showLoading = () => {
    document.getElementById('loadingOverlay').style.display = 'flex';
};

export const hideLoading = () => {
    document.getElementById('loadingOverlay').style.display = 'none';
};

export const showError = (message) => {
    const notification = document.createElement('div');
    notification.className = 'notification error show';
    notification.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <div class="notification-content">${message}</div>
        <div class="notification-progress"></div>
    `;
    document.getElementById('notificationContainer').appendChild(notification);
    
    setTimeout(() => {
        notification.classList.remove('show');
        notification.classList.add('hide');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
};

export const showSuccess = (message) => {
    const notification = document.createElement('div');
    notification.className = 'notification success show';
    notification.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <div class="notification-content">${message}</div>
        <div class="notification-progress"></div>
    `;
    document.getElementById('notificationContainer').appendChild(notification);
    
    setTimeout(() => {
        notification.classList.remove('show');
        notification.classList.add('hide');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}; 