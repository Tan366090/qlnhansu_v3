/**
 * @module export-data
 * @description Handles data export functionality
 */

// Export data module
const ExportData = {
    init() {
        console.log('Export data module initialized');
        this.setupEventListeners();
    },

    setupEventListeners() {
        const exportButtons = document.querySelectorAll('.export-btn');
        exportButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const format = e.target.dataset.format || 'excel';
                const dataType = e.target.dataset.type || 'employees';
                this.exportData(dataType, format);
            });
        });
    },

    async exportData(type, format) {
        try {
            const response = await fetch(`/api/export/${type}?format=${format}`);
            const blob = await response.blob();
            
            // Create download link
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${type}_export.${format}`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();
        } catch (error) {
            console.error('Error exporting data:', error);
            alert('Có lỗi xảy ra khi xuất dữ liệu');
        }
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    ExportData.init();
}); 