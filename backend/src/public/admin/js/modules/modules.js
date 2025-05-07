// Menu configuration
export const menuItems = [
    {
        title: "Quản lý người dùng",
        items: [
            { name: "Danh sách người dùng", path: "/admin/users", permission: "view_users" },
            { name: "Thêm người dùng", path: "/admin/users/add", permission: "create_users" },
            { name: "Phân quyền", path: "/admin/permissions", permission: "manage_permissions" }
        ]
    },
    {
        title: "Quản lý lương",
        items: [
            { name: "Danh sách lương", path: "/admin/salaries", permission: "view_salaries" },
            { name: "Tăng lương", path: "/admin/salaries/increase", permission: "manage_salaries" },
            { name: "Xuất bảng lương", path: "/admin/salaries/export", permission: "export_salaries" }
        ]
    },
    {
        title: "Quản lý chấm công",
        items: [
            { name: "Nhập chấm công", path: "/admin/attendance/input", permission: "manage_attendance" },
            { name: "Xuất bảng chấm công", path: "/admin/attendance/export", permission: "export_attendance" }
        ]
    },
    {
        title: "Quản lý thưởng",
        items: [
            { name: "Thêm thưởng", path: "/admin/bonuses/add", permission: "manage_bonuses" },
            { name: "Danh sách thưởng", path: "/admin/bonuses", permission: "view_bonuses" }
        ]
    },
    {
        title: "Báo cáo",
        items: [
            { name: "Lịch sử thay đổi", path: "/admin/history", permission: "view_history" },
            { name: "Lịch sử thao tác", path: "/admin/audit", permission: "view_audit" }
        ]
    }
];

// Auth utilities
export const AuthUtils = {
    initSessionMonitoring: function() {
        // Check session every 5 minutes
        setInterval(async () => {
            try {
                const response = await fetch("/QLNhanSu_version1/public/api/auth/check.php", {
                    method: "GET",
                    credentials: "include",
                    headers: {
                        "Accept": "application/json"
                    }
                });

                if (!response.ok) {
                    window.location.href = "/QLNhanSu_version1/public/login.html";
                }
            } catch (error) {
                console.error("Session check error:", error);
            }
        }, 300000);
    }
};

// Permission utilities
export const PermissionUtils = {
    checkPermission: function(permission) {
        const userPermissions = JSON.parse(localStorage.getItem("permissions") || "[]");
        return userPermissions.includes(permission);
    },

    applyPermissions: function() {
        const userPermissions = JSON.parse(localStorage.getItem("permissions") || "[]");
        document.querySelectorAll("[data-permission]").forEach(element => {
            const requiredPermission = element.getAttribute("data-permission");
            if (!userPermissions.includes(requiredPermission)) {
                element.style.display = "none";
            }
        });
    }
};

// Common utilities
export const CommonUtils = {
    formatDate: function(date) {
        return new Date(date).toLocaleDateString("vi-VN");
    },

    formatCurrency: function(amount) {
        return new Intl.NumberFormat("vi-VN", {
            style: "currency",
            currency: "VND"
        }).format(amount);
    },

    fetchData: async function(url, options = {}) {
        try {
            const response = await fetch(url, {
                ...options,
                credentials: "include",
                headers: {
                    "Accept": "application/json",
                    ...options.headers
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error("Error fetching data:", error);
            throw error;
        }
    }
};
