import { AuthUtils } from "./auth_utils.js";
import { menuItems } from "./config/menuConfig.js";

const BASE_URL = "/QLNhanSu_version1";

// Mock data for development
const MOCK_DATA = {
    dashboard: {
        success: true,
        data: {
            totalEmployees: 150,
            pendingLeaves: 5,
            todayAttendance: "85%",
            totalSalary: 890500000,
        },
    },
    employees: {
        success: true,
        data: [
            {
                employee_id: "NV001",
                name: "Nguyễn Văn A",
                position: "Developer",
                join_date: "2024-01-15",
            },
            {
                employee_id: "NV002",
                name: "Trần Thị B",
                position: "Designer",
                join_date: "2024-02-01",
            },
            {
                employee_id: "NV003",
                name: "Lê Văn C",
                position: "Manager",
                join_date: "2024-03-10",
            },
        ],
    },
    activities: {
        success: true,
        data: [
            {
                type: "attendance",
                title: "Check-in",
                description: "Nguyễn Văn A đã check-in",
                timestamp: new Date(Date.now() - 3600000).toISOString(),
            },
            {
                type: "leave",
                title: "Đơn xin nghỉ phép",
                description: "Trần Thị B đã gửi đơn xin nghỉ phép",
                timestamp: new Date(Date.now() - 7200000).toISOString(),
            },
            {
                type: "profile",
                title: "Cập nhật hồ sơ",
                description: "Lê Văn C đã cập nhật thông tin cá nhân",
                timestamp: new Date(Date.now() - 10800000).toISOString(),
            },
        ],
    },
};

// Kiểm tra đăng nhập và role
if (!AuthUtils.isLoggedIn()) {
    window.location.href = `${BASE_URL}/login.html`;
} else {
    try {
        // Load dashboard data
        loadDashboardData();
    } catch (error) {
        console.error("Error loading dashboard:", error);
        alert("Có lỗi xảy ra khi tải dữ liệu dashboard");
    }
}

document.addEventListener("DOMContentLoaded", async () => {
    const mainContent = document.querySelector(".main-content");
    const navLinks = document.querySelectorAll(".nav-link");

    // Function to load content dynamically into the main-content area
    async function loadContent(url) {
        try {
            const response = await fetch(url);
            if (!response.ok)
                throw new Error(`Failed to load content from ${url}`);
            const content = await response.text();
            mainContent.innerHTML = content;
        } catch (error) {
            console.error("Error loading content:", error);
            mainContent.innerHTML = `<div class="error">Error loading content: ${error.message}</div>`;
        }
    }

    // Function to initialize the dashboard data
    async function initializeDashboard() {
        try {
            const response = await fetch("../api/manager/dashboard_data.php");
            const result = await response.json();

            if (result.success) {
                const data = result.data;

                // Update dashboard cards
                document.getElementById("employeeCount").textContent =
                    data.totalEmployees;
                document.getElementById("pendingLeaves").textContent =
                    data.pendingLeaves;
                document.getElementById("todayAttendance").textContent =
                    data.todayAttendance;
                document.getElementById("totalSalary").textContent =
                    formatCurrency(data.totalSalary);
            } else {
                console.error("Error fetching dashboard data:", result.message);
            }
        } catch (error) {
            console.error("Error initializing dashboard:", error);
        }
    }

    // Function to format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat("vi-VN", {
            style: "currency",
            currency: "VND",
        }).format(amount);
    }

    // Add click event listeners to menu links
    navLinks.forEach((link) => {
        link.addEventListener("click", function (event) {
            event.preventDefault(); // Prevent default link behavior
            const url = this.getAttribute("href"); // Get the target URL
            loadContent(url); // Load the content dynamically

            // Highlight the active link
            navLinks.forEach((link) => link.classList.remove("active"));
            this.classList.add("active");
        });
    });

    // Load default content (e.g., dashboard.html) on page load
    loadContent("dashboard.html");

    // Initialize dashboard data
    await initializeDashboard();
});

// Constants
const API_ENDPOINTS = {
    CHECK_IN: "/api/attendance/check-in",
    CHECK_OUT: "/api/attendance/check-out",
    GET_USER_INFO: "/api/users/me",
    GET_ATTENDANCE: "/api/attendance/today",
    GET_LEAVE_BALANCE: "/api/leave/balance",
    GET_RECENT_ACTIVITIES: "/api/activities/recent"
};

// DOM Elements
const userNameElement = document.getElementById("userName");
const userRoleElement = document.getElementById("userRole");
const checkInBtn = document.getElementById("checkInBtn");
const checkOutBtn = document.getElementById("checkOutBtn");
const attendanceStatus = document.getElementById("attendanceStatus");
const leaveBalance = document.getElementById("leaveBalance");
const activityList = document.getElementById("activityList");
const logoutBtn = document.getElementById("logoutBtn");

