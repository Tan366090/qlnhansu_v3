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
    // Cập nhật thời gian hiện tại
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);
    
    // Cập nhật ngày hiện tại
    updateCurrentDate();
    
    // Kiểm tra trạng thái chấm công hôm nay
    checkTodayStatus();
    
    // Tải lịch sử chấm công gần đây
    loadRecentRecords();
}

// Hàm thiết lập các sự kiện
function setupEvents() {
    // Sự kiện chấm công vào
    document.getElementById("checkInBtn").addEventListener("click", function() {
        checkIn();
    });
    
    // Sự kiện chấm công ra
    document.getElementById("checkOutBtn").addEventListener("click", function() {
        checkOut();
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

// Hàm cập nhật thời gian hiện tại
function updateCurrentTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString("vi-VN", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit"
    });
    document.getElementById("currentTime").textContent = timeString;
}

// Hàm cập nhật ngày hiện tại
function updateCurrentDate() {
    const now = new Date();
    const dateString = now.toLocaleDateString("vi-VN", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric"
    });
    document.getElementById("currentDate").textContent = dateString;
}

// Hàm kiểm tra trạng thái chấm công hôm nay
function checkTodayStatus() {
    showLoading();
    
    fetch("/QLNhanSu_version1/api/attendance.php?action=getByEmployee")
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                const today = new Date().toISOString().split("T")[0];
                const todayRecord = data.data.find(record => 
                    record.check_in_time.startsWith(today)
                );
                
                if (todayRecord) {
                    updateTodayRecord(todayRecord);
                }
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            hideLoading();
            console.error("Lỗi khi kiểm tra trạng thái:", error);
            showError("Lỗi khi kiểm tra trạng thái");
        });
}

// Hàm cập nhật bản ghi hôm nay
function updateTodayRecord(record) {
    document.getElementById("checkInTime").textContent = formatTime(record.check_in_time);
    document.getElementById("checkOutTime").textContent = record.check_out_time ? formatTime(record.check_out_time) : "-";
    document.getElementById("status").textContent = getStatusText(record.status);
    
    // Cập nhật trạng thái nút
    const checkInBtn = document.getElementById("checkInBtn");
    const checkOutBtn = document.getElementById("checkOutBtn");
    
    if (record.check_out_time) {
        checkInBtn.disabled = true;
        checkOutBtn.disabled = true;
    } else {
        checkInBtn.disabled = true;
        checkOutBtn.disabled = false;
    }
}

// Hàm chấm công vào
function checkIn() {
    showLoading();
    
    fetch("/QLNhanSu_version1/api/attendance.php?action=checkIn", {
        method: "POST"
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            checkTodayStatus();
            loadRecentRecords();
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        hideLoading();
        console.error("Lỗi khi chấm công vào:", error);
        showError("Lỗi khi chấm công vào");
    });
}

// Hàm chấm công ra
function checkOut() {
    showLoading();
    
    fetch("/QLNhanSu_version1/api/attendance.php?action=checkOut", {
        method: "POST"
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            checkTodayStatus();
            loadRecentRecords();
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        hideLoading();
        console.error("Lỗi khi chấm công ra:", error);
        showError("Lỗi khi chấm công ra");
    });
}

// Hàm tải lịch sử chấm công gần đây
function loadRecentRecords() {
    showLoading();
    
    fetch("/QLNhanSu_version1/api/attendance.php?action=getByEmployee")
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                renderRecentTable(data.data);
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            hideLoading();
            console.error("Lỗi khi tải lịch sử:", error);
            showError("Lỗi khi tải lịch sử");
        });
}

// Hàm hiển thị bảng lịch sử gần đây
function renderRecentTable(data) {
    const tbody = document.querySelector("#recentTable tbody");
    tbody.innerHTML = "";
    
    if (data.length === 0) {
        const tr = document.createElement("tr");
        tr.innerHTML = "<td colspan=\"4\" class=\"text-center\">Không có dữ liệu</td>";
        tbody.appendChild(tr);
        return;
    }
    
    // Lấy 5 bản ghi gần nhất
    const recentRecords = data.slice(0, 5);
    
    recentRecords.forEach(record => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${formatDate(record.check_in_time)}</td>
            <td>${formatTime(record.check_in_time)}</td>
            <td>${record.check_out_time ? formatTime(record.check_out_time) : "-"}</td>
            <td>
                <span class="status-badge status-${record.status}">
                    ${getStatusText(record.status)}
                </span>
            </td>
        `;
        tbody.appendChild(tr);
    });
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