// API Object
const api = {
    departments: {
        getAll: async () => {
            const response = await fetch('/qlnhansu_V3/backend/src/public/admin/api/departments.php');
            return await response.json();
        }
    },
    payroll: {
        getYears: async () => {
            const response = await fetch('/qlnhansu_V3/backend/src/public/admin/api/payroll.php?action=years');
            return await response.json();
        },
        getList: async (params) => {
            const queryString = new URLSearchParams(params).toString();
            const response = await fetch(`/qlnhansu_V3/backend/src/public/admin/api/payroll.php?${queryString}`);
            return await response.json();
        },
        add: async (data) => {
            const response = await fetch('/qlnhansu_V3/backend/src/public/admin/api/payroll.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            return await response.json();
        },
        update: async (id, data) => {
            const response = await fetch(`/qlnhansu_V3/backend/src/public/admin/api/payroll.php?id=${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            return await response.json();
        },
        delete: async (id) => {
            const response = await fetch(`/qlnhansu_V3/backend/src/public/admin/api/payroll.php?id=${id}`, {
                method: 'DELETE'
            });
            return await response.json();
        }
    }
};
// DOM Elements
const searchInput = document.getElementById('searchInput');
const departmentFilter = document.getElementById('departmentFilter');
const monthFilter = document.getElementById('monthFilter');
const yearFilter = document.getElementById('yearFilter');
const payrollTableBody = document.getElementById('payrollTableBody');
const pagination = document.getElementById('pagination');
const addPayrollBtn = document.getElementById('addPayrollBtn');
const calculatePayrollBtn = document.getElementById('calculatePayrollBtn');
const exportBtn = document.getElementById('exportBtn');
const reloadBtn = document.getElementById('reloadBtn');
const addPayrollModal = document.getElementById('addPayrollModal');
const closeModalBtn = document.getElementById('closeModalBtn');
const addPayrollForm = document.getElementById('addPayrollForm');
const cancelBtn = document.getElementById('cancelBtn');
const searchEmployeeBtn = document.getElementById('searchEmployeeBtn');
const employeeCodeInput = document.getElementById('employeeCode');
const employeeNameInput = document.getElementById('employeeName');
const departmentInput = document.getElementById('department');
const positionInput = document.getElementById('position');

// State variables
let currentPage = 1;
let itemsPerPage = 10;
let totalPages = 1;
let currentPayrollId = null;
let payrollData = [];
let monthlySalaryChart = null;
let departmentSalaryChart = null;
let salaryTrendChart = null;
let salaryComponentChart = null;

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    initializeElements();
    loadDepartments();
    loadPayrollData();
    loadFilters();
});


// Initialize DOM elements and event listeners
function initializeElements() {
    // Initialize search and filter elements
    if (searchInput) {
        searchInput.addEventListener('input', debounce(handleSearch, 300));
    }
    if (departmentFilter) {
        departmentFilter.addEventListener('change', handleFilter);
    }
    if (monthFilter) {
        monthFilter.addEventListener('change', handleFilter);
    }
    if (yearFilter) {
        yearFilter.addEventListener('change', handleFilter);
    }

    // Initialize button elements
    if (addPayrollBtn) {
        addPayrollBtn.addEventListener('click', () => {
            showAddPayrollModal();
            handleSalaryCalculations(); // Initialize salary calculations when modal opens
        });
    }
    if (calculatePayrollBtn) {
        calculatePayrollBtn.addEventListener('click', handleCalculatePayroll);
    }
    if (exportBtn) {
        exportBtn.addEventListener('click', handleExport);
    }
    if (reloadBtn) {
        reloadBtn.addEventListener('click', handleReload);
    }
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', hideAddPayrollModal);
    }
    if (cancelBtn) {
        cancelBtn.addEventListener('click', hideAddPayrollModal);
    }

    // Initialize form elements
    if (addPayrollForm) {
        addPayrollForm.addEventListener('submit', handleAddPayroll);
    }

    // Initialize modal close buttons
    const closeButtons = document.querySelectorAll('.close');
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });

    // Close modal when clicking outside
    window.addEventListener('click', (event) => {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });

    if (searchEmployeeBtn) {
        searchEmployeeBtn.addEventListener('click', handleSearchEmployee);
    }

    // Thêm event listener cho input mã nhân viên
    if (employeeCodeInput) {
        employeeCodeInput.addEventListener('input', debounce(handleSearchEmployee, 500));
    }

    // Xóa event listener cũ của nút tìm kiếm
    if (searchEmployeeBtn) {
        searchEmployeeBtn.removeEventListener('click', handleSearchEmployee);
    }
}

// API Functions
async function loadDepartments() {
    try {
        const response = await api.departments.getAll();
        if (response.success) {
            const departments = response.data;
            departmentFilter.innerHTML = '<option value="">Tất cả phòng ban</option>';
            departments.forEach(dept => {
                departmentFilter.innerHTML += `<option value="${dept.id}">${dept.name}</option>`;
            });
        }
    } catch (error) {
        showError('Không thể tải danh sách phòng ban');
    }
}

async function loadPayrollData() {
    showLoading();
    try {
        const params = {
            page: currentPage,
            limit: itemsPerPage,
            search: searchInput.value,
            department: departmentFilter.value,
            month: monthFilter.value,
            year: yearFilter.value
        };

        const response = await api.payroll.getList(params);
        
        if (response.success) {
            payrollData = response.data || [];
            totalPages = response.totalPages || 1;
            
            // Update dashboard cards
            updateDashboardCards(payrollData);
            
            // Create all charts
            createMonthlySalaryChart(payrollData);
            createDepartmentSalaryChart(payrollData);
            createSalaryTrendChart(payrollData);
            createSalaryComponentChart(payrollData);
            
            renderPayrollTable(payrollData);
            renderPagination(response.totalItems || 0);

            if (payrollData.length === 0) {
                showInfo('Không có dữ liệu lương thưởng');
            }
        } else {
            showError(response.message || 'Không thể tải dữ liệu lương');
        }
    } catch (error) {
        console.error('Error loading payroll data:', error);
        showError('Lỗi khi tải dữ liệu lương: ' + error.message);
    } finally {
        hideLoading();
    }
}

// Table Rendering
function renderPayrollTable(payrolls) {
    payrollTableBody.innerHTML = '';
    
    payrolls.forEach((payroll, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${(currentPage - 1) * itemsPerPage + index + 1}</td>
            <td>${payroll.employee.code}</td>
            <td>${payroll.employee.name}</td>
            <td>${payroll.employee.department}</td>
            <td>${payroll.period.month}</td>
            <td>${payroll.salary.base}</td>
            <td>${payroll.salary.allowances}</td>
            <td>${payroll.salary.bonuses}</td>
            <td>${payroll.salary.deductions}</td>
            <td>${payroll.salary.net}</td>
            <td>${payroll.created_by.username}</td>
            <td><span class="badge ${getStatusBadgeClass(payroll.status.code)}">${payroll.status.text}</span></td>
            <td>
                <div class="btn-group">
                    <button class="btn btn-sm" onclick="viewPayrollDetails(${payroll.id})" title="Xem chi tiết">
                        <img src="pic/info.png" alt="View" style="width:16px;height:16px;filter: invert(48%) sepia(79%) saturate(2476%) hue-rotate(190deg) brightness(118%) contrast(119%);">
                    </button>
                    <button class="btn btn-sm" onclick="editPayroll(${payroll.id})" title="Chỉnh sửa">
                        <img src="pic/edit.png" alt="Edit" style="width:16px;height:16px;filter: invert(67%) sepia(60%) saturate(456%) hue-rotate(358deg) brightness(104%) contrast(107%);">
                    </button>
                    <button class="btn btn-sm" onclick="deletePayroll(${payroll.id})" title="Xóa">
                        <img src="pic/delete.png" alt="Delete" style="width:16px;height:16px;filter: invert(27%) sepia(51%) saturate(2878%) hue-rotate(346deg) brightness(104%) contrast(97%);">
                    </button>
                </div>
            </td>
        `;
        payrollTableBody.appendChild(row);
    });
}

function renderPagination(totalItems) {
    totalPages = Math.ceil(totalItems / itemsPerPage);
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    pagination.innerHTML = `
        <div class="custom-pagination">
            <button class="page-btn prev" id="prevPageBtn" ${currentPage === 1 ? 'disabled' : ''}>
                &larr; Previous page
            </button>
            <button class="page-btn next" id="nextPageBtn" ${currentPage === totalPages ? 'disabled' : ''}>
                Next page &rarr;
            </button>
            <div class="page-info">
                <input type="number" id="currentPageInput" min="1" max="${totalPages}" value="${currentPage}" style="width:48px;text-align:center;" />
                <span>of <span id="totalPages">${totalPages}</span></span>
                <button class="arrow-btn" id="arrowPrevBtn" ${currentPage === 1 ? 'disabled' : ''}>&lt;</button>
                <button class="arrow-btn" id="arrowNextBtn" ${currentPage === totalPages ? 'disabled' : ''}>&gt;</button>
            </div>
        </div>
    `;
    // Gán sự kiện
    document.getElementById('prevPageBtn').onclick = function() {
        if (currentPage > 1) {
            changePage(currentPage - 1);
        }
    };
    document.getElementById('nextPageBtn').onclick = function() {
        if (currentPage < totalPages) {
            changePage(currentPage + 1);
        }
    };
    document.getElementById('arrowPrevBtn').onclick = function() {
        if (currentPage > 1) {
            changePage(currentPage - 1);
        }
    };
    document.getElementById('arrowNextBtn').onclick = function() {
        if (currentPage < totalPages) {
            changePage(currentPage + 1);
        }
    };
    document.getElementById('currentPageInput').onchange = function() {
        let val = parseInt(this.value, 10);
        if (val >= 1 && val <= totalPages) {
            changePage(val);
        } else {
            this.value = currentPage;
        }
    };
}

// Helper Functions
function formatCurrency(amount) {
    // Chuyển đổi chuỗi thành số nếu cần
    const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
    
    // Kiểm tra nếu không phải số hợp lệ
    if (isNaN(numAmount)) {
        return '0 ₫';
    }
    
    // Format số tiền với định dạng tiền tệ Việt Nam
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(numAmount);
}

function getStatusBadgeClass(status) {
    switch (status.toLowerCase()) {
        case 'approved':
            return 'bg-success';
        case 'pending':
            return 'bg-warning';
        case 'rejected':
            return 'bg-danger';
        case 'paid':
            return 'bg-info';
        default:
            return 'bg-secondary';
    }
}

function getStatusText(status) {
    const statusMap = {
        'pending': 'Đang chờ',
        'approved': 'Đã duyệt',
        'rejected': 'Từ chối',
        'paid': 'Đã thanh toán'
    };
    return statusMap[status.toLowerCase()] || status;
}

function getApproverName(approvalInfo) {
    if (!approvalInfo) return '-';
    const approvals = approvalInfo.split('|');
    const lastApproval = approvals[approvals.length - 1];
    const [level, status, name] = lastApproval.split(':');
    return status === 'approved' ? name : '-';
}

// Event Handlers
function handleSearch() {
    const searchTerm = searchInput.value.toLowerCase().trim();
    const rows = payrollTableBody.getElementsByTagName('tr');
    let hasResults = false;

    for (let row of rows) {
        const cells = row.getElementsByTagName('td');
        let rowVisible = false;

        // Skip the template row
        if (row.id === 'payrollRowTemplate') continue;

        // Search through all cells in the row
        for (let cell of cells) {
            const cellText = cell.textContent.toLowerCase();
            if (cellText.includes(searchTerm)) {
                rowVisible = true;
                break;
            }
        }

        // Show/hide row based on search result
        row.style.display = rowVisible ? '' : 'none';
        if (rowVisible) hasResults = true;
    }

    // Show/hide no results message
    const noResultsRow = document.getElementById('noResultsRow');
    if (noResultsRow) {
        noResultsRow.style.display = hasResults ? 'none' : '';
    }

    // Show notification if no results found
    if (!hasResults && searchTerm !== '') {
        showError('Không tìm thấy kết quả phù hợp với từ khóa: ' + searchTerm);
    }

    // Reset to first page when searching
    if (currentPage !== 1) {
        currentPage = 1;
        loadPayrollData();
    }
}

function handleFilter() {
    currentPage = 1;
    loadPayrollData();
}

function changePage(page) {
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    loadPayrollData();
}

function showAddPayrollModal() {
    addPayrollModal.style.display = 'block';
    loadSalaryComponents();
    loadPayrollPeriods();
}

function hideAddPayrollModal() {
    addPayrollModal.style.display = 'none';
    addPayrollForm.reset();
}

async function loadSalaryComponents() {
    try {
        // Load allowances
        const allowanceResponse = await fetch('/qlnhansu_V3/backend/src/public/api/payroll.php?type=allowance');
        const allowanceData = await allowanceResponse.json();
        if (allowanceData.success) {
            const allowanceSelect = document.getElementById('allowance');
            allowanceSelect.innerHTML = '';
            allowanceData.data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = `${item.name} (${formatCurrency(item.amount)})`;
                allowanceSelect.appendChild(option);
            });
        }

        // Load bonuses
        const bonusResponse = await fetch('/qlnhansu_V3/backend/src/public/api/payroll.php?type=bonus');
        const bonusData = await bonusResponse.json();
        if (bonusData.success) {
            const bonusSelect = document.getElementById('bonus');
            bonusSelect.innerHTML = '';
            bonusData.data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = `${item.name} (${formatCurrency(item.amount)})`;
                bonusSelect.appendChild(option);
            });
        }

        // Load deductions
        const deductionResponse = await fetch('/qlnhansu_V3/backend/src/public/api/payroll.php?type=deduction');
        const deductionData = await deductionResponse.json();
        if (deductionData.success) {
            const deductionSelect = document.getElementById('deduction');
            deductionSelect.innerHTML = '';
            deductionData.data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = `${item.name} (${formatCurrency(item.amount)})`;
                deductionSelect.appendChild(option);
            });
        }
    } catch (error) {
        showError('Không thể tải danh sách thành phần lương');
    }
}

