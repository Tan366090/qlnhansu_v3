document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("changePasswordForm");
    const submitButton = document.getElementById("submitButton");
    const spinner = document.getElementById("spinner");
    const errorMessage = document.getElementById("errorMessage");
    const notification = document.getElementById("notification");
    const notificationMessage = document.getElementById("notificationMessage");

    // Toggle password visibility
    document.querySelectorAll(".toggle-password").forEach(button => {
        button.addEventListener("click", function() {
            const input = this.parentElement.querySelector("input");
            const icon = this.querySelector("i");
            
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        });
    });

    // Form validation
    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        
        // Reset error messages
        document.querySelectorAll(".error-message").forEach(el => el.textContent = "");
        errorMessage.textContent = "";
        
        const currentPassword = document.getElementById("currentPassword").value;
        const newPassword = document.getElementById("newPassword").value;
        const confirmPassword = document.getElementById("confirmPassword").value;
        
        let isValid = true;
        
        // Validate current password
        if (!currentPassword) {
            document.getElementById("currentPasswordError").textContent = "Vui lòng nhập mật khẩu hiện tại";
            isValid = false;
        }
        
        // Validate new password
        if (!newPassword) {
            document.getElementById("newPasswordError").textContent = "Vui lòng nhập mật khẩu mới";
            isValid = false;
        } else if (newPassword.length < 6) {
            document.getElementById("newPasswordError").textContent = "Mật khẩu phải có ít nhất 6 ký tự";
            isValid = false;
        }
        
        // Validate confirm password
        if (!confirmPassword) {
            document.getElementById("confirmPasswordError").textContent = "Vui lòng xác nhận mật khẩu mới";
            isValid = false;
        } else if (confirmPassword !== newPassword) {
            document.getElementById("confirmPasswordError").textContent = "Mật khẩu xác nhận không khớp";
            isValid = false;
        }
        
        if (!isValid) return;
        
        try {
            // Show loading state
            submitButton.disabled = true;
            spinner.hidden = false;
            
            // Send request to change password
            const response = await fetch("/api/auth/change-password", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    current_password: currentPassword,
                    new_password: newPassword
                })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                // Show success notification
                showNotification("Mật khẩu đã được thay đổi thành công", "success");
                
                // Clear form
                form.reset();
                
                // Redirect to login page after 2 seconds
                setTimeout(() => {
                    window.location.href = "login.html";
                }, 2000);
            } else {
                // Show error message
                errorMessage.textContent = data.message || "Đã xảy ra lỗi khi thay đổi mật khẩu";
            }
        } catch (error) {
            console.error("Error:", error);
            errorMessage.textContent = "Đã xảy ra lỗi khi thay đổi mật khẩu";
        } finally {
            // Hide loading state
            submitButton.disabled = false;
            spinner.hidden = true;
        }
    });

    // Function to show notification
    function showNotification(message, type = "info") {
        notificationMessage.textContent = message;
        notification.className = `notification ${type}`;
        notification.hidden = false;
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            notification.hidden = true;
        }, 5000);
    }

    // Close notification button
    document.querySelector(".notification-close").addEventListener("click", () => {
        notification.hidden = true;
    });
}); 