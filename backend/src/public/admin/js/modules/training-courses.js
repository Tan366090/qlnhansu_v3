// Initialize date pickers and DataTable
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date pickers
    flatpickr("#filterStartDate, #startDate", {
        locale: "vn",
        dateFormat: "d/m/Y",
        allowInput: true
    });

    flatpickr("#filterEndDate, #endDate", {
        locale: "vn",
        dateFormat: "d/m/Y",
        allowInput: true
    });

    // Initialize DataTable
    const table = $('#coursesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
        },
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/training/courses',
            type: 'GET',
            data: function(d) {
                d.status = $('#filterStatus').val();
                d.instructor = $('#filterInstructor').val();
                d.start_date = $('#filterStartDate').val();
                d.end_date = $('#filterEndDate').val();
            }
        },
        columns: [
            { data: 'course_code' },
            { data: 'course_name' },
            { data: 'instructor' },
            { 
                data: null,
                render: function(data) {
                    const start = new Date(data.start_date).toLocaleDateString('vi-VN');
                    const end = new Date(data.end_date).toLocaleDateString('vi-VN');
                    return `${start} - ${end}`;
                }
            },
            { data: 'location' },
            { 
                data: null,
                render: function(data) {
                    return `${data.current_students}/${data.max_students}`;
                }
            },
            { 
                data: 'status',
                render: function(data) {
                    let badgeClass = '';
                    let statusText = '';
                    
                    switch(data) {
                        case 'upcoming':
                            badgeClass = 'bg-info';
                            statusText = 'Sắp diễn ra';
                            break;
                        case 'ongoing':
                            badgeClass = 'bg-primary';
                            statusText = 'Đang diễn ra';
                            break;
                        case 'completed':
                            badgeClass = 'bg-success';
                            statusText = 'Đã kết thúc';
                            break;
                        case 'cancelled':
                            badgeClass = 'bg-danger';
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

                    if (data.status === 'upcoming') {
                        buttons += `
                            <button class="btn btn-sm btn-primary edit-course" data-id="${data.id}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-course" data-id="${data.id}">
                                <i class="fas fa-trash"></i>
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
        $('#filterInstructor').val('');
        $('#filterStartDate').val('');
        $('#filterEndDate').val('');
        table.ajax.reload();
    });

    // Handle save course
    $('#saveCourse').on('click', async function() {
        const form = document.getElementById('addCourseForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData();
        formData.append('course_code', $('#courseCode').val());
        formData.append('course_name', $('#courseName').val());
        formData.append('instructor', $('#instructor').val());
        formData.append('location', $('#location').val());
        formData.append('start_date', $('#startDate').val());
        formData.append('end_date', $('#endDate').val());
        formData.append('max_students', $('#maxStudents').val());
        formData.append('course_fee', $('#courseFee').val());
        formData.append('description', $('#description').val());

        const materials = document.getElementById('materials').files;
        for (let i = 0; i < materials.length; i++) {
            formData.append('materials', materials[i]);
        }

        showLoading();
        try {
            const response = await fetch('/api/training/courses', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            if (data.success) {
                showSuccess('Đã thêm khóa học thành công');
                $('#addCourseModal').modal('hide');
                table.ajax.reload();
                form.reset();
            } else {
                showError(data.message);
            }
        } catch (error) {
            showError('Có lỗi xảy ra khi thêm khóa học');
            console.error('Error adding course:', error);
        } finally {
            hideLoading();
        }
    });

    // Handle view details
    $('#coursesTable').on('click', '.view-details', function() {
        const courseId = $(this).data('id');
        showCourseDetails(courseId);
    });

    // Handle edit course
    $('#coursesTable').on('click', '.edit-course', function() {
        const courseId = $(this).data('id');
        editCourse(courseId);
    });

    // Handle delete course
    $('#coursesTable').on('click', '.delete-course', function() {
        const courseId = $(this).data('id');
        deleteCourse(courseId);
    });
});

// Show course details in modal
async function showCourseDetails(courseId) {
    showLoading();
    try {
        const response = await fetch(`/api/training/courses/${courseId}`);
        const data = await response.json();

        if (data.success) {
            const course = data.data;
            
            // Fill modal with course details
            $('#modalCourseCode').text(course.course_code);
            $('#modalCourseName').text(course.course_name);
            $('#modalInstructor').text(course.instructor);
            $('#modalLocation').text(course.location);
            $('#modalDuration').text(`${new Date(course.start_date).toLocaleDateString('vi-VN')} - ${new Date(course.end_date).toLocaleDateString('vi-VN')}`);
            $('#modalStudentCount').text(`${course.current_students}/${course.max_students}`);
            $('#modalDescription').text(course.description);
            
            // Handle materials
            const materialsContainer = $('#modalMaterials');
            materialsContainer.empty();
            
            if (course.materials && course.materials.length > 0) {
                course.materials.forEach(material => {
                    materialsContainer.append(`
                        <div class="mb-2">
                            <a href="/${material.file_path}" target="_blank" class="btn btn-sm btn-primary">
                                <i class="fas fa-download"></i> ${material.file_name}
                            </a>
                        </div>
                    `);
                });
            } else {
                materialsContainer.text('Không có tài liệu');
            }

            // Handle action buttons
            const modalActions = $('#modalActions');
            modalActions.empty();

            if (course.status === 'upcoming') {
                modalActions.html(`
                    <button type="button" class="btn btn-primary" onclick="editCourse(${course.id})">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </button>
                    <button type="button" class="btn btn-danger" onclick="deleteCourse(${course.id})">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                `);
            }

            // Show modal
            new bootstrap.Modal(document.getElementById('courseDetailsModal')).show();
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('Có lỗi xảy ra khi tải chi tiết khóa học');
        console.error('Error loading course details:', error);
    } finally {
        hideLoading();
    }
}

// Edit course
async function editCourse(courseId) {
    showLoading();
    try {
        const response = await fetch(`/api/training/courses/${courseId}`);
        const data = await response.json();

        if (data.success) {
            const course = data.data;
            
            // Fill form with course data
            $('#courseCode').val(course.course_code);
            $('#courseName').val(course.course_name);
            $('#instructor').val(course.instructor);
            $('#location').val(course.location);
            $('#startDate').val(new Date(course.start_date).toLocaleDateString('vi-VN'));
            $('#endDate').val(new Date(course.end_date).toLocaleDateString('vi-VN'));
            $('#maxStudents').val(course.max_students);
            $('#courseFee').val(course.course_fee);
            $('#description').val(course.description);

            // Show modal
            $('#addCourseModal').modal('show');
            
            // Change save button to update
            $('#saveCourse').off('click').on('click', async function() {
                const formData = new FormData();
                formData.append('course_code', $('#courseCode').val());
                formData.append('course_name', $('#courseName').val());
                formData.append('instructor', $('#instructor').val());
                formData.append('location', $('#location').val());
                formData.append('start_date', $('#startDate').val());
                formData.append('end_date', $('#endDate').val());
                formData.append('max_students', $('#maxStudents').val());
                formData.append('course_fee', $('#courseFee').val());
                formData.append('description', $('#description').val());

                const materials = document.getElementById('materials').files;
                for (let i = 0; i < materials.length; i++) {
                    formData.append('materials', materials[i]);
                }

                showLoading();
                try {
                    const updateResponse = await fetch(`/api/training/courses/${courseId}`, {
                        method: 'PUT',
                        body: formData
                    });

                    const updateData = await updateResponse.json();
                    if (updateData.success) {
                        showSuccess('Đã cập nhật khóa học thành công');
                        $('#addCourseModal').modal('hide');
                        $('#coursesTable').DataTable().ajax.reload();
                    } else {
                        showError(updateData.message);
                    }
                } catch (error) {
                    showError('Có lỗi xảy ra khi cập nhật khóa học');
                    console.error('Error updating course:', error);
                } finally {
                    hideLoading();
                }
            });
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('Có lỗi xảy ra khi tải thông tin khóa học');
        console.error('Error loading course:', error);
    } finally {
        hideLoading();
    }
}

// Delete course
async function deleteCourse(courseId) {
    if (!confirm('Bạn có chắc chắn muốn xóa khóa học này?')) {
        return;
    }

    showLoading();
    try {
        const response = await fetch(`/api/training/courses/${courseId}`, {
            method: 'DELETE'
        });

        const data = await response.json();
        if (data.success) {
            showSuccess('Đã xóa khóa học thành công');
            $('#coursesTable').DataTable().ajax.reload();
            $('#courseDetailsModal').modal('hide');
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('Có lỗi xảy ra khi xóa khóa học');
        console.error('Error deleting course:', error);
    } finally {
        hideLoading();
    }
}

// Utility functions
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