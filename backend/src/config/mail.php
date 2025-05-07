<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

class MailConfig {
    private $mail;
    private $debug = false;

    public function __construct($debug = false) {
        $this->debug = $debug;
        $this->mail = new PHPMailer(true);
        
        //Server settings
        $this->mail->isSMTP();
        $this->mail->Host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = getenv('SMTP_USERNAME');
        $this->mail->Password = getenv('SMTP_PASSWORD');
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = getenv('SMTP_PORT') ?: 587;
        $this->mail->CharSet = 'UTF-8';
        
        // Explicit authentication settings
        $this->mail->AuthType = 'LOGIN';
        
        // More permissive SSL settings for testing
        $this->mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        if ($this->debug) {
            $this->mail->SMTPDebug = 3; // Increased debug level
            $this->mail->Debugoutput = function($str, $level) {
                echo "Debug: $str\n";
            };
        }
    }

    public function testConnection() {
        try {
            if ($this->debug) {
                echo "Debug: Attempting to connect to " . getenv('SMTP_HOST') . ":" . getenv('SMTP_PORT') . "\n";
                echo "Debug: Username: " . getenv('SMTP_USERNAME') . "\n";
            }
            
            // Try to send a test email
            $this->mail->setFrom(getenv('SMTP_FROM_EMAIL'), getenv('SMTP_FROM_NAME') ?: 'HR System');
            $this->mail->addAddress(getenv('SMTP_USERNAME')); // Send to ourselves
            $this->mail->Subject = 'SMTP Test';
            $this->mail->Body = 'This is a test email to verify SMTP configuration.';
            $this->mail->AltBody = 'This is a test email to verify SMTP configuration.';
            
            if (!$this->mail->send()) {
                throw new Exception("SMTP test failed: " . $this->mail->ErrorInfo);
            }
            
            return true;
        } catch (Exception $e) {
            throw new Exception("SMTP connection test failed: " . $e->getMessage());
        }
    }

    public function sendWelcomeEmail($to, $name, $password) {
        try {
            // Set From address after successful connection
            $this->mail->setFrom(getenv('SMTP_FROM_EMAIL'), getenv('SMTP_FROM_NAME') ?: 'HR System');
            $this->mail->addAddress($to, $name);
            
            //Content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Chào mừng bạn đến với hệ thống quản lý nhân sự';
            
            $body = "
                <h2>Xin chào {$name},</h2>
                <p>Bạn đã được thêm vào hệ thống quản lý nhân sự.</p>
                <p><strong>Thông tin đăng nhập của bạn:</strong></p>
                <ul>
                    <li>Email: {$to}</li>
                    <li>Mật khẩu: {$password}</li>
                </ul>
                <p>Vui lòng đổi mật khẩu sau khi đăng nhập lần đầu.</p>
                <p>Trân trọng,<br>Phòng Nhân sự</p>
            ";
            
            $this->mail->Body = $body;
            $this->mail->AltBody = strip_tags($body);
            
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
            throw $e;
        }
    }
}

// Khởi tạo cấu hình mail
$mailConfig = new MailConfig();
?> 