class MenuHandler {
    constructor() {
        this.menuItems = [];
        this.activeItem = null;
        this.mobileMenu = null;
        this.mobileMenuToggle = null;
        this.init();
    }

    init() {
        this.loadMenuItems();
        this.setupEventListeners();
        this.setActiveMenuItem();
    }

    loadMenuItems() {
        // Load menu items from the DOM
        this.menuItems = Array.from(document.querySelectorAll('.nav-item'));
        this.mobileMenu = document.querySelector('.mobile-menu');
        this.mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    }

    setupEventListeners() {
        // Add click event listeners to menu items
        this.menuItems.forEach(item => {
            item.addEventListener('click', (e) => this.handleMenuItemClick(e, item));
        });

        // Mobile menu toggle
        if (this.mobileMenuToggle) {
            this.mobileMenuToggle.addEventListener('click', () => this.toggleMobileMenu());
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (this.mobileMenu && this.mobileMenu.classList.contains('active') && 
                !e.target.closest('.mobile-menu') && 
                !e.target.closest('.mobile-menu-toggle')) {
                this.mobileMenu.classList.remove('active');
            }
        });
    }

    handleMenuItemClick(e, item) {
        const submenu = item.querySelector('.submenu');
        if (submenu) {
            e.preventDefault();
            this.toggleSubmenu(submenu);
        }
        this.setActiveMenuItem(item);
    }

    toggleSubmenu(submenu) {
        submenu.classList.toggle('active');
    }

    toggleMobileMenu() {
        if (this.mobileMenu) {
            this.mobileMenu.classList.toggle('active');
        }
    }

    setActiveMenuItem(item = null) {
        if (item) {
            this.activeItem = item;
        } else {
            // Set active menu item based on current URL
            const currentPath = window.location.pathname;
            this.menuItems.forEach(menuItem => {
                const link = menuItem.querySelector('a');
                if (link && currentPath.includes(link.getAttribute('href'))) {
                    this.activeItem = menuItem;
                    menuItem.classList.add('active');
                } else {
                    menuItem.classList.remove('active');
                }
            });
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.menuHandler = new MenuHandler();
}); 