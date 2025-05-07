// Export data module
class ExportData {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            const exportButtons = document.querySelectorAll('.export-btn');
            exportButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    const format = e.target.dataset.format;
                    const type = e.target.dataset.type;
                    this.exportData(type, format);
                });
            });
        });
    }

    exportData(type, format) {
        fetch(`/api/export/${type}?format=${format}`)
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `${type}_export.${format}`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            })
            .catch(error => {
                console.error('Error exporting data:', error);
            });
    }
}

// Initialize export data functionality
const exportData = new ExportData(); 