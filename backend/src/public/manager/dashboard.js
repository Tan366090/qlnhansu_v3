import { Auth } from "../assets/js/auth.js";
import { loadContent } from "../assets/js/common.js";

document.addEventListener("DOMContentLoaded", async () => {
    loadContent("dashboard.html");

    if (!Auth.isLoggedIn()) {
        window.location.href = "../login.html";
        return;
    }

    try {
        // Gọi API để lấy dữ liệu dashboard
        const response = await fetch("../api/manager/dashboard_data.php");
        const result = await response.json();

        if (result.success) {
            const data = result.data;

            // Hiển thị dữ liệu trên giao diện
            document.getElementById("employeeCount").textContent =
                data.totalEmployees;
            document.getElementById("pendingLeaves").textContent =
                data.pendingLeaves;
            document.getElementById("todayAttendance").textContent =
                data.todayAttendance;
            document.getElementById("totalSalary").textContent = formatCurrency(
                data.totalSalary
            );
        } else {
            console.error("Lỗi khi lấy dữ liệu:", result.message);
        }
    } catch (error) {
        console.error("Error loading dashboard data:", error);
    }
});

function formatCurrency(amount) {
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(amount);
}
