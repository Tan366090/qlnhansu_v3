<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

// Kiểm tra phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Lấy dữ liệu từ request body
$data = json_decode(file_get_contents('php://input'), true);

// Validate dữ liệu
if (!isset($data['user_id']) || !isset($data['attendance_date']) || 
    !isset($data['recorded_at']) || !isset($data['attendance_symbol'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Kiểm tra nhân viên tồn tại
    $stmt = $conn->prepare("SELECT employee_id FROM employees WHERE employee_id = ? AND status = 'active'");
    $stmt->bind_param("i", $data['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Employee not found']);
        exit;
    }

    // Lưu chấm công
    $stmt = $conn->prepare("
        INSERT INTO attendance (
            user_id, 
            attendance_date, 
            recorded_at, 
            attendance_symbol, 
            notes
        ) VALUES (?, ?, ?, ?, ?)
    ");

    $notes = isset($data['notes']) ? $data['notes'] : null;
    $stmt->bind_param(
        "issss", 
        $data['user_id'],
        $data['attendance_date'],
        $data['recorded_at'],
        $data['attendance_symbol'],
        $notes
    );

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Attendance recorded successfully',
            'attendance_id' => $stmt->insert_id
        ]);
    } else {
        throw new Exception("Failed to save attendance record");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 