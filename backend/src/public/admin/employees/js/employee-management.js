// Khởi tạo các service thống nhất
const NotificationService = {
    show: function (message, type = "info") {
        const container = document.getElementById("notificationContainer");
        if (!container) return;

        const notification = document.createElement("div");
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        container.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 5000);
    },
    showError: function (message) {
        this.show(message, "danger");
    },
    showSuccess: function (message) {
        this.show(message, "success");
    },
    showFormError: function (field, message) {
        const input = document.getElementById(field);
        if (!input) return;

        input.classList.add("is-invalid");
        const feedback = document.createElement("div");
        feedback.className = "invalid-feedback";
        feedback.textContent = message;
        input.parentNode.appendChild(feedback);
    },
};

const ApiService = {
    async request(url, options = {}) {
        try {
            const response = await fetch(url, {
                ...options,
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    ...options.headers,
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            return data;
        } catch (error) {
            console.error("API Error:", error);
            throw error;
        }
    },
    async get(url) {
        return this.request(url, { method: "GET" });
    },
    async post(url, data) {
        return this.request(url, {
            method: "POST",
            body: JSON.stringify(data),
        });
    },
    async put(url, data) {
        return this.request(url, {
            method: "PUT",
            body: JSON.stringify(data),
        });
    },
    async delete(url) {
        return this.request(url, { method: "DELETE" });
    },
};

const DepartmentService = {
    departments: [],
    async load() {
        try {
            const data = await ApiService.get(
                "/qlnhansu_V2/backend/src/api/departments.php"
            );
            this.departments = Array.isArray(data) ? data : data.data || [];
            return this.departments;
        } catch (error) {
            console.error("Error loading departments:", error);
            throw error;
        }
    },
    getById(id) {
        return this.departments.find((dept) => dept.id === id);
    },
    getByName(name) {
        if (!name) return null;
        return this.departments.find((dept) => dept.name === name);
    },
    getIdByName(name) {
        const dept = this.getByName(name);
        return dept ? dept.id : null;
    },
};

const FormValidationService = {
    validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },
    validatePhone(phone) {
        const phoneRegex = /^\d{10}$/;
        return phoneRegex.test(phone);
    },
    validateDate(dateString) {
        const date = new Date(dateString);
        return date instanceof Date && !isNaN(date);
    },
    validateEmployee(employee) {
        const errors = [];

        if (!employee.full_name || employee.full_name.trim().length < 5) {
            errors.push("Họ và tên phải có ít nhất 5 ký tự");
        }
        if (!this.validateEmail(employee.email)) {
            errors.push("Email không hợp lệ");
        }
        if (!this.validatePhone(employee.phone)) {
            errors.push("Số điện thoại phải có 10 chữ số");
        }
        if (!this.validateDate(employee.birthday)) {
            errors.push("Ngày sinh không hợp lệ");
        }
        if (!employee.department_id) {
            errors.push("Vui lòng chọn phòng ban");
        }
        if (!employee.position_name) {
            errors.push("Vui lòng chọn chức vụ");
        }

        return errors;
    },
};

// Khởi tạo các biến toàn cục
const loadingOverlay = {
    show: function () {
        document.getElementById("loadingOverlay").style.display = "flex";
    },
    hide: function () {
        document.getElementById("loadingOverlay").style.display = "none";
    },
};

// Thêm ModalService
const ModalService = {
    show(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = "block";
            modal.classList.add("active");
            // Add event listener for clicking outside modal
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    this.hide(modalId);
                }
            }.bind(this));
        }
    },
    hide(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove("active");
            setTimeout(() => {
                modal.style.display = "none";
            }, 300);
        }
    },
    resetForm(formId) {
        const form = document.getElementById(formId);
        if (form && typeof form.reset === "function") {
            form.reset();
        }
    },
    // Add new method to handle close button
    handleCloseButton(modalId, closeButtonId) {
        const closeBtn = document.getElementById(closeButtonId);
        if (closeBtn) {
            // Remove any existing click handlers
            const newCloseBtn = closeBtn.cloneNode(true);
            closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);
            
            // Add new click handler
            newCloseBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.hide(modalId);
            });
        }
    }
};

// Thêm EmployeeService
const EmployeeService = {
    async load(page = 1, perPage = 10, filters = {}) {
        try {
            loadingOverlay.show();

            const params = new URLSearchParams({
                page,
                per_page: perPage,
                ...filters,
            });

            const data = await ApiService.get(
                `/qlnhansu_V2/backend/src/api/employees.php?${params.toString()}`
            );

            if (data.success && data.data) {
                return {
                    employees: data.data,
                    pagination: {
                        total: data.total,
                        page: data.page,
                        perPage: data.per_page,
                    },
                };
            } else {
                throw new Error(
                    data.message || "Không thể tải danh sách nhân viên"
                );
            }
        } catch (error) {
            console.error("Lỗi tải nhân viên:", error);
            throw error;
        } finally {
            loadingOverlay.hide();
        }
    },

    async save(employeeData) {
        try {
            loadingOverlay.show();
            const result = await ApiService.post(
                "/qlnhansu_V2/backend/src/api/employees.php",
                employeeData
            );
            return result;
        } catch (error) {
            console.error("Lỗi lưu nhân viên:", error);
            throw error;
        } finally {
            loadingOverlay.hide();
        }
    },

    async update(id, employeeData) {
        try {
            loadingOverlay.show();
            const result = await ApiService.put(
                `/qlnhansu_V2/backend/src/api/employees.php?id=${id}`,
                employeeData
            );
            return result;
        } catch (error) {
            console.error("Lỗi cập nhật nhân viên:", error);
            throw error;
        } finally {
            loadingOverlay.hide();
        }
    },

    async delete(id) {
        try {
            loadingOverlay.show();
            const result = await ApiService.delete(
                `/qlnhansu_V2/backend/src/api/employees.php?id=${id}`
            );
            return result;
        } catch (error) {
            console.error("Lỗi xóa nhân viên:", error);
            throw error;
        } finally {
            loadingOverlay.hide();
        }
    },

    formatDate(dateString) {
        if (!dateString) return null;

        // Kiểm tra định dạng DD/MM/YYYY
        if (dateString.includes("/")) {
            const [day, month, year] = dateString.split("/");
            return `${year}-${month.padStart(2, "0")}-${day.padStart(2, "0")}`;
        }

        return dateString;
    },

    formatDateDisplay(dateString) {
        if (!dateString) return "-";
        try {
            const date = new Date(dateString);
            if (isNaN(date.getTime())) {
                if (dateString.includes("/")) {
                    const parts = dateString.split("/");
                    if (parts.length === 3) {
                        const isoDate = `${parts[2]}-${parts[1].padStart(
                            2,
                            "0"
                        )}-${parts[0].padStart(2, "0")}`;
                        const parsedDate = new Date(isoDate);
                        if (!isNaN(parsedDate.getTime())) {
                            return `${parts[0].padStart(
                                2,
                                "0"
                            )}/${parts[1].padStart(2, "0")}/${parts[2]}`;
                        }
                    }
                }
                return dateString;
            }
            const day = String(date.getDate()).padStart(2, "0");
            const month = String(date.getMonth() + 1).padStart(2, "0");
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        } catch (e) {
            console.error("Error formatting date:", dateString, e);
            return dateString;
        }
    },
};

// Xử lý sự kiện khi trang được tải
document.addEventListener("DOMContentLoaded", async () => {
    // Hiển thị loading
    loadingOverlay.show();

    try {
        // Thêm các event listeners
        addEventListeners();

        // Tải dữ liệu bộ lọc
        await loadFilters();

        // Tải dữ liệu phòng ban
        await loadDepartments();

        // Tải dữ liệu chức vụ
        await loadAllPositions();

        // Tải dữ liệu nhân viên
        await loadEmployees();

        // Tải dữ liệu dashboard
        await loadDashboardStats();

        // Gắn các event listener cho filter và search
        document
            .getElementById("searchInput")
            .addEventListener("input", () => loadEmployees(1));
        document
            .getElementById("departmentFilter")
            .addEventListener("change", () => loadEmployees(1));
        document
            .getElementById("positionFilter")
            .addEventListener("change", () => loadEmployees(1));
        document
            .getElementById("statusFilter")
            .addEventListener("change", () => loadEmployees(1));

        // Thêm listener cho nút Lưu trong modal Add Employee
        const saveButton = document.getElementById("saveEmployeeBtn");
        if (saveButton && !saveButton.hasAttribute("data-listener-added")) {
            saveButton.addEventListener("click", saveEmployee);
            saveButton.setAttribute("data-listener-added", "true");
        }
    } catch (error) {
        console.error("Error during initialization:", error);
        NotificationService.showError("Có lỗi xảy ra khi khởi tạo trang");
    } finally {
        // Ẩn loading
        loadingOverlay.hide();
    }
});

// Thêm các event listeners
function addEventListeners() {
    // Xử lý tìm kiếm
    const searchInput = document.getElementById("searchInput");
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener("input", (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadEmployees(); // Tải lại danh sách nhân viên khi tìm kiếm
            }, 300); // Debounce 300ms
        });
    }

    // Xử lý lọc phòng ban
    const departmentFilter = document.getElementById("departmentFilter");
    if (departmentFilter) {
        departmentFilter.addEventListener("change", async (e) => {
            const departmentId = e.target.value;
            await loadPositionsByDepartment(departmentId);
        });
    }

    // Xử lý lọc chức vụ
    const positionFilter = document.getElementById("positionFilter");
    if (positionFilter) {
        positionFilter.addEventListener("change", () => {
            loadEmployees(); // Tải lại danh sách nhân viên khi thay đổi chức vụ
        });
    }

    // Xử lý lọc trạng thái
    const statusFilter = document.getElementById("statusFilter");
    if (statusFilter) {
        statusFilter.addEventListener("change", () => {
            loadEmployees(); // Tải lại danh sách nhân viên khi thay đổi trạng thái
        });
    }

    // Xử lý nút thêm nhân viên
    document.getElementById("addEmployeeBtn")?.addEventListener("click", () => {
        window.location.href = "add.html";
    });

    // Xử lý các nút thao tác
    document
        .getElementById("employeeTableBody")
        ?.addEventListener("click", (e) => {
            const target = e.target.closest("button");
            if (!target) return;

            const id = target.dataset.id;
            if (!id) return;

            if (target.classList.contains("btn-delete")) {
                e.preventDefault();
                e.stopPropagation();
                deleteEmployee(id);
            }
        });

    // Thêm sự kiện cho nút Xuất Excel
    const exportBtn = document.querySelector(".btn-export");
    if (exportBtn) {
        exportBtn.addEventListener("click", exportEmployeeTableToExcel);
    }

    // Thêm sự kiện cho nút Load lại dữ liệu
    const reloadBtn = document.querySelector(".btn-reload");
    if (reloadBtn) {
        reloadBtn.addEventListener("click", function () {
            loadEmployees();
        });
    }
}

