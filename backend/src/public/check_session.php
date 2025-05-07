<?php
session_start();

// Bypass all session checks for development
$user = [
    'user_id' => 1,
    'role' => 'admin',
    'username' => 'admin',
    'full_name' => 'Administrator',
    'email' => 'admin@example.com'
];

// Always return admin dashboard URL
$dashboardUrl = '/qlnhansu_V3/backend/src/public/admin/dashboard_admin_V1.php';

// Return user data and dashboard URL
echo json_encode([
    'authenticated' => true,
    'user' => $user,
    'dashboard_url' => $dashboardUrl
]);
?>