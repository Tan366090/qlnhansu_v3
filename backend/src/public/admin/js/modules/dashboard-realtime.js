class DashboardRealtime {
    constructor() {
        this.statsContainer = document.querySelector('.realtime-stats');
        this.chartContainer = document.querySelector('.realtime-chart');
        this.updatesContainer = document.querySelector('.realtime-updates');
        this.initialize();
    }

    async initialize() {
        await this.loadStats();
        await this.loadChart();
        await this.loadUpdates();
        this.startAutoRefresh();
    }

    async loadStats() {
        try {
            const response = await fetch('/api/v1/dashboard/stats');
            const data = await response.json();
            this.renderStats(data);
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }

    async loadChart() {
        try {
            const response = await fetch('/api/v1/dashboard/chart');
            const data = await response.json();
            this.renderChart(data);
        } catch (error) {
            console.error('Error loading chart:', error);
        }
    }

    async loadUpdates() {
        try {
            const response = await fetch('/api/v1/dashboard/updates');
            const data = await response.json();
            this.renderUpdates(data);
        } catch (error) {
            console.error('Error loading updates:', error);
        }
    }

    renderStats(data) {
        if (!this.statsContainer) return;

        this.statsContainer.innerHTML = data.map(stat => `
            <div class="stat-card">
                <h3>${stat.title}</h3>
                <div class="value">${stat.value}</div>
                <div class="trend ${stat.trend > 0 ? 'up' : 'down'}">
                    ${stat.trend > 0 ? '↑' : '↓'} ${Math.abs(stat.trend)}%
                </div>
            </div>
        `).join('');
    }

    renderChart(data) {
        if (!this.chartContainer) return;

        const ctx = document.createElement('canvas');
        this.chartContainer.innerHTML = '';
        this.chartContainer.appendChild(ctx);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: data.title,
                    data: data.values,
                    borderColor: '#4ca1af',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    renderUpdates(data) {
        if (!this.updatesContainer) return;

        this.updatesContainer.innerHTML = data.map(update => `
            <div class="update-item">
                <div class="update-content">${update.content}</div>
                <div class="update-time">${new Date(update.timestamp).toLocaleString()}</div>
            </div>
        `).join('');
    }

    startAutoRefresh() {
        setInterval(() => {
            this.loadStats();
            this.loadChart();
            this.loadUpdates();
        }, 30000); // Refresh every 30 seconds
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new DashboardRealtime();
}); 