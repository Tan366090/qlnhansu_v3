export class ErrorHandler {
    constructor() {
        this.notificationContainer = document.getElementById('notificationContainer');
    }

    initialize() {
        window.addEventListener('error', (event) => this.handleError(event.error));
        window.addEventListener('unhandledrejection', (event) => this.handleError(event.reason));
    }

    handleError(error) {
        console.error('Error:', error);
        
        let message = 'Đã xảy ra lỗi';
        if (error instanceof Error) {
            message = error.message;
        } else if (typeof error === 'string') {
            message = error;
        }

        this.showError(message);
    }

    showError(message) {
        if (!this.notificationContainer) {
            console.error('Notification container not found');
            return;
        }

        const notification = document.createElement('div');
        notification.className = 'alert alert-danger alert-dismissible fade show';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        this.notificationContainer.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    showSuccess(message) {
        if (!this.notificationContainer) {
            console.error('Notification container not found');
            return;
        }

        const notification = document.createElement('div');
        notification.className = 'alert alert-success alert-dismissible fade show';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        this.notificationContainer.appendChild(notification);

        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
} 