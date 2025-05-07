document.addEventListener("DOMContentLoaded", function() {
    // Kiểm tra đăng nhập
    checkAuth();

    // Lấy ID nhân viên từ URL
    const urlParams = new URLSearchParams(window.location.search);
    const employeeId = urlParams.get("id");

    if (!employeeId) {
        showError("Không tìm thấy ID nhân viên");
        return;
    }

    // Khởi tạo biến
    let employeeData = null;

    // Load dữ liệu nhân viên
    loadEmployeeData(employeeId);

    // Xử lý sự kiện tab
    setupTabs();

    // Xử lý sự kiện nút chỉnh sửa
    setupEditButton();

    // Xử lý sự kiện nút xóa
    setupDeleteButton();

    // Xử lý sự kiện nút đăng xuất
    setupLogoutButton();
});

// Hàm kiểm tra đăng nhập
function checkAuth() {
    const token = localStorage.getItem("token");
    if (!token) {
        window.location.href = "/QLNhanSu_version1/public/login.html";
        return;
    }

    // Kiểm tra quyền admin
    const userRole = localStorage.getItem("role");
    if (userRole !== "admin") {
        window.location.href = "/QLNhanSu_version1/public/login.html";
        return;
    }
}

// Hàm load dữ liệu nhân viên
async function loadEmployeeData(employeeId) {
    try {
        showLoading();
        const response = await fetch(`/QLNhanSu_version1/api/employees.php?action=getById&id=${employeeId}`, {
            headers: {
                "Authorization": `Bearer ${localStorage.getItem("token")}`
            }
        });

        if (!response.ok) {
            throw new Error("Không thể tải dữ liệu nhân viên");
        }

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message);
        }

        employeeData = data.data;
        renderEmployeeData();
        hideLoading();
    } catch (error) {
        hideLoading();
        showError(error.message);
    }
}

// Hàm hiển thị dữ liệu nhân viên
function renderEmployeeData() {
    if (!employeeData) return;

    // Hiển thị thông tin cơ bản
    document.querySelector(".employee-name").textContent = employeeData.full_name;
    document.querySelector(".employee-id").textContent = `ID: ${employeeData.id}`;
    document.querySelector(".employee-position").textContent = employeeData.position_name;

    // Hiển thị thông tin cá nhân
    const personalInfo = document.querySelector(".detail-group:nth-child(1)");
    personalInfo.querySelector(".detail-item:nth-child(1) .value").textContent = employeeData.full_name;
    personalInfo.querySelector(".detail-item:nth-child(2) .value").textContent = employeeData.gender === "M" ? "Nam" : "Nữ";
    personalInfo.querySelector(".detail-item:nth-child(3) .value").textContent = formatDate(employeeData.birth_date);
    personalInfo.querySelector(".detail-item:nth-child(4) .value").textContent = employeeData.identity_card;
    personalInfo.querySelector(".detail-item:nth-child(5) .value").textContent = employeeData.address;

    // Hiển thị thông tin liên hệ
    const contactInfo = document.querySelector(".detail-group:nth-child(2)");
    contactInfo.querySelector(".detail-item:nth-child(1) .value").textContent = employeeData.email;
    contactInfo.querySelector(".detail-item:nth-child(2) .value").textContent = employeeData.phone_number;

    // Hiển thị thông tin công việc
    const workInfo = document.querySelector(".detail-group:nth-child(3)");
    workInfo.querySelector(".detail-item:nth-child(1) .value").textContent = employeeData.department_name;
    workInfo.querySelector(".detail-item:nth-child(2) .value").textContent = employeeData.position_name;
    workInfo.querySelector(".detail-item:nth-child(3) .value").textContent = formatDate(employeeData.hire_date);
    
    const statusElement = workInfo.querySelector(".detail-item:nth-child(4) .status");
    if (employeeData.status === "active") {
        statusElement.textContent = "Đang làm việc";
        statusElement.classList.add("active");
        statusElement.classList.remove("inactive");
    } else {
        statusElement.textContent = "Đã nghỉ việc";
        statusElement.classList.add("inactive");
        statusElement.classList.remove("active");
    }
}

// Hàm xử lý tab
function setupTabs() {
    const tabButtons = document.querySelectorAll(".tab-btn");
    const tabPanes = document.querySelectorAll(".tab-pane");

    tabButtons.forEach(button => {
        button.addEventListener("click", () => {
            // Xóa active class từ tất cả các tab
            tabButtons.forEach(btn => btn.classList.remove("active"));
            tabPanes.forEach(pane => pane.classList.remove("active"));

            // Thêm active class cho tab được chọn
            button.classList.add("active");
            const tabId = button.getAttribute("data-tab");
            document.getElementById(tabId).classList.add("active");

            // Load dữ liệu cho tab được chọn
            loadTabData(tabId);
        });
    });
}

