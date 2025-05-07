// Loading overlay module
const LoadingOverlay = {
    init() {
        console.log('Loading overlay module initialized');
        this.createOverlay();
    },

    createOverlay() {
        const overlay = document.createElement('div');
        overlay.id = 'loading-overlay';
        overlay.className = 'loading-overlay';

        const container = document.createElement('div');
        container.className = 'loading-container';

        const loader = document.createElement('span');
        loader.className = 'loader';

        const message = document.createElement('div');
        message.className = 'loading-message';
        message.textContent = 'Đang tải...';

        container.appendChild(loader);
        container.appendChild(message);
        overlay.appendChild(container);
        document.body.appendChild(overlay);
    },

    show(message = 'Đang tải...') {
        const overlay = document.getElementById('loading-overlay');
        const messageEl = overlay.querySelector('.loading-message');
        
        if (overlay) {
            overlay.classList.add('show');
            messageEl.textContent = message;
        }
    },

    hide() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.classList.remove('show');
        }
    },

    updateMessage(message) {
        const messageEl = document.querySelector('.loading-message');
        if (messageEl) {
            messageEl.textContent = message;
        }
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    LoadingOverlay.init();
}); 