<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Không có dữ liệu được gửi lên');
    }

    // Prepare response
    $response = [
        'success' => false,
        'message' => '',
        'error' => ''
    ];

    // Connect to database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception('Kết nối database thất bại: ' . $conn->connect_error);
    }

    // Set charset
    $conn->set_charset("utf8");

    // Begin transaction
    $conn->begin_transaction();

    try {
        foreach ($data as $record) {
            // Validate required fields
            if (empty($record['attendance_date']) || empty($record['attendance_symbol'])) {
                throw new Exception('Thiếu thông tin bắt buộc');
            }

            // Prepare SQL statement
            $stmt = $conn->prepare("INSERT INTO attendance (attendance_date, attendance_symbol, notes) VALUES (?, ?, ?)");
            
            if (!$stmt) {
                throw new Exception('Chuẩn bị câu lệnh SQL thất bại: ' . $conn->error);
            }

            // Bind parameters
            $stmt->bind_param("sss", 
                $record['attendance_date'],
                $record['attendance_symbol'],
                $record['notes']
            );

            // Execute statement
            if (!$stmt->execute()) {
                throw new Exception('Thực thi câu lệnh SQL thất bại: ' . $stmt->error);
            }

            $stmt->close();
        }

        // Commit transaction
        $conn->commit();
        
        $response['success'] = true;
        $response['message'] = 'Tải dữ liệu lên thành công';

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }

    // Close connection
    $conn->close();

} catch (Exception $e) {
    $response['success'] = false;
    $response['error'] = $e->getMessage();
}

// Send response
echo json_encode($response);
exit(); // Ensure no additional output
?>