// Global variables
let currentPage = 1;
let totalPages = 1;
let currentDepartmentId = "";
let searchQuery = "";

// DOM Elements
const positionTable = document.getElementById("positionTable");
const searchInput = document.getElementById("searchInput");
const searchBtn = document.getElementById("searchBtn");
const departmentFilter = document.getElementById("departmentFilter");
const addPositionBtn = document.getElementById("addPositionBtn");
const prevPageBtn = document.getElementById("prevPage");
const nextPageBtn = document.getElementById("nextPage");
const pageInfo = document.getElementById("pageInfo");
const loadingSpinner = document.getElementById("loadingSpinner");
const errorMessage = document.getElementById("errorMessage");
const errorText = document.getElementById("errorText");
const userName = document.getElementById("userName");
const logoutBtn = document.getElementById("logoutBtn");

// Statistics elements
const totalPositions = document.getElementById("totalPositions");
const totalDepartments = document.getElementById("totalDepartments");
const totalEmployees = document.getElementById("totalEmployees");
const averageSalary = document.getElementById("averageSalary");

// Initialize page
document.addEventListener("DOMContentLoaded", () => {
    checkAuth();
    loadDepartments();
    loadPositions();
    loadStatistics();
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

// Load departments for filter
async function loadDepartments() {
    try {
        const response = await fetch("/api/departments.php?action=getAll");
        const data = await response.json();

        if (data.success) {
            departmentFilter.innerHTML = "<option value=\"\">Tất cả phòng ban</option>";
            data.departments.forEach(dept => {
                const option = document.createElement("option");
                option.value = dept.id;
                option.textContent = dept.name;
                departmentFilter.appendChild(option);
            });
        } else {
            showError(data.message || "Lỗi tải danh sách phòng ban");
        }
    } catch (error) {
        showError("Lỗi kết nối");
    }
}

// Load positions
async function loadPositions() {
    showLoading();
    try {
        const params = new URLSearchParams({
            page: currentPage,
            department_id: currentDepartmentId,
            search: searchQuery
        });

        const response = await fetch(`/api/positions.php?action=getAll&${params}`);
        const data = await response.json();

        if (data.success) {
            updatePositionTable(data.positions);
            updatePagination(data.total_pages);
        } else {
            showError(data.message || "Lỗi tải danh sách vị trí");
        }
    } catch (error) {
        showError("Lỗi kết nối");
    } finally {
        hideLoading();
    }
}

// Update position table
function updatePositionTable(positions) {
    const tbody = positionTable.querySelector("tbody");
    tbody.innerHTML = "";

    positions.forEach(position => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${position.name}</td>
            <td>${position.department_name}</td>
            <td>${position.employee_count}</td>
            <td>${formatCurrency(position.salary)}</td>
            <td>${position.requirements || "-"}</td>
            <td>${position.benefits || "-"}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-view" onclick="viewPosition(${position.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-edit" onclick="editPosition(${position.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-delete" onclick="deletePosition(${position.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Load statistics
async function loadStatistics() {
    try {
        const response = await fetch("/api/positions.php?action=getStatistics");
        const data = await response.json();

        if (data.success) {
            totalPositions.textContent = data.total_positions;
            totalDepartments.textContent = data.total_departments;
            totalEmployees.textContent = data.total_employees;
            averageSalary.textContent = formatCurrency(data.average_salary);
        } else {
            showError(data.message || "Lỗi tải thống kê");
        }
    } catch (error) {
        showError("Lỗi kết nối");
    }
}

// Update pagination
function updatePagination(total) {
    totalPages = total;
    pageInfo.textContent = `Trang ${currentPage} / ${totalPages}`;
    prevPageBtn.disabled = currentPage === 1;
    nextPageBtn.disabled = currentPage === totalPages;
}

// Setup event listeners
function setupEventListeners() {
    // Search
    searchBtn.addEventListener("click", () => {
        searchQuery = searchInput.value.trim();
        currentPage = 1;
        loadPositions();
    });

    searchInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            searchBtn.click();
        }
    });

    // Filter
    departmentFilter.addEventListener("change", () => {
        currentDepartmentId = departmentFilter.value;
        currentPage = 1;
        loadPositions();
    });

    // Add position
    addPositionBtn.addEventListener("click", () => {
        window.location.href = "/admin/positions/add.html";
    });

    // Pagination
    prevPageBtn.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            loadPositions();
        }
    });

    nextPageBtn.addEventListener("click", () => {
        if (currentPage < totalPages) {
            currentPage++;
            loadPositions();
        }
    });

    // Logout
    logoutBtn.addEventListener("click", () => {
        localStorage.removeItem("token");
        window.location.href = "/login.html";
    });
}

// View position details
function viewPosition(id) {
    window.location.href = `/admin/positions/view.html?id=${id}`;
}

// Edit position
function editPosition(id) {
    window.location.href = `/admin/positions/edit.html?id=${id}`;
}

// Delete position
async function deletePosition(id) {
    if (!confirm("Bạn có chắc chắn muốn xóa vị trí này?")) {
        return;
    }

    showLoading();
    try {
        const response = await fetch(`/api/positions.php?action=delete&id=${id}`, {
            method: "DELETE"
        });
        const data = await response.json();

        if (data.success) {
            loadPositions();
            loadStatistics();
        } else {
            showError(data.message || "Lỗi xóa vị trí");
        }
    } catch (error) {
        showError("Lỗi kết nối");
    } finally {
        hideLoading();
    }
}

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND"
    }).format(amount);
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