// Hàm load dữ liệu cho tab
async function loadTabData(tabId) {
    if (!employeeData) return;

    const tabPane = document.getElementById(tabId);
    if (!tabPane) return;

    try {
        showLoading();
        let response;
        let endpoint;

        switch (tabId) {
            case "attendance":
                endpoint = `/QLNhanSu_version1/api/attendance.php?action=getByEmployee&employee_id=${employeeData.id}`;
                break;
            case "salary":
                endpoint = `/QLNhanSu_version1/api/salary.php?action=getByEmployee&employee_id=${employeeData.id}`;
                break;
            case "leave":
                endpoint = `/QLNhanSu_version1/api/leave.php?action=getByEmployee&employee_id=${employeeData.id}`;
                break;
            case "training":
                endpoint = `/QLNhanSu_version1/api/training.php?action=getByEmployee&employee_id=${employeeData.id}`;
                break;
            case "documents":
                endpoint = `/QLNhanSu_version1/api/documents.php?action=getByEmployee&employee_id=${employeeData.id}`;
                break;
            default:
                return;
        }

        response = await fetch(endpoint, {
            headers: {
                "Authorization": `Bearer ${localStorage.getItem("token")}`
            }
        });

        if (!response.ok) {
            throw new Error("Không thể tải dữ liệu");
        }

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message);
        }

        renderTabData(tabId, data.data);
        hideLoading();
    } catch (error) {
        hideLoading();
        showError(error.message);
    }
}

// Hàm hiển thị dữ liệu cho tab
function renderTabData(tabId, data) {
    const tabPane = document.getElementById(tabId);
    if (!tabPane) return;

    // Xóa nội dung cũ
    tabPane.innerHTML = "";

    // Tạo bảng dữ liệu
    const table = document.createElement("table");
    table.className = "data-table";

    // Thêm header
    const thead = document.createElement("thead");
    const headerRow = document.createElement("tr");

    // Tạo header dựa trên loại dữ liệu
    switch (tabId) {
        case "attendance":
            headerRow.innerHTML = `
                <th>Ngày</th>
                <th>Giờ vào</th>
                <th>Giờ ra</th>
                <th>Trạng thái</th>
                <th>Ghi chú</th>
            `;
            break;
        case "salary":
            headerRow.innerHTML = `
                <th>Tháng</th>
                <th>Lương cơ bản</th>
                <th>Phụ cấp</th>
                <th>Thưởng</th>
                <th>Tổng lương</th>
            `;
            break;
        case "leave":
            headerRow.innerHTML = `
                <th>Loại nghỉ</th>
                <th>Ngày bắt đầu</th>
                <th>Ngày kết thúc</th>
                <th>Số ngày</th>
                <th>Trạng thái</th>
            `;
            break;
        case "training":
            headerRow.innerHTML = `
                <th>Khóa học</th>
                <th>Ngày bắt đầu</th>
                <th>Ngày kết thúc</th>
                <th>Kết quả</th>
                <th>Ghi chú</th>
            `;
            break;
        case "documents":
            headerRow.innerHTML = `
                <th>Tên tài liệu</th>
                <th>Loại</th>
                <th>Ngày tạo</th>
                <th>Người tạo</th>
                <th>Hành động</th>
            `;
            break;
    }

    thead.appendChild(headerRow);
    table.appendChild(thead);

    // Thêm dữ liệu
    const tbody = document.createElement("tbody");
    data.forEach(item => {
        const row = document.createElement("tr");
        let rowContent = "";

        switch (tabId) {
            case "attendance":
                rowContent = `
                    <td>${formatDate(item.attendance_date)}</td>
                    <td>${item.check_in || "-"}</td>
                    <td>${item.check_out || "-"}</td>
                    <td>${getAttendanceStatus(item.status)}</td>
                    <td>${item.notes || "-"}</td>
                `;
                break;
            case "salary":
                rowContent = `
                    <td>${formatMonth(item.month)}</td>
                    <td>${formatCurrency(item.base_salary)}</td>
                    <td>${formatCurrency(item.allowance)}</td>
                    <td>${formatCurrency(item.bonus)}</td>
                    <td>${formatCurrency(item.total_salary)}</td>
                `;
                break;
            case "leave":
                rowContent = `
                    <td>${getLeaveType(item.leave_type)}</td>
                    <td>${formatDate(item.start_date)}</td>
                    <td>${formatDate(item.end_date)}</td>
                    <td>${item.days}</td>
                    <td>${getLeaveStatus(item.status)}</td>
                `;
                break;
            case "training":
                rowContent = `
                    <td>${item.course_name}</td>
                    <td>${formatDate(item.start_date)}</td>
                    <td>${formatDate(item.end_date)}</td>
                    <td>${item.result || "-"}</td>
                    <td>${item.notes || "-"}</td>
                `;
                break;
            case "documents":
                rowContent = `
                    <td>${item.name}</td>
                    <td>${item.type}</td>
                    <td>${formatDate(item.created_at)}</td>
                    <td>${item.created_by}</td>
                    <td>
                        <button class="view-btn" onclick="viewDocument(${item.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="download-btn" onclick="downloadDocument(${item.id})">
                            <i class="fas fa-download"></i>
                        </button>
                    </td>
                `;
                break;
        }

        row.innerHTML = rowContent;
        tbody.appendChild(row);
    });

    table.appendChild(tbody);
    tabPane.appendChild(table);
}

