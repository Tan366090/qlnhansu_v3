// Initialize variables
let currentPage = 1;
const perPage = 10;
let totalPages = 1;

// API endpoints
const LEAVES_API = '/qlnhansu_V3/backend/src/public/admin/api/leaves.php';
const STATISTICS_API = '/qlnhansu_V3/backend/src/public/admin/api/leaves.php?action=statistics';
const NOTIFICATIONS_API = `${api.API_BASE_URL}/notifications.php`;

// DOM Elements
const leaveTableBody = document.getElementById('leaveTableBody');
const pagination = document.getElementById('pagination');
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const leaveTypeFilter = document.getElementById('leaveTypeFilter');
const exportBtn = document.getElementById('exportBtn');
const addLeaveBtn = document.getElementById('addLeaveBtn');
const leaveModal = new bootstrap.Modal(document.getElementById('leaveModal'));
const leaveForm = document.getElementById('leaveForm');
const leaveInfoModal = new bootstrap.Modal(document.getElementById('leaveInfoModal'));

// Add loader styles
const loaderStyles = `
.loader-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    backdrop-filter: blur(3px);
}

.loader {
    animation: rotate 1s infinite;
    height: 50px;
    width: 50px;
}

.loader:before,
.loader:after {
    border-radius: 50%;
    content: "";
    display: block;
    height: 20px;
    width: 20px;
}

.loader:before {
    animation: ball1 1s infinite;
    background-color: #fff;
    box-shadow: 30px 0 0 #ff3d00;
    margin-bottom: 10px;
}

.loader:after {
    animation: ball2 1s infinite;
    background-color: #ff3d00;
    box-shadow: 30px 0 0 #fff;
}

@keyframes rotate {
    0% { transform: rotate(0deg) scale(0.8) }
    50% { transform: rotate(360deg) scale(1.2) }
    100% { transform: rotate(720deg) scale(0.8) }
}

@keyframes ball1 {
    0% {
        box-shadow: 30px 0 0 #ff3d00;
    }
    50% {
        box-shadow: 0 0 0 #ff3d00;
        margin-bottom: 0;
        transform: translate(15px, 15px);
    }
    100% {
        box-shadow: 30px 0 0 #ff3d00;
        margin-bottom: 10px;
    }
}

@keyframes ball2 {
    0% {
        box-shadow: 30px 0 0 #fff;
    }
    50% {
        box-shadow: 0 0 0 #fff;
        margin-top: -20px;
        transform: translate(15px, 15px);
    }
    100% {
        box-shadow: 30px 0 0 #fff;
        margin-top: 0;
    }
}
`;

// Add responsive styles
const responsiveStyles = `
/* Responsive styles */
@media (max-width: 1200px) {
    .container-fluid {
        padding: 15px;
    }
    
    .card {
        margin-bottom: 20px;
    }
    
    .table-responsive {
        overflow-x: auto;
    }
    
    .action-btns {
        flex-wrap: wrap;
        gap: 5px;
    }
    
    .icon-btn {
        padding: 5px;
    }
}

@media (max-width: 992px) {
    .statistics-row {
        flex-direction: column;
    }
    
    .stat-card {
        width: 100%;
        margin-bottom: 15px;
    }
    
    .filters-row {
        flex-direction: column;
    }
    
    .filter-group {
        width: 100%;
        margin-bottom: 10px;
    }
    
    .search-group {
        width: 100%;
    }
}

@media (max-width: 768px) {
    .table th, .table td {
        padding: 8px;
        font-size: 14px;
    }
    
    .modal-dialog {
        margin: 10px;
    }
    
    .modal-body {
        padding: 15px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .btn-group {
        flex-wrap: wrap;
    }
    
    .btn {
        margin-bottom: 5px;
    }
    
    .pagination {
        flex-wrap: wrap;
        justify-content: center;
        gap: 5px;
    }
    
    .page-item {
        margin: 2px;
    }
}

@media (max-width: 576px) {
    .card-header {
        flex-direction: column;
        gap: 10px;
    }
    
    .card-title {
        margin-bottom: 10px;
    }
    
    .btn-toolbar {
        width: 100%;
        justify-content: center;
    }
    
    .table-responsive {
        margin: 0 -15px;
    }
    
    .modal-header {
        flex-direction: column;
        text-align: center;
    }
    
    .modal-footer {
        flex-direction: column;
        gap: 10px;
    }
    
    .modal-footer .btn {
        width: 100%;
    }
    
    .toast-container {
        width: 100%;
        padding: 10px;
    }
    
    .toast {
        width: 100%;
    }
}

/* Common responsive styles */
.table-responsive {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card {
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

.btn {
    border-radius: 6px;
    transition: all 0.2s;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-control {
    border-radius: 6px;
}

.modal-content {
    border-radius: 12px;
}

/* Loading animation */
.loader-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loader {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Toast notifications */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

.toast {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    margin-bottom: 10px;
    min-width: 300px;
    max-width: 100%;
}

/* Table styles */
.table {
    width: 100%;
    margin-bottom: 0;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.table td {
    vertical-align: middle;
}

/* Status badges */
.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 500;
}

/* Action buttons */
.action-btns {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.icon-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    transition: all 0.2s;
    background: transparent !important;
    box-shadow: none !important;
    color: inherit;
    padding: 0;
}
.icon-btn:hover, .icon-btn:focus {
    background: rgba(0,0,0,0.05);
    color: #007bff;
}
.icon-btn.info-btn { color: #17a2b8; }
.icon-btn.edit-btn { color: #ffc107; }
.icon-btn.delete-btn { color: #dc3545; }

/* Form styles */
.form-group {
    margin-bottom: 1rem;
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.form-control:focus {
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

/* Modal styles */
.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

/* Pagination styles */
.pagination {
    margin: 20px 0;
    justify-content: center;
}

.page-item .page-link {
    border-radius: 4px;
    margin: 0 2px;
}

.page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}
`;
// Add styles to document
const styleSheet = document.createElement('style');
styleSheet.textContent = responsiveStyles;
document.head.appendChild(styleSheet);

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    showLoader(); // Show loader when page loads
    Promise.all([
        loadLeaves(),
        loadStatistics(),
        loadEmployees()
    ]).finally(() => {
        hideLoader();
    });
    setupEventListeners();

    // Load employees when modal is shown
    const leaveModalElement = document.getElementById('leaveModal');
    if (leaveModalElement) {
        leaveModalElement.addEventListener('show.bs.modal', function() {
            // Reload employees to ensure we have the latest data
            loadEmployees();
        });
    }
});