// Check if user is authenticated
function checkAuth() {
    const token = localStorage.getItem("token");
    if (!token) {
        window.location.href = "/login.html";
        return false;
    }
    return true;
}

// API Calls with error handling
async function apiCall(endpoint, method = "GET", body = null) {
    try {
        const token = localStorage.getItem("token");
        const response = await fetch(endpoint, {
            method,
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${token}`
            },
            body: body ? JSON.stringify(body) : null
        });

        if (!response.ok) {
            if (response.status === 401) {
                localStorage.removeItem("token");
                window.location.href = "/login.html";
                return null;
            }
            throw new Error(`API call failed: ${response.statusText}`);
        }

        return await response.json();
    } catch (error) {
        console.error("API Error:", error);
        showNotification(error.message, "error");
        return null;
    }
}

// Load user information
async function loadUserInfo() {
    const userData = await apiCall(API_ENDPOINTS.GET_USER_INFO);
    if (userData) {
        userNameElement.textContent = userData.name;
        userRoleElement.textContent = userData.role;
        document.getElementById("userAvatar").textContent = userData.name.charAt(0);
    }
}

// Handle attendance
async function handleAttendance(type) {
    const endpoint = type === "in" ? API_ENDPOINTS.CHECK_IN : API_ENDPOINTS.CHECK_OUT;
    const response = await apiCall(endpoint, "POST");
    
    if (response) {
        showNotification(`Successfully checked ${type}!`, "success");
        updateAttendanceStatus();
    }
}

// Update attendance status
async function updateAttendanceStatus() {
    const attendance = await apiCall(API_ENDPOINTS.GET_ATTENDANCE);
    if (attendance) {
        const { checkedIn, checkedOut, checkInTime, checkOutTime } = attendance;
        
        checkInBtn.disabled = checkedIn;
        checkOutBtn.disabled = !checkedIn || checkedOut;
        
        let status = "Not checked in";
        if (checkedIn && !checkedOut) {
            status = `Checked in at ${formatTime(checkInTime)}`;
        } else if (checkedIn && checkedOut) {
            status = `Checked out at ${formatTime(checkOutTime)}`;
        }
        
        attendanceStatus.textContent = status;
    }
}

// Update leave balance
async function updateLeaveBalance() {
    const balance = await apiCall(API_ENDPOINTS.GET_LEAVE_BALANCE);
    if (balance) {
        leaveBalance.textContent = balance.days;
    }
}

// Load recent activities
async function loadRecentActivities() {
    const activities = await apiCall(API_ENDPOINTS.GET_RECENT_ACTIVITIES);
    if (activities && activities.length > 0) {
        activityList.innerHTML = activities.map(activity => `
            <div class="activity-item">
                <div class="activity-content">
                    <strong>${activity.type}</strong>
                    <p>${activity.description}</p>
                    <small>${formatDate(activity.timestamp)}</small>
                </div>
            </div>
        `).join("");
    } else {
        activityList.innerHTML = "<p>No recent activities</p>";
    }
}

// Utility functions
function formatTime(timestamp) {
    return new Date(timestamp).toLocaleTimeString("vi-VN", {
        hour: "2-digit",
        minute: "2-digit"
    });
}

function formatDate(timestamp) {
    return new Date(timestamp).toLocaleDateString("vi-VN", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit"
    });
}

function showNotification(message, type = "info") {
    // You can implement this using a toast library or custom notification
    alert(message);
}

// Event Listeners
document.addEventListener("DOMContentLoaded", async () => {
    if (!checkAuth()) return;
    
    await Promise.all([
        loadUserInfo(),
        updateAttendanceStatus(),
        updateLeaveBalance(),
        loadRecentActivities()
    ]);
});

checkInBtn?.addEventListener("click", () => handleAttendance("in"));
checkOutBtn?.addEventListener("click", () => handleAttendance("out"));

logoutBtn?.addEventListener("click", () => {
    localStorage.removeItem("token");
    window.location.href = "/login.html";
});

// Auto refresh data every 5 minutes
setInterval(async () => {
    await Promise.all([
        updateAttendanceStatus(),
        loadRecentActivities()
    ]);
}, 5 * 60 * 1000);

// Initialize dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize other dashboard features
    initializeCharts();
    initializeNotifications();
    initializeThemeToggle();
    initializeResponsiveMenu();
});

// Function to initialize responsive menu
function initializeResponsiveMenu() {
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');

    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            if (overlay) {
                overlay.classList.toggle('active');
            }
        });

        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });
        }
    }
}
