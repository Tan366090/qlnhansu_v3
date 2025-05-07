// Danh sách các quyền trong hệ thống
export const PERMISSIONS = {
    // Quản lý nhân sự
    VIEW_EMPLOYEES: "view_employees",
    CREATE_EMPLOYEE: "create_employee",
    EDIT_EMPLOYEE: "edit_employee",
    DELETE_EMPLOYEE: "delete_employee",
    
    // Quản lý phòng ban
    VIEW_DEPARTMENTS: "view_departments",
    CREATE_DEPARTMENT: "create_department",
    EDIT_DEPARTMENT: "edit_department",
    DELETE_DEPARTMENT: "delete_department",
    
    // Quản lý chấm công
    VIEW_ATTENDANCE: "view_attendance",
    MANAGE_ATTENDANCE: "manage_attendance",
    
    // Quản lý lương
    VIEW_SALARY: "view_salary",
    MANAGE_SALARY: "manage_salary",
    
    // Báo cáo
    VIEW_REPORTS: "view_reports",
    EXPORT_REPORTS: "export_reports",
    
    // Cài đặt hệ thống
    MANAGE_SETTINGS: "manage_settings",
    MANAGE_ROLES: "manage_roles"
};

// Quyền mặc định cho từng role
export const ROLE_PERMISSIONS = {
    admin: Object.values(PERMISSIONS),
    manager: [
        PERMISSIONS.VIEW_EMPLOYEES,
        PERMISSIONS.EDIT_EMPLOYEE,
        PERMISSIONS.VIEW_DEPARTMENTS,
        PERMISSIONS.VIEW_ATTENDANCE,
        PERMISSIONS.MANAGE_ATTENDANCE,
        PERMISSIONS.VIEW_SALARY,
        PERMISSIONS.VIEW_REPORTS,
        PERMISSIONS.EXPORT_REPORTS
    ],
    employee: [
        PERMISSIONS.VIEW_EMPLOYEES,
        PERMISSIONS.VIEW_ATTENDANCE
    ]
};

// Hàm kiểm tra quyền
export const checkPermission = (requiredPermissions) => {
    return (req, res, next) => {
        if (!req.user) {
            return res.status(401).json({
                success: false,
                message: "Chưa xác thực người dùng"
            });
        }

        // Admin có quyền truy cập tất cả
        if (req.user.role === "admin") {
            return next();
        }

        // Kiểm tra quyền của người dùng
        const userPermissions = ROLE_PERMISSIONS[req.user.role] || [];
        const hasPermission = requiredPermissions.every(permission => 
            userPermissions.includes(permission)
        );

        if (!hasPermission) {
            return res.status(403).json({
                success: false,
                message: "Bạn không có quyền truy cập chức năng này"
            });
        }

        next();
    };
};

// Hàm kiểm tra quyền truy cập tài liệu
export const checkDocumentPermission = (document, user) => {
    // Admin có quyền truy cập tất cả
    if (user.role === "admin") {
        return true;
    }

    // Manager có quyền truy cập tài liệu của phòng ban mình quản lý
    if (user.role === "manager" && document.department_id === user.department_id) {
        return true;
    }

    // Employee chỉ có quyền truy cập tài liệu của mình
    if (user.role === "employee" && document.user_id === user.user_id) {
        return true;
    }

    return false;
}; 