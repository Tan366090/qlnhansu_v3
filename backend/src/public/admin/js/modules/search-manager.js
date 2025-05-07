class SearchManager {
    constructor() {
        this.searchTimeout = null;
        this.initializeSearch();
    }

    initializeSearch() {
        const searchInput = document.getElementById('globalSearch');
        const searchResults = document.getElementById('searchResults');
        const advancedSearchBtn = document.getElementById('advancedSearchBtn');
        const performAdvancedSearchBtn = document.getElementById('performAdvancedSearch');
        const searchCategory = document.getElementById('searchCategory');

        // Load initial data for comboboxes
        this.loadSearchData();

        // Handle category change
        searchCategory.addEventListener('change', () => {
            this.toggleFilterSections(searchCategory.value);
        });

        // Handle global search input
        searchInput.addEventListener('input', (e) => {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.performSearch(e.target.value);
            }, 300);
        });

        // Handle advanced search button click
        advancedSearchBtn.addEventListener('click', () => {
            this.initializeAdvancedSearch();
        });

        // Handle perform advanced search
        performAdvancedSearchBtn.addEventListener('click', () => {
            this.performAdvancedSearch();
        });

        // Close search results when clicking outside
        document.addEventListener('click', (e) => {
            if (!searchResults.contains(e.target) && e.target !== searchInput) {
                searchResults.classList.remove('show');
            }
        });
    }

    async loadSearchData() {
        try {
            // Load departments
            const departmentsResponse = await fetch('/qlnhansu_V2/backend/src/api/routes/employees.php?action=get_departments');
            if (!departmentsResponse.ok) {
                throw new Error(`HTTP error! status: ${departmentsResponse.status}`);
            }
            const departmentsData = await departmentsResponse.json();
            if (departmentsData.success) {
                this.populateSelect('departmentFilter', departmentsData.data);
            }

            // Load positions
            const positionsResponse = await fetch('/qlnhansu_V2/backend/src/api/routes/employees.php?action=get_positions');
            if (!positionsResponse.ok) {
                throw new Error(`HTTP error! status: ${positionsResponse.status}`);
            }
            const positionsData = await positionsResponse.json();
            if (positionsData.success) {
                this.populateSelect('positionFilter', positionsData.data);
            }

            // Load document categories
            const categoriesResponse = await fetch('/qlnhansu_V2/backend/src/api/routes/employees.php?action=get_document_categories');
            if (!categoriesResponse.ok) {
                throw new Error(`HTTP error! status: ${categoriesResponse.status}`);
            }
            const categoriesData = await categoriesResponse.json();
            if (categoriesData.success) {
                this.populateSelect('documentCategory', categoriesData.data);
            }

        } catch (error) {
            console.error('Error loading search data:', error);
            // Show error message to user
            const searchResults = document.getElementById('searchResults');
            if (searchResults) {
                searchResults.innerHTML = `
                    <div class="alert alert-danger">
                        Không thể tải dữ liệu tìm kiếm. Vui lòng thử lại sau.
                    </div>
                `;
                searchResults.classList.add('show');
            }
        }
    }

    populateSelect(selectId, data) {
        const select = document.getElementById(selectId);
        if (!select) return;

        // Clear existing options except the first one
        while (select.options.length > 1) {
            select.remove(1);
        }

        // Add new options
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            select.appendChild(option);
        });
    }

    toggleFilterSections(category) {
        // Hide all filter sections
        document.querySelectorAll('.filter-section').forEach(section => {
            section.style.display = 'none';
        });

        // Show relevant filter section
        const sectionId = `${category}Filters`;
        const section = document.getElementById(sectionId);
        if (section) {
            section.style.display = 'block';
        }
    }

    async performSearch(query) {
        if (!query.trim()) {
            document.getElementById('searchResults').classList.remove('show');
            return;
        }

        try {
            const response = await fetch(`/admin/api/search.php?query=${encodeURIComponent(query)}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const results = await response.json();
            this.displaySearchResults(results);
        } catch (error) {
            console.error('Search error:', error);
            const searchResults = document.getElementById('searchResults');
            if (searchResults) {
                searchResults.innerHTML = `
                    <div class="alert alert-danger">
                        Có lỗi xảy ra khi tìm kiếm. Vui lòng thử lại sau.
                    </div>
                `;
                searchResults.classList.add('show');
            }
        }
    }

    displaySearchResults(results) {
        const searchResults = document.getElementById('searchResults');
        if (!searchResults) return;

        if (!results || results.length === 0) {
            searchResults.innerHTML = '<div class="no-results">Không tìm thấy kết quả</div>';
        } else {
            const resultsHtml = results.map(result => `
                <div class="result-item">
                    <div class="result-title">${result.title}</div>
                    <div class="result-category">${result.category}</div>
                    <div class="result-description">${result.description}</div>
                    <div class="result-date">${result.date}</div>
                    <a href="${result.link}" class="btn btn-sm btn-primary">Xem chi tiết</a>
                </div>
            `).join('');

            searchResults.innerHTML = `
                <div class="search-results-section">
                    <h3>Kết quả tìm kiếm (${results.length})</h3>
                    <div class="results-grid">
                        ${resultsHtml}
                    </div>
                </div>
            `;
        }

        searchResults.classList.add('show');
    }

    initializeAdvancedSearch() {
        const dateFrom = document.getElementById('dateFrom');
        const dateTo = document.getElementById('dateTo');
        
        // Set default date range to last 30 days
        const today = new Date();
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(today.getDate() - 30);
        
        dateFrom.value = thirtyDaysAgo.toISOString().split('T')[0];
        dateTo.value = today.toISOString().split('T')[0];
    }

    async performAdvancedSearch() {
        const formData = new FormData(document.getElementById('advancedSearchForm'));
        const searchParams = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('/admin/api/search.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(searchParams)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const results = await response.json();
            this.displaySearchResults(results);
        } catch (error) {
            console.error('Error performing search:', error);
            const searchResults = document.getElementById('searchResults');
            if (searchResults) {
                searchResults.innerHTML = `
                    <div class="alert alert-danger">
                        Có lỗi xảy ra khi tìm kiếm. Vui lòng thử lại sau.
                    </div>
                `;
                searchResults.classList.add('show');
            }
        }
    }
}

// Initialize search manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new SearchManager();
}); 