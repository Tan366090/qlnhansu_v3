$(document).ready(function() {
    // Initialize DataTables
    const equipmentTable = $('#equipmentTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
        },
        order: [[0, 'asc']],
        pageLength: 10
    });

    const assignmentHistoryTable = $('#assignmentHistoryTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
        },
        order: [[3, 'desc']],
        pageLength: 10
    });

    // Load employees for dropdowns
    function loadEmployees() {
        $.ajax({
            url: '/api/employees',
            method: 'GET',
            success: function(response) {
                const employeeSelects = $('#employee, #filterUser');
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

    // Load equipment for dropdown
    function loadEquipment() {
        $.ajax({
            url: '/api/equipment',
            method: 'GET',
            success: function(response) {
                const equipmentSelect = $('#equipment');
                equipmentSelect.empty().append('<option value="">Chọn thiết bị</option>');
                response.forEach(equipment => {
                    if (equipment.status === 'available') {
                        equipmentSelect.append(`<option value="${equipment.id}">${equipment.code} - ${equipment.name}</option>`);
                    }
                });
            },
            error: function(xhr) {
                showError('Không thể tải danh sách thiết bị');
            }
        });
    }

    // Load equipment list
    function loadEquipmentList() {
        showLoading();
        $.ajax({
            url: '/api/equipment',
            method: 'GET',
            success: function(response) {
                equipmentTable.clear();
                response.forEach(equipment => {
                    equipmentTable.row.add([
                        equipment.code,
                        equipment.name,
                        getEquipmentTypeLabel(equipment.type),
                        getStatusBadge(equipment.status),
                        equipment.user ? `${equipment.user.code} - ${equipment.user.name}` : '-',
                        getActionButtons(equipment)
                    ]);
                });
                equipmentTable.draw();
                hideLoading();
            },
            error: function(xhr) {
                hideLoading();
                showError('Không thể tải danh sách thiết bị');
            }
        });
    }

    // Load assignment history
    function loadAssignmentHistory() {
        showLoading();
        $.ajax({
            url: '/api/equipment/assignments',
            method: 'GET',
            success: function(response) {
                assignmentHistoryTable.clear();
                response.forEach(assignment => {
                    assignmentHistoryTable.row.add([
                        assignment.equipment.code,
                        assignment.equipment.name,
                        `${assignment.employee.code} - ${assignment.employee.name}`,
                        formatDate(assignment.assignDate),
                        assignment.returnDate ? formatDate(assignment.returnDate) : '-',
                        getStatusBadge(assignment.status),
                        getAssignmentActionButtons(assignment)
                    ]);
                });
                assignmentHistoryTable.draw();
                hideLoading();
            },
            error: function(xhr) {
                hideLoading();
                showError('Không thể tải lịch sử cấp phát');
            }
        });
    }

    // Handle equipment assignment
    $('#assignForm').on('submit', function(e) {
        e.preventDefault();
        showLoading();
        
        const formData = {
            employeeId: $('#employee').val(),
            equipmentId: $('#equipment').val(),
            assignDate: $('#assignDate').val(),
            returnDate: $('#returnDate').val(),
            purpose: $('#purpose').val(),
            condition: $('#condition').val()
        };

        $.ajax({
            url: '/api/equipment/assign',
            method: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(response) {
                hideLoading();
                showSuccess('Cấp phát thiết bị thành công');
                $('#assignForm')[0].reset();
                loadEquipmentList();
                loadAssignmentHistory();
            },
            error: function(xhr) {
                hideLoading();
                showError(xhr.responseJSON?.message || 'Không thể cấp phát thiết bị');
            }
        });
    });

    // Handle equipment return
    $('#saveReturn').on('click', function() {
        const formData = {
            assignmentId: $('#returnAssignmentId').val(),
            returnDate: $('#returnDate').val(),
            condition: $('#returnCondition').val(),
            notes: $('#returnNotes').val()
        };

        $.ajax({
            url: '/api/equipment/return',
            method: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(response) {
                $('#returnEquipmentModal').modal('hide');
                showSuccess('Trả thiết bị thành công');
                loadEquipmentList();
                loadAssignmentHistory();
            },
            error: function(xhr) {
                showError(xhr.responseJSON?.message || 'Không thể trả thiết bị');
            }
        });
    });

    // Handle maintenance
    $('#saveMaintenance').on('click', function() {
        const formData = {
            equipmentId: $('#maintenanceEquipmentId').val(),
            date: $('#maintenanceDate').val(),
            type: $('#maintenanceType').val(),
            description: $('#maintenanceDescription').val(),
            cost: $('#maintenanceCost').val(),
            provider: $('#maintenanceProvider').val()
        };

        $.ajax({
            url: '/api/equipment/maintenance',
            method: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(response) {
                $('#maintenanceModal').modal('hide');
                showSuccess('Lưu thông tin bảo trì thành công');
                loadEquipmentList();
            },
            error: function(xhr) {
                showError(xhr.responseJSON?.message || 'Không thể lưu thông tin bảo trì');
            }
        });
    });

    // Helper functions
    function getEquipmentTypeLabel(type) {
        const types = {
            'laptop': 'Laptop',
            'desktop': 'Máy tính để bàn',
            'printer': 'Máy in',
            'scanner': 'Máy scan',
            'other': 'Khác'
        };
        return types[type] || type;
    }

    function getStatusBadge(status) {
        const badges = {
            'available': '<span class="badge bg-success">Sẵn sàng</span>',
            'assigned': '<span class="badge bg-primary">Đã cấp phát</span>',
            'maintenance': '<span class="badge bg-warning">Đang bảo trì</span>',
            'broken': '<span class="badge bg-danger">Hư hỏng</span>',
            'returned': '<span class="badge bg-secondary">Đã trả</span>'
        };
        return badges[status] || status;
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN');
    }

    function getActionButtons(equipment) {
        let buttons = '';
        if (equipment.status === 'available') {
            buttons += `<button class="btn btn-sm btn-primary assign-btn" data-id="${equipment.id}">
                <i class="fas fa-hand-holding"></i>
            </button>`;
        }
        buttons += `<button class="btn btn-sm btn-info maintenance-btn" data-id="${equipment.id}">
            <i class="fas fa-tools"></i>
        </button>`;
        return buttons;
    }

    function getAssignmentActionButtons(assignment) {
        let buttons = '';
        if (assignment.status === 'assigned') {
            buttons += `<button class="btn btn-sm btn-warning return-btn" data-id="${assignment.id}">
                <i class="fas fa-undo"></i>
            </button>`;
        }
        return buttons;
    }

    // Event handlers for action buttons
    $(document).on('click', '.assign-btn', function() {
        const equipmentId = $(this).data('id');
        window.location.href = `/admin/equipment/assign?equipmentId=${equipmentId}`;
    });

    $(document).on('click', '.maintenance-btn', function() {
        const equipmentId = $(this).data('id');
        $('#maintenanceEquipmentId').val(equipmentId);
        $('#maintenanceModal').modal('show');
    });

    $(document).on('click', '.return-btn', function() {
        const assignmentId = $(this).data('id');
        $('#returnAssignmentId').val(assignmentId);
        $('#returnEquipmentModal').modal('show');
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
    loadEquipment();
    loadEquipmentList();
    loadAssignmentHistory();
}); 