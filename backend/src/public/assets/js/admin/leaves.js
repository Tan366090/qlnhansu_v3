// DOM Elements
const userName = document.getElementById("userName");
const logoutBtn = document.getElementById("logoutBtn");
const searchInput = document.getElementById("searchInput");
const searchBtn = document.getElementById("searchBtn");
const statusFilter = document.getElementById("statusFilter");
const typeFilter = document.getElementById("typeFilter");
const requestBtn = document.getElementById("requestBtn");
const leaveTable = document.getElementById("leaveTable").getElementsByTagName("tbody")[0];
const prevPage = document.getElementById("prevPage");
const nextPage = document.getElementById("nextPage");
const pageInfo = document.getElementById("pageInfo");
const loadingSpinner = document.getElementById("loadingSpinner");
const errorMessage = document.getElementById("errorMessage");
const errorText = document.getElementById("errorText");
const rejectModal = document.getElementById("rejectModal");
const rejectReason = document.getElementById("rejectReason");
const cancelReject = document.getElementById("cancelReject");
const confirmReject = document.getElementById("confirmReject");

// Statistics Elements
const pendingCount = document.getElementById("pendingCount");
const approvedCount = document.getElementById("approvedCount");
const rejectedCount = document.getElementById("rejectedCount");
const totalDays = document.getElementById("totalDays");

// Global Variables
let currentPage = 1;
let totalPages = 1;
let currentStatus = "";
let currentType = "";
let currentSearch = "";
let currentLeaveId = null;

// Initialize Page
document.addEventListener("DOMContentLoaded", () => {
    checkAuth();
    loadLeaves();
    setupEventListeners();
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

// Load Leaves
async function loadLeaves() {
    showLoading();
    try {
        const response = await fetch(`/api/leaves.php?action=getAll&page=${currentPage}&status=${currentStatus}&type=${currentType}&search=${currentSearch}`);
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        updateLeaveTable(data.leaves);
        updatePagination(data.totalPages);
        updateStatistics(data.statistics);
    } catch (error) {
        showError("Lỗi tải dữ liệu: " + error.message);
    } finally {
        hideLoading();
    }
}

// Update Leave Table
function updateLeaveTable(leaves) {
    leaveTable.innerHTML = "";
    
    leaves.forEach(leave => {
        const row = document.createElement("tr");
        
        row.innerHTML = `
            <td>${leave.id}</td>
            <td>${leave.employee_name}</td>
            <td>${leave.department_name}</td>
            <td>${getLeaveTypeText(leave.type)}</td>
            <td>${formatDate(leave.start_date)}</td>
            <td>${formatDate(leave.end_date)}</td>
            <td>${leave.days}</td>
            <td>${leave.reason}</td>
            <td>${getStatusBadge(leave.status)}</td>
            <td>
                <div class="action-buttons">
                    ${leave.status === "pending" ? `
                        <button class="btn btn-primary" onclick="approveLeave(${leave.id})">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn btn-danger" onclick="showRejectModal(${leave.id})">
                            <i class="fas fa-times"></i>
                        </button>
                    ` : ""}
                    <button class="btn btn-secondary" onclick="viewLeave(${leave.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </td>
        `;
        
        leaveTable.appendChild(row);
    });
}

// Update Pagination
function updatePagination(total) {
    totalPages = total;
    pageInfo.textContent = `Trang ${currentPage} / ${totalPages}`;
    prevPage.disabled = currentPage === 1;
    nextPage.disabled = currentPage === totalPages;
}

// Update Statistics
function updateStatistics(stats) {
    pendingCount.textContent = stats.pending;
    approvedCount.textContent = stats.approved;
    rejectedCount.textContent = stats.rejected;
    totalDays.textContent = stats.total_days;
}

// Setup Event Listeners
function setupEventListeners() {
    // Search
    searchBtn.addEventListener("click", () => {
        currentSearch = searchInput.value;
        currentPage = 1;
        loadLeaves();
    });
    
    // Filters
    statusFilter.addEventListener("change", () => {
        currentStatus = statusFilter.value;
        currentPage = 1;
        loadLeaves();
    });
    
    typeFilter.addEventListener("change", () => {
        currentType = typeFilter.value;
        currentPage = 1;
        loadLeaves();
    });
    
    // Pagination
    prevPage.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            loadLeaves();
        }
    });
    
    nextPage.addEventListener("click", () => {
        if (currentPage < totalPages) {
            currentPage++;
            loadLeaves();
        }
    });
    
    // Request Leave
    requestBtn.addEventListener("click", () => {
        window.location.href = "/admin/leaves/add.html";
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
    
    // Reject Modal
    cancelReject.addEventListener("click", () => {
        rejectModal.style.display = "none";
        rejectReason.value = "";
        currentLeaveId = null;
    });
    
    confirmReject.addEventListener("click", async () => {
        if (!currentLeaveId) return;
        
        const reason = rejectReason.value.trim();
        if (!reason) {
            showError("Vui lòng nhập lý do từ chối");
            return;
        }
        
        try {
            const response = await fetch("/api/leaves.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    action: "reject",
                    id: currentLeaveId,
                    reason: reason
                })
            });
            
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            rejectModal.style.display = "none";
            rejectReason.value = "";
            currentLeaveId = null;
            loadLeaves();
        } catch (error) {
            showError("Lỗi từ chối đơn: " + error.message);
        }
    });
}

// Approve Leave
async function approveLeave(id) {
    if (!confirm("Bạn có chắc chắn muốn duyệt đơn nghỉ phép này?")) {
        return;
    }
    
    showLoading();
    try {
        const response = await fetch("/api/leaves.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                action: "approve",
                id: id
            })
        });
        
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        loadLeaves();
    } catch (error) {
        showError("Lỗi duyệt đơn: " + error.message);
    } finally {
        hideLoading();
    }
}

// Show Reject Modal
function showRejectModal(id) {
    currentLeaveId = id;
    rejectModal.style.display = "flex";
}

// View Leave
function viewLeave(id) {
    window.location.href = `/admin/leaves/view.html?id=${id}`;
}

// Utility Functions
function getLeaveTypeText(type) {
    const types = {
        "annual": "Nghỉ phép năm",
        "sick": "Nghỉ ốm",
        "unpaid": "Nghỉ không lương",
        "other": "Khác"
    };
    return types[type] || type;
}

function getStatusBadge(status) {
    const badges = {
        "pending": "<span class=\"badge badge-warning\">Chờ duyệt</span>",
        "approved": "<span class=\"badge badge-success\">Đã duyệt</span>",
        "rejected": "<span class=\"badge badge-danger\">Từ chối</span>"
    };
    return badges[status] || status;
}

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