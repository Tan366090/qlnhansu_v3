$(document).ready(function() {
    // Initialize DataTables
    const kpiTable = $('#kpiTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
        },
        order: [[3, 'desc']],
        pageLength: 10
    });

    // Initialize Chart
    let kpiChart = null;

    // Initialize view chart
    let viewChart = null;

    // Load employees for dropdowns
    function loadEmployees() {
        $.ajax({
            url: '/api/employees',
            method: 'GET',
            success: function(response) {
                const employeeSelects = $('#employee, #filterEmployee');
                employeeSelects.empty().append('<option value="">Chọn nhân viên</option>');
                response.forEach(employee => {
                    employeeSelects.append(`<option value="${employee.id}">${employee.code} - ${employee.name}</option>`);
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
            url: '/api/departments',
            method: 'GET',
            success: function(response) {
                const departmentSelect = $('#filterDepartment');
                departmentSelect.empty().append('<option value="">Tất cả</option>');
                response.forEach(department => {
                    departmentSelect.append(`<option value="${department.id}">${department.name}</option>`);
                });
            },
            error: function(xhr) {
                showError('Không thể tải danh sách phòng ban');
            }
        });
    }

    // Load KPI data
    function loadKPIData() {
        showLoading();
        $.ajax({
            url: '/api/performance/kpi',
            method: 'GET',
            success: function(response) {
                kpiTable.clear();
                response.forEach(kpi => {
                    const completionRate = (kpi.result / kpi.target * 100).toFixed(1);
                    kpiTable.row.add([
                        kpi.metric,
                        kpi.target,
                        kpi.result,
                        getCompletionRateBadge(completionRate),
                        getActionButtons(kpi)
                    ]);
                });
                kpiTable.draw();
                updateKPIChart(response);
                hideLoading();
            },
            error: function(xhr) {
                hideLoading();
                showError('Không thể tải dữ liệu KPI');
            }
        });
    }

    // Update KPI chart
    function updateKPIChart(kpiData) {
        const ctx = document.getElementById('kpiChart').getContext('2d');
        
        // Group KPI data by metric
        const metricData = {};
        kpiData.forEach(kpi => {
            if (!metricData[kpi.metric]) {
                metricData[kpi.metric] = {
                    targets: [],
                    results: [],
                    labels: []
                };
            }
            metricData[kpi.metric].targets.push(kpi.target);
            metricData[kpi.metric].results.push(kpi.result);
            metricData[kpi.metric].labels.push(`${kpi.period} ${kpi.year}`);
        });

        const datasets = [];
        Object.keys(metricData).forEach(metric => {
            datasets.push({
                label: `${metric} - Mục tiêu`,
                data: metricData[metric].targets,
                borderColor: 'rgb(255, 99, 132)',
                tension: 0.1
            });
            datasets.push({
                label: `${metric} - Kết quả`,
                data: metricData[metric].results,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            });
        });

        if (kpiChart) {
            kpiChart.destroy();
        }

        kpiChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: kpiData.map(kpi => `${kpi.period} ${kpi.year}`),
                datasets: datasets
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Handle form submission
    $('#addKPIForm').on('submit', function(e) {
        e.preventDefault();
        showLoading();
        
        const formData = {
            employeeId: $('#employee').val(),
            period: $('#period').val(),
            year: $('#year').val(),
            metric: $('#metric').val(),
            target: $('#target').val(),
            result: $('#result').val(),
            description: $('#description').val(),
            notes: $('#notes').val()
        };

        $.ajax({
            url: '/api/performance/kpi',
            method: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(response) {
                hideLoading();
                showSuccess('Thêm KPI thành công');
                $('#addKPIModal').modal('hide');
                $('#addKPIForm')[0].reset();
                loadKPIData();
            },
            error: function(xhr) {
                hideLoading();
                showError(xhr.responseJSON?.message || 'Không thể thêm KPI');
            }
        });
    });

    // Handle filter form submission
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        const filters = {
            employeeId: $('#filterEmployee').val(),
            departmentId: $('#filterDepartment').val(),
            period: $('#filterPeriod').val(),
            year: $('#filterYear').val()
        };
        loadKPIData(filters);
    });

    // Handle reset filters
    $('#resetFilters').on('click', function() {
        $('#filterForm')[0].reset();
        loadKPIData();
    });

    // Helper functions
    function getCompletionRateBadge(rate) {
        let badgeClass = 'bg-danger';
        if (rate >= 100) {
            badgeClass = 'bg-success';
        } else if (rate >= 80) {
            badgeClass = 'bg-primary';
        } else if (rate >= 60) {
            badgeClass = 'bg-warning';
        }
        return `<span class="badge ${badgeClass}">${rate}%</span>`;
    }

    function getActionButtons(kpi) {
        return `
            <button class="btn btn-sm btn-info view-btn" data-id="${kpi.id}">
                <i class="fas fa-eye"></i>
            </button>
            <button class="btn btn-sm btn-primary edit-btn" data-id="${kpi.id}">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-danger delete-btn" data-id="${kpi.id}">
                <i class="fas fa-trash"></i>
            </button>
        `;
    }

    // Event handlers for action buttons
    $(document).on('click', '.view-btn', function() {
        const kpiId = $(this).data('id');
        showLoading();
        $.ajax({
            url: `/api/performance/kpi/${kpiId}`,
            method: 'GET',
            success: function(response) {
                // Populate view modal
                $('#viewEmployee').text(response.employee.name);
                $('#viewPeriod').text(response.period);
                $('#viewYear').text(response.year);
                $('#viewMetric').text(response.metric);
                $('#viewTarget').text(response.target);
                $('#viewResult').text(response.result);
                $('#viewDescription').text(response.description || 'Không có');
                $('#viewNotes').text(response.notes || 'Không có');

                // Update progress bar
                const completionRate = (response.result / response.target * 100).toFixed(1);
                const progressBar = $('#viewProgress');
                progressBar.css('width', `${Math.min(completionRate, 100)}%`);
                
                // Set progress bar color based on completion rate
                if (completionRate >= 100) {
                    progressBar.removeClass('bg-warning bg-danger').addClass('bg-success');
                } else if (completionRate >= 80) {
                    progressBar.removeClass('bg-success bg-danger').addClass('bg-warning');
                } else {
                    progressBar.removeClass('bg-success bg-warning').addClass('bg-danger');
                }
                
                $('#viewProgressText').text(`${completionRate}%`);

                // Update comparison chart
                updateViewChart(response);
                
                // Show modal
                $('#viewKPIModal').modal('show');
                hideLoading();
            },
            error: function(xhr) {
                hideLoading();
                showError('Không thể tải thông tin KPI');
            }
        });
    });

    // Update view chart
    function updateViewChart(kpiData) {
        const ctx = document.getElementById('viewChart').getContext('2d');
        
        if (viewChart) {
            viewChart.destroy();
        }

        viewChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Mục tiêu', 'Kết quả'],
                datasets: [{
                    label: 'Giá trị',
                    data: [kpiData.target, kpiData.result],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(75, 192, 192, 0.5)'
                    ],
                    borderColor: [
                        'rgb(54, 162, 235)',
                        'rgb(75, 192, 192)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    $(document).on('click', '.edit-btn', function() {
        const kpiId = $(this).data('id');
        showLoading();
        $.ajax({
            url: `/api/performance/kpi/${kpiId}`,
            method: 'GET',
            success: function(response) {
                // Populate edit form
                $('#editKPIId').val(response.id);
                $('#editEmployee').val(response.employeeId);
                $('#editPeriod').val(response.period);
                $('#editYear').val(response.year);
                $('#editMetric').val(response.metric);
                $('#editTarget').val(response.target);
                $('#editResult').val(response.result);
                $('#editDescription').val(response.description);
                $('#editNotes').val(response.notes);
                
                // Show modal
                $('#editKPIModal').modal('show');
                hideLoading();
            },
            error: function(xhr) {
                hideLoading();
                showError('Không thể tải thông tin KPI');
            }
        });
    });

    // Form validation
    function validateKPIForm(formId) {
        const form = $(`#${formId}`);
        let isValid = true;
        const requiredFields = ['employee', 'period', 'year', 'metric', 'target', 'result'];
        
        requiredFields.forEach(field => {
            const value = $(`#${formId === 'addKPIForm' ? '' : 'edit'}${field}`).val();
            if (!value) {
                isValid = false;
                $(`#${formId === 'addKPIForm' ? '' : 'edit'}${field}`).addClass('is-invalid');
            } else {
                $(`#${formId === 'addKPIForm' ? '' : 'edit'}${field}`).removeClass('is-invalid');
            }
        });

        // Validate target and result are numbers
        const target = parseFloat($(`#${formId === 'addKPIForm' ? '' : 'edit'}target`).val());
        const result = parseFloat($(`#${formId === 'addKPIForm' ? '' : 'edit'}result`).val());
        
        if (isNaN(target) || isNaN(result)) {
            isValid = false;
            $(`#${formId === 'addKPIForm' ? '' : 'edit'}target, #${formId === 'addKPIForm' ? '' : 'edit'}result`).addClass('is-invalid');
        }

        return isValid;
    }

    // Handle update KPI
    $('#updateKPI').on('click', function() {
        if (!validateKPIForm('editKPIForm')) {
            showError('Vui lòng điền đầy đủ thông tin');
            return;
        }
        
        $('#confirmEditModal').modal('show');
    });

    // Handle confirm edit
    $('#confirmEdit').on('click', function() {
        const kpiId = $('#editKPIId').val();
        showLoading();
        
        const formData = {
            employeeId: $('#editEmployee').val(),
            period: $('#editPeriod').val(),
            year: $('#editYear').val(),
            metric: $('#editMetric').val(),
            target: $('#editTarget').val(),
            result: $('#editResult').val(),
            description: $('#editDescription').val(),
            notes: $('#editNotes').val()
        };

        $.ajax({
            url: `/api/performance/kpi/${kpiId}`,
            method: 'PUT',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(response) {
                hideLoading();
                showSuccess('Cập nhật KPI thành công');
                $('#editKPIModal').modal('hide');
                $('#confirmEditModal').modal('hide');
                loadKPIData();
            },
            error: function(xhr) {
                hideLoading();
                showError(xhr.responseJSON?.message || 'Không thể cập nhật KPI');
            }
        });
    });

    $(document).on('click', '.delete-btn', function() {
        const kpiId = $(this).data('id');
        if (confirm('Bạn có chắc chắn muốn xóa KPI này?')) {
            $.ajax({
                url: `/api/performance/kpi/${kpiId}`,
                method: 'DELETE',
                success: function(response) {
                    showSuccess('Xóa KPI thành công');
                    loadKPIData();
                },
                error: function(xhr) {
                    showError(xhr.responseJSON?.message || 'Không thể xóa KPI');
                }
            });
        }
    });

    // UI helper functions
    function showLoading() {
        $('.loading-spinner').show();
    }

    function hideLoading() {
        $('.loading-spinner').hide();
    }

    function showError(message) {
        $('.error-message').text(message).show();
        setTimeout(() => $('.error-message').hide(), 5000);
    }

    function showSuccess(message) {
        $('.success-message').text(message).show();
        setTimeout(() => $('.success-message').hide(), 5000);
    }

    // Initial load
    loadEmployees();
    loadDepartments();
    loadKPIData();
}); 