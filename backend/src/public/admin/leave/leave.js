// Initialize variables
let currentPage = 1;
const perPage = 10;
let totalPages = 1;
let leaveChart = null;
let statusChart = null;

// API endpoints
const LEAVES_API = `${api.API_BASE_URL}/hr/leaves.php`;
const STATISTICS_API = `${api.API_BASE_URL}/hr/leaves.php?action=statistics`;
const NOTIFICATIONS_API = `${api.API_BASE_URL}/notifications.php`;

// DOM Elements
const leaveTableBody = document.getElementById('leaveTableBody');
const pagination = document.getElementById('pagination');
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const leaveTypeFilter = document.getElementById('leaveTypeFilter');
const exportBtn = document.getElementById('exportBtn');
const addLeaveBtn = document.getElementById('addLeaveBtn');
const leaveModal = document.getElementById('leaveModal');
const leaveForm = document.getElementById('leaveForm');
const leaveInfoModal = new bootstrap.Modal(document.getElementById('leaveInfoModal'));

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    loadLeaves();
    loadStatistics();
    setupEventListeners();
    initializeCharts();
});

// Load leave data
async function loadLeaves() {
    try {
        const response = await fetch(`${LEAVES_API}?page=${currentPage}&per_page=${perPage}`);
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
            <td>${leave.employee_name}</td>
            <td>${leave.leave_type}</td>
            <td>${formatDateTime(leave.start_date)}</td>
            <td>${formatDateTime(leave.end_date)}</td>
            <td>${leave.leave_duration_days}</td>
            <td>${leave.reason}</td>
            <td>
                <span class="badge bg-${getStatusColor(leave.status)}">
                    ${getStatusText(leave.status)}
                </span>
            </td>
            <td>${leave.approver_name || '-'}</td>
            <td>${formatDateTime(leave.created_at)}</td>
            <td>
                <div class="btn-group">
                    <button class="btn btn-sm btn-info" onclick="viewLeave(${leave.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${leave.status === 'pending' ? `
                        <button class="btn btn-sm btn-success" onclick="approveLeave(${leave.id})">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="rejectLeave(${leave.id})">
                            <i class="fas fa-times"></i>
                        </button>
                    ` : ''}
                    <button class="btn btn-sm btn-danger" onclick="deleteLeave(${leave.id})">
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
    pagination.innerHTML = '';
    
    // Previous button
    pagination.innerHTML += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Trước</a>
        </li>
    `;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        pagination.innerHTML += `
            <li class="page-item ${currentPage === i ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
            </li>
        `;
    }
    
    // Next button
    pagination.innerHTML += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Sau</a>
        </li>
    `;
}

// Load statistics
async function loadStatistics() {
    try {
        const response = await fetch(STATISTICS_API);
        const data = await response.json();
        
        if (data.success) {
            updateStatistics(data.data);
            updateCharts(data.data);
        }
    } catch (error) {
        showToast('error', 'Lỗi', 'Không thể tải thống kê');
    }
}

// Update statistics cards
function updateStatistics(data) {
    document.getElementById('totalLeaves').textContent = data.total_leaves;
    document.getElementById('approvedLeaves').textContent = data.approved_leaves;
    document.getElementById('pendingLeaves').textContent = data.pending_leaves;
}

// Initialize charts
function initializeCharts() {
    // Leave type distribution chart
    const typeCtx = document.getElementById('leaveTypeChart').getContext('2d');
    leaveChart = new Chart(typeCtx, {
        type: 'pie',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#4C00FC',
                    '#4EB5FF',
                    '#FF4E4E',
                    '#FFB74E'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Leave status distribution chart
    const statusCtx = document.getElementById('leaveStatusChart').getContext('2d');
    statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#4C00FC',
                    '#4EB5FF',
                    '#FF4E4E',
                    '#FFB74E'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Update charts with new data
function updateCharts(data) {
    // Update leave type chart
    leaveChart.data.labels = Object.keys(data.leave_type_distribution);
    leaveChart.data.datasets[0].data = Object.values(data.leave_type_distribution);
    leaveChart.update();

    // Update status chart
    statusChart.data.labels = Object.keys(data.status_distribution);
    statusChart.data.datasets[0].data = Object.values(data.status_distribution);
    statusChart.update();
}

// Setup event listeners
function setupEventListeners() {
    // Search input
    searchInput.addEventListener('input', debounce(() => {
        currentPage = 1;
        loadLeaves();
    }, 500));

    // Status filter
    statusFilter.addEventListener('change', () => {
        currentPage = 1;
        loadLeaves();
    });

    // Leave type filter
    leaveTypeFilter.addEventListener('change', () => {
        currentPage = 1;
        loadLeaves();
    });

    // Export button
    exportBtn.addEventListener('click', exportToExcel);

    // Add leave button
    addLeaveBtn.addEventListener('click', () => {
        document.getElementById('modalTitle').textContent = 'Thêm đơn nghỉ phép mới';
        leaveForm.reset();
        leaveModal.style.display = 'block';
    });

    // Close modal
    document.querySelector('.close').addEventListener('click', () => {
        leaveModal.style.display = 'none';
    });

    // Form submit
    leaveForm.addEventListener('submit', handleLeaveSubmit);
}

// Handle leave form submission
async function handleLeaveSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(leaveForm);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch(LEAVES_API, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Thành công', 'Đơn nghỉ phép đã được tạo');
            leaveModal.style.display = 'none';
            loadLeaves();
            loadStatistics();
        } else {
            showToast('error', 'Lỗi', result.message);
        }
    } catch (error) {
        showToast('error', 'Lỗi', 'Không thể tạo đơn nghỉ phép');
    }
}

// View leave details
async function viewLeave(id) {
    try {
        const response = await fetch(`${LEAVES_API}?id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const leave = data.data;
            
            // Update modal content
            document.getElementById('infoLeaveCode').textContent = leave.id;
            document.getElementById('infoEmployee').textContent = leave.employee_name;
            document.getElementById('infoLeaveType').textContent = leave.leave_type;
            document.getElementById('infoStartDate').textContent = formatDateTime(leave.start_date);
            document.getElementById('infoEndDate').textContent = formatDateTime(leave.end_date);
            document.getElementById('infoDuration').textContent = leave.leave_duration_days;
            document.getElementById('infoReason').textContent = leave.reason;
            document.getElementById('infoStatus').textContent = getStatusText(leave.status);
            document.getElementById('infoApprover').textContent = leave.approver_name || '-';
            document.getElementById('infoApproverComments').textContent = leave.approver_comments || '-';
            
            // Show modal
            leaveInfoModal.show();
        }
    } catch (error) {
        showToast('error', 'Lỗi', 'Không thể tải thông tin đơn nghỉ phép');
    }
}