async function loadPayrollPeriods() {
    try {
        const response = await fetch('/qlnhansu_V3/backend/src/public/api/payroll.php?action=periods');
        const data = await response.json();
        
        if (data.success) {
            const periodSelect = document.getElementById('payrollPeriod');
            periodSelect.innerHTML = '<option value="">Chọn kỳ lương</option>';
            data.data.forEach(period => {
                const option = document.createElement('option');
                option.value = period.id;
                option.textContent = `Tháng ${period.month}/${period.year}`;
                periodSelect.appendChild(option);
            });
        }
    } catch (error) {
        showError('Không thể tải danh sách kỳ lương');
    }
}

// Hàm tính tổng các khoản
function calculateTotal(type) {
    let total = 0;
    const elements = document.querySelectorAll(`#${type} option:checked`);
    elements.forEach(element => {
        const amount = parseFloat(element.dataset.amount) || 0;
        total += amount;
    });
    return total;
}

// Hàm tính lương thực lĩnh
function calculateNetSalary() {
    const basicSalary = parseFloat(document.getElementById('basicSalary').value) || 0;
    const allowances = calculateTotal('allowance');
    const bonuses = calculateTotal('bonus');
    const deductions = calculateTotal('deduction');
    
    return basicSalary + allowances + bonuses - deductions;
}

