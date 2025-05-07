import { Dashboard } from './dashboard.js';
import { initRealtimeDashboard } from './dashboard-realtime.js';
import { initMenuSearch } from './menu-search.js';
import { initRecentMenu } from './recent-menu.js';
import { initGlobalSearch } from './global-search.js';
import { initUserProfile } from './user-profile.js';
import { initExportData } from './export-data.js';
import { initAIAnalysis } from './ai-analysis.js';
import { initGamification } from './gamification.js';
import { initMobileStats } from './mobile-stats.js';
import { initActivityFilter } from './activity-filter.js';
import { initNotificationHandler } from './notification-handler.js';
import { initLoadingOverlay } from './loading-overlay.js';
import { initDarkMode } from './dark-mode.js';

// Global variables
let currentEmployee = null;
const API_BASE_URL = "/api/v1";

// Initialize DataTables with custom options
function initializeDataTables() {
    $("#employeeTable").DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/vi.json",
        },
        responsive: true,
        order: [[0, "desc"]],
        pageLength: 10,
        lengthMenu: [
            [10, 25, 50, -1],
            [10, 25, 50, "Tất cả"],
        ],
        dom: '<"top"lf>rt<"bottom"ip>',
        initComplete: function () {
            $(".dataTables_filter input").addClass("form-control");
            $(".dataTables_length select").addClass("form-select");
        },
    });
}

// Setup event listeners
function setupEventListeners() {
    // Form submission handlers
    $("#addEmployeeForm").on("submit", handleAddEmployee);
    $("#profileForm").on("submit", handleUpdateProfile);

    // Modal handlers
    $("#addEmployeeModal").on("show.bs.modal", function () {
        loadDepartmentOptions();
        loadPositionOptions();
    });

    // Tab change handlers
    $('a[data-bs-toggle="tab"]').on("shown.bs.tab", function (e) {
        const target = $(e.target).attr("href");
        loadTabContent(target);
    });

    // Search functionality
    $("#searchInput").on("keyup", function () {
        const searchTerm = $(this).val().toLowerCase();
        filterTable(searchTerm);
    });
}

// Load initial data
function loadInitialData() {
    loadEmployees();
    loadDepartments();
    loadPositions();
}

// Update sidebar toggle for better responsiveness
function setupResponsiveSidebar() {
    const sidebarToggle = $(
        '<button class="btn btn-primary d-md-none" id="sidebarToggle">' +
            '<i class="fas fa-bars"></i></button>'
    );
    $(".main-content").prepend(sidebarToggle);

    $("#sidebarToggle").click(function () {
        $(".sidebar").toggleClass("active");
        $(".main-content").toggleClass("active");
    });

    // Automatically collapse sidebar on smaller screens
    $(window)
        .resize(function () {
            if ($(window).width() < 768) {
                $(".sidebar").addClass("active");
                $(".main-content").addClass("active");
            }
        })
        .trigger("resize");
}

// Setup form validation
function setupFormValidation() {
    // Add employee form validation
    $("#addEmployeeForm").validate({
        rules: {
            employee_id: {
                required: true,
                minlength: 3,
            },
            full_name: {
                required: true,
                minlength: 5,
            },
            email: {
                required: true,
                email: true,
            },
            phone: {
                required: true,
                minlength: 10,
            },
        },
        messages: {
            employee_id: {
                required: "Vui lòng nhập mã nhân viên",
                minlength: "Mã nhân viên phải có ít nhất 3 ký tự",
            },
            full_name: {
                required: "Vui lòng nhập họ tên",
                minlength: "Họ tên phải có ít nhất 5 ký tự",
            },
            email: {
                required: "Vui lòng nhập email",
                email: "Email không hợp lệ",
            },
            phone: {
                required: "Vui lòng nhập số điện thoại",
                minlength: "Số điện thoại phải có ít nhất 10 số",
            },
        },
        errorElement: "div",
        errorClass: "invalid-feedback",
        highlight: function (element) {
            $(element).addClass("is-invalid");
        },
        unhighlight: function (element) {
            $(element).removeClass("is-invalid");
        },
    });
}

// Load employees data
function loadEmployees() {
    $.ajax({
        url: `${API_BASE_URL}/employees`,
        method: "GET",
        success: function (response) {
            const table = $("#employeeTable").DataTable();
            table.clear();
            response.data.forEach((employee) => {
                table.row.add([
                    employee.id,
                    employee.full_name,
                    employee.department,
                    employee.position,
                    formatDate(employee.start_date),
                    getStatusBadge(employee.status),
                    getActionButtons(employee.id),
                ]);
            });
            table.draw();
        },
        error: function (error) {
            showToast("error", "Lỗi khi tải dữ liệu nhân viên");
        },
    });
}

