// Activity filter module
class ActivityFilter {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadFilters();
    }

    setupEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            const filterButtons = document.querySelectorAll('.filter-btn');
            filterButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    const filterType = e.target.dataset.filter;
                    const filterValue = e.target.dataset.value;
                    this.applyFilter(filterType, filterValue);
                });
            });

            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    this.searchActivities(e.target.value);
                });
            }
        });
    }

    loadFilters() {
        fetch('/api/activity-filters')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.updateFilterUI(data.filters);
                }
            })
            .catch(error => {
                console.error('Error loading filters:', error);
            });
    }

    updateFilterUI(filters) {
        const filterContainer = document.querySelector('.filter-container');
        if (filterContainer) {
            filterContainer.innerHTML = filters
                .map(filter => `
                    <div class="filter-group">
                        <h4>${filter.name}</h4>
                        <div class="filter-options">
                            ${filter.options.map(option => `
                                <button class="filter-btn" 
                                        data-filter="${filter.type}" 
                                        data-value="${option.value}">
                                    ${option.label}
                                </button>
                            `).join('')}
                        </div>
                    </div>
                `)
                .join('');
        }
    }

    applyFilter(type, value) {
        const activities = document.querySelectorAll('.activity-item');
        activities.forEach(activity => {
            const activityType = activity.dataset.type;
            const activityValue = activity.dataset.value;

            if (type === 'all' || (activityType === type && activityValue === value)) {
                activity.style.display = 'block';
            } else {
                activity.style.display = 'none';
            }
        });
    }

    searchActivities(query) {
        const activities = document.querySelectorAll('.activity-item');
        const searchTerm = query.toLowerCase();

        activities.forEach(activity => {
            const text = activity.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                activity.style.display = 'block';
            } else {
                activity.style.display = 'none';
            }
        });
    }
}

// Initialize activity filter functionality
const activityFilter = new ActivityFilter(); 