// Hàm cập nhật hiển thị tổng
function updateTotals() {
    try {
        // Lấy các elements
        const totalAllowanceElement = document.getElementById('totalAllowance');
        const totalBonusElement = document.getElementById('totalBonus');
        const totalDeductionElement = document.getElementById('totalDeduction');
        const totalIncomeElement = document.getElementById('totalIncome');
        const totalDeductionsElement = document.getElementById('totalDeductions');
        const netSalaryElement = document.getElementById('netSalary');

        // Kiểm tra các elements tồn tại
        if (!totalAllowanceElement || !totalBonusElement || !totalDeductionElement || 
            !totalIncomeElement || !totalDeductionsElement || !netSalaryElement) {
            console.warn('Some total elements not found');
            return;
        }

        // Tính toán các tổng
        const basicSalary = parseFloat(document.getElementById('basicSalary')?.value) || 0;
        const allowance = parseFloat(document.getElementById('allowance')?.value) || 0;
        const bonus = parseFloat(document.getElementById('bonus')?.value) || 0;
        const deduction = parseFloat(document.getElementById('deduction')?.value) || 0;

        // Tính tổng thu nhập và thực lĩnh
        const totalIncome = basicSalary + allowance + bonus;
        const netSalary = totalIncome - deduction;

        // Cập nhật hiển thị với định dạng tiền tệ
        totalAllowanceElement.textContent = formatCurrency(allowance);
        totalBonusElement.textContent = formatCurrency(bonus);
        totalDeductionElement.textContent = formatCurrency(deduction);
        totalIncomeElement.textContent = formatCurrency(totalIncome);
        totalDeductionsElement.textContent = formatCurrency(deduction);
        netSalaryElement.textContent = formatCurrency(netSalary);

    } catch (error) {
        console.error('Error updating totals:', error);
    }
}

// Thêm event listeners cho các trường input
document.addEventListener('DOMContentLoaded', function() {
    const basicSalaryInput = document.getElementById('basicSalary');
    const allowanceInput = document.getElementById('allowance');
    const bonusInput = document.getElementById('bonus');
    const deductionInput = document.getElementById('deduction');
    
    // Thêm event listeners nếu các elements tồn tại
    if (basicSalaryInput) {
        basicSalaryInput.addEventListener('input', function() {
            if (this.value < 0) this.value = 0;
            this.value = Math.round(this.value / 1000) * 1000;
            updateTotals();
        });
    }
    
    if (allowanceInput) {
        allowanceInput.addEventListener('input', function() {
            if (this.value < 0) this.value = 0;
            this.value = Math.round(this.value / 1000) * 1000;
            updateTotals();
        });
    }
    
    if (bonusInput) {
        bonusInput.addEventListener('input', function() {
            if (this.value < 0) this.value = 0;
            this.value = Math.round(this.value / 1000) * 1000;
            updateTotals();
        });
    }
    
    if (deductionInput) {
        deductionInput.addEventListener('input', function() {
            if (this.value < 0) this.value = 0;
            this.value = Math.round(this.value / 1000) * 1000;
            updateTotals();
        });
    }
});

// Hàm validate form
function validatePayrollForm() {
    const form = document.getElementById('addPayrollForm');
    if (!form) return false;

    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });

    // Validate số tiền
    const basicSalary = parseFloat(document.getElementById('basicSalary').value);
    if (isNaN(basicSalary) || basicSalary < 0) {
        document.getElementById('basicSalary').classList.add('is-invalid');
        isValid = false;
    }

    return isValid;
}

// Cập nhật hàm handleAddPayroll
async function handleAddPayroll(event) {
    event.preventDefault();
    
    if (!validatePayrollForm()) {
        showError('Vui lòng điền đầy đủ thông tin bắt buộc');
        return;
    }

    showLoading();

    try {
        const formData = new FormData(addPayrollForm);
        const data = {
            employee_id: formData.get('employee_id'),
            period_id: formData.get('payrollPeriod'),
            basicSalary: parseFloat(formData.get('basicSalary')),
            allowancesTotal: calculateTotal('allowance'),
            bonusesTotal: calculateTotal('bonus'),
            deductionsTotal: calculateTotal('deduction'),
            netSalary: calculateNetSalary(),
            notes: formData.get('notes')
        };

        const response = await api.payroll.add(data);

        if (response.success) {
            showSuccess('Thêm phiếu lương thành công');
            hideAddPayrollModal();
            loadPayrollData();
        } else {
            showError(response.message || 'Thêm phiếu lương thất bại');
        }
    } catch (error) {
        showError('Lỗi khi thêm phiếu lương');
        console.error(error);
    } finally {
        hideLoading();
    }
}

