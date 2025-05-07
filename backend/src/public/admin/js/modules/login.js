class LoginManager {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Login form submission
        document.getElementById("loginForm").addEventListener("submit", async (e) => {
            e.preventDefault();
            await this.login(new FormData(e.target));
        });

        // Remember me checkbox
        document.getElementById("rememberMe").addEventListener("change", (e) => {
            localStorage.setItem("rememberMe", e.target.checked);
        });

        // Load remember me state
        const rememberMe = localStorage.getItem("rememberMe") === "true";
        document.getElementById("rememberMe").checked = rememberMe;
    }

    async login(formData) {
        try {
            common.showLoading();

            const data = {
                email: formData.get("email"),
                password: formData.get("password"),
                remember: formData.get("remember") === "on"
            };

            const response = await api.auth.login(data);
            
            // Save token
            auth.setToken(response.data.token);
            
            // Save user info
            auth.setUserInfo(response.data.user);
            
            // Redirect based on role
            if (response.data.user.role === "ADMIN") {
                window.location.href = "dashboard-admin.html";
            } else {
                window.location.href = "dashboard-employee.html";
            }
        } catch (error) {
            common.showError("Đăng nhập thất bại: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    validateForm(formData) {
        const email = formData.get("email");
        const password = formData.get("password");

        if (!email) {
            common.showError("Vui lòng nhập email");
            return false;
        }

        if (!this.isValidEmail(email)) {
            common.showError("Email không hợp lệ");
            return false;
        }

        if (!password) {
            common.showError("Vui lòng nhập mật khẩu");
            return false;
        }

        if (password.length < 6) {
            common.showError("Mật khẩu phải có ít nhất 6 ký tự");
            return false;
        }

        return true;
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
}

// Initialize LoginManager
window.loginManager = new LoginManager(); 