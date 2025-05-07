// Import services
import { NotificationService } from './services/NotificationService.js';
import { LoadingService } from './services/LoadingService.js';
import { NetworkErrorHandler } from './services/NetworkErrorHandler.js';
import { NotificationSounds } from './services/NotificationSounds.js';

// Import required modules
import AuthUtils from '/shared/js/auth_utils.js';
import Common from '/shared/js/common.js';
import ApiService from '/shared/js/api_service.js';

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

    // Check if user is already logged in
    const token = AuthUtils.getCookie('token');
    if (token && window.location.pathname === '/login.html') {
        window.location.href = '/dashboard.html';
        return;
    }

    // Initialize form handlers
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        initializeLoginForm(loginForm);
    }

    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    if (forgotPasswordForm) {
        initializeForgotPasswordForm(forgotPasswordForm);
    }

    const changePasswordForm = document.getElementById('changePasswordForm');
    if (changePasswordForm) {
        initializeChangePasswordForm(changePasswordForm);
    }
});

function initializeLoginForm(form) {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        Common.hideError();
        Common.showLoading();

        const username = form.querySelector('#username').value;
        const password = form.querySelector('#password').value;

        try {
            const response = await ApiService.login(username, password);
            if (response.success) {
                AuthUtils.setCookie('token', response.token, 7);
                window.location.href = response.redirect || '/dashboard.html';
            } else {
                Common.showError(response.error || 'Login failed');
            }
        } catch (error) {
            Common.showError(error.message);
        } finally {
            Common.hideLoading();
        }
    });
}

function initializeForgotPasswordForm(form) {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        Common.hideError();
        Common.showLoading();

        const email = form.querySelector('#email').value;

        try {
            const response = await ApiService.forgotPassword(email);
            if (response.success) {
                Common.showSuccess('Password reset instructions have been sent to your email');
                setTimeout(() => {
                    window.location.href = '/verify_otp.html';
                }, 2000);
            } else {
                Common.showError(response.error || 'Failed to process request');
            }
        } catch (error) {
            Common.showError(error.message);
        } finally {
            Common.hideLoading();
        }
    });
}

function initializeChangePasswordForm(form) {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        Common.hideError();
        Common.showLoading();

        const currentPassword = form.querySelector('#current-password').value;
        const newPassword = form.querySelector('#new-password').value;
        const confirmPassword = form.querySelector('#confirm-password').value;

        if (newPassword !== confirmPassword) {
            Common.showError('New passwords do not match');
            Common.hideLoading();
            return;
        }

        try {
            const response = await ApiService.resetPassword(
                AuthUtils.getCookie('email'),
                newPassword,
                AuthUtils.getCookie('token')
            );
            if (response.success) {
                Common.showSuccess('Password has been changed successfully');
                setTimeout(() => {
                    window.location.href = '/login.html';
                }, 2000);
            } else {
                Common.showError(response.error || 'Failed to change password');
            }
        } catch (error) {
            Common.showError(error.message);
        } finally {
            Common.hideLoading();
        }
    });
}

// Export services for use in other modules
export {
    NotificationService,
    LoadingService,
    NetworkErrorHandler,
    NotificationSounds
}; 