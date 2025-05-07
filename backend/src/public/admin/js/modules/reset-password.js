class ResetPasswordManager {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Reset password form submission
        document.getElementById("resetPasswordForm").addEventListener("submit", async (e) => {
            e.preventDefault();
            await this.resetPassword(new FormData(e.target));
        });

        // Back to login button
        document.getElementById("backToLoginBtn").addEventListener("click", () => {
            window.location.href = "login.html";
        });
    }

    async resetPassword(formData) {
        try {
            if (!this.validateForm(formData)) return;

            common.showLoading();

            const data = {
                token: this.getTokenFromUrl(),
                password: formData.get("password"),
                confirmPassword: formData.get("confirmPassword")
            };

            await api.auth.resetPassword(data);
            common.showSuccess("Đặt lại mật khẩu thành công");
            
            // Redirect to login page after 2 seconds
            setTimeout(() => {
                window.location.href = "login.html";
            }, 2000);
        } catch (error) {
            common.showError("Không thể đặt lại mật khẩu: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    validateForm(formData) {
        const password = formData.get("password");
        const confirmPassword = formData.get("confirmPassword");

        if (!password) {
            common.showError("Vui lòng nhập mật khẩu mới");
            return false;
        }

        if (password.length < 6) {
            common.showError("Mật khẩu phải có ít nhất 6 ký tự");
            return false;
        }

        if (!confirmPassword) {
            common.showError("Vui lòng xác nhận mật khẩu");
            return false;
        }

        if (password !== confirmPassword) {
            common.showError("Mật khẩu xác nhận không khớp");
            return false;
        }

        return true;
    }

    getTokenFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        
        if (!token) {
            common.showError("Token không hợp lệ");
            window.location.href = "login.html";
            return null;
        }
        
        return token;
    }
}

// Initialize ResetPasswordManager
window.resetPasswordManager = new ResetPasswordManager(); 