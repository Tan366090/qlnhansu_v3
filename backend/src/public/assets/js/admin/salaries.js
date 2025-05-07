// Biến toàn cục
let currentPage = 1;
let totalPages = 1;
let currentMonth = new Date().getMonth() + 1;
let currentYear = new Date().getFullYear();
let searchQuery = "";

// Khởi tạo trang
document.addEventListener("DOMContentLoaded", () => {
    checkAuth();
    initPage();
    setupEvents();
});

// Kiểm tra xác thực
async function checkAuth() {
    try {
        const response = await fetch("/QLNhanSu_version1/api/auth/check.php");
        const data = await response.json();
        
        if (!data.success) {
            window.location.href = "/QLNhanSu_version1/public/login.html";
            return;
        }

        document.getElementById("userFullName").textContent = data.user.full_name;
    } catch (error) {
        showError("Lỗi kiểm tra xác thực");
    }
}

// Khởi tạo trang
function initPage() {
    // Thiết lập giá trị mặc định cho bộ lọc
    document.getElementById("monthFilter").value = currentMonth;
    document.getElementById("yearFilter").value = currentYear;

    // Tải dữ liệu
    loadSalaryData();
    loadStatistics();
}

// Thiết lập sự kiện
function setupEvents() {
    // Sự kiện tìm kiếm
    const searchInput = document.getElementById("searchInput");
    const searchBtn = document.getElementById("searchBtn");
    
    searchInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            searchQuery = e.target.value;
            currentPage = 1;
            loadSalaryData();
        }
    });

    searchBtn.addEventListener("click", () => {
        searchQuery = searchInput.value;
        currentPage = 1;
        loadSalaryData();
    });

    // Sự kiện bộ lọc
    const filterBtn = document.getElementById("filterBtn");
    filterBtn.addEventListener("click", () => {
        currentMonth = document.getElementById("monthFilter").value;
        currentYear = document.getElementById("yearFilter").value;
        currentPage = 1;
        loadSalaryData();
        loadStatistics();
    });

    // Sự kiện phân trang
    document.getElementById("prevPage").addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            loadSalaryData();
        }
    });

    document.getElementById("nextPage").addEventListener("click", () => {
        if (currentPage < totalPages) {
            currentPage++;
            loadSalaryData();
        }
    });

    // Sự kiện tính lương
    document.getElementById("calculateBtn").addEventListener("click", calculateSalaries);

    // Sự kiện xuất Excel
    document.getElementById("exportBtn").addEventListener("click", exportToExcel);

    // Sự kiện đăng xuất
    document.getElementById("logoutBtn").addEventListener("click", logout);
}