// Hàm tải danh sách nhân viên
async function loadEmployees(page = 1, perPage = 10) {
    try {
        const search = document.getElementById("searchInput").value;
        const departmentId = document.getElementById("departmentFilter").value;
        const positionId = document.getElementById("positionFilter").value;
        const status = document.getElementById("statusFilter").value;

        const filters = {
            ...(search && { search }),
            ...(departmentId && { department_id: departmentId }),
            ...(positionId && { position_id: positionId }),
            ...(status && { status }),
        };

        const result = await EmployeeService.load(page, perPage, filters);
        displayEmployees(result.employees);
        updatePagination(
            result.pagination.total,
            result.pagination.page,
            result.pagination.perPage
        );
    } catch (error) {
        console.error("Lỗi tải nhân viên:", error);
        displayEmployees([]);
        if (error instanceof TypeError && error.message.includes("JSON")) {
            NotificationService.showError(
                "Lỗi: Server trả về dữ liệu không hợp lệ"
            );
        } else {
            NotificationService.showError(
                "Có lỗi xảy ra khi tải danh sách nhân viên: " + error.message
            );
        }
    }
}

// Hàm cập nhật phân trang
function updatePagination(total, currentPage, perPage) {
    const totalPages = Math.ceil(total / perPage);
    const pagination = document.getElementById("pagination");
    pagination.innerHTML = "";

    // Nút Previous
    const prevLi = document.createElement("li");
    prevLi.className = `page-item ${currentPage === 1 ? "disabled" : ""}`;
    prevLi.innerHTML = `
        <a class="page-link" href="#" aria-label="Previous" ${
            currentPage === 1 ? 'tabindex="-1"' : ""
        }>
            <span aria-hidden="true">&laquo;</span>
        </a>
    `;
    prevLi.addEventListener("click", (e) => {
        e.preventDefault();
        if (currentPage > 1) {
            loadEmployees(currentPage - 1, perPage);
        }
    });
    pagination.appendChild(prevLi);

    // Các nút trang
    for (let i = 1; i <= totalPages; i++) {
        const li = document.createElement("li");
        li.className = `page-item ${i === currentPage ? "active" : ""}`;
        li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
        li.addEventListener("click", (e) => {
            e.preventDefault();
            loadEmployees(i, perPage);
        });
        pagination.appendChild(li);
    }

    // Nút Next
    const nextLi = document.createElement("li");
    nextLi.className = `page-item ${
        currentPage === totalPages ? "disabled" : ""
    }`;
    nextLi.innerHTML = `
        <a class="page-link" href="#" aria-label="Next" ${
            currentPage === totalPages ? 'tabindex="-1"' : ""
        }>
            <span aria-hidden="true">&raquo;</span>
        </a>
    `;
    nextLi.addEventListener("click", (e) => {
        e.preventDefault();
        if (currentPage < totalPages) {
            loadEmployees(currentPage + 1, perPage);
        }
    });
    pagination.appendChild(nextLi);
}

// Hàm hiển thị danh sách nhân viên
function displayEmployees(employees) {
    const tbody = document.getElementById("employeeTableBody");
    tbody.innerHTML = "";

    if (!employees || employees.length === 0) {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td colspan="11" class="text-center py-4">
                <div class="no-data-message">
                    <i class="fas fa-info-circle"></i>
                    <p>Không có dữ liệu</p>
                </div>
            </td>
        `;
        tbody.appendChild(row);
        return;
    }

    // Sắp xếp nhân viên theo phòng ban
    const sortedEmployees = [...employees].sort((a, b) => {
        const deptA = a.department_name || "";
        const deptB = b.department_name || "";
        return deptA.localeCompare(deptB);
    });

    sortedEmployees.forEach((employee, index) => {
        const row = document.createElement("tr");
        row.setAttribute("data-employee-id", employee.id);

        // Chọn ảnh đại diện: ưu tiên avatar_url, nếu không có thì theo giới tính
        let avatarSrc = "";
        if (employee.avatar_url && employee.avatar_url.trim() !== "") {
            avatarSrc = employee.avatar_url;
        } else if (employee.gender === "Female") {
            avatarSrc = "human.png";
        } else {
            avatarSrc = "employee.png";
        }

        // Format status
        const statusClass =
            employee.status === "active"
                ? "badge-success"
                : employee.status === "inactive"
                ? "badge-warning"
                : employee.status === "terminated"
                ? "badge-danger"
                : "badge-info";
        const statusText =
            employee.status === "active"
                ? "Đang làm việc"
                : employee.status === "inactive"
                ? "Nghỉ việc"
                : employee.status === "terminated"
                ? "Đã nghỉ"
                : "Nghỉ phép";

        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${employee.employee_code || ""}</td>
            <td>
                <img src="${avatarSrc}" 
                     alt="Avatar" 
                     class="rounded-circle"
                     width="40" 
                     height="40">
            </td>
            <td class="editable" data-field="full_name">${
                employee.full_name || employee.username || ""
            }</td>
            <td class="editable" data-field="birthday">${
                employee.birth_date
                    ? new Date(employee.birth_date).toLocaleDateString("vi-VN")
                    : ""
            }</td>
            <td class="editable" data-field="phone">${
                employee.phone_number || ""
            }</td>
            <td class="editable" data-field="status"><span class="badge ${statusClass}">${statusText}</span></td>
            <td class="editable" data-field="department_name" data-department-id="${
                employee.department_id || ""
            }">${employee.department_name || ""}</td>
            <td class="editable" data-field="position_name" data-position-id="${
                employee.position_id || ""
            }">${employee.position_name || ""}</td>
            <td class="editable" data-field="email">${employee.email || ""}</td>
            <td>
                <div style="font-size: 13px;" class="action-buttons">
                    <button style="font-size: 13px;" class="btn-action btn-edit edit-btn" data-id="${
                        employee.id
                    }">Sửa</button>
                    <button style="font-size: 13px;" class="btn-action btn-delete delete-btn" data-id="${
                        employee.id
                    }">Xóa</button>
                </div>
            </td>
        `;

        tbody.appendChild(row);
    });

    // Thêm event listener cho nút Sửa
    document.querySelectorAll(".edit-btn").forEach((button) => {
        button.addEventListener("click", function (e) {
            e.stopPropagation();
            const employeeId = this.dataset.id;
            startEditEmployee(employeeId);
        });
    });

    // Thêm event listener cho nút Xóa
    document.querySelectorAll(".delete-btn").forEach((button) => {
        button.addEventListener("click", function (e) {
            e.stopPropagation();
            const employeeId = this.dataset.id;
            deleteEmployee(employeeId);
        });
    });
}

// Thêm CSS cho thông báo không có dữ liệu
const style = document.createElement("style");
style.textContent = `
    .no-data-message {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        color: #6c757d;
    }
    .no-data-message i {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
    .no-data-message p {
        margin: 0;
        font-size: 1.1rem;
    }
    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
        color: white;
    }
    .btn-success:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }
`;
document.head.appendChild(style);

// Hàm tải chức vụ theo phòng ban
async function loadPositionsByDepartment(departmentId) {
    try {
        const positionFilter = document.getElementById("positionFilter");
        if (!positionFilter) return;

        // Reset dropdown chức vụ
        positionFilter.innerHTML = '<option value="">Tất cả chức vụ</option>';

        // Nếu không chọn phòng ban, không cần load chức vụ
        if (!departmentId) {
            await loadEmployees();
            return;
        }

        // Gọi API lấy chức vụ theo phòng ban
        const data = await ApiService.get(
            `/qlnhansu_V2/backend/src/api/positions.php?department_id=${departmentId}`
        );

        if (data.success && data.data) {
            // Thêm các chức vụ vào dropdown
            data.data.forEach((pos) => {
                const option = document.createElement("option");
                option.value = pos.id;
                option.textContent = pos.name;
                positionFilter.appendChild(option);
            });
        } else {
            NotificationService.showError(
                data.message || "Không thể tải danh sách chức vụ"
            );
        }

        // Tải lại danh sách nhân viên sau khi load chức vụ
        await loadEmployees();
    } catch (error) {
        console.error("Lỗi tải chức vụ:", error);
        NotificationService.showError(
            "Có lỗi xảy ra khi tải danh sách chức vụ"
        );
    }
}

// Hàm tải bộ lọc
async function loadFilters() {
    try {
        // Tải danh sách phòng ban
        const deptData = await ApiService.get(
            "/qlnhansu_V2/backend/src/api/departments.php"
        );

        if (deptData.success && deptData.data) {
            // Tải phòng ban
            const departmentFilter =
                document.getElementById("departmentFilter");
            if (departmentFilter) {
                departmentFilter.innerHTML =
                    '<option value="">Tất cả phòng ban</option>';
                deptData.data.forEach((dept) => {
                    const option = document.createElement("option");
                    option.value = dept.id;
                    option.textContent = dept.name;
                    departmentFilter.appendChild(option);
                });
            }
        }

        // Reset dropdown chức vụ
        const positionFilter = document.getElementById("positionFilter");
        if (positionFilter) {
            positionFilter.innerHTML =
                '<option value="">Tất cả chức vụ</option>';
        }

        // Tải lại danh sách nhân viên sau khi load bộ lọc
        await loadEmployees();
    } catch (error) {
        console.error("Lỗi tải bộ lọc:", error);
        NotificationService.showError("Có lỗi xảy ra khi tải dữ liệu bộ lọc");
    }
}

// Hàm lọc nhân viên
function filterEmployees(
    searchTerm = null,
    departmentId = null,
    positionId = null,
    status = null
) {
    const rows = document.querySelectorAll("#employeeTableBody tr");
    let visibleCount = 0;

    rows.forEach((row) => {
        const name = row
            .querySelector("td:nth-child(4)")
            .textContent.toLowerCase();
        const empCode = row
            .querySelector("td:nth-child(2)")
            .textContent.toLowerCase();
        const deptId =
            row.querySelector("td:nth-child(8)").dataset.departmentId;
        const posId = row.querySelector("td:nth-child(9)").dataset.positionId;
        const stat = row
            .querySelector("td:nth-child(7)")
            .textContent.toLowerCase();

        let show = true;

        // Lọc theo từ khóa tìm kiếm
        if (searchTerm) {
            const searchLower = searchTerm.toLowerCase();
            if (!name.includes(searchLower) && !empCode.includes(searchLower)) {
                show = false;
            }
        }

        // Lọc theo phòng ban
        if (departmentId && departmentId !== "") {
            if (deptId !== departmentId) {
                show = false;
            }
        }

        // Lọc theo chức vụ
        if (positionId && positionId !== "") {
            if (posId !== positionId) {
                show = false;
            }
        }

        // Lọc theo trạng thái
        if (status && status !== "") {
            const statusMap = {
                active: "đang làm việc",
                inactive: "nghỉ việc",
                terminated: "đã nghỉ",
                on_leave: "nghỉ phép",
            };
            if (!stat.includes(statusMap[status])) {
                show = false;
            }
        }

        row.style.display = show ? "" : "none";
        if (show) visibleCount++;
    });

    // Cập nhật số lượng nhân viên hiển thị
    updateEmployeeCounts(visibleCount);
}

