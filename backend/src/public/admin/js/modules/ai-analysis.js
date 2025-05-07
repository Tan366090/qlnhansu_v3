/**
 * @module ai-analysis
 * @description Handles AI analysis functionality
 */

export const AIAnalysis = {
    init() {
        this.setupEventListeners();
        this.loadAnalysis(); // Tự động tải dữ liệu khi khởi tạo
    },

    setupEventListeners() {
        const updateBtn = document.getElementById('updateAnalysis');
        if (updateBtn) {
            updateBtn.addEventListener('click', () => this.loadAnalysis());
        }
    },

    async loadAnalysis() {
        try {
            console.log('Loading analysis data...');
            
            // Mock data for HR Trends - Dựa trên cấu trúc bảng employees và departments
            const hrTrendsData = {
                labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 
                         'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
                values: [3, 5, 5, 5, 5, 5, 5, 5, 10, 8, 20, 120],
                insights: [
                    "Tổng số nhân viên tăng từ 3 lên 120 trong 12 tháng",
                    "Tháng 11 và tháng 12 có tốc độ tăng trưởng cao nhất",
                    "Phòng ban IT có 25 nhân viên, chiếm 20.8% tổng số nhân viên",
                    "Phòng ban Kinh doanh có 35 nhân viên, chiếm 29.2% tổng số nhân viên",
                    "Phòng ban Hành chính có 20 nhân viên, chiếm 16.7% tổng số nhân viên",
                    "Phòng ban Kế toán có 15 nhân viên, chiếm 12.5% tổng số nhân viên",
                    "Phòng ban Kỹ thuật có 25 nhân viên, chiếm 20.8% tổng số nhân viên",
                    "Dự đoán tăng trưởng năm sau: 15-20 nhân viên mới",
                    "Tỷ lệ nghỉ việc thấp: 2% trong 6 tháng gần nhất",
                    "Tỷ lệ nhân viên mới hoàn thành thử việc: 95%",
                    "Chi phí tuyển dụng trung bình: 15 triệu VNĐ/người",
                    "Thời gian tuyển dụng trung bình: 30 ngày",
                    "Tỷ lệ nhân viên nữ: 45%",
                    "Tỷ lệ nhân viên có bằng đại học: 80%"
                ]
            };

            // Mock data for Sentiment Analysis - Dựa trên cấu trúc bảng evaluations và feedback
            const sentimentData = {
                positive: 70,  // 70% tích cực
                neutral: 27,   // 20% trung lập
                negative: 3,  // 10% tiêu cực
                insights: [
                    "70% nhân viên đánh giá tích cực về môi trường làm việc",
                    "27% nhân viên có đánh giá trung lập về các chính sách",
                    "3% nhân viên có ý kiến tiêu cực, chủ yếu về lương thưởng",
                    "Phòng ban IT có tỷ lệ hài lòng cao nhất: 85%",
                    "Phòng ban Kinh doanh có tỷ lệ hài lòng: 75%",
                    "Phòng ban Hành chính có tỷ lệ hài lòng: 65%",
                    "Cần cải thiện chính sách lương thưởng để tăng tỷ lệ hài lòng",
                    "Nên tăng cường giao tiếp giữa quản lý và nhân viên",
                    "Chương trình đào tạo được đánh giá cao: 85% hài lòng",
                    "Cơ hội thăng tiến cần được cải thiện: 60% hài lòng",
                    "Môi trường làm việc được đánh giá tốt: 80% hài lòng",
                    "Công cụ và thiết bị làm việc đầy đủ: 90% hài lòng",
                    "Chế độ phúc lợi được đánh giá tốt: 75% hài lòng",
                    "Văn hóa công ty được đánh giá tích cực: 70% hài lòng",
                    "Cần cải thiện công tác đánh giá hiệu suất: 55% hài lòng"
                ]
            };

            // Combine data
            const combinedData = {
                hrTrends: hrTrendsData,
                sentiment: sentimentData,
                insights: {
                    hrTrends: hrTrendsData.insights,
                    sentiment: sentimentData.insights
                }
            };

            this.updateUI(combinedData);
        } catch (error) {
            console.error('Error loading analysis:', error);
            this.showError('Không thể tải dữ liệu phân tích. Vui lòng thử lại sau.');
        }
    },

    updateUI(data) {
        console.log('Updating UI with data:', data);
        
        // Cập nhật biểu đồ xu hướng nhân sự
        const hrTrendsChart = document.getElementById('hrTrendsChart');
        if (hrTrendsChart) {
            this.updateHRTrendsChart(hrTrendsChart, data.hrTrends);
        } else {
            console.error('HR Trends chart element not found');
        }

        // Cập nhật biểu đồ phân tích tâm lý
        const sentimentChart = document.getElementById('sentimentChart');
        if (sentimentChart) {
            this.updateSentimentChart(sentimentChart, data.sentiment);
        } else {
            console.error('Sentiment chart element not found');
        }

        // Cập nhật insights
        this.updateInsights(data.insights);
    },

    updateHRTrendsChart(canvas, data) {
        console.log('Updating HR Trends chart with data:', data);
        if (!data || !data.labels || !data.values) {
            console.error('Invalid HR Trends data:', data);
            return;
        }

        // Xóa biểu đồ cũ nếu tồn tại
        if (canvas.chart) {
            canvas.chart.destroy();
        }

        canvas.chart = new Chart(canvas, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Tổng số nhân viên',
                    data: data.values,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: 'Xu hướng tăng trưởng nhân sự'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Số lượng nhân viên'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Thời gian'
                        }
                    }
                }
            }
        });
    },

    updateSentimentChart(canvas, data) {
        console.log('Updating Sentiment chart with data:', data);
        if (!data) {
            console.error('Invalid Sentiment data:', data);
            return;
        }

        // Xóa biểu đồ cũ nếu tồn tại
        if (canvas.chart) {
            canvas.chart.destroy();
        }

        canvas.chart = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: ['Tích cực', 'Trung lập', 'Tiêu cực'],
                datasets: [{
                    data: [data.positive, data.neutral, data.negative],
                    backgroundColor: [
                        'rgb(75, 192, 192)',
                        'rgb(255, 205, 86)',
                        'rgb(255, 99, 132)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'right'
                    },
                    title: {
                        display: true,
                        text: 'Phân tích tâm lý nhân viên'
                    }
                }
            }
        });
    },

    updateInsights(insights) {
        console.log('Updating insights with data:', insights);
        
        const hrTrendsInsights = document.getElementById('hrTrendsInsights');
        if (hrTrendsInsights) {
            // Chọn 3 dòng quan trọng nhất để hiển thị mặc định
            const importantInsights = insights.hrTrends.slice(0, 3);
            const additionalInsights = insights.hrTrends.slice(3);
            
            // Tạo nội dung insights
            const defaultHTML = importantInsights.map(insight => 
                `<li class="important">${insight}</li>`
            ).join('');
            
            const additionalHTML = additionalInsights.map(insight => 
                `<li class="additional">${insight}</li>`
            ).join('');
            
            // Thêm nút toggle nếu có thêm insights
            let toggleButton = null;
            if (additionalInsights.length > 0) {
                toggleButton = document.createElement('button');
                toggleButton.className = 'toggle-button';
                toggleButton.textContent = 'Xem thêm';
                
                // Thêm sự kiện click cho nút toggle
                toggleButton.addEventListener('click', () => {
                    hrTrendsInsights.classList.toggle('expanded');
                    toggleButton.textContent = hrTrendsInsights.classList.contains('expanded') ? 'Thu gọn' : 'Xem thêm';
                });
                
                // Thêm sự kiện hover
                hrTrendsInsights.addEventListener('mouseleave', () => {
                    if (hrTrendsInsights.classList.contains('expanded')) {
                        setTimeout(() => {
                            hrTrendsInsights.classList.remove('expanded');
                            toggleButton.textContent = 'Xem thêm';
                        }, 1000);
                    }
                });
            }
            
            // Cập nhật nội dung
            hrTrendsInsights.innerHTML = defaultHTML + additionalHTML;
            if (toggleButton) {
                hrTrendsInsights.parentNode.appendChild(toggleButton);
            }
        } else {
            console.error('HR Trends insights element not found');
        }

        const sentimentInsights = document.getElementById('sentimentInsights');
        if (sentimentInsights) {
            // Chọn 3 dòng quan trọng nhất để hiển thị mặc định
            const importantInsights = insights.sentiment.slice(0, 3);
            const additionalInsights = insights.sentiment.slice(3);
            
            // Tạo nội dung insights
            const defaultHTML = importantInsights.map(insight => 
                `<li class="important">${insight}</li>`
            ).join('');
            
            const additionalHTML = additionalInsights.map(insight => 
                `<li class="additional">${insight}</li>`
            ).join('');
            
            // Thêm nút toggle nếu có thêm insights
            let toggleButton = null;
            if (additionalInsights.length > 0) {
                toggleButton = document.createElement('button');
                toggleButton.className = 'toggle-button';
                toggleButton.textContent = 'Xem thêm';
                
                // Thêm sự kiện click cho nút toggle
                toggleButton.addEventListener('click', () => {
                    sentimentInsights.classList.toggle('expanded');
                    toggleButton.textContent = sentimentInsights.classList.contains('expanded') ? 'Thu gọn' : 'Xem thêm';
                });
                
                // Thêm sự kiện hover
                sentimentInsights.addEventListener('mouseleave', () => {
                    if (sentimentInsights.classList.contains('expanded')) {
                        setTimeout(() => {
                            sentimentInsights.classList.remove('expanded');
                            toggleButton.textContent = 'Xem thêm';
                        }, 1000);
                    }
                });
            }
            
            // Cập nhật nội dung
            sentimentInsights.innerHTML = defaultHTML + additionalHTML;
            if (toggleButton) {
                sentimentInsights.parentNode.appendChild(toggleButton);
            }
        } else {
            console.error('Sentiment insights element not found');
        }
    },

    showSuccess(message) {
        const notificationContainer = document.getElementById('notificationContainer');
        if (notificationContainer) {
            const notification = document.createElement('div');
            notification.className = 'notification success';
            notification.textContent = message;
            notificationContainer.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
    },

    showError(message) {
        const notificationContainer = document.getElementById('notificationContainer');
        if (notificationContainer) {
            const notification = document.createElement('div');
            notification.className = 'notification error';
            notification.textContent = message;
            notificationContainer.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
    }
}; 