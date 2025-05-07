<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Lấy dữ liệu từ request
$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email không được để trống']);
    exit;
}

try {
    // Kết nối database
    $db = new Database();
    $conn = $db->getConnection();

    // Kiểm tra email có tồn tại trong hệ thống không
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Email không tồn tại trong hệ thống']);
        exit;
    }

    // Tạo mã OTP 6 chữ số
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires_at = date('Y-m-d H:i:s', strtotime('+2 minutes'));

    // Lưu OTP vào database
    $stmt = $conn->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user['id'], $otp, $expires_at]);

    // Gửi email
    $mail = new PHPMailer(true);

    // Cấu hình SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'tan366090@gmail.com'; // Email của bạn
    $mail->Password = 'your_app_password'; // Mật khẩu ứng dụng của Gmail
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    // Cấu hình người gửi và người nhận
    $mail->setFrom('tan366090@gmail.com', 'VNPT HR System');
    $mail->addAddress($email);

    // Nội dung email
    $mail->isHTML(true);
    $mail->Subject = 'Mã OTP khôi phục mật khẩu - VNPT HR System';
    
    // Template email đẹp
    $mail->Body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
            }
            .container {
                background-color: #f9f9f9;
                border-radius: 10px;
                padding: 30px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
            }
            .logo {
                max-width: 150px;
                margin-bottom: 20px;
            }
            .otp-box {
                background-color: #667eea;
                color: white;
                padding: 15px;
                border-radius: 5px;
                font-size: 24px;
                font-weight: bold;
                text-align: center;
                margin: 20px 0;
                letter-spacing: 5px;
            }
            .footer {
                margin-top: 30px;
                text-align: center;
                font-size: 12px;
                color: #666;
            }
            .note {
                background-color: #fff3cd;
                color: #856404;
                padding: 10px;
                border-radius: 5px;
                margin: 20px 0;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <img src='https://vnpt.com.vn/wp-content/uploads/2020/07/logo-vnpt.png' alt='VNPT Logo' class='logo'>
                <h2>Khôi phục mật khẩu</h2>
            </div>
            
            <p>Xin chào,</p>
            <p>Chúng tôi đã nhận được yêu cầu khôi phục mật khẩu cho tài khoản của bạn.</p>
            
            <div class='otp-box'>$otp</div>
            
            <div class='note'>
                <p><strong>Lưu ý:</strong> Mã OTP này sẽ hết hạn sau 2 phút.</p>
            </div>
            
            <p>Nếu bạn không yêu cầu khôi phục mật khẩu, vui lòng bỏ qua email này.</p>
            
            <div class='footer'>
                <p>© 2025 VNPT. Bảo mật thông tin của bạn là ưu tiên hàng đầu.</p>
            </div>
        </div>
    </body>
    </html>";

    $mail->send();
    
    echo json_encode(['success' => true, 'message' => 'Mã OTP đã được gửi đến email của bạn']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
}
?> 