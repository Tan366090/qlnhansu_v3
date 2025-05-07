class TableManager {
    constructor(options) {
        this.tableData = options.tableData;
        this.rows = options.rows;
        this.modal = options.modal;
        this.closeModal = options.closeModal;
        this.searchInput = options.searchInput;
        this.statusFilter = options.statusFilter;
        this.countFilter = options.countFilter;
        this.exportPdfBtn = options.exportPdfBtn;
        this.tableBody = options.tableBody;
        this.pagination = options.pagination;

        this.currentPage = 1;
        this.rowsPerPage = 10;
        this.currentSort = { column: 'name', direction: 'asc' };
        this.filteredRows = Array.from(this.rows);

        this.initialize();
    }

    initialize() {
        // Event listeners
        this.searchInput.addEventListener('input', () => this.filterRows());
        this.statusFilter.addEventListener('change', () => this.filterRows());
        this.countFilter.addEventListener('change', () => this.filterRows());
        
        // Export button
        if (this.exportPdfBtn) {
            this.exportPdfBtn.addEventListener('click', () => {
                this.exportPdfBtn.classList.add('loading');
                window.location.href = '?export=pdf';
                setTimeout(() => {
                    this.exportPdfBtn.classList.remove('loading');
                }, 5000);
            });
        }

        // Sort functionality
        document.querySelectorAll('.sortable').forEach(header => {
            header.addEventListener('click', () => {
                const column = header.getAttribute('data-sort');
                if (this.currentSort.column === column) {
                    this.currentSort.direction = this.currentSort.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    this.currentSort.column = column;
                    this.currentSort.direction = 'asc';
                }

                // Update sort icons
                document.querySelectorAll('.sortable i').forEach(icon => {
                    icon.className = 'fas fa-sort';
                });
                header.querySelector('i').className = `fas fa-sort-${this.currentSort.direction === 'asc' ? 'up' : 'down'}`;

                this.sortRows();
            });
        });

        // Modal functionality
        this.rows.forEach(row => {
            row.addEventListener('click', () => this.showModal(row));
        });

        // View sample data button
        document.addEventListener('click', (e) => {
            if (e.target && e.target.classList.contains('view-sample-btn')) {
                e.stopPropagation(); // Prevent row click event
                const tableName = e.target.dataset.table;
                this.showSampleData(tableName);
            }
        });

        // Close buttons
        this.closeModal.addEventListener('click', () => this.closeModalFunc());
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.closeModalFunc();
            }
        });

        // Sample modal close button
        const sampleModalClose = document.querySelector('.sample-modal-close');
        if (sampleModalClose) {
            sampleModalClose.addEventListener('click', () => this.closeSampleModal());
        }

        // Sample modal background click
        const sampleModal = document.getElementById('sampleModal');
        if (sampleModal) {
            sampleModal.addEventListener('click', (e) => {
                if (e.target === sampleModal) {
                    this.closeSampleModal();
                }
            });
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (this.modal.classList.contains('active')) {
                    this.closeModalFunc();
                }
                if (sampleModal && sampleModal.classList.contains('active')) {
                    this.closeSampleModal();
                }
            }
        });

        // Initialize
        this.filterRows();
        this.updatePagination();
    }

    closeSampleModal() {
        const sampleModal = document.getElementById('sampleModal');
        if (sampleModal) {
            sampleModal.classList.remove('active');
        }
    }

    filterRows() {
        const searchTerm = this.searchInput.value.toLowerCase();
        const statusValue = this.statusFilter.value;
        const countValue = this.countFilter.value;

        this.filteredRows = Array.from(this.rows).filter(row => {
            const tableName = row.querySelector('.table-name').textContent.toLowerCase();
            const description = row.cells[2].textContent.toLowerCase();
            const count = parseInt(row.cells[3].textContent) || 0;
            const statusBadge = row.cells[4].querySelector('.status-badge');
            const status = statusBadge ? statusBadge.textContent.trim() : '';

            const matchesSearch = tableName.includes(searchTerm) || description.includes(searchTerm);
            const matchesStatus = !statusValue || status === statusValue;
            const matchesCount = !countValue || (
                (countValue === '0' && count === 0) ||
                (countValue === '1-10' && count >= 1 && count <= 10) ||
                (countValue === '11-100' && count >= 11 && count <= 100) ||
                (countValue === '101+' && count > 100)
            );

            return matchesSearch && matchesStatus && matchesCount;
        });

        this.currentPage = 1;
        this.updatePagination();
        this.displayCurrentPage();
    }

    sortRows() {
        const rowsArray = Array.from(this.filteredRows);
        rowsArray.sort((a, b) => {
            let aValue, bValue;
            
            if (this.currentSort.column === 'name') {
                aValue = a.querySelector('.table-name').textContent;
                bValue = b.querySelector('.table-name').textContent;
            } else if (this.currentSort.column === 'description') {
                aValue = a.cells[2].textContent;
                bValue = b.cells[2].textContent;
            } else if (this.currentSort.column === 'count') {
                aValue = parseInt(a.cells[3].textContent) || 0;
                bValue = parseInt(b.cells[3].textContent) || 0;
            } else if (this.currentSort.column === 'status') {
                const aBadge = a.cells[4].querySelector('.status-badge');
                const bBadge = b.cells[4].querySelector('.status-badge');
                aValue = aBadge ? aBadge.textContent.trim() : '';
                bValue = bBadge ? bBadge.textContent.trim() : '';
            }

            if (this.currentSort.direction === 'asc') {
                return aValue > bValue ? 1 : -1;
            } else {
                return aValue < bValue ? 1 : -1;
            }
        });

        this.filteredRows = rowsArray;
        this.displayCurrentPage();
    }

    displayCurrentPage() {
        const start = (this.currentPage - 1) * this.rowsPerPage;
        const end = start + this.rowsPerPage;
        const currentRows = this.filteredRows.slice(start, end);

        this.tableBody.innerHTML = '';
        currentRows.forEach(row => this.tableBody.appendChild(row.cloneNode(true)));
    }

    updatePagination() {
        const totalPages = Math.ceil(this.filteredRows.length / this.rowsPerPage);
        this.pagination.innerHTML = '';

        // Previous button
        const prevButton = document.createElement('button');
        prevButton.textContent = 'Trước';
        prevButton.disabled = this.currentPage === 1;
        prevButton.addEventListener('click', () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.displayCurrentPage();
                this.updatePagination();
            }
        });
        this.pagination.appendChild(prevButton);

        // Page buttons
        for (let i = 1; i <= totalPages; i++) {
            const pageButton = document.createElement('button');
            pageButton.textContent = i;
            pageButton.className = this.currentPage === i ? 'active' : '';
            pageButton.addEventListener('click', () => {
                this.currentPage = i;
                this.displayCurrentPage();
                this.updatePagination();
            });
            this.pagination.appendChild(pageButton);
        }

        // Next button
        const nextButton = document.createElement('button');
        nextButton.textContent = 'Sau';
        nextButton.disabled = this.currentPage === totalPages;
        nextButton.addEventListener('click', () => {
            if (this.currentPage < totalPages) {
                this.currentPage++;
                this.displayCurrentPage();
                this.updatePagination();
            }
        });
        this.pagination.appendChild(nextButton);
    }

    showModal(row) {
        const tableName = row.getAttribute('data-table');
        const tableData = this.tableData[tableName];
        
        if (!tableData) return;

        // Update modal content
        document.getElementById('detailTableName').textContent = tableName;
        document.getElementById('detailDescription').textContent = tableData.description;
        document.getElementById('detailRecordCount').textContent = tableData.count || '-';
        
        // Update status with appropriate badge
        const statusElement = document.getElementById('detailStatus');
        statusElement.innerHTML = '';
        if (tableData.error) {
            statusElement.innerHTML = '<span class="status-badge status-error">Lỗi truy cập</span>';
        } else if (tableData.count > 0) {
            statusElement.innerHTML = '<span class="status-badge status-success">Có dữ liệu</span>';
        } else {
            statusElement.innerHTML = '<span class="status-badge status-empty">Không có dữ liệu</span>';
        }
        
        document.getElementById('detailColumns').textContent = tableData.columns ? tableData.columns.join(', ') : '-';
        
        // Format and display sample data
        const sampleData = tableData.sample || [];
        const formattedData = JSON.stringify(sampleData, null, 2);
        const sampleDataElement = document.getElementById('detailSampleData');
        sampleDataElement.textContent = formattedData;
        if (typeof hljs !== 'undefined') {
            hljs.highlightElement(sampleDataElement);
        }

        // Show modal
        this.modal.classList.add('active');
    }

    showSampleData(tableName) {
        const tableData = this.tableData[tableName];
        if (!tableData || !tableData.sample || tableData.sample.length === 0) return;

        // Update modal content
        document.getElementById('modalTableName').textContent = tableName;
        document.getElementById('modalTableDescription').textContent = tableData.description;
        document.getElementById('modalTableCount').textContent = tableData.count || '-';
        
        // Update status
        const statusElement = document.getElementById('modalTableStatus');
        if (tableData.error) {
            statusElement.textContent = 'Lỗi truy cập';
            statusElement.style.color = 'var(--danger-color)';
        } else if (tableData.count > 0) {
            statusElement.textContent = 'Có dữ liệu';
            statusElement.style.color = 'var(--success-color)';
        } else {
            statusElement.textContent = 'Không có dữ liệu';
            statusElement.style.color = 'var(--warning-color)';
        }

        // Render sample data table
        const sampleTableHeader = document.getElementById('sampleTableHeader');
        const sampleTableBody = document.getElementById('sampleTableBody');
        
        // Clear existing content
        sampleTableHeader.innerHTML = '';
        sampleTableBody.innerHTML = '';

        // Add STT column
        const sttTh = document.createElement('th');
        sttTh.textContent = 'STT';
        sampleTableHeader.appendChild(sttTh);

        // Add other columns
        if (tableData.sample.length > 0) {
            const firstRow = tableData.sample[0];
            Object.keys(firstRow).forEach(key => {
                const th = document.createElement('th');
                th.textContent = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                sampleTableHeader.appendChild(th);
            });

            // Add data rows
            tableData.sample.forEach((row, index) => {
                const tr = document.createElement('tr');
                
                // Add STT cell
                const sttTd = document.createElement('td');
                sttTd.textContent = index + 1;
                tr.appendChild(sttTd);

                // Add data cells
                Object.values(row).forEach(value => {
                    const td = document.createElement('td');
                    td.textContent = this.formatValue(value);
                    tr.appendChild(td);
                });

                sampleTableBody.appendChild(tr);
            });
        }

        // Show sample modal
        document.getElementById('sampleModal').classList.add('active');
    }

    formatValue(value) {
        if (value === null) return 'Chưa có dữ liệu';
        if (typeof value === 'string' && value.match(/^\d{4}-\d{2}-\d{2}/)) {
            const date = new Date(value);
            return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN');
        }
        return value;
    }

    closeModalFunc() {
        this.modal.classList.remove('active');
    }
} 