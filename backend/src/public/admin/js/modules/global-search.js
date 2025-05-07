// Global search functionality
const GlobalSearch = {
    init() {
        this.setupEventListeners();
        this.setupSearchInput();
        this.setupClickOutsideHandler();
    },

    setupEventListeners() {
        const searchToggle = document.getElementById('searchToggle');
        if (searchToggle) {
            searchToggle.addEventListener('click', () => this.toggleSearch());
        }

        // Add ESC key handler
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeSearch();
            }
        });
    },

    setupClickOutsideHandler() {
        document.addEventListener('click', (e) => {
            const searchContainer = document.getElementById('searchContainer');
            const searchResults = document.getElementById('searchResults');
            const searchInput = document.getElementById('globalSearch');
            
            if (searchContainer && !searchContainer.contains(e.target) && 
                searchResults && !searchResults.contains(e.target) &&
                searchInput && !searchInput.contains(e.target)) {
                this.closeSearch();
            }
        });
    },

    setupSearchInput() {
        const searchInput = document.getElementById('globalSearch');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    this.performSearch(e.target.value);
                }
            });
            searchInput.addEventListener('focus', () => {
                const searchResults = document.getElementById('searchResults');
                if (searchResults) {
                    searchResults.classList.add('active');
                }
            });
        }
    },

    async handleSearch(query) {
        if (query.length < 2) {
            this.clearResults();
            return;
        }

        try {
            const response = await fetch(`/api/search?q=${encodeURIComponent(query)}`);
            if (!response.ok) throw new Error('Search failed');
            const results = await response.json();
            this.displayResults(results);
        } catch (error) {
            console.error('Search error:', error);
            this.displayError('Search failed. Please try again.');
        }
    },

    async performSearch(query) {
        if (!query.trim()) return;
        
        try {
            const response = await fetch(`/api/search/perform?q=${encodeURIComponent(query)}`);
            if (!response.ok) throw new Error('Search failed');
            const results = await response.json();
            this.displayResults(results);
        } catch (error) {
            console.error('Search error:', error);
            this.displayError('Search failed. Please try again.');
        }
    },

    displayResults(results) {
        const resultsContainer = document.getElementById('searchResults');
        if (!resultsContainer) return;

        if (!results || results.length === 0) {
            resultsContainer.innerHTML = '<div class="no-results">No results found</div>';
            return;
        }

        resultsContainer.innerHTML = results.map(result => `
            <div class="search-result-item">
                <a href="${result.url}">
                    <h4>${result.title}</h4>
                    <p>${result.description}</p>
                </a>
            </div>
        `).join('');
    },

    clearResults() {
        const resultsContainer = document.getElementById('searchResults');
        if (resultsContainer) {
            resultsContainer.innerHTML = '';
            resultsContainer.classList.remove('active');
        }
    },

    displayError(message) {
        const resultsContainer = document.getElementById('searchResults');
        if (resultsContainer) {
            resultsContainer.innerHTML = `<div class="search-error">${message}</div>`;
        }
    },

    toggleSearch() {
        const searchContainer = document.getElementById('searchContainer');
        if (searchContainer) {
            searchContainer.classList.toggle('show');
            if (searchContainer.classList.contains('show')) {
                const searchInput = document.getElementById('globalSearch');
                if (searchInput) {
                    searchInput.focus();
                }
            } else {
                this.closeSearch();
            }
        }
    },

    closeSearch() {
        const searchContainer = document.getElementById('searchContainer');
        const searchResults = document.getElementById('searchResults');
        const searchInput = document.getElementById('globalSearch');
        
        if (searchContainer) {
            searchContainer.classList.remove('show');
        }
        if (searchResults) {
            searchResults.classList.remove('active');
        }
        if (searchInput) {
            searchInput.value = '';
        }
        this.clearResults();
    }
};

// Initialize global search
document.addEventListener('DOMContentLoaded', () => {
    GlobalSearch.init();
}); 