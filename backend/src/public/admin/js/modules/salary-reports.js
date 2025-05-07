// Initialize DataTable and Charts
let reportTable;
let departmentChart;
let trendChart;

$(document).ready(function() {
    // Initialize DataTable
    reportTable = $('#reportTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
        },
        order: [[6, 'desc']], // Sort by total salary by default
        pageLength: 10
    });

    // Initialize Charts
    const departmentCtx = document.getElementById('departmentChart').getContext('2d');
    const trendCtx = document.getElementById('trendChart').getContext('2d');

    departmentChart = new Chart(departmentCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Tổng lương theo phòng ban',
                data: [],
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                }
            }
        }
    });

    trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Tổng lương',
                data: [],
                fill: false,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                }
            }
        }
    });

    // Load initial data
    loadYears();
    loadDepartments();
    loadReports();

    // Event handlers
    $('#filterForm').submit(function(e) {
        e.preventDefault();
        loadReports();
    });

    $('#resetFilters').click(function() {
        $('#filterForm')[0].reset();
        loadReports();
    });

    $('#exportExcel').click(function() {
        exportToExcel();
    });

    $('#printReport').click(function() {
        window.print();
    });

    $('#filterChartType').change(function() {
        updateChartType();
    });
});

// Load years for dropdown
function loadYears() {
    const currentYear = new Date().getFullYear();
    const yearSelect = $('#filterYear');
    
    for (let year = currentYear; year >= currentYear - 5; year--) {
        yearSelect.append(`<option value="${year}">${year}</option>`);
    }
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

// Load report data
function loadReports() {
    showLoading();
    const filters = {
        year: $('#filterYear').val(),
        department_id: $('#filterDepartment').val(),
        report_type: $('#filterReportType').val()
    };

    $.ajax({
        url: '/api/v1/salaries/reports',
        method: 'GET',
        data: filters,
        success: function(response) {
            const data = response.data;
            
            // Update summary cards
            updateSummaryCards(data.summary);
            
            // Update department chart
            updateDepartmentChart(data.department_data);
            
            // Update trend chart
            updateTrendChart(data.trend_data);
            
            // Update detailed report table
            updateReportTable(data.detailed_report);
            
            hideLoading();
        },
        error: function(xhr) {
            hideLoading();
            showError('Không thể tải báo cáo');
        }
    });
}

// Update summary cards
function updateSummaryCards(summary) {
    $('#totalSalary').text(formatCurrency(summary.total_salary));
    $('#totalAllowance').text(formatCurrency(summary.total_allowance));
    $('#totalBonus').text(formatCurrency(summary.total_bonus));
    $('#totalDeduction').text(formatCurrency(summary.total_deduction));
    
    $('#totalSalaryChange').text(formatChange(summary.salary_change));
    $('#totalAllowanceChange').text(formatChange(summary.allowance_change));
    $('#totalBonusChange').text(formatChange(summary.bonus_change));
    $('#totalDeductionChange').text(formatChange(summary.deduction_change));
}

// Update department chart
function updateDepartmentChart(data) {
    const chartType = $('#filterChartType').val();
    departmentChart.config.type = chartType;
    
    departmentChart.data.labels = data.map(item => item.department_name);
    departmentChart.data.datasets[0].data = data.map(item => item.total_salary);
    departmentChart.update();
}

// Update trend chart
function updateTrendChart(data) {
    const chartType = $('#filterChartType').val();
    trendChart.config.type = chartType;
    
    trendChart.data.labels = data.map(item => item.period);
    trendChart.data.datasets[0].data = data.map(item => item.total_salary);
    trendChart.update();
}

// Update report table
function updateReportTable(data) {
    reportTable.clear();
    
    data.forEach(item => {
        const avgSalary = item.total_salary / item.employee_count;
        reportTable.row.add([
            item.department_name,
            item.employee_count,
            formatCurrency(item.total_basic_salary),
            formatCurrency(item.total_allowance),
            formatCurrency(item.total_bonus),
            formatCurrency(item.total_deduction),
            formatCurrency(item.total_salary),
            formatCurrency(avgSalary)
        ]);
    });
    
    reportTable.draw();
}

// Export to Excel
function exportToExcel() {
    const data = [];
    const headers = [
        'Phòng ban',
        'Số nhân viên',
        'Tổng lương cơ bản',
        'Tổng phụ cấp',
        'Tổng thưởng',
        'Tổng khấu trừ',
        'Tổng lương',
        'Lương trung bình'
    ];
    
    data.push(headers);
    
    reportTable.rows().data().each(function(row) {
        data.push(row);
    });
    
    const ws = XLSX.utils.aoa_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Báo cáo lương');
    
    const fileName = `Bao_cao_luong_${new Date().toISOString().split('T')[0]}.xlsx`;
    XLSX.writeFile(wb, fileName);
}

// Helper functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

function formatChange(change) {
    const prefix = change >= 0 ? '+' : '';
    return `${prefix}${change}% so với tháng trước`;
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