async function handleExport() {
    try {
        showLoading();
        
        // Lấy giá trị từ các trường tìm kiếm
        const search = document.getElementById('searchInput').value;
        const department = document.getElementById('departmentFilter').value;
        const month = document.getElementById('monthFilter').value;
        const year = document.getElementById('yearFilter').value;

        // Tạo FormData để gửi dữ liệu
        const formData = new FormData();
        formData.append('search', search);
        formData.append('department', department);
        formData.append('month', month);
        formData.append('year', year);

        // Gọi API xuất Excel
        const response = await fetch('/qlnhansu_V3/backend/src/public/admin/api/payroll.php?action=export', {
            method: 'POST',
            body: formData
        });

        // Kiểm tra content type của response
        const contentType = response.headers.get('content-type');
        
        if (contentType && contentType.includes('application/json')) {
            // Nếu là JSON thì có lỗi
            const errorData = await response.json();
            throw new Error(errorData.error || 'Lỗi khi xuất file Excel');
        }

        if (!response.ok) {
            throw new Error('Lỗi khi xuất file Excel');
        }

        // Lấy tên file từ header
        const contentDisposition = response.headers.get('content-disposition');
        let filename = 'payroll_export.xlsx';
        if (contentDisposition) {
            const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(contentDisposition);
            if (matches != null && matches[1]) {
                filename = matches[1].replace(/['"]/g, '');
            }
        }

        // Tạo blob và download file
        const blob = await response.blob();
        if (blob.size === 0) {
            throw new Error('File Excel trống');
        }

        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        showSuccess('Xuất file Excel thành công');
    } catch (error) {
        console.error('Export error:', error);
        showError(error.message || 'Lỗi khi xuất file Excel');
    } finally {
        hideLoading();
    }
}

function handleReload() {
    showInfo('Đang tải lại dữ liệu...');
    currentPage = 1;
    searchInput.value = '';
    departmentFilter.value = '';
    monthFilter.value = '';
    yearFilter.value = '';
    loadPayrollData();
}

// Utility Functions
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

// UI Functions
function showLoading() {
    console.log('Showing loading overlay...');
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'flex';
        // Add show class for animation
        loadingOverlay.classList.add('show');
        console.log('Loading overlay displayed');
    } else {
        console.error('Loading overlay element not found');
    }
}

function hideLoading() {
    console.log('Hiding loading overlay...');
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
        // Remove show class first for animation
        loadingOverlay.classList.remove('show');
        // Wait for animation to complete before hiding
        setTimeout(() => {
            loadingOverlay.style.display = 'none';
            console.log('Loading overlay hidden');
        }, 300);
    } else {
        console.error('Loading overlay element not found');
    }
}

