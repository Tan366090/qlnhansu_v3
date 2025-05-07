import { authService } from "../services/authService.js";

// Kiểm tra quyền truy cập
export function checkPermission(requiredPermissions) {
    const user = authService.getUser();
    if (!user || !user.role) return false;
    
    // Admin có quyền truy cập tất cả
    if (user.role === "admin") return true;
    
    // Kiểm tra quyền của user
    const userPermissions = user.permissions || [];
    return requiredPermissions.every(permission => 
        userPermissions.includes(permission) || userPermissions.includes("*")
    );
}

// Lọc menu theo quyền
export function filterMenuByRole(menuItems) {
    const user = authService.getUser();
    if (!user || !user.role) return [];
    
    return menuItems.filter(item => {
        if (!item.requiredPermissions) return true;
        return checkPermission(item.requiredPermissions);
    });
}

// Kiểm tra quyền truy cập route
export function checkRoutePermission(route) {
    const user = authService.getUser();
    if (!user || !user.role) return false;
    
    // Admin có quyền truy cập tất cả routes
    if (user.role === "admin") return true;
    
    // Lấy danh sách routes được phép truy cập
    const allowedRoutes = getAllowedRoutes(user.role, user.permissions);
    return allowedRoutes.includes(route) || allowedRoutes.includes("*");
}

// Lấy danh sách routes được phép truy cập theo role và permissions
function getAllowedRoutes(role, permissions) {
    const baseRoutes = {
        admin: ["*"], // Tất cả routes
        manager: [
            "/dashboard",
            "/attendance",
            "/leaves",
            "/trainings",
            "/equipment",
            "/reports",
            "/salary/view"
        ],
        hr: [
            "/dashboard",
            "/users",
            "/attendance",
            "/salary",
            "/leaves",
            "/trainings",
            "/degrees",
            "/documents",
            "/reports",
            "/contracts"
        ],
        employee: [
            "/dashboard",
            "/profile",
            "/attendance",
            "/leaves",
            "/trainings",
            "/certificates",
            "/salary/view"
        ]
    };

    // Thêm các routes dựa trên permissions cụ thể
    const additionalRoutes = {
        "manage_users": ["/users/create", "/users/edit", "/users/delete"],
        "manage_salaries": ["/salary/edit", "/salary/approve"],
        "manage_attendance": ["/attendance/edit", "/attendance/approve"],
        "manage_leaves": ["/leaves/approve"],
        "manage_trainings": ["/trainings/create", "/trainings/edit"],
        "manage_equipment": ["/equipment/create", "/equipment/edit"],
        "manage_documents": ["/documents/create", "/documents/edit"],
        "manage_contracts": ["/contracts/create", "/contracts/edit"]
    };

    let routes = baseRoutes[role] || [];
    
    // Thêm các routes dựa trên permissions
    if (permissions) {
        permissions.forEach(permission => {
            if (additionalRoutes[permission]) {
                routes = [...routes, ...additionalRoutes[permission]];
            }
        });
    }

    return [...new Set(routes)]; // Loại bỏ các routes trùng lặp
}

// Kiểm tra quyền truy cập tài liệu
export function checkDocumentPermission(document, user) {
    if (!user || !user.role) return false;
    
    // Admin có quyền truy cập tất cả tài liệu
    if (user.role === "admin") return true;
    
    // Kiểm tra quyền truy cập dựa trên loại tài liệu
    if (document.access_level === "public") return true;
    if (document.created_by === user.id) return true;
    if (document.department_id === user.department_id) return true;
    
    // Kiểm tra quyền cụ thể
    if (user.permissions) {
        if (document.type === "salary" && user.permissions.includes("view_salaries")) return true;
        if (document.type === "contract" && user.permissions.includes("manage_contracts")) return true;
        if (document.type === "training" && user.permissions.includes("manage_trainings")) return true;
    }
    
    return false;
} 