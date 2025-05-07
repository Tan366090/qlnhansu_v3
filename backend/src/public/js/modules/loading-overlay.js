// Loading Overlay Module
const LoadingOverlay = {
    init() {
        // Create loading overlay element
        const overlay = document.createElement('div');
        overlay.id = 'loading-overlay';
        overlay.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2">Đang tải...</div>
            </div>
        `;
        document.body.appendChild(overlay);

        // Add styles
        const style = document.createElement('style');
        style.textContent = `
            #loading-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(255, 255, 255, 0.8);
                display: none;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }

            #loading-overlay.active {
                display: flex;
            }

            .loading-spinner {
                text-align: center;
            }

            .loading-spinner .spinner-border {
                width: 3rem;
                height: 3rem;
            }
        `;
        document.head.appendChild(style);
    },

    show() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.classList.add('active');
        }
    },

    hide() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.classList.remove('active');
        }
    }
};

// Initialize loading overlay when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    LoadingOverlay.init();
});

// Export the module
export default LoadingOverlay; 