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
        
        // Update loading state management
        this.isLoading = false;
        this.loadingOverlay = null;
        this.loadingQueue = [];
        
        this.initializeEventListeners();
        this.initializeModalEvents();
        this.loadData();
        
        // Initialize additional features
        this.initializeSessionCheck();
        this.initializeResponsiveHandlers();
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
        try {
            // Show loading with initial message
            this.showLoading('Đang tải dữ liệu...');

            // Load dashboard stats
            const stats = await this.fetchData('dashboard_stats');
            this.updateDashboardStats(stats);

            // Update loading message
            this.showLoading('Đang tải biểu đồ...');

            // Load degree distribution
            const degreeDist = await this.fetchData('degree_distribution');
            this.updateDegreeChart(degreeDist);

            // Load certificate distribution
            const certDist = await this.fetchData('certificate_distribution');
            this.updateCertificateChart(certDist);

            // Update loading message
            this.showLoading('Đang tải danh sách...');

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
            // Hide loading if no more requests in queue
            if (this.loadingQueue.length === 0) {
                this.hideLoading();
            }
        }
    }

    async fetchData(action, params = {}) {
        try {
            const queryParams = new URLSearchParams({
                action: action,
                ...params
            });

            console.log('Fetching data for action:', action);
            console.log('With params:', params);

            const response = await fetch(`/qlnhansu_V3/backend/src/public/admin/api/degrees.php?${queryParams}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Received data:', data);
            
            // Check if response has error property
            if (data.error) {
                throw new Error(data.error);
            }

            // For dashboard_stats and other direct data responses
            if (action === 'dashboard_stats' || action === 'degree_distribution' || action === 'certificate_distribution') {
                console.log('Returning direct data for action:', action);
                return data.data || data;
            }

            // For list and other responses that should have success property
            if (!data.success && data.message) {
                throw new Error(data.message);
            }

            console.log('Returning processed data:', data.data || data);
            return data.data || data;
        } catch (error) {
            console.error('Error in fetchData:', error);
            this.showToast(error.message || 'Có lỗi xảy ra khi tải dữ liệu', 'error');
            throw error;
        }
    }

    updateDashboardStats(stats) {
        try {
            console.log('Updating dashboard stats with data:', stats);
            
            // Log all available elements
            console.log('Available elements:', {
                totalDegrees: document.getElementById('totalDegrees'),
                totalCertificates: document.getElementById('totalCertificates'),
                expiringCertificates: document.getElementById('expiringCertificates'),
                totalCourses: document.getElementById('totalCourses'),
                activeRegistrations: document.getElementById('activeRegistrations')
            });
            
            // Update total degrees
            const totalDegreesElement = document.getElementById('totalDegrees');
            if (totalDegreesElement) {
                const value = stats.total_degrees || '0';
                console.log('Setting totalDegrees to:', value);
                totalDegreesElement.textContent = value;
            } else {
                console.warn('Element totalDegrees not found');
            }

            // Update total certificates
            const totalCertificatesElement = document.getElementById('totalCertificates');
            if (totalCertificatesElement) {
                const value = stats.total_certificates || '0';
                console.log('Setting totalCertificates to:', value);
                totalCertificatesElement.textContent = value;
            } else {
                console.warn('Element totalCertificates not found');
            }

            // Update expiring certificates
            const expiringCertificatesElement = document.getElementById('expiringCertificates');
            if (expiringCertificatesElement) {
                const value = stats.expiring_certificates || '0';
                console.log('Setting expiringCertificates to:', value);
                expiringCertificatesElement.textContent = value;
            } else {
                console.warn('Element expiringCertificates not found');
            }

            // Update total courses if element exists
            const totalCoursesElement = document.getElementById('totalCourses');
            if (totalCoursesElement) {
                const value = stats.total_courses || '0';
                console.log('Setting totalCourses to:', value);
                totalCoursesElement.textContent = value;
            } else {
                console.warn('Element totalCourses not found');
            }

            // Update active registrations if element exists
            const activeRegistrationsElement = document.getElementById('activeRegistrations');
            if (activeRegistrationsElement) {
                const value = stats.active_registrations || '0';
                console.log('Setting activeRegistrations to:', value);
                activeRegistrationsElement.textContent = value;
            } else {
                console.warn('Element activeRegistrations not found');
            }
        } catch (error) {
            console.error('Error updating dashboard stats:', error);
            this.showToast('Lỗi khi cập nhật thống kê: ' + error.message, 'error');
        }
    }

    updateDegreeChart(data) {
        const ctx = document.getElementById('degreeTypeChart').getContext('2d');
        
        if (this.degreeTypeChart) {
            this.degreeTypeChart.destroy();
        }

        // Use a single gray color for all bars
        const grayColor = '#90A4AE';

        this.degreeTypeChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(item => item.degree_name),
                datasets: [{
                    label: 'Số lượng',
                    data: data.map(item => item.count),
                    backgroundColor: grayColor,
                    borderColor: grayColor,
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

        // Vibrant color palette
        const pieColors = [
            '#FFA726', // Orange
            '#FFD600', // Yellow
            '#90A4AE', // Gray
            '#66BB6A', // Green
            '#42A5F5', // Blue
            '#EF5350', // Red
            '#AB47BC', // Purple
            '#26C6DA', // Teal
            '#FF7043', // Deep Orange
            '#8D6E63', // Brown
            '#FBC02D', // Bright Yellow
            '#29B6F6', // Light Blue
            '#7E57C2', // Deep Purple
            '#EC407A', // Pink
            '#BDBDBD'  // Light Gray
        ];

        this.certificateOrgChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data.map(item => item.issuing_organization),
                datasets: [{
                    data: data.map(item => item.count),
                    backgroundColor: data.map((_, i) => pieColors[i % pieColors.length]),
                    borderColor: '#fff',
                    borderWidth: 2
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
        // If already showing loading, just update message
        if (this.loadingOverlay) {
            const loadingText = this.loadingOverlay.querySelector('.loading-text');
            if (loadingText) {
                loadingText.textContent = message;
            }
            return this.loadingOverlay;
        }

        // Create new loading overlay
        this.loadingOverlay = document.createElement('div');
        this.loadingOverlay.className = 'loading-overlay';
        this.loadingOverlay.innerHTML = `
            <div class="loader"></div>
            <div class="loading-text">${message}</div>
        `;
        document.body.appendChild(this.loadingOverlay);
        return this.loadingOverlay;
    }

    hideLoading() {
        if (this.loadingOverlay && this.loadingOverlay.parentElement) {
            this.loadingOverlay.remove();
            this.loadingOverlay = null;
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
        // Validate required fields
        const requiredFields = {
            'degreeType': 'Loại',
            'employeeId': 'Nhân viên',
            'degreeName': 'Tên bằng cấp/chứng chỉ',
            'organization': 'Tổ chức cấp',
            'issueDate': 'Ngày cấp'
        };

        for (const [field, label] of Object.entries(requiredFields)) {
            const element = document.getElementById(field);
            if (!element.value.trim()) {
                this.showToast(`Vui lòng nhập ${label}`, 'error');
                element.focus();
                return;
            }
        }

        // Validate file if exists
        const fileInput = document.getElementById('attachment');
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            if (!this.validateFile(file)) {
                return;
            }
        }

        const loadingOverlay = this.showLoading('Đang lưu thông tin...');
        try {
            const formData = new FormData(this.form);
            const data = {
                type: document.getElementById('degreeType').value,
                employee_id: formData.get('employeeId'),
                name: formData.get('degreeName'),
                organization: formData.get('organization'),
                issue_date: formData.get('issueDate'),
                major: formData.get('major'),
                gpa: formData.get('gpa'),
                attachment_url: formData.get('attachment')
            };

            if (data.type === 'certificate') {
                data.expiry_date = formData.get('expiryDate');
                data.credential_id = formData.get('credentialId');
            }

            if (this.currentEditId) {
                data.id = this.currentEditId;
                data.edit_type = this.currentEditType;
            }

            const response = await this.fetchData(
                this.currentEditId ? 'update' : 'create',
                JSON.stringify(data)
            );
            
            if (response.success) {
                this.showToast('Lưu thông tin thành công', 'success');
                this.hideModal();
                this.refreshData();
            } else {
                throw new Error(response.message || 'Lỗi không xác định');
            }
        } catch (error) {
            this.showToast('Lỗi khi lưu thông tin: ' + error.message, 'error');
        } finally {
            this.hideLoading();
        }
    }

    validateFile(file) {
        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            this.showToast('File không được vượt quá 5MB', 'error');
            return false;
        }

        // Validate file type
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            this.showToast('Chỉ chấp nhận file PDF, JPEG hoặc PNG', 'error');
            return false;
        }

        return true;
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

        const loadingOverlay = this.showLoading('Đang xóa...');
        try {
            const response = await this.fetchData('delete', { id, type });
            if (response.success) {
                this.showToast('Xóa thành công', 'success');
                this.loadData();
            } else {
                throw new Error(response.message || 'Lỗi không xác định');
            }
        } catch (error) {
            this.showToast('Lỗi khi xóa: ' + error.message, 'error');
        } finally {
            this.hideLoading();
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
        const loadingOverlay = this.showLoading('Đang xuất dữ liệu...');
        try {
            console.log('Starting export process...');
            
            // Get all data without pagination
            const response = await fetch(`/qlnhansu_V3/backend/src/public/admin/api/degrees.php?action=export`);
            const data = await response.json();
            
            console.log('Export response:', data);

            if (!data.success) {
                throw new Error(data.error || 'Không thể xuất dữ liệu');
            }

            if (!data.data || !Array.isArray(data.data) || data.data.length === 0) {
                throw new Error('Không có dữ liệu để xuất');
            }

            console.log('Processing data for Excel:', data.data);

            // Create worksheet with Vietnamese headers
            const ws = XLSX.utils.json_to_sheet(data.data.map(item => ({
                'STT': item.id,
                'Loại': item.type === 'degree' ? 'Bằng cấp' : 'Chứng chỉ',
                'Tên': item.name || '',
                'Nhân viên': item.employee_name || '',
                'Tổ chức cấp': item.organization || '',
                'Ngày cấp': this.formatDate(item.issue_date),
                'Ngày hết hạn': item.expiry_date ? this.formatDate(item.expiry_date) : 'N/A',
                'Trạng thái': this.getStatusText(item.status),
                'Mã chứng chỉ': item.credential_id || 'N/A'
            })));

            // Set column widths
            const wscols = [
                {wch: 5},  // STT
                {wch: 10}, // Loại
                {wch: 30}, // Tên
                {wch: 20}, // Nhân viên
                {wch: 25}, // Tổ chức cấp
                {wch: 12}, // Ngày cấp
                {wch: 12}, // Ngày hết hạn
                {wch: 15}, // Trạng thái
                {wch: 15}  // Mã chứng chỉ
            ];
            ws['!cols'] = wscols;

            // Create workbook
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Bằng cấp và chứng chỉ');

            // Generate Excel file with current date
            const fileName = `bang_cap_chung_chi_${new Date().toISOString().split('T')[0]}.xlsx`;
            
            // Use XLSX.writeFile to save the file
            XLSX.writeFile(wb, fileName);

            this.showToast('Xuất Excel thành công', 'success');
        } catch (error) {
            console.error('Export error:', error);
            this.showToast('Lỗi khi xuất Excel: ' + error.message, 'error');
        } finally {
            this.hideLoading();
        }
    }

    updateActiveFilters() {
        const activeFilters = document.getElementById('activeFilters');
        activeFilters.innerHTML = '';

        const filters = {
            type: this.type,
            status: this.status,
            date: this.date,
            organization: this.organization,
            dateFrom: this.dateFrom,
            dateTo: this.dateTo,
            department: this.department,
            employee: this.employee
        };

        Object.entries(filters).forEach(([key, value]) => {
            if (value) {
                const filterTag = document.createElement('div');
                filterTag.className = 'filter-tag';
                filterTag.innerHTML = `
                    <span>${this.getFilterLabel(key)}: ${value}</span>
                    <span class="remove" data-filter="${key}">&times;</span>
                `;
                activeFilters.appendChild(filterTag);
            }
        });

        // Add event listeners to remove buttons
        activeFilters.querySelectorAll('.remove').forEach(button => {
            button.addEventListener('click', () => {
                const filter = button.dataset.filter;
                this[filter] = '';
                if (filter === 'dateFrom' || filter === 'dateTo') {
                    document.getElementById(filter).value = '';
                } else if (filter === 'type' || filter === 'status' || filter === 'date') {
                    document.getElementById(`${filter}Filter`).value = '';
                } else {
                    document.getElementById(filter === 'organization' ? 'orgFilter' : `${filter}Filter`).value = '';
                }
                this.currentPage = 1;
                this.loadData();
            });
        });
    }

    getFilterLabel(key) {
        const labels = {
            type: 'Loại',
            status: 'Trạng thái',
            date: 'Thời gian',
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

    // Add new methods for improved functionality
    initializeSessionCheck() {
        // Check session every 5 minutes
        this.sessionCheckInterval = setInterval(() => this.checkSession(), 5 * 60 * 1000);
    }

    initializeResponsiveHandlers() {
        // Handle responsive layout changes
        window.addEventListener('resize', debounce(() => {
            this.updateResponsiveLayout();
        }, 250));
    }

    updateResponsiveLayout() {
        const isMobile = window.innerWidth <= 768;
        document.querySelector('.search-filter').classList.toggle('mobile-view', isMobile);
        document.querySelector('.quick-filters').classList.toggle('mobile-view', isMobile);
    }

    async checkSession() {
        try {
            const response = await fetch('../api/check_session.php');
            const data = await response.json();
            if (data.error === 'session_expired') {
                this.showToast('Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.', 'warning');
                setTimeout(() => {
                    window.location.href = '../login.php';
                }, 2000);
            }
        } catch (error) {
            console.error('Session check error:', error);
        }
    }

    refreshData() {
        this.currentPage = 1;
        this.loadData();
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
            searchParams.append('search', smartSearchInput.value);
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
            const response = await fetch(`/backend/src/public/admin/api/degrees.php?action=list&${searchParams}`);
            const data = await response.json();
            if (data.error) {
                showToast(data.error, 'error');
                return;
            }
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