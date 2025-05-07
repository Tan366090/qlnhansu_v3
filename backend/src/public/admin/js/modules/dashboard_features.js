// Dashboard features module
const DashboardFeatures = {
    init() {
        console.log('Dashboard features initialized');
        this.loadData();
    },

    async loadData() {
        try {
            const response = await fetch('/api/dashboard-data');
            const data = await response.json();
            this.updateDashboard(data);
        } catch (error) {
            console.error('Error loading dashboard data:', error);
        }
    },

    updateDashboard(data) {
        // Update metrics
        document.getElementById('totalEmployees').textContent = data.totalEmployees || 0;
        document.getElementById('activeEmployees').textContent = data.activeEmployees || 0;
        document.getElementById('todayAttendance').textContent = data.todayAttendance || '0%';
        
        // Update charts if they exist
        if (window.updateCharts) {
            window.updateCharts(data);
        }
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    DashboardFeatures.init();
}); 