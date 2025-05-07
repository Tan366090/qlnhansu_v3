// Permission utilities
export const checkPermission = async (permission) => {
    try {
        const response = await fetch('/QLNhanSu_version1/backend/src/api/auth/permissions.php', {
            method: 'GET',
            credentials: 'include'
        });
        const data = await response.json();
        return data.permissions.includes(permission);
    } catch (error) {
        console.error('Error checking permission:', error);
        return false;
    }
};

export const hasPermission = (permissions, requiredPermission) => {
    return permissions.includes(requiredPermission);
};

export const initPermissionCheck = async () => {
    try {
        const response = await fetch('/QLNhanSu_version1/backend/src/api/auth/permissions.php', {
            method: 'GET',
            credentials: 'include'
        });
        const data = await response.json();
        return data.permissions;
    } catch (error) {
        console.error('Error initializing permissions:', error);
        return [];
    }
}; 