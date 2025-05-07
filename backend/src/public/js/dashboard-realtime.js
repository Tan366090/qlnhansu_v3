class DashboardRealtime {
    constructor() {
        this.charts = new Map();
        this.reports = new Map();
        this.customWidgets = new Map();
        this.initializeWebSocket();
        this.initializeCharts();
        this.initializeReports();
        this.initializeCustomDashboard();
    }

    initializeWebSocket() {
        // Lắng nghe các sự kiện từ WebSocket
        wsManager.on('dashboard_update', (data) => {
            this.updateDashboard(data);
        });

        wsManager.on('notification', (data) => {
            this.showNotification(data.message, data.type);
        });

        wsManager.on('chat', (data) => {
            this.handleChatMessage(data);
        });
    }

    initializeCharts() {
        // Khởi tạo các biểu đồ
        this.charts.set('employeeStats', new Chart(document.getElementById('employeeStats'), {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Nhân viên',
                    data: [],
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        }));

        this.charts.set('departmentStats', new Chart(document.getElementById('departmentStats'), {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Phòng ban',
                    data: [],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)'
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
        }));

        this.charts.set('performanceStats', new Chart(document.getElementById('performanceStats'), {
            type: 'radar',
            data: {
                labels: ['KPI', 'Chất lượng', 'Hiệu suất', 'Teamwork'],
                datasets: [{
                    label: 'Hiệu suất trung bình',
                    data: [0, 0, 0, 0],
                    fill: true,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgb(255, 99, 132)',
                    pointBackgroundColor: 'rgb(255, 99, 132)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgb(255, 99, 132)'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 10
                    }
                }
            }
        }));
    }

    initializeReports() {
        // Khởi tạo các báo cáo động
        this.reports.set('attendanceReport', {
            element: document.getElementById('attendanceReport'),
            template: `
                <div class="report-card">
                    <h3>Báo cáo chấm công</h3>
                    <div class="report-content">
                        <p>Tổng số nhân viên: <span id="totalEmployees">0</span></p>
                        <p>Điểm danh hôm nay: <span id="todayAttendance">0</span></p>
                        <p>Vắng mặt: <span id="absentToday">0</span></p>
                    </div>
                </div>
            `
        });

        this.reports.set('performanceReport', {
            element: document.getElementById('performanceReport'),
            template: `
                <div class="report-card">
                    <h3>Báo cáo hiệu suất</h3>
                    <div class="report-content">
                        <p>Nhân viên xuất sắc: <span id="topPerformers">0</span></p>
                        <p>Cần cải thiện: <span id="needImprovement">0</span></p>
                        <p>Đánh giá trung bình: <span id="avgRating">0</span></p>
                    </div>
                </div>
            `
        });
    }

    initializeCustomDashboard() {
        // Khởi tạo dashboard tùy chỉnh
        this.customWidgets.set('quickActions', {
            element: document.getElementById('quickActions'),
            template: `
                <div class="widget-card">
                    <h3>Thao tác nhanh</h3>
                    <div class="widget-content">
                        <button onclick="addEmployee()">Thêm nhân viên</button>
                        <button onclick="scheduleMeeting()">Lên lịch họp</button>
                        <button onclick="sendAnnouncement()">Gửi thông báo</button>
                    </div>
                </div>
            `
        });

        this.customWidgets.set('recentActivity', {
            element: document.getElementById('recentActivity'),
            template: `
                <div class="widget-card">
                    <h3>Hoạt động gần đây</h3>
                    <div class="widget-content" id="activityList">
                        <!-- Activity items will be added here -->
                    </div>
                </div>
            `
        });
    }

    updateDashboard(data) {
        // Cập nhật dữ liệu cho các biểu đồ
        if (data.employeeStats) {
            this.updateChart('employeeStats', data.employeeStats);
        }
        if (data.departmentStats) {
            this.updateChart('departmentStats', data.departmentStats);
        }
        if (data.performanceStats) {
            this.updateChart('performanceStats', data.performanceStats);
        }

        // Cập nhật các báo cáo
        if (data.attendanceReport) {
            this.updateReport('attendanceReport', data.attendanceReport);
        }
        if (data.performanceReport) {
            this.updateReport('performanceReport', data.performanceReport);
        }

        // Cập nhật các widget tùy chỉnh
        if (data.recentActivity) {
            this.updateWidget('recentActivity', data.recentActivity);
        }
    }

    updateChart(chartId, data) {
        const chart = this.charts.get(chartId);
        if (chart) {
            chart.data.labels = data.labels;
            chart.data.datasets[0].data = data.values;
            chart.update();
        }
    }

    updateReport(reportId, data) {
        const report = this.reports.get(reportId);
        if (report) {
            Object.keys(data).forEach(key => {
                const element = report.element.querySelector(`#${key}`);
                if (element) {
                    element.textContent = data[key];
                }
            });
        }
    }

    updateWidget(widgetId, data) {
        const widget = this.customWidgets.get(widgetId);
        if (widget) {
            const activityList = widget.element.querySelector('#activityList');
            if (activityList) {
                activityList.innerHTML = data.map(activity => `
                    <div class="activity-item">
                        <span class="activity-time">${activity.time}</span>
                        <span class="activity-content">${activity.content}</span>
                    </div>
                `).join('');
            }
        }
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    handleChatMessage(data) {
        const chatContainer = document.getElementById('chatContainer');
        if (chatContainer) {
            const messageElement = document.createElement('div');
            messageElement.className = 'chat-message';
            messageElement.innerHTML = `
                <span class="sender">${data.sender}:</span>
                <span class="message">${data.message}</span>
            `;
            chatContainer.appendChild(messageElement);
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    }

    // Phương thức để thêm widget tùy chỉnh
    addCustomWidget(widgetId, template) {
        this.customWidgets.set(widgetId, {
            element: document.getElementById(widgetId),
            template: template
        });
    }

    // Phương thức để xóa widget tùy chỉnh
    removeCustomWidget(widgetId) {
        this.customWidgets.delete(widgetId);
    }

    // Phương thức để cập nhật layout dashboard
    updateLayout(layout) {
        const dashboard = document.getElementById('dashboard');
        if (dashboard) {
            dashboard.style.gridTemplateColumns = layout.columns;
            dashboard.style.gridTemplateRows = layout.rows;
        }
    }
}

// Khởi tạo instance
const dashboardRealtime = new DashboardRealtime();

// Export instance
window.dashboardRealtime = dashboardRealtime; 