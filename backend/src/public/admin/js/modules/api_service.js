// API Service
const API_BASE_URL = "/qlnhansu_V3/backend/src/api";

const api = {
    API_BASE_URL,  // Export API_BASE_URL

    // Generic API call function
    async call(endpoint, method = "GET", data = null) {
        try {
            const token = localStorage.getItem("token");
            const headers = {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${token}`
            };

            const config = {
                method,
                headers,
                credentials: "include"
            };

            if (data && (method === "POST" || method === "PUT")) {
                config.body = JSON.stringify(data);
            }

            const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error("API call failed:", error);
            throw error;
        }
    },

    // Users endpoints
    users: {
        getAll: (params) => api.call(`/users.php?${new URLSearchParams(params)}`),
        getById: (id) => api.call(`/users.php?id=${id}`),
        create: (data) => api.call("/users.php", "POST", data),
        update: (id, data) => api.call(`/users.php?id=${id}`, "PUT", data),
        delete: (id) => api.call(`/users.php?id=${id}`, "DELETE")
    },

    // Departments endpoints
    departments: {
        getAll: (params) => api.call(`/departments.php?${new URLSearchParams(params)}`),
        getById: (id) => api.call(`/departments.php?id=${id}`),
        create: (data) => api.call("/departments.php", "POST", data),
        update: (id, data) => api.call(`/departments.php?id=${id}`, "PUT", data),
        delete: (id) => api.call(`/departments.php?id=${id}`, "DELETE")
    },

    // Positions endpoints
    positions: {
        getAll: (params) => api.call(`/positions.php?${new URLSearchParams(params)}`),
        getById: (id) => api.call(`/positions.php?id=${id}`),
        create: (data) => api.call("/positions.php", "POST", data),
        update: (id, data) => api.call(`/positions.php?id=${id}`, "PUT", data),
        delete: (id) => api.call(`/positions.php?id=${id}`, "DELETE")
    },

    // Attendance endpoints
    attendance: {
        getAll: (params) => api.call(`/attendance.php?${new URLSearchParams(params)}`),
        getById: (id) => api.call(`/attendance.php?id=${id}`),
        create: (data) => api.call("/attendance.php", "POST", data),
        update: (id, data) => api.call(`/attendance.php?id=${id}`, "PUT", data),
        delete: (id) => api.call(`/attendance.php?id=${id}`, "DELETE")
    },

    // Leaves endpoints
    leaves: {
        getAll: (params) => api.call(`/leaves.php?${new URLSearchParams(params)}`),
        getById: (id) => api.call(`/leaves.php?id=${id}`),
        create: (data) => api.call("/leaves.php", "POST", data),
        update: (id, data) => api.call(`/leaves.php?id=${id}`, "PUT", data),
        delete: (id) => api.call(`/leaves.php?id=${id}`, "DELETE")
    },

    // Salaries endpoints
    salaries: {
        getAll: (params) => api.call(`/salaries.php?${new URLSearchParams(params)}`),
        getById: (id) => api.call(`/salaries.php?id=${id}`),
        create: (data) => api.call("/salaries.php", "POST", data),
        update: (id, data) => api.call(`/salaries.php?id=${id}`, "PUT", data),
        delete: (id) => api.call(`/salaries.php?id=${id}`, "DELETE")
    },

    // Activities endpoints
    activities: {
        getAll: (params) => api.call(`/activities.php?${new URLSearchParams(params)}`),
        getById: (id) => api.call(`/activities.php?id=${id}`),
        create: (data) => api.call("/activities.php", "POST", data),
        update: (id, data) => api.call(`/activities.php?id=${id}`, "PUT", data),
        delete: (id) => api.call(`/activities.php?id=${id}`, "DELETE")
    }
};

// Export api object
window.api = api; 