// Hàm xử lý nút chỉnh sửa
function setupEditButton() {
    const editBtn = document.querySelector(".edit-btn");
    editBtn.addEventListener("click", () => {
        if (!employeeData) return;
        window.location.href = `/QLNhanSu_version1/public/admin/employees/edit.html?id=${employeeData.id}`;
    });
}

// Hàm xử lý nút xóa
function setupDeleteButton() {
    const deleteBtn = document.querySelector(".delete-btn");
    deleteBtn.addEventListener("click", async () => {
        if (!employeeData) return;

        if (!confirm("Bạn có chắc chắn muốn xóa nhân viên này?")) {
            return;
        }

        try {
            showLoading();
            const response = await fetch(`/QLNhanSu_version1/api/employees.php?action=delete&id=${employeeData.id}`, {
                method: "DELETE",
                headers: {
                    "Authorization": `Bearer ${localStorage.getItem("token")}`
                }
            });

            if (!response.ok) {
                throw new Error("Không thể xóa nhân viên");
            }

            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message);
            }

            showSuccess("Xóa nhân viên thành công");
            setTimeout(() => {
                window.location.href = "/QLNhanSu_version1/public/admin/employees/list.html";
            }, 1500);
        } catch (error) {
            hideLoading();
            showError(error.message);
        }
    });
}

// Hàm xử lý nút đăng xuất
function setupLogoutButton() {
    const logoutBtn = document.querySelector(".logout-btn");
    logoutBtn.addEventListener("click", () => {
        localStorage.removeItem("token");
        localStorage.removeItem("role");
        window.location.href = "/QLNhanSu_version1/public/login.html";
    });
}

// Các hàm tiện ích
function formatDate(dateString) {
    if (!dateString) return "-";
    const date = new Date(dateString);
    return date.toLocaleDateString("vi-VN");
}

function formatMonth(monthString) {
    if (!monthString) return "-";
    const [year, month] = monthString.split("-");
    return `${month}/${year}`;
}

function formatCurrency(amount) {
    if (!amount) return "-";
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND"
    }).format(amount);
}

function getAttendanceStatus(status) {
    switch (status) {
        case "present":
            return "<span class=\"status active\">Có mặt</span>";
        case "absent":
            return "<span class=\"status inactive\">Vắng mặt</span>";
        case "late":
            return "<span class=\"status warning\">Đi muộn</span>";
        default:
            return "-";
    }
}

function getLeaveType(type) {
    switch (type) {
        case "annual":
            return "Nghỉ phép năm";
        case "sick":
            return "Nghỉ ốm";
        case "unpaid":
            return "Nghỉ không lương";
        default:
            return type;
    }
}

function getLeaveStatus(status) {
    switch (status) {
        case "approved":
            return "<span class=\"status active\">Đã duyệt</span>";
        case "pending":
            return "<span class=\"status warning\">Chờ duyệt</span>";
        case "rejected":
            return "<span class=\"status inactive\">Từ chối</span>";
        default:
            return status;
    }
}

// Hàm hiển thị loading
function showLoading() {
    const loading = document.createElement("div");
    loading.className = "loading";
    loading.innerHTML = "<div class=\"spinner\"></div>";
    document.body.appendChild(loading);
}

// Hàm ẩn loading
function hideLoading() {
    const loading = document.querySelector(".loading");
    if (loading) {
        loading.remove();
    }
}

// Hàm hiển thị lỗi
function showError(message) {
    const error = document.createElement("div");
    error.className = "error-message";
    error.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(error);
    setTimeout(() => error.remove(), 3000);
}

// Hàm hiển thị thành công
function showSuccess(message) {
    const success = document.createElement("div");
    success.className = "success-message";
    success.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(success);
    setTimeout(() => success.remove(), 3000);
} 