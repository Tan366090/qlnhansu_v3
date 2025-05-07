const userDashboard = {
    menu: [
        {
            title: "Thông tin cá nhân",
            items: [
                {
                    name: "Cập nhật thông tin",
                    path: "/user/profile",
                    permission: "edit_profile",
                },
                {
                    name: "Đổi mật khẩu",
                    path: "/user/change-password",
                    permission: "change_password",
                },
            ],
        },
        {
            title: "Bằng cấp",
            items: [
                {
                    name: "Danh sách bằng cấp",
                    path: "/user/certificates",
                    permission: "view_certificates",
                },
                {
                    name: "Thêm bằng cấp",
                    path: "/user/certificates/add",
                    permission: "manage_certificates",
                },
            ],
        },
        {
            title: "Lương",
            items: [
                {
                    name: "Thông tin lương",
                    path: "/user/salary",
                    permission: "view_salary",
                },
                {
                    name: "Lịch sử tăng lương",
                    path: "/user/salary/history",
                    permission: "view_salary_history",
                },
            ],
        },
        {
            title: "Chấm công",
            items: [
                {
                    name: "Chấm công",
                    path: "/user/attendance",
                    permission: "manage_attendance",
                },
                {
                    name: "Lịch sử chấm công",
                    path: "/user/attendance/history",
                    permission: "view_attendance",
                },
            ],
        },
    ],
};

export default userDashboard;

document.addEventListener("DOMContentLoaded", async () => {
    try {
        // Load dashboard data
        await loadDashboardData();
        await loadRecentActivities();
    } catch (error) {
        console.error("Error initializing employee dashboard:", error);
    }
});

async function loadDashboardData() {
    try {
        // Load thông tin cá nhân
        const userInfo = await fetch("../api/user/profile");
        const userData = await userInfo.json();
        document.getElementById("employeeInfo").innerHTML = `
            <strong>Mã NV:</strong> ${userData.employee_code}<br>
            <strong>Họ tên:</strong> ${userData.full_name}<br>
            <strong>Phòng ban:</strong> ${userData.department}<br>
            <strong>Vị trí:</strong> ${userData.position}
        `;

        // Load thông tin chấm công
        const attendance = await fetch("../api/attendance/month");
        const attendanceData = await attendance.json();
        document.getElementById("attendanceInfo").innerHTML = `
            <strong>Đi làm:</strong> ${attendanceData.present} ngày<br>
            <strong>Nghỉ phép:</strong> ${attendanceData.leave} ngày<br>
            <strong>Đi muộn:</strong> ${attendanceData.late} lần<br>
            <strong>Về sớm:</strong> ${attendanceData.early} lần
        `;

        // Load thông tin nghỉ phép
        const leave = await fetch("../api/leave/balance");
        const leaveData = await leave.json();
        document.getElementById("leaveInfo").innerHTML = `
            <strong>Phép năm:</strong> ${leaveData.annual} ngày<br>
            <strong>Đã sử dụng:</strong> ${leaveData.used} ngày<br>
            <strong>Còn lại:</strong> ${leaveData.remaining} ngày
        `;

        // Load thông tin lương
        const salary = await fetch("../api/salary/current");
        const salaryData = await salary.json();
        document.getElementById("salaryInfo").innerHTML = `
            <strong>Lương cơ bản:</strong> ${formatCurrency(
                salaryData.base_salary
            )}<br>
            <strong>Phụ cấp:</strong> ${formatCurrency(
                salaryData.allowance
            )}<br>
            <strong>Khấu trừ:</strong> ${formatCurrency(
                salaryData.deduction
            )}<br>
            <strong>Thực lãnh:</strong> ${formatCurrency(salaryData.net_salary)}
        `;
    } catch (error) {
        console.error("Error loading dashboard data:", error);
        alert("Có lỗi xảy ra khi tải dữ liệu dashboard");
    }
}

async function loadRecentActivities() {
    try {
        const activities = await fetch("../api/user/recent-activities");
        const activitiesData = await activities.json();
        const activitiesList = activitiesData
            .map((activity) => `<li>${activity.description}</li>`)
            .join("");
        document.getElementById(
            "recentActivities"
        ).innerHTML = `<ul>${activitiesList}</ul>`;
    } catch (error) {
        console.error("Error loading recent activities:", error);
        alert("Có lỗi xảy ra khi tải dữ liệu hoạt động gần đây");
    }
}

function formatCurrency(amount) {
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(amount);
}