// Load employees for dropdown
async function loadEmployees() {
    try {
        const response = await fetch('/qlnhansu_V3/backend/src/api/employees.php?action=getAll');
        if (!response.ok) {
            throw new Error('Failed to load employees');
        }
        const data = await response.json();
        if (data.success) {
            const employeeSelect = document.getElementById('employeeId');
            employeeSelect.innerHTML = '<option value="">Chọn nhân viên</option>';
            data.data.forEach(employee => {
                const option = document.createElement('option');
                option.value = employee.id;
                option.textContent = `${employee.employee_code} - ${employee.name}`;
                option.dataset.employeeName = employee.name;
                employeeSelect.appendChild(option);
            });

            // Add event listener for employee selection
            employeeSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const employeeName = selectedOption.dataset.employeeName || '';
                document.getElementById('employeeName').value = employeeName;
            });
        } else {
            throw new Error(data.message || 'Failed to load employees');
        }
    } catch (error) {
        console.error('Error loading employees:', error);
        showToast('error', 'Không thể tải danh sách nhân viên');
    }
}

// Add loader functions
function showLoader() {
    // Add styles if not exists
    if (!document.getElementById('loader-styles')) {
        const styleSheet = document.createElement('style');
        styleSheet.id = 'loader-styles';
        styleSheet.textContent = loaderStyles;
        document.head.appendChild(styleSheet);
    }

    // Create loader container if not exists
    if (!document.querySelector('.loader-container')) {
        const loaderContainer = document.createElement('div');
        loaderContainer.className = 'loader-container';
        loaderContainer.innerHTML = '<div class="loader"></div>';
        document.body.appendChild(loaderContainer);
    }
}

function hideLoader() {
    const loaderContainer = document.querySelector('.loader-container');
    if (loaderContainer) {
        loaderContainer.remove();
    }
}

// Load leave data
async function loadLeaves() {
    try {
        const params = new URLSearchParams({
            page: currentPage,
            per_page: perPage
        });
        
        if (searchInput.value) {
            params.append('search', searchInput.value);
        }
        if (statusFilter.value) {
            params.append('status', statusFilter.value);
        }
        if (leaveTypeFilter.value) {
            params.append('leave_type', leaveTypeFilter.value);
        }
        
        const response = await fetch(`${LEAVES_API}?${params.toString()}`);
        const data = await response.json();
        
        if (data.success) {
            renderLeaves(data.data);
            renderPagination(data.total);
        } else {
            showToast('error', 'Lỗi', data.message);
        }
    } catch (error) {
        showToast('error', 'Lỗi', 'Không thể tải dữ liệu');
    }
}

