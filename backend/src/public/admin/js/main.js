// Import all modules from index.js
import * as modules from './index.js';

// Loading state management
const loadingState = {
    isLoading: false,
    setLoading: (state) => {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.toggle('d-none', !state);
        }
    }
};

// Error handling
const errorHandler = {
    handleError: (error, context) => {
        console.error(`Error in ${context}:`, error);
        showNotification('error', `Lỗi ${context}: ${error.message}`);
    }
};

// Configuration validation
function validateConfig() {
    const requiredConfigs = [
        'API_BASE_URL',
        'AUTH_TOKEN_KEY',
        'DEFAULT_LANGUAGE'
    ];

    const missingConfigs = requiredConfigs.filter(config => !window[config]);
    if (missingConfigs.length > 0) {
        throw new Error(`Missing required configurations: ${missingConfigs.join(', ')}`);
    }
}

// Task management functions
function loadTasks() {
    // Implementation for loading tasks
}

// Weather widget functions
function updateWeather() {
    // Implementation for updating weather
}

// Chat functions
function loadChats() {
    // Implementation for loading chats
}

// Backup functions
function loadBackupInfo() {
    // Implementation for loading backup information
}

// Make modules available globally
window.modules = modules;
window.loadingState = loadingState;
window.errorHandler = errorHandler;

// Main application module
const App = {
    init() {
        console.log('Application initialized');
        this.validateConfig();
        this.loadModules();
        this.setupEventListeners();
    },

    validateConfig() {
        try {
            validateConfig();
        } catch (error) {
            errorHandler.handleError(error, 'Configuration');
        }
    },

    loadModules() {
        // Load all required modules
        const modules = [
            'dashboard_features',
            'export-data',
            /*
            'dark-mode',
            */
            'loading-overlay',
            'notification-handler',
            'activity-filter',
            'mobile-stats',
            'change-password',
            'profile',
            'attendance-employee',
            'leaves-employee',
            'dashboard-employee',
            'login',
            'forgot-password',
            'reset-password',
            'activity-log'
        ];

        modules.forEach(module => {
            const script = document.createElement('script');
            script.src = `js/modules/${module}.js`;
            script.async = true;
            document.head.appendChild(script);
        });
    },

    setupEventListeners() {
        // Setup global event listeners
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-loading]')) {
                loadingState.setLoading(true);
            }
        });

        // Setup error handling
        window.addEventListener('error', (event) => {
            errorHandler.handleError(event.error, 'Global');
        });

        // Setup unhandled promise rejections
        window.addEventListener('unhandledrejection', (event) => {
            errorHandler.handleError(event.reason, 'Promise');
        });
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    App.init();
});

async function loadUserProfile() {
    try {
        const response = await fetch('/qlnhansu_V2/backend/src/public/admin/api/user_api.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });

        if (!response.ok) {
            throw new Error('Failed to load user profile');
        }

        const data = await response.json();
        updateUserProfile(data);
    } catch (error) {
        console.error('Error loading user profile:', error);
        showError('Failed to load user profile');
    }
} 