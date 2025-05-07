<?php
// Start output buffering
ob_start();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');    // cache for 1 day

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';
require_once '../middleware/auth.php';

try {
    // Clear any previous output
    ob_clean();

    // Verify user is logged in
    Auth::requireAuth();

    $database = new Database();
    $db = $database->getConnection();
    $user_id = Auth::getUserId();

    // Handle different HTTP methods
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            // Get notifications for the current user
            $query = "
                SELECT 
                    n.*,
                    CASE 
                        WHEN n.type = 'leave_approved' THEN 'Đơn nghỉ phép đã được duyệt'
                        WHEN n.type = 'leave_rejected' THEN 'Đơn nghỉ phép bị từ chối'
                        WHEN n.type = 'leave_pending' THEN 'Có đơn nghỉ phép mới cần duyệt'
                        WHEN n.type = 'leave_cancelled' THEN 'Đơn nghỉ phép đã bị hủy'
                        ELSE n.type
                    END as type_name
                FROM notifications n
                WHERE n.user_id = :user_id
                ORDER BY n.created_at DESC
                LIMIT 50
            ";

            $stmt = $db->prepare($query);
            $stmt->execute([':user_id' => $user_id]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format timestamps
            foreach ($notifications as &$notification) {
                $notification['created_at'] = date('d/m/Y H:i:s', strtotime($notification['created_at']));
                if ($notification['read_at']) {
                    $notification['read_at'] = date('d/m/Y H:i:s', strtotime($notification['read_at']));
                }
            }

            // Clear output buffer before sending response
            ob_end_clean();
            echo json_encode([
                'success' => true,
                'data' => $notifications
            ]);
            break;

        case 'POST':
            // Mark notification as read
            $data = json_decode(file_get_contents('php://input'), true);
            $notification_id = $data['id'] ?? null;

            if (!$notification_id) {
                throw new Exception('Notification ID is required');
            }

            $query = "
                UPDATE notifications 
                SET is_read = 1,
                    read_at = NOW()
                WHERE id = :id AND user_id = :user_id
            ";

            $stmt = $db->prepare($query);
            $stmt->execute([
                ':id' => $notification_id,
                ':user_id' => $user_id
            ]);

            // Clear output buffer before sending response
            ob_end_clean();
            echo json_encode([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
            break;

        case 'DELETE':
            // Delete notification
            $notification_id = $_GET['id'] ?? null;

            if (!$notification_id) {
                throw new Exception('Notification ID is required');
            }

            $query = "DELETE FROM notifications WHERE id = :id AND user_id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':id' => $notification_id,
                ':user_id' => $user_id
            ]);

            // Clear output buffer before sending response
            ob_end_clean();
            echo json_encode([
                'success' => true,
                'message' => 'Notification deleted successfully'
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