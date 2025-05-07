// Session utilities
const SessionUtils = {
    checkSession: async () => {
        try {
            const response = await fetch('/QLNhanSu_version1/public/check_session.php', {
                method: 'GET',
                credentials: 'include'
            });
            
            const data = await response.json();
            
            if (!data.authenticated) {
                window.location.href = data.redirectUrl;
                return null;
            }
            
            return data.user;
        } catch (error) {
            console.error('Lỗi kiểm tra session:', error);
            window.location.href = '/QLNhanSu_version1/public/login.html';
            return null;
        }
    },
    
    logout: async () => {
        try {
            const response = await fetch('/QLNhanSu_version1/public/logout.php', {
                method: 'POST',
                credentials: 'include'
            });
            
            const data = await response.json();
            
            if (data.success) {
                window.location.href = '/QLNhanSu_version1/public/login.html';
            }
        } catch (error) {
            console.error('Lỗi đăng xuất:', error);
        }
    },
    
    hasPermission: (permission) => {
        const user = SessionUtils.getCurrentUser();
        if (!user) return false;
        
        // Kiểm tra quyền dựa trên role
        switch (user.role.toLowerCase()) {
            case 'admin':
                return true; // Admin có tất cả quyền
            case 'manager':
                return ['view_employees', 'manage_attendance', 'view_reports'].includes(permission);
            case 'hr':
                return ['manage_employees', 'manage_salary', 'view_reports'].includes(permission);
            case 'employee':
                return ['view_profile', 'check_attendance', 'request_leave'].includes(permission);
            default:
                return false;
        }
    },
    
    getCurrentUser: () => {
        try {
            const userData = localStorage.getItem('userData');
            return userData ? JSON.parse(userData) : null;
        } catch (error) {
            console.error('Lỗi lấy thông tin người dùng:', error);
            return null;
        }
    },
    
    updateUserData: (userData) => {
        try {
            localStorage.setItem('userData', JSON.stringify(userData));
        } catch (error) {
            console.error('Lỗi cập nhật thông tin người dùng:', error);
        }
    }
};

// Kiểm tra session khi trang được tải
document.addEventListener('DOMContentLoaded', async () => {
    const user = await SessionUtils.checkSession();
    if (user) {
        SessionUtils.updateUserData(user);
    }
}); 