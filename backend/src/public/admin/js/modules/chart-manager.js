export class ChartManager {
    constructor() {
        this.charts = {};
    }

    initialize() {
        // Initialize chart configurations
        this.setupChartDefaults();
        this.setupEventListeners();
    }

    setupChartDefaults() {
        // Set default Chart.js configurations
        Chart.defaults.font.family = "'Roboto', 'Helvetica Neue', 'Arial', sans-serif";
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0, 0, 0, 0.8)';
        Chart.defaults.plugins.legend.position = 'bottom';
    }

    setupEventListeners() {
        // Add any chart-related event listeners
        window.addEventListener('resize', () => this.handleResize());
    }

    handleResize() {
        // Handle window resize for responsive charts
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.resize === 'function') {
                chart.resize();
            }
        });
    }

    createChart(canvasId, config) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;

        const ctx = canvas.getContext('2d');
        const chart = new Chart(ctx, config);
        this.charts[canvasId] = chart;
        return chart;
    }

    updateChart(canvasId, newData) {
        const chart = this.charts[canvasId];
        if (!chart) return;

        chart.data = newData;
        chart.update();
    }

    destroyChart(canvasId) {
        const chart = this.charts[canvasId];
        if (chart) {
            chart.destroy();
            delete this.charts[canvasId];
        }
    }
} 