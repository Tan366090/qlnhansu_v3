class ForgotPasswordManager {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Forgot password form submission
        document.getElementById("forgotPasswordForm").addEventListener("submit", async (e) => {
            e.preventDefault();
            await this.sendResetLink(new FormData(e.target));
        });

        // Back to login button
        document.getElementById("backToLoginBtn").addEventListener("click", () => {
            window.location.href = "login.html";
        });
    }

    async sendResetLink(formData) {
        try {
            if (!this.validateForm(formData)) return;

            common.showLoading();

            const data = {
                email: formData.get("email")
            };

            await api.auth.forgotPassword(data);
            common.showSuccess("Đã gửi liên kết đặt lại mật khẩu đến email của bạn");
            
            // Clear form
            document.getElementById("forgotPasswordForm").reset();
        } catch (error) {
            common.showError("Không thể gửi liên kết đặt lại mật khẩu: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    validateForm(formData) {
        const email = formData.get("email");

        if (!email) {
            common.showError("Vui lòng nhập email");
            return false;
        }

        if (!this.isValidEmail(email)) {
            common.showError("Email không hợp lệ");
            return false;
        }

        return true;
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
}

// Initialize ForgotPasswordManager
window.forgotPasswordManager = new ForgotPasswordManager(); 