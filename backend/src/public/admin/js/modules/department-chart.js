class DepartmentChart {
    constructor() {
        this.chart = null;
        this.initialize();
    }

    async initialize() {
        try {
            const response = await fetch('/qlnhansu_V2/backend/src/public/admin/api/departments.php');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            
            if (data.success) {
                this.renderChart(data.data);
            } else {
                console.error('Failed to load department data:', data.message);
            }
        } catch (error) {
            console.error('Error loading department data:', error);
            // Hiển thị thông báo lỗi cho người dùng
            const chartContainer = document.getElementById('departmentChart');
            if (chartContainer) {
                chartContainer.innerHTML = '<div class="alert alert-danger">Không thể tải dữ liệu phòng ban. Vui lòng thử lại sau.</div>';
            }
        }
    }

    renderChart(departments) {
        const ctx = document.getElementById('departmentChart');
        if (!ctx) {
            console.error('Chart canvas not found');
            return;
        }

        const context = ctx.getContext('2d');
        
        // Prepare data for chart
        const labels = departments.map(dept => dept.name);
        const employeeCounts = departments.map(dept => dept.employee_count);
        const backgroundColors = this.generateColors(departments.length);

        if (this.chart) {
            this.chart.destroy();
        }

        this.chart = new Chart(context, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: employeeCounts,
                    backgroundColor: backgroundColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Phân bố nhân viên theo phòng ban',
                        font: {
                            size: 16
                        }
                    }
                }
            }
        });
    }

    generateColors(count) {
        const colors = [];
        const hueStep = 360 / count;
        
        for (let i = 0; i < count; i++) {
            const hue = i * hueStep;
            colors.push(`hsl(${hue}, 70%, 50%)`);
        }
        
        return colors;
    }

    refresh() {
        this.initialize();
    }
}

// Export the class
window.DepartmentChart = DepartmentChart; 