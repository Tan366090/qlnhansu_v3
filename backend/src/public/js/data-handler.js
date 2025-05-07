// Data Handler Class
class DataHandler {
    constructor(tableId, options = {}) {
        this.tableId = tableId;
        this.options = {
            pageSize: 10,
            searchable: true,
            filterable: true,
            exportable: true,
            printable: true,
            ...options
        };
        this.currentPage = 1;
        this.filteredData = [];
        this.setupTable();
    }

    // Setup table with search, filter, and pagination
    setupTable() {
        const table = document.getElementById(this.tableId);
        if (!table) return;

        // Add search container if searchable
        if (this.options.searchable) {
            this.addSearchContainer(table);
        }

        // Add filter container if filterable
        if (this.options.filterable) {
            this.addFilterContainer(table);
        }

        // Add action buttons if exportable or printable
        if (this.options.exportable || this.options.printable) {
            this.addActionButtons(table);
        }

        // Make table responsive
        table.classList.add('responsive-table');

        // Add pagination
        this.addPagination(table);
    }

    // Add search container
    addSearchContainer(table) {
        const container = document.createElement('div');
        container.className = 'search-container';
        container.innerHTML = `
            <input type="text" placeholder="Tìm kiếm..." aria-label="Tìm kiếm">
            <i class="fas fa-search"></i>
        `;
        table.parentNode.insertBefore(container, table);

        const searchInput = container.querySelector('input');
        searchInput.addEventListener('input', (e) => {
            this.filterData(e.target.value);
            this.updatePagination();
            this.renderTable();
        });
    }

    // Add filter container
    addFilterContainer(table) {
        const container = document.createElement('div');
        container.className = 'filter-container';
        
        // Add filter options based on table columns
        const headers = table.querySelectorAll('th');
        headers.forEach((header, index) => {
            if (header.dataset.filterable !== 'false') {
                const select = document.createElement('select');
                select.setAttribute('aria-label', `Lọc theo ${header.textContent}`);
                select.innerHTML = `
                    <option value="">Tất cả ${header.textContent}</option>
                `;
                container.appendChild(select);

                select.addEventListener('change', () => {
                    this.filterData(null, index, select.value);
                    this.updatePagination();
                    this.renderTable();
                });
            }
        });

        table.parentNode.insertBefore(container, table);
    }

    // Add action buttons
    addActionButtons(table) {
        const container = document.createElement('div');
        container.className = 'action-buttons';

        if (this.options.exportable) {
            const exportBtn = document.createElement('button');
            exportBtn.className = 'export-btn';
            exportBtn.innerHTML = '<i class="fas fa-file-export"></i> Xuất dữ liệu';
            exportBtn.addEventListener('click', () => this.exportData());
            container.appendChild(exportBtn);
        }

        if (this.options.printable) {
            const printBtn = document.createElement('button');
            printBtn.className = 'print-btn';
            printBtn.innerHTML = '<i class="fas fa-print"></i> In';
            printBtn.addEventListener('click', () => this.printData());
            container.appendChild(printBtn);
        }

        table.parentNode.insertBefore(container, table);
    }

    // Add pagination
    addPagination(table) {
        const container = document.createElement('div');
        container.className = 'pagination';
        table.parentNode.appendChild(container);
    }

    // Filter data based on search text and filters
    filterData(searchText = '', filterIndex = -1, filterValue = '') {
        const table = document.getElementById(this.tableId);
        if (!table) return;

        const rows = Array.from(table.querySelectorAll('tbody tr'));
        this.filteredData = rows.filter(row => {
            let matchesSearch = true;
            let matchesFilter = true;

            if (searchText) {
                matchesSearch = Array.from(row.cells).some(cell => 
                    cell.textContent.toLowerCase().includes(searchText.toLowerCase())
                );
            }

            if (filterIndex >= 0 && filterValue) {
                matchesFilter = row.cells[filterIndex].textContent === filterValue;
            }

            return matchesSearch && matchesFilter;
        });
    }

    // Update pagination
    updatePagination() {
        const container = document.querySelector(`#${this.tableId} + .pagination`);
        if (!container) return;

        const totalPages = Math.ceil(this.filteredData.length / this.options.pageSize);
        let html = '';

        // Previous button
        html += `
            <button 
                class="prev-page" 
                ${this.currentPage === 1 ? 'disabled' : ''}
                aria-label="Trang trước"
            >
                <i class="fas fa-chevron-left"></i>
            </button>
        `;

        // Page buttons
        for (let i = 1; i <= totalPages; i++) {
            html += `
                <button 
                    class="${i === this.currentPage ? 'active' : ''}"
                    aria-label="Trang ${i}"
                    ${i === this.currentPage ? 'aria-current="page"' : ''}
                >
                    ${i}
                </button>
            `;
        }

        // Next button
        html += `
            <button 
                class="next-page" 
                ${this.currentPage === totalPages ? 'disabled' : ''}
                aria-label="Trang sau"
            >
                <i class="fas fa-chevron-right"></i>
            </button>
        `;

        container.innerHTML = html;

        // Add event listeners
        container.querySelectorAll('button').forEach(button => {
            button.addEventListener('click', () => {
                if (button.classList.contains('prev-page')) {
                    this.currentPage = Math.max(1, this.currentPage - 1);
                } else if (button.classList.contains('next-page')) {
                    this.currentPage = Math.min(totalPages, this.currentPage + 1);
                } else {
                    this.currentPage = parseInt(button.textContent);
                }
                this.renderTable();
            });
        });
    }

    // Render table with current page data
    renderTable() {
        const table = document.getElementById(this.tableId);
        if (!table) return;

        const tbody = table.querySelector('tbody');
        const start = (this.currentPage - 1) * this.options.pageSize;
        const end = start + this.options.pageSize;
        const pageData = this.filteredData.slice(start, end);

        tbody.innerHTML = pageData.map(row => row.outerHTML).join('');
    }

    // Export data to CSV
    exportData() {
        const table = document.getElementById(this.tableId);
        if (!table) return;

        const headers = Array.from(table.querySelectorAll('th')).map(th => th.textContent);
        const rows = Array.from(table.querySelectorAll('tbody tr')).map(tr => 
            Array.from(tr.cells).map(cell => cell.textContent)
        );

        const csvContent = [
            headers.join(','),
            ...rows.map(row => row.join(','))
        ].join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `${this.tableId}_export_${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
    }

    // Print table data
    printData() {
        const table = document.getElementById(this.tableId);
        if (!table) return;

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>In bảng dữ liệu</title>
                    <style>
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f5f5f5; }
                    </style>
                </head>
                <body>
                    <h1>${document.title}</h1>
                    ${table.outerHTML}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }
}

// Example usage:
/*
const dataHandler = new DataHandler('myTable', {
    pageSize: 10,
    searchable: true,
    filterable: true,
    exportable: true,
    printable: true
});
*/ 