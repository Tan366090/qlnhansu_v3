class AuthUtils {
    static async login(email, password) {
        try {
            const response = await fetch("/QLNhanSu_version1/api/auth/login.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();
            
            if (data.success) {
                // Lưu thông tin user vào localStorage
                localStorage.setItem("user", JSON.stringify(data.user));
                // Chuyển hướng đến trang tương ứng
                window.location.href = data.redirect;
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error("Login error:", error);
            alert(error.message || "Đăng nhập thất bại");
        }
    }

    static logout() {
        localStorage.removeItem("user");
        window.location.href = "/QLNhanSu_version1/login.html";
    }

    static isLoggedIn() {
        return !!localStorage.getItem("user");
    }

    static getUser() {
        const user = localStorage.getItem("user");
        return user ? JSON.parse(user) : null;
    }
} 