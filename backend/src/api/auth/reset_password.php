<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

// Lấy dữ liệu từ request
$data = json_decode(file_get_contents('php://input'), true);
$token = $data['token'] ?? '';
$new_password = $data['new_password'] ?? '';
$confirm_password = $data['confirm_password'] ?? '';

if (empty($token) || empty($new_password) || empty($confirm_password)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
    exit;
}

if ($new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Mật khẩu xác nhận không khớp']);
    exit;
}

if (strlen($new_password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Mật khẩu phải có ít nhất 8 ký tự']);
    exit;
}

try {
    // Kết nối database
    $db = new Database();
    $conn = $db->getConnection();

    // Kiểm tra token
    $stmt = $conn->prepare("
        SELECT prt.*, u.id as user_id 
        FROM password_reset_tokens prt
        JOIN users u ON prt.user_id = u.id
        WHERE prt.token = ? AND prt.expires_at > NOW() AND prt.is_used = 1
        ORDER BY prt.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $token_data = $stmt->fetch();

    if (!$token_data) {
        echo json_encode(['success' => false, 'message' => 'Token không hợp lệ hoặc đã hết hạn']);
        exit;
    }

    // Mã hóa mật khẩu mới
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Cập nhật mật khẩu
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed_password, $token_data['user_id']]);

    // Đánh dấu token đã sử dụng
    $stmt = $conn->prepare("UPDATE password_reset_tokens SET is_used = 2 WHERE id = ?");
    $stmt->execute([$token_data['id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Đổi mật khẩu thành công'
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
}
?> 