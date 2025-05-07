// Mobile stats module
class MobileStats {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadMobileStats();
    }

    setupEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            const refreshButton = document.querySelector('.refresh-stats');
            if (refreshButton) {
                refreshButton.addEventListener('click', () => {
                    this.loadMobileStats();
                });
            }
        });
    }

    loadMobileStats() {
        fetch('/api/mobile-stats')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.updateStatsUI(data.data);
                }
            })
            .catch(error => {
                console.error('Error loading mobile stats:', error);
            });
    }

    updateStatsUI(stats) {
        // Update total users
        const totalUsers = document.querySelector('.total-users');
        if (totalUsers) {
            totalUsers.textContent = stats.total_users;
        }

        // Update active users
        const activeUsers = document.querySelector('.active-users');
        if (activeUsers) {
            activeUsers.textContent = stats.active_users;
        }

        // Update app usage chart
        this.updateUsageChart(stats.usage_data);

        // Update device distribution
        this.updateDeviceDistribution(stats.device_distribution);

        // Update recent activities
        this.updateRecentActivities(stats.recent_activities);
    }

    updateUsageChart(usageData) {
        const ctx = document.querySelector('.usage-chart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: usageData.labels,
                    datasets: [{
                        label: 'App Usage',
                        data: usageData.values,
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    }

    updateDeviceDistribution(distribution) {
        const ctx = document.querySelector('.device-distribution-chart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: distribution.labels,
                    datasets: [{
                        data: distribution.values,
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)'
                        ]
                    }]
                },
                options: {
                    responsive: true
                }
            });
        }
    }

    updateRecentActivities(activities) {
        const container = document.querySelector('.recent-activities');
        if (container) {
            container.innerHTML = activities
                .map(activity => `
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas ${this.getActivityIcon(activity.type)}"></i>
                        </div>
                        <div class="activity-details">
                            <div class="activity-title">${activity.title}</div>
                            <div class="activity-time">${activity.time}</div>
                        </div>
                    </div>
                `)
                .join('');
        }
    }

    getActivityIcon(type) {
        const icons = {
            'login': 'fa-sign-in-alt',
            'logout': 'fa-sign-out-alt',
            'update': 'fa-sync',
            'error': 'fa-exclamation-circle'
        };
        return icons[type] || 'fa-circle';
    }
}

// Initialize mobile stats functionality
const mobileStats = new MobileStats(); 