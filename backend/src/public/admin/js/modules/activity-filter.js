// Activity filter module
const ActivityFilter = {
    init() {
        console.log('Activity filter module initialized');
        this.setupEventListeners();
    },

    setupEventListeners() {
        const filterForm = document.getElementById('activity-filter-form');
        if (filterForm) {
            filterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.applyFilters();
            });
        }
    },

    async applyFilters() {
        const formData = new FormData(document.getElementById('activity-filter-form'));
        const filters = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('/api/activities/filter', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(filters)
            });

            const data = await response.json();
            this.updateActivityList(data);
        } catch (error) {
            console.error('Error applying filters:', error);
            NotificationHandler.error('Có lỗi xảy ra khi áp dụng bộ lọc');
        }
    },

    updateActivityList(activities) {
        const container = document.getElementById('activity-list');
        if (!container) return;

        container.innerHTML = activities.map(activity => `
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas ${this.getActivityIcon(activity.type)}"></i>
                </div>
                <div class="activity-content">
                    <h6>${activity.title}</h6>
                    <p>${activity.description}</p>
                    <small>${this.formatTime(activity.timestamp)}</small>
                </div>
            </div>
        `).join('');
    },

    getActivityIcon(type) {
        const icons = {
            'create': 'fa-plus-circle',
            'update': 'fa-edit',
            'delete': 'fa-trash',
            'login': 'fa-sign-in-alt',
            'logout': 'fa-sign-out-alt'
        };
        return icons[type] || 'fa-circle';
    },

    formatTime(timestamp) {
        return new Date(timestamp).toLocaleString('vi-VN', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    ActivityFilter.init();
}); 