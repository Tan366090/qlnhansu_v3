// Global variables
let currentPage = 1;
let totalPages = 1;
let currentDepartmentId = "";
let currentEmployeeId = "";
let searchQuery = "";
let certificates = [];
let departments = [];
let employees = [];

// DOM Elements
const certificateTable = document.getElementById("certificateTable").getElementsByTagName("tbody")[0];
const searchInput = document.getElementById("searchInput");
const searchBtn = document.getElementById("searchBtn");
const departmentFilter = document.getElementById("departmentFilter");
const employeeFilter = document.getElementById("employeeFilter");
const addCertificateBtn = document.getElementById("addCertificateBtn");
const prevPageBtn = document.getElementById("prevPage");
const nextPageBtn = document.getElementById("nextPage");
const pageInfo = document.getElementById("pageInfo");
const loadingSpinner = document.getElementById("loadingSpinner");
const errorMessage = document.getElementById("errorMessage");
const errorText = document.getElementById("errorText");
const deleteModal = document.getElementById("deleteModal");
const cancelDeleteBtn = document.getElementById("cancelDelete");
const confirmDeleteBtn = document.getElementById("confirmDelete");
const totalCertificatesEl = document.getElementById("totalCertificates");
const employeesWithCertificatesEl = document.getElementById("employeesWithCertificates");
const departmentsWithCertificatesEl = document.getElementById("departmentsWithCertificates");
const userName = document.getElementById("userName");
const logoutBtn = document.getElementById("logoutBtn");

// Initialize page
document.addEventListener("DOMContentLoaded", () => {
    checkAuth();
    loadDepartments();
    loadEmployees();
    loadCertificates();
    setupEventListeners();
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

// Load departments
async function loadDepartments() {
    try {
        const response = await fetch("/api/departments.php");
        const data = await response.json();
        
        departments = data;
        departmentFilter.innerHTML = "<option value=\"\">Tất cả phòng ban</option>";
        
        departments.forEach(dept => {
            const option = document.createElement("option");
            option.value = dept.id;
            option.textContent = dept.name;
            departmentFilter.appendChild(option);
        });
    } catch (error) {
        showError("Lỗi tải danh sách phòng ban: " + error.message);
    }
}

// Load employees
async function loadEmployees() {
    try {
        const response = await fetch("/api/employees.php");
        const data = await response.json();
        
        employees = data;
        employeeFilter.innerHTML = "<option value=\"\">Tất cả nhân viên</option>";
        
        employees.forEach(emp => {
            const option = document.createElement("option");
            option.value = emp.id;
            option.textContent = emp.name;
            employeeFilter.appendChild(option);
        });
    } catch (error) {
        showError("Lỗi tải danh sách nhân viên: " + error.message);
    }
}

// Load certificates
async function loadCertificates() {
    showLoading();
    try {
        let url = `/api/certificates.php?page=${currentPage}`;
        
        if (searchQuery) {
            url += `&search=${encodeURIComponent(searchQuery)}`;
        }
        
        if (currentDepartmentId) {
            url += `&department_id=${currentDepartmentId}`;
        }
        
        if (currentEmployeeId) {
            url += `&employee_id=${currentEmployeeId}`;
        }
        
        const response = await fetch(url);
        const data = await response.json();
        
        certificates = data.certificates || [];
        totalPages = data.total_pages || 1;
        
        updateCertificateTable();
        updatePagination();
        updateStatistics();
    } catch (error) {
        showError("Lỗi tải danh sách bằng cấp: " + error.message);
    } finally {
        hideLoading();
    }
}

// Update certificate table
function updateCertificateTable() {
    certificateTable.innerHTML = "";
    
    certificates.forEach(cert => {
        const row = document.createElement("tr");
        
        row.innerHTML = `
            <td>${cert.id}</td>
            <td>${cert.name}</td>
            <td>${cert.employee_name}</td>
            <td>${cert.department_name}</td>
            <td>${cert.issuing_organization}</td>
            <td>${formatDate(cert.issue_date)}</td>
            <td>${cert.expiry_date ? formatDate(cert.expiry_date) : "-"}</td>
            <td>
                <button class="btn btn-primary" onclick="editCertificate(${cert.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger" onclick="confirmDeleteCertificate(${cert.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        certificateTable.appendChild(row);
    });
}

// Update pagination
function updatePagination() {
    prevPageBtn.disabled = currentPage <= 1;
    nextPageBtn.disabled = currentPage >= totalPages;
    pageInfo.textContent = `Trang ${currentPage} / ${totalPages}`;
}

// Update statistics
function updateStatistics() {
    totalCertificatesEl.textContent = certificates.length;
    
    const uniqueEmployees = new Set(certificates.map(c => c.employee_id)).size;
    employeesWithCertificatesEl.textContent = uniqueEmployees;
    
    const uniqueDepartments = new Set(certificates.map(c => c.department_id)).size;
    departmentsWithCertificatesEl.textContent = uniqueDepartments;
}

// Setup event listeners
function setupEventListeners() {
    // Search
    searchBtn.addEventListener("click", () => {
        searchQuery = searchInput.value.trim();
        currentPage = 1;
        loadCertificates();
    });
    
    searchInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            searchBtn.click();
        }
    });
    
    // Filters
    departmentFilter.addEventListener("change", () => {
        currentDepartmentId = departmentFilter.value;
        currentPage = 1;
        loadCertificates();
    });
    
    employeeFilter.addEventListener("change", () => {
        currentEmployeeId = employeeFilter.value;
        currentPage = 1;
        loadCertificates();
    });
    
    // Add certificate
    addCertificateBtn.addEventListener("click", () => {
        window.location.href = "/admin/certificates/add.html";
    });
    
    // Pagination
    prevPageBtn.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            loadCertificates();
        }
    });
    
    nextPageBtn.addEventListener("click", () => {
        if (currentPage < totalPages) {
            currentPage++;
            loadCertificates();
        }
    });
    
    // Delete modal
    cancelDeleteBtn.addEventListener("click", () => {
        deleteModal.style.display = "none";
    });
    
    confirmDeleteBtn.addEventListener("click", deleteCertificate);
    
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

// Edit certificate
function editCertificate(id) {
    window.location.href = `/admin/certificates/edit.html?id=${id}`;
}

// Confirm delete certificate
function confirmDeleteCertificate(id) {
    confirmDeleteBtn.dataset.id = id;
    deleteModal.style.display = "flex";
}

// Delete certificate
async function deleteCertificate() {
    const id = confirmDeleteBtn.dataset.id;
    showLoading();
    
    try {
        const response = await fetch("/api/certificates.php", {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ id })
        });
        
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        deleteModal.style.display = "none";
        loadCertificates();
    } catch (error) {
        showError("Lỗi xóa bằng cấp: " + error.message);
    } finally {
        hideLoading();
    }
}

// Utility functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString("vi-VN");
}

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