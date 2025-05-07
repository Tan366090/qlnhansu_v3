$(document).ready(function() {
    // Initialize DataTable
    const documentsTable = $('#documentsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
        },
        order: [[3, 'desc']], // Sort by upload date by default
        pageLength: 10
    });

    // Initialize date picker
    flatpickr('#filterDate', {
        locale: 'vn',
        mode: 'range',
        dateFormat: 'd/m/Y',
        allowInput: true
    });

    // Load employees for uploader filter
    function loadEmployees() {
        $.ajax({
            url: '/api/employees',
            method: 'GET',
            success: function(response) {
                const employees = response.data;
                $('#filterUploader').empty().append('<option value="">Tất cả</option>');
                
                employees.forEach(employee => {
                    $('#filterUploader').append(`<option value="${employee.id}">${employee.code} - ${employee.name}</option>`);
                });
            },
            error: function(xhr) {
                showError('Không thể tải danh sách nhân viên: ' + xhr.responseJSON?.message);
            }
        });
    }

    // Load documents with filters
    function loadDocuments() {
        showLoading();
        const filters = {
            type: $('#filterType').val(),
            uploader_id: $('#filterUploader').val(),
            access_level: $('#filterAccess').val(),
            start_date: $('#filterDate').val().split(' to ')[0],
            end_date: $('#filterDate').val().split(' to ')[1]
        };

        $.ajax({
            url: '/api/documents',
            method: 'GET',
            data: filters,
            success: function(response) {
                documentsTable.clear();
                response.data.forEach(document => {
                    documentsTable.row.add([
                        document.name,
                        getDocumentTypeLabel(document.type),
                        document.uploader.name,
                        formatDate(document.upload_date),
                        formatFileSize(document.file_size),
                        getAccessLevelLabel(document.access_level),
                        getActionButtons(document)
                    ]);
                });
                documentsTable.draw();
                hideLoading();
            },
            error: function(xhr) {
                hideLoading();
                showError('Không thể tải danh sách tài liệu: ' + xhr.responseJSON?.message);
            }
        });
    }

    // Handle document upload
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('name', $('#documentName').val());
        formData.append('type', $('#documentType').val());
        formData.append('file', $('#file')[0].files[0]);
        formData.append('access_level', $('#accessLevel').val());
        formData.append('description', $('#description').val());

        // Add restricted access groups if applicable
        if ($('#accessLevel').val() === 'restricted') {
            const restrictedGroups = [];
            if ($('#accessHR').is(':checked')) restrictedGroups.push('hr');
            if ($('#accessFinance').is(':checked')) restrictedGroups.push('finance');
            if ($('#accessIT').is(':checked')) restrictedGroups.push('it');
            formData.append('restricted_groups', JSON.stringify(restrictedGroups));
        }

        showLoading();
        $.ajax({
            url: '/api/documents',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                hideLoading();
                showSuccess('Upload tài liệu thành công');
                setTimeout(() => {
                    window.location.href = '/admin/documents';
                }, 1500);
            },
            error: function(xhr) {
                hideLoading();
                showError('Không thể upload tài liệu: ' + xhr.responseJSON?.message);
            }
        });
    });

    // Show/hide restricted access section
    $('#accessLevel').on('change', function() {
        if ($(this).val() === 'restricted') {
            $('#restrictedAccessSection').show();
        } else {
            $('#restrictedAccessSection').hide();
        }
    });

    // Handle document download
    $(document).on('click', '.download-document', function() {
        const id = $(this).data('id');
        window.location.href = `/api/documents/${id}/download`;
    });

    // Handle document delete
    $(document).on('click', '.delete-document', function() {
        const id = $(this).data('id');
        if (confirm('Bạn có chắc chắn muốn xóa tài liệu này?')) {
            showLoading();
            $.ajax({
                url: `/api/documents/${id}`,
                method: 'DELETE',
                success: function(response) {
                    hideLoading();
                    showSuccess('Xóa tài liệu thành công');
                    loadDocuments();
                },
                error: function(xhr) {
                    hideLoading();
                    showError('Không thể xóa tài liệu: ' + xhr.responseJSON?.message);
                }
            });
        }
    });

    // Helper functions
    function getDocumentTypeLabel(type) {
        const types = {
            'policy': 'Chính sách',
            'procedure': 'Quy trình',
            'form': 'Biểu mẫu',
            'report': 'Báo cáo',
            'other': 'Khác'
        };
        return types[type] || type;
    }

    function getAccessLevelLabel(level) {
        const levels = {
            'public': '<span class="badge bg-success">Công khai</span>',
            'private': '<span class="badge bg-warning">Riêng tư</span>',
            'restricted': '<span class="badge bg-info">Hạn chế</span>'
        };
        return levels[level] || level;
    }

    function formatDate(date) {
        return new Date(date).toLocaleDateString('vi-VN');
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function getActionButtons(document) {
        return `
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-info download-document" data-id="${document.id}">
                    <i class="fas fa-download"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger delete-document" data-id="${document.id}">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    }

    // UI helpers
    function showLoading() {
        $('.loading-spinner').show();
    }

    function hideLoading() {
        $('.loading-spinner').hide();
    }

    function showError(message) {
        $('.error-message').text(message).show().delay(5000).fadeOut();
    }

    function showSuccess(message) {
        $('.success-message').text(message).show().delay(5000).fadeOut();
    }

    // Event handlers for filters
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        loadDocuments();
    });

    $('#resetFilters').on('click', function() {
        $('#filterForm')[0].reset();
        loadDocuments();
    });

    // Initial load
    loadEmployees();
    loadDocuments();
}); 