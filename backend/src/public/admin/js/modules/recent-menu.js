/**
 * @module recent-menu
 * @description Handles recent menu items functionality
 */

// Recent menu functionality
class RecentMenu {
    constructor() {
        this.recentItems = [];
        this.maxItems = 5;
    }

    async init() {
        try {
            await this.loadRecentItems();
            this.render();
            this.setupEventListeners();
        } catch (error) {
            console.error('Error initializing recent menu:', error);
            this.showError('Không thể tải menu gần đây');
        }
    }

    async loadRecentItems() {
        try {
            const response = await fetch('/qlnhansu_V2/backend/src/public/api/recent-items.php');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            
            // Kiểm tra dữ liệu trả về
            if (!Array.isArray(data)) {
                throw new Error('Invalid data format received from API');
            }
            
            // Lưu dữ liệu và giới hạn số lượng item
            this.recentItems = data.slice(0, this.maxItems);
        } catch (error) {
            console.error('Error loading recent items:', error);
            this.recentItems = [];
            throw error;
        }
    }

    render() {
        const container = document.querySelector('.recent-menu');
        if (!container) {
            console.warn('Recent menu container not found');
            return;
        }

        if (this.recentItems.length === 0) {
            container.innerHTML = '<p class="text-muted">Không có menu gần đây</p>';
            return;
        }

        const html = `
            <div class="recent-menu-header">
                <h4>Menu gần đây</h4>
            </div>
            <ul class="recent-items-list list-unstyled">
                ${this.recentItems.map(item => `
                    <li class="recent-item mb-2">
                        <a href="${item.url}" class="recent-item-link d-flex align-items-center text-decoration-none">
                            <i class="${item.icon} me-2"></i>
                            <span>${item.title}</span>
                        </a>
                    </li>
                `).join('')}
            </ul>
        `;
        container.innerHTML = html;
    }

    showError(message) {
        const container = document.querySelector('.recent-menu');
        if (container) {
            container.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    ${message}
                </div>
            `;
        }
    }

    setupEventListeners() {
        const items = document.querySelectorAll('.recent-item-link');
        items.forEach(item => {
            item.addEventListener('click', async (e) => {
                e.preventDefault();
                const url = item.getAttribute('href');
                if (url) {
                    try {
                        const response = await fetch(url);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const content = await response.text();
                        
                        // Update main content
                        const mainContent = document.querySelector('.main-content');
                        if (mainContent) {
                            mainContent.innerHTML = content;
                        }
                        
                        // Add to recent items
                        const title = item.querySelector('span').textContent;
                        const icon = item.querySelector('i').className;
                        this.addItem(title, url, icon);
                    } catch (error) {
                        console.error('Error loading content:', error);
                        this.showError('Không thể tải nội dung');
                    }
                }
            });
        });
    }

    addItem(title, url, icon) {
        // Add new item to the beginning of the array
        this.recentItems.unshift({ title, url, icon });
        
        // Keep only maxItems
        if (this.recentItems.length > this.maxItems) {
            this.recentItems = this.recentItems.slice(0, this.maxItems);
        }
        
        // Save to localStorage
        localStorage.setItem('recentMenuItems', JSON.stringify(this.recentItems));
        
        // Re-render the menu
        this.render();
        this.setupEventListeners();
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const recentMenu = new RecentMenu();
    recentMenu.init();
}); 