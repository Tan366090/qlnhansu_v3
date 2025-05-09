// Add required libraries
document.head.innerHTML += `
    <script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>
    <style>
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            transition: opacity 0.3s ease;
        }

        #degreeModal {
            z-index: 1050;
        }

        #degreeModal .modal-dialog {
            margin: 1.75rem auto;
            max-width: 800px;
        }

        #degreeModal .modal-content {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            border: none;
            border-radius: 8px;
            background-color: #fff;
        }

        #degreeModal .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            border-radius: 8px 8px 0 0;
            padding: 1rem;
        }

        #degreeModal .modal-body {
            padding: 1.5rem;
        }

        #degreeModal .modal-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            border-radius: 0 0 8px 8px;
            padding: 1rem;
        }

        .modal-backdrop {
            z-index: 1039;
        }

        .modal-backdrop.show {
            opacity: 0;
        }
    </style>
`;

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
        this.infoModal = new bootstrap.Modal(document.getElementById('degreeInfoModal'), {
            backdrop: 'static',
            keyboard: false
        });
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
        
        // Add event listeners for modal
        document.getElementById('degreeInfoModal').addEventListener('shown.bs.modal', () => {
            console.log('Info modal shown');
        });
        
        document.getElementById('degreeInfoModal').addEventListener('hidden.bs.modal', () => {
            console.log('Info modal hidden');
        });
    }

    initializeEventListeners() {
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
        // Initialize Bootstrap modal
        this.modal = new bootstrap.Modal(document.getElementById('degreeModal'));
        
        // Form submit handler
        const form = document.getElementById('degreeForm');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmit();
            });
        }

        // File upload handler
        const attachmentInput = document.getElementById('attachment');
        if (attachmentInput) {
            attachmentInput.onchange = (e) => this.handleFileUpload(e);
        }

        // Handle degree type change
        const degreeTypeSelect = document.getElementById('degreeType');
        if (degreeTypeSelect) {
            degreeTypeSelect.addEventListener('change', () => {
                const isCertificate = degreeTypeSelect.value === 'certificate';
                document.querySelectorAll('.certificate-fields').forEach(el => {
                    el.style.display = isCertificate ? 'block' : 'none';
                });
                document.querySelectorAll('.degree-fields').forEach(el => {
                    el.style.display = isCertificate ? 'none' : 'block';
                });
            });
        }
    }

    async loadData() {
        try {
            // Show loading with initial message
            this.showLoading('Đang tải dữ liệu...');

            // Load dashboard stats
            const stats = await this.fetchData('dashboard_stats');
            this.updateDashboardStats(stats);

            // Lấy tổng số chứng chỉ đã hết hạn từ API list (chỉ lấy certificates)
            const expiredData = await this.fetchData('list', { status: 'expired', type: 'certificate', limit: 1 });
            const expiredCountElem = document.getElementById('expiredCertificates');
            if (expiredCountElem && expiredData && typeof expiredData.total !== 'undefined') {
                expiredCountElem.textContent = expiredData.total;
            }

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

            // Prepare list parameters
            const listParams = {
                page: this.currentPage,
                limit: this.limit
            };

            // Only add non-empty parameters
            if (this.search) listParams.search = this.search;
            if (this.type) listParams.type = this.type;
            if (this.status) listParams.status = this.status;
            if (this.date) listParams.date = this.date;
            if (this.organization) listParams.organization = this.organization;
            if (this.dateFrom) listParams.dateFrom = this.dateFrom;
            if (this.dateTo) listParams.dateTo = this.dateTo;
            if (this.department) listParams.department = this.department;
            if (this.employee) listParams.employee = this.employee;
            if (this.sortColumn) {
                listParams.sortColumn = this.sortColumn;
                listParams.sortDirection = this.sortDirection;
            }

            // Load main list with all filters
            const listData = await this.fetchData('list', listParams);
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
            const queryParams = new URLSearchParams();
            queryParams.append('action', action);

            // Handle different types of params
            if (typeof params === 'string') {
                // If params is a JSON string, parse it
                try {
                    const parsedParams = JSON.parse(params);
                    Object.entries(parsedParams).forEach(([key, value]) => {
                        if (value !== null && value !== undefined) {
                            queryParams.append(key, value);
                        }
                    });
                } catch (e) {
                    console.error('Error parsing params string:', e);
                }
            } else if (typeof params === 'object') {
                // If params is an object, append each key-value pair
                Object.entries(params).forEach(([key, value]) => {
                    if (value !== null && value !== undefined) {
                        queryParams.append(key, value);
                    }
                });
            }

            console.log('Fetching data for action:', action);
            console.log('With params:', Object.fromEntries(queryParams));

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
            
            // Define all possible stat elements and their corresponding data keys
            const statElements = {
                'totalDegrees': 'total_degrees',
                'totalCertificates': 'total_certificates',
                'expiringCertificates': 'expiring_certificates',
                'totalCourses': 'total_courses',
                'activeRegistrations': 'active_registrations'
            };

            // Update each stat only if both the element exists and the data is available
            Object.entries(statElements).forEach(([elementId, dataKey]) => {
                const element = document.getElementById(elementId);
                if (element && stats[dataKey] !== undefined) {
                    element.textContent = stats[dataKey] || '0';
                }
            });

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
            // Log expiry_date and type for debugging
            console.log('Row:', {
                id: item.id,
                type: item.type,
                expiry_date: item.expiry_date
            });
            let expiryDisplay = 'Không thời hạn';
            if (item.type === 'certificate' && item.expiry_date && item.expiry_date !== 'null' && item.expiry_date !== '') {
                expiryDisplay = this.formatDate(item.expiry_date);
            }
            // Lưu dữ liệu gốc vào biến toàn cục để dùng lại khi sửa
            if (!window.degreesRowData) window.degreesRowData = {};
            window.degreesRowData[item.id] = item;
            
            const row = `
                <tr data-id="${item.id}" data-employee-id="${item.employee_id}" data-type="${item.type}">
                    <td>${(this.currentPage - 1) * this.limit + index + 1}</td>
                    <td>${item.type === 'degree' ? 'Bằng cấp' : 'Chứng chỉ'}</td>
                    <td>${item.name || item.degree_name || ''}</td>
                    <td>${item.employee_name || ''}</td>
                    <td>${item.organization || item.issuing_organization || item.institution || ''}</td>
                    <td>${this.formatDate(item.issue_date)}</td>
                    <td>${expiryDisplay}</td>
                    <td>
                        <span class="badge ${this.getStatusBadgeClass(item.status)}">
                            ${this.getStatusText(item.status)}
                        </span>
                    </td>
                    <td>
                        ${(item.attachment_url || item.file_url) ? 
                            `<a href="${item.attachment_url || item.file_url}" target="_blank" class="btn btn-sm btn-outline-primary">
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
                            <button class="btn btn-edit">
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
        // Kiểm tra nếu là null hoặc undefined
        if (dateString === null || dateString === undefined) {
            return 'Không thời hạn';
        }
        
        // Kiểm tra nếu là chuỗi rỗng
        if (dateString === '') {
            return 'Không thời hạn';
        }

        // Thử parse ngày
        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
            console.log('Invalid date:', dateString);
            return 'Không thời hạn';
        }

        // Format thành dd/mm/yyyy
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
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

    showAddModal() {
        // Reset form
        const form = document.getElementById('degreeForm');
        if (form) form.reset();
        
        // Reset modal title and fields
        document.getElementById('modalTitle').textContent = 'Thêm bằng cấp/chứng chỉ mới';
        document.getElementById('employeeIdModal').value = '';
        document.getElementById('employeeNameModal').value = '';
        document.getElementById('modalEmployeeDegreesList').innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Vui lòng nhập mã nhân viên để xem thông tin
            </div>
        `;

        // Show/hide fields based on type
        const degreeType = document.getElementById('degreeType');
        const degreeFields = document.querySelectorAll('.degree-fields');
        const certificateFields = document.querySelectorAll('.certificate-fields');

        function toggleFields() {
            if (degreeType.value === 'degree') {
                degreeFields.forEach(field => field.style.display = 'block');
                certificateFields.forEach(field => field.style.display = 'none');
            } else {
                degreeFields.forEach(field => field.style.display = 'none');
                certificateFields.forEach(field => field.style.display = 'block');
            }
        }

        degreeType.addEventListener('change', toggleFields);
        toggleFields(); // Initial toggle

        // Show modal
        const degreeModal = new bootstrap.Modal(document.getElementById('degreeModal'));
        degreeModal.show();
    }

    async handleFormSubmit() {
        try {
            const form = document.getElementById('degreeForm');
            if (!form) {
                throw new Error('Form not found');
            }

            // Get form data
            const formData = new FormData();

            // Get and validate employee ID
            const employeeId = document.getElementById('employeeIdModal').value;
            if (!employeeId) {
                throw new Error('Vui lòng chọn nhân viên');
            }
            formData.append('employee_id', employeeId);

            // Get other form fields
            const type = document.getElementById('degreeType').value;
            if (!type) {
                throw new Error('Vui lòng chọn loại bằng cấp/chứng chỉ');
            }
            formData.append('type', type);

            const name = document.getElementById('degreeName').value;
            if (!name) {
                throw new Error('Vui lòng nhập tên bằng cấp/chứng chỉ');
            }
            formData.append('name', name);

            const organization = document.getElementById('organization').value;
            if (!organization) {
                throw new Error('Vui lòng nhập tổ chức cấp');
            }
            formData.append('organization', organization);

            const issueDate = document.getElementById('issueDate').value;
            if (!issueDate) {
                throw new Error('Vui lòng nhập ngày cấp');
            }
            formData.append('issue_date', issueDate);

            // Add type-specific fields
            if (type === 'degree') {
                const major = document.getElementById('major').value;
                if (!major) {
                    throw new Error('Vui lòng nhập chuyên ngành');
                }
                formData.append('major', major);

                const gpa = document.getElementById('gpa').value;
                if (!gpa) {
                    throw new Error('Vui lòng nhập điểm GPA');
                }
                formData.append('gpa', gpa);
            } else {
                const expiryDate = document.getElementById('expiryDate').value;
                if (!expiryDate) {
                    throw new Error('Vui lòng nhập ngày hết hạn');
                }
                formData.append('expiry_date', expiryDate);

                const credentialId = document.getElementById('credentialId').value;
                if (!credentialId) {
                    throw new Error('Vui lòng nhập mã chứng chỉ');
                }
                formData.append('credential_id', credentialId);
            }

            // Handle file attachment
            const attachment = document.getElementById('attachment').files[0];
            if (attachment) {
                if (!this.validateFile(attachment)) {
                    throw new Error('File không hợp lệ. Chỉ chấp nhận file PDF, JPG, JPEG, PNG và kích thước tối đa 5MB');
                }
                formData.append('attachment', attachment);
            }

            // Add action parameter
            formData.append('action', 'create');

            // Show loading state
            this.showLoading('Đang lưu thông tin...');

            // Log form data for debugging
            console.log('Form data being sent:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }

            // Send request
            const apiUrl = '/qlnhansu_V3/backend/src/api/degrees.php?action=create';
            console.log('Sending request to:', apiUrl);

            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    body: formData
                });

                // Log response status and headers
                console.log('Response status:', response.status);
                console.log('Response headers:', Object.fromEntries(response.headers.entries()));

                // Get response text first for debugging
                const responseText = await response.text();
                console.log('Raw response:', responseText);

                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    console.error('Error parsing JSON response:', e);
                    throw new Error('Invalid JSON response from server');
                }

                console.log('Parsed response data:', data);

                if (!response.ok) {
                    throw new Error(data.message || `Server error: ${response.status}`);
                }
                
                if (data.success) {
                    this.showToast('Lưu thông tin thành công', 'success');
                    this.hideModal();
                    await this.loadData(); // Refresh the table
                } else {
                    throw new Error(data.message || 'Lỗi khi lưu thông tin');
                }

            } catch (fetchError) {
                console.error('Fetch error:', fetchError);
                throw new Error(`Network error: ${fetchError.message}`);
            }

        } catch (error) {
            console.error('Error in form submission:', error);
            console.error('Error stack:', error.stack);
            this.showToast(error.message || 'Lỗi khi lưu thông tin', 'error');
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
        this.modal.hide();
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

        // Show loading immediately
        const loadingOverlay = this.showLoading('Đang xóa...');
        
        try {
            // Add timeout to the fetch request
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout

            const response = await fetch(`/qlnhansu_V3/backend/src/public/admin/api/degrees.php?action=delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ id, type }),
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                this.showToast('Xóa thành công', 'success');
                // Use requestAnimationFrame to defer the data reload
                requestAnimationFrame(() => {
                    this.loadData();
                });
            } else {
                throw new Error(data.message || 'Lỗi không xác định');
            }
        } catch (error) {
            if (error.name === 'AbortError') {
                this.showToast('Yêu cầu xóa bị timeout. Vui lòng thử lại.', 'error');
            } else {
                this.showToast('Lỗi khi xóa: ' + error.message, 'error');
            }
            console.error('Delete error:', error);
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
            const ws = XLSX.utils.json_to_sheet(data.data.map((item, idx) => ({
                'STT': idx + 1,
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

        // Mapping giá trị sang tiếng Việt, icon và mô tả thân thiện
        const typeMap = { degree: 'Bằng cấp', certificate: 'Chứng chỉ' };
        const statusMap = { valid: 'Còn hiệu lực', expired: 'Hết hạn', expiring: 'Sắp hết hạn', completed: 'Đã hoàn thành', registered: 'Đã đăng ký', attended: 'Đã tham gia', failed: 'Không đạt', cancelled: 'Đã hủy' };
        const dateMap = { today: 'Hôm nay', week: 'Tuần này', month: 'Tháng này', year: 'Năm nay' };
        const icons = {
            type: '<i class="fas fa-graduation-cap"></i>',
            status: '<i class="fas fa-check-circle"></i>',
            date: '<i class="fas fa-calendar-alt"></i>',
            organization: '<i class="fas fa-building"></i>',
            dateFrom: '<i class="fas fa-calendar-day"></i>',
            dateTo: '<i class="fas fa-calendar-day"></i>',
            department: '<i class="fas fa-sitemap"></i>',
            employee: '<i class="fas fa-user"></i>'
        };

        Object.entries(filters).forEach(([key, value]) => {
            if (value) {
                let displayValue = value;
                if (key === 'type') displayValue = typeMap[value] || value;
                if (key === 'status') displayValue = statusMap[value] || value;
                if (key === 'date') displayValue = dateMap[value] || value;
                if (key === 'dateFrom' || key === 'dateTo') displayValue = this.formatDate(value);
                // organization, department, employee giữ nguyên

                const filterTag = document.createElement('div');
                filterTag.className = 'filter-tag ' + key;
                filterTag.innerHTML = `
                    ${icons[key] || ''} <span>${this.getFilterLabel(key)}: <b>${displayValue}</b></span>
                    <span class="remove" data-filter="${key}" title="Bỏ lọc">&times;</span>
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

    getCourseStatusText(status) {
        const statusMap = {
            'registered': 'Đã đăng ký',
            'attended': 'Đã tham gia',
            'completed': 'Đã hoàn thành',
            'failed': 'Không đạt',
            'cancelled': 'Đã hủy'
        };
        return statusMap[status] || status;
    }

    formatDateForInput(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return '';
        
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        
        return `${year}-${month}-${day}`;
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

    // Helper function: Convert dd/mm/yyyy to yyyy-MM-dd
    function ddmmyyyyToYyyymmdd(str) {
        if (!str || str === 'N/A') return '';
        const [day, month, year] = str.split('/');
        if (!day || !month || !year) return '';
        return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
    }

    $(document).on('click', '.btn-edit', function(event) {
        event.preventDefault();
        console.log('Edit clicked', event);
        const $row = $(this).closest('tr');
        if ($row.hasClass('editing')) return;
        $row.addClass('editing');

        // Lấy dữ liệu hiện tại
        const cells = $row.children('td');
        const type = cells.eq(1).text().trim() === 'Bằng cấp' ? 'degree' : 'certificate';
        const name = cells.eq(2).text().trim();
        const employeeName = cells.eq(3).text().trim();
        const organization = cells.eq(4).text().trim();

        // Lấy ngày cấp từ bảng (dd/mm/yyyy) và chuyển sang yyyy-MM-dd cho input
        const issueDateText = cells.eq(5).text().trim();
        const issueDate = issueDateText === 'Không thời hạn' ? '' : ddmmyyyyToYyyymmdd(issueDateText);

        // Lấy ngày hết hạn từ bảng (dd/mm/yyyy) và chuyển sang yyyy-MM-dd cho input
        const expiryDateText = cells.eq(6).text().trim();
        console.log('Expiry date text from table:', expiryDateText);
        const expiryDate = expiryDateText === 'Không thời hạn' ? '' : ddmmyyyyToYyyymmdd(expiryDateText);
        console.log('Converted expiry date:', expiryDate);

        const status = cells.eq(7).find('.badge').text().trim();
        const credentialId = cells.eq(8).text().trim();

        // Lưu employee_id vào data attribute của row
        let employeeId = $row.data('employee-id');
        if (!employeeId || employeeId === 'undefined' || employeeId === undefined) {
            // Lấy lại từ dữ liệu gốc
            const rowId = $row.data('id');
            if (window.degreesRowData && window.degreesRowData[rowId]) {
                employeeId = window.degreesRowData[rowId].employee_id || '';
            } else {
                employeeId = '';
            }
        }
        console.log('Employee ID from row (fixed):', employeeId);

        // Loại select
        const typeSelect = `<select class='form-select form-select-sm inline-type'><option value='degree' ${type==='degree'?'selected':''}>Bằng cấp</option><option value='certificate' ${type==='certificate'?'selected':''}>Chứng chỉ</option></select>`;
        // Tên
        const nameInput = `<input type='text' class='form-control form-control-sm inline-name' value="${name}">`;
        // Nhân viên (autocomplete input, tạm thời là text)
        const employeeInput = `<input type='text' class='form-control form-control-sm inline-employee' value="${employeeName}" readonly data-employee-id="${employeeId}">`;
        // Tổ chức cấp
        const orgInput = `<input type='text' class='form-control form-control-sm inline-org' value="${organization}">`;
        // Ngày cấp
        const issueInput = `<input type='date' class='form-control form-control-sm inline-issue' value="${issueDate}">`;
        // Ngày hết hạn
        const expiryInput = `<input type='date' class='form-control form-control-sm inline-expiry' value="${expiryDate}">`;
        // Mã chứng chỉ
        const credInput = `<input type='text' class='form-control form-control-sm inline-cred' value="${credentialId}">`;
        // Trạng thái (không chỉnh sửa trực tiếp, chỉ hiển thị)
        const statusHtml = cells.eq(7).html();
        // File đính kèm giữ nguyên
        const fileHtml = cells.eq(8).html();

        // Thay thế các ô bằng input/select
        cells.eq(1).html(typeSelect);
        cells.eq(2).html(nameInput);
        cells.eq(3).html(employeeInput);
        cells.eq(4).html(orgInput);
        cells.eq(5).html(issueInput);
        cells.eq(6).html(expiryInput);
        cells.eq(7).html(statusHtml);
        cells.eq(8).html(fileHtml);

        // Thay nút hành động
        const saveBtn = `<button class='btn btn-success btn-save' title='Lưu'><i class='fas fa-save'></i></button>`;
        const cancelBtn = `<button class='btn btn-secondary btn-cancel' title='Hủy'><i class='fas fa-times'></i></button>`;
        cells.eq(9).html(saveBtn + ' ' + cancelBtn);
    });

    $(document).on('click', '.btn-save', async function(event) {
        event.preventDefault();
        console.log('Save clicked', event);
        
        const $row = $(this).closest('tr');
        const id = $row.data('id');
        const type = $row.find('.inline-type').val();
        
        // Lấy employee_id từ input employee (data-employee-id)
        const employeeInput = $row.find('.inline-employee');
        const employeeId = employeeInput.data('employee-id');
        console.log('Employee ID on save:', employeeId, 'Input:', employeeInput[0]);
        
        // Kiểm tra employee_id
        if (!employeeId) {
            degreesManager.showToast('Không thể sửa chứng chỉ này vì thiếu thông tin nhân viên. Vui lòng liên hệ quản trị viên để bổ sung dữ liệu.', 'error');
            return;
        }
        
        // Validate required fields
        if (!id || !type) {
            degreesManager.showToast('Lỗi: Thiếu thông tin cần thiết (ID hoặc loại)', 'error');
            return;
        }

        // Validate employee_id is a valid number
        const employeeIdNum = parseInt(employeeId);
        if (isNaN(employeeIdNum)) {
            degreesManager.showToast('Lỗi: ID nhân viên không hợp lệ', 'error');
            return;
        }

        const name = $row.find('.inline-name').val();
        const organization = $row.find('.inline-org').val();
        const issueDate = $row.find('.inline-issue').val();
        const expiryDate = $row.find('.inline-expiry').val(); // yyyy-MM-dd hoặc ''
        console.log('Expiry date from input:', expiryDate);

        const credentialId = $row.find('.inline-cred').val();

        // Validate other required fields
        if (!name || !organization || !issueDate) {
            degreesManager.showToast('Vui lòng điền đầy đủ thông tin bắt buộc', 'error');
            return;
        }

        // Log giá trị ngày hết hạn trước khi gửi
        console.log('Expiry date to save:', expiryDate);

        // Prepare request data
        const requestData = {
            id: parseInt(id),
            type: type,
            name: name,
            employee_id: employeeIdNum,
            organization: organization,
            issue_date: issueDate,
            expiry_date: expiryDate || null, // Nếu rỗng sẽ là null
            credential_id: credentialId || null
        };

        console.log('Sending request data:', requestData);

        try {
            const response = await fetch(`/qlnhansu_V3/backend/src/public/admin/api/degrees.php?action=update`, {
                method: 'POST',
                body: JSON.stringify(requestData),
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            
            const result = await response.json();
            console.log('API result:', result);
            
            if (result.success) {
                degreesManager.showToast('Cập nhật thành công', 'success');
                degreesManager.loadData(); // loadData sẽ render lại bảng, formatDate sẽ hiển thị dd/mm/yyyy
            } else {
                throw new Error(result.error || result.message || 'Lỗi không xác định');
            }
        } catch (e) {
            console.error('Error saving:', e);
            degreesManager.showToast('Lỗi khi lưu: ' + e.message, 'error');
        }
    });

    $(document).on('click', '.btn-cancel', function(event) {
        event.preventDefault();
        console.log('Cancel clicked', event);
        degreesManager.loadData();
    });
});

// Add global formatDate function if not already defined
if (typeof window.formatDate !== 'function') {
    window.formatDate = function(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN');
    };
} 