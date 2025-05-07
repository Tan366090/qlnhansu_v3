// Authentication utilities
export const checkAuth = async () => {
    try {
        const response = await fetch('/QLNhanSu_version1/backend/src/api/auth/check.php', {
            method: 'GET',
            credentials: 'include'
        });
        const data = await response.json();
        return data.authenticated;
    } catch (error) {
        console.error('Error checking authentication:', error);
        return false;
    }
};

export const logout = async () => {
    try {
        const response = await fetch('/QLNhanSu_version1/backend/src/api/auth/logout.php', {
            method: 'POST',
            credentials: 'include'
        });
        if (response.ok) {
            window.location.href = '/QLNhanSu_version1/public/login_new.html';
        }
    } catch (error) {
        console.error('Error logging out:', error);
    }
};

export const initSessionMonitoring = () => {
    setInterval(async () => {
        const isAuthenticated = await checkAuth();
        if (!isAuthenticated) {
            window.location.href = '/QLNhanSu_version1/public/login_new.html';
        }
    }, 300000); // 5 minutes
};

const auth = {
    isAuthenticated: function() {
        const token = localStorage.getItem("token");
        return !!token;
    },

    getCurrentUser: function() {
        const user = localStorage.getItem("user");
        return user ? JSON.parse(user) : null;
    },

    updateUserInfo: function() {
        const user = this.getCurrentUser();
        const userNameElement = document.getElementById("userName");
        if (user && userNameElement) {
            userNameElement.textContent = user.name;
        }
    },

    handleNavigation: function(event) {
        // Kiểm tra nếu click vào link
        if (event.target.tagName === "A" || event.target.closest("a")) {
            // Nếu chưa đăng nhập thì lưu URL và chuyển đến trang đăng nhập
            if (!this.isAuthenticated()) {
                event.preventDefault();
                localStorage.setItem("redirectUrl", event.target.href || event.target.closest("a").href);
                window.location.href = "/QLNhanSu_version1/public/login_new.html";
            }
        }
    }
};

// Add event listeners when DOM is loaded
document.addEventListener("DOMContentLoaded", function() {
    // Logout button
    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", auth.logout);
    }
    
    // Check authentication on protected pages
    const isProtectedPage = !window.location.pathname.includes("login_new.html");
    if (isProtectedPage) {
        auth.checkAuth();
    }
    
    // Update user info if authenticated
    if (auth.isAuthenticated()) {
        auth.updateUserInfo();
    }

    // Add click handler for navigation
    document.body.addEventListener("click", (e) => auth.handleNavigation(e));
}); 