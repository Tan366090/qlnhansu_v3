// DOM Elements
const departmentTableBody = document.getElementById('departmentTableBody');
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const totalDepartments = document.getElementById('totalDepartments');
const totalEmployees = document.getElementById('totalEmployees');
const totalManagers = document.getElementById('totalManagers');
const addDepartmentBtn = document.getElementById('addDepartmentBtn');
const exportBtn = document.getElementById('exportBtn');
const departmentForm = document.getElementById('departmentForm');
const departmentModal = document.getElementById('departmentModal');
const cancelBtn = document.getElementById('cancelBtn');
const parentDepartmentSelect = document.getElementById('parentDepartment');
const departmentManagerSelect = document.getElementById('departmentManager');

// State
let departments = [];
let filteredDepartments = [];
let currentDepartmentId = null;
let isLoading = false;
let debounceTimer;
let departmentChart = null;
let departmentStatusChart = null;
let isBarChart = true;

// Fetch departments data with loading state
async function fetchDepartments() {
    if (isLoading) return;
    
    try {
        isLoading = true;
        showLoadingState();
        
        const response = await fetch('/qlnhansu_V3/backend/src/api/v1/departments.php?action=getAll');
        const result = await response.json();
        
        if (result.status === 'success') {
            departments = result.data;
            filteredDepartments = [...departments];
            updateDashboardStats();
            renderDepartments();
            updateParentDepartmentOptions();
            updateManagerOptions();
            
            // Đảm bảo Chart.js đã được tải
            if (typeof Chart !== 'undefined') {
                initializeCharts(departments);
            } else {
                console.warn('Chart.js chưa được tải. Biểu đồ sẽ không được hiển thị.');
            }
        } else {
            showToast('error', 'Không thể tải dữ liệu phòng ban');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Lỗi kết nối server');
    } finally {
        isLoading = false;
        hideLoadingState();
    }
}

// Show loading state
function showLoadingState() {
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';
    loadingOverlay.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    `;
    document.body.appendChild(loadingOverlay);
}

// Hide loading state
function hideLoadingState() {
    const loadingOverlay = document.querySelector('.loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.remove();
    }
}

// Update dashboard statistics with animations
function updateDashboardStats() {
    animateNumber(totalDepartments, departments.length);
    animateNumber(totalEmployees, departments.reduce((sum, dept) => sum + dept.employee_count, 0));
    animateNumber(totalManagers, departments.filter(dept => dept.manager.id).length);
}

// Animate number counting
function animateNumber(element, target) {
    const start = parseInt(element.textContent) || 0;
    const duration = 1000;
    const steps = 60;
    const increment = (target - start) / steps;
    let current = start;
    let step = 0;

    const timer = setInterval(() => {
        step++;
        current = Math.round(start + (increment * step));
        element.textContent = current.toLocaleString();

        if (step >= steps) {
            element.textContent = target.toLocaleString();
            clearInterval(timer);
        }
    }, duration / steps);
}

// Update parent department options
function updateParentDepartmentOptions() {
    parentDepartmentSelect.innerHTML = '<option value="">Không có</option>';
    
    departments
        .filter(dept => dept.id !== currentDepartmentId)
        .forEach(dept => {
            const option = document.createElement('option');
            option.value = dept.id;
            option.textContent = dept.name;
            parentDepartmentSelect.appendChild(option);
        });
}

// Update manager options
function updateManagerOptions() {
    departmentManagerSelect.innerHTML = '<option value="">Chọn quản lý</option>';
    
    // Fetch employees who can be managers
    fetch('/qlnhansu_V3/backend/src/api/v1/employees.php?action=getPotentialManagers')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(result => {
            if (result.success) {
                result.data.forEach(employee => {
                    const option = document.createElement('option');
                    option.value = employee.id;
                    option.textContent = `${employee.name} - ${employee.position}`;
                    departmentManagerSelect.appendChild(option);
                });
            } else {
                console.warn('Không thể lấy danh sách quản lý:', result.message);
            }
        })
        .catch(error => {
            console.error('Error fetching managers:', error);
            // Thêm một option mặc định khi có lỗi
            const option = document.createElement('option');
            option.value = "";
            option.textContent = "Không thể tải danh sách quản lý";
            departmentManagerSelect.appendChild(option);
        });
}

// Render departments table with sorting and pagination
function renderDepartments() {
    departmentTableBody.innerHTML = '';
    
    // Sort departments
    const sortBy = localStorage.getItem('departmentSortBy') || 'name';
    const sortOrder = localStorage.getItem('departmentSortOrder') || 'asc';
    
    filteredDepartments.sort((a, b) => {
        let comparison = 0;
        if (sortBy === 'name') {
            comparison = a.name.localeCompare(b.name);
        } else if (sortBy === 'employee_count') {
            comparison = a.employee_count - b.employee_count;
        } else if (sortBy === 'created_at') {
            comparison = new Date(a.created_at) - new Date(b.created_at);
        }
        return sortOrder === 'asc' ? comparison : -comparison;
    });
    
    // Pagination
    const itemsPerPage = 10;
    const currentPage = parseInt(localStorage.getItem('currentPage')) || 1;
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedDepartments = filteredDepartments.slice(startIndex, endIndex);
    
    // Render rows
    paginatedDepartments.forEach((dept, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${startIndex + index + 1}</td>
            <td>${dept.id}</td>
            <td>${dept.name}</td>
            <td>${dept.manager.name || 'Chưa có'}</td>
            <td>${dept.employee_count}</td>
            <td>
                <span class="status-badge ${dept.status}">
                    ${dept.status === 'active' ? 'Đang hoạt động' : 'Không hoạt động'}
                </span>
            </td>
            <td>${dept.manager.position || 'Chưa có'}</td>
            <td>${dept.description || 'Chưa có'}</td>
            <td>${formatDate(dept.created_at)}</td>
            <td>${formatDate(dept.updated_at)}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-view" onclick="viewDepartment(${dept.id})" title="Xem chi tiết">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-edit" onclick="editDepartment(${dept.id})" title="Chỉnh sửa">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-delete" onclick="deleteDepartment(${dept.id})" title="Xóa">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        departmentTableBody.appendChild(row);
    });
    
    // Render pagination
    renderPagination();
}

// Render pagination controls
function renderPagination() {
    const itemsPerPage = 10;
    const totalPages = Math.ceil(filteredDepartments.length / itemsPerPage);
    const currentPage = parseInt(localStorage.getItem('currentPage')) || 1;
    
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';
    
    // Previous button
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `
        <button class="page-link" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
            <i class="fas fa-chevron-left"></i>
        </button>
    `;
    pagination.appendChild(prevLi);
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === currentPage ? 'active' : ''}`;
        li.innerHTML = `
            <button class="page-link" onclick="changePage(${i})">${i}</button>
        `;
        pagination.appendChild(li);
    }
    
    // Next button
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    nextLi.innerHTML = `
        <button class="page-link" onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
            <i class="fas fa-chevron-right"></i>
        </button>
    `;
    pagination.appendChild(nextLi);
}

