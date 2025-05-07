export class MenuHandler {
    constructor() {
        this.menuItems = [];
        this.activeItem = null;
    }

    initialize() {
        this.loadMenuItems();
        this.setupEventListeners();
    }

    loadMenuItems() {
        const menuContainer = document.querySelector('.sidebar-menu');
        if (!menuContainer) return;

        this.menuItems = Array.from(menuContainer.querySelectorAll('.menu-item'));
        this.setActiveMenuItem();
    }

    setupEventListeners() {
        this.menuItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleMenuItemClick(item);
            });
        });

        // Mobile menu toggle
        const menuToggle = document.querySelector('.menu-toggle');
        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                this.toggleMobileMenu();
            });
        }
    }

    handleMenuItemClick(item) {
        if (this.activeItem) {
            this.activeItem.classList.remove('active');
        }
        item.classList.add('active');
        this.activeItem = item;

        // Handle submenu if exists
        const submenu = item.querySelector('.submenu');
        if (submenu) {
            this.toggleSubmenu(submenu);
        }

        // Update URL if needed
        const href = item.getAttribute('href');
        if (href && href !== '#') {
            window.location.href = href;
        }
    }

    toggleSubmenu(submenu) {
        const isOpen = submenu.style.display === 'block';
        submenu.style.display = isOpen ? 'none' : 'block';
    }

    toggleMobileMenu() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.toggle('mobile-open');
        }
    }

    setActiveMenuItem() {
        const currentPath = window.location.pathname;
        const activeItem = this.menuItems.find(item => {
            const href = item.getAttribute('href');
            return href && currentPath.includes(href);
        });

        if (activeItem) {
            this.handleMenuItemClick(activeItem);
        }
    }
}

// Initialize when DOM is loaded
if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        new MenuHandler();
    });
}

// Export for testing
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MenuHandler;
} 