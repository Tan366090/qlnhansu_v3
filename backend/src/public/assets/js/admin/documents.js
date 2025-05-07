// Global variables
let currentPage = 1;
let totalPages = 1;
let departmentId = "";
let employeeId = "";
let typeFilter = "";
let searchQuery = "";
let documents = [];
let departments = [];
let employees = [];

// DOM Elements
const documentsTableBody = document.getElementById("documentsTableBody");
const searchInput = document.getElementById("searchInput");
const searchBtn = document.getElementById("searchBtn");
const departmentFilter = document.getElementById("departmentFilter");
const employeeFilter = document.getElementById("employeeFilter");
const typeFilterSelect = document.getElementById("typeFilter");
const uploadBtn = document.getElementById("uploadBtn");
const prevPageBtn = document.getElementById("prevPage");
const nextPageBtn = document.getElementById("nextPage");
const pageInfo = document.getElementById("pageInfo");
const loadingSpinner = document.getElementById("loadingSpinner");
const errorMessage = document.getElementById("errorMessage");
const errorText = document.getElementById("errorText");
const deleteModal = document.getElementById("deleteModal");
const cancelDeleteBtn = document.getElementById("cancelDelete");
const confirmDeleteBtn = document.getElementById("confirmDelete");
const userName = document.getElementById("userName");
const logoutBtn = document.getElementById("logoutBtn");

// Initialize page
async function init() {
    try {
        await checkAuth();
        await loadDepartments();
        await loadEmployees();
        await loadDocuments();
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
            departments = data.departments;
            updateDepartmentFilter();
        }
    } catch (error) {
        showError("Không thể tải danh sách phòng ban: " + error.message);
    }
}

// Update department filter
function updateDepartmentFilter() {
    departmentFilter.innerHTML = "<option value=\"\">Tất cả phòng ban</option>";
    
    departments.forEach(dept => {
        const option = document.createElement("option");
        option.value = dept.id;
        option.textContent = dept.name;
        departmentFilter.appendChild(option);
    });
}

// Load employees
async function loadEmployees() {
    try {
        const response = await fetch("/api/employees.php");
        const data = await response.json();
        
        if (data.success) {
            employees = data.employees;
            updateEmployeeFilter();
        }
    } catch (error) {
        showError("Không thể tải danh sách nhân viên: " + error.message);
    }
}

// Update employee filter
function updateEmployeeFilter() {
    employeeFilter.innerHTML = "<option value=\"\">Tất cả nhân viên</option>";
    
    employees.forEach(emp => {
        const option = document.createElement("option");
        option.value = emp.id;
        option.textContent = emp.name;
        employeeFilter.appendChild(option);
    });
}

// Load documents
async function loadDocuments() {
    showLoading();
    try {
        let url = `/api/documents.php?page=${currentPage}`;
        
        if (searchQuery) {
            url += `&search=${encodeURIComponent(searchQuery)}`;
        }
        
        if (departmentId) {
            url += `&department_id=${departmentId}`;
        }
        
        if (employeeId) {
            url += `&employee_id=${employeeId}`;
        }
        
        if (typeFilter) {
            url += `&type=${typeFilter}`;
        }
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            documents = data.documents;
            totalPages = data.total_pages;
            updateDocumentsTable();
            updatePagination();
            updateStatistics();
        }
    } catch (error) {
        showError("Không thể tải danh sách tài liệu: " + error.message);
    } finally {
        hideLoading();
    }
}

