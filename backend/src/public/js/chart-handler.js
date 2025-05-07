// Chart Handler with Error Handling
class ChartHandler {
    constructor() {
        this.charts = new Map();
        this.defaultOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            }
        };
    }

    // Initialize a chart
    initChart(chartId, type, data, options = {}) {
        const canvas = document.getElementById(chartId);
        if (!canvas) {
            console.error(`Canvas element with id ${chartId} not found`);
            return null;
        }

        try {
            // Destroy existing chart if it exists
            if (this.charts.has(chartId)) {
                this.charts.get(chartId).destroy();
            }

            // Create new chart
            const chart = new Chart(canvas, {
                type: type,
                data: data,
                options: {
                    ...this.defaultOptions,
                    ...options
                }
            });

            this.charts.set(chartId, chart);
            return chart;

        } catch (error) {
            console.error(`Error initializing chart ${chartId}:`, error);
            this.handleChartError(chartId, error);
            return null;
        }
    }

    // Update chart data
    updateChart(chartId, data) {
        const chart = this.charts.get(chartId);
        if (!chart) {
            console.error(`Chart with id ${chartId} not found`);
            return false;
        }

        try {
            chart.data = data;
            chart.update();
            return true;
        } catch (error) {
            console.error(`Error updating chart ${chartId}:`, error);
            this.handleChartError(chartId, error);
            return false;
        }
    }

    // Handle chart errors
    handleChartError(chartId, error) {
        const canvas = document.getElementById(chartId);
        if (!canvas) return;

        const container = canvas.parentElement;
        container.innerHTML = `
            <div class="chart-error">
                <i class="fas fa-exclamation-circle"></i>
                <p>Không thể tải dữ liệu biểu đồ</p>
                <small>${error.message}</small>
            </div>
        `;
    }

    // Process data for line chart
    processLineData(labels, datasets) {
        return {
            labels: labels,
            datasets: datasets.map(dataset => ({
                label: dataset.label,
                data: dataset.data,
                borderColor: dataset.color || this.getRandomColor(),
                backgroundColor: dataset.backgroundColor || 'rgba(0, 0, 0, 0)',
                borderWidth: 2,
                tension: 0.1,
                fill: dataset.fill || false
            }))
        };
    }

    // Process data for bar chart
    processBarData(labels, datasets) {
        return {
            labels: labels,
            datasets: datasets.map(dataset => ({
                label: dataset.label,
                data: dataset.data,
                backgroundColor: dataset.color || this.getRandomColor(),
                borderColor: dataset.borderColor || 'rgba(0, 0, 0, 0)',
                borderWidth: 1
            }))
        };
    }

    // Process data for pie chart
    processPieData(labels, data) {
        return {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: labels.map(() => this.getRandomColor()),
                borderWidth: 1
            }]
        };
    }

    // Get random color
    getRandomColor() {
        const letters = '0123456789ABCDEF';
        let color = '#';
        for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    // Destroy a chart
    destroyChart(chartId) {
        const chart = this.charts.get(chartId);
        if (chart) {
            chart.destroy();
            this.charts.delete(chartId);
        }
    }

    // Destroy all charts
    destroyAllCharts() {
        for (const [chartId] of this.charts) {
            this.destroyChart(chartId);
        }
    }

    // Export chart as image
    exportChart(chartId, fileName = 'chart') {
        const chart = this.charts.get(chartId);
        if (!chart) return;

        const link = document.createElement('a');
        link.download = `${fileName}.png`;
        link.href = chart.toBase64Image();
        link.click();
    }
}

// Example usage:
/*
const chartHandler = new ChartHandler();

// Line chart
const lineData = chartHandler.processLineData(
    ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
    [
        {
            label: 'Dataset 1',
            data: [12, 19, 3, 5, 2],
            color: '#FF6384'
        },
        {
            label: 'Dataset 2',
            data: [7, 11, 5, 8, 3],
            color: '#36A2EB'
        }
    ]
);

chartHandler.initChart('lineChart', 'line', lineData);

// Bar chart
const barData = chartHandler.processBarData(
    ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
    [
        {
            label: 'Dataset 1',
            data: [12, 19, 3, 5, 2]
        }
    ]
);

chartHandler.initChart('barChart', 'bar', barData);

// Pie chart
const pieData = chartHandler.processPieData(
    ['Red', 'Blue', 'Yellow'],
    [12, 19, 3]
);

chartHandler.initChart('pieChart', 'pie', pieData);
*/ 