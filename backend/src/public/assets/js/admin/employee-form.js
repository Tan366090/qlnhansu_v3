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

    // Khởi tạo form
    initializeForm(employeeId);

    // Xử lý sự kiện nút đăng xuất
    setupLogoutButton();

    // Xử lý khi thay đổi phòng ban
    const departmentSelect = document.getElementById('departmentId');
    if (departmentSelect) {
        departmentSelect.addEventListener('change', function() {
            const departmentId = this.value;
            if (departmentId) {
                loadPositionsByDepartment(departmentId);
            } else {
                // Reset chức vụ nếu không chọn phòng ban
                const positionSelect = document.getElementById('positionId');
                positionSelect.innerHTML = '<option value="">Chọn chức vụ</option>';
            }
        });
    }
    
    // Xử lý khi thay đổi chức vụ
    const positionSelect = document.getElementById('positionId');
    if (positionSelect) {
        positionSelect.addEventListener('change', function() {
            const positionId = this.value;
            if (positionId) {
                loadContractInfo(positionId);
            }
        });
    }
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

// Hàm khởi tạo form
async function initializeForm(employeeId) {
    try {
        showLoading();
        
        // Load dữ liệu phòng ban và chức vụ
        await Promise.all([
            loadDepartments(),
            loadPositions()
        ]);

        // Load dữ liệu nhân viên
        await loadEmployeeData(employeeId);

        // Thiết lập validation
        setupValidation();

        // Xử lý submit form
        setupFormSubmit(employeeId);

        hideLoading();
    } catch (error) {
        hideLoading();
        showError(error.message);
    }
}

// Hàm load dữ liệu phòng ban
async function loadDepartments() {
    try {
        const response = await fetch("/QLNhanSu_version1/api/departments.php?action=getAll", {
            headers: {
                "Authorization": `Bearer ${localStorage.getItem("token")}`
            }
        });

        if (!response.ok) {
            throw new Error("Không thể tải danh sách phòng ban");
        }

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message);
        }

        const departmentSelect = document.getElementById("department");
        data.data.forEach(department => {
            const option = document.createElement("option");
            option.value = department.id;
            option.textContent = department.name;
            departmentSelect.appendChild(option);
        });
    } catch (error) {
        throw new Error("Lỗi khi tải danh sách phòng ban: " + error.message);
    }
}

// Hàm load dữ liệu chức vụ
async function loadPositions() {
    try {
        const response = await fetch("/QLNhanSu_version1/api/positions.php?action=getAll", {
            headers: {
                "Authorization": `Bearer ${localStorage.getItem("token")}`
            }
        });

        if (!response.ok) {
            throw new Error("Không thể tải danh sách chức vụ");
        }

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message);
        }

        const positionSelect = document.getElementById("position");
        data.data.forEach(position => {
            const option = document.createElement("option");
            option.value = position.id;
            option.textContent = position.name;
            positionSelect.appendChild(option);
        });
    } catch (error) {
        throw new Error("Lỗi khi tải danh sách chức vụ: " + error.message);
    }
}

// Hàm load dữ liệu nhân viên
async function loadEmployeeData(employeeId) {
    try {
        const response = await fetch(`/QLNhanSu_version1/api/employees.php?action=getById&id=${employeeId}`, {
            headers: {
                "Authorization": `Bearer ${localStorage.getItem("token")}`
            }
        });

        if (!response.ok) {
            throw new Error("Không thể tải thông tin nhân viên");
        }

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message);
        }

        // Điền dữ liệu vào form
        const employee = data.data;
        document.getElementById("username").value = employee.username;
        document.getElementById("email").value = employee.email;
        document.getElementById("fullName").value = employee.full_name;
        document.getElementById("phoneNumber").value = employee.phone_number;
        document.getElementById("gender").value = employee.gender;
        document.getElementById("birthDate").value = formatDateForInput(employee.birth_date);
        document.getElementById("identityCard").value = employee.identity_card;
        document.getElementById("address").value = employee.address;
        document.getElementById("department").value = employee.department_id;
        document.getElementById("position").value = employee.position_id;
        document.getElementById("hireDate").value = formatDateForInput(employee.hire_date);
        document.getElementById("status").value = employee.status;
    } catch (error) {
        throw new Error("Lỗi khi tải thông tin nhân viên: " + error.message);
    }
}

// Hàm thiết lập validation
function setupValidation() {
    const form = document.getElementById("employeeForm");
    const inputs = form.querySelectorAll("input, select");

    inputs.forEach(input => {
        input.addEventListener("input", function() {
            if (this.checkValidity()) {
                this.classList.remove("invalid");
            } else {
                this.classList.add("invalid");
            }
        });
    });
}

// Hàm xử lý submit form
function setupFormSubmit(employeeId) {
    const form = document.getElementById("employeeForm");
    
    form.addEventListener("submit", async function(e) {
        e.preventDefault();

        if (!form.checkValidity()) {
            showError("Vui lòng điền đầy đủ thông tin bắt buộc");
            return;
        }

        try {
            showLoading();

            // Chuẩn bị dữ liệu
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => {
                if (value) {
                    data[key] = value;
                }
            });

            // Gửi request cập nhật
            const response = await fetch(`/QLNhanSu_version1/api/employees.php?action=update&id=${employeeId}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${localStorage.getItem("token")}`
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                throw new Error("Không thể cập nhật thông tin nhân viên");
            }

            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message);
            }

            showSuccess("Cập nhật thông tin nhân viên thành công");
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
function formatDateForInput(dateString) {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toISOString().split("T")[0];
}

function showLoading() {
    const loading = document.createElement("div");
    loading.className = "loading";
    loading.innerHTML = "<div class=\"spinner\"></div>";
    document.body.appendChild(loading);
}

function hideLoading() {
    const loading = document.querySelector(".loading");
    if (loading) {
        loading.remove();
    }
}

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

// Hàm load chức vụ dựa vào phòng ban
async function loadPositionsByDepartment(departmentId) {
    try {
        const response = await fetch(`/api/positions?department_id=${departmentId}`);
        const data = await response.json();
        
        if (data.success) {
            const positionSelect = document.getElementById('positionId');
            positionSelect.innerHTML = '<option value="">Chọn chức vụ</option>';
            
            data.data.forEach(position => {
                const option = document.createElement('option');
                option.value = position.id;
                option.textContent = position.name;
                positionSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Lỗi khi tải chức vụ:', error);
    }
}

// Hàm load thông tin hợp đồng dựa vào chức vụ
async function loadContractInfo(positionId) {
    try {
        const response = await fetch(`/api/positions/${positionId}/contract-info`);
        const data = await response.json();
        
        if (data.success) {
            // Cập nhật thông tin hợp đồng
            const contractTypeSelect = document.getElementById('contractType');
            const salaryInput = document.getElementById('salary');
            const startDateInput = document.getElementById('startDate');
            
            // Cập nhật loại hợp đồng
            contractTypeSelect.value = data.contract_type || '';
            
            // Cập nhật lương
            if (data.salary) {
                salaryInput.value = data.salary;
            }
            
            // Cập nhật ngày bắt đầu
            if (data.start_date) {
                startDateInput.value = data.start_date;
            }
        }
    } catch (error) {
        console.error('Lỗi khi tải thông tin hợp đồng:', error);
    }
} 