// API URL
const API_URL = "http://localhost/QLNhanSu_version1";

let currentDepartmentId = null;

// Hàm tải danh sách phòng ban
async function loadDepartments() {
    try {
        const response = await fetch(
            "http://localhost/QLNhanSu_version1/api/departments"
        );
        if (!response.ok)
            throw new Error(`HTTP error! status: ${response.status}`);
        const departments = await response.json();

        const tbody = document.getElementById("departmentList");
        tbody.innerHTML = "";

        departments.forEach((dept) => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${dept.code}</td>
                <td>${dept.name}</td>
                <td>${dept.description || ""}</td>
                <td>${dept.manager_name || "Chưa có"}</td>
                <td>${dept.employee_count || 0}</td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="editDepartment(${
                        dept.id
                    })">
                        <ion-icon name="create-outline"></ion-icon>
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="showDeleteModal(${
                        dept.id
                    })">
                        <ion-icon name="trash-outline"></ion-icon>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    } catch (error) {
        console.error("Lỗi khi tải danh sách phòng ban:", error);
        showToast("Có lỗi xảy ra khi tải danh sách phòng ban", "error");
    }
}

// Hàm tải danh sách nhân viên cho trưởng phòng
async function loadManagers() {
    try {
        const response = await fetch(`${API_URL}/api/employees`);
        const employees = await response.json();

        const select = document.getElementById("manager");
        select.innerHTML = "<option value=\"\">Chọn trưởng phòng</option>";

        employees.forEach((emp) => {
            const option = document.createElement("option");
            option.value = emp.id;
            option.textContent = `${emp.employee_id} - ${emp.full_name}`;
            select.appendChild(option);
        });
    } catch (error) {
        console.error("Lỗi khi tải danh sách nhân viên:", error);
        showToast("Có lỗi xảy ra khi tải danh sách nhân viên", "error");
    }
}

// Hàm hiển thị modal thêm/sửa
function showAddDepartmentModal() {
    currentDepartmentId = null;
    document.getElementById("modalTitle").textContent = "Thêm phòng ban";
    document.getElementById("departmentForm").reset();
    const modal = new bootstrap.Modal(
        document.getElementById("departmentModal")
    );
    modal.show();
}

// Hàm lưu phòng ban
async function saveDepartment() {
    const form = document.getElementById("departmentForm");
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const data = {
        code: document.getElementById("departmentCode").value,
        name: document.getElementById("departmentName").value,
        description: document.getElementById("description").value,
        manager_id: document.getElementById("manager").value,
    };

    try {
        const url = currentDepartmentId
            ? `${API_URL}/api/departments/${currentDepartmentId}`
            : `${API_URL}/api/departments`;
        const method = currentDepartmentId ? "PUT" : "POST";

        const response = await fetch(url, {
            method: method,
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(data),
        });

        if (response.ok) {
            showToast(
                currentDepartmentId
                    ? "Cập nhật phòng ban thành công"
                    : "Thêm phòng ban thành công",
                "success"
            );
            const modal = bootstrap.Modal.getInstance(
                document.getElementById("departmentModal")
            );
            modal.hide();
            loadDepartments();
        } else {
            throw new Error("Lưu phòng ban thất bại");
        }
    } catch (error) {
        console.error("Lỗi khi lưu phòng ban:", error);
        showToast("Có lỗi xảy ra khi lưu phòng ban", "error");
    }
}

// Hàm sửa phòng ban
async function editDepartment(id) {
    try {
        const response = await fetch(`${API_URL}/api/departments/${id}`);
        const department = await response.json();

        currentDepartmentId = id;
        document.getElementById("modalTitle").textContent = "Sửa phòng ban";
        document.getElementById("departmentCode").value = department.code;
        document.getElementById("departmentName").value = department.name;
        document.getElementById("description").value =
            department.description || "";
        document.getElementById("manager").value = department.manager_id || "";

        const modal = new bootstrap.Modal(
            document.getElementById("departmentModal")
        );
        modal.show();
    } catch (error) {
        console.error("Lỗi khi tải thông tin phòng ban:", error);
        showToast("Có lỗi xảy ra khi tải thông tin phòng ban", "error");
    }
}

// Hàm hiển thị modal xóa
function showDeleteModal(id) {
    currentDepartmentId = id;
    const modal = new bootstrap.Modal(document.getElementById("deleteModal"));
    modal.show();
}

// Hàm xác nhận xóa
async function confirmDelete() {
    if (!currentDepartmentId) return;

    try {
        const response = await fetch(
            `${API_URL}/api/departments/${currentDepartmentId}`,
            {
                method: "DELETE",
            }
        );

        if (response.ok) {
            showToast("Xóa phòng ban thành công", "success");
            const modal = bootstrap.Modal.getInstance(
                document.getElementById("deleteModal")
            );
            modal.hide();
            loadDepartments();
        } else {
            throw new Error("Xóa phòng ban thất bại");
        }
    } catch (error) {
        console.error("Lỗi khi xóa phòng ban:", error);
        showToast("Có lỗi xảy ra khi xóa phòng ban", "error");
    }
}

// Show toast message
function showToast(message, type = "success") {
    const toast = document.createElement("div");
    toast.className = `toast align-items-center text-white bg-${
        type === "success" ? "success" : "danger"
    } border-0`;
    toast.setAttribute("role", "alert");
    toast.setAttribute("aria-live", "assertive");
    toast.setAttribute("aria-atomic", "true");

    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    const toastContainer = document.createElement("div");
    toastContainer.className =
        "toast-container position-fixed bottom-0 end-0 p-3";
    toastContainer.appendChild(toast);
    document.body.appendChild(toastContainer);

    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();

    // Remove toast after it's hidden
    toast.addEventListener("hidden.bs.toast", () => {
        toastContainer.remove();
    });
}

// Tải dữ liệu khi trang được tải
document.addEventListener("DOMContentLoaded", () => {
    loadDepartments();
    loadManagers();
});
