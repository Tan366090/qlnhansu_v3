// Khởi tạo biểu đồ
let employeeChart = null;

// Hàm lấy dữ liệu từ API
async function fetchEmployeeData() {
    try {
        const response = await fetch("/api/employees/report", {
            method: "GET",
            headers: {
                "Content-Type": "application/json"
            }
        });
        
        if (!response.ok) {
            throw new Error("Lỗi khi lấy dữ liệu");
        }
        
        return await response.json();
    } catch (error) {
        console.error("Lỗi:", error);
        alert("Có lỗi xảy ra khi lấy dữ liệu");
    }
}

// Hàm tạo biểu đồ thống kê nhân viên theo phòng ban
function createDepartmentChart(data) {
    const ctx = document.getElementById("employeeChart").getContext("2d");
    
    if (employeeChart) {
        employeeChart.destroy();
    }
    
    employeeChart = new Chart(ctx, {
        type: "pie",
        data: {
            labels: data.departments.map(dept => dept.name),
            datasets: [{
                data: data.departments.map(dept => dept.count),
                backgroundColor: [
                    "#FF6384",
                    "#36A2EB",
                    "#FFCE56",
                    "#4BC0C0",
                    "#9966FF",
                    "#FF9F40"
                ]
            }]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: "Thống kê nhân viên theo phòng ban"
            }
        }
    });
}

// Hàm hiển thị danh sách nhân viên
function displayEmployeeList(employees) {
    const tbody = document.getElementById("employeeList");
    tbody.innerHTML = "";
    
    employees.forEach((employee, index) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${index + 1}</td>
            <td>${employee.employee_id}</td>
            <td>${employee.full_name}</td>
            <td>${employee.department_name}</td>
            <td>${employee.position_name}</td>
            <td>${new Date(employee.hire_date).toLocaleDateString("vi-VN")}</td>
            <td>${employee.status === "active" ? "Đang làm việc" : "Đã nghỉ việc"}</td>
        `;
        tbody.appendChild(tr);
    });
}

// Hàm lấy danh sách phòng ban
async function loadDepartments() {
    try {
        const response = await fetch("/api/departments");
        const departments = await response.json();
        
        const select = document.getElementById("department");
        departments.forEach(dept => {
            const option = document.createElement("option");
            option.value = dept.id;
            option.textContent = dept.name;
            select.appendChild(option);
        });
    } catch (error) {
        console.error("Lỗi khi lấy danh sách phòng ban:", error);
    }
}

// Hàm lấy danh sách chức vụ
async function loadPositions() {
    try {
        const response = await fetch("/api/positions");
        const positions = await response.json();
        
        const select = document.getElementById("position");
        positions.forEach(pos => {
            const option = document.createElement("option");
            option.value = pos.id;
            option.textContent = pos.name;
            select.appendChild(option);
        });
    } catch (error) {
        console.error("Lỗi khi lấy danh sách chức vụ:", error);
    }
}

// Hàm tạo báo cáo
async function generateReport() {
    const departmentId = document.getElementById("department").value;
    const positionId = document.getElementById("position").value;
    const status = document.getElementById("status").value;
    
    try {
        const response = await fetch("/api/employees/report", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                department_id: departmentId,
                position_id: positionId,
                status: status
            })
        });
        
        if (!response.ok) {
            throw new Error("Lỗi khi tạo báo cáo");
        }
        
        const data = await response.json();
        createDepartmentChart(data);
        displayEmployeeList(data.employees);
    } catch (error) {
        console.error("Lỗi:", error);
        alert("Có lỗi xảy ra khi tạo báo cáo");
    }
}

// Khởi tạo trang
document.addEventListener("DOMContentLoaded", async () => {
    await loadDepartments();
    await loadPositions();
    
    // Lấy dữ liệu ban đầu
    const initialData = await fetchEmployeeData();
    if (initialData) {
        createDepartmentChart(initialData);
        displayEmployeeList(initialData.employees);
    }
    
    // Xử lý sự kiện nút tạo báo cáo
    document.getElementById("generate-report").addEventListener("click", generateReport);
}); 