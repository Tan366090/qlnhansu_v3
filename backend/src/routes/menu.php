<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/MenuController.php';

// Get user role from session or token
$userRole = $_SESSION['user_role'] ?? null;

if (!$userRole) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized'
    ]);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $menuController = new MenuController($db, $userRole);
    
    $response = $menuController->getMenuItems();
    
    header('Content-Type: application/json');
    echo json_encode($response);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 