// Render leaves table
function renderLeaves(leaves) {
    leaveTableBody.innerHTML = '';
    
    leaves.forEach((leave, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${(currentPage - 1) * perPage + index + 1}</td>
            <td>${leave.id}</td>
            <td>${leave.employee_name || 'N/A'}</td>
            <td>${leave.leave_type}</td>
            <td>${formatDateOnly(leave.start_date)}</td>
            <td>${formatDateOnly(leave.end_date)}</td>
            <td>${parseInt(leave.leave_duration_days)}</td>
            <td>${leave.reason}</td>
            <td>
                <span class="badge bg-${getStatusColor(leave.status)} badge-status">
                    ${getStatusText(leave.status)}
                </span>
            </td>
            <td>${leave.approved_by_user_id || '-'}</td>
            <td>${formatDateTime(leave.created_at)}</td>
            <td>
                <div class="action-btns d-flex gap-2 justify-content-center">
                    <button class="icon-btn info-btn" title="Xem chi tiết" onclick="viewLeave(${leave.id})">
                        <i class="fas fa-info"></i>
                    </button>
                    <button class="icon-btn edit-btn" title="Sửa" onclick="editLeave(${leave.id})">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button class="icon-btn delete-btn" title="Xóa" onclick="deleteLeave(${leave.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        leaveTableBody.appendChild(row);
    });
}

// Render pagination
function renderPagination(total) {
    totalPages = Math.ceil(total / perPage);
    const container = document.getElementById('customPagination');
    if (!container) return;
    container.innerHTML = '';

    // Previous page button
    const prevBtn = document.createElement('button');
    prevBtn.className = 'btn btn-outline-secondary';
    prevBtn.innerHTML = '&larr; Previous page';
    prevBtn.disabled = currentPage === 1;
    prevBtn.onclick = () => {
        showLoader();
        changePage(currentPage - 1).finally(() => {
            hideLoader();
        });
    };
    container.appendChild(prevBtn);

    // Next page button
    const nextBtn = document.createElement('button');
    nextBtn.className = 'btn btn-success';
    nextBtn.innerHTML = 'Next page &rarr;';
    nextBtn.disabled = currentPage === totalPages || totalPages === 0;
    nextBtn.onclick = () => {
        showLoader();
        changePage(currentPage + 1).finally(() => {
            hideLoader();
        });
    };

    // Page input and total
    const pageInput = document.createElement('input');
    pageInput.type = 'number';
    pageInput.value = currentPage;
    pageInput.min = 1;
    pageInput.max = totalPages;
    pageInput.style.width = '60px';
    pageInput.className = 'form-control d-inline-block text-center';
    pageInput.onkeydown = (e) => {
        if (e.key === 'Enter') {
            let val = parseInt(pageInput.value);
            if (!isNaN(val) && val >= 1 && val <= totalPages) {
                showLoader();
                changePage(val).finally(() => {
                    hideLoader();
                });
            }
        }
    };

    const totalSpan = document.createElement('span');
    totalSpan.textContent = `of ${totalPages}`;
    totalSpan.style.margin = '0 8px';

    // Small arrow buttons
    const leftArrow = document.createElement('button');
    leftArrow.className = 'btn btn-light border';
    leftArrow.innerHTML = '<i class="fas fa-chevron-left"></i>';
    leftArrow.disabled = currentPage === 1;
    leftArrow.onclick = () => {
        showLoader();
        changePage(currentPage - 1).finally(() => {
            hideLoader();
        });
    };

    const rightArrow = document.createElement('button');
    rightArrow.className = 'btn btn-light border';
    rightArrow.innerHTML = '<i class="fas fa-chevron-right"></i>';
    rightArrow.disabled = currentPage === totalPages || totalPages === 0;
    rightArrow.onclick = () => {
        showLoader();
        changePage(currentPage + 1).finally(() => {
            hideLoader();
        });
    };

    // Append elements
    container.appendChild(nextBtn);
    container.appendChild(pageInput);
    container.appendChild(totalSpan);
    container.appendChild(leftArrow);
    container.appendChild(rightArrow);
}

// Thay đổi trang
async function changePage(page) {
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    await loadLeaves();
}

// Load statistics
async function loadStatistics() {
    showLoader();
    try {
        const response = await fetch(STATISTICS_API);
        const data = await response.json();
        
        if (data.success) {
            updateStatistics(data.data);
        }
    } catch (error) {
        showToast('error', 'Lỗi', 'Không thể tải thống kê');
    } finally {
        hideLoader();
    }
}

// Update statistics cards
function updateStatistics(data) {
    document.getElementById('totalLeaves').textContent = data.total_leaves || 0;
    document.getElementById('approvedLeaves').textContent = data.approved_leaves || 0;
    document.getElementById('pendingLeaves').textContent = data.pending_leaves || 0;
}

