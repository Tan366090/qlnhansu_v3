// Mock data for dashboard
const mockData = {
    // Statistics
    statistics: {
        totalEmployees: 150,
        totalDepartments: 8,
        totalLeaves: 25,
        totalDocuments: 120
    },

    // Performance data
    performance: {
        labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
        data: [75, 82, 78, 85, 88, 90]
    },

    // Department distribution
    departments: {
        labels: ['IT', 'HR', 'Finance', 'Marketing', 'Sales', 'Operations'],
        data: [30, 20, 15, 25, 40, 20]
    },

    // Recent activities
    activities: [
        { id: 1, type: 'login', user: 'Nguyễn Văn A', time: '10 phút trước' },
        { id: 2, type: 'edit', user: 'Trần Thị B', time: '30 phút trước' },
        { id: 3, type: 'view', user: 'Lê Văn C', time: '1 giờ trước' },
        { id: 4, type: 'delete', user: 'Phạm Thị D', time: '2 giờ trước' }
    ],

    // Notifications
    notifications: [
        { id: 1, type: 'info', message: 'Có 5 đơn nghỉ phép mới cần phê duyệt', time: '5 phút trước' },
        { id: 2, type: 'warning', message: '3 hợp đồng sắp hết hạn', time: '15 phút trước' },
        { id: 3, type: 'success', message: 'Đã cập nhật thông tin nhân viên', time: '1 giờ trước' }
    ],

    // HR Trends
    hrTrends: {
        labels: ['Q1', 'Q2', 'Q3', 'Q4'],
        data: [120, 135, 145, 150],
        insights: [
            'Tăng trưởng nhân sự ổn định qua các quý',
            'Tỷ lệ nghỉ việc giảm 15% so với năm trước',
            'Nhu cầu tuyển dụng tăng ở bộ phận IT'
        ]
    },

    // Sentiment Analysis
    sentimentAnalysis: {
        labels: ['Rất tích cực', 'Tích cực', 'Trung lập', 'Tiêu cực', 'Rất tiêu cực'],
        data: [30, 40, 20, 8, 2],
        insights: [
            '85% nhân viên hài lòng với môi trường làm việc',
            'Cần cải thiện phúc lợi cho nhân viên mới',
            'Đề xuất tăng cường hoạt động team building'
        ]
    },

    // Recent employees
    recentEmployees: [
        {
            id: 'NV001',
            name: 'Nguyễn Văn A',
            position: 'Nhân viên IT',
            department: 'IT',
            joinDate: '01/01/2024',
            birthDate: '15/05/1990',
            phone: '0123456789',
            email: 'nva@company.com',
            address: 'Hà Nội',
            status: 'Đang làm việc'
        },
        {
            id: 'NV002',
            name: 'Trần Thị B',
            position: 'Nhân viên HR',
            department: 'HR',
            joinDate: '15/01/2024',
            birthDate: '20/08/1992',
            phone: '0987654321',
            email: 'ttb@company.com',
            address: 'TP.HCM',
            status: 'Đang làm việc'
        }
    ],

    // Mobile app stats
    mobileStats: {
        downloads: 500,
        activeUsers: 350,
        notificationsSent: 1200
    }
};

// Function to populate statistics cards
function populateStatistics() {
    const stats = mockData.statistics;
    document.querySelector('[data-widget="total-employees"] .stat-value').textContent = stats.totalEmployees;
    document.querySelector('[data-widget="total-departments"] .stat-value').textContent = stats.totalDepartments;
    document.querySelector('[data-widget="total-leaves"] .stat-value').textContent = stats.totalLeaves;
    document.querySelector('[data-widget="total-documents"] .stat-value').textContent = stats.totalDocuments;
}

// Function to create performance chart
function createPerformanceChart() {
    const ctx = document.getElementById('performanceChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: mockData.performance.labels,
            datasets: [{
                label: 'Hiệu suất trung bình',
                data: mockData.performance.data,
                borderColor: '#3498db',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

// Function to create department chart
function createDepartmentChart() {
    const ctx = document.getElementById('departmentChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: mockData.departments.labels,
            datasets: [{
                data: mockData.departments.data,
                backgroundColor: [
                    '#3498db',
                    '#2ecc71',
                    '#f1c40f',
                    '#e74c3c',
                    '#9b59b6',
                    '#1abc9c'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

// Function to populate activities
function populateActivities() {
    const activityList = document.getElementById('activityList');
    activityList.innerHTML = mockData.activities.map(activity => `
        <div class="activity-item">
            <i class="fas fa-${getActivityIcon(activity.type)}"></i>
            <div class="activity-info">
                <p>${activity.user}</p>
                <small>${activity.time}</small>
            </div>
        </div>
    `).join('');
}

// Function to populate notifications
function populateNotifications() {
    const notificationList = document.getElementById('notificationList');
    notificationList.innerHTML = mockData.notifications.map(notification => `
        <div class="notification-item ${notification.type}">
            <i class="fas fa-${getNotificationIcon(notification.type)}"></i>
            <div class="notification-info">
                <p>${notification.message}</p>
                <small>${notification.time}</small>
            </div>
        </div>
    `).join('');
}

// Function to populate HR trends
function populateHRTrends() {
    const hrTrends = document.getElementById('hrTrends');
    const ctx = document.createElement('canvas');
    hrTrends.appendChild(ctx);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: mockData.hrTrends.labels,
            datasets: [{
                label: 'Số lượng nhân viên',
                data: mockData.hrTrends.data,
                borderColor: '#3498db',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    const insightsList = document.createElement('ul');
    insightsList.className = 'insights-list';
    mockData.hrTrends.insights.forEach(insight => {
        const li = document.createElement('li');
        li.textContent = insight;
        insightsList.appendChild(li);
    });
    hrTrends.appendChild(insightsList);
}

// Function to populate sentiment analysis
function populateSentimentAnalysis() {
    const sentimentAnalysis = document.getElementById('sentimentAnalysis');
    const ctx = document.createElement('canvas');
    sentimentAnalysis.appendChild(ctx);
    
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: mockData.sentimentAnalysis.labels,
            datasets: [{
                data: mockData.sentimentAnalysis.data,
                backgroundColor: [
                    '#2ecc71',
                    '#27ae60',
                    '#f1c40f',
                    '#e74c3c',
                    '#c0392b'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    const insightsList = document.createElement('ul');
    insightsList.className = 'insights-list';
    mockData.sentimentAnalysis.insights.forEach(insight => {
        const li = document.createElement('li');
        li.textContent = insight;
        insightsList.appendChild(li);
    });
    sentimentAnalysis.appendChild(insightsList);
}

// Helper functions
function getActivityIcon(type) {
    const icons = {
        login: 'sign-in-alt',
        edit: 'edit',
        view: 'eye',
        delete: 'trash'
    };
    return icons[type] || 'circle';
}

function getNotificationIcon(type) {
    const icons = {
        info: 'info-circle',
        warning: 'exclamation-triangle',
        success: 'check-circle'
    };
    return icons[type] || 'bell';
}

// Initialize all data
function initializeMockData() {
    populateStatistics();
    createPerformanceChart();
    createDepartmentChart();
    populateActivities();
    populateNotifications();
    populateHRTrends();
    populateSentimentAnalysis();
}

// Export the initialization function
export { initializeMockData }; 