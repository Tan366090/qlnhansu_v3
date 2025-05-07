// Menu Handler Class
class MenuHandler {
    constructor(menuId, options = {}) {
        this.menuId = menuId;
        this.options = {
            searchable: true,
            responsive: true,
            accessibility: true,
            ...options
        };
        this.setupMenu();
    }

    // Setup menu with search and responsive features
    setupMenu() {
        const menu = document.getElementById(this.menuId);
        if (!menu) return;

        if (this.options.searchable) {
            this.addSearchFunctionality(menu);
        }

        if (this.options.responsive) {
            this.addResponsiveFeatures(menu);
        }

        if (this.options.accessibility) {
            this.addAccessibilityFeatures(menu);
        }
    }

    // Add search functionality to menu
    addSearchFunctionality(menu) {
        const searchContainer = document.createElement('div');
        searchContainer.className = 'menu-search-container';
        searchContainer.innerHTML = `
            <input type="text" 
                   placeholder="Tìm kiếm menu..." 
                   aria-label="Tìm kiếm menu"
                   class="menu-search-input">
            <i class="fas fa-search"></i>
        `;
        menu.parentNode.insertBefore(searchContainer, menu);

        const searchInput = searchContainer.querySelector('input');
        searchInput.addEventListener('input', (e) => {
            this.filterMenuItems(e.target.value);
        });
    }

    // Filter menu items based on search text
    filterMenuItems(searchText) {
        const menu = document.getElementById(this.menuId);
        if (!menu) return;

        const menuItems = menu.querySelectorAll('li');
        menuItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            const matches = text.includes(searchText.toLowerCase());
            item.style.display = matches ? '' : 'none';
        });
    }

    // Add responsive features
    addResponsiveFeatures(menu) {
        const toggleButton = document.createElement('button');
        toggleButton.className = 'menu-toggle';
        toggleButton.innerHTML = '<i class="fas fa-bars"></i>';
        toggleButton.setAttribute('aria-label', 'Toggle menu');
        menu.parentNode.insertBefore(toggleButton, menu);

        toggleButton.addEventListener('click', () => {
            menu.classList.toggle('active');
            toggleButton.setAttribute('aria-expanded', 
                menu.classList.contains('active'));
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!menu.contains(e.target) && !toggleButton.contains(e.target)) {
                menu.classList.remove('active');
                toggleButton.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Add accessibility features
    addAccessibilityFeatures(menu) {
        const menuItems = menu.querySelectorAll('li');
        menuItems.forEach((item, index) => {
            item.setAttribute('role', 'menuitem');
            item.setAttribute('tabindex', '0');
            
            // Add keyboard navigation
            item.addEventListener('keydown', (e) => {
                switch(e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        const nextItem = menuItems[index + 1];
                        if (nextItem) nextItem.focus();
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        const prevItem = menuItems[index - 1];
                        if (prevItem) prevItem.focus();
                        break;
                    case 'Enter':
                    case ' ':
                        e.preventDefault();
                        item.click();
                        break;
                }
            });
        });
    }
}

// Notification Handler Class
class NotificationHandler {
    constructor(containerId = 'notification-container') {
        this.containerId = containerId;
        this.setupContainer();
    }

    setupContainer() {
        let container = document.getElementById(this.containerId);
        if (!container) {
            container = document.createElement('div');
            container.id = this.containerId;
            container.className = 'notification-container';
            document.body.appendChild(container);
        }
    }

    show(message, type = 'info', duration = 3000) {
        const container = document.getElementById(this.containerId);
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.setAttribute('role', 'alert');
        notification.innerHTML = `
            <i class="fas ${this.getIcon(type)}"></i>
            <span>${message}</span>
        `;
        container.appendChild(notification);

        // Add animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);

        // Remove after duration
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                container.removeChild(notification);
            }, 300);
        }, duration);
    }

    getIcon(type) {
        switch(type) {
            case 'success': return 'fa-check-circle';
            case 'error': return 'fa-exclamation-circle';
            case 'warning': return 'fa-exclamation-triangle';
            default: return 'fa-info-circle';
        }
    }
}

// Loading State Handler
class LoadingHandler {
    constructor(containerId = 'loading-overlay') {
        this.containerId = containerId;
        this.setupContainer();
    }

    setupContainer() {
        let container = document.getElementById(this.containerId);
        if (!container) {
            container = document.createElement('div');
            container.id = this.containerId;
            container.className = 'loading-overlay';
            container.innerHTML = `
                <div class="loading-spinner" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            `;
            document.body.appendChild(container);
        }
    }

    show() {
        const container = document.getElementById(this.containerId);
        container.style.display = 'flex';
    }

    hide() {
        const container = document.getElementById(this.containerId);
        container.style.display = 'none';
    }
}

// Error Boundary Component
class ErrorBoundary {
    constructor(elementId, fallbackMessage = 'Đã xảy ra lỗi') {
        this.elementId = elementId;
        this.fallbackMessage = fallbackMessage;
        this.setupErrorBoundary();
    }

    setupErrorBoundary() {
        const element = document.getElementById(this.elementId);
        if (!element) return;

        // Add error handling for async operations
        window.addEventListener('error', (event) => {
            this.handleError(event.error);
        });

        // Add error handling for unhandled promise rejections
        window.addEventListener('unhandledrejection', (event) => {
            this.handleError(event.reason);
        });
    }

    handleError(error) {
        console.error('Error caught by boundary:', error);
        const element = document.getElementById(this.elementId);
        if (element) {
            element.innerHTML = `
                <div class="error-boundary">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>${this.fallbackMessage}</h3>
                    <p>${error.message || 'Vui lòng thử lại sau'}</p>
                    <button onclick="window.location.reload()">Tải lại trang</button>
                </div>
            `;
        }
    }
}

// Security Enhancements
class SecurityHandler {
    static sanitizeInput(input) {
        return input.replace(/[<>]/g, '');
    }

    static validateInput(input, type) {
        switch(type) {
            case 'email':
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input);
            case 'password':
                return input.length >= 8;
            case 'number':
                return !isNaN(input);
            default:
                return true;
        }
    }

    static encodeHTML(str) {
        return str.replace(/[&<>'"]/g, 
            tag => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#39;',
                '"': '&quot;'
            }[tag]));
    }
}

// Example usage:
/*
const menuHandler = new MenuHandler('main-menu', {
    searchable: true,
    responsive: true,
    accessibility: true
});

const notificationHandler = new NotificationHandler();
notificationHandler.show('Thao tác thành công', 'success');

const loadingHandler = new LoadingHandler();
loadingHandler.show();
// ... do something
loadingHandler.hide();

const errorBoundary = new ErrorBoundary('app-container', 'Đã xảy ra lỗi không mong muốn');

// Security usage
const sanitizedInput = SecurityHandler.sanitizeInput(userInput);
const isValidEmail = SecurityHandler.validateInput(email, 'email');
const encodedHTML = SecurityHandler.encodeHTML(unsafeHTML);
*/ 