// Import auth utils
import { authUtils } from "./auth_utils.js";

// Handle login success
function handleLoginSuccess(response) {
    if (response.success) {
        // Store user data in session storage
        sessionStorage.setItem("user", JSON.stringify(response.user));
        
        // Redirect to appropriate dashboard based on role
        redirectToDashboard(response.user.role_id);
    } else {
        showError(response.message);
    }
} 