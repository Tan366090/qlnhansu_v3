// Common utility functions
const Common = {
    showLoading: () => {
        const loading = document.querySelector('.loading');
        if (loading) {
            loading.style.display = 'flex';
        }
    },

    hideLoading: () => {
        const loading = document.querySelector('.loading');
        if (loading) {
            loading.style.display = 'none';
        }
    },

    showError: (message) => {
        const errorMessage = document.getElementById('errorMessage');
        if (errorMessage) {
            errorMessage.textContent = message;
            errorMessage.style.display = 'block';
        }
    },

    hideError: () => {
        const errorMessage = document.getElementById('errorMessage');
        if (errorMessage) {
            errorMessage.style.display = 'none';
        }
    },

    showSuccess: (message) => {
        const successMessage = document.getElementById('successMessage');
        if (successMessage) {
            successMessage.textContent = message;
            successMessage.style.display = 'block';
        }
    },

    hideSuccess: () => {
        const successMessage = document.getElementById('successMessage');
        if (successMessage) {
            successMessage.style.display = 'none';
        }
    }
};

export default Common; 