class Dashboard {
    constructor() {
        this.charts = {};
        this.widgets = {};
        this.stats = {};
        console.log('Dashboard initialized');
        this.init();
    }

    init() {
        console.log('Initializing dashboard...');
        this.loadCharts();
        this.loadStats();
        this.initializeCharts();
        this.initializeWidgets();
        this.loadDashboardStats();
        this.setupEventListeners();
    }

    async initializeCharts() {
        try {
            console.log('Fetching chart data...');
            const response = await fetch('api/dashboard_charts.php');
            console.log('API Response:', response);
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Network response was not ok');
            }
            
            const data = await response.json();
            console.log('Chart data:', data);
            
            if (!data.success) {
                throw new Error(data.message || 'Failed to load chart data');
            }
            
            this.renderCharts(data.data);
        } catch (error) {
            console.error('Error initializing charts:', error);
            this.showError('Không thể tải dữ liệu biểu đồ: ' + error.message);
        }
    }

    initializeWidgets() {
        // Initialize dashboard widgets
        const widgets = document.querySelectorAll('.dashboard-widget');
        widgets.forEach(widget => {
            const widgetId = widget.id;
            if (widgetId) {
                this.widgets[widgetId] = {
                    element: widget,
                    state: 'initialized'
                };
            }
        });
    }

    renderCharts(data) {
        console.log('Rendering charts with data:', data);
        
        // Performance Chart
        if (data.performance && data.performance.length > 0) {
            const performanceCtx = document.getElementById('performanceChart');
            console.log('Performance chart canvas:', performanceCtx);
            if (performanceCtx) {
                this.renderPerformanceChart(data.performance);
            } else {
                console.error('Performance chart canvas not found');
            }
        }

        // Salary Chart
        if (data.salary && data.salary.length > 0) {
            const salaryCtx = document.getElementById('salaryChart');
            console.log('Salary chart canvas:', salaryCtx);
            if (salaryCtx) {
                this.renderSalaryChart(data.salary);
            } else {
                console.error('Salary chart canvas not found');
            }
        }

        // Leave Chart
        if (data.leaves && data.leaves.length > 0) {
            const leaveCtx = document.getElementById('leaveChart');
            console.log('Leave chart canvas:', leaveCtx);
            if (leaveCtx) {
                this.renderLeaveChart(data.leaves);
            } else {
                console.error('Leave chart canvas not found');
            }
        }

        // Assets Chart
        if (data.assets && data.assets.length > 0) {
            const assetsCtx = document.getElementById('assetsChart');
            console.log('Assets chart canvas:', assetsCtx);
            if (assetsCtx) {
                this.renderAssetsChart(data.assets);
            } else {
                console.error('Assets chart canvas not found');
            }
        }
    }

    renderPerformanceChart(data) {
        const ctx = document.getElementById('performanceChart');
        if (!ctx) return;

        this.charts.performance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(item => `Q${item.quarter}`),
                datasets: [{
                    label: 'Hiệu suất trung bình',
                    data: data.map(item => item.avg_score),
                    backgroundColor: '#2196F3',
                    borderColor: '#1976D2',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: value => value + '%'
                        }
                    }
                }
            }
        });
    }

    renderSalaryChart(data) {
        const ctx = document.getElementById('salaryChart');
        if (!ctx) return;

        this.charts.salary = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => item.month),
                datasets: [{
                    label: 'Tổng chi phí lương',
                    data: data.map(item => parseFloat(item.total_salary)),
                    borderColor: '#FFC107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => value.toLocaleString('vi-VN') + 'đ'
                        }
                    }
                }
            }
        });
    }

    renderLeaveChart(data) {
        const ctx = document.getElementById('leaveChart');
        if (!ctx) return;

        this.charts.leave = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(item => item.leave_type),
                datasets: [{
                    label: 'Số lượng nghỉ phép',
                    data: data.map(item => item.count),
                    backgroundColor: ['#4CAF50', '#2196F3', '#FFC107', '#F44336']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    renderAssetsChart(data) {
        const ctx = document.getElementById('assetsChart');
        if (!ctx) return;

        this.charts.assets = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data.map(item => item.status),
                datasets: [{
                    data: data.map(item => item.count),
                    backgroundColor: ['#4CAF50', '#2196F3', '#FFC107']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    async loadDashboardStats() {
        try {
            const response = await fetch('api/dashboard_stats.php');
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();
            this.updateStats(data.data);
        } catch (error) {
            console.error('Error loading dashboard stats:', error);
            this.showError('Không thể tải thống kê dashboard');
        }
    }

    updateStats(data) {
        const elements = {
            totalEmployees: document.getElementById('totalEmployees'),
            presentToday: document.getElementById('presentToday'),
            absentToday: document.getElementById('absentToday'),
            onTimePercentage: document.getElementById('onTimePercentage')
        };

        if (elements.totalEmployees) elements.totalEmployees.textContent = data.totalEmployees;
        if (elements.presentToday) elements.presentToday.textContent = data.presentToday;
        if (elements.absentToday) elements.absentToday.textContent = data.absentToday;
        if (elements.onTimePercentage) elements.onTimePercentage.textContent = data.onTimePercentage + '%';
    }

    setupEventListeners() {
        // Refresh button
        const refreshBtn = document.getElementById('refreshDashboard');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.refreshDashboard();
            });
        }

        // Auto refresh every 5 minutes
        setInterval(() => {
            this.refreshDashboard();
        }, 300000);
    }

    async refreshDashboard() {
        await this.initializeCharts();
        await this.loadDashboardStats();
    }

    showError(message) {
        const notificationContainer = document.getElementById('notificationContainer');
        if (notificationContainer) {
            const notification = document.createElement('div');
            notification.className = 'alert alert-danger alert-dismissible fade show';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            notificationContainer.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
        console.error(message);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const dashboard = new Dashboard();
    dashboard.init();
}); 