// Setup event listeners
function setupEventListeners() {
    // Search input with debounce
    searchInput.addEventListener('input', debounce(() => {
        showLoader();
        currentPage = 1; // Reset to first page when searching
        loadLeaves().finally(() => {
            hideLoader();
        });
    }, 300)); // 300ms debounce time

    // Clear search button
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', () => {
            showLoader();
            searchInput.value = '';
            currentPage = 1;
            loadLeaves().finally(() => {
                hideLoader();
            });
        });
    }

    // Status filter
    statusFilter.addEventListener('change', () => {
        showLoader();
        currentPage = 1;
        loadLeaves().finally(() => {
            hideLoader();
        });
    });

    // Leave type filter
    leaveTypeFilter.addEventListener('change', () => {
        showLoader();
        currentPage = 1;
        loadLeaves().finally(() => {
            hideLoader();
        });
    });

    // Export button
    exportBtn.addEventListener('click', () => {
        showLoader();
        exportToExcel();
        setTimeout(() => hideLoader(), 1000); // Hide loader after export starts
    });

    // Add leave button
    addLeaveBtn.addEventListener('click', () => {
        document.getElementById('leaveModalLabel').textContent = 'Thêm đơn nghỉ phép mới';
        leaveForm.reset();
        delete leaveForm.dataset.leaveId;
        leaveModal.show();
    });

    // Form submit
    leaveForm.addEventListener('submit', handleLeaveSubmit);

    // Add modal event listeners
    const leaveModalElement = document.getElementById('leaveModal');
    if (leaveModalElement) {
        // Reset form when modal is hidden
        leaveModalElement.addEventListener('hidden.bs.modal', () => {
            leaveForm.reset();
            delete leaveForm.dataset.leaveId;
            document.getElementById('leaveModalLabel').textContent = 'Thêm đơn nghỉ phép mới';
        });
        
        // Validate dates when they change
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');
        
        if (startDateInput && endDateInput) {
            startDateInput.addEventListener('change', validateDates);
            endDateInput.addEventListener('change', validateDates);
        }
    }

    // Add click event listener for pending leaves card
    document.getElementById('pendingLeavesCard').addEventListener('click', function() {
        showPendingLeavesModal();
    });
}

