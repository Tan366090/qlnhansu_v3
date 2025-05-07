<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

// Lấy dữ liệu từ request
$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$otp = $data['otp'] ?? '';

if (empty($email) || empty($otp)) {
    echo json_encode(['success' => false, 'message' => 'Email và OTP không được để trống']);
    exit;
}

try {
    // Kết nối database
    $db = new Database();
    $conn = $db->getConnection();

    // Kiểm tra OTP
    $stmt = $conn->prepare("
        SELECT prt.*, u.id as user_id 
        FROM password_reset_tokens prt
        JOIN users u ON prt.user_id = u.id
        WHERE u.email = ? AND prt.token = ? AND prt.expires_at > NOW()
        ORDER BY prt.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$email, $otp]);
    $token = $stmt->fetch();

    if (!$token) {
        echo json_encode(['success' => false, 'message' => 'Mã OTP không hợp lệ hoặc đã hết hạn']);
        exit;
    }

    // Tạo token để đổi mật khẩu
    $reset_token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // Lưu token vào database
    $stmt = $conn->prepare("
        INSERT INTO password_reset_tokens (user_id, token, expires_at, is_used)
        VALUES (?, ?, ?, 1)
    ");
    $stmt->execute([$token['user_id'], $reset_token, $expires_at]);

    // Đánh dấu OTP đã sử dụng
    $stmt = $conn->prepare("UPDATE password_reset_tokens SET is_used = 1 WHERE id = ?");
    $stmt->execute([$token['id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Xác nhận OTP thành công',
        'token' => $reset_token
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
}
?> 