// DOM Elements
const addPositionForm = document.getElementById("addPositionForm");
const departmentSelect = document.getElementById("department");
const loadingSpinner = document.getElementById("loadingSpinner");
const errorMessage = document.getElementById("errorMessage");
const errorText = document.getElementById("errorText");
const userName = document.getElementById("userName");
const logoutBtn = document.getElementById("logoutBtn");

// Initialize page
document.addEventListener("DOMContentLoaded", () => {
    checkAuth();
    loadDepartments();
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

// Setup event listeners
function setupEventListeners() {
    // Form submission
    addPositionForm.addEventListener("submit", handleSubmit);

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
        const formData = new FormData(addPositionForm);
        const data = Object.fromEntries(formData.entries());

        const response = await fetch("/api/positions.php?action=create", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            window.location.href = "/admin/positions/list.html";
        } else {
            showError(result.message || "Lỗi thêm vị trí mới");
        }
    } catch (error) {
        showError("Lỗi kết nối");
    } finally {
        hideLoading();
    }
}

// Validate form
function validateForm() {
    const name = document.getElementById("name").value.trim();
    const department = document.getElementById("department").value;
    const salary = document.getElementById("salary").value;

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