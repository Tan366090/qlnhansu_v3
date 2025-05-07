// Global variables
let currentPage = 1;
let totalPages = 1;
let departments = [];
let equipmentToDelete = null;

// DOM Elements
const equipmentTableBody = document.getElementById("equipmentTableBody");
const searchInput = document.getElementById("searchInput");
const searchBtn = document.getElementById("searchBtn");
const departmentFilter = document.getElementById("departmentFilter");
const statusFilter = document.getElementById("statusFilter");
const addEquipmentBtn = document.getElementById("addEquipmentBtn");
const prevPageBtn = document.getElementById("prevPage");
const nextPageBtn = document.getElementById("nextPage");
const pageInfo = document.getElementById("pageInfo");
const loadingSpinner = document.getElementById("loadingSpinner");
const errorMessage = document.getElementById("errorMessage");
const errorText = document.getElementById("errorText");
const deleteModal = document.getElementById("deleteModal");
const cancelDeleteBtn = document.getElementById("cancelDeleteBtn");
const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
const userName = document.getElementById("userName");
const logoutBtn = document.getElementById("logoutBtn");

// Statistics elements
const totalEquipmentsEl = document.getElementById("totalEquipments");
const availableEquipmentsEl = document.getElementById("availableEquipments");
const assignedEquipmentsEl = document.getElementById("assignedEquipments");
const maintenanceEquipmentsEl = document.getElementById("maintenanceEquipments");

// Initialize page
async function init() {
    try {
        await checkAuth();
        await loadDepartments();
        await loadEquipments();
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

// Load equipments
async function loadEquipments() {
    showLoading();
    
    try {
        const params = new URLSearchParams({
            page: currentPage,
            search: searchInput.value,
            department_id: departmentFilter.value,
            status: statusFilter.value
        });
        
        const response = await fetch(`/api/equipments.php?${params}`);
        const data = await response.json();
        
        if (data.success) {
            updateEquipmentTable(data.equipments);
            updatePagination(data.total_pages);
            updateStatistics(data.equipments);
        } else {
            throw new Error(data.message || "Không thể tải danh sách thiết bị");
        }
    } catch (error) {
        showError(error.message);
    } finally {
        hideLoading();
    }
}

// Update equipment table
function updateEquipmentTable(equipments) {
    equipmentTableBody.innerHTML = "";
    
    if (equipments.length === 0) {
        const row = document.createElement("tr");
        row.innerHTML = "<td colspan=\"9\" class=\"text-center\">Không có thiết bị nào</td>";
        equipmentTableBody.appendChild(row);
        return;
    }
    
    equipments.forEach(equipment => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${equipment.name}</td>
            <td>${equipment.type}</td>
            <td>${equipment.model || "-"}</td>
            <td>${equipment.serial_number || "-"}</td>
            <td>${equipment.department_name || "-"}</td>
            <td>
                <span class="status-badge ${equipment.status}">
                    ${getStatusText(equipment.status)}
                </span>
            </td>
            <td>${formatDate(equipment.purchase_date)}</td>
            <td>${formatDate(equipment.warranty_end_date)}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-primary btn-sm view-btn" data-id="${equipment.id}">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-success btn-sm assign-btn" data-id="${equipment.id}" 
                            ${equipment.status !== "available" ? "disabled" : ""}>
                        <i class="fas fa-user-plus"></i>
                    </button>
                    <button class="btn btn-warning btn-sm edit-btn" data-id="${equipment.id}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-danger btn-sm delete-btn" data-id="${equipment.id}"
                            ${equipment.status === "assigned" ? "disabled" : ""}>
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        equipmentTableBody.appendChild(row);
    });
}

// Get status text
function getStatusText(status) {
    const statusMap = {
        "available": "Sẵn sàng",
        "assigned": "Đã cấp phát",
        "maintenance": "Bảo trì",
        "broken": "Hỏng"
    };
    return statusMap[status] || status;
}

// Update pagination
function updatePagination(total) {
    totalPages = total;
    pageInfo.textContent = `Trang ${currentPage} / ${totalPages}`;
    prevPageBtn.disabled = currentPage === 1;
    nextPageBtn.disabled = currentPage === totalPages;
}

// Update statistics
function updateStatistics(equipments) {
    const stats = {
        total: equipments.length,
        available: 0,
        assigned: 0,
        maintenance: 0
    };
    
    equipments.forEach(equipment => {
        if (equipment.status === "available") stats.available++;
        else if (equipment.status === "assigned") stats.assigned++;
        else if (equipment.status === "maintenance") stats.maintenance++;
    });
    
    totalEquipmentsEl.textContent = stats.total;
    availableEquipmentsEl.textContent = stats.available;
    assignedEquipmentsEl.textContent = stats.assigned;
    maintenanceEquipmentsEl.textContent = stats.maintenance;
}

// Format date
function formatDate(dateString) {
    if (!dateString) return "-";
    const date = new Date(dateString);
    return date.toLocaleDateString("vi-VN");
}

// Setup event listeners
function setupEventListeners() {
    // Search
    searchBtn.addEventListener("click", () => {
        currentPage = 1;
        loadEquipments();
    });
    
    searchInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            currentPage = 1;
            loadEquipments();
        }
    });
    
    // Filters
    departmentFilter.addEventListener("change", () => {
        currentPage = 1;
        loadEquipments();
    });
    
    statusFilter.addEventListener("change", () => {
        currentPage = 1;
        loadEquipments();
    });
    
    // Pagination
    prevPageBtn.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            loadEquipments();
        }
    });
    
    nextPageBtn.addEventListener("click", () => {
        if (currentPage < totalPages) {
            currentPage++;
            loadEquipments();
        }
    });
    
    // Add equipment
    addEquipmentBtn.addEventListener("click", () => {
        window.location.href = "/admin/equipments/add.html";
    });
    
    // Table actions
    equipmentTableBody.addEventListener("click", (e) => {
        const target = e.target.closest("button");
        if (!target) return;
        
        const id = target.dataset.id;
        if (!id) return;
        
        if (target.classList.contains("view-btn")) {
            window.location.href = `/admin/equipments/view.html?id=${id}`;
        } else if (target.classList.contains("assign-btn")) {
            window.location.href = `/admin/equipments/assign.html?id=${id}`;
        } else if (target.classList.contains("edit-btn")) {
            window.location.href = `/admin/equipments/edit.html?id=${id}`;
        } else if (target.classList.contains("delete-btn")) {
            showDeleteModal(id);
        }
    });
    
    // Delete modal
    cancelDeleteBtn.addEventListener("click", () => {
        hideDeleteModal();
    });
    
    confirmDeleteBtn.addEventListener("click", async () => {
        if (!equipmentToDelete) return;
        
        try {
            const response = await fetch(`/api/equipments.php?id=${equipmentToDelete}`, {
                method: "DELETE"
            });
            
            const data = await response.json();
            
            if (data.success) {
                hideDeleteModal();
                loadEquipments();
            } else {
                throw new Error(data.message || "Không thể xóa thiết bị");
            }
        } catch (error) {
            showError(error.message);
        }
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

// Show delete modal
function showDeleteModal(id) {
    equipmentToDelete = id;
    deleteModal.style.display = "flex";
}

// Hide delete modal
function hideDeleteModal() {
    equipmentToDelete = null;
    deleteModal.style.display = "none";
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
    errorText.textContent = message;
    errorMessage.style.display = "flex";
    
    setTimeout(() => {
        errorMessage.style.display = "none";
    }, 5000);
}

// Initialize page when DOM is loaded
document.addEventListener("DOMContentLoaded", init); 