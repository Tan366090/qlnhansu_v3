class DegreesManager {
    constructor() {
        this.currentPage = 1;
        this.limit = 10;
        this.search = '';
        this.type = '';
        this.status = '';
        this.date = '';
        this.organization = '';
        this.dateFrom = '';
        this.dateTo = '';
        this.department = '';
        this.employee = '';
        this.degreeTypeChart = null;
        this.certificateOrgChart = null;
        this.modal = document.getElementById('degreeModal');
        this.form = document.getElementById('degreeForm');
        this.currentEditId = null;
        this.currentEditType = null;
        this.infoModal = new bootstrap.Modal(document.getElementById('degreeInfoModal'));
        this.sortColumn = null;
        this.sortDirection = 'asc';
        
        // Initialize toast container if not exists
        if (!document.querySelector('.toast-container')) {
            const toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
            document.body.appendChild(toastContainer);
        }
        
        this.initializeEventListeners();
        this.initializeModalEvents();
        this.loadData();
    }

    initializeEventListeners() {
        // Smart Search
        $('#smartSearchInput').on('input', debounce(() => {
            this.search = $('#smartSearchInput').val();
            this.currentPage = 1;
            this.loadData();
        }, 500));

        // Quick Filters
        $('#typeFilter').on('change', () => {
            this.type = $('#typeFilter').val();
            this.currentPage = 1;
            this.loadData();
        });

        $('#statusFilter').on('change', () => {
            this.status = $('#statusFilter').val();
            this.currentPage = 1;
            this.loadData();
        });

        $('#dateFilter').on('change', () => {
            this.date = $('#dateFilter').val();
            this.currentPage = 1;
            this.loadData();
        });

        // Advanced Search
        $('#advancedSearchToggle').on('click', () => {
            $('#advancedSearchPanel').toggle();
        });

        $('#applyFilters').on('click', () => {
            this.organization = $('#orgFilter').val();
            this.dateFrom = $('#dateFrom').val();
            this.dateTo = $('#dateTo').val();
            this.department = $('#departmentFilter').val();
            this.employee = $('#employeeFilter').val();
            this.currentPage = 1;
            this.loadData();
        });

        $('#resetFilters').on('click', () => {
            $('#advancedSearchPanel input, #advancedSearchPanel select').val('');
            $('#activeFilters').empty();
            this.organization = '';
            this.dateFrom = '';
            this.dateTo = '';
            this.department = '';
            this.employee = '';
            this.loadData();
        });

        $('#saveFilterPreset').on('click', () => {
            const presetName = prompt('Nhập tên cho bộ lọc này:');
            if (presetName) {
                const filters = {
                    name: presetName,
                    organization: this.organization,
                    dateFrom: this.dateFrom,
                    dateTo: this.dateTo,
                    department: this.department,
                    employee: this.employee
                };
                this.saveFilterPreset(filters);
            }
        });

        // Add new degree/certificate
        $('#addDegreeBtn').on('click', () => this.showAddModal());

        // Export to Excel
        $('#exportBtn').on('click', () => this.exportToExcel());

        // Toggle chart view
        $('#toggleChartView').on('click', () => this.toggleChartView());

        // Add sorting event listeners
        $('.table th').on('click', (e) => {
            const column = $(e.target).data('column');
            if (column) {
                this.handleSort(column);
            }
        });
    }

    initializeModalEvents() {
        // Close modal when clicking close button or outside
        const closeBtn = this.modal.querySelector('.close');
        const cancelBtn = document.getElementById('cancelBtn');
        
        closeBtn.onclick = () => this.hideModal();
        cancelBtn.onclick = () => this.hideModal();
        window.onclick = (event) => {
            if (event.target === this.modal) {
                this.hideModal();
            }
        };

        // Form submit handler
        this.form.onsubmit = (e) => {
            e.preventDefault();
            this.handleFormSubmit();
        };

        // File upload handler
        const attachmentInput = document.getElementById('attachment');
        attachmentInput.onchange = (e) => this.handleFileUpload(e);
    }

    async loadData() {
        const loadingToast = this.showLoading();
        try {
            // Load dashboard stats
            const stats = await this.fetchData('dashboard_stats');
            this.updateDashboardStats(stats);

            // Load degree distribution
            const degreeDist = await this.fetchData('degree_distribution');
            this.updateDegreeChart(degreeDist);

            // Load certificate distribution
            const certDist = await this.fetchData('certificate_distribution');
            this.updateCertificateChart(certDist);

            // Load main list with all filters
            const listData = await this.fetchData('list', {
                page: this.currentPage,
                limit: this.limit,
                search: this.search,
                type: this.type,
                status: this.status,
                date: this.date,
                organization: this.organization,
                dateFrom: this.dateFrom,
                dateTo: this.dateTo,
                department: this.department,
                employee: this.employee,
                sortColumn: this.sortColumn,
                sortDirection: this.sortDirection
            });
            this.updateTable(listData);
            this.updatePagination(listData.total);
            this.updateActiveFilters();

            this.showToast('Dữ liệu đã được cập nhật thành công', 'success');
        } catch (error) {
            this.showToast('Lỗi khi tải dữ liệu: ' + error.message, 'error');
        } finally {
            this.hideLoading(loadingToast);
        }
    }

    async fetchData(action, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = `../api/degrees.php?action=${action}${queryString ? '&' + queryString : ''}`;
        
        try {
            const response = await fetch(url, {
                method: params instanceof FormData ? 'POST' : 'GET',
                body: params instanceof FormData ? params : undefined,
                headers: params instanceof FormData ? {} : {
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            // Add CSRF token to all subsequent requests if provided
            if (data.csrf_token) {
                this.csrfToken = data.csrf_token;
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            this.showToast('Lỗi kết nối API: ' + error.message, 'error');
            throw error;
        }
    }

    updateDashboardStats(stats) {
        $('#totalDegrees').text(stats.total_degrees);
        $('#totalCertificates').text(stats.total_certificates);
        $('#expiringCertificates').text(stats.expiring_certificates);
    }

    updateDegreeChart(data) {
        const ctx = document.getElementById('degreeTypeChart').getContext('2d');
        
        if (this.degreeTypeChart) {
            this.degreeTypeChart.destroy();
        }

        this.degreeTypeChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(item => item.degree_name),
                datasets: [{
                    label: 'Số lượng',
                    data: data.map(item => item.count),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    updateCertificateChart(data) {
        const ctx = document.getElementById('certificateOrgChart').getContext('2d');
        
        if (this.certificateOrgChart) {
            this.certificateOrgChart.destroy();
        }

        this.certificateOrgChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data.map(item => item.issuing_organization),
                datasets: [{
                    data: data.map(item => item.count),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(153, 102, 255, 0.5)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    updateTable(data) {
        const tbody = $('#degreesTableBody');
        tbody.empty();

        if (!data || !data.data || data.data.length === 0) {
            tbody.html('<tr><td colspan="10" class="text-center">Không có dữ liệu</td></tr>');
            return;
        }

        data.data.forEach((item, index) => {
            const row = `
                <tr>
                    <td>${(this.currentPage - 1) * this.limit + index + 1}</td>
                    <td>${item.type === 'degree' ? 'Bằng cấp' : 'Chứng chỉ'}</td>
                    <td>${item.name || ''}</td>
                    <td>${item.employee_name || ''}</td>
                    <td>${item.organization || ''}</td>
                    <td>${this.formatDate(item.issue_date)}</td>
                    <td>${item.expiry_date ? this.formatDate(item.expiry_date) : 'N/A'}</td>
                    <td>
                        <span class="badge ${this.getStatusBadgeClass(item.status)}">
                            ${this.getStatusText(item.status)}
                        </span>
                    </td>
                    <td>
                        ${item.attachment_url ? 
                            `<a href="${item.attachment_url}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-file"></i> Xem
                            </a>` : 
                            'Không có'
                        }
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-view" onclick="degreesManager.viewDetails(${item.id}, '${item.type}')">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-edit" onclick="degreesManager.editItem(${item.id}, '${item.type}')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-delete" onclick="degreesManager.deleteItem(${item.id}, '${item.type}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    updatePagination(total) {
        const totalPages = Math.ceil(total / this.limit);
        const pagination = $('#pagination');
        pagination.empty();

        // Previous button
        pagination.append(`
            <li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${this.currentPage - 1}">Trước</a>
            </li>
        `);

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            pagination.append(`
                <li class="page-item ${this.currentPage === i ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }

        // Next button
        pagination.append(`
            <li class="page-item ${this.currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${this.currentPage + 1}">Sau</a>
            </li>
        `);

        // Add click event
        pagination.find('.page-link').on('click', (e) => {
            e.preventDefault();
            const page = $(e.target).data('page');
            if (page && page !== this.currentPage) {
                this.currentPage = page;
                this.loadData();
            }
        });
    }

    toggleChartView() {
        if (this.degreeTypeChart.config.type === 'bar') {
            this.degreeTypeChart.config.type = 'pie';
        } else {
            this.degreeTypeChart.config.type = 'bar';
        }
        this.degreeTypeChart.update();
    }

    showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        const icon = type === 'success' ? 'check-circle' : 
                    type === 'error' ? 'exclamation-circle' :
                    type === 'warning' ? 'exclamation-triangle' : 'info-circle';
        
        toast.innerHTML = `
            <i class="fas fa-${icon}"></i>
            <div class="toast-content">
                <div class="toast-title">${type === 'success' ? 'Thành công' : 
                                         type === 'error' ? 'Lỗi' :
                                         type === 'warning' ? 'Cảnh báo' : 'Thông tin'}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close">
                <i class="fas fa-times"></i>
            </button>
            <div class="toast-progress"></div>
        `;

        const container = document.querySelector('.toast-container');
        container.appendChild(toast);

        // Add close button functionality
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => {
            toast.classList.add('hide');
            setTimeout(() => toast.remove(), 300);
        });

        // Auto remove after 3 seconds
        setTimeout(() => {
            if (toast.parentElement) {
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 300);
            }
        }, 3000);
    }

    showLoading(message = 'Đang tải dữ liệu...') {
        const toast = document.createElement('div');
        toast.className = 'toast info';
        toast.innerHTML = `
            <i class="fas fa-spinner fa-spin"></i>
            <div class="toast-content">
                <div class="toast-title">Đang xử lý</div>
                <div class="toast-message">${message}</div>
            </div>
        `;

        const container = document.querySelector('.toast-container');
        container.appendChild(toast);
        return toast;
    }

    hideLoading(loadingToast) {
        if (loadingToast && loadingToast.parentElement) {
            loadingToast.classList.add('hide');
            setTimeout(() => loadingToast.remove(), 300);
        }
    }

    // Utility functions
    formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN');
    }

    getStatusBadgeClass(status) {
        switch (status) {
            case 'valid': return 'bg-success';
            case 'expired': return 'bg-danger';
            case 'expiring': return 'bg-warning';
            default: return 'bg-secondary';
        }
    }

    getStatusText(status) {
        switch (status) {
            case 'valid': return 'Còn hiệu lực';
            case 'expired': return 'Hết hạn';
            case 'expiring': return 'Sắp hết hạn';
            default: return 'Không xác định';
        }
    }

    async showAddModal() {
        this.currentEditId = null;
        this.currentEditType = null;
        document.getElementById('modalTitle').textContent = 'Thêm bằng cấp/chứng chỉ mới';
        this.form.reset();
        await this.loadEmployeeList();
        this.modal.style.display = 'block';
    }

    async editItem(id, type) {
        this.currentEditId = id;
        this.currentEditType = type;
        document.getElementById('modalTitle').textContent = 'Chỉnh sửa bằng cấp/chứng chỉ';
        
        try {
            const data = await this.fetchData('get', { id, type });
            if (data) {
                this.populateForm(data);
                await this.loadEmployeeList();
                this.modal.style.display = 'block';
            }
        } catch (error) {
            this.showToast('Lỗi khi tải thông tin: ' + error.message, 'error');
        }
    }

    async loadEmployeeList() {
        try {
            const employees = await this.fetchData('employees');
            const select = document.getElementById('employeeId');
            select.innerHTML = '<option value="">Chọn nhân viên</option>';
            
            employees.forEach(employee => {
                const option = document.createElement('option');
                option.value = employee.id;
                option.textContent = employee.name;
                select.appendChild(option);
            });
        } catch (error) {
            this.showToast('Lỗi khi tải danh sách nhân viên: ' + error.message, 'error');
        }
    }

    populateForm(data) {
        document.getElementById('degreeType').value = data.type;
        document.getElementById('employeeId').value = data.employee_id;
        document.getElementById('degreeName').value = data.name;
        document.getElementById('organization').value = data.organization;
        document.getElementById('issueDate').value = data.issue_date;
        document.getElementById('expiryDate').value = data.expiry_date || '';
        document.getElementById('credentialId').value = data.credential_id || '';
    }

    async handleFormSubmit() {
        const formData = new FormData(this.form);
        formData.append('type', document.getElementById('degreeType').value);
        
        if (this.currentEditId) {
            formData.append('id', this.currentEditId);
            formData.append('edit_type', this.currentEditType);
        }

        try {
            const loadingToast = this.showLoading('Đang lưu thông tin...');
            const response = await this.fetchData(
                this.currentEditId ? 'update' : 'create',
                formData
            );
            
            this.hideLoading(loadingToast);
            if (response.success) {
                this.showToast('Lưu thông tin thành công', 'success');
                this.hideModal();
                this.loadData();
            } else {
                throw new Error(response.message || 'Lỗi không xác định');
            }
        } catch (error) {
            this.showToast('Lỗi khi lưu thông tin: ' + error.message, 'error');
        }
    }

    handleFileUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            this.showToast('File không được vượt quá 5MB', 'error');
            event.target.value = '';
            return;
        }

        // Validate file type
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            this.showToast('Chỉ chấp nhận file PDF, JPEG hoặc PNG', 'error');
            event.target.value = '';
            return;
        }
    }

    hideModal() {
        this.modal.style.display = 'none';
        this.form.reset();
        this.currentEditId = null;
        this.currentEditType = null;
    }

    async viewDetails(id, type) {
        try {
            const data = await this.fetchData('get', { id, type });
            if (data) {
                this.populateInfoModal(data);
                this.infoModal.show();
            }
        } catch (error) {
            this.showToast('Lỗi khi tải thông tin: ' + error.message, 'error');
        }
    }

    populateInfoModal(data) {
        document.getElementById('infoType').textContent = data.type === 'degree' ? 'Bằng cấp' : 'Chứng chỉ';
        document.getElementById('infoName').textContent = data.name;
        document.getElementById('infoEmployee').textContent = data.employee_name;
        document.getElementById('infoOrganization').textContent = data.organization;
        document.getElementById('infoCredentialId').textContent = data.credential_id || 'N/A';
        document.getElementById('infoIssueDate').textContent = this.formatDate(data.issue_date);
        document.getElementById('infoExpiryDate').textContent = data.expiry_date ? this.formatDate(data.expiry_date) : 'N/A';
        document.getElementById('infoStatus').innerHTML = `
            <span class="badge ${this.getStatusBadgeClass(data.status)}">
                ${this.getStatusText(data.status)}
            </span>
        `;

        const attachmentElement = document.getElementById('infoAttachment');
        if (data.attachment_url) {
            attachmentElement.innerHTML = `
                <div class="attachment-preview">
                    <a href="${data.attachment_url}" target="_blank" class="btn btn-sm btn-outline-primary me-2">
                        <i class="fas fa-eye"></i> Xem
                    </a>
                    <a href="${data.attachment_url}" download class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-download"></i> Tải xuống
                    </a>
                </div>
            `;
        } else {
            attachmentElement.textContent = 'Không có file đính kèm';
        }
    }

    async deleteItem(id, type) {
        if (!confirm('Bạn có chắc chắn muốn xóa mục này?')) {
            return;
        }

        try {
            const loadingToast = this.showLoading('Đang xóa...');
            const response = await this.fetchData('delete', { id, type });
            
            this.hideLoading(loadingToast);
            if (response.success) {
                this.showToast('Xóa thành công', 'success');
                this.loadData();
            } else {
                throw new Error(response.message || 'Lỗi không xác định');
            }
        } catch (error) {
            this.showToast('Lỗi khi xóa: ' + error.message, 'error');
        }
    }

    handleSort(column) {
        if (this.sortColumn === column) {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortColumn = column;
            this.sortDirection = 'asc';
        }
        this.loadData();
    }

    async exportToExcel() {
        try {
            const loadingToast = this.showLoading('Đang xuất dữ liệu...');
            
            // Get all data without pagination
            const data = await this.fetchData('list', {
                search: this.search,
                type: this.type,
                status: this.status,
                export: true
            });

            if (!data || !data.data) {
                throw new Error('Không có dữ liệu để xuất');
            }

            // Create worksheet
            const ws = XLSX.utils.json_to_sheet(data.data.map(item => ({
                'STT': item.id,
                'Loại': item.type === 'degree' ? 'Bằng cấp' : 'Chứng chỉ',
                'Tên': item.name,
                'Nhân viên': item.employee_name,
                'Tổ chức cấp': item.organization,
                'Ngày cấp': this.formatDate(item.issue_date),
                'Ngày hết hạn': item.expiry_date ? this.formatDate(item.expiry_date) : 'N/A',
                'Trạng thái': this.getStatusText(item.status),
                'Mã chứng chỉ': item.credential_id || 'N/A'
            })));

            // Create workbook
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Bằng cấp và chứng chỉ');

            // Generate Excel file
            const fileName = `bang_cap_chung_chi_${new Date().toISOString().split('T')[0]}.xlsx`;
            XLSX.writeFile(wb, fileName);

            this.hideLoading(loadingToast);
            this.showToast('Xuất Excel thành công', 'success');
        } catch (error) {
            this.showToast('Lỗi khi xuất Excel: ' + error.message, 'error');
        }
    }

    updateActiveFilters() {
        const activeFilters = $('#activeFilters');
        activeFilters.empty();

        const filters = {
            organization: this.organization,
            dateFrom: this.dateFrom,
            dateTo: this.dateTo,
            department: this.department,
            employee: this.employee
        };

        Object.entries(filters).forEach(([key, value]) => {
            if (value) {
                const filterTag = $(`
                    <div class="filter-tag">
                        <span>${this.getFilterLabel(key)}: ${value}</span>
                        <span class="remove" data-filter="${key}">&times;</span>
                    </div>
                `);
                activeFilters.append(filterTag);
            }
        });

        // Add remove filter functionality
        $('.filter-tag .remove').on('click', (e) => {
            const filterKey = $(e.target).data('filter');
            this[filterKey] = '';
            $(`#${filterKey}Filter`).val('');
            $(e.target).closest('.filter-tag').remove();
            this.loadData();
        });
    }

    getFilterLabel(key) {
        const labels = {
            organization: 'Tổ chức',
            dateFrom: 'Từ ngày',
            dateTo: 'Đến ngày',
            department: 'Phòng ban',
            employee: 'Nhân viên'
        };
        return labels[key] || key;
    }

    saveFilterPreset(filters) {
        const presets = JSON.parse(localStorage.getItem('degreeFilterPresets') || '[]');
        presets.push(filters);
        localStorage.setItem('degreeFilterPresets', JSON.stringify(presets));
        this.showToast('Đã lưu bộ lọc thành công!', 'success');
    }
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize the manager when document is ready
let degreesManager;
$(document).ready(() => {
    degreesManager = new DegreesManager();
});

// Add global formatDate function if not already defined
if (typeof window.formatDate !== 'function') {
    window.formatDate = function(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN');
    };
}

// Add required libraries
document.head.innerHTML += `
    <script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>
`;

// Smart Search Implementation
document.addEventListener('DOMContentLoaded', function() {
    const smartSearchInput = document.getElementById('smartSearchInput');
    const searchSuggestions = document.getElementById('searchSuggestions');
    const advancedSearchToggle = document.getElementById('advancedSearchToggle');
    const advancedSearchPanel = document.getElementById('advancedSearchPanel');
    const activeFilters = document.getElementById('activeFilters');
    let searchTimeout;

    // Debounce function for search
    function debounce(func, wait) {
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(searchTimeout);
                func(...args);
            };
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(later, wait);
        };
    }

    // Smart search with suggestions
    smartSearchInput.addEventListener('input', debounce(async function(e) {
        const query = e.target.value.trim();
        if (query.length < 2) {
            searchSuggestions.style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`/api/degrees/search-suggestions?q=${encodeURIComponent(query)}`);
            const suggestions = await response.json();
            
            if (suggestions.length > 0) {
                searchSuggestions.innerHTML = suggestions.map(suggestion => `
                    <div class="suggestion-item" data-type="${suggestion.type}" data-id="${suggestion.id}">
                        <i class="fas ${getSuggestionIcon(suggestion.type)}"></i>
                        ${suggestion.text}
                    </div>
                `).join('');
                searchSuggestions.style.display = 'block';
            } else {
                searchSuggestions.style.display = 'none';
            }
        } catch (error) {
            console.error('Error fetching suggestions:', error);
        }
    }, 300));

    // Handle suggestion click
    searchSuggestions.addEventListener('click', function(e) {
        const suggestionItem = e.target.closest('.suggestion-item');
        if (suggestionItem) {
            const type = suggestionItem.dataset.type;
            const id = suggestionItem.dataset.id;
            smartSearchInput.value = suggestionItem.textContent.trim();
            searchSuggestions.style.display = 'none';
            applySearch();
        }
    });

    // Toggle advanced search panel
    advancedSearchToggle.addEventListener('click', function() {
        advancedSearchPanel.style.display = 
            advancedSearchPanel.style.display === 'none' ? 'block' : 'none';
    });

    // Apply filters
    document.getElementById('applyFilters').addEventListener('click', function() {
        const filters = {
            organization: document.getElementById('orgFilter').value,
            dateFrom: document.getElementById('dateFrom').value,
            dateTo: document.getElementById('dateTo').value,
            department: document.getElementById('departmentFilter').value,
            employee: document.getElementById('employeeFilter').value
        };
        
        updateActiveFilters(filters);
        applySearch();
    });

    // Reset filters
    document.getElementById('resetFilters').addEventListener('click', function() {
        document.querySelectorAll('#advancedSearchPanel input, #advancedSearchPanel select')
            .forEach(input => input.value = '');
        activeFilters.innerHTML = '';
        applySearch();
    });

    // Save filter preset
    document.getElementById('saveFilterPreset').addEventListener('click', function() {
        const presetName = prompt('Nhập tên cho bộ lọc này:');
        if (presetName) {
            const filters = {
                name: presetName,
                organization: document.getElementById('orgFilter').value,
                dateFrom: document.getElementById('dateFrom').value,
                dateTo: document.getElementById('dateTo').value,
                department: document.getElementById('departmentFilter').value,
                employee: document.getElementById('employeeFilter').value
            };
            saveFilterPreset(filters);
        }
    });

    // Update active filters display
    function updateActiveFilters(filters) {
        activeFilters.innerHTML = '';
        Object.entries(filters).forEach(([key, value]) => {
            if (value) {
                const filterTag = document.createElement('div');
                filterTag.className = 'filter-tag';
                filterTag.innerHTML = `
                    <span>${getFilterLabel(key)}: ${value}</span>
                    <span class="remove" data-filter="${key}">&times;</span>
                `;
                activeFilters.appendChild(filterTag);
            }
        });

        // Add remove filter functionality
        document.querySelectorAll('.filter-tag .remove').forEach(removeBtn => {
            removeBtn.addEventListener('click', function() {
                const filterKey = this.dataset.filter;
                document.getElementById(`${filterKey}Filter`).value = '';
                this.parentElement.remove();
                applySearch();
            });
        });
    }

    // Helper function to get filter labels
    function getFilterLabel(key) {
        const labels = {
            organization: 'Tổ chức',
            dateFrom: 'Từ ngày',
            dateTo: 'Đến ngày',
            department: 'Phòng ban',
            employee: 'Nhân viên'
        };
        return labels[key] || key;
    }

    // Helper function to get suggestion icons
    function getSuggestionIcon(type) {
        const icons = {
            degree: 'fa-graduation-cap',
            certificate: 'fa-certificate',
            employee: 'fa-user',
            organization: 'fa-building'
        };
        return icons[type] || 'fa-search';
    }

    // Save filter preset to localStorage
    function saveFilterPreset(filters) {
        const presets = JSON.parse(localStorage.getItem('degreeFilterPresets') || '[]');
        presets.push(filters);
        localStorage.setItem('degreeFilterPresets', JSON.stringify(presets));
        showToast('Đã lưu bộ lọc thành công!', 'success');
    }

    // Apply search with all active filters
    function applySearch() {
        const searchParams = new URLSearchParams();
        
        // Add smart search query
        if (smartSearchInput.value) {
            searchParams.append('q', smartSearchInput.value);
        }

        // Add quick filters
        const typeFilter = document.getElementById('typeFilter').value;
        const statusFilter = document.getElementById('statusFilter').value;
        const dateFilter = document.getElementById('dateFilter').value;

        if (typeFilter) searchParams.append('type', typeFilter);
        if (statusFilter) searchParams.append('status', statusFilter);
        if (dateFilter) searchParams.append('date', dateFilter);

        // Add advanced filters
        const advancedFilters = {
            organization: document.getElementById('orgFilter').value,
            dateFrom: document.getElementById('dateFrom').value,
            dateTo: document.getElementById('dateTo').value,
            department: document.getElementById('departmentFilter').value,
            employee: document.getElementById('employeeFilter').value
        };

        Object.entries(advancedFilters).forEach(([key, value]) => {
            if (value) searchParams.append(key, value);
        });

        // Fetch and update results
        fetchDegrees(searchParams.toString());
    }

    // Fetch degrees with search parameters
    async function fetchDegrees(searchParams) {
        try {
            const response = await fetch(`/api/degrees/search?${searchParams}`);
            const data = await response.json();
            updateDegreesTable(data);
        } catch (error) {
            console.error('Error fetching degrees:', error);
            showToast('Có lỗi xảy ra khi tìm kiếm', 'error');
        }
    }

    // Show toast notification
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        document.querySelector('.toast-container').appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
}); 