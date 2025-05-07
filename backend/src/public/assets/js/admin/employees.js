document.addEventListener("DOMContentLoaded", function() {
    // Kiểm tra đăng nhập
    checkAuth();

    // Khởi tạo các biến
    let currentPage = 1;
    const itemsPerPage = 10;
    let allEmployees = [];
    let filteredEmployees = [];

    // Load dữ liệu ban đầu
    loadEmployees();

    // Xử lý tìm kiếm
    const searchInput = document.getElementById("searchInput");
    searchInput.addEventListener("input", debounce(handleSearch, 300));

    // Xử lý phân trang
    document.getElementById("prevPage").addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            renderTable();
        }
    });

    document.getElementById("nextPage").addEventListener("click", () => {
        const totalPages = Math.ceil(filteredEmployees.length / itemsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            renderTable();
        }
    });

    // Hàm load dữ liệu nhân viên
    async function loadEmployees() {
        try {
            const response = await fetch("/QLNhanSu_version1/api/employees.php?action=getAll");
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            const data = await response.json();
            
            if (data.success) {
                allEmployees = data.data;
                filteredEmployees = [...allEmployees];
                renderTable();
            } else {
                showError("Không thể tải dữ liệu nhân viên: " + data.message);
            }
        } catch (error) {
            showError("Lỗi khi tải dữ liệu: " + error.message);
        }
    }

    // Hàm render bảng dữ liệu
    function renderTable() {
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const currentEmployees = filteredEmployees.slice(startIndex, endIndex);
        
        const tbody = document.getElementById("employeeTableBody");
        tbody.innerHTML = "";

        currentEmployees.forEach(employee => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${employee.id}</td>
                <td>${employee.full_name}</td>
                <td>${employee.department_name || "Chưa phân phòng"}</td>
                <td>${employee.position_name || "Chưa phân chức vụ"}</td>
                <td>${employee.email}</td>
                <td>${employee.phone || "Chưa cập nhật"}</td>
                <td>
                    <span class="status-badge ${employee.status === "active" ? "active" : "inactive"}">
                        ${employee.status === "active" ? "Đang làm việc" : "Đã nghỉ việc"}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-view" onclick="viewEmployee(${employee.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-edit" onclick="editEmployee(${employee.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-delete" onclick="deleteEmployee(${employee.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Cập nhật thông tin phân trang
        const totalPages = Math.ceil(filteredEmployees.length / itemsPerPage);
        document.getElementById("pageInfo").textContent = `Trang ${currentPage} / ${totalPages}`;
    }

    // Hàm xử lý tìm kiếm
    function handleSearch() {
        const searchTerm = searchInput.value.toLowerCase();
        filteredEmployees = allEmployees.filter(employee => 
            employee.full_name.toLowerCase().includes(searchTerm) ||
            employee.email.toLowerCase().includes(searchTerm) ||
            (employee.phone && employee.phone.includes(searchTerm)) ||
            (employee.department_name && employee.department_name.toLowerCase().includes(searchTerm)) ||
            (employee.position_name && employee.position_name.toLowerCase().includes(searchTerm))
        );
        currentPage = 1;
        renderTable();
    }

    // Hàm debounce để giảm số lần gọi API khi tìm kiếm
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Hàm hiển thị thông báo lỗi
    function showError(message) {
        // Tạo và hiển thị thông báo lỗi
        const errorDiv = document.createElement("div");
        errorDiv.className = "error-message";
        errorDiv.textContent = message;
        document.querySelector(".content").prepend(errorDiv);
        setTimeout(() => errorDiv.remove(), 5000);
    }
});

// Hàm xem chi tiết nhân viên
function viewEmployee(id) {
    window.location.href = `view.html?id=${id}`;
}

// Hàm sửa thông tin nhân viên
function editEmployee(id) {
    window.location.href = `edit.html?id=${id}`;
}

// Hàm xóa nhân viên
async function deleteEmployee(id) {
    if (confirm("Bạn có chắc chắn muốn xóa nhân viên này?")) {
        try {
            const response = await fetch(`/QLNhanSu_version1/api/employees.php?action=delete&id=${id}`, {
                method: "DELETE"
            });
            const data = await response.json();
            
            if (data.success) {
                alert("Xóa nhân viên thành công!");
                location.reload();
            } else {
                alert("Lỗi khi xóa nhân viên: " + data.message);
            }
        } catch (error) {
            alert("Lỗi khi xóa nhân viên: " + error.message);
        }
    }
} 