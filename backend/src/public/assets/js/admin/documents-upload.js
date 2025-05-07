// DOM Elements
const uploadForm = document.getElementById("uploadForm");
const employeeSelect = document.getElementById("employee");
const documentFile = document.getElementById("documentFile");
const fileInfo = document.querySelector(".file-info");
const fileName = document.querySelector(".file-name");
const description = document.getElementById("description");
const cancelBtn = document.getElementById("cancelBtn");
const loadingSpinner = document.getElementById("loadingSpinner");
const errorMessage = document.getElementById("errorMessage");
const errorText = document.getElementById("errorText");
const userName = document.getElementById("userName");
const logoutBtn = document.getElementById("logoutBtn");

// Initialize page
async function init() {
    try {
        await checkAuth();
        await loadEmployees();
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

// Load employees
async function loadEmployees() {
    try {
        const response = await fetch("/api/employees.php");
        const data = await response.json();
        
        if (data.success) {
            updateEmployeeSelect(data.employees);
        }
    } catch (error) {
        showError("Không thể tải danh sách nhân viên: " + error.message);
    }
}

// Update employee select
function updateEmployeeSelect(employees) {
    employeeSelect.innerHTML = "<option value=\"\">Chọn nhân viên</option>";
    
    employees.forEach(emp => {
        const option = document.createElement("option");
        option.value = emp.id;
        option.textContent = emp.name;
        employeeSelect.appendChild(option);
    });
}

// Setup event listeners
function setupEventListeners() {
    // File input change
    documentFile.addEventListener("change", handleFileSelect);
    
    // Drag and drop
    fileInfo.addEventListener("dragover", (e) => {
        e.preventDefault();
        fileInfo.classList.add("dragover");
    });
    
    fileInfo.addEventListener("dragleave", () => {
        fileInfo.classList.remove("dragover");
    });
    
    fileInfo.addEventListener("drop", (e) => {
        e.preventDefault();
        fileInfo.classList.remove("dragover");
        
        if (e.dataTransfer.files.length) {
            documentFile.files = e.dataTransfer.files;
            handleFileSelect();
        }
    });
    
    // Form submit
    uploadForm.addEventListener("submit", handleFormSubmit);
    
    // Cancel button
    cancelBtn.addEventListener("click", () => {
        window.location.href = "/admin/documents/list.html";
    });
    
    // Logout
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

// Handle file select
function handleFileSelect() {
    const file = documentFile.files[0];
    
    if (file) {
        // Check file type
        const allowedTypes = ["application/pdf", "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document"];
        
        if (!allowedTypes.includes(file.type)) {
            showError("Chỉ hỗ trợ file PDF và Word");
            documentFile.value = "";
            fileName.textContent = "";
            return;
        }
        
        // Check file size (max 10MB)
        if (file.size > 10 * 1024 * 1024) {
            showError("Kích thước file không được vượt quá 10MB");
            documentFile.value = "";
            fileName.textContent = "";
            return;
        }
        
        fileName.textContent = file.name;
    } else {
        fileName.textContent = "";
    }
}

// Handle form submit
async function handleFormSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(uploadForm);
    
    // Validate form
    if (!formData.get("employee_id")) {
        showError("Vui lòng chọn nhân viên");
        return;
    }
    
    if (!formData.get("file")) {
        showError("Vui lòng chọn file tài liệu");
        return;
    }
    
    showLoading();
    
    try {
        const response = await fetch("/api/documents.php", {
            method: "POST",
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = "/admin/documents/list.html";
        } else {
            showError(data.message || "Không thể tải lên tài liệu");
        }
    } catch (error) {
        showError("Không thể tải lên tài liệu: " + error.message);
    } finally {
        hideLoading();
    }
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

// Initialize page when DOM is loaded
document.addEventListener("DOMContentLoaded", init); 