// DOM Elements
const positionName = document.getElementById("positionName");
const departmentName = document.getElementById("departmentName");
const salary = document.getElementById("salary");
const employeeCount = document.getElementById("employeeCount");
const requirements = document.getElementById("requirements");
const benefits = document.getElementById("benefits");
const employeeTable = document.getElementById("employeeTable").getElementsByTagName("tbody")[0];
const editBtn = document.getElementById("editBtn");
const deleteBtn = document.getElementById("deleteBtn");
const deleteModal = document.getElementById("deleteModal");
const cancelDelete = document.getElementById("cancelDelete");
const confirmDelete = document.getElementById("confirmDelete");
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

// Load position data
async function loadPosition() {
    showLoading();
    try {
        const response = await fetch(`/api/positions.php?action=getById&id=${id}`);
        const data = await response.json();

        if (data.success) {
            // Update position details
            positionName.textContent = data.position.name;
            departmentName.textContent = data.position.department_name;
            salary.textContent = formatCurrency(data.position.salary);
            employeeCount.textContent = data.position.employee_count;
            requirements.textContent = data.position.requirements || "Không có yêu cầu";
            benefits.textContent = data.position.benefits || "Không có quyền lợi";

            // Update employee table
            if (data.position.employees && data.position.employees.length > 0) {
                employeeTable.innerHTML = data.position.employees.map(employee => `
                    <tr>
                        <td>${employee.id}</td>
                        <td>${employee.name}</td>
                        <td>${employee.email}</td>
                        <td>${employee.phone}</td>
                        <td>${formatDate(employee.hire_date)}</td>
                        <td>
                            <span class="status-badge ${employee.status === "active" ? "active" : "inactive"}">
                                ${employee.status === "active" ? "Đang làm việc" : "Đã nghỉ việc"}
                            </span>
                        </td>
                    </tr>
                `).join("");
            } else {
                employeeTable.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center">Không có nhân viên nào trong vị trí này</td>
                    </tr>
                `;
            }
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
    // Edit button
    editBtn.addEventListener("click", () => {
        window.location.href = `/admin/positions/edit.html?id=${id}`;
    });

    // Delete button
    deleteBtn.addEventListener("click", () => {
        deleteModal.style.display = "flex";
    });

    // Cancel delete
    cancelDelete.addEventListener("click", () => {
        deleteModal.style.display = "none";
    });

    // Confirm delete
    confirmDelete.addEventListener("click", async () => {
        showLoading();
        try {
            const response = await fetch(`/api/positions.php?action=delete&id=${id}`, {
                method: "DELETE"
            });
            const data = await response.json();

            if (data.success) {
                window.location.href = "/admin/positions/list.html";
            } else {
                showError(data.message || "Lỗi xóa vị trí");
            }
        } catch (error) {
            showError("Lỗi kết nối");
        } finally {
            hideLoading();
            deleteModal.style.display = "none";
        }
    });

    // Logout
    logoutBtn.addEventListener("click", () => {
        localStorage.removeItem("token");
        window.location.href = "/login.html";
    });
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

function formatCurrency(amount) {
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND"
    }).format(amount);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString("vi-VN");
} 