// Tải dữ liệu lương
async function loadSalaryData() {
    showLoading();
    try {
        const response = await fetch(`/QLNhanSu_version1/api/salaries.php?action=getAll&page=${currentPage}&month=${currentMonth}&year=${currentYear}&search=${searchQuery}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        renderSalaryTable(data.data);
        updatePagination(data.total, data.per_page);
    } catch (error) {
        showError(error.message);
    } finally {
        hideLoading();
    }
}

// Tải thống kê
async function loadStatistics() {
    try {
        const response = await fetch(`/QLNhanSu_version1/api/salaries.php?action=getStatistics&month=${currentMonth}&year=${currentYear}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        updateStatistics(data.data);
    } catch (error) {
        showError(error.message);
    }
}

// Hiển thị bảng lương
function renderSalaryTable(data) {
    const tbody = document.querySelector("#salaryTable tbody");
    tbody.innerHTML = "";

    if (data.length === 0) {
        const tr = document.createElement("tr");
        tr.innerHTML = "<td colspan=\"11\" class=\"text-center\">Không có dữ liệu</td>";
        tbody.appendChild(tr);
        return;
    }

    data.forEach((item, index) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${(currentPage - 1) * 10 + index + 1}</td>
            <td>${item.employee_name}</td>
            <td>${formatCurrency(item.basic_salary)}</td>
            <td>${formatCurrency(item.allowance)}</td>
            <td>${formatCurrency(item.bonus)}</td>
            <td>${formatCurrency(item.insurance)}</td>
            <td>${formatCurrency(item.tax)}</td>
            <td>${formatCurrency(item.total_salary)}</td>
            <td>${item.month}/${item.year}</td>
            <td>${getStatusBadge(item.status)}</td>
            <td>
                <button class="btn-primary" onclick="viewSalaryDetail(${item.id})">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Cập nhật thống kê
function updateStatistics(data) {
    document.getElementById("totalSalary").textContent = formatCurrency(data.total_salary);
    document.getElementById("totalEmployees").textContent = data.total_employees;
    document.getElementById("totalTax").textContent = formatCurrency(data.total_tax);
    document.getElementById("totalInsurance").textContent = formatCurrency(data.total_insurance);
}

// Cập nhật phân trang
function updatePagination(total, perPage) {
    totalPages = Math.ceil(total / perPage);
    document.getElementById("pageInfo").textContent = `Trang ${currentPage} / ${totalPages}`;
    
    const prevBtn = document.getElementById("prevPage");
    const nextBtn = document.getElementById("nextPage");
    
    prevBtn.disabled = currentPage <= 1;
    nextBtn.disabled = currentPage >= totalPages;
}

// Tính lương
async function calculateSalaries() {
    showLoading();
    try {
        const response = await fetch("/QLNhanSu_version1/api/salaries.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                action: "calculate",
                month: currentMonth,
                year: currentYear
            })
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        loadSalaryData();
        loadStatistics();
    } catch (error) {
        showError(error.message);
    } finally {
        hideLoading();
    }
}

// Xuất Excel
async function exportToExcel() {
    showLoading();
    try {
        const response = await fetch(`/QLNhanSu_version1/api/salaries.php?action=export&month=${currentMonth}&year=${currentYear}`);
        
        if (!response.ok) {
            throw new Error("Lỗi xuất file");
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = `salary_${currentMonth}_${currentYear}.xlsx`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    } catch (error) {
        showError(error.message);
    } finally {
        hideLoading();
    }
}

// Xem chi tiết lương
async function viewSalaryDetail(id) {
    showLoading();
    try {
        const response = await fetch(`/QLNhanSu_version1/api/salaries.php?action=getById&id=${id}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        // TODO: Hiển thị modal chi tiết lương
        console.log(data.data);
    } catch (error) {
        showError(error.message);
    } finally {
        hideLoading();
    }
}

// Đăng xuất
async function logout() {
    try {
        const response = await fetch("/QLNhanSu_version1/api/auth/logout.php");
        const data = await response.json();

        if (data.success) {
            window.location.href = "/QLNhanSu_version1/public/login.html";
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError("Lỗi đăng xuất");
    }
}

// Hiển thị loading
function showLoading() {
    document.getElementById("loadingSpinner").style.display = "flex";
}

// Ẩn loading
function hideLoading() {
    document.getElementById("loadingSpinner").style.display = "none";
}

// Hiển thị lỗi
function showError(message) {
    const errorElement = document.getElementById("errorMessage");
    document.getElementById("errorText").textContent = message;
    errorElement.style.display = "flex";
    
    setTimeout(() => {
        errorElement.style.display = "none";
    }, 5000);
}

// Định dạng tiền tệ
function formatCurrency(amount) {
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND"
    }).format(amount);
}

// Lấy badge trạng thái
function getStatusBadge(status) {
    const badges = {
        "pending": "<span class=\"badge badge-warning\">Chờ duyệt</span>",
        "approved": "<span class=\"badge badge-success\">Đã duyệt</span>",
        "rejected": "<span class=\"badge badge-danger\">Từ chối</span>",
        "paid": "<span class=\"badge badge-info\">Đã thanh toán</span>"
    };
    return badges[status] || status;
} 