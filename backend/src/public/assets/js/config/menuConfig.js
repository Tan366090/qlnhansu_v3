export const menuItems = [
    {
        id: "dashboard",
        title: "Dashboard",
        icon: "home",
        contentPath: "dashboard.html",
        dataPage: "dashboard"
    },
    {
        id: "employees",
        title: "Nhân viên",
        icon: "people",
        submenu: [
            {
                id: "employee-list",
                title: "Danh sách nhân viên",
                contentPath: "list_user.html",
                dataPage: "list_user",
                icon: "list"
            },
            {
                id: "employee-add",
                title: "Thêm nhân viên",
                contentPath: "add_user.html",
                dataPage: "add_user",
                icon: "person-add"
            },
            {
                id: "contracts",
                title: "Hợp đồng",
                icon: "document",
                submenu: [
                    {
                        id: "contract-list",
                        title: "Danh sách hợp đồng",
                        contentPath: "contract.html",
                        dataPage: "contract",
                        icon: "list"
                    },
                    {
                        id: "contract-add",
                        title: "Thêm hợp đồng",
                        contentPath: "contract.html",
                        dataPage: "contract",
                        icon: "add"
                    }
                ]
            }
        ]
    },
    {
        id: "departments",
        title: "Phòng ban",
        icon: "business",
        submenu: [
            {
                id: "department-list",
                title: "Danh sách phòng ban",
                contentPath: "department.html",
                dataPage: "department",
                icon: "list"
            },
            {
                id: "department-add",
                title: "Thêm phòng ban",
                contentPath: "department.html",
                dataPage: "department",
                icon: "add"
            },
            {
                id: "positions",
                title: "Chức vụ",
                icon: "briefcase",
                submenu: [
                    {
                        id: "position-list",
                        title: "Danh sách chức vụ",
                        contentPath: "position.html",
                        dataPage: "position",
                        icon: "list"
                    },
                    {
                        id: "position-add",
                        title: "Thêm chức vụ",
                        contentPath: "position.html",
                        dataPage: "position",
                        icon: "add"
                    }
                ]
            }
        ]
    },
    {
        id: "attendance",
        title: "Chấm công",
        icon: "time",
        submenu: [
            {
                id: "daily-attendance",
                title: "Chấm công hàng ngày",
                contentPath: "attendance.html",
                dataPage: "attendance",
                icon: "calendar"
            },
            {
                id: "monthly-report",
                title: "Báo cáo tháng",
                contentPath: "attendance_history.html",
                dataPage: "attendance_history",
                icon: "document"
            },
            {
                id: "leave",
                title: "Nghỉ phép",
                icon: "timeOff",
                submenu: [
                    {
                        id: "leave-list",
                        title: "Danh sách đơn nghỉ",
                        contentPath: "leave.html",
                        dataPage: "leave",
                        icon: "list"
                    },
                    {
                        id: "leave-approval",
                        title: "Duyệt đơn nghỉ",
                        contentPath: "leave.html",
                        dataPage: "leave",
                        icon: "checkmark"
                    }
                ]
            }
        ]
    },
    {
        id: "salary",
        title: "Lương thưởng",
        icon: "money",
        submenu: [
            {
                id: "salary-calculate",
                title: "Tính lương",
                contentPath: "salary.html",
                dataPage: "salary",
                icon: "calculator"
            },
            {
                id: "bonus",
                title: "Thưởng",
                contentPath: "bonus.html",
                dataPage: "bonus",
                icon: "gift"
            },
            {
                id: "salary-reports",
                title: "Báo cáo",
                icon: "document",
                submenu: [
                    {
                        id: "monthly-salary",
                        title: "Báo cáo tháng",
                        contentPath: "salary_history.html",
                        dataPage: "salary_history",
                        icon: "calendar"
                    },
                    {
                        id: "yearly-salary",
                        title: "Báo cáo năm",
                        contentPath: "salary_history.html",
                        dataPage: "salary_history",
                        icon: "calendar"
                    }
                ]
            }
        ]
    },
    {
        id: "reports",
        title: "Báo cáo",
        icon: "document",
        submenu: [
            {
                id: "hr-report",
                title: "Báo cáo nhân sự",
                contentPath: "report.html",
                dataPage: "report",
                icon: "people"
            },
            {
                id: "attendance-report",
                title: "Báo cáo chấm công",
                contentPath: "report.html",
                dataPage: "report",
                icon: "time"
            },
            {
                id: "salary-report",
                title: "Báo cáo lương",
                contentPath: "report.html",
                dataPage: "report",
                icon: "money"
            }
        ]
    },
    {
        id: "settings",
        title: "Cài đặt",
        icon: "settings",
        submenu: [
            {
                id: "system-settings",
                title: "Cài đặt hệ thống",
                contentPath: "system.html",
                dataPage: "system",
                icon: "settings"
            },
            {
                id: "user-management",
                title: "Quản lý người dùng",
                contentPath: "users.html",
                dataPage: "users",
                icon: "people"
            },
            {
                id: "role-management",
                title: "Phân quyền",
                contentPath: "roles.html",
                dataPage: "roles",
                icon: "shield"
            }
        ]
    }
];
