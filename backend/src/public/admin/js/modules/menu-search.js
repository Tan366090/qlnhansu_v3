// Menu Search Functionality
class MenuSearch {
    constructor() {
        this.searchInput = document.querySelector('.menu-search-input');
        this.menuItems = document.querySelectorAll('.menu-item');
        this.init();
    }

    init() {
        if (this.searchInput) {
            this.searchInput.addEventListener('input', this.handleSearch.bind(this));
        }
    }

    handleSearch(event) {
        const searchTerm = event.target.value.toLowerCase();
        
        this.menuItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }
}

// Initialize menu search when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new MenuSearch();
}); 