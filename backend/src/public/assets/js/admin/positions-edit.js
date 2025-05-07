// DOM Elements
const editPositionForm = document.getElementById("editPositionForm");
const positionId = document.getElementById("positionId");
const nameInput = document.getElementById("name");
const departmentSelect = document.getElementById("department");
const salaryInput = document.getElementById("salary");
const requirementsInput = document.getElementById("requirements");
const benefitsInput = document.getElementById("benefits");
const loadingSpinner = document.getElementById("loadingSpinner");
const errorMessage = document.getElementById("errorMessage");
const errorText = document.getElementById("errorText");
const userName = document.getElementById("userName");
const logoutBtn = document.getElementById("logoutBtn");

// Get position ID from URL
const urlParams = new URLSearchParams(window.location.search);
const id = urlParams.get("id");

// Initialize page
document.addEventListener("DOMContentLoaded", () => {
    if (!id) {
        showError("Không tìm thấy vị trí");
        setTimeout(() => {
            window.location.href = "/admin/positions/list.html";
        }, 2000);
        return;
    }

    checkAuth();
    loadDepartments();
    loadPosition();
    setupEventListeners();
});

// Check authentication
function checkAuth() {
    const token = localStorage.getItem("token");
    if (!token) {
        window.location.href = "/login.html";
        return;
    }

    // Get user info from token
    try {
        const payload = JSON.parse(atob(token.split(".")[1]));
        userName.textContent = payload.name;
    } catch (error) {
        showError("Lỗi xác thực");
        setTimeout(() => {
            window.location.href = "/login.html";
        }, 2000);
    }
}

// Load departments for select
async function loadDepartments() {
    showLoading();
    try {
        const response = await fetch("/api/departments.php?action=getAll");
        const data = await response.json();

        if (data.success) {
            departmentSelect.innerHTML = "<option value=\"\">Chọn phòng ban</option>";
            data.departments.forEach(dept => {
                const option = document.createElement("option");
                option.value = dept.id;
                option.textContent = dept.name;
                departmentSelect.appendChild(option);
            });
        } else {
            showError(data.message || "Lỗi tải danh sách phòng ban");
        }
    } catch (error) {
        showError("Lỗi kết nối");
    } finally {
        hideLoading();
    }
}

// Load position data
async function loadPosition() {
    showLoading();
    try {
        const response = await fetch(`/api/positions.php?action=getById&id=${id}`);
        const data = await response.json();

        if (data.success) {
            positionId.value = data.position.id;
            nameInput.value = data.position.name;
            departmentSelect.value = data.position.department_id;
            salaryInput.value = data.position.salary;
            requirementsInput.value = data.position.requirements || "";
            benefitsInput.value = data.position.benefits || "";
        } else {
            showError(data.message || "Lỗi tải thông tin vị trí");
            setTimeout(() => {
                window.location.href = "/admin/positions/list.html";
            }, 2000);
        }
    } catch (error) {
        showError("Lỗi kết nối");
    } finally {
        hideLoading();
    }
}

// Setup event listeners
function setupEventListeners() {
    // Form submission
    editPositionForm.addEventListener("submit", handleSubmit);

    // Logout
    logoutBtn.addEventListener("click", () => {
        localStorage.removeItem("token");
        window.location.href = "/login.html";
    });
}

// Handle form submission
async function handleSubmit(event) {
    event.preventDefault();
    
    // Validate form
    if (!validateForm()) {
        return;
    }

    showLoading();
    try {
        const formData = new FormData(editPositionForm);
        const data = Object.fromEntries(formData.entries());

        const response = await fetch("/api/positions.php?action=update", {
            method: "PUT",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            window.location.href = "/admin/positions/list.html";
        } else {
            showError(result.message || "Lỗi cập nhật thông tin vị trí");
        }
    } catch (error) {
        showError("Lỗi kết nối");
    } finally {
        hideLoading();
    }
}

// Validate form
function validateForm() {
    const name = nameInput.value.trim();
    const department = departmentSelect.value;
    const salary = salaryInput.value;

    if (!name) {
        showError("Vui lòng nhập tên vị trí");
        return false;
    }

    if (!department) {
        showError("Vui lòng chọn phòng ban");
        return false;
    }

    if (!salary || salary < 0) {
        showError("Vui lòng nhập mức lương hợp lệ");
        return false;
    }

    return true;
}

// Utility functions
function showLoading() {
    loadingSpinner.style.display = "flex";
}

function hideLoading() {
    loadingSpinner.style.display = "none";
}

function showError(message) {
    errorText.textContent = message;
    errorMessage.style.display = "flex";
    setTimeout(() => {
        errorMessage.style.display = "none";
    }, 5000);
} 