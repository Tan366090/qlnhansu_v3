// Auth utility functions
const AuthUtils = {
    // Check if user is logged in
    isLoggedIn: async function() {
        try {
            const response = await fetch("/QLNhanSu/backend/src/api/auth/check.php", {
                credentials: "include"
            });
            const data = await response.json();
            return data.success;
        } catch (error) {
            console.error("Error checking login status:", error);
            return false;
        }
    },

    // Get current user data
    getCurrentUser: async function() {
        try {
            const response = await fetch("/QLNhanSu/backend/src/api/auth/check.php", {
                credentials: "include"
            });
            const data = await response.json();
            return data.success ? data.data : null;
        } catch (error) {
            console.error("Error getting user data:", error);
            return null;
        }
    },

    // Logout user
    logout: async function() {
        try {
            await fetch("/QLNhanSu/backend/src/api/auth/logout.php", {
                method: "POST",
                credentials: "include"
            });
            window.location.href = "/QLNhanSu/backend/src/public/login_new.html";
        } catch (error) {
            console.error("Error logging out:", error);
        }
    },

    // Check if user has required role
    hasRole: async function(requiredRole) {
        const user = await this.getCurrentUser();
        return user && user.role === requiredRole;
    },

    // Redirect to login if not authenticated
    requireAuth: async function() {
        const isLoggedIn = await this.isLoggedIn();
        if (!isLoggedIn) {
            window.location.href = "/QLNhanSu/backend/src/public/login_new.html";
            return false;
        }
        return true;
    },

    // Redirect to login if not authenticated with specific role
    requireRole: async function(requiredRole) {
        const hasRole = await this.hasRole(requiredRole);
        if (!hasRole) {
            window.location.href = "/QLNhanSu/backend/src/public/login_new.html";
            return false;
        }
        return true;
    }
};

export { AuthUtils }; 