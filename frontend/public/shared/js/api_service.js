import API_ENDPOINTS from "./api_config.js";

// API Service for handling all API calls
const ApiService = {
    baseUrl: 'http://localhost/QLNhanSu_version1/api',

    async login(username, password) {
        try {
            const response = await fetch(`${this.baseUrl}/auth/login.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ username, password }),
                credentials: 'include'
            });

            // Kiểm tra content type
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server returned non-JSON response');
            }

            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Login failed');
            }

            return data;
        } catch (error) {
            console.error('Login error:', error);
            throw new Error(error.message || 'Login failed');
        }
    },

    async forgotPassword(email) {
        try {
            const response = await fetch(`${this.baseUrl}/auth/forgot_password.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email })
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            return await response.json();
        } catch (error) {
            throw new Error('Forgot password request failed: ' + error.message);
        }
    },

    async verifyOTP(email, otp) {
        try {
            const response = await fetch(`${this.baseUrl}/auth/verify_otp.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, otp })
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            return await response.json();
        } catch (error) {
            throw new Error('OTP verification failed: ' + error.message);
        }
    },

    async resetPassword(email, newPassword, token) {
        try {
            const response = await fetch(`${this.baseUrl}/auth/reset_password.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, new_password: newPassword, token })
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            return await response.json();
        } catch (error) {
            throw new Error('Password reset failed: ' + error.message);
        }
    },

    async request(endpoint, options = {}) {
        try {
            const token = localStorage.getItem("token");
            const headers = {
                "Content-Type": "application/json",
                ...(token && { "Authorization": `Bearer ${token}` }),
                ...options.headers
            };

            const response = await fetch(endpoint, {
                ...options,
                headers
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error("API request failed:", error);
            throw error;
        }
    },

    // Employee methods
    async getEmployees(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return await this.request(`${API_ENDPOINTS.EMPLOYEES}?${queryString}`);
    },

    async getEmployeeById(id) {
        return await this.request(API_ENDPOINTS.EMPLOYEE_PROFILE.replace(":id", id));
    },

    async createEmployee(employeeData) {
        return await this.request(API_ENDPOINTS.EMPLOYEES, {
            method: "POST",
            body: JSON.stringify(employeeData)
        });
    },

    // Department methods
    async getDepartments() {
        return await this.request(API_ENDPOINTS.DEPARTMENTS);
    },

    async getDepartmentEmployees(departmentId) {
        return await this.request(
            API_ENDPOINTS.DEPARTMENT_EMPLOYEES.replace(":id", departmentId)
        );
    },

    // Salary methods
    async getSalaries(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return await this.request(`${API_ENDPOINTS.SALARIES}?${queryString}`);
    },

    async getSalaryHistory(employeeId) {
        return await this.request(
            API_ENDPOINTS.SALARY_HISTORY.replace(":employeeId", employeeId)
        );
    },

    // Attendance methods
    async getAttendance(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return await this.request(`${API_ENDPOINTS.ATTENDANCE}?${queryString}`);
    },

    async createAttendance(attendanceData) {
        return await this.request(API_ENDPOINTS.ATTENDANCE, {
            method: "POST",
            body: JSON.stringify(attendanceData)
        });
    },

    // Leave request methods
    async getLeaveRequests(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return await this.request(`${API_ENDPOINTS.LEAVE_REQUESTS}?${queryString}`);
    },

    async createLeaveRequest(leaveData) {
        return await this.request(API_ENDPOINTS.LEAVE_REQUESTS, {
            method: "POST",
            body: JSON.stringify(leaveData)
        });
    },

    // Evaluation methods
    async getEvaluations(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return await this.request(`${API_ENDPOINTS.EVALUATIONS}?${queryString}`);
    },

    async createEvaluation(evaluationData) {
        return await this.request(API_ENDPOINTS.EVALUATIONS, {
            method: "POST",
            body: JSON.stringify(evaluationData)
        });
    }
};

export default ApiService; 