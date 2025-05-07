// Initialize DataTables
let allowanceTable;
let deductionTable;
let formulaTable;

$(document).ready(function() {
    // Initialize DataTables
    allowanceTable = $('#allowanceTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
        },
        order: [[0, 'asc']],
        pageLength: 10
    });

    deductionTable = $('#deductionTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
        },
        order: [[0, 'asc']],
        pageLength: 10
    });

    formulaTable = $('#formulaTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
        },
        order: [[0, 'asc']],
        pageLength: 10
    });

    // Load initial data
    loadAllowances();
    loadDeductions();
    loadFormulas();

    // Event handlers for allowance form
    $('#saveAllowance').click(function() {
        saveAllowance();
    });

    // Event handlers for deduction form
    $('#saveDeduction').click(function() {
        saveDeduction();
    });

    // Event handlers for formula form
    $('#formulaApplyTo').change(function() {
        const value = $(this).val();
        if (value === 'all') {
            $('#formulaApplyToValueContainer').hide();
        } else {
            $('#formulaApplyToValueContainer').show();
            loadApplyToOptions(value);
        }
    });

    $('#saveFormula').click(function() {
        saveFormula();
    });
});

// Load allowances
function loadAllowances() {
    showLoading();
    $.ajax({
        url: '/api/v1/salaries/allowances',
        method: 'GET',
        success: function(response) {
            allowanceTable.clear();
            response.data.forEach(allowance => {
                allowanceTable.row.add([
                    allowance.name,
                    allowance.description,
                    formatValue(allowance.value, allowance.value_type),
                    formatValueType(allowance.value_type),
                    formatStatus(allowance.status),
                    generateActionButtons('allowance', allowance.id)
                ]);
            });
            allowanceTable.draw();
            hideLoading();
        },
        error: function(xhr) {
            hideLoading();
            showError('Không thể tải danh sách phụ cấp');
        }
    });
}

// Load deductions
function loadDeductions() {
    showLoading();
    $.ajax({
        url: '/api/v1/salaries/deductions',
        method: 'GET',
        success: function(response) {
            deductionTable.clear();
            response.data.forEach(deduction => {
                deductionTable.row.add([
                    deduction.name,
                    deduction.description,
                    formatValue(deduction.value, deduction.value_type),
                    formatValueType(deduction.value_type),
                    formatStatus(deduction.status),
                    generateActionButtons('deduction', deduction.id)
                ]);
            });
            deductionTable.draw();
            hideLoading();
        },
        error: function(xhr) {
            hideLoading();
            showError('Không thể tải danh sách khấu trừ');
        }
    });
}

// Load formulas
function loadFormulas() {
    showLoading();
    $.ajax({
        url: '/api/v1/salaries/formulas',
        method: 'GET',
        success: function(response) {
            formulaTable.clear();
            response.data.forEach(formula => {
                formulaTable.row.add([
                    formula.name,
                    formula.description,
                    formula.expression,
                    formatApplyTo(formula.apply_to, formula.apply_to_value),
                    formatStatus(formula.status),
                    generateActionButtons('formula', formula.id)
                ]);
            });
            formulaTable.draw();
            hideLoading();
        },
        error: function(xhr) {
            hideLoading();
            showError('Không thể tải danh sách công thức');
        }
    });
}

// Load options for apply to value
function loadApplyToOptions(type) {
    const url = type === 'department' ? '/api/v1/departments' : '/api/v1/positions';
    $.ajax({
        url: url,
        method: 'GET',
        success: function(response) {
            const select = $('#formulaApplyToValue');
            select.empty();
            response.data.forEach(item => {
                select.append(`<option value="${item.id}">${item.name}</option>`);
            });
        },
        error: function(xhr) {
            showError('Không thể tải danh sách ' + (type === 'department' ? 'phòng ban' : 'vị trí'));
        }
    });
}

// Save allowance
function saveAllowance() {
    const data = {
        name: $('#allowanceName').val(),
        description: $('#allowanceDescription').val(),
        value: parseFloat($('#allowanceValue').val()),
        value_type: $('#allowanceType').val(),
        status: $('#allowanceStatus').val()
    };

    showLoading();
    $.ajax({
        url: '/api/salaries/allowances',
        method: 'POST',
        data: data,
        success: function(response) {
            hideLoading();
            showSuccess('Thêm phụ cấp thành công');
            $('#addAllowanceModal').modal('hide');
            $('#addAllowanceForm')[0].reset();
            loadAllowances();
        },
        error: function(xhr) {
            hideLoading();
            showError('Không thể thêm phụ cấp');
        }
    });
}

// Save deduction
function saveDeduction() {
    const data = {
        name: $('#deductionName').val(),
        description: $('#deductionDescription').val(),
        value: parseFloat($('#deductionValue').val()),
        value_type: $('#deductionType').val(),
        status: $('#deductionStatus').val()
    };

    showLoading();
    $.ajax({
        url: '/api/salaries/deductions',
        method: 'POST',
        data: data,
        success: function(response) {
            hideLoading();
            showSuccess('Thêm khấu trừ thành công');
            $('#addDeductionModal').modal('hide');
            $('#addDeductionForm')[0].reset();
            loadDeductions();
        },
        error: function(xhr) {
            hideLoading();
            showError('Không thể thêm khấu trừ');
        }
    });
}

// Save formula
function saveFormula() {
    const data = {
        name: $('#formulaName').val(),
        description: $('#formulaDescription').val(),
        expression: $('#formulaExpression').val(),
        apply_to: $('#formulaApplyTo').val(),
        apply_to_value: $('#formulaApplyTo').val() === 'all' ? null : $('#formulaApplyToValue').val(),
        status: $('#formulaStatus').val()
    };

    showLoading();
    $.ajax({
        url: '/api/salaries/formulas',
        method: 'POST',
        data: data,
        success: function(response) {
            hideLoading();
            showSuccess('Thêm công thức thành công');
            $('#addFormulaModal').modal('hide');
            $('#addFormulaForm')[0].reset();
            loadFormulas();
        },
        error: function(xhr) {
            hideLoading();
            showError('Không thể thêm công thức');
        }
    });
}

// Helper functions
function formatValue(value, type) {
    return type === 'percentage' ? value + '%' : formatCurrency(value);
}

function formatValueType(type) {
    return type === 'percentage' ? 'Phần trăm' : 'Cố định';
}

function formatStatus(status) {
    return status === 'active' ? 
        '<span class="badge bg-success">Kích hoạt</span>' : 
        '<span class="badge bg-danger">Vô hiệu hóa</span>';
}

function formatApplyTo(type, value) {
    if (type === 'all') return 'Tất cả nhân viên';
    if (type === 'department') return 'Phòng ban: ' + value;
    if (type === 'position') return 'Vị trí: ' + value;
    return '';
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

function generateActionButtons(type, id) {
    return `
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-info" onclick="view${type.charAt(0).toUpperCase() + type.slice(1)}(${id})">
                <i class="fas fa-eye"></i>
            </button>
            <button type="button" class="btn btn-sm btn-warning" onclick="edit${type.charAt(0).toUpperCase() + type.slice(1)}(${id})">
                <i class="fas fa-edit"></i>
            </button>
            <button type="button" class="btn btn-sm btn-danger" onclick="delete${type.charAt(0).toUpperCase() + type.slice(1)}(${id})">
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