// Hàm cập nhật số lượng nhân viên
function updateEmployeeCounts(visibleCount) {
    const totalEmployees = document.getElementById("totalEmployees");
    if (totalEmployees) {
        totalEmployees.textContent = visibleCount;
    }
}

// Hàm hiển thị lỗi
function showError(message) {
    const notificationContainer = document.getElementById(
        "notificationContainer"
    );
    if (!notificationContainer) return;

    const notification = document.createElement("div");
    notification.className = "alert alert-danger alert-dismissible fade show";
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    notificationContainer.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Hàm hiển thị thành công
function showSuccess(message) {
    const notificationContainer = document.getElementById(
        "notificationContainer"
    );
    if (!notificationContainer) return;

    const notification = document.createElement("div");
    notification.className = "alert alert-success alert-dismissible fade show";
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    notificationContainer.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Hàm tải dữ liệu dashboard
async function loadDashboardStats() {
    try {
        // Lấy tổng số nhân viên
        const empRes = await fetch(
            "/qlnhansu_V2/backend/src/api/employees.php?page=1&per_page=1",
            {
                method: "GET",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
            }
        );
        let totalEmployees = 0;
        if (empRes.ok) {
            const empData = await empRes.json();
            if (empData.total !== undefined) {
                totalEmployees = empData.total;
                document.getElementById("totalEmployees").textContent =
                    totalEmployees;
            }
        }

        // Lấy tổng số phòng ban
        const deptRes = await fetch(
            "/qlnhansu_V2/backend/src/api/departments.php",
            {
                method: "GET",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
            }
        );
        if (deptRes.ok) {
            const deptData = await deptRes.json();
            if (deptData.success && Array.isArray(deptData.data)) {
                document.getElementById("totalDepartments").textContent =
                    deptData.data.length;
            }
        }

        // Đang làm việc = tổng nhân viên (giả sử tất cả đều đang làm việc, hoặc cần API riêng để lấy số đang làm việc)
        document.getElementById("activeEmployees").textContent = totalEmployees;
        // Nhân viên mới: cần API riêng, hoặc để 0 nếu chưa có
        document.getElementById("newEmployees").textContent = 0;
    } catch (err) {
        console.error("Lỗi tải dữ liệu dashboard:", err);
    }
}

function exportEmployeeTableToExcel() {
    // Lấy bảng
    const table = document.querySelector(".employee-table table");
    if (!table) return;

    // Tạo mảng dữ liệu, loại bỏ cột "Thao tác"
    const rows = Array.from(table.querySelectorAll("tr")).map((tr) => {
        const cells = Array.from(tr.querySelectorAll("th,td"));
        // Loại bỏ cột cuối cùng (Thao tác)
        return cells.slice(0, -1).map((cell) => cell.innerText.trim());
    });

    // Tạo worksheet và workbook
    const ws = XLSX.utils.aoa_to_sheet(rows);

    // Định dạng: bôi đậm dòng tiêu đề, căn giữa, border
    const range = XLSX.utils.decode_range(ws["!ref"]);
    for (let C = range.s.c; C <= range.e.c; ++C) {
        const cellAddress = XLSX.utils.encode_cell({ r: 0, c: C });
        if (!ws[cellAddress]) continue;
        ws[cellAddress].s = {
            font: { bold: true, color: { rgb: "FFFFFF" } },
            alignment: { horizontal: "center", vertical: "center" },
            fill: { fgColor: { rgb: "4F8CFF" } },
        };
    }
    // Thêm border cho toàn bộ bảng
    for (let R = range.s.r; R <= range.e.r; ++R) {
        for (let C = range.s.c; C <= range.e.c; ++C) {
            const cellAddress = XLSX.utils.encode_cell({ r: R, c: C });
            if (!ws[cellAddress]) continue;
            ws[cellAddress].s = ws[cellAddress].s || {};
            ws[cellAddress].s.border = {
                top: { style: "thin", color: { rgb: "CCCCCC" } },
                bottom: { style: "thin", color: { rgb: "CCCCCC" } },
                left: { style: "thin", color: { rgb: "CCCCCC" } },
                right: { style: "thin", color: { rgb: "CCCCCC" } },
            };
        }
    }
    // Tự động căn chỉnh độ rộng cột
    ws["!cols"] = rows[0].map((_, i) => {
        const maxLen = Math.max(
            ...rows.map((row) => (row[i] ? row[i].length : 0))
        );
        return { wch: Math.max(10, maxLen + 2) };
    });

    // Tạo workbook và xuất file
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "DanhSachNhanVien");
    XLSX.writeFile(wb, "DanhSachNhanVien.xlsx");
}

// Thêm các hàm vào window để có thể gọi từ HTML
window.startEditEmployee = startEditEmployee;
window.saveEditEmployee = saveEditEmployee;
window.cancelEditEmployee = cancelEditEmployee;

// Hàm xóa nhân viên
async function deleteEmployee(id) {
    if (confirm("Bạn có chắc chắn muốn xóa nhân viên này?")) {
        try {
            const result = await EmployeeService.delete(id);

            if (result.success) {
                NotificationService.showSuccess("Xóa nhân viên thành công");
                await loadEmployees();
            } else {
                let errorMessage = result.message || "Không thể xóa nhân viên";
                if (errorMessage.includes("tài sản")) {
                    errorMessage +=
                        '<br><a href="../assets/index.html" class="alert-link">Quản lý tài sản</a>';
                }
                NotificationService.showError(errorMessage);
            }
        } catch (error) {
            console.error("Lỗi xóa nhân viên:", error);
            NotificationService.showError("Có lỗi xảy ra khi xóa nhân viên");
        }
    }
}

let departmentsData = [];

// Hàm load dữ liệu phòng ban với xử lý lỗi và loading state
async function loadDepartments() {
    try {
        showLoading();

        const response = await fetch(
            "/qlnhansu_V2/backend/src/api/departments.php",
            {
                method: "GET",
                headers: {
                    Accept: "application/json",
                },
            }
        );

        // Kiểm tra content type
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            throw new Error(
                `API không trả về JSON. Content-Type: ${contentType}`
            );
        }

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log("API Response:", data);

        // Kiểm tra cấu trúc response
        if (Array.isArray(data)) {
            departmentsData = data;
        }
        // Nếu response có format {data}
        else if (Array.isArray(data.data)) {
            departmentsData = data.data;
        } else {
            throw new Error("Cấu trúc dữ liệu không hợp lệ");
        }

        // Cập nhật số lượng phòng ban trong thẻ thống kê
        document.getElementById("totalDepartments").textContent =
            departmentsData.length;

        // Cập nhật dropdown filter
        updateDepartmentFilter();

        // Cập nhật dropdown trong form thêm nhân viên
        updateDepartmentForm();

        // Hiển thị thông báo thành công
        showNotification("Đã tải danh sách phòng ban thành công", "success");
    } catch (error) {
        // console.error('Error loading departments:', error);
        // showNotification(`Lỗi khi tải danh sách phòng ban: ${error.message}`, 'error');

        // Nếu có lỗi, hiển thị option mặc định
        const defaultOption = '<option value="">Tất cả phòng ban</option>';
        document.getElementById("departmentFilter").innerHTML = defaultOption;
        const formSelect = document.getElementById("departmentId");
        if (formSelect) {
            formSelect.innerHTML = '<option value="">Chọn phòng ban</option>';
        }
    } finally {
        // Ẩn loading
        hideLoading();
    }
}

// Hàm cập nhật dropdown filter với hiệu ứng mượt mà
function updateDepartmentFilter() {
    const filterSelect = document.getElementById("departmentFilter");
    if (!filterSelect) {
        console.error("Không tìm thấy element departmentFilter");
        return;
    }

    const currentValue = filterSelect.value;

    // Tạo HTML cho các option
    let optionsHTML = '<option value="">Tất cả phòng ban</option>';

    // Kiểm tra và log dữ liệu
    console.log("Departments data:", departmentsData);

    if (Array.isArray(departmentsData)) {
        departmentsData.forEach((dept) => {
            if (dept && dept.id && dept.name) {
                optionsHTML += `<option value="${dept.id}" ${
                    currentValue === dept.id ? "selected" : ""
                }>${dept.name}</option>`;
            } else {
                console.warn("Invalid department data:", dept);
            }
        });
    } else {
        console.error("departmentsData is not an array:", departmentsData);
    }

    // Thêm hiệu ứng fade out
    filterSelect.style.opacity = "0";

    setTimeout(() => {
        // Cập nhật nội dung
        filterSelect.innerHTML = optionsHTML;

        // Thêm hiệu ứng fade in
        filterSelect.style.opacity = "1";
    }, 300);
}

// Hàm cập nhật dropdown trong form thêm nhân viên
function updateDepartmentForm() {
    const formSelect = document.getElementById("departmentId");
    if (!formSelect) {
        console.error("Không tìm thấy element departmentId");
        return;
    }

    const currentValue = formSelect.value;

    // Tạo HTML cho các option
    let optionsHTML = '<option value="">Chọn phòng ban</option>';

    if (Array.isArray(departmentsData)) {
        departmentsData.forEach((dept) => {
            if (dept && dept.id && dept.name) {
                optionsHTML += `<option value="${dept.id}" ${
                    currentValue === dept.id ? "selected" : ""
                }>${dept.name}</option>`;
            }
        });
    }

    // Thêm hiệu ứng fade out
    formSelect.style.opacity = "0";

    setTimeout(() => {
        // Cập nhật nội dung
        formSelect.innerHTML = optionsHTML;

        // Thêm hiệu ứng fade in
        formSelect.style.opacity = "1";
    }, 300);
}

// Hàm kiểm tra xem phòng ban có tồn tại không
function isValidDepartment(departmentName) {
    return getDepartmentIdByName(departmentName) !== null;
}

// Hàm lấy tên phòng ban theo ID
function getDepartmentName(id) {
    if (!id) return "";
    const department = departmentsData.find((dept) => dept.id === id);
    return department ? department.name : "";
}

