import { authUtils } from "./auth_utils.js";

// API Service module
const apiService = {
    baseUrl: "http://localhost/QLNhanSu_version1/api",

    // Headers
    getHeaders(isFormData = false) {
        const headers = {
            "Accept": "application/json"
        };

        if (!isFormData) {
            headers["Content-Type"] = "application/json";
        }

        // Add token if available
        const token = authUtils.getToken();
        if (token) {
            headers["Authorization"] = `Bearer ${token}`;
        }

        return headers;
    },

    // Handle Response
    async handleResponse(response) {
        const contentType = response.headers.get("content-type");
        const isJson = contentType && contentType.includes("application/json");
        
        if (!response.ok) {
            let errorMessage = "Có lỗi xảy ra";
            if (isJson) {
                const error = await response.json();
                errorMessage = error.message || errorMessage;
            } else {
                errorMessage = await response.text() || errorMessage;
            }
            throw new Error(errorMessage);
        }
        
        return isJson ? response.json() : response.text();
    },

    // GET request
    async get(endpoint) {
        try {
            const response = await fetch(`${this.baseUrl}/${endpoint}`);
            return await response.json();
        } catch (error) {
            console.error("API Error:", error);
            throw error;
        }
    },

    // POST request
    async post(endpoint, data) {
        try {
            const response = await fetch(`${this.baseUrl}/${endpoint}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (error) {
            console.error("API Error:", error);
            throw error;
        }
    },

    // PUT request
    async put(endpoint, data) {
        try {
            const isFormData = data instanceof FormData;
            const response = await fetch(`${this.baseUrl}/${endpoint}`, {
                method: "PUT",
                headers: this.getHeaders(isFormData),
                credentials: "include",
                body: isFormData ? data : JSON.stringify(data)
            });
            
            return await this.handleResponse(response);
        } catch (error) {
            console.error("API PUT error:", error);
            throw error;
        }
    },

    // DELETE request
    async delete(endpoint) {
        try {
            const response = await fetch(`${this.baseUrl}/${endpoint}`, {
                method: "DELETE",
                headers: this.getHeaders(),
                credentials: "include"
            });
            
            return await this.handleResponse(response);
        } catch (error) {
            console.error("API DELETE error:", error);
            throw error;
        }
    }
};

export { apiService }; 