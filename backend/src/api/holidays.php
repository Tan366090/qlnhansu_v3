<?php
// Start output buffering
ob_start();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../middleware/auth.php';

try {
    // Clear any previous output
    ob_clean();

    // Verify user is logged in
    Auth::requireAuth();

    $database = new Database();
    $db = $database->getConnection();

    // Handle different HTTP methods
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            // Get holidays
            $year = $_GET['year'] ?? date('Y');
            $query = "
                SELECT 
                    id,
                    name,
                    date,
                    description,
                    is_recurring,
                    created_at,
                    updated_at
                FROM holidays
                WHERE YEAR(date) = :year
                ORDER BY date ASC
            ";

            $stmt = $db->prepare($query);
            $stmt->execute([':year' => $year]);
            $holidays = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format dates
            foreach ($holidays as &$holiday) {
                $holiday['date'] = date('Y-m-d', strtotime($holiday['date']));
                $holiday['created_at'] = date('d/m/Y H:i:s', strtotime($holiday['created_at']));
                $holiday['updated_at'] = date('d/m/Y H:i:s', strtotime($holiday['updated_at']));
            }

            // Clear output buffer before sending response
            ob_end_clean();
            echo json_encode([
                'success' => true,
                'data' => $holidays
            ]);
            break;

        case 'POST':
            // Add new holiday
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['name']) || !isset($data['date'])) {
                throw new Exception('Name and date are required');
            }

            $query = "
                INSERT INTO holidays (
                    name,
                    date,
                    description,
                    is_recurring,
                    created_at
                ) VALUES (
                    :name,
                    :date,
                    :description,
                    :is_recurring,
                    NOW()
                )
            ";

            $stmt = $db->prepare($query);
            $stmt->execute([
                ':name' => $data['name'],
                ':date' => $data['date'],
                ':description' => $data['description'] ?? null,
                ':is_recurring' => $data['is_recurring'] ?? 0
            ]);

            // Clear output buffer before sending response
            ob_end_clean();
            echo json_encode([
                'success' => true,
                'message' => 'Holiday added successfully',
                'id' => $db->lastInsertId()
            ]);
            break;

        case 'PUT':
            // Update holiday
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $_GET['id'] ?? null;

            if (!$id) {
                throw new Exception('Holiday ID is required');
            }

            if (!isset($data['name']) || !isset($data['date'])) {
                throw new Exception('Name and date are required');
            }

            $query = "
                UPDATE holidays 
                SET name = :name,
                    date = :date,
                    description = :description,
                    is_recurring = :is_recurring,
                    updated_at = NOW()
                WHERE id = :id
            ";

            $stmt = $db->prepare($query);
            $stmt->execute([
                ':id' => $id,
                ':name' => $data['name'],
                ':date' => $data['date'],
                ':description' => $data['description'] ?? null,
                ':is_recurring' => $data['is_recurring'] ?? 0
            ]);

            // Clear output buffer before sending response
            ob_end_clean();
            echo json_encode([
                'success' => true,
                'message' => 'Holiday updated successfully'
            ]);
            break;

        case 'DELETE':
            // Delete holiday
            $id = $_GET['id'] ?? null;

            if (!$id) {
                throw new Exception('Holiday ID is required');
            }

            $query = "DELETE FROM holidays WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute([':id' => $id]);

            // Clear output buffer before sending response
            ob_end_clean();
            echo json_encode([
                'success' => true,
                'message' => 'Holiday deleted successfully'
            ]);
            break;

        default:
            throw new Exception('Method not allowed');
    }

} catch (Exception $e) {
    // Clear output buffer and return error
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
    exit;
}
?> 