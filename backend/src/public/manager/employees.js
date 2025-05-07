document.addEventListener("DOMContentLoaded", async () => {
    try {
        // Gọi API để lấy danh sách nhân viên
        const response = await fetch("../api/manager/employees.php");
        const result = await response.json();

        if (result.success) {
            const employees = result.data;
            const tableBody = document.getElementById("employeeTableBody");

            // Xóa nội dung cũ
            tableBody.innerHTML = "";

            // Hiển thị danh sách nhân viên
            employees.forEach((employee, index) => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${employee.full_name}</td>
                    <td>${employee.email}</td>
                    <td>${employee.phone}</td>
                    <td>${employee.department}</td>
                    <td>${employee.position}</td>
                `;
                tableBody.appendChild(row);
            });
        } else {
            console.error("Lỗi khi lấy danh sách nhân viên:", result.message);
        }
    } catch (error) {
        console.error("Error loading employees:", error);
    }
});
