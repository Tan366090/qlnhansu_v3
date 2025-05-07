// ...nội dung script nội tuyến từ login.html...

// Inline Scripts
import { authUtils } from "./auth_utils.js";
import ApiService from "./api_service.js";

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
    // Login form handler
    const loginForm = document.getElementById("loginForm");
    const submitButton = document.getElementById("submitButton");
    const spinner = document.getElementById("spinner");
    const errorDiv = document.getElementById("errorMessage");
    
    if (loginForm) {
        loginForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            
            // Clear previous error
            if (errorDiv) {
                errorDiv.textContent = "";
                errorDiv.style.display = "none";
            }
            
            const username = document.getElementById("username").value.trim();
            const password = document.getElementById("password").value;
            
            // Basic validation
            if (!username || !password) {
                if (errorDiv) {
                    errorDiv.textContent = "Vui lòng nhập đầy đủ thông tin đăng nhập";
                    errorDiv.style.display = "block";
                }
                return;
            }
            
            try {
                // Show loading state
                submitButton.disabled = true;
                spinner.hidden = false;
                
                // Login
                const result = await authUtils.login(username, password);
                
                if (result.success) {
                    // Redirect based on user role
                    const role = result.role || "employee";
                    const redirectMap = {
                        admin: "/QLNhanSu_version1/admin/dashboard.html",
                        employee: "/QLNhanSu_version1/employee/dashboard.html",
                        manager: "/QLNhanSu_version1/manager/dashboard.html",
                        hr: "/QLNhanSu_version1/hr/dashboard.html"
                    };
                    
                    window.location.href = redirectMap[role] || redirectMap.employee;
                } else {
                    throw new Error(result.message || "Đăng nhập thất bại");
                }
            } catch (error) {
                console.error("Login error:", error);
                // Show error message to user
                if (errorDiv) {
                    errorDiv.textContent = error.message || "Có lỗi xảy ra khi đăng nhập";
                    errorDiv.style.display = "block";
                }
            } finally {
                // Hide loading state
                submitButton.disabled = false;
                spinner.hidden = true;
            }
        });
    }
    
    // Error message handler
    if (errorDiv) {
        errorDiv.addEventListener("click", () => {
            errorDiv.style.display = "none";
        });
    }
    
    // Toggle password visibility
    const togglePassword = document.querySelector(".toggle-password");
    const passwordInput = document.getElementById("password");
    
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener("click", () => {
            const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
            passwordInput.setAttribute("type", type);
            togglePassword.querySelector("i").classList.toggle("fa-eye");
            togglePassword.querySelector("i").classList.toggle("fa-eye-slash");
        });
    }
});
