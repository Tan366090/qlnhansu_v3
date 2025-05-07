class AttendanceTrend {
    constructor() {
        this.period = 'month';
        this.chart = null;
        this.initialize();
    }

    initialize() {
        // Setup event listeners
        const periodSelect = document.getElementById('attendancePeriod');
        if (periodSelect) {
            periodSelect.addEventListener('change', (e) => {
                this.period = e.target.value;
                this.loadData();
            });
        }

        // Initial data load
        this.loadData();

        // Auto refresh every 5 minutes
        setInterval(() => {
            this.loadData();
        }, 5 * 60 * 1000);
    }

    // Thêm hàm refreshData để cập nhật dữ liệu ngay lập tức
    refreshData() {
        this.loadData();
    }

    async loadData() {
        try {
            const response = await fetch(`/qlnhansu_V2/backend/src/public/api/dashboard_api.php?endpoint=attendance&period=${this.period}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            
            // Kiểm tra và chuyển đổi dữ liệu thành mảng nếu cần
            const attendanceData = Array.isArray(data) ? data : [data];
            
            this.updateChart(attendanceData);
        } catch (error) {
            console.error('Error loading attendance data:', error);
            // Hiển thị biểu đồ trống nếu có lỗi
            this.updateChart([]);
        }
    }

    showNoData() {
        const ctx = document.getElementById('attendanceTrendChart');
        if (!ctx) return;

        if (this.chart) {
            this.chart.destroy();
        }

        this.chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Không có dữ liệu'],
                datasets: [{
                    label: 'Chưa có dữ liệu chấm công',
                    data: [0],
                    backgroundColor: 'rgba(200, 200, 200, 0.5)',
                    borderColor: 'rgb(200, 200, 200)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Ngày'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Số nhân viên'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 20
                        }
                    },
                    tooltip: {
                        enabled: false
                    }
                }
            }
        });
    }

    updateChart(data) {
        const ctx = document.getElementById('attendanceTrendChart').getContext('2d');
        
        if (this.chart) {
            this.chart.destroy();
        }

        this.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => item.date),
                datasets: [
                    {
                        label: 'Có mặt',
                        data: data.map(item => item.present || 0),
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.1
                    },
                    {
                        label: 'Vắng mặt',
                        data: data.map(item => item.absent || 0),
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.1
                    },
                    {
                        label: 'Đi muộn',
                        data: data.map(item => item.late || 0),
                        borderColor: 'rgb(255, 205, 86)',
                        backgroundColor: 'rgba(255, 205, 86, 0.1)',
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Xu hướng chấm công'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    setPeriod(period) {
        this.period = period;
        this.loadData();
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.attendanceTrend = new AttendanceTrend();
}); 