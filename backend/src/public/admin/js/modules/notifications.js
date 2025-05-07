const Notifications = {
    // Initialize notifications
    init: function() {
        this.loadNotifications();
        this.setupEventListeners();
        this.startPolling();
    },

    // Load notifications from API
    loadNotifications: async function() {
        try {
            const response = await api.call('/notifications.php');
            if (response.success) {
                this.renderNotifications(response.data);
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    },

    // Render notifications in the UI
    renderNotifications: function(notifications) {
        const container = document.querySelector('.toast-container');
        if (!container) return;

        // Clear existing notifications
        container.innerHTML = '';

        // Add new notifications
        notifications.forEach(notification => {
            if (!notification.is_read) {
                const toast = this.createToastElement(notification);
                container.appendChild(toast);
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();
            }
        });

        // Update notification count in navbar if exists
        const unreadCount = notifications.filter(n => !n.is_read).length;
        this.updateNotificationCount(unreadCount);
    },

    // Create toast element for a notification
    createToastElement: function(notification) {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        toast.setAttribute('data-notification-id', notification.id);

        const icon = this.getNotificationIcon(notification.type);
        const timeAgo = this.getTimeAgo(notification.created_at);

        toast.innerHTML = `
            <div class="toast-header">
                <i class="fas ${icon} me-2"></i>
                <strong class="me-auto">${notification.title}</strong>
                <small>${timeAgo}</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${notification.message}
            </div>
        `;

        return toast;
    },

    // Get appropriate icon for notification type
    getNotificationIcon: function(type) {
        const icons = {
            'leave_approved': 'fa-check-circle text-success',
            'leave_rejected': 'fa-times-circle text-danger',
            'leave_pending': 'fa-clock text-warning',
            'leave_cancelled': 'fa-ban text-info'
        };
        return icons[type] || 'fa-bell text-primary';
    },

    // Format time ago
    getTimeAgo: function(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;

        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (minutes < 60) {
            return `${minutes} phút trước`;
        } else if (hours < 24) {
            return `${hours} giờ trước`;
        } else {
            return `${days} ngày trước`;
        }
    },

    // Update notification count in navbar
    updateNotificationCount: function(count) {
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline' : 'none';
        }
    },

    // Mark notification as read
    markAsRead: async function(notificationId) {
        try {
            const response = await api.call('/notifications.php', 'POST', { id: notificationId });
            if (response.success) {
                this.loadNotifications(); // Reload notifications
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    },

    // Delete notification
    deleteNotification: async function(notificationId) {
        try {
            const response = await api.call(`/notifications.php?id=${notificationId}`, 'DELETE');
            if (response.success) {
                this.loadNotifications(); // Reload notifications
            }
        } catch (error) {
            console.error('Error deleting notification:', error);
        }
    },

    // Setup event listeners
    setupEventListeners: function() {
        document.addEventListener('click', (e) => {
            // Handle notification click
            if (e.target.closest('.toast')) {
                const toast = e.target.closest('.toast');
                const notificationId = toast.dataset.notificationId;
                this.markAsRead(notificationId);
            }
        });
    },

    // Start polling for new notifications
    startPolling: function() {
        setInterval(() => {
            this.loadNotifications();
        }, 30000); // Poll every 30 seconds
    }
};

// Initialize notifications when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    Notifications.init();
}); 