// Hàm lấy ID phòng ban theo tên
function getDepartmentIdByName(departmentName) {
    const departments = window.departments || [];
    const department = departments.find(d => d.name.toLowerCase() === departmentName.toLowerCase());
    return department ? department.id : null;
}

// Xử lý sự kiện khi thay đổi phòng ban trong filter
document
    .getElementById("departmentFilter")
    .addEventListener("change", function () {
        const departmentId = this.value;
        const positionFilter = document.getElementById("positionFilter");

        // Reset position filter
        positionFilter.innerHTML = '<option value="">Tất cả chức vụ</option>';

        // Nếu có chọn phòng ban, load danh sách chức vụ
        if (departmentId) {
            loadPositions(departmentId);
        }

        // Load lại danh sách nhân viên với filter mới
        loadEmployees(1);
    });

// Hàm xử lý khi thay đổi chức vụ
function handlePositionChange() {
    const positionSelect = document.getElementById("positionId");
    const newPositionGroup = document.getElementById("newPositionGroup");
    const newPositionInput = document.getElementById("newPosition");

    if (positionSelect.value === "new") {
        // Hiển thị ô nhập chức vụ mới
        newPositionGroup.style.display = "block";
        newPositionInput.required = true;
        newPositionInput.disabled = false;
        newPositionInput.style.backgroundColor = "#fff";
    } else {
        // Ẩn ô nhập chức vụ mới
        newPositionGroup.style.display = "none";
        newPositionInput.required = false;
        newPositionInput.disabled = true;
        newPositionInput.style.backgroundColor = "#f8f9fa";
    }
}

// Sửa lại hàm loadPositions
async function loadPositions(departmentId) {
    try {
        const response = await fetch(
            `/qlnhansu_V2/backend/src/api/positions.php?department_id=${departmentId}`,
            {
                method: "GET",
                headers: {
                    Accept: "application/json",
                },
            }
        );

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log("Positions Response:", data);

        // Lấy select element cho vị trí
        const positionSelect = document.getElementById("positionId");
        if (!positionSelect) return;

        // Reset options nhưng giữ lại option "Thêm chức vụ mới"
        positionSelect.innerHTML =
            '<option value="">Chọn chức vụ</option><option value="new">+ Thêm chức vụ mới</option>';

        // Thêm các vị trí vào select
        if (Array.isArray(data)) {
            data.forEach((position) => {
                if (position && position.id && position.name) {
                    const option = document.createElement("option");
                    option.value = position.id;
                    option.textContent = position.name;
                    positionSelect.appendChild(option);
                }
            });
        } else if (data.data && Array.isArray(data.data)) {
            data.data.forEach((position) => {
                if (position && position.id && position.name) {
                    const option = document.createElement("option");
                    option.value = position.id;
                    option.textContent = position.name;
                    positionSelect.appendChild(option);
                }
            });
        }

        // Reset trạng thái ô nhập chức vụ mới
        const newPositionGroup = document.getElementById("newPositionGroup");
        const newPositionInput = document.getElementById("newPosition");
        newPositionGroup.style.display = "none";
        newPositionInput.value = "";
        newPositionInput.required = false;
        newPositionInput.disabled = true;
        newPositionInput.style.backgroundColor = "#f8f9fa";
    } catch (error) {
        console.error("Error loading positions:", error);
        // this.showNotification('Không thể tải danh sách chức vụ', 'error');
    }
}

// Hàm thêm thành viên gia đình mới
function addFamilyMember() {
    const familyMembers = document.getElementById("familyMembers");
    const newMember = document.createElement("div");
    newMember.className = "family-member";
    newMember.innerHTML = `
                <div class="form-grid">
                    <div class="form-group">
                        <label class="required-field">Tên thành viên</label>
                        <input type="text" class="form-control member-name" required>
                    </div>
                    <div class="form-group">
                        <label class="required-field">Mối quan hệ</label>
                        <select class="form-control relationship" required>
                            <option value="">Chọn mối quan hệ</option>
                            <option value="Vợ">Vợ</option>
                            <option value="Chồng">Chồng</option>
                            <option value="Con">Con</option>
                            <option value="Cha">Cha</option>
                            <option value="Mẹ">Mẹ</option>
                            <option value="Anh trai">Anh trai</option>
                            <option value="Chị gái">Chị gái</option>
                            <option value="Em trai">Em trai</option>
                            <option value="Em gái">Em gái</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ngày sinh</label>
                        <input type="date" class="form-control member-birthday">
                    </div>
                    <div class="form-group">
                        <label>Nghề nghiệp</label>
                        <input type="text" class="form-control member-occupation">
                    </div>
                    <div class="form-group">
                        <label class="d-flex align-items-center">
                            <input type="checkbox" class="member-dependent me-2"> Người phụ thuộc
                        </label>
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-member">
                    <i class="fas fa-trash"></i> Xóa
                </button>
            `;

    // Thêm hiệu ứng fade in
    newMember.style.opacity = "0";
    familyMembers.appendChild(newMember);
    setTimeout(() => {
        newMember.style.opacity = "1";
    }, 10);

    // Thêm sự kiện xóa cho nút xóa
    const removeButton = newMember.querySelector(".remove-member");
    removeButton.addEventListener("click", function () {
        // Thêm hiệu ứng fade out
        newMember.style.opacity = "0";
        setTimeout(() => {
            familyMembers.removeChild(newMember);
        }, 300);
    });
}

// Thêm sự kiện cho nút Lưu khi DOM đã load
document.addEventListener("DOMContentLoaded", function () {
    const saveButton = document.getElementById("saveEmployeeBtn");
    if (saveButton) {
        saveButton.addEventListener("click", saveEmployee);
    }
});

// Sửa lại hàm saveEmployee
async function saveEmployee() {
    try {
        const form = document.getElementById("addEmployeeForm");
        if (!form) {
            throw new Error("Không tìm thấy form thêm nhân viên");
        }

        // Lấy dữ liệu gia đình
        const familyMembers = [];
        document.querySelectorAll(".family-member").forEach((member) => {
            const name = member.querySelector(".member-name").value;
            const relationship = member.querySelector(".relationship").value;
            const birthday = member.querySelector(".member-birthday").value;
            const occupation = member.querySelector(".member-occupation").value;
            const isDependent =
                member.querySelector(".member-dependent").checked;

            if (name && relationship) {
                familyMembers.push({
                    name,
                    relationship,
                    birthday: birthday
                        ? EmployeeService.formatDate(birthday)
                        : null,
                    occupation,
                    is_dependent: isDependent,
                });
            }
        });

        // Lấy dữ liệu chức vụ
        const positionId = form.positionId.value;
        let positionData = {};

        if (positionId === "new") {
            const newPositionName =
                document.getElementById("newPosition").value;
            if (!newPositionName) {
                NotificationService.showFormError(
                    "newPosition",
                    "Vui lòng nhập tên chức vụ mới"
                );
                return;
            }
            positionData = {
                name: newPositionName,
                department_id: DepartmentService.getIdByName(
                    form.departmentId.value
                ),
            };
        } else {
            positionData = {
                position_id: positionId,
            };
        }

        // Tạo object dữ liệu nhân viên
        const employeeData = {
            name: document.getElementById("employeeName").value,
            full_name: document.getElementById("employeeFullName").value,
            email: document.getElementById("employeeEmail").value,
            phone: document.getElementById("employeePhone").value,
            birthday: EmployeeService.formatDate(
                document.getElementById("employeeBirthday").value
            ),
            address: document.getElementById("employeeAddress").value,
            employee_code: document.getElementById("employeeCode").value,
            department_id: DepartmentService.getIdByName(
                document.getElementById("departmentId").value
            ),
            hire_date: EmployeeService.formatDate(
                document.getElementById("hireDate").value
            ),
            contract_type: document.getElementById("contractType").value,
            base_salary: parseFloat(
                document.getElementById("baseSalary").value
            ),
            contract_start_date: EmployeeService.formatDate(
                document.getElementById("contractStartDate").value
            ),
            family_members: familyMembers,
        };

        // Validate dữ liệu
        const validationErrors =
            FormValidationService.validateEmployee(employeeData);
        if (validationErrors.length > 0) {
            validationErrors.forEach((error) => {
                NotificationService.showFormError("general", error);
            });
            return;
        }

        // Gửi dữ liệu lên server
        const result = await EmployeeService.save(employeeData);

        if (result.success) {
            NotificationService.showSuccess("Thêm nhân viên thành công");
            setTimeout(() => {
                closeAddEmployeeModal();
                loadEmployees();
            }, 2000);
        } else {
            if (result.errors) {
                Object.entries(result.errors).forEach(([field, messages]) => {
                    NotificationService.showFormError(
                        field,
                        Array.isArray(messages) ? messages.join(", ") : messages
                    );
                });
            } else {
                NotificationService.showFormError(
                    "general",
                    result.message || "Lỗi không xác định khi lưu nhân viên"
                );
            }
        }
    } catch (error) {
        console.error("Error saving employee:", error);
        NotificationService.showFormError(
            "general",
            error.message || "Lỗi không xác định"
        );
    }
}

