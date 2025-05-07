// Import services
import { NotificationService } from './services/NotificationService.js';
import { LoadingService } from './services/LoadingService.js';
import { NetworkErrorHandler } from './services/NetworkErrorHandler.js';
import { NotificationSounds } from './services/NotificationSounds.js';

// Initialize services
document.addEventListener('DOMContentLoaded', () => {
    // Initialize all services
    NotificationService.init();
    LoadingService.init();
    NetworkErrorHandler.init();
    NotificationSounds.init();

    // Setup global error handler
    window.addEventListener('error', (event) => {
        NetworkErrorHandler.handle({
            message: event.message,
            filename: event.filename,
            lineno: event.lineno,
            colno: event.colno
        });
    });

    // Setup unhandled promise rejection handler
    window.addEventListener('unhandledrejection', (event) => {
        NetworkErrorHandler.handle(event.reason);
    });

    // Setup axios interceptor for loading states
    axios.interceptors.request.use((config) => {
        const loadingId = LoadingService.show({
            message: 'Đang tải dữ liệu...',
            showProgress: true
        });
        config.loadingId = loadingId;
        return config;
    }, (error) => {
        return Promise.reject(error);
    });

    axios.interceptors.response.use((response) => {
        if (response.config.loadingId) {
            LoadingService.hide(response.config.loadingId);
        }
        return response;
    }, (error) => {
        if (error.config.loadingId) {
            LoadingService.hide(error.config.loadingId);
        }
        NetworkErrorHandler.handle(error);
        return Promise.reject(error);
    });

    // Setup fetch interceptor for loading states
    const originalFetch = window.fetch;
    window.fetch = async (...args) => {
        const loadingId = LoadingService.show({
            message: 'Đang tải dữ liệu...',
            showProgress: true
        });

        try {
            const response = await originalFetch(...args);
            LoadingService.hide(loadingId);
            return response;
        } catch (error) {
            LoadingService.hide(loadingId);
            NetworkErrorHandler.handle(error);
            throw error;
        }
    };
});

// Export services for use in other modules
export {
    NotificationService,
    LoadingService,
    NetworkErrorHandler,
    NotificationSounds
}; 