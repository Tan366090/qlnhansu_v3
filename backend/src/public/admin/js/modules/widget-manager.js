class WidgetManager {
    constructor() {
        this.widgets = new Map();
        this.initialized = false;
    }

    async initialize() {
        if (this.initialized) return;
        
        try {
            // Khởi tạo các widget
            await this.initializeWidgets();
            this.initialized = true;
        } catch (error) {
            console.error('Lỗi khởi tạo WidgetManager:', error);
            throw error;
        }
    }

    async initializeWidgets() {
        // Khởi tạo các widget chính
        await this.initializeStatisticsWidgets();
        await this.initializeChartWidgets();
        await this.initializeActivityWidgets();
        await this.initializeNotificationWidgets();
    }

    async initializeStatisticsWidgets() {
        try {
            const response = await fetch('/api/dashboard/statistics', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            });

            if (!response.ok) throw new Error('Lỗi tải thống kê');

            const data = await response.json();
            
            // Cập nhật các widget thống kê
            this.updateStatCard('total-employees', data.totalEmployees);
            this.updateStatCard('total-departments', data.totalDepartments);
            this.updateStatCard('total-leaves', data.totalLeaves);
            this.updateStatCard('total-documents', data.totalDocuments);

        } catch (error) {
            console.error('Lỗi khởi tạo widget thống kê:', error);
        }
    }

    async initializeChartWidgets() {
        try {
            // Khởi tạo biểu đồ hiệu suất
            const performanceResponse = await fetch('/api/dashboard/performance', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            });

            if (!performanceResponse.ok) throw new Error('Lỗi tải dữ liệu hiệu suất');

            const performanceData = await performanceResponse.json();
            this.initializePerformanceChart(performanceData);

            // Khởi tạo biểu đồ phòng ban
            const departmentResponse = await fetch('/api/dashboard/departments', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            });

            if (!departmentResponse.ok) throw new Error('Lỗi tải dữ liệu phòng ban');

            const departmentData = await departmentResponse.json();
            this.initializeDepartmentChart(departmentData);

        } catch (error) {
            console.error('Lỗi khởi tạo widget biểu đồ:', error);
        }
    }

    async initializeActivityWidgets() {
        try {
            const response = await fetch('/api/dashboard/activities', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            });

            if (!response.ok) throw new Error('Lỗi tải hoạt động');

            const data = await response.json();
            this.updateActivityList(data.activities);

        } catch (error) {
            console.error('Lỗi khởi tạo widget hoạt động:', error);
        }
    }

    async initializeNotificationWidgets() {
        try {
            const response = await fetch('/api/dashboard/notifications', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            });

            if (!response.ok) throw new Error('Lỗi tải thông báo');

            const data = await response.json();
            this.updateNotificationList(data.notifications);

        } catch (error) {
            console.error('Lỗi khởi tạo widget thông báo:', error);
        }
    }

    updateStatCard(widgetId, value) {
        const widget = document.querySelector(`[data-widget="${widgetId}"]`);
        if (widget) {
            const valueElement = widget.querySelector('.stat-value');
            if (valueElement) {
                valueElement.textContent = value;
            }
        }
    }

    initializePerformanceChart(data) {
        const ctx = document.getElementById('performanceChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Hiệu suất',
                    data: data.values,
                    borderColor: '#3498db',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    }

    initializeDepartmentChart(data) {
        const ctx = document.getElementById('departmentChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.values,
                    backgroundColor: [
                        '#3498db',
                        '#2ecc71',
                        '#e74c3c',
                        '#f1c40f',
                        '#9b59b6'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });
    }

    updateActivityList(activities) {
        const activityList = document.getElementById('activityList');
        if (!activityList) return;

        activityList.innerHTML = activities.map(activity => `
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas ${this.getActivityIcon(activity.type)}"></i>
                </div>
                <div class="activity-content">
                    <p class="activity-text">${activity.description}</p>
                    <span class="activity-time">${activity.time}</span>
                </div>
            </div>
        `).join('');
    }

    updateNotificationList(notifications) {
        const notificationList = document.getElementById('notificationList');
        if (!notificationList) return;

        notificationList.innerHTML = notifications.map(notification => `
            <div class="notification-item ${notification.unread ? 'unread' : ''}">
                <div class="notification-icon">
                    <i class="fas ${this.getNotificationIcon(notification.type)}"></i>
                </div>
                <div class="notification-content">
                    <p class="notification-text">${notification.message}</p>
                    <span class="notification-time">${notification.time}</span>
                </div>
            </div>
        `).join('');
    }

    getActivityIcon(type) {
        const icons = {
            'login': 'fa-sign-in-alt',
            'edit': 'fa-edit',
            'view': 'fa-eye',
            'delete': 'fa-trash',
            'create': 'fa-plus',
            'update': 'fa-sync'
        };
        return icons[type] || 'fa-info-circle';
    }

    getNotificationIcon(type) {
        const icons = {
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-circle',
            'warning': 'fa-exclamation-triangle',
            'info': 'fa-info-circle'
        };
        return icons[type] || 'fa-bell';
    }

    async refreshWidget(widgetId) {
        switch (widgetId) {
            case 'statistics':
                await this.initializeStatisticsWidgets();
                break;
            case 'performance':
                await this.initializeChartWidgets();
                break;
            case 'activities':
                await this.initializeActivityWidgets();
                break;
            case 'notifications':
                await this.initializeNotificationWidgets();
                break;
        }
    }

    cleanup() {
        this.widgets.clear();
        this.initialized = false;
    }
}

// Export class
export { WidgetManager }; 