// Approve leave request
async function approveLeave(id) {
    if (!confirm('Bạn có chắc chắn muốn duyệt đơn nghỉ phép này?')) return;
    
    try {
        const response = await fetch(`${LEAVES_API}/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                status: 'approved'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('success', 'Thành công', 'Đơn nghỉ phép đã được duyệt');
            loadLeaves();
            loadStatistics();
        } else {
            showToast('error', 'Lỗi', data.message);
        }
    } catch (error) {
        showToast('error', 'Lỗi', 'Không thể duyệt đơn nghỉ phép');
    }
}

// Reject leave request
async function rejectLeave(id) {
    const comments = prompt('Nhập lý do từ chối:');
    if (comments === null) return;
    
    try {
        const response = await fetch(`${LEAVES_API}/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                status: 'rejected',
                approver_comments: comments
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('success', 'Thành công', 'Đơn nghỉ phép đã bị từ chối');
            loadLeaves();
            loadStatistics();
        } else {
            showToast('error', 'Lỗi', data.message);
        }
    } catch (error) {
        showToast('error', 'Lỗi', 'Không thể từ chối đơn nghỉ phép');
    }
}

// Delete leave request
async function deleteLeave(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa đơn nghỉ phép này?')) return;
    
    try {
        const response = await fetch(`${LEAVES_API}/${id}`, {
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
    
    window.location.href = `${LEAVES_API}/export.php?${params.toString()}`;
}

// Helper functions
function formatDateTime(dateString) {
    return new Date(dateString).toLocaleString('vi-VN');
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
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <strong>${title}</strong><br>
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