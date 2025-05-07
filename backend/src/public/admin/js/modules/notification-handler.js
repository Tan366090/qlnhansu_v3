// Notification handler module
const NotificationHandler = {
    init() {
        console.log('Notification handler module initialized');
        this.createContainer();
    },

    createContainer() {
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        `;
        document.body.appendChild(container);
    },

    show(message, type = 'info', duration = 3000) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.role = 'alert';
        
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        const container = document.getElementById('notification-container');
        container.appendChild(notification);

        // Auto remove after duration
        setTimeout(() => {
            notification.remove();
        }, duration);
    },

    success(message, duration = 3000) {
        this.show(message, 'success', duration);
    },

    error(message, duration = 3000) {
        this.show(message, 'danger', duration);
    },

    warning(message, duration = 3000) {
        this.show(message, 'warning', duration);
    },

    info(message, duration = 3000) {
        this.show(message, 'info', duration);
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    NotificationHandler.init();
}); 