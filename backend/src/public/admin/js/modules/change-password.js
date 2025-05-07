class ChangePasswordManager {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Change password form submission
        document.getElementById("changePasswordForm").addEventListener("submit", async (e) => {
            e.preventDefault();
            await this.changePassword(new FormData(e.target));
        });

        // Back to profile button
        document.getElementById("backToProfileBtn").addEventListener("click", () => {
            window.location.href = "profile.html";
        });
    }

    async changePassword(formData) {
        try {
            if (!this.validateForm(formData)) return;

            common.showLoading();

            const data = {
                currentPassword: formData.get("currentPassword"),
                newPassword: formData.get("newPassword"),
                confirmPassword: formData.get("confirmPassword")
            };

            await api.auth.changePassword(data);
            common.showSuccess("Đổi mật khẩu thành công");
            
            // Clear form
            document.getElementById("changePasswordForm").reset();
        } catch (error) {
            common.showError("Không thể đổi mật khẩu: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    validateForm(formData) {
        const currentPassword = formData.get("currentPassword");
        const newPassword = formData.get("newPassword");
        const confirmPassword = formData.get("confirmPassword");

        if (!currentPassword) {
            common.showError("Vui lòng nhập mật khẩu hiện tại");
            return false;
        }

        if (!newPassword) {
            common.showError("Vui lòng nhập mật khẩu mới");
            return false;
        }

        if (newPassword.length < 6) {
            common.showError("Mật khẩu mới phải có ít nhất 6 ký tự");
            return false;
        }

        if (!confirmPassword) {
            common.showError("Vui lòng xác nhận mật khẩu mới");
            return false;
        }

        if (newPassword !== confirmPassword) {
            common.showError("Mật khẩu xác nhận không khớp");
            return false;
        }

        return true;
    }
}

// Initialize ChangePasswordManager
window.changePasswordManager = new ChangePasswordManager(); 