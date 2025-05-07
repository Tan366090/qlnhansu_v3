// Initialize DataTable
let salaryTable;
$(document).ready(function() {
    salaryTable = $('#salaryTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
        },
        order: [[3, 'desc']], // Sort by month by default
        pageLength: 10
    });

    // Load initial data
    loadEmployees();
    loadDepartments();
    loadSalaries();

    // Initialize date pickers
    flatpickr("#month, #editMonth, #filterMonth", {
        dateFormat: "Y-m",
        defaultDate: "today"
    });
});

// Load employees for dropdowns
function loadEmployees() {
    $.ajax({
        url: '/api/v1/employees',
        method: 'GET',
        success: function(response) {
            const employees = response.data;
            const employeeSelects = ['#employee', '#editEmployee', '#filterEmployee'];
            
            employeeSelects.forEach(select => {
                $(select).empty().append('<option value="">Tất cả</option>');
                employees.forEach(employee => {
                    $(select).append(`<option value="${employee.id}">${employee.code} - ${employee.name}</option>`);
                });
            });
        },
        error: function(xhr) {
            showError('Không thể tải danh sách nhân viên');
        }
    });
}

// Load departments for dropdown
function loadDepartments() {
    $.ajax({
        url: '/api/v1/departments',
        method: 'GET',
        success: function(response) {
            const departments = response.data;
            $('#filterDepartment').empty().append('<option value="">Tất cả</option>');
            departments.forEach(dept => {
                $('#filterDepartment').append(`<option value="${dept.id}">${dept.name}</option>`);
            });
        },
        error: function(xhr) {
            showError('Không thể tải danh sách phòng ban');
        }
    });
}

// Load salary data
function loadSalaries() {
    showLoading();
    $.ajax({
        url: '/api/v1/salaries',
        method: 'GET',
        success: function(response) {
            const salaries = response.data;
            salaryTable.clear();
            
            salaries.forEach(salary => {
                const totalSalary = salary.basic_salary + salary.allowance + salary.bonus - salary.deduction;
                salaryTable.row.add([
                    salary.employee_code,
                    salary.employee_name,
                    salary.department_name,
                    formatMonth(salary.month),
                    formatCurrency(salary.basic_salary),
                    formatCurrency(salary.allowance),
                    formatCurrency(salary.bonus),
                    formatCurrency(salary.deduction),
                    formatCurrency(totalSalary),
                    generateStatusBadge(salary.status),
                    generateActionButtons(salary.id)
                ]);
            });
            
            salaryTable.draw();
            hideLoading();
        },
        error: function(xhr) {
            hideLoading();
            showError('Không thể tải danh sách lương');
        }
    });
}

// Save new salary
$('#saveSalary').click(function() {
    const form = $('#addSalaryForm');
    if (!form[0].checkValidity()) {
        form[0].reportValidity();
        return;
    }

    const data = {
        employee_id: $('#employee').val(),
        month: $('#month').val(),
        basic_salary: $('#basicSalary').val(),
        allowance: $('#allowance').val() || 0,
        bonus: $('#bonus').val() || 0,
        deduction: $('#deduction').val() || 0,
        notes: $('#notes').val()
    };

    showLoading();
    $.ajax({
        url: '/api/salaries',
        method: 'POST',
        data: data,
        success: function(response) {
            hideLoading();
            $('#addSalaryModal').modal('hide');
            form[0].reset();
            showSuccess('Thêm bảng lương thành công');
            loadSalaries();
        },
        error: function(xhr) {
            hideLoading();
            showError(xhr.responseJSON?.message || 'Không thể thêm bảng lương');
        }
    });
});

// View salary details
function viewSalary(id) {
    showLoading();
    $.ajax({
        url: `/api/v1/salaries/${id}`,
        method: 'GET',
        success: function(response) {
            const salary = response.data;
            const totalSalary = salary.basic_salary + salary.allowance + salary.bonus - salary.deduction;
            
            $('#viewEmployee').text(`${salary.employee_code} - ${salary.employee_name}`);
            $('#viewMonth').text(formatMonth(salary.month));
            $('#viewBasicSalary').text(formatCurrency(salary.basic_salary));
            $('#viewAllowance').text(formatCurrency(salary.allowance));
            $('#viewBonus').text(formatCurrency(salary.bonus));
            $('#viewDeduction').text(formatCurrency(salary.deduction));
            $('#viewTotalSalary').text(formatCurrency(totalSalary));
            $('#viewStatus').html(generateStatusBadge(salary.status));
            $('#viewNotes').text(salary.notes || 'Không có ghi chú');
            
            $('#viewSalaryModal').modal('show');
            hideLoading();
        },
        error: function(xhr) {
            hideLoading();
            showError('Không thể tải thông tin bảng lương');
        }
    });
}