// Handle leave form submission
async function handleLeaveSubmit(e) {
    e.preventDefault();
    showLoader();
    
    const leaveId = leaveForm.dataset.leaveId;
    const isEdit = !!leaveId;
    
    const leaveData = {
        employee_id: document.getElementById('employeeId').value,
        leave_type: document.getElementById('leaveType').value,
        start_date: document.getElementById('startDate').value,
        end_date: document.getElementById('endDate').value,
        reason: document.getElementById('reason').value
    };
    
    try {
        const response = await fetch(`${LEAVES_API}${isEdit ? `?action=update&id=${leaveId}` : '?action=create'}`, {
            method: isEdit ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(leaveData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Thành công', `Đơn nghỉ phép đã được ${isEdit ? 'cập nhật' : 'tạo'}`);
            leaveModal.hide();
            loadLeaves();
            loadStatistics();
            
            // Reset form and remove leave ID
            leaveForm.reset();
            delete leaveForm.dataset.leaveId;
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        showToast('error', 'Lỗi', error.message || `Không thể ${isEdit ? 'cập nhật' : 'tạo'} đơn nghỉ phép`);
    } finally {
        hideLoader();
    }
}

// Function to show leave details
async function viewLeave(id) {
    try {
        showLoader();
        const response = await fetch(`${LEAVES_API}?id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const leave = data.data;
            
            // Update modal content
            document.getElementById('modalLeaveCode').textContent = leave.id;
            document.getElementById('modalEmployee').textContent = leave.employee_name || 'N/A';
            document.getElementById('modalLeaveType').textContent = leave.leave_type;
            document.getElementById('modalStartDate').textContent = formatDateTime(leave.start_date);
            document.getElementById('modalEndDate').textContent = formatDateTime(leave.end_date);
            document.getElementById('modalDuration').textContent = leave.leave_duration_days;
            document.getElementById('modalReason').textContent = leave.reason;

            // Set up approve and reject buttons
            const approveButton = document.getElementById('modalApproveButton');
            const rejectButton = document.getElementById('modalRejectButton');

            approveButton.onclick = () => {
                approveLeave(leave.id);
                bootstrap.Modal.getInstance(document.getElementById('leaveDetailsModal')).hide();
            };
            
            rejectButton.onclick = () => {
                showRejectModal(leave.id);
                bootstrap.Modal.getInstance(document.getElementById('leaveDetailsModal')).hide();
            };

            // Show modal
            const leaveDetailsModal = new bootstrap.Modal(document.getElementById('leaveDetailsModal'));
            leaveDetailsModal.show();
        } else {
            showToast('error', 'Lỗi', data.message || 'Không thể tải thông tin đơn nghỉ phép');
        }
    } catch (error) {
        console.error('Error loading leave details:', error);
        showToast('error', 'Lỗi', 'Không thể kết nối đến server');
    } finally {
        hideLoader();
    }
}

// Function to show pending leaves modal
async function showPendingLeavesModal() {
    try {
        showLoader();
        const response = await fetch(`${LEAVES_API}?status=pending`);
        const result = await response.json();
        
        if (result.success) {
            const pendingLeavesTableBody = document.getElementById('pendingLeavesTableBody');
            pendingLeavesTableBody.innerHTML = '';
            
            if (result.data.length === 0) {
                pendingLeavesTableBody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <div class="no-results">
                                <i class="fas fa-inbox fa-2x mb-3"></i>
                                <p class="mb-0">Không có đơn nào đang chờ duyệt</p>
                            </div>
                        </td>
                    </tr>
                `;
            } else {
                result.data.forEach((leave, index) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${leave.id}</td>
                        <td>${leave.employee_name || 'N/A'}</td>
                        <td>${leave.leave_type}</td>
                        <td>${formatDateOnly(leave.start_date)}</td>
                        <td>${formatDateOnly(leave.end_date)}</td>
                        <td>${parseInt(leave.leave_duration_days)}</td>
                        <td>${leave.reason}</td>
                        <td>${formatDateTime(leave.created_at)}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-outline-success btn-sm" data-bs-toggle="tooltip" title="Duyệt đơn này" onclick="approveLeave(${leave.id})">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm" data-bs-toggle="tooltip" title="Từ chối đơn này" onclick="showInlineRejectForm(this, ${leave.id})">
                                    <i class="fas fa-times"></i>
                                </button>
                                <button class="btn btn-outline-info btn-sm" data-bs-toggle="tooltip" title="Xem chi tiết" onclick="viewLeave(${leave.id})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning btn-sm" data-bs-toggle="tooltip" title="Sửa thông tin" onclick="editPendingRow(this, ${leave.id})">
                                    <i class="fas fa-pen"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    pendingLeavesTableBody.appendChild(row);
                });
            }
            
            // Show modal
            const pendingLeavesModal = new bootstrap.Modal(document.getElementById('pendingLeavesModal'));
            pendingLeavesModal.show();
        } else {
            showToast('error', 'Lỗi', result.message || 'Không thể tải danh sách đơn chờ duyệt');
        }
    } catch (error) {
        console.error('Error loading pending leaves:', error);
        showToast('error', 'Lỗi', 'Không thể kết nối đến server');
    } finally {
        hideLoader();
    }
}

// Update approveLeave function
async function approveLeave(leaveId) {
    if (!confirm('Bạn có chắc chắn muốn duyệt đơn nghỉ phép này?')) return;
    
    showLoader();
    try {
        const response = await fetch(`${LEAVES_API}?id=${leaveId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                status: 'approved',
                approved_by_user_id: window.currentUserId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('success', 'Thành công', 'Đơn nghỉ phép đã được duyệt');
            // Hide details section
            hideLeaveDetails();
            // Refresh data
            loadLeaves();
            loadStatistics();
        } else {
            showToast('error', 'Lỗi', data.message);
        }
    } catch (error) {
        showToast('error', 'Lỗi', 'Không thể duyệt đơn nghỉ phép');
    } finally {
        hideLoader();
    }
}

// Update rejectLeave function to return true/false
async function rejectLeave(leaveId, reason) {
    showLoader();
    try {
        const response = await fetch(`${LEAVES_API}?id=${leaveId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                status: 'rejected',
                approved_by_user_id: window.currentUserId,
                approver_comments: reason
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('success', 'Thành công', 'Đơn nghỉ phép đã bị từ chối');
            hideLeaveDetails();
            loadLeaves();
            loadStatistics();
            return true;
        } else {
            showToast('error', 'Lỗi', data.message || 'Không thể từ chối đơn nghỉ phép');
            return false;
        }
    } catch (error) {
        console.error('Error rejecting leave:', error);
        // showToast('error', 'Lỗi', 'Không thể kết nối đến server');
        return false;
    } finally {
        hideLoader();
    }
}

// Delete leave request
async function deleteLeave(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa đơn nghỉ phép này?')) return;
    
    showLoader();
    try {
        const response = await fetch(`${LEAVES_API}?id=${id}`, {
            method: 'DELETE'
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('success', 'Thành công', 'Đơn nghỉ phép đã được xóa');
            loadLeaves();
            loadStatistics();
        } else {
            showToast('error', 'Lỗi', data.message);
        }
    } catch (error) {
        showToast('error', 'Lỗi', 'Không thể xóa đơn nghỉ phép');
    } finally {
        hideLoader();
    }
}

// Export to Excel
function exportToExcel() {
    const params = new URLSearchParams({
        start_date: document.getElementById('startDate').value,
        end_date: document.getElementById('endDate').value,
        status: statusFilter.value,
        leave_type: leaveTypeFilter.value
    });
    // Đúng endpoint export
    window.location.href = '/qlnhansu_V3/backend/src/api/leaves/export.php?' + params.toString();
}

// Helper functions
function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleString('vi-VN');
}

function formatDateOnly(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('vi-VN');
}

function getStatusColor(status) {
    switch (status) {
        case 'approved': return 'success';
        case 'rejected': return 'danger';
        case 'pending': return 'warning';
        case 'cancelled': return 'secondary';
        default: return 'primary';
    }
}

function getStatusText(status) {
    switch (status) {
        case 'approved': return 'Đã duyệt';
        case 'rejected': return 'Đã từ chối';
        case 'pending': return 'Đang chờ duyệt';
        case 'cancelled': return 'Đã hủy';
        default: return status;
    }
}

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

function showToast(type, title, message) {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    // Get icon based on type
    const getIcon = (type) => {
        switch(type) {
            case 'success': return '<i class="fas fa-check-circle me-2"></i>';
            case 'error': return '<i class="fas fa-exclamation-circle me-2"></i>';
            case 'warning': return '<i class="fas fa-exclamation-triangle me-2"></i>';
            case 'info': return '<i class="fas fa-info-circle me-2"></i>';
            default: return '';
        }
    };

    // Add animation classes
    toast.style.animation = 'slideIn 0.5s ease-out';
    
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="toast-body d-flex align-items-center">
                ${getIcon(type)}
                <div>
                    <strong class="me-auto">${title}</strong>
                    <div class="mt-1">${message}</div>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white ms-auto me-2" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    // Add custom styles
    const style = document.createElement('style');
    style.textContent = `
        .toast-container {
            z-index: 1050;
        }
        .toast {
            min-width: 300px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .toast.bg-success {
            background-color: rgba(40, 167, 69, 0.95) !important;
        }
        .toast.bg-error {
            background-color: rgba(220, 53, 69, 0.95) !important;
        }
        .toast.bg-warning {
            background-color: rgba(255, 193, 7, 0.95) !important;
        }
        .toast.bg-info {
            background-color: rgba(23, 162, 184, 0.95) !important;
        }
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
    
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, {
        animation: true,
        autohide: true,
        delay: 5000
    });
    
    // Add slide out animation before removing
    toast.addEventListener('hide.bs.toast', () => {
        toast.style.animation = 'slideOut 0.5s ease-in';
    });
    
    bsToast.show();
    
    // Remove toast and style after hiding
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
        style.remove();
    });
}

// Lấy ID user hiện tại từ session
function getCurrentUserId() {
    // Có thể lấy từ một biến global được set khi load trang
    return window.currentUserId;
}

// Update editLeave function to handle employee data correctly
async function editLeave(id) {
    showLoader();
    try {
        // Get leave information
        const leaveResponse = await fetch(`${LEAVES_API}?action=getById&id=${id}`);
        if (!leaveResponse.ok) {
            throw new Error('Không thể kết nối đến server');
        }
        const leaveData = await leaveResponse.json();
        
        if (!leaveData.success) {
            throw new Error(leaveData.message || 'Không thể lấy thông tin đơn nghỉ phép');
        }
        
        const leave = leaveData.data;
        
        // Get employee information - Fix the API path
        const employeeResponse = await fetch(`/qlnhansu_V3/backend/src/api/v1/employees.php?action=getById&id=${leave.employee_id}`);
        if (!employeeResponse.ok) {
            throw new Error('Không thể kết nối đến server');
        }
        const employeeData = await employeeResponse.json();
        
        if (!employeeData.success) {
            throw new Error(employeeData.message || 'Không thể lấy thông tin nhân viên');
        }
        
        const employee = employeeData.data;
        
        // Update modal title
        document.getElementById('leaveModalLabel').textContent = 'Sửa đơn nghỉ phép';
        
        // Fill form with leave data
        const employeeSelect = document.getElementById('employeeId');
        
        // Check if employee exists in dropdown
        let employeeOption = Array.from(employeeSelect.options).find(option => 
            option.value === employee.id.toString()
        );
        
        if (!employeeOption) {
            // If employee not found in dropdown, add it
            employeeOption = document.createElement('option');
            employeeOption.value = employee.id;
            employeeOption.textContent = `${employee.employee_code || 'N/A'} - ${employee.name || 'N/A'}`;
            employeeOption.dataset.employeeCode = employee.employee_code || '';
            employeeOption.dataset.employeeName = employee.name || '';
            employeeOption.dataset.positionName = employee.position_name || '';
            employeeOption.dataset.departmentName = employee.department_name || '';
            employeeSelect.appendChild(employeeOption);
        }
        
        // Set the selected employee
        employeeSelect.value = employee.id;
        
        // Fill other form fields
        document.getElementById('leaveType').value = leave.leave_type;
        document.getElementById('startDate').value = leave.start_date.split(' ')[0];
        document.getElementById('endDate').value = leave.end_date.split(' ')[0];
        document.getElementById('reason').value = leave.reason;
        
        // Store leave ID for form submission
        document.getElementById('leaveForm').dataset.leaveId = id;
        
        // Show modal
        const leaveModal = new bootstrap.Modal(document.getElementById('leaveModal'));
        leaveModal.show();
        
    } catch (error) {
        console.error('Error in editLeave:', error);
        showToast('error', 'Lỗi', error.message);
    } finally {
        hideLoader();
    }
}

// Add date validation function
function validateDates() {
    const startDate = new Date(document.getElementById('startDate').value);
    const endDate = new Date(document.getElementById('endDate').value);
    
    if (startDate && endDate && startDate > endDate) {
        showToast('error', 'Lỗi', 'Ngày kết thúc phải sau ngày bắt đầu');
        document.getElementById('endDate').value = '';
    }
}

// Function to show reject modal
function showRejectModal(leaveId) {
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    document.getElementById('rejectModal').dataset.leaveId = leaveId;
    modal.show();
}

// Update event listener for reject confirmation to use async/await and only close modal on success
const confirmRejectBtn = document.getElementById('confirmReject');
if (confirmRejectBtn) {
    confirmRejectBtn.addEventListener('click', async function() {
        const leaveId = document.getElementById('rejectModal').dataset.leaveId;
        const reason = document.getElementById('rejectReason').value;
        
        if (!reason.trim()) {
            showNotification('error', 'Lỗi', 'Vui lòng nhập lý do từ chối');
            return;
        }
        // Await rejectLeave and only close modal if success
        const result = await rejectLeave(leaveId, reason);
        if (result === true) {
            bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
            document.getElementById('rejectReason').value = '';
        }
    });
}

// Update pagination function
function updatePagination(total, currentPage, perPage) {
    const totalPages = Math.ceil(total / perPage);
    const paginationContainer = document.getElementById('customPagination');
    if (!paginationContainer) return;

    let paginationHTML = '';
    
    // Previous button
    paginationHTML += `
        <button class="btn btn-outline-secondary" ${currentPage === 1 ? 'disabled' : ''} 
                onclick="changePage(${currentPage - 1})">
            <i class="fas fa-chevron-left"></i> Trang trước
        </button>
    `;

    // Page numbers
    const maxVisiblePages = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

    if (endPage - startPage + 1 < maxVisiblePages) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }

    if (startPage > 1) {
        paginationHTML += `
            <button class="btn btn-outline-secondary" onclick="changePage(1)">1</button>
            ${startPage > 2 ? '<span class="mx-2">...</span>' : ''}
        `;
    }

    for (let i = startPage; i <= endPage; i++) {
        paginationHTML += `
            <button class="btn ${i === currentPage ? 'btn-primary' : 'btn-outline-secondary'}" 
                    onclick="changePage(${i})">${i}</button>
        `;
    }

    if (endPage < totalPages) {
        paginationHTML += `
            ${endPage < totalPages - 1 ? '<span class="mx-2">...</span>' : ''}
            <button class="btn btn-outline-secondary" onclick="changePage(${totalPages})">${totalPages}</button>
        `;
    }

    // Next button
    paginationHTML += `
        <button class="btn btn-outline-secondary" ${currentPage === totalPages ? 'disabled' : ''} 
                onclick="changePage(${currentPage + 1})">
            Trang sau <i class="fas fa-chevron-right"></i>
        </button>
    `;

    paginationContainer.innerHTML = paginationHTML;
}

// Update fetchLeaveData function to use the new pagination
async function fetchLeaveData() {
    try {
        const params = new URLSearchParams(window.location.search);
        const response = await fetch(`../api/leaves.php?${params.toString()}`);
        const result = await response.json();
        
        if (result.success) {
            updateTableData(result.data);
            updatePagination(result.total, result.page, result.per_page);
        } else {
            showNotification('error', 'Lỗi khi tải dữ liệu', result.message);
        }
    } catch (error) {
        console.error('Error fetching leave data:', error);
        showNotification('error', 'Lỗi khi tải dữ liệu', 'Không thể kết nối đến server');
    }
}

// Thêm hàm showInlineRejectForm để hiển thị form từ chối ngay trong dòng
function showInlineRejectForm(btn, leaveId) {
    const row = btn.closest('tr');
    // Nếu đã có form thì không thêm nữa
    if (row.nextElementSibling && row.nextElementSibling.classList.contains('inline-reject-row')) return;
    // Xóa form cũ nếu có
    const oldForm = document.querySelector('.inline-reject-row');
    if (oldForm) oldForm.remove();
    // Tạo dòng mới cho form
    const formRow = document.createElement('tr');
    formRow.className = 'inline-reject-row';
    formRow.innerHTML = `<td colspan="10">
        <form class="d-flex align-items-center gap-2" onsubmit="submitInlineReject(event, ${leaveId})">
            <input type="text" class="form-control" name="reason" placeholder="Nhập lý do từ chối..." required style="max-width:300px;">
            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-check"></i> Xác nhận từ chối</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="this.closest('tr').remove()"><i class="fas fa-times"></i> Hủy</button>
        </form>
    </td>`;
    row.parentNode.insertBefore(formRow, row.nextSibling);
    // Focus vào input
    formRow.querySelector('input[name="reason"]').focus();
}

// Hàm submitInlineReject để gửi lý do từ chối
async function submitInlineReject(event, leaveId) {
    event.preventDefault();
    const form = event.target;
    const reason = form.reason.value.trim();
    if (!reason) return;
    await rejectLeave(leaveId, reason);
    // Xóa form sau khi gửi
    form.closest('tr').remove();
}

// Kích hoạt tooltip Bootstrap khi render xong bảng
setTimeout(() => {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
}, 300);

// Hàm chuyển dòng sang chế độ chỉnh sửa
function editPendingRow(btn, leaveId) {
    // Đảm bảo chỉ một dòng được sửa
    const editingRow = document.querySelector('.editing-row');
    if (editingRow) {
        editingRow.classList.remove('editing-row');
        editingRow.innerHTML = editingRow.dataset.originalHtml;
    }
    const row = btn.closest('tr');
    row.classList.add('editing-row');
    row.dataset.originalHtml = row.innerHTML;
    // Lấy dữ liệu hiện tại
    const tds = row.querySelectorAll('td');
    const stt = tds[0].textContent;
    const id = tds[1].textContent;
    const employee = tds[2].textContent;
    const leaveType = tds[3].textContent;
    const startDate = tds[4].textContent.split('/').reverse().join('-');
    const endDate = tds[5].textContent.split('/').reverse().join('-');
    const duration = tds[6].textContent;
    const reason = tds[7].textContent;
    const createdAt = tds[8].textContent;
    // Render input
    row.innerHTML = `
        <td>${stt}</td>
        <td>${id}</td>
        <td><input type="text" class="form-control form-control-sm" value="${employee}" readonly></td>
        <td>
            <select class="form-select form-select-sm">
                <option value="Annual" ${leaveType==='Annual'?'selected':''}>Nghỉ phép năm</option>
                <option value="Sick" ${leaveType==='Sick'?'selected':''}>Nghỉ ốm</option>
                <option value="Unpaid" ${leaveType==='Unpaid'?'selected':''}>Nghỉ không lương</option>
                <option value="Maternity" ${leaveType==='Maternity'?'selected':''}>Nghỉ thai sản</option>
            </select>
        </td>
        <td><input type="date" class="form-control form-control-sm" value="${startDate}"></td>
        <td><input type="date" class="form-control form-control-sm" value="${endDate}"></td>
        <td><input type="number" class="form-control form-control-sm" value="${duration}" min="1"></td>
        <td><input type="text" class="form-control form-control-sm" value="${reason}"></td>
        <td>${createdAt}</td>
        <td>
            <div class="btn-group" role="group">
                <button class="btn btn-outline-success btn-sm" title="Lưu" onclick="savePendingRow(this, ${leaveId})"><i class="fas fa-save"></i></button>
                <button class="btn btn-outline-secondary btn-sm" title="Hủy" onclick="cancelEditPendingRow(this)"><i class="fas fa-times"></i></button>
            </div>
        </td>
    `;
}

// Hàm lưu chỉnh sửa
async function savePendingRow(btn, leaveId) {
    const row = btn.closest('tr');
    const tds = row.querySelectorAll('td');
    const leave_type = tds[3].querySelector('select').value;
    const start_date = tds[4].querySelector('input').value;
    const end_date = tds[5].querySelector('input').value;
    const leave_duration_days = tds[6].querySelector('input').value;
    const reason = tds[7].querySelector('input').value;
    // Validate
    if (!leave_type || !start_date || !end_date || !leave_duration_days || !reason) {
        showToast('error', 'Lỗi', 'Vui lòng nhập đầy đủ thông tin');
        return;
    }
    showLoader();
    try {
        const response = await fetch(`${LEAVES_API}?id=${leaveId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                leave_type,
                start_date,
                end_date,
                leave_duration_days,
                reason
            })
        });
        const data = await response.json();
        if (data.success) {
            showToast('success', 'Thành công', 'Đã cập nhật đơn nghỉ phép');
            showPendingLeavesModal(); // Reload lại bảng
        } else {
            showToast('error', 'Lỗi', data.message);
        }
    } catch (error) {
        showToast('error', 'Lỗi', 'Không thể cập nhật đơn nghỉ phép');
    } finally {
        hideLoader();
    }
}

// Hàm hủy chỉnh sửa
function cancelEditPendingRow(btn) {
    const row = btn.closest('tr');
    row.innerHTML = row.dataset.originalHtml;
    row.classList.remove('editing-row');
} 