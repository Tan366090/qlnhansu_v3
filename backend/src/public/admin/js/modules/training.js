// Training Module JavaScript
const TrainingModule = {
    // Common utilities
    utils: {
        formatDate: (date) => {
            return new Date(date).toLocaleDateString('vi-VN');
        },
        formatDateTime: (date) => {
            return new Date(date).toLocaleString('vi-VN');
        },
        showNotification: (message, type = 'success') => {
            const container = document.getElementById('notificationContainer');
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            container.appendChild(notification);
            setTimeout(() => notification.remove(), 5000);
        }
    },

    // API endpoints
    endpoints: {
        courses: '/api/trainings/courses',
        registrations: '/api/trainings/registrations',
        evaluations: '/api/trainings/evaluations',
        reports: '/api/trainings/reports'
    },

    // Status mapping
    status: {
        course: {
            planned: { class: 'status-planned', text: 'Dự kiến' },
            ongoing: { class: 'status-ongoing', text: 'Đang diễn ra' },
            completed: { class: 'status-completed', text: 'Đã hoàn thành' },
            cancelled: { class: 'status-cancelled', text: 'Đã hủy' }
        },
        registration: {
            registered: { class: 'status-planned', text: 'Đã đăng ký' },
            attending: { class: 'status-ongoing', text: 'Đang tham gia' },
            completed: { class: 'status-completed', text: 'Đã hoàn thành' },
            dropped: { class: 'status-cancelled', text: 'Đã bỏ học' }
        },
        evaluation: {
            excellent: { class: 'status-ongoing', text: 'Xuất sắc' },
            good: { class: 'status-planned', text: 'Tốt' },
            average: { class: 'status-completed', text: 'Trung bình' },
            poor: { class: 'status-cancelled', text: 'Kém' }
        }
    },

    // Common functions
    loadEmployees: async function() {
        try {
            const response = await fetch('/api/employees');
            if (!response.ok) throw new Error('Failed to fetch employees');
            return await response.json();
        } catch (error) {
            console.error('Error loading employees:', error);
            this.utils.showNotification('Lỗi khi tải danh sách nhân viên', 'error');
            return [];
        }
    },

    loadDepartments: async function() {
        try {
            const response = await fetch('/api/departments');
            if (!response.ok) throw new Error('Failed to fetch departments');
            return await response.json();
        } catch (error) {
            console.error('Error loading departments:', error);
            this.utils.showNotification('Lỗi khi tải danh sách phòng ban', 'error');
            return [];
        }
    },

    loadCourses: async function() {
        try {
            const response = await fetch(this.endpoints.courses);
            if (!response.ok) throw new Error('Failed to fetch courses');
            return await response.json();
        } catch (error) {
            console.error('Error loading courses:', error);
            this.utils.showNotification('Lỗi khi tải danh sách khóa học', 'error');
            return [];
        }
    },

    // DataTable configurations
    dataTableConfig: {
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/vi.json'
        },
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [[0, 'desc']]
    },

    // Chart configurations
    chartConfig: {
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    },

    // Form validation
    validateForm: function(formId) {
        const form = document.getElementById(formId);
        if (!form) return false;

        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        });

        return isValid;
    },

    // Initialize common components
    init: function() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize popovers
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });

        // Add loading overlay
        if (!document.getElementById('loadingOverlay')) {
            const overlay = document.createElement('div');
            overlay.id = 'loadingOverlay';
            overlay.className = 'loading-overlay';
            overlay.innerHTML = `
                <div class="text-center">
                    <div class="loading-spinner"></div>
                    <div class="loading-text">Đang tải...</div>
                </div>
            `;
            document.body.appendChild(overlay);
        }
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    TrainingModule.init();
}); 