// Edit salary
function editSalary(id) {
    showLoading();
    $.ajax({
        url: `/api/v1/salaries/${id}`,
        method: 'GET',
        success: function(response) {
            const salary = response.data;
            
            $('#editSalaryId').val(salary.id);
            $('#editEmployee').val(salary.employee_id);
            $('#editMonth').val(salary.month);
            $('#editBasicSalary').val(salary.basic_salary);
            $('#editAllowance').val(salary.allowance);
            $('#editBonus').val(salary.bonus);
            $('#editDeduction').val(salary.deduction);
            $('#editNotes').val(salary.notes);
            
            $('#editSalaryModal').modal('show');
            hideLoading();
        },
        error: function(xhr) {
            hideLoading();
            showError('Không thể tải thông tin bảng lương');
        }
    });
}

// Update salary
$('#updateSalary').click(function() {
    const form = $('#editSalaryForm');
    if (!form[0].checkValidity()) {
        form[0].reportValidity();
        return;
    }

    $('#confirmEditModal').modal('show');
});

$('#confirmEdit').click(function() {
    const id = $('#editSalaryId').val();
    const data = {
        employee_id: $('#editEmployee').val(),
        month: $('#editMonth').val(),
        basic_salary: $('#editBasicSalary').val(),
        allowance: $('#editAllowance').val() || 0,
        bonus: $('#editBonus').val() || 0,
        deduction: $('#editDeduction').val() || 0,
        notes: $('#editNotes').val()
    };

    showLoading();
    $.ajax({
        url: `/api/salaries/${id}`,
        method: 'PUT',
        data: data,
        success: function(response) {
            hideLoading();
            $('#editSalaryModal').modal('hide');
            $('#confirmEditModal').modal('hide');
            showSuccess('Cập nhật bảng lương thành công');
            loadSalaries();
        },
        error: function(xhr) {
            hideLoading();
            showError(xhr.responseJSON?.message || 'Không thể cập nhật bảng lương');
        }
    });
});

// Delete salary
function deleteSalary(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa bảng lương này?')) {
        return;
    }

    showLoading();
    $.ajax({
        url: `/api/salaries/${id}`,
        method: 'DELETE',
        success: function(response) {
            hideLoading();
            showSuccess('Xóa bảng lương thành công');
            loadSalaries();
        },
        error: function(xhr) {
            hideLoading();
            showError('Không thể xóa bảng lương');
        }
    });
}

// Filter salaries
$('#filterForm').submit(function(e) {
    e.preventDefault();
    const filters = {
        employee_id: $('#filterEmployee').val(),
        department_id: $('#filterDepartment').val(),
        month: $('#filterMonth').val(),
        status: $('#filterStatus').val()
    };

    showLoading();
    $.ajax({
        url: '/api/v1/salaries',
        method: 'GET',
        data: filters,
        success: function(response) {
            const salaries = response.data;
            salaryTable.clear();
            
            salaries.forEach(salary => {
                const totalSalary = salary.basic_salary + salary.allowance + salary.bonus - salary.deduction;
                salaryTable.row.add([
                    salary.employee_code,
                    salary.employee_name,
                    salary.department_name,
                    formatMonth(salary.month),
                    formatCurrency(salary.basic_salary),
                    formatCurrency(salary.allowance),
                    formatCurrency(salary.bonus),
                    formatCurrency(salary.deduction),
                    formatCurrency(totalSalary),
                    generateStatusBadge(salary.status),
                    generateActionButtons(salary.id)
                ]);
            });
            
            salaryTable.draw();
            hideLoading();
        },
        error: function(xhr) {
            hideLoading();
            showError('Không thể tải danh sách lương');
        }
    });
});

// Reset filters
$('#resetFilters').click(function() {
    $('#filterForm')[0].reset();
    loadSalaries();
});

// Helper functions
function formatMonth(dateString) {
    const [year, month] = dateString.split('-');
    return `${month}/${year}`;
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

function generateStatusBadge(status) {
    const statusMap = {
        pending: ['warning', 'Chờ duyệt'],
        approved: ['info', 'Đã duyệt'],
        paid: ['success', 'Đã thanh toán']
    };
    
    const [className, text] = statusMap[status] || ['secondary', status];
    return `<span class="badge bg-${className}">${text}</span>`;
}

function generateActionButtons(id) {
    return `
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-info" onclick="viewSalary(${id})">
                <i class="fas fa-eye"></i>
            </button>
            <button type="button" class="btn btn-sm btn-primary" onclick="editSalary(${id})">
                <i class="fas fa-edit"></i>
            </button>
            <button type="button" class="btn btn-sm btn-danger" onclick="deleteSalary(${id})">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
}

// UI helper functions
function showLoading() {
    $('.loading-spinner').show();
}

function hideLoading() {
    $('.loading-spinner').hide();
}

function showError(message) {
    const errorDiv = $('.error-message');
    errorDiv.text(message).show();
    setTimeout(() => errorDiv.fadeOut(), 5000);
}

function showSuccess(message) {
    const successDiv = $('.success-message');
    successDiv.text(message).show();
    setTimeout(() => successDiv.fadeOut(), 5000);
} 