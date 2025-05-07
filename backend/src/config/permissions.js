const permissions = {
    admin: [
        "*" // Quyền truy cập tất cả
    ],
    manager: [
        // Quản lý người dùng
        "view_users",
        // Chấm công
        "view_attendance",
        "manage_attendance",
        // Nghỉ phép
        "view_leaves",
        "manage_leaves",
        "approve_leaves",
        // Đào tạo
        "view_trainings",
        "manage_trainings",
        // Thiết bị
        "view_equipment",
        "manage_equipment",
        // Báo cáo
        "view_reports",
        // Lương
        "view_salaries"
    ],
    hr: [
        // Quản lý người dùng
        "manage_users",
        "view_users",
        "create_users",
        "edit_users",
        "delete_users",
        // Chấm công
        "view_attendance",
        "manage_attendance",
        // Lương
        "view_salaries",
        "manage_salaries",
        // Nghỉ phép
        "view_leaves",
        "manage_leaves",
        // Đào tạo
        "view_trainings",
        "manage_trainings",
        // Bằng cấp
        "view_certificates",
        "manage_certificates",
        // Tài liệu
        "view_documents",
        "manage_documents",
        // Báo cáo
        "view_reports",
        // Hợp đồng
        "view_contracts",
        "manage_contracts"
    ],
    employee: [
        // Thông tin cá nhân
        "view_profile",
        "edit_profile",
        "change_password",
        // Bằng cấp
        "view_certificates",
        "manage_certificates",
        // Lương
        "view_salary",
        "view_salary_history",
        // Chấm công
        "manage_attendance",
        "view_attendance",
        // Nghỉ phép
        "view_leaves",
        "request_leaves",
        // Đào tạo
        "view_trainings"
    ]
};

export default permissions; 