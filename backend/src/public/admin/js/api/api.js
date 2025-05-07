import { CommonUtils } from '../utils/common.js';
import { AuthUtils } from '../auth/auth.js';

export const APIUtils = {
    baseUrl: window.API_BASE_URL,

    async request(endpoint, options = {}) {
        try {
            CommonUtils.showLoading();
            
            const headers = {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${AuthUtils.getToken()}`
            };

            const response = await fetch(`${this.baseUrl}/${endpoint}`, {
                ...options,
                headers: {
                    ...headers,
                    ...options.headers
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Response is not JSON');
            }

            const data = await response.json();
            return data;
        } catch (error) {
            console.error(`API Error: ${endpoint}`, error);
            CommonUtils.showNotification(`Lỗi API: ${error.message}`, 'error');
            
            if (error.message.includes('401')) {
                AuthUtils.logout();
            }
            
            return null;
        } finally {
            CommonUtils.hideLoading();
        }
    },

    async get(endpoint) {
        return this.request(endpoint, { method: 'GET' });
    },

    async post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },

    async put(endpoint, data) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },

    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    },

    async getWithRetry(endpoint, maxRetries = 3) {
        for (let i = 0; i < maxRetries; i++) {
            try {
                const result = await this.get(endpoint);
                if (result !== null) return result;
            } catch (error) {
                if (i === maxRetries - 1) throw error;
                await new Promise(resolve => setTimeout(resolve, 1000 * (i + 1)));
            }
        }
        return null;
    }
}; 