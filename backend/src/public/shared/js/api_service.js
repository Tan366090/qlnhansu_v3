import API_ENDPOINTS from "./api_config.js";

class ApiService {
    static async request(endpoint, options = {}) {
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
    }

    // Auth methods
    static async login(email, password) {
        return await this.request(API_ENDPOINTS.LOGIN, {
            method: "POST",
            body: JSON.stringify({ email, password })
        });
    }

    // Employee methods
    static async getEmployees(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return await this.request(`${API_ENDPOINTS.EMPLOYEES}?${queryString}`);
    }

    static async getEmployeeById(id) {
        return await this.request(API_ENDPOINTS.EMPLOYEE_PROFILE.replace(":id", id));
    }

    static async createEmployee(employeeData) {
        return await this.request(API_ENDPOINTS.EMPLOYEES, {
            method: "POST",
            body: JSON.stringify(employeeData)
        });
    }

    // Department methods
    static async getDepartments() {
        return await this.request(API_ENDPOINTS.DEPARTMENTS);
    }

    static async getDepartmentEmployees(departmentId) {
        return await this.request(
            API_ENDPOINTS.DEPARTMENT_EMPLOYEES.replace(":id", departmentId)
        );
    }

    // Salary methods
    static async getSalaries(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return await this.request(`${API_ENDPOINTS.SALARIES}?${queryString}`);
    }

    static async getSalaryHistory(employeeId) {
        return await this.request(
            API_ENDPOINTS.SALARY_HISTORY.replace(":employeeId", employeeId)
        );
    }

    // Attendance methods
    static async getAttendance(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return await this.request(`${API_ENDPOINTS.ATTENDANCE}?${queryString}`);
    }

    static async createAttendance(attendanceData) {
        return await this.request(API_ENDPOINTS.ATTENDANCE, {
            method: "POST",
            body: JSON.stringify(attendanceData)
        });
    }

    // Leave request methods
    static async getLeaveRequests(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return await this.request(`${API_ENDPOINTS.LEAVE_REQUESTS}?${queryString}`);
    }

    static async createLeaveRequest(leaveData) {
        return await this.request(API_ENDPOINTS.LEAVE_REQUESTS, {
            method: "POST",
            body: JSON.stringify(leaveData)
        });
    }

    // Evaluation methods
    static async getEvaluations(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return await this.request(`${API_ENDPOINTS.EVALUATIONS}?${queryString}`);
    }

    static async createEvaluation(evaluationData) {
        return await this.request(API_ENDPOINTS.EVALUATIONS, {
            method: "POST",
            body: JSON.stringify(evaluationData)
        });
    }
}

export default ApiService; 