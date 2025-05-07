// Function to handle auto login
async function autoLogin() {
    try {
        const response = await fetch('/qlnhansu_V2/backend/src/api/auth/auto_login.php', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (data.success) {
            // Store user data in localStorage
            localStorage.setItem('user', JSON.stringify(data.user));
            
            // Redirect based on role
            const baseUrl = '/qlnhansu_V2/backend/src/public';
            switch (data.user.role_id) {
                case 1: // Admin
                    window.location.href = `${baseUrl}/admin/dashboard.html`;
                    break;
                case 2: // Manager
                    window.location.href = `${baseUrl}/manager/dashboard.html`;
                    break;
                case 3: // HR
                    window.location.href = `${baseUrl}/hr/dashboard.html`;
                    break;
                case 4: // Employee
                    window.location.href = `${baseUrl}/employee/dashboard.html`;
                    break;
                default:
                    window.location.href = `${baseUrl}/login.html`;
            }
        } else {
            console.error('Auto login failed:', data.message);
            // Redirect to login page if auto login fails
            window.location.href = '/qlnhansu_V2/backend/src/public/login.html';
        }
    } catch (error) {
        console.error('Error during auto login:', error);
        // Redirect to login page if there's an error
        window.location.href = '/qlnhansu_V2/backend/src/public/login.html';
    }
}

// Call auto login when the page loads
document.addEventListener('DOMContentLoaded', autoLogin); 