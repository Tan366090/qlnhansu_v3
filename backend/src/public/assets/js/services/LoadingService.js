class LoadingService {
    static #overlay = null;
    static #spinner = null;
    static #message = null;
    static #progress = null;
    static #isLoading = false;
    static #loadingQueue = [];
    static #currentLoadingId = null;

    static init() {
        this.#createOverlay();
        this.#setupEventListeners();
    }

    static #createOverlay() {
        this.#overlay = document.createElement('div');
        this.#overlay.className = 'loading-overlay';
        this.#overlay.style.display = 'none';
        this.#overlay.style.opacity = '0';

        const container = document.createElement('div');
        container.className = 'loading-container';

        this.#spinner = document.createElement('div');
        this.#spinner.className = 'loading-spinner';

        this.#message = document.createElement('div');
        this.#message.className = 'loading-message';

        this.#progress = document.createElement('div');
        this.#progress.className = 'loading-progress';
        const progressBar = document.createElement('div');
        progressBar.className = 'progress-bar';
        this.#progress.appendChild(progressBar);

        container.appendChild(this.#spinner);
        container.appendChild(this.#message);
        container.appendChild(this.#progress);
        this.#overlay.appendChild(container);
        document.body.appendChild(this.#overlay);
    }

    static #setupEventListeners() {
        // Prevent clicks on the overlay from propagating
        this.#overlay.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    }

    static show(config = {}) {
        const loadingId = Math.random().toString(36).substr(2, 9);
        this.#loadingQueue.push(loadingId);

        if (!this.#isLoading) {
            this.#currentLoadingId = loadingId;
            this.#isLoading = true;
            this.#updateOverlay(config);
            this.#showOverlay();
        }

        return loadingId;
    }

    static hide(loadingId = null) {
        if (loadingId) {
            this.#loadingQueue = this.#loadingQueue.filter(id => id !== loadingId);
        } else {
            this.#loadingQueue = [];
        }

        if (this.#loadingQueue.length === 0) {
            this.#isLoading = false;
            this.#currentLoadingId = null;
            this.#hideOverlay();
        } else if (loadingId === this.#currentLoadingId) {
            this.#currentLoadingId = this.#loadingQueue[0];
            this.#updateOverlay();
        }
    }

    static updateProgress(progress, loadingId = null) {
        if (loadingId && loadingId !== this.#currentLoadingId) return;
        
        const progressBar = this.#progress.querySelector('.progress-bar');
        if (progressBar) {
            progressBar.style.width = `${Math.min(100, Math.max(0, progress))}%`;
        }
    }

    static updateMessage(message, loadingId = null) {
        if (loadingId && loadingId !== this.#currentLoadingId) return;
        
        if (this.#message) {
            this.#message.textContent = message;
        }
    }

    static #updateOverlay(config = {}) {
        const {
            message = 'Đang tải...',
            showSpinner = true,
            showMessage = true,
            showProgress = false,
            overlayColor = 'rgba(255, 255, 255, 0.8)',
            spinnerColor = '#3498db',
            progressColor = '#3498db',
            zIndex = 9999
        } = config;

        this.#overlay.style.backgroundColor = overlayColor;
        this.#overlay.style.zIndex = zIndex;

        this.#spinner.style.display = showSpinner ? 'block' : 'none';
        this.#spinner.style.borderTopColor = spinnerColor;

        this.#message.style.display = showMessage ? 'block' : 'none';
        this.#message.textContent = message;

        this.#progress.style.display = showProgress ? 'block' : 'none';
        const progressBar = this.#progress.querySelector('.progress-bar');
        if (progressBar) {
            progressBar.style.backgroundColor = progressColor;
        }
    }

    static #showOverlay() {
        this.#overlay.style.display = 'flex';
        requestAnimationFrame(() => {
            this.#overlay.style.opacity = '1';
        });
    }

    static #hideOverlay() {
        this.#overlay.style.opacity = '0';
        setTimeout(() => {
            this.#overlay.style.display = 'none';
        }, 300);
    }

    static isLoading() {
        return this.#isLoading;
    }

    static getCurrentLoadingId() {
        return this.#currentLoadingId;
    }
}

// Initialize the service when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    LoadingService.init();
}); 