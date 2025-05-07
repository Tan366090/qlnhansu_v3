class AttendanceHistory {
    constructor() {
        this.filterForm = document.getElementById('filterForm');
        this.attendanceBody = document.getElementById('attendanceBody');
        this.pagination = document.getElementById('pagination');
        this.exportBtn = document.getElementById('exportBtn');
        this.loadingSpinner = document.getElementById('loadingSpinner');
        this.errorMessage = document.getElementById('errorMessage');
        this.successMessage = document.getElementById('successMessage');

        this.currentPage = 1;
        this.pageSize = 10;
        this.totalPages = 1;
        this.filters = {
            start_date: '',
            end_date: '',
            employee_id: '',
            status: ''
        };

        this.initializeElements();
        this.setupEventListeners();
        this.loadAttendanceData();
    }

    initializeElements() {
        // Set default date range to current month
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

        this.filterForm.elements['start_date'].value = this.formatDate(firstDay);
        this.filterForm.elements['end_date'].value = this.formatDate(lastDay);

        this.filters.start_date = this.formatDate(firstDay);
        this.filters.end_date = this.formatDate(lastDay);
    }

    setupEventListeners() {
        // Filter form submit
        this.filterForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.currentPage = 1;
            this.updateFilters();
            this.loadAttendanceData();
        });

        // Filter form reset
        this.filterForm.addEventListener('reset', () => {
            this.currentPage = 1;
            this.initializeElements();
            this.loadAttendanceData();
        });

        // Export button click
        this.exportBtn.addEventListener('click', () => {
            this.exportReport();
        });
    }

    updateFilters() {
        this.filters = {
            start_date: this.filterForm.elements['start_date'].value,
            end_date: this.filterForm.elements['end_date'].value,
            employee_id: this.filterForm.elements['employee_id'].value,
            status: this.filterForm.elements['status'].value
        };
    }

    async loadAttendanceData() {
        this.showLoading();
        try {
            const queryParams = new URLSearchParams({
                page: this.currentPage,
                page_size: this.pageSize,
                ...this.filters
            });

            const response = await fetch(`/api/attendance/history?${queryParams}`);
            if (!response.ok) throw new Error('Failed to load attendance data');
            
            const data = await response.json();
            this.totalPages = Math.ceil(data.total / this.pageSize);
            
            this.renderAttendanceData(data.data);
            this.renderPagination();
        } catch (error) {
            this.showError('Lỗi khi tải dữ liệu chấm công');
            console.error(error);
        } finally {
            this.hideLoading();
        }
    }

    renderAttendanceData(attendanceData) {
        this.attendanceBody.innerHTML = attendanceData.map(record => `
            <tr>
                <td>${record.employee_id}</td>
                <td>${record.employee_name}</td>
                <td>${this.formatDate(new Date(record.date))}</td>
                <td>${record.check_in || '-'}</td>
                <td>${record.check_out || '-'}</td>
                <td>
                    <span class="status-badge status-${record.status}">
                        ${this.getStatusLabel(record.status)}
                    </span>
                </td>
            </tr>
        `).join('');
    }

    renderPagination() {
        let paginationHTML = '';
        
        // Previous button
        paginationHTML += `
            <li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${this.currentPage - 1}">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        `;

        // Page numbers
        for (let i = 1; i <= this.totalPages; i++) {
            if (
                i === 1 || 
                i === this.totalPages || 
                (i >= this.currentPage - 2 && i <= this.currentPage + 2)
            ) {
                paginationHTML += `
                    <li class="page-item ${i === this.currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `;
            } else if (
                i === this.currentPage - 3 || 
                i === this.currentPage + 3
            ) {
                paginationHTML += `
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                `;
            }
        }

        // Next button
        paginationHTML += `
            <li class="page-item ${this.currentPage === this.totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${this.currentPage + 1}">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `;

        this.pagination.innerHTML = paginationHTML;

        // Add click event listeners to pagination links
        this.pagination.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = parseInt(link.dataset.page);
                if (page && page !== this.currentPage) {
                    this.currentPage = page;
                    this.loadAttendanceData();
                }
            });
        });
    }

    async exportReport() {
        this.showLoading();
        try {
            const queryParams = new URLSearchParams(this.filters);
            const response = await fetch(`/api/attendance/export?${queryParams}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                }
            });

            if (!response.ok) throw new Error('Failed to export report');

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `attendance_report_${this.formatDate(new Date())}.xlsx`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);

            this.showSuccess('Xuất báo cáo thành công');
        } catch (error) {
            this.showError('Lỗi khi xuất báo cáo');
            console.error(error);
        } finally {
            this.hideLoading();
        }
    }

    formatDate(date) {
        return date.toISOString().split('T')[0];
    }

    getStatusLabel(status) {
        const labels = {
            'on_time': 'Đúng giờ',
            'late': 'Muộn',
            'absent': 'Nghỉ'
        };
        return labels[status] || status;
    }

    showLoading() {
        this.loadingSpinner.style.display = 'flex';
    }

    hideLoading() {
        this.loadingSpinner.style.display = 'none';
    }

    showError(message) {
        this.errorMessage.querySelector('span').textContent = message;
        this.errorMessage.style.display = 'block';
        setTimeout(() => {
            this.errorMessage.style.display = 'none';
        }, 5000);
    }

    showSuccess(message) {
        this.successMessage.querySelector('span').textContent = message;
        this.successMessage.style.display = 'block';
        setTimeout(() => {
            this.successMessage.style.display = 'none';
        }, 5000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.attendanceHistory = new AttendanceHistory();
}); 