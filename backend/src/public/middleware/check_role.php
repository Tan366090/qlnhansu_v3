<?php
session_start();

// Get the required role from query parameter
$required_role = $_GET['role'] ?? '';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: /QLNhanSu/backend/src/public/login_new.html');
    exit();
}

// Check if user has the required role
if ($_SESSION['role'] !== $required_role) {
    // Redirect to appropriate dashboard based on actual role
    switch ($_SESSION['role']) {
        case 'admin':
            header('Location: /QLNhanSu/backend/src/public/admin/dashboard.html');
            break;
        case 'manager':
            header('Location: /QLNhanSu/backend/src/public/manager/dashboard.html');
            break;
        case 'hr':
            header('Location: /QLNhanSu/backend/src/public/hr/dashboard.html');
            break;
        case 'employee':
            header('Location: /QLNhanSu/backend/src/public/employee/dashboard.html');
            break;
        default:
            header('Location: /QLNhanSu/backend/src/public/login_new.html?error=invalid_role');
    }
    exit();
}

// If role is correct, continue to the requested page
return; 