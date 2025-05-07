const API_BASE_URL = "http://localhost:80/QLNhanSu/backend/src/api";

const API_ENDPOINTS = {
    // Auth endpoints
    LOGIN: `${API_BASE_URL}/auth/login.php`,
    FORGOT_PASSWORD: `${API_BASE_URL}/auth/forgot_password.php`,
    RESET_PASSWORD: `${API_BASE_URL}/auth/reset_password.php`,
    CHANGE_PASSWORD: `${API_BASE_URL}/auth/change_password.php`,

    // Dashboard endpoint
    DASHBOARD: `${API_BASE_URL}/dashboard.php`,

    // Employee endpoints
    EMPLOYEES: `${API_BASE_URL}/v1/employees`,
    EMPLOYEE_PROFILE: `${API_BASE_URL}/employees/profile.php`,
    EMPLOYEE_DOCUMENTS: `${API_BASE_URL}/employees/documents.php`,
    
    // Department endpoints
    DEPARTMENTS: `${API_BASE_URL}/v1/departments`,
    DEPARTMENT_EMPLOYEES: `${API_BASE_URL}/departments/employees.php`,
    
    // Position endpoints
    POSITIONS: `${API_BASE_URL}/positions/index.php`,
    
    // Salary endpoints
    SALARIES: `${API_BASE_URL}/v1/salaries`,
    SALARY_HISTORY: `${API_BASE_URL}/salary/history.php`,
    BONUSES: `${API_BASE_URL}/salary/bonuses.php`,
    
    // Attendance endpoints
    ATTENDANCE: `${API_BASE_URL}/attendance/index.php`,
    ATTENDANCE_HISTORY: `${API_BASE_URL}/attendance/history.php`,
    LEAVE_REQUESTS: `${API_BASE_URL}/leave/index.php`,
    
    // Evaluation endpoints
    EVALUATIONS: `${API_BASE_URL}/evaluations/index.php`,
    EMPLOYEE_EVALUATIONS: `${API_BASE_URL}/evaluations/employee.php`,
    
    // Document endpoints
    DOCUMENTS: `${API_BASE_URL}/documents/index.php`,
    CONTRACTS: `${API_BASE_URL}/contracts/index.php`,
    
    // Degree endpoints
    DEGREES: `${API_BASE_URL}/degrees/index.php`,
    EMPLOYEE_DEGREES: `${API_BASE_URL}/degrees/employee.php`,
};

export default API_ENDPOINTS; 