// Main application module
class MainApp {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeModules();
    }

    setupEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            // Add global event listeners here
        });
    }

    initializeModules() {
        // Initialize all modules
        // The modules will be initialized automatically when their files are loaded
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    showLoading() {
        const loading = document.createElement('div');
        loading.className = 'loading-overlay';
        loading.innerHTML = '<div class="spinner"></div>';
        document.body.appendChild(loading);
    }

    hideLoading() {
        const loading = document.querySelector('.loading-overlay');
        if (loading) {
            loading.remove();
        }
    }
}

// Initialize main application
const mainApp = new MainApp(); 