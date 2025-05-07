// DOM Elements
const addCertificateForm = document.getElementById("addCertificateForm");
const employeeIdSelect = document.getElementById("employeeId");
const nameInput = document.getElementById("name");
const issuingOrganizationInput = document.getElementById("issuingOrganization");
const issueDateInput = document.getElementById("issueDate");
const expiryDateInput = document.getElementById("expiryDate");
const descriptionInput = document.getElementById("description");
const cancelBtn = document.getElementById("cancelBtn");
const loadingSpinner = document.getElementById("loadingSpinner");
const errorMessage = document.getElementById("errorMessage");
const errorText = document.getElementById("errorText");
const userName = document.getElementById("userName");
const logoutBtn = document.getElementById("logoutBtn");

// Initialize page
document.addEventListener("DOMContentLoaded", () => {
    checkAuth();
    loadEmployees();
    setupEventListeners();
    setMinDates();
});

// Check authentication
async function checkAuth() {
    try {
        const response = await fetch("/api/auth/check.php");
        const data = await response.json();
        
        if (!data.authenticated) {
            window.location.href = "/login.html";
            return;
        }
        
        userName.textContent = data.user.name;
    } catch (error) {
        showError("Lỗi xác thực: " + error.message);
    }
}

// Load employees
async function loadEmployees() {
    try {
        const response = await fetch("/api/employees.php");
        const data = await response.json();
        
        employeeIdSelect.innerHTML = "<option value=\"\">Chọn nhân viên</option>";
        
        data.forEach(emp => {
            const option = document.createElement("option");
            option.value = emp.id;
            option.textContent = emp.name;
            employeeIdSelect.appendChild(option);
        });
    } catch (error) {
        showError("Lỗi tải danh sách nhân viên: " + error.message);
    }
}

// Set minimum dates
function setMinDates() {
    const today = new Date().toISOString().split("T")[0];
    issueDateInput.min = today;
    expiryDateInput.min = today;
}

// Setup event listeners
function setupEventListeners() {
    // Form submission
    addCertificateForm.addEventListener("submit", handleSubmit);
    
    // Cancel button
    cancelBtn.addEventListener("click", () => {
        window.location.href = "/admin/certificates/list.html";
    });
    
    // Issue date change
    issueDateInput.addEventListener("change", () => {
        expiryDateInput.min = issueDateInput.value;
    });
    
    // Logout
    logoutBtn.addEventListener("click", () => {
        fetch("/api/auth/logout.php")
            .then(() => {
                window.location.href = "/login.html";
            })
            .catch(error => {
                showError("Lỗi đăng xuất: " + error.message);
            });
    });
}

// Handle form submission
async function handleSubmit(e) {
    e.preventDefault();
    
    if (!validateForm()) {
        return;
    }
    
    showLoading();
    
    try {
        const formData = {
            employee_id: employeeIdSelect.value,
            name: nameInput.value.trim(),
            issuing_organization: issuingOrganizationInput.value.trim(),
            issue_date: issueDateInput.value,
            expiry_date: expiryDateInput.value || null,
            description: descriptionInput.value.trim() || null
        };
        
        const response = await fetch("/api/certificates.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        window.location.href = "/admin/certificates/list.html";
    } catch (error) {
        showError("Lỗi thêm bằng cấp: " + error.message);
    } finally {
        hideLoading();
    }
}

// Validate form
function validateForm() {
    let isValid = true;
    
    // Clear previous errors
    document.querySelectorAll(".error").forEach(el => {
        el.classList.remove("error");
    });
    
    // Validate employee
    if (!employeeIdSelect.value) {
        employeeIdSelect.classList.add("error");
        showError("Vui lòng chọn nhân viên");
        isValid = false;
    }
    
    // Validate name
    if (!nameInput.value.trim()) {
        nameInput.classList.add("error");
        showError("Vui lòng nhập tên bằng cấp");
        isValid = false;
    }
    
    // Validate issuing organization
    if (!issuingOrganizationInput.value.trim()) {
        issuingOrganizationInput.classList.add("error");
        showError("Vui lòng nhập tên tổ chức cấp");
        isValid = false;
    }
    
    // Validate issue date
    if (!issueDateInput.value) {
        issueDateInput.classList.add("error");
        showError("Vui lòng chọn ngày cấp");
        isValid = false;
    }
    
    // Validate expiry date
    if (expiryDateInput.value && expiryDateInput.value < issueDateInput.value) {
        expiryDateInput.classList.add("error");
        showError("Ngày hết hạn phải sau ngày cấp");
        isValid = false;
    }
    
    return isValid;
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