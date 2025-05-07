class AuthService {
    constructor() {
        this.tokenKey = "auth_token";
        this.refreshTokenKey = "refresh_token";
        this.userKey = "user_data";
    }

    // Lưu thông tin đăng nhập
    setAuthData(token, refreshToken, userData) {
        localStorage.setItem(this.tokenKey, token);
        localStorage.setItem(this.refreshTokenKey, refreshToken);
        localStorage.setItem(this.userKey, JSON.stringify(userData));
    }

    // Lấy token
    getToken() {
        return localStorage.getItem(this.tokenKey);
    }

    // Lấy refresh token
    getRefreshToken() {
        return localStorage.getItem(this.refreshTokenKey);
    }

    // Lấy thông tin người dùng
    getUser() {
        const userData = localStorage.getItem(this.userKey);
        return userData ? JSON.parse(userData) : null;
    }

    // Kiểm tra đăng nhập
    isLoggedIn() {
        return !!this.getToken();
    }

    // Kiểm tra quyền
    hasRole(requiredRoles) {
        const user = this.getUser();
        if (!user || !user.role) return false;
        return requiredRoles.includes(user.role);
    }

    // Đăng xuất
    logout() {
        localStorage.removeItem(this.tokenKey);
        localStorage.removeItem(this.refreshTokenKey);
        localStorage.removeItem(this.userKey);
        window.location.href = "/login.html";
    }

    // Refresh token
    async refreshToken() {
        try {
            const refreshToken = this.getRefreshToken();
            if (!refreshToken) throw new Error("No refresh token");

            const response = await fetch("/api/auth/refresh", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ refreshToken })
            });

            if (!response.ok) throw new Error("Refresh token failed");

            const data = await response.json();
            this.setAuthData(data.token, data.refreshToken, data.user);
            return data.token;
        } catch (error) {
            console.error("Refresh token error:", error);
            this.logout();
            throw error;
        }
    }
}

export const authService = new AuthService(); 