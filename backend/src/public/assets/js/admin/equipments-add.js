// DOM Elements
const addEquipmentForm = document.getElementById("addEquipmentForm");
const departmentSelect = document.getElementById("department");
const cancelBtn = document.getElementById("cancelBtn");
const logoutBtn = document.getElementById("logoutBtn");
const userName = document.getElementById("userName");
const loadingSpinner = document.getElementById("loadingSpinner");
const errorMessage = document.getElementById("errorMessage");
const errorText = document.getElementById("errorText");

// Initialize page
async function init() {
    try {
        await checkAuth();
        await loadDepartments();
        setupEventListeners();
    } catch (error) {
        showError("Không thể khởi tạo trang: " + error.message);
    }
}

// Check authentication
async function checkAuth() {
    try {
        const response = await fetch("/api/auth.php");
        const data = await response.json();
        
        if (!data.authenticated) {
            window.location.href = "/login.html";
            return;
        }
        
        userName.textContent = data.user.name;
    } catch (error) {
        window.location.href = "/login.html";
    }
}

// Load departments
async function loadDepartments() {
    try {
        const response = await fetch("/api/departments.php");
        const data = await response.json();
        
        if (data.success) {
            updateDepartmentSelect(data.departments);
        } else {
            throw new Error(data.message || "Không thể tải danh sách phòng ban");
        }
    } catch (error) {
        showError("Không thể tải danh sách phòng ban: " + error.message);
    }
}

// Update department select
function updateDepartmentSelect(departments) {
    departmentSelect.innerHTML = "<option value=\"\">Chọn phòng ban</option>";
    
    departments.forEach(dept => {
        const option = document.createElement("option");
        option.value = dept.id;
        option.textContent = dept.name;
        departmentSelect.appendChild(option);
    });
}

// Validate form
function validateForm(formData) {
    const errors = [];
    
    if (!formData.get("name")) {
        errors.push("Vui lòng nhập tên thiết bị");
    }
    
    if (!formData.get("type")) {
        errors.push("Vui lòng chọn loại thiết bị");
    }
    
    if (!formData.get("department_id")) {
        errors.push("Vui lòng chọn phòng ban");
    }
    
    if (!formData.get("purchase_date")) {
        errors.push("Vui lòng chọn ngày mua");
    }
    
    const purchaseDate = new Date(formData.get("purchase_date"));
    const warrantyEndDate = formData.get("warranty_end_date") ? new Date(formData.get("warranty_end_date")) : null;
    
    if (warrantyEndDate && warrantyEndDate < purchaseDate) {
        errors.push("Ngày hết hạn bảo hành phải sau ngày mua");
    }
    
    return errors;
}

// Handle form submission
async function handleSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(addEquipmentForm);
    const errors = validateForm(formData);
    
    if (errors.length > 0) {
        showError(errors.join("<br>"));
        return;
    }
    
    showLoading();
    
    try {
        const response = await fetch("/api/equipments.php", {
            method: "POST",
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = "/admin/equipments/list.html";
        } else {
            throw new Error(data.message || "Không thể thêm thiết bị");
        }
    } catch (error) {
        showError(error.message);
    } finally {
        hideLoading();
    }
}

// Setup event listeners
function setupEventListeners() {
    // Form submission
    addEquipmentForm.addEventListener("submit", handleSubmit);
    
    // Cancel button
    cancelBtn.addEventListener("click", () => {
        window.location.href = "/admin/equipments/list.html";
    });
    
    // Logout button
    logoutBtn.addEventListener("click", async () => {
        try {
            const response = await fetch("/api/auth.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ action: "logout" })
            });
            
            const data = await response.json();
            
            if (data.success) {
                window.location.href = "/login.html";
            }
        } catch (error) {
            showError("Không thể đăng xuất: " + error.message);
        }
    });
}

// Show loading spinner
function showLoading() {
    loadingSpinner.style.display = "flex";
}

// Hide loading spinner
function hideLoading() {
    loadingSpinner.style.display = "none";
}

// Show error message
function showError(message) {
    errorText.innerHTML = message;
    errorMessage.style.display = "flex";
    
    setTimeout(() => {
        errorMessage.style.display = "none";
    }, 5000);
}

// Initialize page when DOM is loaded
document.addEventListener("DOMContentLoaded", init); 