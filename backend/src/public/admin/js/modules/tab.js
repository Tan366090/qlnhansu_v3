// Tab management
export class TabManager {
    constructor() {
        this.currentTab = null;
        this.tabs = new Map();
    }

    init() {
        // Initialize tab buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            const tabId = button.dataset.tab;
            this.tabs.set(tabId, {
                button,
                content: document.getElementById(tabId),
                isActive: false
            });

            button.addEventListener('click', () => this.switchTab(tabId));
        });

        // Activate first tab by default
        const firstTab = this.tabs.keys().next().value;
        if (firstTab) {
            this.switchTab(firstTab);
        }
    }

    switchTab(tabId) {
        // Deactivate current tab
        if (this.currentTab) {
            const currentTab = this.tabs.get(this.currentTab);
            currentTab.button.classList.remove('active');
            currentTab.content.classList.remove('active');
            currentTab.isActive = false;
        }

        // Activate new tab
        const newTab = this.tabs.get(tabId);
        if (newTab) {
            newTab.button.classList.add('active');
            newTab.content.classList.add('active');
            newTab.isActive = true;
            this.currentTab = tabId;
        }
    }

    getCurrentTab() {
        return this.currentTab;
    }

    isTabActive(tabId) {
        const tab = this.tabs.get(tabId);
        return tab ? tab.isActive : false;
    }
}

// Initialize tab manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const tabManager = new TabManager();
    tabManager.init();
}); 