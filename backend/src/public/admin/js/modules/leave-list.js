// Initialize date pickers and DataTable
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date pickers
    flatpickr("#filterStartDate", {
        locale: "vn",
        dateFormat: "d/m/Y",
        allowInput: true
    });

    flatpickr("#filterEndDate", {
        locale: "vn",
        dateFormat: "d/m/Y",
        allowInput: true
    });

    // Initialize DataTable
    const table = $('#leaveTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
        },
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/leave/list',
            type: 'GET',
            data: function(d) {
                d.status = $('#filterStatus').val();
                d.type = $('#filterType').val();
                d.start_date = $('#filterStartDate').val();
                d.end_date = $('#filterEndDate').val();
            }
        },
        columns: [
            { data: 'employee.employee_code' },
            { data: 'employee.name' },
            { 
                data: 'leave_type',
                render: function(data) {
                    switch(data) {
                        case 'annual': return 'Nghỉ phép';
                        case 'sick': return 'Nghỉ ốm';
                        case 'unpaid': return 'Nghỉ không lương';
                        default: return data;
                    }
                }
            },
            { 
                data: null,
                render: function(data) {
                    const start = new Date(data.start_date).toLocaleDateString('vi-VN');
                    const end = new Date(data.end_date).toLocaleDateString('vi-VN');
                    return `${start} - ${end}`;
                }
            },
            { data: 'days' },
            { 
                data: 'reason',
                render: function(data) {
                    return data.length > 50 ? data.substring(0, 50) + '...' : data;
                }
            },
            { 
                data: 'status',
                render: function(data) {
                    let badgeClass = '';
                    let statusText = '';
                    
                    switch(data) {
                        case 'pending':
                            badgeClass = 'bg-warning';
                            statusText = 'Chờ phê duyệt';
                            break;
                        case 'approved':
                            badgeClass = 'bg-success';
                            statusText = 'Đã phê duyệt';
                            break;
                        case 'rejected':
                            badgeClass = 'bg-danger';
                            statusText = 'Đã từ chối';
                            break;
                        case 'cancelled':
                            badgeClass = 'bg-secondary';
                            statusText = 'Đã hủy';
                            break;
                    }
                    
                    return `<span class="badge ${badgeClass}">${statusText}</span>`;
                }
            },
            {
                data: null,
                render: function(data) {
                    let buttons = `
                        <button class="btn btn-sm btn-info view-details" data-id="${data.id}">
                            <i class="fas fa-eye"></i>
                        </button>
                    `;

                    if (data.status === 'pending') {
                        buttons += `
                            <button class="btn btn-sm btn-success approve-leave" data-id="${data.id}">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-sm btn-danger reject-leave" data-id="${data.id}">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                    }

                    return `<div class="btn-group">${buttons}</div>`;
                }
            }
        ]
    });

    // Handle filter form submission
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        table.ajax.reload();
    });

    // Handle reset filters
    $('#resetFilters').on('click', function() {
        $('#filterStatus').val('');
        $('#filterType').val('');
        $('#filterStartDate').val('');
        $('#filterEndDate').val('');
        table.ajax.reload();
    });

    // Handle view details
    $('#leaveTable').on('click', '.view-details', function() {
        const leaveId = $(this).data('id');
        showLeaveDetails(leaveId);
    });

    // Handle approve leave
    $('#leaveTable').on('click', '.approve-leave', function() {
        const leaveId = $(this).data('id');
        approveLeave(leaveId);
    });

    // Handle reject leave
    $('#leaveTable').on('click', '.reject-leave', function() {
        const leaveId = $(this).data('id');
        rejectLeave(leaveId);
    });
});

// Show leave details in modal
async function showLeaveDetails(leaveId) {
    showLoading();
    try {
        const response = await fetch(`/api/leave/${leaveId}`);
        const data = await response.json();

        if (data.success) {
            const leave = data.data;
            
            // Fill modal with leave details
            $('#modalEmployeeCode').text(leave.employee.employee_code);
            $('#modalEmployeeName').text(leave.employee.name);
            $('#modalLeaveType').text(getLeaveTypeText(leave.leave_type));
            $('#modalDays').text(leave.days);
            $('#modalStartDate').text(new Date(leave.start_date).toLocaleDateString('vi-VN'));
            $('#modalEndDate').text(new Date(leave.end_date).toLocaleDateString('vi-VN'));
            $('#modalReason').text(leave.reason);
            
            // Handle attachment
            if (leave.attachment) {
                $('#modalAttachment').html(`
                    <a href="/${leave.attachment}" target="_blank" class="btn btn-sm btn-primary">
                        <i class="fas fa-download"></i> Tải xuống
                    </a>
                `);
            } else {
                $('#modalAttachment').text('Không có file đính kèm');
            }

            // Handle status
            $('#modalStatus').html(getStatusBadge(leave.status));

            // Handle approval info
            if (leave.status !== 'pending') {
                $('#modalApprovalInfo').show();
                let approvalDetails = '';
                
                if (leave.status === 'approved') {
                    approvalDetails = `
                        Đã phê duyệt bởi: ${leave.approver.name}<br>
                        Thời gian: ${new Date(leave.approved_at).toLocaleString('vi-VN')}<br>
                        ${leave.comment ? `Ghi chú: ${leave.comment}` : ''}
                    `;
                } else if (leave.status === 'rejected') {
                    approvalDetails = `
                        Đã từ chối bởi: ${leave.rejecter.name}<br>
                        Thời gian: ${new Date(leave.rejected_at).toLocaleString('vi-VN')}<br>
                        ${leave.comment ? `Lý do: ${leave.comment}` : ''}
                    `;
                } else if (leave.status === 'cancelled') {
                    approvalDetails = `
                        Đã hủy bởi: ${leave.canceller.name}<br>
                        Thời gian: ${new Date(leave.cancelled_at).toLocaleString('vi-VN')}<br>
                        ${leave.cancellation_reason ? `Lý do: ${leave.cancellation_reason}` : ''}
                    `;
                }
                
                $('#modalApprovalDetails').html(approvalDetails);
            } else {
                $('#modalApprovalInfo').hide();
            }

            // Handle action buttons
            const modalActions = $('#modalActions');
            modalActions.empty();

            if (leave.status === 'pending') {
                modalActions.html(`
                    <button type="button" class="btn btn-success" onclick="approveLeave(${leave.id})">
                        <i class="fas fa-check"></i> Phê duyệt
                    </button>
                    <button type="button" class="btn btn-danger" onclick="rejectLeave(${leave.id})">
                        <i class="fas fa-times"></i> Từ chối
                    </button>
                `);
            }

            // Show modal
            new bootstrap.Modal(document.getElementById('leaveDetailsModal')).show();
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('Có lỗi xảy ra khi tải chi tiết đơn nghỉ phép');
        console.error('Error loading leave details:', error);
    } finally {
        hideLoading();
    }
}

// Approve leave request
async function approveLeave(leaveId) {
    const comment = prompt('Nhập ghi chú (nếu có):');
    if (comment === null) return;

    showLoading();
    try {
        const response = await fetch(`/api/leave/${leaveId}/approve`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ comment })
        });

        const data = await response.json();
        if (data.success) {
            showSuccess('Đã phê duyệt đơn nghỉ phép thành công');
            $('#leaveTable').DataTable().ajax.reload();
            $('#leaveDetailsModal').modal('hide');
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('Có lỗi xảy ra khi phê duyệt đơn nghỉ phép');
        console.error('Error approving leave:', error);
    } finally {
        hideLoading();
    }
}

// Reject leave request
async function rejectLeave(leaveId) {
    const comment = prompt('Nhập lý do từ chối:');
    if (comment === null) return;

    showLoading();
    try {
        const response = await fetch(`/api/leave/${leaveId}/reject`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ comment })
        });

        const data = await response.json();
        if (data.success) {
            showSuccess('Đã từ chối đơn nghỉ phép thành công');
            $('#leaveTable').DataTable().ajax.reload();
            $('#leaveDetailsModal').modal('hide');
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('Có lỗi xảy ra khi từ chối đơn nghỉ phép');
        console.error('Error rejecting leave:', error);
    } finally {
        hideLoading();
    }
}

// Utility functions
function getLeaveTypeText(type) {
    switch(type) {
        case 'annual': return 'Nghỉ phép';
        case 'sick': return 'Nghỉ ốm';
        case 'unpaid': return 'Nghỉ không lương';
        default: return type;
    }
}

function getStatusBadge(status) {
    let badgeClass = '';
    let statusText = '';
    
    switch(status) {
        case 'pending':
            badgeClass = 'bg-warning';
            statusText = 'Chờ phê duyệt';
            break;
        case 'approved':
            badgeClass = 'bg-success';
            statusText = 'Đã phê duyệt';
            break;
        case 'rejected':
            badgeClass = 'bg-danger';
            statusText = 'Đã từ chối';
            break;
        case 'cancelled':
            badgeClass = 'bg-secondary';
            statusText = 'Đã hủy';
            break;
    }
    
    return `<span class="badge ${badgeClass}">${statusText}</span>`;
}

function showLoading() {
    $('.loading-spinner').show();
}

function hideLoading() {
    $('.loading-spinner').hide();
}

function showError(message) {
    const errorElement = $('.error-message');
    errorElement.text(message);
    errorElement.show();
    setTimeout(() => errorElement.hide(), 3000);
}

function showSuccess(message) {
    const successElement = $('.success-message');
    successElement.text(message);
    successElement.show();
    setTimeout(() => successElement.hide(), 3000);
} 