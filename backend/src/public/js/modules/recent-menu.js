// Recent Menu Module
class RecentMenu {
    constructor() {
        this.maxItems = 5;
        this.storageKey = 'recentMenuItems';
        this.init();
    }

    init() {
        this.loadRecentItems();
        this.setupEventListeners();
    }

    loadRecentItems() {
        const items = this.getStoredItems();
        this.renderItems(items);
    }

    getStoredItems() {
        const stored = localStorage.getItem(this.storageKey);
        return stored ? JSON.parse(stored) : [];
    }

    renderItems(items) {
        const container = document.getElementById('recent-menu-items');
        if (!container) return;

        container.innerHTML = items.map(item => `
            <a href="${item.url}" class="recent-menu-item">
                <i class="${item.icon}"></i>
                <span>${item.title}</span>
            </a>
        `).join('');
    }

    addItem(title, url, icon) {
        const items = this.getStoredItems();
        
        // Remove if already exists
        const existingIndex = items.findIndex(item => item.url === url);
        if (existingIndex !== -1) {
            items.splice(existingIndex, 1);
        }

        // Add new item to beginning
        items.unshift({ title, url, icon });

        // Keep only maxItems
        if (items.length > this.maxItems) {
            items.pop();
        }

        // Save and render
        localStorage.setItem(this.storageKey, JSON.stringify(items));
        this.renderItems(items);
    }

    setupEventListeners() {
        // Listen for menu item clicks
        document.addEventListener('click', (e) => {
            const menuItem = e.target.closest('.menu-item');
            if (menuItem) {
                const title = menuItem.querySelector('.menu-title').textContent;
                const url = menuItem.getAttribute('href');
                const icon = menuItem.querySelector('i').className;
                this.addItem(title, url, icon);
            }
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.recentMenu = new RecentMenu();
}); 