function showError(message) {
    const notificationContainer = document.getElementById('notificationContainer');
    const notification = document.createElement('div');
    notification.className = 'alert alert-danger alert-dismissible fade show';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <img src="pic/delete-button.png" alt="Error" style="width: 24px; height: 24px; margin-right: 10px;">
            <div>${message}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    notificationContainer.appendChild(notification);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

function showSuccess(message) {
    const notificationContainer = document.getElementById('notificationContainer');
    const notification = document.createElement('div');
    notification.className = 'alert alert-success alert-dismissible fade show';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <img src="pic/check.png" alt="Success" style="width: 24px; height: 24px; margin-right: 10px;">
            <div>${message}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    notificationContainer.appendChild(notification);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

function showWarning(message) {
    const notificationContainer = document.getElementById('notificationContainer');
    const notification = document.createElement('div');
    notification.className = 'alert alert-warning alert-dismissible fade show';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <img src="pic/warning.png" alt="Warning" style="width: 24px; height: 24px; margin-right: 10px;">
            <div>${message}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    notificationContainer.appendChild(notification);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

function showInfo(message) {
    const notificationContainer = document.getElementById('notificationContainer');
    const notification = document.createElement('div');
    notification.className = 'alert alert-info alert-dismissible fade show';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-info-circle me-2"></i>
            <div>${message}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    notificationContainer.appendChild(notification);
    
    // Tự động ẩn sau 5 giây
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// CRUD Operations
async function viewPayrollDetails(id) {
    showLoading();
    try {
        const response = await fetch(`/qlnhansu_V3/backend/src/public/api/payroll.php?id=${id}`);
        const data = await response.json();

        if (data.success) {
            currentPayrollId = id;
            const payroll = data.data;
            
            // Hiển thị modal chi tiết
            const modal = document.getElementById('approvalDetailsModal');
            
            // Cập nhật thông tin chi tiết
            document.getElementById('approver1').textContent = payroll.created_by.username;
            document.getElementById('date1').textContent = formatDate(payroll.created_at);
            document.getElementById('comments1').textContent = payroll.notes || '-';

            // Cập nhật trạng thái
            const statusBadge = document.querySelector('.status-badge');
            statusBadge.className = `status-badge ${payroll.status.code}`;
            statusBadge.textContent = payroll.status.text;

            modal.style.display = 'block';
        } else {
            showError(data.message || 'Không thể xem chi tiết phiếu lương');
        }
    } catch (error) {
        showError('Lỗi khi xem chi tiết phiếu lương');
    } finally {
        hideLoading();
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

function updateDashboardCards(data) {
    if (!data || !Array.isArray(data)) {
        console.warn('Invalid statistics data:', data);
        // Set default values
        document.getElementById('totalSalary').textContent = formatCurrency(0);
        document.getElementById('totalBonus').textContent = formatCurrency(0);
        document.getElementById('averageSalary').textContent = formatCurrency(0);
        document.getElementById('totalPayrolls').textContent = '0';
        return;
    }

    try {
        // Tính toán tổng lương
        const totalSalary = data.reduce((sum, payroll) => {
            if (!payroll.salary) return sum;
            
            let netSalary = 0;
            if (typeof payroll.salary.net === 'string') {
                // Chuyển đổi chuỗi tiền tệ thành số
                netSalary = convertCurrencyToNumber(payroll.salary.net);
            } else if (typeof payroll.salary.net === 'number') {
                netSalary = payroll.salary.net;
            }
            
            return sum + (isNaN(netSalary) ? 0 : netSalary);
        }, 0);

        // Tính toán tổng thưởng
        const totalBonus = data.reduce((sum, payroll) => {
            if (!payroll.salary) return sum;
            
            let bonuses = 0;
            if (typeof payroll.salary.bonuses === 'string') {
                bonuses = convertCurrencyToNumber(payroll.salary.bonuses);
            } else if (typeof payroll.salary.bonuses === 'number') {
                bonuses = payroll.salary.bonuses;
            }
            
            return sum + (isNaN(bonuses) ? 0 : bonuses);
        }, 0);

        // Tính lương trung bình
        const averageSalary = data.length > 0 ? Math.round(totalSalary / data.length) : 0;

        // Cập nhật các card với định dạng tiền tệ
        document.getElementById('totalSalary').textContent = formatCurrency(totalSalary);
        document.getElementById('totalBonus').textContent = formatCurrency(totalBonus);
        document.getElementById('averageSalary').textContent = formatCurrency(averageSalary);
        document.getElementById('totalPayrolls').textContent = data.length;

    } catch (error) {
        console.error('Error updating dashboard cards:', error);
        // Set default values if there's an error
        document.getElementById('totalSalary').textContent = formatCurrency(0);
        document.getElementById('totalBonus').textContent = formatCurrency(0);
        document.getElementById('averageSalary').textContent = formatCurrency(0);
        document.getElementById('totalPayrolls').textContent = '0';
    }
}

// Hàm chuyển đổi chuỗi tiền tệ thành số
function convertCurrencyToNumber(currencyString) {
    if (!currencyString) return 0;
    
    // Loại bỏ tất cả ký tự không phải số
    const numericString = currencyString.replace(/[^\d]/g, '');
    
    // Chuyển đổi thành số
    const number = parseInt(numericString, 10);
    
    return isNaN(number) ? 0 : number;
}

async function editPayroll(id) {
    showLoading();
    try {
        const response = await fetch(`/qlnhansu_V3/backend/src/public/api/payroll.php?id=${id}`);
        const data = await response.json();

        if (data.success) {
            // Implement edit payroll
            console.log('Edit payroll:', data.data);
        } else {
            showError(data.message || 'Không thể chỉnh sửa phiếu lương');
        }
    } catch (error) {
        showError('Lỗi khi chỉnh sửa phiếu lương');
    } finally {
        hideLoading();
    }
}

async function deletePayroll(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa phiếu lương này?')) {
        showWarning('Đã hủy thao tác xóa');
        return;
    }

    showLoading();
    try {
        const response = await api.payroll.delete(id);

        if (response.success) {
            showSuccess('Xóa phiếu lương thành công');
            loadPayrollData();
        } else {
            showError(response.message || 'Xóa phiếu lương thất bại');
        }
    } catch (error) {
        showError('Lỗi khi xóa phiếu lương');
    } finally {
        hideLoading();
    }
}

async function handleCalculatePayroll() {
    if (!confirm('Bạn có chắc chắn muốn tính lương tự động cho tất cả nhân viên?')) {
        showWarning('Đã hủy thao tác tính lương tự động');
        return;
    }

    showLoading();
    try {
        const response = await fetch('/qlnhansu_V3/backend/src/public/api/payroll.php?action=calculate', {
            method: 'POST'
        });

        const data = await response.json();

        if (data.success) {
            showSuccess('Tính lương tự động thành công');
            loadPayrollData();
        } else {
            showError(data.message || 'Tính lương tự động thất bại');
        }
    } catch (error) {
        showError('Lỗi khi tính lương tự động');
    } finally {
        hideLoading();
    }
}

// Hàm tạo biểu đồ lương theo tháng
function createMonthlySalaryChart(data) {
    console.log('Starting createMonthlySalaryChart with data:', data);

    if (!data || !Array.isArray(data)) {
        console.warn('Invalid data for chart:', data);
        return;
    }

    const ctx = document.getElementById('monthlySalaryChart');
    if (!ctx) {
        console.error('Canvas element not found');
        return;
    }

    // Xử lý dữ liệu theo thời gian
    const yearFilter = document.getElementById('chartYearFilter').value;
    const monthRange = parseInt(document.getElementById('chartMonthRange').value);
    
    console.log('Chart filters:', { yearFilter, monthRange });

    // Lọc dữ liệu theo năm và khoảng thời gian
    const filteredData = data.filter(payroll => {
        if (!payroll.period || !payroll.period.month) {
            console.warn('Invalid payroll period:', payroll);
            return false;
        }

        const [month, year] = payroll.period.month.split('/');
        console.log('Processing payroll:', { month, year, payroll });
        
        return year === yearFilter;
    });

    console.log('Filtered data:', filteredData);

    // Chuẩn bị dữ liệu cho biểu đồ
    const monthlyData = {};
    filteredData.forEach(payroll => {
        const monthKey = payroll.period.month;
        
        if (!monthlyData[monthKey]) {
            monthlyData[monthKey] = {
                totalSalary: 0,
                totalBonus: 0,
                count: 0
            };
        }
        
        // Đảm bảo các giá trị là số
        const salary = convertCurrencyToNumber(payroll.salary?.net || 0);
        const bonus = convertCurrencyToNumber(payroll.salary?.bonuses || 0);
        
        monthlyData[monthKey].totalSalary += salary;
        monthlyData[monthKey].totalBonus += bonus;
        monthlyData[monthKey].count++;
    });

    console.log('Monthly aggregated data:', monthlyData);

    // Sắp xếp dữ liệu theo tháng
    const sortedMonths = Object.keys(monthlyData).sort((a, b) => {
        const [monthA, yearA] = a.split('/');
        const [monthB, yearB] = b.split('/');
        return (yearA - yearB) || (monthA - monthB);
    });

    console.log('Sorted months:', sortedMonths);

    // Chuẩn bị dữ liệu cho biểu đồ
    const labels = sortedMonths;
    const salaryData = sortedMonths.map(month => monthlyData[month].totalSalary);
    const bonusData = sortedMonths.map(month => monthlyData[month].totalBonus);
    const avgSalaryData = sortedMonths.map(month => 
        Math.round(monthlyData[month].totalSalary / monthlyData[month].count)
    );

    console.log('Chart data:', {
        labels,
        salaryData,
        bonusData,
        avgSalaryData
    });

    // Nếu đã có biểu đồ cũ, xóa nó
    if (monthlySalaryChart) {
        monthlySalaryChart.destroy();
    }

    try {
        // Tạo biểu đồ mới
        monthlySalaryChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Tổng lương',
                        data: salaryData,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Tổng thưởng',
                        data: bonusData,
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Lương trung bình',
                        data: avgSalaryData,
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                        type: 'line'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Biểu đồ lương theo tháng',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: {
                            top: 10,
                            bottom: 20
                        }
                    },
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#2c3e50',
                        bodyColor: '#495057',
                        borderColor: '#e0e0e0',
                        borderWidth: 1,
                        padding: 12,
                        boxPadding: 6,
                        usePointStyle: true,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += formatCurrency(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 12
                            },
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                }
            }
        });
        console.log('Chart created successfully');
    } catch (error) {
        console.error('Error creating chart:', error);
    }
}

// Thêm event listeners cho các bộ lọc
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    
    const yearFilter = document.getElementById('chartYearFilter');
    const monthRange = document.getElementById('chartMonthRange');

    if (yearFilter) {
        console.log('Year filter found');
        yearFilter.addEventListener('change', function() {
            console.log('Year filter changed:', this.value);
            loadPayrollData();
        });
    } else {
        console.warn('Year filter not found');
    }

    if (monthRange) {
        console.log('Month range found');
        monthRange.addEventListener('change', function() {
            console.log('Month range changed:', this.value);
            loadPayrollData();
        });
    } else {
        console.warn('Month range not found');
    }
});