// Load department options
function loadDepartmentOptions() {
    $.ajax({
        url: `${API_BASE_URL}/departments`,
        method: "GET",
        success: function (response) {
            const select = $('select[name="department"]');
            select.empty();
            response.data.forEach((dept) => {
                select.append(
                    `<option value="${dept.id}">${dept.name}</option>`
                );
            });
        },
    });
}

// Load position options
function loadPositionOptions() {
    $.ajax({
        url: `${API_BASE_URL}/positions`,
        method: "GET",
        success: function (response) {
            const select = $('select[name="position"]');
            select.empty();
            response.data.forEach((pos) => {
                select.append(`<option value="${pos.id}">${pos.name}</option>`);
            });
        },
    });
}

// Handle add employee
function handleAddEmployee(e) {
    e.preventDefault();
    if (!$(this).valid()) return;

    const formData = $(this).serialize();
    $.ajax({
        url: `${API_BASE_URL}/employees`,
        method: "POST",
        data: formData,
        success: function (response) {
            $("#addEmployeeModal").modal("hide");
            showToast("success", "Thêm nhân viên thành công");
            loadEmployees();
        },
        error: function (error) {
            showToast("error", "Lỗi khi thêm nhân viên");
        },
    });
}

// Handle update profile
function handleUpdateProfile(e) {
    e.preventDefault();
    if (!$(this).valid()) return;

    const formData = $(this).serialize();
    $.ajax({
        url: `${API_BASE_URL}/employees/${currentEmployee}/profile`,
        method: "PUT",
        data: formData,
        success: function (response) {
            showToast("success", "Cập nhật thông tin thành công");
        },
        error: function (error) {
            showToast("error", "Lỗi khi cập nhật thông tin");
        },
    });
}

// Load tab content
function loadTabContent(tabId) {
    if (!currentEmployee) return;

    switch (tabId) {
        case "#profile":
            loadEmployeeProfile();
            break;
        case "#family":
            loadFamilyMembers();
            break;
        case "#training":
            loadTrainingHistory();
            break;
        case "#performance":
            loadPerformanceHistory();
            break;
    }
}

// Helper functions
function formatDate(date) {
    return new Date(date).toLocaleDateString("vi-VN");
}

function getStatusBadge(status) {
    const badges = {
        active: '<span class="badge bg-success">Đang làm việc</span>',
        inactive: '<span class="badge bg-danger">Nghỉ việc</span>',
        on_leave: '<span class="badge bg-warning">Nghỉ phép</span>',
    };
    return badges[status] || "";
}

function getActionButtons(id) {
    return `
        <div class="btn-group">
            <button class="btn btn-sm btn-primary" onclick="editEmployee(${id})">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-info" onclick="viewEmployee(${id})">
                <i class="fas fa-eye"></i>
            </button>
            <button class="btn btn-sm btn-danger" onclick="deleteEmployee(${id})">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
}

function showToast(type, message) {
    const toast = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    $(".toast-container").append(toast);
    const toastElement = $(".toast").last();
    const bsToast = new bootstrap.Toast(toastElement);
    bsToast.show();

    toastElement.on("hidden.bs.toast", function () {
        $(this).remove();
    });
}

// Employee actions
function editEmployee(id) {
    currentEmployee = id;
    $.ajax({
        url: `${API_BASE_URL}/employees/${id}`,
        method: "GET",
        success: function (response) {
            const employee = response.data;
            $("#addEmployeeModal").modal("show");
            $("#addEmployeeForm")
                .find('input[name="employee_id"]')
                .val(employee.id);
            $("#addEmployeeForm")
                .find('input[name="full_name"]')
                .val(employee.full_name);
            // Fill other fields...
        },
    });
}

function viewEmployee(id) {
    currentEmployee = id;
    $("#profile-tab").tab("show");
}

function deleteEmployee(id) {
    if (confirm("Bạn có chắc chắn muốn xóa nhân viên này?")) {
        $.ajax({
            url: `${API_BASE_URL}/employees/${id}`,
            method: "DELETE",
            success: function (response) {
                showToast("success", "Xóa nhân viên thành công");
                loadEmployees();
            },
            error: function (error) {
                showToast("error", "Lỗi khi xóa nhân viên");
            },
        });
    }
}

// Initialize dashboard
const dashboard = new Dashboard();
dashboard.init();

// Initialize other modules
document.addEventListener('DOMContentLoaded', () => {
    initRealtimeDashboard();
    initMenuSearch();
    initRecentMenu();
    initGlobalSearch();
    initUserProfile();
    initExportData();
    initAIAnalysis();
    initGamification();
    initMobileStats();
    initActivityFilter();
    initNotificationHandler();
    initLoadingOverlay();
    initDarkMode();
});