// Update documents table
function updateDocumentsTable() {
    documentsTableBody.innerHTML = "";
    
    if (documents.length === 0) {
        const row = document.createElement("tr");
        row.innerHTML = "<td colspan=\"7\" class=\"text-center\">Không có tài liệu nào</td>";
        documentsTableBody.appendChild(row);
        return;
    }
    
    documents.forEach(doc => {
        const row = document.createElement("tr");
        
        const typeIcon = doc.type === "application/pdf" ? "fa-file-pdf" : "fa-file-word";
        const typeText = doc.type === "application/pdf" ? "PDF" : "Word";
        
        row.innerHTML = `
            <td>${doc.name}</td>
            <td><i class="fas ${typeIcon}"></i> ${typeText}</td>
            <td>${formatFileSize(doc.size)}</td>
            <td>${doc.employee_name}</td>
            <td>${doc.department_name}</td>
            <td>${formatDate(doc.upload_date)}</td>
            <td>
                <button class="btn btn-primary" onclick="viewDocument(${doc.id})">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-danger" onclick="confirmDelete(${doc.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        documentsTableBody.appendChild(row);
    });
}

// Update pagination
function updatePagination() {
    prevPageBtn.disabled = currentPage === 1;
    nextPageBtn.disabled = currentPage === totalPages;
    pageInfo.textContent = `Trang ${currentPage} / ${totalPages}`;
}

// Update statistics
function updateStatistics() {
    const totalDocs = documents.length;
    const pdfDocs = documents.filter(doc => doc.type === "application/pdf").length;
    const wordDocs = documents.filter(doc => doc.type === "application/msword").length;
    
    document.getElementById("totalDocuments").textContent = totalDocs;
    document.getElementById("pdfDocuments").textContent = pdfDocs;
    document.getElementById("wordDocuments").textContent = wordDocs;
}

// Setup event listeners
function setupEventListeners() {
    // Search
    searchBtn.addEventListener("click", () => {
        searchQuery = searchInput.value.trim();
        currentPage = 1;
        loadDocuments();
    });
    
    searchInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            searchQuery = searchInput.value.trim();
            currentPage = 1;
            loadDocuments();
        }
    });
    
    // Filters
    departmentFilter.addEventListener("change", () => {
        departmentId = departmentFilter.value;
        currentPage = 1;
        loadDocuments();
    });
    
    employeeFilter.addEventListener("change", () => {
        employeeId = employeeFilter.value;
        currentPage = 1;
        loadDocuments();
    });
    
    typeFilterSelect.addEventListener("change", () => {
        typeFilter = typeFilterSelect.value;
        currentPage = 1;
        loadDocuments();
    });
    
    // Upload
    uploadBtn.addEventListener("click", () => {
        window.location.href = "/admin/documents/upload.html";
    });
    
    // Pagination
    prevPageBtn.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            loadDocuments();
        }
    });
    
    nextPageBtn.addEventListener("click", () => {
        if (currentPage < totalPages) {
            currentPage++;
            loadDocuments();
        }
    });
    
    // Delete modal
    cancelDeleteBtn.addEventListener("click", () => {
        deleteModal.style.display = "none";
    });
    
    confirmDeleteBtn.addEventListener("click", async () => {
        const documentId = confirmDeleteBtn.dataset.documentId;
        await deleteDocument(documentId);
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

// View document
function viewDocument(id) {
    window.location.href = `/admin/documents/view.html?id=${id}`;
}

// Confirm delete
function confirmDelete(id) {
    confirmDeleteBtn.dataset.documentId = id;
    deleteModal.style.display = "flex";
}

// Delete document
async function deleteDocument(id) {
    showLoading();
    try {
        const response = await fetch(`/api/documents.php?id=${id}`, {
            method: "DELETE"
        });
        
        const data = await response.json();
        
        if (data.success) {
            deleteModal.style.display = "none";
            await loadDocuments();
        } else {
            showError(data.message || "Không thể xóa tài liệu");
        }
    } catch (error) {
        showError("Không thể xóa tài liệu: " + error.message);
    } finally {
        hideLoading();
    }
}

// Utility functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString("vi-VN");
}

function formatFileSize(bytes) {
    if (bytes === 0) return "0 Bytes";
    
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
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

// Initialize page when DOM is loaded
document.addEventListener("DOMContentLoaded", init); 