// Hàm tạo biểu đồ phân bố lương theo phòng ban
function createDepartmentSalaryChart(data) {
    if (!data || !Array.isArray(data)) {
        console.warn('Invalid data for department chart:', data);
        return;
    }

    const ctx = document.getElementById('departmentSalaryChart');
    if (!ctx) {
        console.error('Department chart canvas not found');
        return;
    }

    const yearFilter = document.getElementById('departmentChartYear').value;
    const monthFilter = document.getElementById('departmentChartMonth').value;

    // Lọc dữ liệu theo năm và tháng
    const filteredData = data.filter(payroll => {
        const [month, year] = payroll.period.month.split('/');
        return year === yearFilter && (!monthFilter || month === monthFilter);
    });

    // Tổng hợp dữ liệu theo phòng ban
    const departmentData = {};
    filteredData.forEach(payroll => {
        const dept = payroll.employee.department;
        if (!departmentData[dept]) {
            departmentData[dept] = {
                totalSalary: 0,
                totalBonus: 0,
                employeeCount: 0
            };
        }
        
        departmentData[dept].totalSalary += convertCurrencyToNumber(payroll.salary.net);
        departmentData[dept].totalBonus += convertCurrencyToNumber(payroll.salary.bonuses);
        departmentData[dept].employeeCount++;
    });

    // Tính lương trung bình cho mỗi phòng ban
    const departments = Object.keys(departmentData);
    const avgSalaries = departments.map(dept => 
        Math.round(departmentData[dept].totalSalary / departmentData[dept].employeeCount)
    );
    const totalBonuses = departments.map(dept => departmentData[dept].totalBonus);
    const employeeCounts = departments.map(dept => departmentData[dept].employeeCount);

    // Nếu đã có biểu đồ cũ, xóa nó
    if (departmentSalaryChart) {
        departmentSalaryChart.destroy();
    }

    // Tạo biểu đồ mới
    departmentSalaryChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: departments,
            datasets: [
                {
                    label: 'Lương trung bình',
                    data: avgSalaries,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Tổng thưởng',
                    data: totalBonuses,
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Số nhân viên',
                    data: employeeCounts,
                    type: 'line',
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Phân bố lương theo phòng ban',
                    font: {
                        size: 16,
                        weight: 'bold'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.dataset.yAxisID === 'y1') {
                                label += context.parsed.y + ' người';
                            } else {
                                label += formatCurrency(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Lương (VNĐ)'
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Số nhân viên'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
}

// Hàm tạo biểu đồ xu hướng lương
function createSalaryTrendChart(data) {
    if (!data || !Array.isArray(data)) {
        console.warn('Invalid data for trend chart:', data);
        return;
    }

    const ctx = document.getElementById('salaryTrendChart');
    if (!ctx) {
        console.error('Trend chart canvas not found');
        return;
    }

    const yearFilter = document.getElementById('trendChartYear').value;
    const typeFilter = document.getElementById('trendChartType').value;

    // Lọc dữ liệu theo năm
    const filteredData = data.filter(payroll => {
        const [month, year] = payroll.period.month.split('/');
        return year === yearFilter;
    });

    // Tổng hợp dữ liệu theo tháng
    const monthlyData = {};
    filteredData.forEach(payroll => {
        const month = payroll.period.month;
        if (!monthlyData[month]) {
            monthlyData[month] = {
                baseSalary: 0,
                allowances: 0,
                bonuses: 0,
                count: 0
            };
        }
        
        monthlyData[month].baseSalary += convertCurrencyToNumber(payroll.salary.base);
        monthlyData[month].allowances += convertCurrencyToNumber(payroll.salary.allowances);
        monthlyData[month].bonuses += convertCurrencyToNumber(payroll.salary.bonuses);
        monthlyData[month].count++;
    });

    // Sắp xếp tháng
    const months = Object.keys(monthlyData).sort((a, b) => {
        const [monthA, yearA] = a.split('/');
        const [monthB, yearB] = b.split('/');
        return (yearA - yearB) || (monthA - monthB);
    });

    // Chuẩn bị dữ liệu cho biểu đồ
    const datasets = [];
    if (typeFilter === 'all' || typeFilter === 'base') {
        datasets.push({
            label: 'Lương cơ bản',
            data: months.map(month => monthlyData[month].baseSalary / monthlyData[month].count),
            borderColor: 'rgba(54, 162, 235, 1)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            fill: true
        });
    }
    if (typeFilter === 'all' || typeFilter === 'allowance') {
        datasets.push({
            label: 'Phụ cấp',
            data: months.map(month => monthlyData[month].allowances / monthlyData[month].count),
            borderColor: 'rgba(255, 99, 132, 1)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            fill: true
        });
    }
    if (typeFilter === 'all' || typeFilter === 'bonus') {
        datasets.push({
            label: 'Thưởng',
            data: months.map(month => monthlyData[month].bonuses / monthlyData[month].count),
            borderColor: 'rgba(75, 192, 192, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            fill: true
        });
    }

    // Nếu đã có biểu đồ cũ, xóa nó
    if (salaryTrendChart) {
        salaryTrendChart.destroy();
    }

    // Tạo biểu đồ mới
    salaryTrendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Xu hướng lương theo thời gian',
                    font: {
                        size: 16,
                        weight: 'bold'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += formatCurrency(context.parsed.y);
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Lương (VNĐ)'
                    }
                }
            }
        }
    });
}

