export const menuConfig = {
    admin: {
        dashboard: {
            path: "/admin/dashboard.html",
            icon: "fas fa-tachometer-alt",
            label: "Dashboard"
        },
        employees: {
            path: "/admin/employees/list.html",
            icon: "fas fa-users",
            label: "Nhân viên",
            submenu: {
                list: {
                    path: "/admin/employees/list.html",
                    icon: "fas fa-list",
                    label: "Danh sách nhân viên"
                },
                add: {
                    path: "/admin/employees/add.html",
                    icon: "fas fa-plus",
                    label: "Thêm nhân viên"
                },
                edit: {
                    path: "/admin/employees/edit.html",
                    icon: "fas fa-edit",
                    label: "Chỉnh sửa hồ sơ"
                }
            }
        },
        attendance: {
            path: "/admin/attendance/history.html",
            icon: "fas fa-clock",
            label: "Chấm công",
            submenu: {
                history: {
                    path: "/admin/attendance/history.html",
                    icon: "fas fa-history",
                    label: "Lịch sử chấm công"
                },
                check: {
                    path: "/admin/attendance/check.html",
                    icon: "fas fa-check",
                    label: "Chấm công"
                }
            }
        }
    }
}; 