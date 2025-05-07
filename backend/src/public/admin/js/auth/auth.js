import { CommonUtils } from '../utils/common.js';

export const AuthUtils = {
    isAuthenticated: () => {
        return localStorage.getItem(window.AUTH_TOKEN_KEY) !== null;
    },

    getToken: () => {
        return localStorage.getItem(window.AUTH_TOKEN_KEY);
    },

    setToken: (token) => {
        localStorage.setItem(window.AUTH_TOKEN_KEY, token);
    },

    removeToken: () => {
        localStorage.removeItem(window.AUTH_TOKEN_KEY);
    },

    logout: () => {
        this.removeToken();
        window.location.href = '/login.html';
    },

    checkSession: () => {
        if (!this.isAuthenticated()) {
            CommonUtils.showNotification('Phiên đăng nhập đã hết hạn', 'error');
            this.logout();
        }
    },

    getPermissions: () => {
        const permissions = localStorage.getItem('permissions');
        return permissions ? JSON.parse(permissions) : [];
    },

    hasPermission: (permission) => {
        const permissions = this.getPermissions();
        return permissions.includes(permission);
    }
}; 