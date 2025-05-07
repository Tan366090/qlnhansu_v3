// DOM Elements
const userName = document.getElementById("userName");
const logoutBtn = document.getElementById("logoutBtn");
const leaveForm = document.getElementById("leaveForm");
const typeSelect = document.getElementById("type");
const startDateInput = document.getElementById("startDate");
const endDateInput = document.getElementById("endDate");
const daysInput = document.getElementById("days");
const reasonInput = document.getElementById("reason");
const attachmentsInput = document.getElementById("attachments");
const cancelBtn = document.getElementById("cancelBtn");
const loadingSpinner = document.getElementById("loadingSpinner");
const errorMessage = document.getElementById("errorMessage");
const errorText = document.getElementById("errorText");

// Initialize Page
document.addEventListener("DOMContentLoaded", () => {
    checkAuth();
    setupEventListeners();
    setMinDate();
});

// Check Authentication
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

// Set Minimum Date
function setMinDate() {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, "0");
    const day = String(today.getDate()).padStart(2, "0");
    const minDate = `${year}-${month}-${day}`;
    
    startDateInput.min = minDate;
    endDateInput.min = minDate;
}

// Setup Event Listeners
function setupEventListeners() {
    // Date Change Events
    startDateInput.addEventListener("change", calculateDays);
    endDateInput.addEventListener("change", calculateDays);
    
    // Form Submit
    leaveForm.addEventListener("submit", handleSubmit);
    
    // Cancel Button
    cancelBtn.addEventListener("click", () => {
        window.location.href = "/admin/leaves/list.html";
    });
    
    // Logout
    logoutBtn.addEventListener("click", async () => {
        try {
            const response = await fetch("/api/auth/logout.php");
            const data = await response.json();
            
            if (data.success) {
                window.location.href = "/login.html";
            } else {
                showError("Lỗi đăng xuất: " + data.error);
            }
        } catch (error) {
            showError("Lỗi đăng xuất: " + error.message);
        }
    });
}

// Calculate Days
function calculateDays() {
    if (startDateInput.value && endDateInput.value) {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);
        
        if (endDate < startDate) {
            showError("Ngày kết thúc phải sau ngày bắt đầu");
            endDateInput.value = "";
            daysInput.value = "";
            return;
        }
        
        // Calculate business days (excluding weekends)
        let days = 0;
        let currentDate = new Date(startDate);
        
        while (currentDate <= endDate) {
            const dayOfWeek = currentDate.getDay();
            if (dayOfWeek !== 0 && dayOfWeek !== 6) { // Not Saturday or Sunday
                days++;
            }
            currentDate.setDate(currentDate.getDate() + 1);
        }
        
        daysInput.value = days;
    }
}

// Handle Form Submit
async function handleSubmit(event) {
    event.preventDefault();
    
    if (!validateForm()) {
        return;
    }
    
    const formData = new FormData();
    formData.append("action", "request");
    formData.append("type", typeSelect.value);
    formData.append("start_date", startDateInput.value);
    formData.append("end_date", endDateInput.value);
    formData.append("days", daysInput.value);
    formData.append("reason", reasonInput.value);
    
    // Add attachments if any
    const attachments = attachmentsInput.files;
    if (attachments.length > 0) {
        for (let i = 0; i < attachments.length; i++) {
            formData.append("attachments[]", attachments[i]);
        }
    }
    
    showLoading();
    try {
        const response = await fetch("/api/leaves.php", {
            method: "POST",
            body: formData
        });
        
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        window.location.href = "/admin/leaves/list.html";
    } catch (error) {
        showError("Lỗi gửi đơn: " + error.message);
    } finally {
        hideLoading();
    }
}

// Validate Form
function validateForm() {
    // Check required fields
    if (!typeSelect.value) {
        showError("Vui lòng chọn loại nghỉ phép");
        return false;
    }
    
    if (!startDateInput.value) {
        showError("Vui lòng chọn ngày bắt đầu");
        return false;
    }
    
    if (!endDateInput.value) {
        showError("Vui lòng chọn ngày kết thúc");
        return false;
    }
    
    if (!daysInput.value || daysInput.value <= 0) {
        showError("Số ngày nghỉ phải lớn hơn 0");
        return false;
    }
    
    if (!reasonInput.value.trim()) {
        showError("Vui lòng nhập lý do nghỉ");
        return false;
    }
    
    // Check file size and type
    const attachments = attachmentsInput.files;
    if (attachments.length > 0) {
        for (let i = 0; i < attachments.length; i++) {
            const file = attachments[i];
            
            // Check file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                showError("Kích thước file không được vượt quá 5MB");
                return false;
            }
            
            // Check file type
            const allowedTypes = ["application/pdf", "image/jpeg", "image/png"];
            if (!allowedTypes.includes(file.type)) {
                showError("Chỉ chấp nhận file PDF, JPG hoặc PNG");
                return false;
            }
        }
    }
    
    return true;
}

// Utility Functions
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