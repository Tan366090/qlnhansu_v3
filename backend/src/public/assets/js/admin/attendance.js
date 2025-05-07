document.addEventListener("DOMContentLoaded", function() {
    // Kiểm tra đăng nhập
    checkAuth();
    
    // Khởi tạo trang
    initPage();
    
    // Thiết lập các sự kiện
    setupEvents();
});

// Hàm kiểm tra đăng nhập
function checkAuth() {
    fetch("/QLNhanSu_version1/api/auth.php?action=check")
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                window.location.href = "/QLNhanSu_version1/public/login.html";
            } else {
                document.getElementById("userFullName").textContent = data.user.full_name;
            }
        })
        .catch(error => {
            console.error("Lỗi khi kiểm tra đăng nhập:", error);
            window.location.href = "/QLNhanSu_version1/public/login.html";
        });
}

// Hàm khởi tạo trang
function initPage() {
    // Lấy tháng và năm hiện tại
    const currentDate = new Date();
    const currentMonth = currentDate.getMonth() + 1;
    const currentYear = currentDate.getFullYear();
    
    // Thiết lập giá trị mặc định cho bộ lọc
    document.getElementById("monthFilter").value = currentMonth;
    document.getElementById("yearFilter").value = currentYear;
    
    // Tải dữ liệu
    loadAttendanceData();
    loadStatistics();
}

// Hàm thiết lập các sự kiện
function setupEvents() {
    // Sự kiện tìm kiếm
    const searchInput = document.getElementById("searchInput");
    const searchBtn = document.getElementById("searchBtn");
    
    let searchTimeout;
    searchInput.addEventListener("input", function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadAttendanceData();
        }, 500);
    });
    
    searchBtn.addEventListener("click", function() {
        loadAttendanceData();
    });
    
    // Sự kiện lọc
    document.getElementById("filterBtn").addEventListener("click", function() {
        loadAttendanceData();
        loadStatistics();
    });
    
    // Sự kiện phân trang
    document.getElementById("prevPage").addEventListener("click", function() {
        currentPage--;
        loadAttendanceData();
    });
    
    document.getElementById("nextPage").addEventListener("click", function() {
        currentPage++;
        loadAttendanceData();
    });
    
    // Sự kiện đăng xuất
    document.getElementById("logoutBtn").addEventListener("click", function() {
        fetch("/QLNhanSu_version1/api/auth.php?action=logout")
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = "/QLNhanSu_version1/public/login.html";
                }
            })
            .catch(error => {
                console.error("Lỗi khi đăng xuất:", error);
                showError("Lỗi khi đăng xuất");
            });
    });
}

// Biến lưu trữ dữ liệu phân trang
let currentPage = 1;
const itemsPerPage = 10;

// Hàm tải dữ liệu chấm công
function loadAttendanceData() {
    showLoading();
    
    const searchQuery = document.getElementById("searchInput").value;
    const month = document.getElementById("monthFilter").value;
    const year = document.getElementById("yearFilter").value;
    
    let url = `/QLNhanSu_version1/api/attendance.php?action=getAll&page=${currentPage}&limit=${itemsPerPage}`;
    
    if (searchQuery) {
        url += `&search=${encodeURIComponent(searchQuery)}`;
    }
    
    if (month) {
        url += `&month=${month}`;
    }
    
    if (year) {
        url += `&year=${year}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                renderAttendanceTable(data.data);
                updatePagination(data.total);
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            hideLoading();
            console.error("Lỗi khi tải dữ liệu chấm công:", error);
            showError("Lỗi khi tải dữ liệu chấm công");
        });
}

// Hàm tải thống kê
function loadStatistics() {
    const month = document.getElementById("monthFilter").value;
    const year = document.getElementById("yearFilter").value;
    
    let url = "/QLNhanSu_version1/api/attendance.php?action=getStatistics";
    
    if (month) {
        url += `&month=${month}`;
    }
    
    if (year) {
        url += `&year=${year}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStatistics(data.data);
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            console.error("Lỗi khi tải thống kê:", error);
            showError("Lỗi khi tải thống kê");
        });
}

// Hàm hiển thị bảng chấm công
function renderAttendanceTable(data) {
    const tbody = document.querySelector("#attendanceTable tbody");
    tbody.innerHTML = "";
    
    if (data.length === 0) {
        const tr = document.createElement("tr");
        tr.innerHTML = "<td colspan=\"7\" class=\"text-center\">Không có dữ liệu</td>";
        tbody.appendChild(tr);
        return;
    }
    
    data.forEach((item, index) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${(currentPage - 1) * itemsPerPage + index + 1}</td>
            <td>${item.full_name}</td>
            <td>${formatDate(item.check_in_time)}</td>
            <td>${formatTime(item.check_in_time)}</td>
            <td>${item.check_out_time ? formatTime(item.check_out_time) : "-"}</td>
            <td>
                <span class="status-badge status-${item.status}">
                    ${getStatusText(item.status)}
                </span>
            </td>
            <td>${item.notes || "-"}</td>
        `;
        tbody.appendChild(tr);
    });
}

// Hàm cập nhật thống kê
function updateStatistics(data) {
    document.getElementById("totalDays").textContent = data.total_days || 0;
    document.getElementById("presentDays").textContent = data.present_days || 0;
    document.getElementById("absentDays").textContent = data.absent_days || 0;
    document.getElementById("lateDays").textContent = data.late_days || 0;
}

// Hàm cập nhật phân trang
function updatePagination(total) {
    const totalPages = Math.ceil(total / itemsPerPage);
    
    document.getElementById("prevPage").disabled = currentPage === 1;
    document.getElementById("nextPage").disabled = currentPage === totalPages;
    
    document.getElementById("pageInfo").textContent = `Trang ${currentPage} / ${totalPages}`;
}

// Hàm hiển thị loading
function showLoading() {
    document.getElementById("loadingSpinner").style.display = "flex";
}

// Hàm ẩn loading
function hideLoading() {
    document.getElementById("loadingSpinner").style.display = "none";
}

// Hàm hiển thị lỗi
function showError(message) {
    const errorMessage = document.getElementById("errorMessage");
    const errorText = document.getElementById("errorText");
    
    errorText.textContent = message;
    errorMessage.classList.add("show");
    
    setTimeout(() => {
        errorMessage.classList.remove("show");
    }, 3000);
}

// Hàm định dạng ngày
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString("vi-VN");
}

// Hàm định dạng giờ
function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString("vi-VN", { hour: "2-digit", minute: "2-digit" });
}

// Hàm lấy text trạng thái
function getStatusText(status) {
    switch (status) {
        case "present":
            return "Đi làm";
        case "absent":
            return "Nghỉ";
        case "late":
            return "Đi muộn";
        default:
            return status;
    }
} 