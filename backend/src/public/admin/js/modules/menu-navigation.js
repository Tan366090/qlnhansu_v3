class MenuNavigation {
    constructor() {
        this.mainContent = document.querySelector('.main-content');
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Handle all menu item clicks
        document.addEventListener('click', async (e) => {
            const menuItem = e.target.closest('.nav-link');
            if (menuItem) {
                e.preventDefault();
                const url = menuItem.getAttribute('href');
                if (url) {
                    try {
                        // Show loading state
                        this.showLoading();
                        
                        // Fetch content
                        const response = await fetch(url);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const content = await response.text();
                        
                        // Update main content
                        if (this.mainContent) {
                            this.mainContent.innerHTML = content;
                            
                            // Reinitialize any scripts in the loaded content
                            this.reinitializeScripts();
                        }
                        
                        // Update active menu item
                        this.updateActiveMenuItem(menuItem);
                        
                        // Update browser history
                        history.pushState({ url: url }, '', url);
                        
                        // Hide loading state
                        this.hideLoading();
                    } catch (error) {
                        console.error('Error loading content:', error);
                        this.showError('Không thể tải nội dung');
                        this.hideLoading();
                    }
                }
            }
        });

        // Handle browser back/forward
        window.addEventListener('popstate', async (e) => {
            if (e.state && e.state.url) {
                try {
                    this.showLoading();
                    const response = await fetch(e.state.url);
                    const content = await response.text();
                    if (this.mainContent) {
                        this.mainContent.innerHTML = content;
                        this.reinitializeScripts();
                    }
                    this.hideLoading();
                } catch (error) {
                    console.error('Error loading content:', error);
                    this.showError('Không thể tải nội dung');
                    this.hideLoading();
                }
            }
        });
    }

    reinitializeScripts() {
        // Reinitialize any scripts that need to be run after content is loaded
        const scripts = this.mainContent.querySelectorAll('script');
        scripts.forEach(script => {
            const newScript = document.createElement('script');
            newScript.textContent = script.textContent;
            script.parentNode.replaceChild(newScript, script);
        });
    }

    updateActiveMenuItem(activeItem) {
        // Remove active class from all menu items
        document.querySelectorAll('.nav-link').forEach(item => {
            item.classList.remove('active');
        });
        
        // Add active class to clicked item
        activeItem.classList.add('active');
        
        // Also update parent nav-item if exists
        const parentNavItem = activeItem.closest('.nav-item');
        if (parentNavItem) {
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            parentNavItem.classList.add('active');
        }
    }

    showLoading() {
        if (this.mainContent) {
            this.mainContent.innerHTML = `
                <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
        }
    }

    hideLoading() {
        // Loading state is automatically removed when content is loaded
    }

    showError(message) {
        if (this.mainContent) {
            this.mainContent.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    ${message}
                </div>
            `;
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new MenuNavigation();
}); 