// Hàm hiển thị lỗi cho trường cụ thể
function showFormError(field, message) {
    // Xóa tất cả các thông báo lỗi cũ
    document.querySelectorAll(".alert-danger").forEach((el) => el.remove());
    document
        .querySelectorAll(".is-invalid")
        .forEach((el) => el.classList.remove("is-invalid"));
    document.querySelectorAll(".invalid-feedback").forEach((el) => el.remove());

    if (field === "general") {
        // Hiển thị lỗi chung
        const errorDiv = document.createElement("div");
        errorDiv.className = "alert alert-danger";
        errorDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${message}
                `;
        document
            .getElementById("addEmployeeForm")
            .insertBefore(
                errorDiv,
                document.getElementById("addEmployeeForm").firstChild
            );
        return;
    }

    // Tìm input tương ứng
    const input = document.getElementById(field);
    if (!input) return;

    // Thêm class invalid
    input.classList.add("is-invalid");

    // Tạo thông báo lỗi
    const feedback = document.createElement("div");
    feedback.className = "invalid-feedback";
    feedback.textContent = message;

    // Thêm thông báo lỗi sau input
    input.parentNode.appendChild(feedback);

    // Cuộn đến trường bị lỗi
    input.scrollIntoView({ behavior: "smooth", block: "center" });
}

// Hàm validate dữ liệu nhân viên
function validateEmployeeData(employee) {
    const errors = [];
    
    if (!employee.full_name || employee.full_name.trim().length < 5) {
        errors.push("Họ và tên phải có ít nhất 5 ký tự");
    }
    if (!this.validateEmail(employee.email)) {
        errors.push("Email không hợp lệ");
    }
    if (!this.validatePhone(employee.phone)) {
        errors.push("Số điện thoại phải có 10 chữ số");
    }
    if (!this.validateDate(employee.birthday)) {
        errors.push("Ngày sinh không hợp lệ");
    }
    if (!employee.department_id) {
        errors.push("Vui lòng chọn phòng ban");
    }
    if (!employee.position_name) {
        errors.push("Vui lòng chọn chức vụ");
    }

    return errors;
}

// Load dữ liệu khi trang được tải
document.addEventListener("DOMContentLoaded", function () {
    loadDepartments();
});

// Hàm hiển thị loading
function showLoading() {
    const loadingOverlay = document.getElementById("loadingOverlay");
    if (loadingOverlay) {
        loadingOverlay.style.display = "flex";
        loadingOverlay.style.opacity = "1";
        // Ngăn chặn scroll khi đang loading
        document.body.style.overflow = "hidden";
    }
}

// Hàm ẩn loading
function hideLoading() {
    const loadingOverlay = document.getElementById("loadingOverlay");
    if (loadingOverlay) {
        loadingOverlay.style.opacity = "0";
        setTimeout(() => {
            loadingOverlay.style.display = "none";
            // Cho phép scroll lại
            document.body.style.overflow = "auto";
        }, 300);
    }
}

// Hàm reload dữ liệu nhân viên
async function reloadEmployeeData() {
    try {
        // Hiển thị loading
        showLoading();

        // Reset tất cả các filter về trạng thái ban đầu
        document.getElementById("searchInput").value = "";
        document.getElementById("departmentFilter").value = "";
        document.getElementById("positionFilter").value = "";
        document.getElementById("statusFilter").value = "";

        // Load lại dữ liệu nhân viên với trang đầu tiên
        await loadEmployees(1);

        // Hiển thị thông báo thành công
        showNotification("Đã tải lại dữ liệu thành công", "success");
    } catch (error) {
        console.error("Error reloading data:", error);
        showNotification(`Lỗi khi tải lại dữ liệu: ${error.message}`, "error");
    } finally {
        // Ẩn loading
        hideLoading();
    }
}

// Hàm hiển thị modal thêm nhân viên
function showAddEmployeeModal() {
    ModalService.show("addEmployeeModal");
    ModalService.resetForm("addEmployeeForm");
    generateEmployeeCode();
}

// Hàm đóng modal thêm nhân viên
function closeAddEmployeeModal() {
    ModalService.hide("addEmployeeModal");
}

// Hàm tạo mã nhân viên tự động
function generateEmployeeCode() {
    const prefix = "NV";
    const random = Math.floor(Math.random() * 10000)
        .toString()
        .padStart(4, "0");
    const date = new Date();
    const year = date.getFullYear().toString().slice(-2);
    const month = (date.getMonth() + 1).toString().padStart(2, "0");
    const employeeCode = `${prefix}${year}${month}${random}`;
    document.getElementById("employeeCode").value = employeeCode;
}

// Thêm sự kiện change cho input file
document
    .getElementById("employeeFile")
    .addEventListener("change", function (e) {
        const file = e.target.files[0];
        if (file) {
            // Kiểm tra xem modal có đang mở không
            const modal = document.getElementById("addEmployeeByFileModal");
            if (modal && modal.classList.contains("active")) {
                previewFile();
            }
        }
    });

// Hàm hiển thị modal thêm nhân viên bằng file
function showAddEmployeeByFileModal() {
    ModalService.show("addEmployeeByFileModal");
    ModalService.resetForm("employeeFile");
    document.getElementById("previewSection").classList.add("hidden");
    document.getElementById("uploadStatus").innerHTML = "";
    // Reset file input
    const fileInput = document.getElementById("employeeFile");
    if (fileInput) {
        fileInput.value = "";
    }
}

// Hàm đóng modal thêm nhân viên bằng file
function closeAddEmployeeByFileModal() {
    ModalService.hide("addEmployeeByFileModal");
}

// Xử lý sự kiện click bên ngoài modal để đóng
window.onclick = function (event) {
    if (event.target.id === "addEmployeeModal") {
        closeAddEmployeeModal();
    }
    if (event.target.id === "addEmployeeByFileModal") {
        closeAddEmployeeByFileModal();
    }
};

// Hàm kiểm tra email
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Hàm kiểm tra số điện thoại
function isValidPhone(phone) {
    // Kiểm tra số điện thoại bắt đầu bằng 0 và có 10 chữ số
    return /^0\d{9}$/.test(phone);
}

// Hàm kiểm tra ngày hợp lệ
function isValidDate(dateString) {
    const date = new Date(dateString);
    return date instanceof Date && !isNaN(date);
}

// Hàm kiểm tra ngày trong tương lai
function isFutureDate(dateString) {
    const date = new Date(dateString);
    const today = new Date();
    return date > today;
}

// Sửa lại hàm previewFile để mapping đúng với file có cột EMP ở đầu
async function previewFile() {
    const fileInput = document.getElementById("employeeFile");
    const file = fileInput.files[0];
    const errorContainer = document.getElementById("uploadError");
    const previewSection = document.getElementById("previewSection");
    const previewTableBody = document.getElementById("previewTableBody");
    const saveToDbBtn = document.getElementById("saveToDbBtn");

    if (
        !fileInput ||
        !errorContainer ||
        !previewSection ||
        !previewTableBody ||
        !saveToDbBtn
    ) {
        console.error("Không tìm thấy các phần tử HTML cần thiết");
        return;
    }

    if (!file) {
        errorContainer.textContent = "Vui lòng chọn file";
        errorContainer.style.display = "block";
        previewSection.classList.add("hidden");
        saveToDbBtn.style.display = "none";
        return;
    }

    // Kiểm tra định dạng file
    if (!file.name.endsWith(".txt")) {
        errorContainer.textContent = "Vui lòng chọn file có định dạng .txt";
        errorContainer.style.display = "block";
        previewSection.classList.add("hidden");
        saveToDbBtn.style.display = "none";
        return;
    }

    try {
        showLoading();
        const text = await file.text();
        const lines = text.split("\n");

        // Kiểm tra định dạng dữ liệu
        if (lines.length === 0 || !lines[0].startsWith("EMP|")) {
            throw new Error(
                'File không đúng định dạng. Dòng đầu tiên phải bắt đầu bằng "EMP|"'
            );
        }

        let currentEmployee = null;
        const employees = [];
        const employeeCodes = new Set(); // Để kiểm tra trùng mã nhân viên

        for (let i = 0; i < lines.length; i++) {
            const line = lines[i].trim();
            if (!line) continue;

            const parts = line.split("|");

            // Kiểm tra định dạng dòng EMP
            if (parts[0] === "EMP") {
                if (parts.length < 14) {
                    throw new Error(`Dòng ${i + 1}: Thiếu thông tin. Dòng EMP phải có 14 trường`);
                }

                if (currentEmployee) {
                    employees.push(currentEmployee);
                }

                // Kiểm tra trùng mã nhân viên
                const employeeCode = parts[1]?.trim();
                if (employeeCodes.has(employeeCode)) {
                    throw new Error(`Dòng ${i + 1}: Mã nhân viên ${employeeCode} đã tồn tại`);
                }
                employeeCodes.add(employeeCode);

                // Xử lý đúng thứ tự các trường
                currentEmployee = {
                    employee_code: employeeCode,
                    name: parts[2]?.trim() || "",
                    full_name: parts[3]?.trim() || "",
                    email: parts[4]?.trim() || "",
                    phone: parts[5]?.trim() || "",
                    birthday: parts[6]?.trim() ? EmployeeService.formatDate(parts[6].trim()) : null,
                    address: parts[7]?.trim() || null,
                    department_name: parts[8]?.trim() || "",
                    position_name: parts[9]?.trim() || "",
                    contract_type: parts[10]?.trim() || "",
                    base_salary: parseFloat(parts[11]?.replace(/,/g, "").trim()) || 0,
                    contract_start_date: parts[12]?.trim() ? EmployeeService.formatDate(parts[12].trim()) : "",
                    contract_end_date: parts[13]?.trim() ? EmployeeService.formatDate(parts[13].trim()) : null,
                    family_members: [],
                };

                // Kiểm tra các trường bắt buộc
                const requiredFields = {
                    employee_code: "Mã nhân viên",
                    email: "Email",
                    name: "Tên",
                    full_name: "Họ và tên đầy đủ",
                    phone: "Số điện thoại",
                    department_name: "Phòng ban",
                    position_name: "Chức vụ",
                    contract_type: "Loại hợp đồng",
                    base_salary: "Lương",
                    contract_start_date: "Ngày bắt đầu hợp đồng",
                    contract_end_date: "Ngày kết thúc hợp đồng"
                };

                const missingFields = [];
                for (const [field, label] of Object.entries(requiredFields)) {
                    if (!currentEmployee[field]) {
                        missingFields.push(label);
                    }
                }

                if (missingFields.length > 0) {
                    throw new Error(`Dòng ${i + 1}: Thiếu các trường bắt buộc: ${missingFields.join(", ")}`);
                }

                // Kiểm tra định dạng email
                if (!isValidEmail(currentEmployee.email)) {
                    throw new Error(`Dòng ${i + 1}: Email không hợp lệ`);
                }

                // Kiểm tra định dạng số điện thoại
                if (!isValidPhone(currentEmployee.phone)) {
                    throw new Error(`Dòng ${i + 1}: Số điện thoại không hợp lệ (phải bắt đầu bằng 0 và có 10 chữ số)`);
                }

                // Kiểm tra phòng ban có tồn tại không
                if (!isValidDepartment(currentEmployee.department_name)) {
                    throw new Error(`Dòng ${i + 1}: Phòng ban "${currentEmployee.department_name}" không tồn tại`);
                }

                // Kiểm tra chức vụ có tồn tại trong phòng ban không
                const departmentId = getDepartmentIdByName(currentEmployee.department_name);
                const positionId = getPositionIdByName(currentEmployee.position_name, departmentId);
                if (!positionId) {
                    throw new Error(`Dòng ${i + 1}: Chức vụ "${currentEmployee.position_name}" không tồn tại trong phòng ban "${currentEmployee.department_name}"`);
                }

                // Kiểm tra ngày kết thúc hợp đồng phải sau ngày bắt đầu
                const startDate = new Date(currentEmployee.contract_start_date);
                const endDate = new Date(currentEmployee.contract_end_date);
                if (endDate <= startDate) {
                    throw new Error(`Dòng ${i + 1}: Ngày kết thúc hợp đồng phải sau ngày bắt đầu`);
                }
            }
            // Kiểm tra định dạng dòng FAM
            else if (parts[0] === "FAM") {
                if (parts.length < 6) {
                    throw new Error(`Dòng ${i + 1}: Thiếu thông tin. Dòng FAM phải có ít nhất 6 trường`);
                }

                if (!currentEmployee) {
                    throw new Error(`Dòng ${i + 1}: Dòng FAM phải đi sau dòng EMP`);
                }

                currentEmployee.family_members.push({
                    name: parts[1]?.trim() || "",
                    relationship: parts[2]?.trim() || "",
                    birthday: parts[3]?.trim() ? EmployeeService.formatDate(parts[3].trim()) : null,
                    occupation: parts[4]?.trim() || "",
                    is_dependent: parts[5]?.trim() === "1",
                });
            } else {
                throw new Error(`Dòng ${i + 1}: Không đúng định dạng. Dòng phải bắt đầu bằng "EMP|" hoặc "FAM|"`);
            }
        }

        if (currentEmployee) {
            employees.push(currentEmployee);
        }

        // Hiển thị thông báo số lượng nhân viên
        const employeeCount = employees.length;
        const notificationMessage = employeeCount === 1
            ? "Đã tìm thấy 1 nhân viên trong file"
            : `Đã tìm thấy ${employeeCount} nhân viên trong file`;

        showNotification(notificationMessage, "success");

        // Hiển thị dữ liệu đã đọc đúng thứ tự
        previewTableBody.innerHTML = employees
            .map(emp => `
                <tr data-employee='${JSON.stringify(emp)}'>
                    <td>${emp.employee_code}</td>
                    <td>${emp.name}</td>
                    <td>${emp.full_name}</td>
                    <td>${emp.email}</td>
                    <td>${emp.phone}</td>
                    <td>${emp.birthday || "-"}</td>
                    <td>${emp.address || "-"}</td>
                    <td>${emp.department_name}</td>
                    <td>${emp.position_name}</td>
                    <td>${emp.contract_type}</td>
                    <td>${emp.base_salary.toLocaleString()}</td>
                    <td>${emp.contract_start_date}</td>
                    <td>${emp.contract_end_date || "-"}</td>
                    <td>
                        ${emp.family_members.map(member => `
                            <div class="mb-1">
                                <strong>${member.name}</strong> (${member.relationship})<br>
                                ${member.birthday ? `Ngày sinh: ${member.birthday}<br>` : ""}
                                ${member.occupation ? `Nghề nghiệp: ${member.occupation}<br>` : ""}
                                ${member.is_dependent ? "Người phụ thuộc" : ""}
                            </div>
                        `).join("")}
                    </td>
                </tr>
            `).join("");

        previewSection.classList.remove("hidden");
        saveToDbBtn.style.display = "inline-block";
        errorContainer.style.display = "none";
    } catch (error) {
        console.error("Error reading file:", error);
        if (errorContainer) {
            errorContainer.className = "alert alert-danger";
            errorContainer.innerHTML = `
                <h5 class="mb-2">Lỗi khi đọc file:</h5>
                <p class="mb-0">${error.message}</p>
                <small class="d-block mt-2">Vui lòng kiểm tra lại định dạng file và thử lại</small>
            `;
            errorContainer.style.display = "block";
        }
        if (previewSection) {
            previewSection.classList.add("hidden");
        }
        if (saveToDbBtn) {
            saveToDbBtn.style.display = "none";
        }
    } finally {
        hideLoading();
    }
}

// Hàm phụ trợ để lấy ID phòng ban từ tên
function getDepartmentIdByName(name) {
    const department = departmentsData.find((dept) => dept.name === name);
    return department ? department.id : null;
}

// Hàm phụ trợ để phân tích thông tin gia đình
function parseFamilyMembers(cell) {
    const members = [];
    const memberTexts = cell.textContent
        .split("\n")
        .filter((text) => text.trim());

    let currentMember = null;

    memberTexts.forEach((text) => {
        text = text.trim();

        // Nếu dòng bắt đầu bằng tên và có mối quan hệ trong ngoặc
        if (text.includes("(") && text.includes(")")) {
            if (currentMember) {
                members.push(currentMember);
            }

            const nameMatch = text.match(/^([^(]+)\s*\(([^)]+)\)/);
            if (nameMatch) {
                currentMember = {
                    name: nameMatch[1].trim(),
                    relationship: nameMatch[2].trim(),
                    birthday: null,
                    occupation: null,
                    is_dependent: false,
                };
            }
        }
        // Nếu là dòng ngày sinh
        else if (text.startsWith("Ngày sinh:")) {
            if (currentMember) {
                currentMember.birthday = text.replace("Ngày sinh:", "").trim();
            }
        }
        // Nếu là dòng nghề nghiệp
        else if (text.startsWith("Nghề nghiệp:")) {
            if (currentMember) {
                currentMember.occupation = text
                    .replace("Nghề nghiệp:", "")
                    .trim();
            }
        }
        // Nếu là dòng người phụ thuộc
        else if (text === "Người phụ thuộc") {
            if (currentMember) {
                currentMember.is_dependent = true;
            }
        }
    });

    // Thêm thành viên cuối cùng
    if (currentMember) {
        members.push(currentMember);
    }

    return members;
}

// Hàm chuyển đổi định dạng ngày tháng
function formatDate(dateString) {
    if (!dateString) return null;

    // Kiểm tra định dạng DD/MM/YYYY
    if (dateString.includes("/")) {
        const [day, month, year] = dateString.split("/");
        return `${year}-${month.padStart(2, "0")}-${day.padStart(2, "0")}`;
    }

    return dateString;
}

// Thêm hàm làm mới form upload file
function refreshFileUpload() {
    // Reset file input
    const fileInput = document.getElementById("employeeFile");
    fileInput.value = "";

    // Ẩn preview section
    const previewSection = document.getElementById("previewSection");
    previewSection.classList.add("hidden");

    // Ẩn nút lưu
    const saveToDbBtn = document.getElementById("saveToDbBtn");
    saveToDbBtn.style.display = "none";

    // Xóa thông báo lỗi
    const errorContainer = document.getElementById("uploadError");
    errorContainer.style.display = "none";
    errorContainer.innerHTML = "";

    // Xóa nội dung bảng preview
    const previewTableBody = document.getElementById("previewTableBody");
    previewTableBody.innerHTML = "";

    // Hiển thị thông báo thành công
    showNotification("Đã làm mới form thêm nhân viên bằng file", "success");
}

// Hàm hiển thị thông báo
function showNotification(message, type = "info") {
    const container = document.getElementById("notificationContainer");
    if (!container) return;

    const notification = document.createElement("div");
    notification.className = `notification ${type}`;
    notification.innerHTML = `
                <i class="fas ${
                    type === "success"
                        ? "fa-check-circle"
                        : type === "error"
                        ? "fa-exclamation-circle"
                        : "fa-info-circle"
                }"></i>
                ${message}
            `;

    container.appendChild(notification);

    // Tự động xóa thông báo sau 3 giây
    setTimeout(() => {
        notification.style.opacity = "0";
        setTimeout(() => {
            container.removeChild(notification);
        }, 300);
    }, 3000);
}

// Hàm xóa nhân viên
async function deleteEmployee(employeeId) {
    if (!employeeId) {
        showNotification("Không tìm thấy ID nhân viên", "error");
        return;
    }

    // Hiển thị hộp thoại xác nhận
    if (
        !confirm(
            "Bạn có chắc chắn muốn xóa nhân viên này? Hành động này sẽ xóa tất cả thông tin liên quan đến nhân viên."
        )
    ) {
        return;
    }

    try {
        showLoading();

        // 1. Kiểm tra trạng thái nhân viên
        const statusCheck = await fetch(
            `/qlnhansu_V2/backend/src/api/employees.php?id=${employeeId}/status`,
            {
                method: "GET",
                headers: {
                    Accept: "application/json",
                },
            }
        );

        if (!statusCheck.ok) {
            throw new Error("Không thể kiểm tra trạng thái nhân viên");
        }

        const statusData = await statusCheck.json();

        if (statusData.isProcessing) {
            throw new Error(
                "Nhân viên đang trong quá trình xử lý khác. Vui lòng thử lại sau."
            );
        }

        if (statusData.hasActiveContract) {
            throw new Error(
                "Nhân viên đang có hợp đồng còn hiệu lực. Vui lòng kết thúc hợp đồng trước khi xóa."
            );
        }

        // 2. Khóa tài nguyên
        const lockResponse = await fetch(
            `/qlnhansu_V2/backend/src/api/locks.php`,
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    resource_type: "employee",
                    resource_id: employeeId,
                    action: "delete",
                }),
            }
        );

        if (!lockResponse.ok) {
            throw new Error(
                "Không thể khóa tài nguyên. Có thể nhân viên đang được xử lý bởi người khác."
            );
        }

        // 3. Lưu trữ dữ liệu gốc để rollback nếu cần
        const backupResponse = await fetch(
            `/qlnhansu_V2/backend/src/api/employees.php?id=${employeeId}/backup`,
            {
                method: "GET",
                headers: {
                    Accept: "application/json",
                },
            }
        );

        if (!backupResponse.ok) {
            throw new Error("Không thể sao lưu dữ liệu nhân viên");
        }

        const backupData = await backupResponse.json();

        // 4. Thực hiện xóa
        const response = await fetch(
            `/qlnhansu_V2/backend/src/api/employees.php?id=${employeeId}`,
            {
                method: "DELETE",
                headers: {
                    Accept: "application/json",
                },
            }
        );

        const result = await response.json();

        if (!response.ok) {
            // Rollback nếu xóa thất bại
            await fetch(`/qlnhansu_V2/backend/src/api/employees.php/rollback`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(backupData),
            });
            throw new Error(
                result.message || "Xóa thất bại. Đã khôi phục dữ liệu."
            );
        }

        if (result.success) {
            // 5. Mở khóa tài nguyên
            await fetch(`/qlnhansu_V2/backend/src/api/locks.php`, {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    resource_type: "employee",
                    resource_id: employeeId,
                }),
            });

            showNotification("Đã xóa nhân viên thành công", "success");
            // Load lại danh sách nhân viên
            loadEmployees(1);
        } else {
            throw new Error(result.message || "Không thể xóa nhân viên");
        }
    } catch (error) {
        console.error("Error deleting employee:", error);

        // Xử lý lỗi ràng buộc khóa ngoại
        if (error.message.includes("Integrity constraint violation")) {
            showNotification(
                "Không thể xóa nhân viên. Vui lòng tịch thu tất cả tài sản đang được gán cho nhân viên này trước khi xóa.",
                "error"
            );
        } else {
            showNotification(
                `Lỗi khi xóa nhân viên: ${error.message}`,
                "error"
            );
        }
    } finally {
        hideLoading();
    }
}

// Hàm cập nhật bảng nhân viên

// Hàm gắn listener cho các nút
function addTableButtonListeners() {
    console.log("=== Gắn sự kiện cho nút Xóa ===");

    // Gắn sự kiện cho nút Xóa
    const deleteButtons = document.querySelectorAll(".delete-btn");
    console.log("Số nút Xóa:", deleteButtons.length);

    deleteButtons.forEach((button) => {
        // Xóa các event listener cũ
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);

        // Gắn event listener mới
        newButton.addEventListener("click", function (e) {
            e.stopPropagation(); // Ngăn chặn sự kiện lan truyền
            console.log("Nhấn nút Xóa");
            const employeeId = this.dataset.id;
            console.log("ID nhân viên:", employeeId);
            deleteEmployee(employeeId);
        });
    });
}

// Hàm xử lý sự kiện click nút Sửa
function handleEditClick(event) {
    const employeeId = event.target.closest("button").dataset.id;
    startEditEmployee(employeeId);
}

// Hàm xử lý sự kiện click nút Xóa
function handleDeleteClick(event) {
    const employeeId = event.target.closest("button").dataset.id;
    deleteEmployee(employeeId);
}

// Hàm cập nhật trạng thái nhân viên
async function updateEmployeeStatus(employeeId, newStatus) {
    try {
        showLoading();
        const response = await fetch(
            `/qlnhansu_V2/backend/src/api/employees.php?id=${employeeId}`,
            {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
                body: JSON.stringify({ status: newStatus }),
            }
        );
        const result = await response.json();
        if (!response.ok)
            throw new Error(result.message || "Lỗi cập nhật trạng thái");
        if (result.success) {
            showNotification("Cập nhật trạng thái thành công", "success");
        } else {
            throw new Error(result.message || "Không thể cập nhật trạng thái");
        }
    } catch (error) {
        console.error("Error updating status:", error);
        showNotification(`Lỗi: ${error.message}`, "error");
        loadEmployees();
    } finally {
        hideLoading();
    }
}

// Cập nhật sự kiện DOMContentLoaded
document.addEventListener("DOMContentLoaded", async function () {
    showLoading();
    await loadDepartments();
    await loadAllPositions();
    await loadEmployees(1);
    hideLoading();

    // Gắn các event listener cho filter và search
    document
        .getElementById("searchInput")
        .addEventListener("input", () => loadEmployees(1));
    document
        .getElementById("departmentFilter")
        .addEventListener("change", () => loadEmployees(1));
    document
        .getElementById("positionFilter")
        .addEventListener("change", () => loadEmployees(1));
    document
        .getElementById("statusFilter")
        .addEventListener("change", () => loadEmployees(1));

    // Thêm listener cho nút Lưu trong modal Add Employee
    const saveButton = document.getElementById("saveEmployeeBtn");
    if (saveButton && !saveButton.hasAttribute("data-listener-added")) {
        saveButton.addEventListener("click", saveEmployee);
        saveButton.setAttribute("data-listener-added", "true");
    }
});

// Hàm bắt đầu sửa nhân viên
function startEditEmployee(employeeId) {
    console.log("=== Bắt đầu quá trình sửa nhân viên ===");
    console.log("ID nhân viên:", employeeId);

    const row = document.querySelector(`tr[data-employee-id="${employeeId}"]`);
    if (!row) {
        console.error("Không tìm thấy dòng nhân viên:", employeeId);
        return;
    }
    console.log("Tìm thấy dòng:", row);

    // Lưu trạng thái ban đầu của dòng
    row.dataset.originalState = row.innerHTML;
    console.log("Đã lưu trạng thái ban đầu");

    // Lấy tất cả các ô có thể sửa
    const editableCells = row.querySelectorAll(".editable");
    console.log("Số ô có thể sửa:", editableCells.length);

    editableCells.forEach((cell) => {
        const field = cell.dataset.field;
        const currentValue = cell.textContent.trim();
        console.log(`Ô ${field}:`, currentValue);

        // Tạo input tương ứng với từng trường
        let input;
        if (field === "birthday") {
            input = document.createElement("input");
            input.type = "date";
            input.value = currentValue;
            console.log("Tạo input ngày sinh:", input.value);
        } else if (field === "email") {
            input = document.createElement("input");
            input.type = "email";
            input.value = currentValue;
            console.log("Tạo input email:", input.value);
        } else if (field === "phone") {
            input = document.createElement("input");
            input.type = "tel";
            input.value = currentValue;
            input.maxLength = 10;
            input.pattern = "[0-9]{10}";
            input.title = "Số điện thoại phải có 10 chữ số";
            console.log("Tạo input số điện thoại:", input.value);
        } else if (field === "department_name" || field === "position_name") {
            input = document.createElement("select");
            input.className = "form-select form-select-sm";

            if (field === "department_name") {
                console.log("Tạo select phòng ban");
                departmentsData.forEach((dept) => {
                    const option = document.createElement("option");
                    option.value = dept.name;
                    option.textContent = dept.name;
                    if (dept.name === currentValue) option.selected = true;
                    input.appendChild(option);
                });
                console.log("Danh sách phòng ban:", departmentsData);

                input.addEventListener("change", function () {
                    console.log("Phòng ban thay đổi:", this.value);
                    const positionSelect = row.querySelector(
                        '[data-field="position_name"] select'
                    );
                    if (positionSelect) {
                        const positions = getPositionsByDepartment(this.value);
                        console.log("Danh sách chức vụ mới:", positions);
                        positionSelect.innerHTML = "";
                        positions.forEach((pos) => {
                            const option = document.createElement("option");
                            option.value = pos.name;
                            option.textContent = pos.name;
                            positionSelect.appendChild(option);
                        });
                    }
                });
            } else {
                console.log("Tạo select chức vụ");
                const positions = getPositionsByDepartment(
                    row
                        .querySelector('[data-field="department_name"]')
                        .textContent.trim()
                );
                console.log("Danh sách chức vụ:", positions);
                positions.forEach((pos) => {
                    const option = document.createElement("option");
                    option.value = pos.name;
                    option.textContent = pos.name;
                    if (pos.name === currentValue) option.selected = true;
                    input.appendChild(option);
                });
            }
        } else {
            input = document.createElement("input");
            input.type = "text";
            input.value = currentValue;
            console.log(`Tạo input ${field}:`, input.value);
        }

        input.className = "form-control form-control-sm";
        cell.innerHTML = "";
        cell.appendChild(input);

        input.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
                console.log("Nhấn Enter để lưu");
                saveEditEmployee(employeeId);
            } else if (e.key === "Escape") {
                console.log("Nhấn Escape để hủy");
                cancelEditEmployee(employeeId);
            }
        });
    });

    // Thay đổi nút Sửa thành nút Lưu và Hủy
    const actionButtons = row.querySelector(".action-buttons");
    if (actionButtons) {
        console.log("Thay đổi nút Sửa thành Lưu và Hủy");
        actionButtons.innerHTML = `
                    <button style="font-size: 13px;" class="btn-action btn-success save-btn" data-id="${employeeId}">Lưu</button>
                    <button style="font-size: 13px;" class="btn-action btn-secondary cancel-btn" data-id="${employeeId}">Hủy</button>
                `;

        const saveBtn = actionButtons.querySelector(".save-btn");
        const cancelBtn = actionButtons.querySelector(".cancel-btn");

        if (saveBtn) {
            saveBtn.addEventListener("click", async (e) => {
                e.preventDefault();
                console.log("Nhấn nút Lưu");
                try {
                    // Lấy dữ liệu từ form
                    const formData = new FormData();
                    const inputs = row.querySelectorAll("input, select");
                    inputs.forEach((input) => {
                        formData.append(input.name, input.value);
                    });

                    // Gửi request cập nhật
                    console.log("Gửi request cập nhật với dữ liệu:", formData);
                    const response = await fetch(
                        `/qlnhansu_V2/backend/src/api/employees.php?id=${employeeId}`,
                        {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                Accept: "application/json",
                            },
                            body: JSON.stringify(formData),
                        }
                    );

                    console.log("Response status:", response.status);
                    console.log("Response headers:", response.headers);

                    if (!response.ok) {
                        const errorText = await response.text();
                        console.error("Error response:", errorText);
                        throw new Error(
                            `HTTP error! status: ${response.status}, message: ${errorText}`
                        );
                    }

                    const result = await response.json();
                    console.log("Response data:", result);

                    if (result.success) {
                        showNotification(
                            "Cập nhật thông tin thành công",
                            "success"
                        );
                        // Reset lại trạng thái của row
                        row.classList.remove("editing");
                        row.querySelectorAll("input, select").forEach(
                            (input) => {
                                input.disabled = true;
                            }
                        );
                        // Reset lại nút
                        actionButtons.innerHTML = `
                                    <button style="font-size: 13px;" class="btn-action btn-primary edit-btn" data-id="${employeeId}">Sửa</button>
                                `;
                        // Thêm lại sự kiện cho nút Sửa
                        const editBtn =
                            actionButtons.querySelector(".edit-btn");
                        if (editBtn) {
                            editBtn.addEventListener("click", handleEditClick);
                        }
                    } else {
                        throw new Error(
                            result.message ||
                                "Có lỗi xảy ra khi cập nhật thông tin"
                        );
                    }
                } catch (error) {
                    console.error("Error saving employee:", error);
                    showNotification(error.message, "error");
                }
            });
        }
        if (cancelBtn) {
            cancelBtn.addEventListener("click", () => {
                console.log("Nhấn nút Hủy");
                // Reset lại trạng thái của row
                row.classList.remove("editing");
                row.querySelectorAll("input, select").forEach((input) => {
                    input.disabled = true;
                });
                // Reset lại nút
                actionButtons.innerHTML = `
                            <button style="font-size: 13px;" class="btn-action btn-primary edit-btn" data-id="${employeeId}">Sửa</button>
                        `;
                // Thêm lại sự kiện cho nút Sửa
                const editBtn = actionButtons.querySelector(".edit-btn");
                if (editBtn) {
                    editBtn.addEventListener("click", handleEditClick);
                }
            });
        }
    }

    // Tự động focus vào trường đầu tiên
    const firstInput = row.querySelector("input, select");
    if (firstInput) {
        firstInput.focus();
        console.log("Focus vào trường đầu tiên:", firstInput);
    }
}

// Hàm lưu thông tin sửa
async function saveEditEmployee(employeeId) {
    console.log("=== Bắt đầu lưu thông tin sửa ===");
    console.log("ID nhân viên:", employeeId);

    try {
        const row = document.querySelector(
            `tr[data-employee-id="${employeeId}"]`
        );
        if (!row) {
            console.error("Không tìm thấy dòng nhân viên");
            return;
        }

        // Lấy dữ liệu từ các input
        const fullNameInput = row.querySelector(
            '[data-field="full_name"] input'
        );
        const birthdayInput = row.querySelector(
            '[data-field="birthday"] input'
        );
        const phoneInput = row.querySelector('[data-field="phone"] input');
        const emailInput = row.querySelector('[data-field="email"] input');
        const departmentSelect = row.querySelector(
            '[data-field="department_name"] select'
        );
        const positionSelect = row.querySelector(
            '[data-field="position_name"] select'
        );
        const statusSelect = row.querySelector('[data-field="status"] select');

        // Validate dữ liệu
        if (!fullNameInput?.value?.trim()) {
            throw new Error("Họ và tên không được để trống");
        }
        if (!birthdayInput?.value) {
            throw new Error("Ngày sinh không được để trống");
        }
        if (!phoneInput?.value?.trim()) {
            throw new Error("Số điện thoại không được để trống");
        }
        if (!emailInput?.value?.trim()) {
            throw new Error("Email không được để trống");
        }
        if (!departmentSelect?.value) {
            throw new Error("Phòng ban không được để trống");
        }
        if (!positionSelect?.value) {
            throw new Error("Chức vụ không được để trống");
        }

        // Kiểm tra định dạng email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailInput.value.trim())) {
            throw new Error("Email không hợp lệ");
        }

        // Kiểm tra định dạng số điện thoại
        const phoneRegex = /^0[0-9]{9}$/;
        if (!phoneRegex.test(phoneInput.value.trim())) {
            throw new Error(
                "Số điện thoại phải bắt đầu bằng 0 và có 10 chữ số"
            );
        }

        // Lấy department_id từ tên phòng ban
        const departmentId = getDepartmentIdByName(departmentSelect.value);
        if (!departmentId) {
            throw new Error("Không tìm thấy ID phòng ban");
        }

        // Lấy position_id từ tên chức vụ
        const positionId = getPositionIdByName(
            positionSelect.value,
            departmentId
        );
        if (!positionId) {
            throw new Error("Không tìm thấy ID chức vụ");
        }

        // Chuẩn bị dữ liệu gửi đi
        const data = {
            id: parseInt(employeeId),
            full_name: fullNameInput.value.trim(),
            date_of_birth: birthdayInput.value,
            phone_number: phoneInput.value.trim(),
            email: emailInput.value.trim(),
            department_id: parseInt(departmentId),
            position_id: parseInt(positionId),
            status: statusSelect?.value || "active",
        };

        // Log chi tiết dữ liệu
        console.log("=== Dữ liệu chuẩn bị gửi ===");
        console.log("ID nhân viên:", data.id);
        console.log("Họ tên:", data.full_name);
        console.log("Ngày sinh:", data.date_of_birth);
        console.log("Số điện thoại:", data.phone_number);
        console.log("Email:", data.email);
        console.log("ID phòng ban:", data.department_id);
        console.log("ID chức vụ:", data.position_id);
        console.log("Trạng thái:", data.status);
        console.log("Toàn bộ dữ liệu:", JSON.stringify(data, null, 2));

        // Gửi request cập nhật
        const response = await fetch(
            `/qlnhansu_V2/backend/src/api/employees.php?id=${employeeId}`,
            {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
                body: JSON.stringify(data),
            }
        );

        // Log response để debug
        console.log("=== Thông tin response ===");
        console.log("Status:", response.status);
        console.log("Status text:", response.statusText);
        console.log("Headers:", Object.fromEntries(response.headers.entries()));

        const responseText = await response.text();
        console.log("Response text:", responseText);

        if (!response.ok) {
            try {
                const errorJson = JSON.parse(responseText);
                throw new Error(
                    errorJson.message || "Có lỗi xảy ra khi cập nhật thông tin"
                );
            } catch (e) {
                throw new Error(
                    `HTTP error! status: ${response.status}, message: ${responseText}`
                );
            }
        }

        const result = JSON.parse(responseText);
        if (result.success) {
            showNotification(
                "Đã cập nhật thông tin nhân viên thành công",
                "success"
            );
            loadEmployees();
        } else {
            throw new Error(result.message || "Không thể cập nhật nhân viên");
        }
    } catch (error) {
        console.error("Lỗi khi lưu:", error);
        showNotification(
            `Lỗi khi cập nhật nhân viên: ${error.message}`,
            "error"
        );
    }
}

// Hàm hủy sửa
function cancelEditEmployee(employeeId) {
    console.log("=== Hủy sửa nhân viên ===");
    console.log("ID nhân viên:", employeeId);

    const row = document.querySelector(`tr[data-employee-id="${employeeId}"]`);
    if (row && row.dataset.originalState) {
        console.log("Khôi phục trạng thái ban đầu");
        row.innerHTML = row.dataset.originalState;
        delete row.dataset.originalState;

        const actionButtons = row.querySelector(".action-buttons");
        if (actionButtons) {
            console.log("Tạo lại nút Xóa");
            actionButtons.innerHTML = `
                        <button class="btn-action btn-delete delete-btn" data-id="${employeeId}">Xóa</button>
                    `;

            const deleteBtn = actionButtons.querySelector(".delete-btn");

            if (deleteBtn) {
                deleteBtn.addEventListener("click", function (e) {
                    e.stopPropagation();
                    console.log("Nhấn nút Xóa");
                    deleteEmployee(employeeId);
                });
            }
        }
    } else {
        console.log("Không có trạng thái ban đầu, tải lại danh sách");
        loadEmployees(1);
    }
}

// Thêm hàm formatDateDisplay
function formatDateDisplay(dateString) {
    if (!dateString) return "-";
    try {
        const date = new Date(dateString);
        // Kiểm tra xem ngày có hợp lệ không
        if (isNaN(date.getTime())) {
            // Nếu không hợp lệ, thử phân tích định dạng dd/mm/yyyy
            if (dateString.includes("/")) {
                const parts = dateString.split("/");
                if (parts.length === 3) {
                    // Lưu ý: Tháng trong new Date() là 0-based
                    const isoDate = `${parts[2]}-${parts[1].padStart(
                        2,
                        "0"
                    )}-${parts[0].padStart(2, "0")}`;
                    const parsedDate = new Date(isoDate);
                    if (!isNaN(parsedDate.getTime())) {
                        return `${parts[0].padStart(
                            2,
                            "0"
                        )}/${parts[1].padStart(2, "0")}/${parts[2]}`;
                    }
                }
            }
            return dateString; // Trả về chuỗi gốc nếu không parse được
        }
        const day = String(date.getDate()).padStart(2, "0");
        const month = String(date.getMonth() + 1).padStart(2, "0"); // Tháng là 0-based
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    } catch (e) {
        console.error("Error formatting date:", dateString, e);
        return dateString; // Trả về chuỗi gốc nếu có lỗi
    }
}

// Biến tạm lưu trữ chức vụ
let allPositions = [];

// Hàm load tất cả chức vụ
async function loadAllPositions() {
    try {
        const response = await fetch(
            "/qlnhansu_V2/backend/src/api/positions.php"
        );
        if (!response.ok) throw new Error("Failed to load positions");
        const data = await response.json();
        if (Array.isArray(data)) {
            allPositions = data;
        } else if (data.data && Array.isArray(data.data)) {
            allPositions = data.data;
        }
        console.log("All positions loaded:", allPositions);
    } catch (error) {
        console.error("Error loading all positions:", error);
    }
}

// Hàm lấy chức vụ theo phòng ban
function getPositionsByDepartment(departmentName) {
    const departmentId = getDepartmentIdByName(departmentName);
    if (!departmentId) return [];
    return allPositions.filter((pos) => pos.department_id == departmentId);
}

// Thêm hàm phụ trợ để lấy position_id
function getPositionIdByName(positionName, departmentId) {
    const positions = window.positions || [];
    const position = positions.find(p => 
        p.name.toLowerCase() === positionName.toLowerCase() && 
        p.department_id === departmentId
    );
    return position ? position.id : null;
}

// ...existing code...

function validateEmployeeData(employeeData) {
    // Kiểm tra nếu employeeData là null hoặc undefined
    if (!employeeData) {
        return ["Dữ liệu nhân viên không hợp lệ"];
    }

    const errors = [];
    
    if (!employeeData.name || employeeData.name.trim() === "") {
        errors.push("Tên nhân viên không được để trống");
    }
    if (!employeeData.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(employeeData.email)) {
        errors.push("Email không hợp lệ");
    }
    if (!employeeData.phone || !/^\d{10,15}$/.test(employeeData.phone)) {
        errors.push("Số điện thoại không hợp lệ");
    }
    if (!employeeData.department) {
        errors.push("Phòng ban không được để trống");
    }
    if (!employeeData.position) {
        errors.push("Chức vụ không được để trống");
    }
    if (!employeeData.startDate) {
        errors.push("Ngày bắt đầu không được để trống");
    }
    
    return errors;
}

// Đổi tên hàm saveEmployee có tham số thành saveEmployeeData
async function saveEmployeeData(employeeData) {
    try {
        // Kiểm tra nếu employeeData là null hoặc undefined
        if (!employeeData) {
            showError("Dữ liệu nhân viên không hợp lệ");
            return;
        }

        // Validate dữ liệu trước khi gửi
        const validationErrors = validateEmployeeData(employeeData);
        if (validationErrors && validationErrors.length > 0) {
            showError(validationErrors.join('\n'));
            return;
        }

        // Format dữ liệu theo yêu cầu của API
        const formattedData = {
            fullName: employeeData.name,
            email: employeeData.email,
            phone: employeeData.phone,
            department: employeeData.department,
            position: employeeData.position,
            startDate: formatDate(employeeData.startDate),
            contract_type: employeeData.contractType || 'Permanent',
            base_salary: parseFloat(employeeData.baseSalary) || 0,
            contract_start_date: formatDate(employeeData.contractStartDate),
            contract_end_date: employeeData.contractEndDate ? formatDate(employeeData.contractEndDate) : null,
            address: employeeData.address || '',
            employeeCode: employeeData.employeeCode || generateEmployeeCode()
        };

        // Gửi request đến API
        const response = await fetch('/qlnhansu_V2/backend/src/api/employees.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formattedData)
        });

        // Kiểm tra response
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Lỗi khi lưu nhân viên');
        }

        const result = await response.json();
        if (result.success) {
            showSuccess('Thêm nhân viên thành công');
            // Reload danh sách nhân viên
            await loadEmployees();
            // Đóng modal
            closeAddEmployeeModal();
        } else {
            throw new Error(result.message || 'Lỗi khi lưu nhân viên');
        }
    } catch (error) {
        console.error('Error saving employee:', error);
        showError(error.message || 'Đã xảy ra lỗi khi kết nối đến máy chủ');
    }
}

