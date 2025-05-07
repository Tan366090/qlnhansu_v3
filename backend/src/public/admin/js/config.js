// API Configuration
window.API_CONFIG = {
    BASE_URL: '/qlnhansu_V2/backend/src/api',
    ENDPOINTS: {
        USER_PROFILE: '/user/profile',
        RECENT_ITEMS: '/recent-items',
        HR_TRENDS: '/ai/hr-trends',
        SENTIMENT: '/ai/sentiment',
        LEADERBOARD: '/gamification/leaderboard',
        PROGRESS: '/gamification/progress'
    },
    WS_ENDPOINT: 'ws://localhost:8080/ws/notifications'
};

// Application Configuration
const APP_CONFIG = {
    DEFAULT_LANGUAGE: 'vi-VN',
    DATE_FORMAT: 'DD/MM/YYYY',
    TIME_FORMAT: 'HH:mm:ss',
    CURRENCY: 'VND',
    THEME: {
        DEFAULT: 'light',
        DARK: 'dark'
    }
};

// Helper function to get full API URL
window.getApiUrl = function(endpoint) {
    return `${window.API_CONFIG.BASE_URL}${endpoint}`;
};

// Export configurations
window.APP_CONFIG = APP_CONFIG; 