// Hàm tạo biểu đồ phân tích thành phần lương
function createSalaryComponentChart(data) {
    if (!data || !Array.isArray(data)) {
        console.warn('Invalid data for component chart:', data);
        return;
    }

    const ctx = document.getElementById('salaryComponentChart');
    if (!ctx) {
        console.error('Component chart canvas not found');
        return;
    }

    const yearFilter = document.getElementById('componentChartYear').value;
    const monthFilter = document.getElementById('componentChartMonth').value;

    // Lọc dữ liệu theo năm và tháng
    const filteredData = data.filter(payroll => {
        const [month, year] = payroll.period.month.split('/');
        return year === yearFilter && (!monthFilter || month === monthFilter);
    });

    // Tính tổng các thành phần lương
    const totals = filteredData.reduce((acc, payroll) => {
        acc.baseSalary += convertCurrencyToNumber(payroll.salary.base);
        acc.allowances += convertCurrencyToNumber(payroll.salary.allowances);
        acc.bonuses += convertCurrencyToNumber(payroll.salary.bonuses);
        acc.deductions += convertCurrencyToNumber(payroll.salary.deductions);
        return acc;
    }, {
        baseSalary: 0,
        allowances: 0,
        bonuses: 0,
        deductions: 0
    });

    // Nếu đã có biểu đồ cũ, xóa nó
    if (salaryComponentChart) {
        salaryComponentChart.destroy();
    }

    // Tạo biểu đồ mới
    salaryComponentChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Lương cơ bản', 'Phụ cấp', 'Thưởng', 'Khấu trừ'],
            datasets: [{
                data: [
                    totals.baseSalary,
                    totals.allowances,
                    totals.bonuses,
                    totals.deductions
                ],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(255, 159, 64, 0.5)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Phân tích thành phần lương',
                    font: {
                        size: 16,
                        weight: 'bold'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${formatCurrency(value)} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

// Thêm event listeners cho các bộ lọc mới
document.addEventListener('DOMContentLoaded', function() {
    // Event listeners cho biểu đồ phòng ban
    const departmentYearFilter = document.getElementById('departmentChartYear');
    const departmentMonthFilter = document.getElementById('departmentChartMonth');
    
    if (departmentYearFilter) {
        departmentYearFilter.addEventListener('change', loadPayrollData);
    }
    if (departmentMonthFilter) {
        departmentMonthFilter.addEventListener('change', loadPayrollData);
    }

    // Event listeners cho biểu đồ xu hướng
    const trendYearFilter = document.getElementById('trendChartYear');
    const trendTypeFilter = document.getElementById('trendChartType');
    
    if (trendYearFilter) {
        trendYearFilter.addEventListener('change', loadPayrollData);
    }
    if (trendTypeFilter) {
        trendTypeFilter.addEventListener('change', loadPayrollData);
    }

    // Event listeners cho biểu đồ thành phần
    const componentYearFilter = document.getElementById('componentChartYear');
    const componentMonthFilter = document.getElementById('componentChartMonth');
    
    if (componentYearFilter) {
        componentYearFilter.addEventListener('change', loadPayrollData);
    }
    if (componentMonthFilter) {
        componentMonthFilter.addEventListener('change', loadPayrollData);
    }
});

async function loadFilters() {
    try {
        // Load departments
        const deptResponse = await api.departments.getAll();
        const departmentSelect = document.getElementById("departmentFilter");
        departmentSelect.innerHTML = '<option value="">Tất cả phòng ban</option>';
        deptResponse.data.forEach(dept => {
            const option = document.createElement("option");
            option.value = dept.department_id;
            option.textContent = dept.name;
            departmentSelect.appendChild(option);
        });

        // Load years from payroll data
        const yearResponse = await api.payroll.getYears();
        const yearSelect = document.getElementById("yearFilter");
        yearSelect.innerHTML = '<option value="">Chọn năm</option>';
        yearResponse.data.forEach(year => {
            let yearValue = year;
            if (typeof year === 'object' && year !== null) {
                yearValue = year.year || year.value || Object.values(year)[0];
            }
            const option = document.createElement("option");
            option.value = yearValue;
            option.textContent = yearValue;
            yearSelect.appendChild(option);
        });

        // Set current year as default
        const currentYear = new Date().getFullYear();
        yearSelect.value = currentYear;
    } catch (error) {
        console.error("Error loading filters:", error);
        showError("Không thể tải dữ liệu bộ lọc");
    }
}

async function handleSearchEmployee() {
    const employeeCode = employeeCodeInput.value.trim();
    
    if (!employeeCode) {
        showError('Vui lòng nhập mã nhân viên');
        return;
    }

    showLoading();

    try {
        const response = await fetch(`/qlnhansu_V3/backend/src/public/admin/api/payroll.php?action=searchEmployee&employeeCode=${encodeURIComponent(employeeCode)}`);
        const data = await response.json();
        
        if (data.success) {
            const { employee, payrollHistory } = data.data;
            
            // Cập nhật thông tin nhân viên
            employeeNameInput.value = employee.name || employee.email;
            departmentInput.value = employee.department_name || '';
            positionInput.value = employee.position_name || '';
            
            // Hiển thị section lịch sử lương
            const payrollHistorySection = document.getElementById('payrollHistorySection');
            const payrollHistoryContainer = document.getElementById('payrollHistoryContainer');
            
            if (payrollHistory && payrollHistory.length > 0) {
                // Tạo bảng lịch sử lương
                const table = document.createElement('table');
                table.className = 'table table-striped table-bordered';
                table.innerHTML = `
                    <thead>
                        <tr>
                            <th>Kỳ lương</th>
                            <th>Lương cơ bản</th>
                            <th>Phụ cấp</th>
                            <th>Thưởng</th>
                            <th>Khấu trừ</th>
                            <th>Thực lĩnh</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody id="payrollHistoryBody">
                        ${payrollHistory.slice(0, 3).map(payroll => `
                            <tr>
                                <td>${formatDate(payroll.pay_period_start)} - ${formatDate(payroll.pay_period_end)}</td>
                                <td class="text-end">${formatCurrency(payroll.basic_salary)}</td>
                                <td class="text-end">${formatCurrency(payroll.allowances)}</td>
                                <td class="text-end">${formatCurrency(payroll.bonuses)}</td>
                                <td class="text-end">${formatCurrency(payroll.deductions)}</td>
                                <td class="text-end">${formatCurrency(payroll.net_salary)}</td>
                                <td class="text-center">
                                    <span class="badge ${getStatusBadgeClass(payroll.status)}">
                                        ${getStatusText(payroll.status)}
                                    </span>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                `;
                
                // Tạo nút xem thêm/thu gọn
                const buttonContainer = document.createElement('div');
                buttonContainer.className = 'text-center mt-3';
                
                if (payrollHistory.length > 3) {
                    buttonContainer.innerHTML = `
                        <button type="button" class="btn btn-link" id="togglePayrollHistory">
                            Xem thêm <i class="fas fa-chevron-down"></i>
                        </button>
                    `;
                }
                
                payrollHistoryContainer.innerHTML = '';
                payrollHistoryContainer.appendChild(table);
                payrollHistoryContainer.appendChild(buttonContainer);
                payrollHistorySection.style.display = 'block';

                // Thêm sự kiện cho nút xem thêm/thu gọn
                if (payrollHistory.length > 3) {
                    const toggleButton = document.getElementById('togglePayrollHistory');
                    let currentIndex = 3;
                    let isExpanded = false;

                    toggleButton.addEventListener('click', () => {
                        const tbody = document.getElementById('payrollHistoryBody');
                        
                        if (!isExpanded) {
                            // Xem thêm
                            const nextItems = payrollHistory.slice(currentIndex, currentIndex + 5);
                            if (nextItems.length > 0) {
                                nextItems.forEach(payroll => {
                                    const row = document.createElement('tr');
                                    row.innerHTML = `
                                        <td>${formatDate(payroll.pay_period_start)} - ${formatDate(payroll.pay_period_end)}</td>
                                        <td class="text-end">${formatCurrency(payroll.basic_salary)}</td>
                                        <td class="text-end">${formatCurrency(payroll.allowances)}</td>
                                        <td class="text-end">${formatCurrency(payroll.bonuses)}</td>
                                        <td class="text-end">${formatCurrency(payroll.deductions)}</td>
                                        <td class="text-end">${formatCurrency(payroll.net_salary)}</td>
                                        <td class="text-center">
                                            <span class="badge ${getStatusBadgeClass(payroll.status)}">
                                                ${getStatusText(payroll.status)}
                                            </span>
                                        </td>
                                    `;
                                    tbody.appendChild(row);
                                });
                                currentIndex += 5;
                                
                                // Cập nhật nút nếu đã hiển thị hết
                                if (currentIndex >= payrollHistory.length) {
                                    toggleButton.innerHTML = 'Thu gọn <i class="fas fa-chevron-up"></i>';
                                    isExpanded = true;
                                }
                            }
                        } else {
                            // Thu gọn
                            while (tbody.children.length > 3) {
                                tbody.removeChild(tbody.lastChild);
                            }
                            currentIndex = 3;
                            toggleButton.innerHTML = 'Xem thêm <i class="fas fa-chevron-down"></i>';
                            isExpanded = false;
                        }
                    });
                }
            } else {
                payrollHistoryContainer.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Nhân viên chưa có bản ghi lương nào
                    </div>
                `;
                payrollHistorySection.style.display = 'block';
            }
        } else {
            showError(data.message || 'Không tìm thấy thông tin nhân viên');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Lỗi khi tìm kiếm thông tin nhân viên');
    } finally {
        hideLoading();
    }
}

// Thêm CSS cho bảng lịch sử lương
const style = document.createElement('style');
style.textContent = `
    #payrollHistorySection {
        margin-top: 20px;
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 5px;
    }

    #payrollHistorySection h4 {
        color: #2c3e50;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    #payrollHistorySection h4 i {
        color: #3498db;
    }

    #payrollHistoryContainer {
        margin-top: 10px;
    }

    #payrollHistoryContainer .table {
        margin-bottom: 0;
    }

    #payrollHistoryContainer .table th {
        background-color: #f1f1f1;
        font-weight: 600;
        text-align: center;
    }

    #payrollHistoryContainer .table td {
        vertical-align: middle;
    }

    #payrollHistoryContainer .badge {
        padding: 5px 10px;
        font-size: 0.85em;
    }

    #payrollHistoryContainer .alert {
        margin-bottom: 0;
    }

    .text-end {
        text-align: right !important;
    }

    .text-center {
        text-align: center !important;
    }

    #togglePayrollHistory {
        color: #3498db;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    #togglePayrollHistory:hover {
        color: #2980b9;
        text-decoration: underline;
    }

    #togglePayrollHistory i {
        margin-left: 5px;
        transition: transform 0.3s ease;
    }

    #togglePayrollHistory:hover i {
        transform: translateY(2px);
    }
`;
document.head.appendChild(style);

// Xử lý tính toán lương
function handleSalaryCalculations() {
    // Lấy các input elements
    const basicSalaryInput = document.getElementById('basicSalary');
    const allowanceInput = document.getElementById('allowance');
    const bonusInput = document.getElementById('bonus');
    const deductionInput = document.getElementById('deduction');
    
    // Lấy các elements hiển thị kết quả
    const totalIncomeElement = document.getElementById('totalIncome');
    const totalDeductionsElement = document.getElementById('totalDeductions');
    const netSalaryElement = document.getElementById('netSalary');

    // Hàm xử lý input
    function handleInput(input) {
        // Chỉ cho phép nhập số và dấu chấm
        input.value = input.value.replace(/[^\d.]/g, '');
        
        // Format giá trị
        formatInputValue(input);
        
        // Cập nhật hiển thị
        updateDisplay();
    }

    // Hàm format số tiền khi nhập
    function formatInputValue(input) {
        if (!input) return;
        
        // Lấy giá trị hiện tại và xóa tất cả ký tự không phải số và dấu chấm
        let value = input.value.replace(/[^\d.]/g, '');
        
        // Chuyển đổi thành số
        let numValue = parseFloat(value) || 0;
        
        // Lưu giá trị số vào data attribute
        input.dataset.numericValue = numValue;
        
        // Format lại với dấu phẩy ngăn cách
        input.value = numValue.toLocaleString('vi-VN');
        
        return numValue;
    }

    // Thêm event listeners cho các input
    [basicSalaryInput, allowanceInput, bonusInput, deductionInput].forEach(input => {
        if (!input) return;

        // Remove existing event listeners
        input.removeEventListener('input', input._inputHandler);
        input.removeEventListener('focus', input._focusHandler);
        input.removeEventListener('blur', input._blurHandler);

        // Create new event handlers
        input._inputHandler = function() {
            handleInput(this);
        };
        input._focusHandler = function() {
            // Khi focus, hiển thị giá trị số không có định dạng
            this.value = this.dataset.numericValue || '0';
        };
        input._blurHandler = function() {
            formatInputValue(this);
            updateDisplay();
        };

        // Add new event listeners
        input.addEventListener('input', input._inputHandler);
        input.addEventListener('focus', input._focusHandler);
        input.addEventListener('blur', input._blurHandler);
    });

    // Hàm tính toán tổng thu nhập
    function calculateTotalIncome() {
        const basicSalary = parseInt(basicSalaryInput?.dataset.numericValue || '0');
        const allowance = parseInt(allowanceInput?.dataset.numericValue || '0');
        const bonus = parseInt(bonusInput?.dataset.numericValue || '0');
        return basicSalary + allowance + bonus;
    }

    // Hàm tính toán thực lĩnh
    function calculateNetSalary() {
        const totalIncome = calculateTotalIncome();
        const deduction = parseInt(deductionInput?.dataset.numericValue || '0');
        return totalIncome - deduction;
    }

    // Hàm cập nhật hiển thị
    function updateDisplay() {
        if (!totalIncomeElement || !totalDeductionsElement || !netSalaryElement) return;

        const totalIncome = calculateTotalIncome();
        const deduction = parseInt(deductionInput?.dataset.numericValue || '0');
        const netSalary = calculateNetSalary();

        totalIncomeElement.textContent = formatCurrency(totalIncome);
        totalDeductionsElement.textContent = formatCurrency(deduction);
        netSalaryElement.textContent = formatCurrency(netSalary);
    }

    // Validate input khi submit form
    const form = document.getElementById('addPayrollForm');
    if (form) {
        form.removeEventListener('submit', form._submitHandler);
        form._submitHandler = function(e) {
            e.preventDefault();
            
            // Kiểm tra các trường bắt buộc
            if (!basicSalaryInput?.dataset.numericValue) {
                basicSalaryInput?.classList.add('is-invalid');
                return;
            } else {
                basicSalaryInput?.classList.remove('is-invalid');
            }

            // Tạo object chứa thông tin lương
            const salaryData = {
                basicSalary: parseInt(basicSalaryInput?.dataset.numericValue || '0'),
                allowance: parseInt(allowanceInput?.dataset.numericValue || '0'),
                bonus: parseInt(bonusInput?.dataset.numericValue || '0'),
                deduction: parseInt(deductionInput?.dataset.numericValue || '0'),
                totalIncome: calculateTotalIncome(),
                netSalary: calculateNetSalary()
            };

            // Gọi API để lưu thông tin lương
            savePayrollData(salaryData);
        };
        form.addEventListener('submit', form._submitHandler);
    }

    // Initial update of display
    updateDisplay();
}

// Khởi tạo xử lý tính toán lương khi modal được mở
document.getElementById('addPayrollBtn')?.addEventListener('click', function() {
    handleSalaryCalculations();
});  
