import { apiService } from "./api_service.js";
import { authUtils } from "./auth_utils.js";

// Export các hàm cần thiết
export const common = {
    init: async () => {
        try {
            // const token = await authUtils.getCSRFToken();
            // apiService.setCSRFToken(token);
        } catch (error) {
            console.error("Error initializing common:", error);
        }
    }
}; 