// Change page
function changePage(page) {
    localStorage.setItem('currentPage', page);
    renderDepartments();
}

// Smart search with debounce
function smartSearch() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        
        filteredDepartments = departments.filter(dept => {
            const matchesSearch = 
                dept.name.toLowerCase().includes(searchTerm) ||
                (dept.description && dept.description.toLowerCase().includes(searchTerm)) ||
                (dept.manager.name && dept.manager.name.toLowerCase().includes(searchTerm)) ||
                dept.id.toString().includes(searchTerm);
                
            const matchesStatus = !statusValue || dept.status === statusValue;
            return matchesSearch && matchesStatus;
        });
        
        localStorage.setItem('currentPage', 1);
        renderDepartments();
    }, 300);
}

// Enhanced save department with validation
async function saveDepartment(event) {
    event.preventDefault();
    
    const name = document.getElementById('departmentName').value.trim();
    const description = document.getElementById('departmentDescription').value.trim();
    const status = document.getElementById('departmentStatus').value;
    const parentId = document.getElementById('parentDepartment').value;
    const managerId = document.getElementById('departmentManager').value;
    
    // Validation
    if (!name) {
        showToast('error', 'Vui lòng nhập tên phòng ban');
        return;
    }
    
    if (name.length < 3) {
        showToast('error', 'Tên phòng ban phải có ít nhất 3 ký tự');
        return;
    }
    
    // Check for duplicate names
    const isDuplicate = departments.some(dept => 
        dept.name.toLowerCase() === name.toLowerCase() && 
        dept.id !== currentDepartmentId
    );
    
    if (isDuplicate) {
        showToast('error', 'Tên phòng ban đã tồn tại');
        return;
    }
    
    const formData = {
        name,
        description,
        status,
        parent_id: parentId || null,
        manager_id: managerId || null
    };
    
    try {
        showLoadingState();
        
        const url = currentDepartmentId 
            ? `/qlnhansu_V3/backend/src/api/v1/departments.php?action=update&id=${currentDepartmentId}`
            : '/qlnhansu_V3/backend/src/api/v1/departments.php?action=create';
            
        const response = await fetch(url, {
            method: currentDepartmentId ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('success', currentDepartmentId ? 'Cập nhật thành công' : 'Thêm mới thành công');
            departmentModal.style.display = 'none';
            fetchDepartments();
        } else {
            showToast('error', result.message || 'Có lỗi xảy ra');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Lỗi kết nối server');
    } finally {
        hideLoadingState();
    }
}

// Enhanced delete with dependency check
async function deleteDepartment(id) {
    const dept = departments.find(d => d.id === id);
    if (!dept) return;
    
    // Check for dependencies
    if (dept.employee_count > 0) {
        showToast('error', 'Không thể xóa phòng ban đang có nhân viên');
        return;
    }
    
    // Check for child departments
    const hasChildren = departments.some(d => d.parent_id === id);
    if (hasChildren) {
        showToast('error', 'Không thể xóa phòng ban đang có phòng ban con');
        return;
    }
    
    if (!confirm('Bạn có chắc chắn muốn xóa phòng ban này?')) return;
    
    try {
        showLoadingState();
        
        const response = await fetch(`/qlnhansu_V3/backend/src/api/v1/departments.php?action=delete&id=${id}`, {
            method: 'DELETE'
        });
        const result = await response.json();
        
        if (result.success) {
            showToast('success', 'Xóa phòng ban thành công');
            fetchDepartments();
        } else {
            showToast('error', result.message || 'Không thể xóa phòng ban');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Lỗi kết nối server');
    } finally {
        hideLoadingState();
    }
}

// Enhanced export with progress
async function exportToExcel() {
    try {
        showLoadingState();
        
        const data = filteredDepartments.map(dept => ({
            'Mã PB': dept.id,
            'Tên phòng ban': dept.name,
            'Trưởng phòng': dept.manager.name || 'Chưa có',
            'Số nhân viên': dept.employee_count,
            'Trạng thái': dept.status === 'active' ? 'Đang hoạt động' : 'Không hoạt động',
            'Mô tả': dept.description || 'Chưa có',
            'Ngày tạo': formatDate(dept.created_at),
            'Cập nhật lần cuối': formatDate(dept.updated_at)
        }));
        
        const worksheet = XLSX.utils.json_to_sheet(data);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Phòng ban');
        
        // Add styling
        const wscols = [
            {wch: 10}, // Mã PB
            {wch: 30}, // Tên phòng ban
            {wch: 25}, // Trưởng phòng
            {wch: 15}, // Số nhân viên
            {wch: 20}, // Trạng thái
            {wch: 40}, // Mô tả
            {wch: 20}, // Ngày tạo
            {wch: 20}  // Cập nhật lần cuối
        ];
        worksheet['!cols'] = wscols;
        
        XLSX.writeFile(workbook, `danh_sach_phong_ban_${formatDate(new Date())}.xlsx`);
        showToast('success', 'Xuất Excel thành công');
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Lỗi khi xuất Excel');
    } finally {
        hideLoadingState();
    }
}

// View department details
async function viewDepartment(id) {
    try {
        const response = await fetch(`/qlnhansu_V3/backend/src/api/v1/departments.php?action=getById&id=${id}`);
        const result = await response.json();
        
        if (result.success) {
            const dept = result.data;
            
            // Update modal content
            document.getElementById('infoDeptCode').textContent = dept.id;
            document.getElementById('infoDeptName').textContent = dept.name;
            document.getElementById('infoDeptDesc').textContent = dept.description || 'Chưa có';
            document.getElementById('infoDeptManager').textContent = dept.manager.name || 'Chưa có';
            document.getElementById('infoDeptEmployeeCount').textContent = dept.employee_count;
            document.getElementById('infoDeptParent').textContent = dept.parent_department.name || 'Không có';
            document.getElementById('infoDeptCreated').textContent = formatDate(dept.created_at);
            document.getElementById('infoDeptUpdated').textContent = formatDate(dept.updated_at);
            document.getElementById('infoDeptStatus').textContent = 
                dept.status === 'active' ? 'Đang hoạt động' : 'Không hoạt động';
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('departmentInfoModal'));
            modal.show();
        } else {
            showToast('error', 'Không thể lấy thông tin phòng ban');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('error', 'Lỗi kết nối server');
    }
}

// Add new department
function addDepartment() {
    currentDepartmentId = null;
    document.getElementById('modalTitle').textContent = 'Thêm phòng ban mới';
    document.getElementById('departmentForm').reset();
    departmentModal.style.display = 'block';
}

// Edit department
function editDepartment(id) {
    const dept = departments.find(d => d.id === id);
    if (!dept) return;
    
    currentDepartmentId = id;
    document.getElementById('modalTitle').textContent = 'Chỉnh sửa phòng ban';
    
    // Fill form data
    document.getElementById('departmentName').value = dept.name;
    document.getElementById('departmentCode').value = dept.id;
    document.getElementById('departmentDescription').value = dept.description || '';
    document.getElementById('departmentStatus').value = dept.status;
    
    departmentModal.style.display = 'block';
}

// Format date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Show toast notification
function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.querySelector('.toast-container').appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Event Listeners
searchInput.addEventListener('input', smartSearch);
statusFilter.addEventListener('change', smartSearch);
addDepartmentBtn.addEventListener('click', addDepartment);
exportBtn.addEventListener('click', exportToExcel);
departmentForm.addEventListener('submit', saveDepartment);
cancelBtn.addEventListener('click', () => {
    departmentModal.style.display = 'none';
});

// Close modal when clicking outside
window.addEventListener('click', (event) => {
    if (event.target === departmentModal) {
        departmentModal.style.display = 'none';
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    fetchDepartments();
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            searchInput.focus();
        }
        if (e.ctrlKey && e.key === 'n') {
            e.preventDefault();
            addDepartment();
        }
    });
});

// Hàm khởi tạo biểu đồ
function initializeCharts(departments) {
    // Dữ liệu cho biểu đồ phân bố nhân viên
    const employeeData = {
        labels: departments.map(dept => dept.name),
        datasets: [{
            label: 'Số nhân viên',
            data: departments.map(dept => dept.employee_count),
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',    // Hồng tươi
                'rgba(54, 162, 235, 0.8)',    // Xanh dương tươi
                'rgba(255, 206, 86, 0.8)',    // Vàng tươi
                'rgba(75, 192, 192, 0.8)',    // Ngọc lam
                'rgba(153, 102, 255, 0.8)',   // Tím tươi
                'rgba(255, 159, 64, 0.8)',    // Cam tươi
                'rgba(46, 204, 113, 0.8)',    // Xanh lá tươi
                'rgba(155, 89, 182, 0.8)'     // Tím hồng
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(46, 204, 113, 1)',
                'rgba(155, 89, 182, 1)'
            ],
            borderWidth: 2
        }]
    };

    // Dữ liệu cho biểu đồ trạng thái phòng ban
    const activeDepartments = departments.filter(dept => dept.status === 'active');
    const inactiveDepartments = departments.filter(dept => dept.status === 'inactive');
    
    const statusData = {
        labels: [
            ...activeDepartments.map(dept => `${dept.name} (Đang hoạt động)`),
            ...inactiveDepartments.map(dept => `${dept.name} (Không hoạt động)`)
        ],
        datasets: [{
            data: [
                ...activeDepartments.map(() => 1),
                ...inactiveDepartments.map(() => 1)
            ],
            backgroundColor: [
                ...activeDepartments.map(() => 'rgba(46, 204, 113, 0.8)'),  // Xanh lá tươi
                ...inactiveDepartments.map(() => 'rgba(231, 76, 60, 0.8)') // Đỏ tươi
            ],
            borderColor: [
                ...activeDepartments.map(() => 'rgba(46, 204, 113, 1)'),
                ...inactiveDepartments.map(() => 'rgba(231, 76, 60, 1)')
            ],
            borderWidth: 2
        }]
    };

    // Cấu hình chung cho biểu đồ
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    font: {
                        size: 12,
                        weight: 'bold'
                    },
                    boxWidth: 15,
                    padding: 15,
                    color: '#2c3e50'
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        return label;
                    }
                },
                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                titleColor: '#2c3e50',
                bodyColor: '#2c3e50',
                borderColor: '#e2e8f0',
                borderWidth: 1,
                padding: 10,
                cornerRadius: 8
            }
        }
    };

    // Khởi tạo biểu đồ phân bố nhân viên
    const departmentCtx = document.getElementById('departmentChart').getContext('2d');
    departmentChart = new Chart(departmentCtx, {
        type: isBarChart ? 'bar' : 'pie',
        data: employeeData,
        options: {
            ...commonOptions,
            scales: isBarChart ? {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        color: '#2c3e50',
                        font: {
                            weight: 'bold'
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    ticks: {
                        color: '#2c3e50',
                        font: {
                            weight: 'bold'
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                }
            } : undefined
        }
    });

    // Khởi tạo biểu đồ trạng thái
    const statusCtx = document.getElementById('departmentStatusChart').getContext('2d');
    departmentStatusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: statusData,
        options: {
            ...commonOptions,
            cutout: '60%',
            plugins: {
                ...commonOptions.plugins,
                title: {
                    display: true,
                    font: {
                        size: 16,
                        weight: 'bold',
                        family: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif"
                    },
                    color: '#2c3e50',
                    padding: {
                        top: 10,
                        bottom: 20
                    }
                }
            }
        }
    });
}

// Hàm cập nhật biểu đồ
function updateCharts(departments) {
    if (departmentChart) {
        departmentChart.destroy();
    }
    if (departmentStatusChart) {
        departmentStatusChart.destroy();
    }
    initializeCharts(departments);
}

// Xử lý sự kiện chuyển đổi loại biểu đồ
document.getElementById('toggleChartView').addEventListener('click', function() {
    isBarChart = !isBarChart;
    const departments = JSON.parse(localStorage.getItem('departments') || '[]');
    updateCharts(departments);
}); 